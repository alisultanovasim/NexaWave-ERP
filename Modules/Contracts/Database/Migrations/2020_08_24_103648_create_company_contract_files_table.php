<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyContractFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_contract_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_contract_id');
            $table->string('name', 255);
            $table->json('file');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_contract_id')->references('id')->on('contracts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('company_contract_files');
    }
}
