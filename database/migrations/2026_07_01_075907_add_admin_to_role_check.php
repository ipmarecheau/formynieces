<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite: rebuild the CHECK to add 'admin'. Uses Doctrine-free raw rebuild.
        $this->rebuildRoleCheck(['guardian', 'parent', 'student', 'admin']);
    }

    public function down(): void
    {
        $this->rebuildRoleCheck(['guardian', 'parent', 'student']);
    }

    private function rebuildRoleCheck(array $roles): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver !== 'sqlite') {
            // MySQL/Postgres path if ever migrated — adjust as needed.
            return;
        }

        $list = collect($roles)->map(fn ($r) => "'".$r."'")->implode(', ');

        // SQLite requires a table rebuild to change a CHECK. Do it inside a
        // transaction with foreign keys off, per SQLite's recommended pattern.
        DB::statement('PRAGMA foreign_keys=OFF');
        DB::transaction(function () use ($list) {
            DB::statement("
                CREATE TABLE users_new (
                    id integer primary key autoincrement not null,
                    name varchar not null,
                    email varchar not null,
                    email_verified_at datetime,
                    password varchar not null,
                    remember_token varchar,
                    created_at datetime,
                    updated_at datetime,
                    role varchar check (role in ($list)) not null default 'student',
                    parent_id integer,
                    age_attested_at datetime,
                    onboarding_completed_at datetime,
                    target_sea_year integer,
                    known_weak_areas text,
                    weekly_module_cap_override integer,
                    foreign key(parent_id) references users(id) on delete set null
                )
            ");
            DB::statement('INSERT INTO users_new SELECT * FROM users');
            DB::statement('DROP TABLE users');
            DB::statement('ALTER TABLE users_new RENAME TO users');
            DB::statement('CREATE UNIQUE INDEX users_email_unique ON users (email)');
        });
        DB::statement('PRAGMA foreign_keys=ON');
    }
};
