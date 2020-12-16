<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyAgreementFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_agreement_files', function (Blueprint $table) {
            $table->uuid('id');
            $table->unsignedBigInteger('company_agreement_id');
            $table->unsignedBigInteger('company_agreement_additional_id')->nullable()->default(null);
            $table->string('name');
            $table->string('file');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_agreement_id')->references('id')->on('company_agreements');
            $table->foreign('company_agreement_additional_id')->references('id')->on('company_agreement_additions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('company_agreement_files');
    }
}
