<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE sessions MODIFY api_secret TEXT NULL');
        DB::statement('ALTER TABLE sessions MODIFY moderator_secret TEXT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE sessions MODIFY api_secret VARCHAR(255) NULL');
        DB::statement('ALTER TABLE sessions MODIFY moderator_secret VARCHAR(255) NULL');
    }
};
