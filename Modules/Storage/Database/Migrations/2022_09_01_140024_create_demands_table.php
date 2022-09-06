<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->text('description');
            $table->decimal('price_approx');
            $table->double('amount');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedTinyInteger('status')->default(\Modules\Storage\Entities\Demand::STATUS_WAIT);
            $table->tinyInteger('progress_status')->default(1);
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
