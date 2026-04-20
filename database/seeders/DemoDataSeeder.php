<?php

namespace Database\Seeders;

use App\Models\BuildingCategory;
use App\Models\BuildingPermit;
use App\Models\BuildingType;
use App\Models\PermitStatusLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = Role::query()->pluck('id', 'slug');

        $users = [
            ['name' => 'System Admin', 'username' => 'admin', 'email' => 'admin@mbprs.local', 'role_id' => $roles[Role::ADMIN], 'is_active' => true, 'password' => Hash::make('password123')],
            ['name' => 'System Administrator', 'username' => 'administrator', 'email' => 'administrator@mbprs.local', 'role_id' => $roles[Role::ADMINISTRATOR], 'is_active' => true, 'password' => Hash::make('password123')],
            ['name' => 'Permit Encoder', 'username' => 'encoder', 'email' => 'encoder@mbprs.local', 'role_id' => $roles[Role::PERMIT_STAFF], 'is_active' => true, 'password' => Hash::make('password123')],
        ];

        foreach ($users as $user) {
            User::query()->updateOrCreate(['username' => $user['username']], $user);
        }

        User::query()->whereNotIn('username', ['admin', 'administrator', 'encoder'])->delete();

        $admin = User::query()->where('username', 'admin')->firstOrFail();
        $administrator = User::query()->where('username', 'administrator')->firstOrFail();

        foreach (['Residential', 'Commercial', 'Industrial', 'Institutional'] as $type) {
            BuildingType::query()->updateOrCreate(['name' => $type], [
                'description' => $type.' buildings',
                'created_by' => $admin->id,
            ]);
        }

        foreach (['1 Storey', '2 Storey', '3 Storey', 'Multi-storey'] as $category) {
            BuildingCategory::query()->updateOrCreate(['name' => $category], [
                'description' => $category,
                'created_by' => $admin->id,
            ]);
        }

        if (BuildingPermit::query()->exists()) {
            return;
        }

        $type = BuildingType::query()->where('name', 'Residential')->firstOrFail();
        $category = BuildingCategory::query()->where('name', '2 Storey')->firstOrFail();

        foreach ([
            ['permit_id' => 'MBPR-'.now()->format('Ymd').'-0001', 'owner_last_name' => 'Santos', 'owner_first_name' => 'Maria', 'owner_middle_name' => 'Lopez', 'owner_suffix' => null, 'building_type_id' => $type->id, 'building_category_id' => $category->id, 'barangay' => 'Poblacion', 'city_municipality' => 'Mambajao', 'province' => 'Camiguin', 'status' => BuildingPermit::STATUS_PENDING, 'created_by' => $admin->id],
            ['permit_id' => 'MBPR-'.now()->format('Ymd').'-0002', 'owner_last_name' => 'Reyes', 'owner_first_name' => 'Jose', 'owner_middle_name' => null, 'owner_suffix' => 'Jr.', 'building_type_id' => $type->id, 'building_category_id' => $category->id, 'barangay' => 'San Isidro', 'city_municipality' => 'Iligan City', 'province' => 'Lanao del Norte', 'status' => BuildingPermit::STATUS_APPROVED, 'created_by' => $admin->id, 'approved_by' => $administrator->id, 'approved_at' => now()->subDays(3)],
            ['permit_id' => 'MBPR-'.now()->format('Ymd').'-0003', 'owner_last_name' => 'Dela Cruz', 'owner_first_name' => 'Ana', 'owner_middle_name' => 'P.', 'owner_suffix' => null, 'building_type_id' => $type->id, 'building_category_id' => $category->id, 'barangay' => 'Mabini', 'city_municipality' => 'Valencia', 'province' => 'Bukidnon', 'status' => BuildingPermit::STATUS_RETURNED, 'remarks' => 'Missing structural plans.', 'created_by' => $admin->id],
            ['permit_id' => 'MBPR-'.now()->format('Ymd').'-0004', 'owner_last_name' => 'Garcia', 'owner_first_name' => 'Luis', 'owner_middle_name' => null, 'owner_suffix' => null, 'building_type_id' => $type->id, 'building_category_id' => $category->id, 'barangay' => 'Poblacion', 'city_municipality' => 'Cagayan de Oro City', 'province' => 'Misamis Oriental', 'status' => BuildingPermit::STATUS_APPROVED, 'created_by' => $admin->id, 'approved_by' => $administrator->id, 'approved_at' => now()->subMonths(1)],
        ] as $permit) {
            $record = BuildingPermit::query()->create($permit);

            PermitStatusLog::query()->create([
                'building_permit_id' => $record->id,
                'old_status' => null,
                'new_status' => $record->status,
                'remarks' => $record->remarks,
                'acted_by' => $record->approved_by ?? $record->created_by,
            ]);
        }
    }
}
