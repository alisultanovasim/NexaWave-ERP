<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployerPassportInformationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employer_passport_information', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger("sign_number"); //tabel
            $table->string("name", 50);
            $table->string("surname", 50);
            $table->string("father_name", 50)->nullable();
            $table->enum("gender", ['m', 'f'])->nullable();
            $table->date("birthday");
            $table->unsignedBigInteger("nationality_id")->nullable();
            $table->unsignedBigInteger("citizen_id")->nullable();
            $table->unsignedBigInteger("birth_country_id")->nullable();
            $table->unsignedBigInteger("birth_city_id")->nullable();
            $table->unsignedBigInteger("birth_region_id")->nullable();
            $table->enum("series", ['AA', "AZE"])->nullable();
            $table->unsignedBigInteger("number")->nullable();
            $table->string("fin", 7);
            $table->string("from_organ")->nullable();
            $table->date("from_date")->nullable();
            $table->date("expired_at")->nullable();
            $table->unsignedBigInteger("eye_color_id")->nullable();
            $table->unsignedBigInteger("blood_group_id")->nullable();
            $table->string("picture")->nullable();

            $table->timestamps();
            $table->softDeletes();
            $table->foreign("nationality_id")->references("id")->on("nationalities")
                ->onDelete("cascade");
            $table->foreign("citizen_id")->references("id")->on("countries")
                ->onDelete("cascade");
            $table->foreign("birth_country_id")->references("id")->on("countries")
                ->onDelete("cascade");
            $table->foreign("birth_city_id")->references("id")->on("cities")
                ->onDelete("cascade");
            $table->foreign("birth_region_id")->references("id")->on("regions")
                ->onDelete("cascade");
            $table->foreign("eye_color_id")->references("id")->on("colors")
                ->onDelete("cascade");
            $table->foreign("blood_group_id")->references("id")->on("blood_groups")
                ->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employer_passport_information');
    }
}
