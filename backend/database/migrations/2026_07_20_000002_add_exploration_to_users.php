<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'exploration_pct')) {
                $table->decimal('exploration_pct', 5, 2)->default(0.00)->after('level');
            }
            if (!Schema::hasColumn('users', 'fog_tiles_revealed')) {
                $table->integer('fog_tiles_revealed')->default(0)->after('exploration_pct');
            }
            if (!Schema::hasColumn('users', 'badges')) {
                $table->json('badges')->nullable()->after('fog_tiles_revealed'); // array of earned badge names
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = ['exploration_pct', 'fog_tiles_revealed', 'badges'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
