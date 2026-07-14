<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Creator authenticity in [0,1] — a ranking signal that rewards
            // genuine accounts. Seeded/curated for now; a real trust model
            // would compute it from account and engagement history.
            $table->float('authenticity_score')->default(0.5)->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('authenticity_score');
        });
    }
};
