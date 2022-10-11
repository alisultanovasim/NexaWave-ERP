<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('company_name',55);
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('propose_document_id');
            $table->unsignedBigInteger('company_id');
            $table->tinyInteger('send_back')->default(0);
//            $table->decimal('transport_tax');
//            $table->tinyInteger('payment_type')->default(0);
//            $table->tinyInteger('payment_condition');
//            $table->tinyInteger('deliver_condition');
//            $table->date('deliver_deadline');
//            $table->date('payment_deadline');
//            $table->decimal('total_price');
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('progress_status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchases');
    }
}
