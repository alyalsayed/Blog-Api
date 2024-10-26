<?php

use Illuminate\Support\Facades\Schedule;
use App\Jobs\ForceDeleteOldPosts;
use App\Jobs\LogRandomUserResponse;


// Schedule::command('app:test-job')->everyMinute();

Schedule::job(new ForceDeleteOldPosts())->daily();

Schedule::job(new LogRandomUserResponse())->everyMinute();