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
        DB::statement("CREATE OR REPLACE VIEW goals_achievement AS 
          select 
  `clients`.`executive_id` AS `operation_executive_id`, 
  month(`operations`.`operation_date`) AS `operation_month`, 
  year(`operations`.`operation_date`) AS `operation_year`, 
  sum(
    if(
      (`operations`.`currency_id` = 1), 
      round(
        (
          `operations`.`amount` / `operations`.`exchange_rate`
        ), 
        2
      ), 
      `operations`.`amount`
    )
  ) AS `progress`, 
  (
    select 
      `eg`.`goal` 
    from 
      `executive_goals` `eg` 
    where 
      (
        (`eg`.`month` = `operation_month`) 
        and (`eg`.`year` = `operation_year`) 
        and (
          `eg`.`executive_id` = `clients`.`executive_id`
        )
      )
  ) AS `goal`, 
  round(
    (
      sum(
        if(
          (`operations`.`currency_id` = 1), 
          round(
            (
              `operations`.`amount` / `operations`.`exchange_rate`
            ), 
            2
          ), 
          `operations`.`amount`
        )
      ) / (
        select 
          `eg`.`goal` 
        from 
          `executive_goals` `eg` 
        where 
          (
            (`eg`.`month` = `operation_month`) 
            and (`eg`.`year` = `operation_year`) 
            and (
              `eg`.`executive_id` = `clients`.`executive_id`
            )
          )
      )
    ), 
    2
  ) AS `achievement`, 
  (
    select 
      `cs`.`comission` 
    from 
      `comission_schemes` `cs` 
    where 
      (
        (
          (
            sum(
              if(
                (`operations`.`currency_id` = 1), 
                round(
                  (
                    `operations`.`amount` / `operations`.`exchange_rate`
                  ), 
                  2
                ), 
                `operations`.`amount`
              )
            ) / (
              select 
                `eg`.`goal` 
              from 
                `executive_goals` `eg` 
              where 
                (
                  (`eg`.`month` = `operation_month`) 
                  and (`eg`.`year` = `operation_year`) 
                  and (
                    `eg`.`executive_id` = `clients`.`executive_id`
                  )
                )
            )
          ) >= `cs`.`min_range`
        ) 
        and (
          (
            sum(
              if(
                (`operations`.`currency_id` = 1), 
                round(
                  (
                    `operations`.`amount` / `operations`.`exchange_rate`
                  ), 
                  2
                ), 
                `operations`.`amount`
              )
            ) / (
              select 
                `eg`.`goal` 
              from 
                `executive_goals` `eg` 
              where 
                (
                  (`eg`.`month` = `operation_month`) 
                  and (`eg`.`year` = `operation_year`) 
                  and (
                    `eg`.`executive_id` = `clients`.`executive_id`
                  )
                )
            )
          ) <= `cs`.`max_range`
        )
      )
  ) AS `comission_achieved` 
from 
  (
    `operations` 
    join `clients` on(
      (
        `clients`.`id` = `operations`.`client_id`
      )
    )
  ) 
where 
  (
    year(`operations`.`operation_date`) >= 2023
  ) 
group by 
  `clients`.`executive_id`, 
  month(`operations`.`operation_date`), 
  year(`operations`.`operation_date`) 
order by 
  year(`operations`.`operation_date`), 
  month(`operations`.`operation_date`)");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW goals_achievement');
    }
};
