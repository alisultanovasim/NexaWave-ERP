<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyAgreementTerminationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_agreement_terminations', function (Blueprint $table) {
            $table->uuid('id');
            $table->unsignedBigInteger('company_agreement_id');
            $table->string('reason')->nullable()->default(null);
            $table->string('signed_by');
            $table->date('termination_date');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_agreement_id')->references('id')->on('company_agreements');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('company_agreement_terminations');
    }
}
