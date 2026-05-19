<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Story permissions
            ['name' => 'Create Stories', 'slug' => 'stories.create', 'group' => 'stories'],
            ['name' => 'Edit Own Stories', 'slug' => 'stories.edit_own', 'group' => 'stories'],
            ['name' => 'Delete Own Stories', 'slug' => 'stories.delete_own', 'group' => 'stories'],
            ['name' => 'View All Stories', 'slug' => 'stories.view_all', 'group' => 'stories'],
            ['name' => 'Approve Stories', 'slug' => 'stories.approve', 'group' => 'stories'],
            ['name' => 'Delete Any Story', 'slug' => 'stories.delete_any', 'group' => 'stories'],

            // Feature list permissions
            ['name' => 'Generate Feature Lists', 'slug' => 'features.generate', 'group' => 'features'],
            ['name' => 'Edit Feature Lists', 'slug' => 'features.edit', 'group' => 'features'],
            ['name' => 'Export Feature Lists', 'slug' => 'features.export', 'group' => 'features'],

            // Technical design permissions
            ['name' => 'Create Technical Designs', 'slug' => 'designs.create', 'group' => 'designs'],
            ['name' => 'Edit Technical Designs', 'slug' => 'designs.edit', 'group' => 'designs'],
            ['name' => 'Approve Technical Designs', 'slug' => 'designs.approve', 'group' => 'designs'],

            // Coding prompts permissions
            ['name' => 'Generate Coding Prompts', 'slug' => 'prompts.generate', 'group' => 'prompts'],
            ['name' => 'Edit Coding Prompts', 'slug' => 'prompts.edit', 'group' => 'prompts'],

            // Admin permissions
            ['name' => 'Manage Users', 'slug' => 'admin.users', 'group' => 'admin'],
            ['name' => 'Manage Configuration', 'slug' => 'admin.config', 'group' => 'admin'],
            ['name' => 'View Audit Logs', 'slug' => 'admin.audit', 'group' => 'admin'],
            ['name' => 'View Analytics', 'slug' => 'admin.analytics', 'group' => 'admin'],
            ['name' => 'Manage Domains', 'slug' => 'admin.domains', 'group' => 'admin'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['slug' => $perm['slug']], $perm);
        }

        // Assign permissions to roles
        $rolePermissions = [
            'admin' => Permission::pluck('id')->toArray(),
            'business_user' => Permission::whereIn('slug', [
                'stories.create',
                'stories.edit_own',
                'stories.delete_own',
                'stories.view_all',
                'features.export',
            ])->pluck('id')->toArray(),
            'technical_designer' => Permission::whereIn('slug', [
                'stories.create',
                'stories.edit_own',
                'stories.delete_own',
                'stories.view_all',
                'stories.approve',
                'features.generate',
                'features.edit',
                'features.export',
                'designs.create',
                'designs.edit',
            ])->pluck('id')->toArray(),
            'technical_developer' => Permission::whereIn('slug', [
                'stories.create',
                'stories.edit_own',
                'stories.delete_own',
                'stories.view_all',
                'stories.approve',
                'features.generate',
                'features.edit',
                'features.export',
                'designs.create',
                'designs.edit',
                'designs.approve',
                'prompts.generate',
                'prompts.edit',
            ])->pluck('id')->toArray(),
        ];

        foreach ($rolePermissions as $slug => $permIds) {
            $role = Role::where('slug', $slug)->first();
            if ($role) {
                $role->permissions()->sync($permIds);
            }
        }
    }
}
