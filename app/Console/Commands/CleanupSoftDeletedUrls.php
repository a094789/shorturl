<?php

namespace App\Console\Commands;

use App\Models\ShortUrl;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CleanupSoftDeletedUrls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'urls:cleanup-deleted';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '永久刪除已超過一週的軟刪除短網址';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('開始清理已軟刪除的短網址...');

        // 查找所有需要清理的短網址
        $urlsToDelete = ShortUrl::onlyTrashed()
            ->where('auto_cleanup_at', '<=', Carbon::now())
            ->get();

        $count = 0;
        foreach ($urlsToDelete as $url) {
            // 永久刪除記錄
            $url->forceDelete();
            $count++;
        }

        $this->info("清理完成！共永久刪除了 {$count} 個短網址。");
    }
} 