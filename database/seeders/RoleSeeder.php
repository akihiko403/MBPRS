<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'Admin', 'slug' => Role::ADMIN],
            ['name' => 'Administrator', 'slug' => Role::ADMINISTRATOR],
            ['name' => 'Permit Staff / Encoder', 'slug' => Role::PERMIT_STAFF],
        ];

        foreach ($roles as $role) {
            Role::query()->updateOrCreate(['slug' => $role['slug']], $role);
        }

        $administratorRoleId = Role::query()->where('slug', Role::ADMINISTRATOR)->value('id');

        if ($administratorRoleId) {
            User::query()
                ->whereIn('role_id', Role::query()->whereNotIn('slug', Role::allowedSlugs())->pluck('id'))
                ->update(['role_id' => $administratorRoleId]);
        }

        Role::query()->whereNotIn('slug', Role::allowedSlugs())->delete();
    }
}
