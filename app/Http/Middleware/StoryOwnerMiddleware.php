<?php

namespace App\Http\Middleware;

use App\Models\Story;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StoryOwnerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $story = $request->route('story');

        if ($story instanceof Story && !$story->isOwner($request->user())) {
            abort(403, 'You can only modify your own stories.');
        }

        return $next($request);
    }
}
