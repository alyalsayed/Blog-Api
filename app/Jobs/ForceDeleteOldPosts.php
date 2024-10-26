<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Container\Attributes\Log;

class ForceDeleteOldPosts implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void

    {
        info('force delete old posts');

        $date = Carbon::now()->subDays(30);

        Post::onlyTrashed()->where('deleted_at', '<', $date)->forceDelete();
    }
}
