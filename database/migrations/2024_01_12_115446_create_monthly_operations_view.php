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
        DB::statement("CREATE OR REPLACE VIEW monthly_operations_view AS 
            select 
  month(
    `operations_view`.`operation_date`
  ) AS `month`, 
  year(
    `operations_view`.`operation_date`
  ) AS `year`, 
  `operations_view`.`type` AS `type`, 
  sum(`operations_view`.`amount`) AS `amount`, 
  round(
    (
      sum(
        (
          `operations_view`.`amount` * `operations_view`.`exchange_rate`
        )
      ) / sum(`operations_view`.`amount`)
    ), 
    4
  ) AS `mean_exchange_rate`, 
  `operations_view`.`currency_id` AS `currency_id`, 
  round(
    (
      sum(
        (
          `operations_view`.`amount` * `operations_view`.`comission_spread`
        )
      ) / sum(`operations_view`.`amount`)
    ), 
    2
  ) AS `mean_comission_spread`, 
  sum(
    `operations_view`.`comission_amount`
  ) AS `comission_amount`, 
  sum(`operations_view`.`igv`) AS `igv`, 
  count(0) AS `operations_number` 
from 
  `operations_view` 
where 
  (
    `operations_view`.`operation_status_id` in (6, 7, 8)
  ) 
group by 
  month(
    `operations_view`.`operation_date`
  ), 
  year(
    `operations_view`.`operation_date`
  ), 
  `operations_view`.`type`, 
  `operations_view`.`currency_id`

        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW monthly_operations_view');
    }
};
