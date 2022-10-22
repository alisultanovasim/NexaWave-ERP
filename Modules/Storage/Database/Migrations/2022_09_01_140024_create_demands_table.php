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
            $table->text('description');
            $table->unsignedBigInteger('took_by');
            $table->tinyInteger('is_took')->default(false);
            $table->string('attachment');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedTinyInteger('status')->default(\Modules\Storage\Entities\Demand::STATUS_WAIT);
            $table->tinyInteger('progress_status')->default(1);
            $table->tinyInteger('is_sent')->default(1);
            $table->tinyInteger('edit_status')->default(true);
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
