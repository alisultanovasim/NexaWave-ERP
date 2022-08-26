<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDemandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('demands', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('restrict');

            $table->text('description');
            $table->decimal('price_approx');
            $table->double('amount');

            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('forward_to');
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees')
                ->onDelete('restrict');

            $table->unsignedTinyInteger('status')->default(\Modules\Storage\Entities\Demand::STATUS_WAIT);


            $table->unsignedBigInteger('company_id');

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
        Schema::dropIfExists('demands');
    }
}
