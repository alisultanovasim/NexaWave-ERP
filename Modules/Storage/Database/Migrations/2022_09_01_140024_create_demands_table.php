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
            $table->string('title',55);
            $table->unsignedBigInteger('title_id');
            $table->string('kind',55);
            $table->unsignedBigInteger('kind_id');
            $table->unsignedBigInteger('model');
            $table->unsignedBigInteger('model_id');
            $table->double('amount');
            $table->text('description');
            $table->tinyInteger('type_of_doc')->default(\Modules\Storage\Entities\Demand::DRAFT);
            $table->integer('attachment');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedTinyInteger('status')->default(\Modules\Storage\Entities\Demand::STATUS_WAIT);
            $table->tinyInteger('progress_status')->default(1);
            $table->tinyInteger('is_sent')->default(1);
            $table->tinyInteger('edit_status')->default(false);
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
