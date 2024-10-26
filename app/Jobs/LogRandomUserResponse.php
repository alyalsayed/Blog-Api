<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LogRandomUserResponse implements ShouldQueue
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
    public function handle()
    {
        $response = Http::get('https://randomuser.me/api/');

        if ($response->successful()) {
            $results = $response->json()['results'] ?? null;

            if ($results) {
                Log::info('Random User Response:', $results);
            } else {
                Log::warning('No results found in the Random User Response.');
            }
        } else {
            Log::error('Failed to fetch random user', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }
    }
}