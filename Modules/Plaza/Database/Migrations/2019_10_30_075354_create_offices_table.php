<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfficesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string("image")->nullable();
            $table->unsignedBigInteger('company_id');
            $table->unsignedTinyInteger('status')->default(config('plaza.office.status.active'));

            $table->unsignedTinyInteger('entity');
            $table->string('voen')->nullable();

            $table->decimal('per_month');
            $table->date('start_time');
            $table->unsignedSmallInteger('month_count');
            $table->unsignedSmallInteger('payed_month_count')->default(0);
            $table->timestamp('create_at')->useCurrent();
            $table->softDeletes();

            $table->json('history')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('offices');
    }
}
