<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyAgreementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_agreements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('parent_id')->nullable()->default(null);
            $table->json('contract_type')->nullable()->default(null);
            $table->enum('agreement_type', ['internal', 'external']);
            $table->char('agreement_number');
            $table->string('name');
            $table->date('start_date')->nullable()->default(null);
            $table->date('end_date')->nullable()->default(null);
            $table->date('agreement_date')->nullable()->default(null);
            $table->json('currency');
            $table->float('amount');
            $table->float('vat');
            $table->string('subject');
            $table->unsignedInteger('status')->default(0);
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('parent_id')->references('id')->on('company_agreements');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('company_agreements');
    }
}
