<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArchiveRejectedPurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archive_rejected_purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('from_id');
            $table->unsignedBigInteger('purchase_id');
            $table->text('reason');
            $table->tinyInteger('is_active')->default(1);
            $table->softDeletes();
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
        Schema::dropIfExists('archive_rejected_purchases');
    }
}
