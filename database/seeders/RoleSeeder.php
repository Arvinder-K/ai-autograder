<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Administrator', 'slug' => 'admin', 'description' => 'Full system access and configuration management'],
            ['name' => 'Business User', 'slug' => 'business_user', 'description' => 'Create and manage user stories and business requirements'],
            ['name' => 'Technical Designer', 'slug' => 'technical_designer', 'description' => 'Validate stories, create feature lists and technical designs'],
            ['name' => 'Technical Developer', 'slug' => 'technical_developer', 'description' => 'Validate designs and generate AI coding prompts'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['slug' => $role['slug']], $role);
        }
    }
}
