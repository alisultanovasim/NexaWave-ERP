<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStructableColumnsToSectorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sectors', function (Blueprint $table) {
            $table->unsignedBigInteger('structable_id')->nullable()->default(null);
            $table->char('structable_type')->nullable()->default(null);
            $table->unsignedBigInteger('section_id')->nullable()->default(null)->change();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sectors', function (Blueprint $table) {
            $table->dropColumn('company_id');
            $table->dropColumn('structable_id');
            $table->dropColumn('structable_type');
        });
    }
}
