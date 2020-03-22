<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrganizationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string("code", 10);
            $table->string("name");
            $table->string("short_name", 10)->nullable();
            $table->unsignedBigInteger("organization_type_id")->nullable();
            $table->boolean("is_head")->default(true);
            $table->unsignedBigInteger("assigned_to")->nullable();
            $table->string("phone", 20)->nullable();
            $table->string("fax", 30)->nullable();
            $table->string("address")->nullable();
            $table->string("email", 50)->nullable();
            $table->string("website", 50)->nullable();
            $table->string("post_code", 10)->nullable();
            $table->unsignedBigInteger("country_id");
            $table->unsignedBigInteger("city_id")->nullable();
            $table->unsignedBigInteger("region_id")->nullable();
            $table->string("profession")->nullable();
            $table->boolean("is_closed")->default(false);
            $table->date("closed_date")->nullable();
            $table->string("note")->nullable();
            $table->unsignedBigInteger("bank_information_id")->nullable();
            $table->bigInteger("index");
            $table->unsignedBigInteger("company_id")->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign("organization_type_id")
                ->references("id")->on("organization_types")->onDelete("cascade");
            $table->foreign("assigned_to")
                ->references("id")->on("organizations")->onDelete("cascade");
            $table->foreign("country_id")
                ->references("id")->on("countries")->onDelete("cascade");
            $table->foreign("city_id")
                ->references("id")->on("cities")->onDelete("cascade");
            $table->foreign("region_id")
                ->references("id")->on("regions")->onDelete("cascade");
            $table->foreign("bank_information_id")
                ->references("id")->on("bank_information")->onDelete("cascade");

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('organizations');
    }
}
