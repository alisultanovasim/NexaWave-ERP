<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyContractChangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_contract_changes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_contract_id');
            $table->date('start_date')->nullable()->default(null);
            $table->date('start_date')->nullable()->default(null);
            $table->string('subject')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('company_contract_changes');
    }
}
