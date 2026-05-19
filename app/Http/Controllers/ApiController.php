<?php

namespace App\Http\Controllers;

use App\Models\BusinessUnit;
use App\Models\Domain;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function businessUnits(Request $request)
    {
        $domainIds = $request->input('domain_ids', []);

        if (empty($domainIds)) {
            return response()->json([]);
        }

        $units = BusinessUnit::whereIn('domain_id', $domainIds)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'domain_id', 'name', 'slug']);

        return response()->json($units);
    }

    public function domains()
    {
        $domains = Domain::where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'slug', 'icon']);

        return response()->json($domains);
    }
}
