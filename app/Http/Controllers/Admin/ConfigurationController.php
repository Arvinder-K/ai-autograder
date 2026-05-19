<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Configuration;
use App\Models\Domain;
use App\Services\AuditService;
use Illuminate\Http\Request;

class ConfigurationController extends Controller
{
    public function index()
    {
        $configs = Configuration::orderBy('group')->orderBy('key')->get()->groupBy('group');
        $domains = Domain::with('businessUnits')->orderBy('sort_order')->get();

        return view('admin.config.index', compact('configs', 'domains'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'configs' => 'required|array',
            'configs.*.id' => 'required|exists:configurations,id',
            'configs.*.value' => 'nullable|string',
        ]);

        foreach ($validated['configs'] as $configData) {
            $config = Configuration::find($configData['id']);
            if ($config) {
                $oldValue = $config->value;
                $config->update(['value' => $configData['value']]);

                if ($oldValue !== $configData['value']) {
                    AuditService::log(
                        'update',
                        'configuration',
                        $config->id,
                        "Config updated: {$config->group}.{$config->key}",
                        ['value' => $oldValue],
                        ['value' => $configData['value']]
                    );
                }
            }
        }

        return back()->with('success', 'Configuration saved.');
    }
}
