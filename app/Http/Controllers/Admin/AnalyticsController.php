<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\FeatureList;
use App\Models\Story;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();
        $totalStories = Story::count();
        $approvedStories = Story::where('status', 'approved')->count();
        $totalFeatureLists = FeatureList::count();

        // Stories by status
        $storiesByStatus = Story::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Stories by domain (from JSON field)
        $storiesByDomain = Story::whereNotNull('selected_domains')
            ->get()
            ->flatMap(fn($s) => $s->selected_domains ?? [])
            ->countBy()
            ->sortDesc()
            ->take(10)
            ->toArray();

        // Recent activity (last 30 days)
        $recentActivity = AuditLog::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // Top users by stories
        $topUsers = User::withCount('stories')
            ->orderByDesc('stories_count')
            ->take(10)
            ->get();

        return view('admin.analytics.index', compact(
            'totalUsers',
            'activeUsers',
            'totalStories',
            'approvedStories',
            'totalFeatureLists',
            'storiesByStatus',
            'storiesByDomain',
            'recentActivity',
            'topUsers'
        ));
    }
}
