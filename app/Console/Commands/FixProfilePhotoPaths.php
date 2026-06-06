<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class FixProfilePhotoPaths extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:profile-photos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix incorrect profile photo paths in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to fix profile photo paths...');

        $users = User::whereNotNull('photo')->get();
        $fixedCount = 0;

        foreach ($users as $user) {
            $originalPath = $user->photo;
            
            // Check if the path is incorrect (has double /storage/)
            if (str_contains($originalPath, '/storage/storage/')) {
                // Fix the path by removing the extra /storage/
                $fixedPath = str_replace('/storage/storage/', '/storage/', $originalPath);
                
                // Check if the file exists with the fixed path
                $storagePath = str_replace('/storage/profile-photos/', 'profile-photos/', $fixedPath);
                
                if (Storage::disk('public')->exists($storagePath)) {
                    $user->photo = $fixedPath;
                    $user->save();
                    $fixedCount++;
                    $this->info("Fixed user {$user->id} ({$user->name}): {$originalPath} -> {$fixedPath}");
                } else {
                    $this->warn("File not found for user {$user->id} ({$user->name}): {$storagePath}");
                }
            }
        }

        $this->info("Fixed {$fixedCount} profile photo paths.");
        return 0;
    }
}
