<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Story;
use App\Services\AuditService;
use Illuminate\Http\Request;

class StoryManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Story::with('user')->latest();

        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $stories = $query->paginate(20);

        return view('admin.stories.index', compact('stories'));
    }

    public function destroy(Story $story)
    {
        if ($story->isApproved()) {
            return back()->with('error', 'Approved stories cannot be deleted.');
        }

        AuditService::log('delete', 'story', $story->id, "Admin deleted story: {$story->title}");
        $story->forceDelete();

        return back()->with('success', 'Story deleted permanently.');
    }
}
