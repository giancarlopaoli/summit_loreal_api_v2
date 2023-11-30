<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*Schema::create('operations_view_view', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });*/

        DB::statement("CREATE OR REPLACE VIEW operations_view AS 
            select 
  `operations`.`id` AS `id`, 
  `operations`.`code` AS `code`, 
  `operations`.`class` AS `class`, 
  `operations`.`type` AS `type`, 
  `operations`.`client_id` AS `client_id`, 
  `operations`.`user_id` AS `user_id`, 
  `operations`.`use_escrow_account` AS `use_escrow_account`, 
  `operations`.`amount` AS `amount`, 
  `operations`.`currency_id` AS `currency_id`, 
  `operations`.`exchange_rate` AS `exchange_rate`, 
  `operations`.`comission_spread` AS `comission_spread`, 
  `operations`.`comission_amount` AS `comission_amount`, 
  `operations`.`igv` AS `igv`, 
  `operations`.`spread` AS `spread`, 
  `operations`.`operation_status_id` AS `operation_status_id`, 
  `operations`.`operation_date` AS `operation_date`, 
  (
    select 
      if(
        (`clients`.`customer_type` = 'PJ'), 
        `clients`.`name`, 
        concat(
          `clients`.`name`, ' ', `clients`.`last_name`, 
          ' ', `clients`.`mothers_name`
        )
      ) 
    from 
      `clients` 
    where 
      (
        `clients`.`id` = `operations`.`client_id`
      )
  ) AS `client_name`, 
  (
    select 
      `clients`.`executive_id` 
    from 
      `clients` 
    where 
      (
        (
          `clients`.`id` = `operations`.`client_id`
        ) 
        and (
          `operations`.`operation_date` >= `clients`.`comission_start_date`
        )
      )
  ) AS `executive_id`, 
  (
    select 
      concat(
        `users`.`name`, ' ', `users`.`last_name`
      ) 
    from 
      `users` 
    where 
      (`executive_id` = `users`.`id`)
  ) AS `executive_name`, 
  if(
    isnull(
      (
        select 
          `executives_comissions`.`executive_id` 
        from 
          `executives_comissions` 
        where 
          (
            (
              `executives_comissions`.`client_id` = `operations`.`client_id`
            ) 
            and (
              `operations`.`operation_date` >= `executives_comissions`.`start_date`
            ) 
            and (
              `operations`.`operation_date` <= `executives_comissions`.`end_date`
            )
          ) 
        order by 
          `executives_comissions`.`start_date` desc 
        limit 
          1
      )
    ), 
    coalesce(
      (
        select 
          if(
            (
              (
                (
                  `ga`.`operation_executive_id` = 2801
                ) 
                or (
                  `ga`.`operation_executive_id` = 2811
                )
              ) 
              and (
                year(`operations`.`operation_date`) = 2023
              )
            ), 
            0.05, 
            `ga`.`comission_achieved`
          ) 
        from 
          `goals_achievement` `ga` 
        where 
          (
            (
              `ga`.`operation_executive_id` = `executive_id`
            ) 
            and (
              `ga`.`operation_month` = month(`operations`.`operation_date`)
            ) 
            and (
              `ga`.`operation_year` = year(`operations`.`operation_date`)
            )
          )
      ), 
      0
    ), 
    least(
      (
        select 
          `clients`.`comission` 
        from 
          `clients` 
        where 
          (
            (
              `clients`.`id` = `operations`.`client_id`
            ) 
            and (
              `operations`.`operation_date` >= `clients`.`comission_start_date`
            )
          )
      ), 
      coalesce(
        (
          select 
            if(
              (
                (
                  (
                    `ga`.`operation_executive_id` = 2801
                  ) 
                  or (
                    `ga`.`operation_executive_id` = 2811
                  )
                ) 
                and (
                  year(`operations`.`operation_date`) = 2023
                )
              ), 
              0.05, 
              `ga`.`comission_achieved`
            ) 
          from 
            `goals_achievement` `ga` 
          where 
            (
              (
                `ga`.`operation_executive_id` = `executive_id`
              ) 
              and (
                `ga`.`operation_month` = month(`operations`.`operation_date`)
              ) 
              and (
                `ga`.`operation_year` = year(`operations`.`operation_date`)
              )
            )
        ), 
        0
      )
    )
  ) AS `executive_comission`, 
  (
    select 
      `clients`.`comission_start_date` 
    from 
      `clients` 
    where 
      (
        (
          `clients`.`id` = `operations`.`client_id`
        ) 
        and (
          `operations`.`operation_date` >= `clients`.`comission_start_date`
        )
      )
  ) AS `executive_start_date`, 
  (
    select 
      `executives_comissions`.`executive_id` 
    from 
      `executives_comissions` 
    where 
      (
        (
          `executives_comissions`.`client_id` = `operations`.`client_id`
        ) 
        and (
          `operations`.`operation_date` >= `executives_comissions`.`start_date`
        ) 
        and (
          `operations`.`operation_date` <= `executives_comissions`.`end_date`
        )
      ) 
    order by 
      `executives_comissions`.`start_date` desc 
    limit 
      1
  ) AS `executive2_id`, 
  (
    select 
      `executives_comissions`.`comission` 
    from 
      `executives_comissions` 
    where 
      (
        (
          `executives_comissions`.`client_id` = `operations`.`client_id`
        ) 
        and (
          `operations`.`operation_date` >= `executives_comissions`.`start_date`
        ) 
        and (
          `operations`.`operation_date` <= `executives_comissions`.`end_date`
        )
      ) 
    order by 
      `executives_comissions`.`start_date` desc 
    limit 
      1
  ) AS `executive2_comission`, 
  (
    select 
      `executives_comissions`.`start_date` 
    from 
      `executives_comissions` 
    where 
      (
        (
          `executives_comissions`.`client_id` = `operations`.`client_id`
        ) 
        and (
          `operations`.`operation_date` >= `executives_comissions`.`start_date`
        ) 
        and (
          `operations`.`operation_date` <= `executives_comissions`.`end_date`
        )
      ) 
    order by 
      `executives_comissions`.`start_date` desc 
    limit 
      1
  ) AS `executive2_start_date` 
from 
  `operations` 
where 
  (
    (
      `operations`.`operation_status_id` in (6, 7, 8)
    ) 
    and (
      not(
        `operations`.`client_id` in (
          select 
            `clients`.`id` 
          from 
            `clients` 
          where 
            (`clients`.`type` = 'PL')
        )
      )
    )
  )");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW operations_view');
    }
};
