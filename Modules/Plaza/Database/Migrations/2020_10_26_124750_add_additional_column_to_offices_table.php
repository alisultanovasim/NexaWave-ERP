<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Plaza\Entities\Office;

class AddAdditionalColumnToOfficesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('offices', function (Blueprint $table) {
            $table->float("price_without_adv");
            $table->boolean("is_adv_payer")->default(true);
            $table->boolean("is_buy_attendance")->default(false);
            $table->unsignedInteger("parking_count");
            $table->unsignedSmallInteger("parking_type")->default(Office::PARKING_ABOVE_GROUND);
            $table->float("internet_monthly_price")->nullable();
            $table->float("electric_monthly_price")->nullable();
            $table->boolean("is_pay_for_repair")->default(false);
            $table->integer("free_entrance_card");
            $table->integer("paid_entrance_card");
            $table->float("price_per_card");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('offices', function (Blueprint $table) {
            $table->dropColumn("price_without_adv");
            $table->dropColumn("is_adv_payer");
            $table->dropColumn("is_buy_attendance");
            $table->dropColumn("parking_count");
            $table->dropColumn("parking_type");
            $table->dropColumn("internet_monthly_price");
            $table->dropColumn("electric_monthly_price");
            $table->dropColumn("is_pay_for_repair");
            $table->dropColumn("free_entrance_card");
            $table->dropColumn("paid_entrance_card");
            $table->dropColumn("price_per_card");
        });
    }
}
