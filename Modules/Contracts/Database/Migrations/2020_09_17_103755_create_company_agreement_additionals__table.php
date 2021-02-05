<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyAgreementAdditionalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_agreement_additions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_agreement_id');
            $table->date('date');
            $table->date('start_date')->nullable()->default(null);
            $table->date('end_date')->nullable()->default(null);
            $table->json('currency');
            $table->string('subject');
            $table->float('amount');
            $table->enum('amount_type', ['plus', 'minus']);
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
        Schema::dropIfExists('company_agreement_additions');
    }
}
