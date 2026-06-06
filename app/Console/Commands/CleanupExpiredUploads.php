<?php

namespace App\Console\Commands;

use App\Models\UploadSession;
use App\Models\FileChunk;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CleanupExpiredUploads extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'uploads:cleanup 
                            {--dry-run : Show what would be cleaned without actually deleting}
                            {--force : Skip confirmation prompts}';

    /**
     * The console command description.
     */
    protected $description = 'Clean up expired upload sessions, chunks, and temporary files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $isForce = $this->option('force');

        $this->info('🧹 Starting upload cleanup process...');
        
        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No files will be deleted');
        }

        // Get expired sessions
        $expiredSessions = UploadSession::expired()->with('chunks')->get();
        
        if ($expiredSessions->isEmpty()) {
            $this->info('✅ No expired upload sessions found.');
            return;
        }

        $this->info("Found {$expiredSessions->count()} expired upload sessions");

        // Show what will be cleaned
        $this->displayCleanupSummary($expiredSessions);

        // Confirm unless forced
        if (!$isForce && !$isDryRun) {
            if (!$this->confirm('Do you want to proceed with cleanup?')) {
                $this->info('Cleanup cancelled.');
                return;
            }
        }

        // Perform cleanup
        $stats = $this->performCleanup($expiredSessions, $isDryRun);

        // Display results
        $this->displayResults($stats, $isDryRun);
    }

    /**
     * Display cleanup summary
     */
    private function displayCleanupSummary($expiredSessions)
    {
        $this->info("\n📊 Cleanup Summary:");
        
        $totalSessions = $expiredSessions->count();
        $totalChunks = $expiredSessions->sum(function ($session) {
            return $session->chunks->count();
        });
        
        $totalSize = 0;
        $tempDirs = [];
        
        foreach ($expiredSessions as $session) {
            $totalSize += $session->total_size;
            if ($session->temp_directory && is_dir($session->temp_directory)) {
                $tempDirs[] = $session->temp_directory;
            }
        }

        $this->table([
            'Metric', 'Count'
        ], [
            ['Expired Sessions', $totalSessions],
            ['Total Chunks', $totalChunks],
            ['Estimated Size', $this->formatBytes($totalSize)],
            ['Temp Directories', count($tempDirs)],
        ]);

        // Show session details
        if ($this->option('verbose')) {
            $this->info("\n📋 Session Details:");
            $headers = ['Session ID', 'File Name', 'Size', 'Chunks', 'Status', 'Expired Since'];
            $rows = [];
            
            foreach ($expiredSessions as $session) {
                $rows[] = [
                    substr($session->session_id, 0, 8) . '...',
                    $session->original_filename,
                    $this->formatBytes($session->total_size),
                    $session->chunks->count(),
                    $session->status,
                    $session->expires_at->diffForHumans()
                ];
            }
            
            $this->table($headers, $rows);
        }
    }

    /**
     * Perform the actual cleanup
     */
    private function performCleanup($expiredSessions, $isDryRun)
    {
        $stats = [
            'sessions_cleaned' => 0,
            'chunks_cleaned' => 0,
            'temp_dirs_removed' => 0,
            'temp_files_removed' => 0,
            'bytes_freed' => 0,
            'errors' => 0
        ];

        $progressBar = $this->output->createProgressBar($expiredSessions->count());
        $progressBar->start();

        foreach ($expiredSessions as $session) {
            try {
                // Clean up temp directory and files
                if ($session->temp_directory && is_dir($session->temp_directory)) {
                    $dirStats = $this->cleanupTempDirectory($session->temp_directory, $isDryRun);
                    $stats['temp_files_removed'] += $dirStats['files_removed'];
                    $stats['bytes_freed'] += $dirStats['bytes_freed'];
                    
                    if ($dirStats['dir_removed']) {
                        $stats['temp_dirs_removed']++;
                    }
                }

                // Count chunks before deletion
                $chunkCount = $session->chunks->count();
                $stats['chunks_cleaned'] += $chunkCount;

                if (!$isDryRun) {
                    // Delete chunks (cascades via model events)
                    $session->chunks()->delete();
                    
                    // Delete session
                    $session->delete();
                }

                $stats['sessions_cleaned']++;

            } catch (\Exception $e) {
                $stats['errors']++;
                Log::error('Error cleaning up upload session', [
                    'session_id' => $session->session_id,
                    'error' => $e->getMessage()
                ]);
                
                if ($this->option('verbose')) {
                    $this->error("Error cleaning session {$session->session_id}: {$e->getMessage()}");
                }
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        // Clean up orphaned chunks (chunks without sessions)
        if (!$isDryRun) {
            $orphanedChunks = FileChunk::whereNotExists(function ($query) {
                $query->select('session_id')
                      ->from('upload_sessions')
                      ->whereRaw('upload_sessions.session_id = file_chunks.session_id');
            })->count();

            if ($orphanedChunks > 0) {
                $this->info("Cleaning up {$orphanedChunks} orphaned chunks...");
                FileChunk::whereNotExists(function ($query) {
                    $query->select('session_id')
                          ->from('upload_sessions')
                          ->whereRaw('upload_sessions.session_id = file_chunks.session_id');
                })->delete();
                
                $stats['chunks_cleaned'] += $orphanedChunks;
            }
        }

        return $stats;
    }

    /**
     * Clean up temporary directory
     */
    private function cleanupTempDirectory($tempDir, $isDryRun)
    {
        $stats = [
            'files_removed' => 0,
            'bytes_freed' => 0,
            'dir_removed' => false
        ];

        if (!is_dir($tempDir)) {
            return $stats;
        }

        try {
            $files = glob($tempDir . '/*');
            
            foreach ($files as $file) {
                if (is_file($file)) {
                    $stats['bytes_freed'] += filesize($file);
                    $stats['files_removed']++;
                    
                    if (!$isDryRun) {
                        unlink($file);
                    }
                }
            }

            // Remove directory if empty
            if (!$isDryRun && count(glob($tempDir . '/*')) === 0) {
                rmdir($tempDir);
                $stats['dir_removed'] = true;
            } elseif ($isDryRun && count($files) === 0) {
                $stats['dir_removed'] = true;
            }

        } catch (\Exception $e) {
            Log::error('Error cleaning temp directory', [
                'directory' => $tempDir,
                'error' => $e->getMessage()
            ]);
        }

        return $stats;
    }

    /**
     * Display cleanup results
     */
    private function displayResults($stats, $isDryRun)
    {
        $this->newLine();
        
        if ($isDryRun) {
            $this->info('🔍 DRY RUN RESULTS (no changes made):');
        } else {
            $this->info('✅ CLEANUP COMPLETED:');
        }

        $this->table([
            'Metric', 'Count'
        ], [
            ['Sessions ' . ($isDryRun ? 'would be' : '') . ' cleaned', $stats['sessions_cleaned']],
            ['Chunks ' . ($isDryRun ? 'would be' : '') . ' cleaned', $stats['chunks_cleaned']],
            ['Temp directories ' . ($isDryRun ? 'would be' : '') . ' removed', $stats['temp_dirs_removed']],
            ['Temp files ' . ($isDryRun ? 'would be' : '') . ' removed', $stats['temp_files_removed']],
            ['Storage ' . ($isDryRun ? 'would be' : '') . ' freed', $this->formatBytes($stats['bytes_freed'])],
            ['Errors encountered', $stats['errors']],
        ]);

        if ($stats['errors'] > 0) {
            $this->warn("⚠️  {$stats['errors']} errors occurred during cleanup. Check logs for details.");
        }

        // Log the cleanup
        Log::info('Upload cleanup completed', [
            'dry_run' => $isDryRun,
            'stats' => $stats
        ]);
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        
        return $bytes . ' bytes';
    }
}