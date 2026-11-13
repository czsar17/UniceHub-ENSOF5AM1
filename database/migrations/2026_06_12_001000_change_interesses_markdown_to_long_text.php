<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // modify so existe no mysql, no sqlite o text ja nao tem limite
        if ($this->ehMysql() && Schema::hasColumn('users', 'interesses_markdown')) {
            DB::statement('ALTER TABLE users MODIFY interesses_markdown LONGTEXT NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if ($this->ehMysql() && Schema::hasColumn('users', 'interesses_markdown')) {
            DB::statement('ALTER TABLE users MODIFY interesses_markdown TEXT NULL');
        }
    }

    private function ehMysql(): bool
    {
        return in_array(DB::getDriverName(), ['mysql', 'mariadb']);
    }
};
