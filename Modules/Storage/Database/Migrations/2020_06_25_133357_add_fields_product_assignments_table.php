<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsProductAssignmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_assignments', function (Blueprint $table) {
            $table->unsignedTinyInteger('status')->default(\Modules\Storage\Entities\ProductAssignment::ACTIVE);
            $table->json('reasons');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_assignments', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('reasons');
        });
    }
}
