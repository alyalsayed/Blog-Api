<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Post;
use Illuminate\Support\Facades\Cache;

class StatsController extends Controller
{
    /**
     * Get statistics about users and posts.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $stats = Cache::remember('user_post_stats', 60 * 60, function () {
            $totalUsers = User::count();
            $totalPosts = Post::count();
            $usersWithNoPosts = User::doesntHave('posts')->count();

            return [
                'total_users' => $totalUsers,
                'total_posts' => $totalPosts,
                'users_with_no_posts' => $usersWithNoPosts,
            ];
        });

        return response()->json(['success' => true, 'data' => $stats]);
    }
}
