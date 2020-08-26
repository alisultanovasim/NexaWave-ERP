<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyContractDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_contract_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_contract_id');
            $table->string('company_name');
            $table->string('tax_rate');
            $table->string('bank_voen');
            $table->string('company_voen');
            $table->string('account_number');
            $table->string('bank_code');
            $table->string('bank_name');
            $table->string('correspondent_account');
            $table->string('signed_person');
            $table->string('position');
            $table->enum('type', ['client', 'producer']);
            $table->timestamps();
            $table->foreign('company_contract_id')->references('id')->on('company_contracts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('company_contract_details');
    }
}
