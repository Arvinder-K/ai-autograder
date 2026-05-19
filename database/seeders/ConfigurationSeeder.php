<?php

namespace Database\Seeders;

use App\Models\Configuration;
use Illuminate\Database\Seeder;

class ConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            // General
            ['group' => 'general', 'key' => 'app_name', 'value' => 'AI Auto Grader', 'type' => 'string', 'label' => 'Application Name'],
            ['group' => 'general', 'key' => 'default_currency', 'value' => 'USD', 'type' => 'string', 'label' => 'Default Currency'],
            ['group' => 'general', 'key' => 'gst_rate', 'value' => '0', 'type' => 'string', 'label' => 'GST Rate (%)'],
            ['group' => 'general', 'key' => 'default_timezone', 'value' => 'UTC', 'type' => 'string', 'label' => 'Default Timezone'],

            // AI Configuration
            ['group' => 'ai', 'key' => 'ai_provider', 'value' => 'openai', 'type' => 'string', 'label' => 'AI Provider'],
            ['group' => 'ai', 'key' => 'ai_model', 'value' => 'gpt-4o', 'type' => 'string', 'label' => 'AI Model'],
            ['group' => 'ai', 'key' => 'ai_max_tokens', 'value' => '4096', 'type' => 'integer', 'label' => 'Max Tokens per Request'],
            ['group' => 'ai', 'key' => 'ai_temperature', 'value' => '0.7', 'type' => 'string', 'label' => 'AI Temperature'],
            ['group' => 'ai', 'key' => 'micro_prompt_max_tokens', 'value' => '2000', 'type' => 'integer', 'label' => 'Micro Prompt Max Tokens'],

            // SSO Configuration
            ['group' => 'sso', 'key' => 'microsoft_enabled', 'value' => '1', 'type' => 'boolean', 'label' => 'Enable Microsoft SSO'],
            ['group' => 'sso', 'key' => 'google_enabled', 'value' => '1', 'type' => 'boolean', 'label' => 'Enable Google SSO'],
            ['group' => 'sso', 'key' => 'local_login_enabled', 'value' => '0', 'type' => 'boolean', 'label' => 'Enable Local Email/Password Login'],

            // Story Settings
            ['group' => 'stories', 'key' => 'auto_version', 'value' => '1', 'type' => 'boolean', 'label' => 'Auto-version on save'],
            ['group' => 'stories', 'key' => 'max_versions', 'value' => '50', 'type' => 'integer', 'label' => 'Maximum versions per story'],
            ['group' => 'stories', 'key' => 'require_approval', 'value' => '1', 'type' => 'boolean', 'label' => 'Require approval before feature generation'],
        ];

        foreach ($configs as $config) {
            Configuration::firstOrCreate(
                ['group' => $config['group'], 'key' => $config['key']],
                $config
            );
        }
    }
}
