<?php

namespace App\Http\Controllers;

use App\Models\Story;
use App\Services\OpenAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(private OpenAIService $openAIService) {}

    public function index()
    {
        $user = Auth::user();

        $myStories = Story::where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        $stats = [
            'total_stories' => Story::where('user_id', $user->id)->count(),
            'draft_stories' => Story::where('user_id', $user->id)->where('status', 'draft')->count(),
            'approved_stories' => Story::where('user_id', $user->id)->where('status', 'approved')->count(),
            'total_all_stories' => Story::count(),
        ];

        $recentActivity = Story::with('user')
            ->latest()
            ->take(5)
            ->get();

        $apiStatus = $this->openAIService->checkConnection();

        return view('dashboard.index', compact('myStories', 'stats', 'recentActivity', 'apiStatus'));
    }
}
