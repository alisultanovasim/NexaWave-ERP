<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CreateUserResetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_resets', function (Blueprint $table) {
            $table->uuid('id')->default(Str::uuid());
            $table->char('email', 150);
            $table->string('user_ip');
            $table->string('user_agent');
            $table->string('hash');
            $table->timestamp('expire_date');
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
        Schema::dropIfExists('user_resets');
    }
}
