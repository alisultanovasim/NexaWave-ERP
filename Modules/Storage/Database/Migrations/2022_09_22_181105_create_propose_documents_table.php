<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProposeDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('propose_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('demand_id');
            $table->string('offer_file');
            $table->text('description');
            $table->unsignedBigInteger('employee_id');
            $table->tinyInteger('status')->default(\Modules\Storage\Entities\ProposeDocument::STATUS_WAIT);
            $table->tinyInteger('progress_status')->default(1);
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
        Schema::dropIfExists('propose_documents');
    }
}
