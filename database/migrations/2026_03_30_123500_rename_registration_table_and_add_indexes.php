<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('registration') && ! Schema::hasTable('registrations')) {
            Schema::rename('registration', 'registrations');
        }

        Schema::table('workshops', function (Blueprint $table) {
            $table->index('starts_at', 'workshops_starts_at_idx');
        });

        Schema::table('registrations', function (Blueprint $table) {
            $table->index('status', 'registrations_status_idx');
            $table->index('workshop_id', 'registrations_workshop_id_idx');
            $table->index('user_id', 'registrations_user_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropIndex('registrations_status_idx');
            $table->dropIndex('registrations_workshop_id_idx');
            $table->dropIndex('registrations_user_id_idx');
        });

        Schema::table('workshops', function (Blueprint $table) {
            $table->dropIndex('workshops_starts_at_idx');
        });

        if (Schema::hasTable('registrations') && ! Schema::hasTable('registration')) {
            Schema::rename('registrations', 'registration');
        }
    }
};

