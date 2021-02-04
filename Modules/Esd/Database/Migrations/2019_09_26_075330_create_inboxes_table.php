<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInboxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inboxes', function (Blueprint $table) {
            $table->bigIncrements('id');
//            $table->string("from");
//            $table->longText("description")->nullable();
//            $table->unsignedBigInteger("company_id");
//            $table->string("url");
//            $table->string("theme");
//            $table->unsignedTinyInteger("is_read")->default(0);
//            $table->timestamp("create_at")->useCurrent();
//            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
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
        Schema::dropIfExists('inboxes');
    }
}
