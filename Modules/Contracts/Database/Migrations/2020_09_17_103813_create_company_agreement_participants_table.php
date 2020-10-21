<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyAgreementParticipantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_agreement_participants', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_agreement_id');
            $table->enum('type', ['client', 'executor']);
            $table->unsignedBigInteger('company_id')->nullable()->default(null);
            $table->string('name');
            $table->float('tax_rate')->nullable()->default(null);
            $table->string('bank_voen')->nullable()->default(null);
            $table->string('company_voen')->nullable()->default(null);
            $table->string('account_number')->nullable()->default(null);
            $table->string('bank_code')->nullable()->default(null);
            $table->string('bank_name')->nullable()->default(null);
            $table->string('correspondent_account')->nullable()->default(null);
            $table->string('signed_person_name')->nullable()->default(null);
            $table->string('signed_person_position')->nullable()->default(null);
            $table->string('swift')->nullable()->default(null);
            $table->string('intermediary_bank_name')->nullable()->default(null);
            $table->string('intermediary_bank_swift')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_agreement_id')->references('id')->on('company_agreements');
            $table->foreign('company_id')->references('id')->on('companies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('company_agreement_participants');
    }
}
