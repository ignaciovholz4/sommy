<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instagram_reels', function (Blueprint $table) {
            $table->string('video')->nullable()->after('url');
            $table->string('poster')->nullable()->after('video');
        });

        // Sin doctrine/dbal en el proyecto: se altera con SQL directo en vez de ->change().
        DB::statement('ALTER TABLE instagram_reels MODIFY url VARCHAR(255) NULL');
    }

    public function down(): void
    {
        DB::statement("UPDATE instagram_reels SET url = '' WHERE url IS NULL");
        DB::statement('ALTER TABLE instagram_reels MODIFY url VARCHAR(255) NOT NULL');

        Schema::table('instagram_reels', function (Blueprint $table) {
            $table->dropColumn(['video', 'poster']);
        });
    }
};
