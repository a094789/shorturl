<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ShortUrl;
use App\Models\Click;
use App\Models\UrlClick;
use Carbon\Carbon;

class RecycleExpiredShortUrls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shorturl:recycle {--days=30 : 回收超過指定天數的過期短網址}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '回收已過期的短網址編碼，以便重新利用';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        
        $expiredDate = Carbon::now()->subDays($days);
        
        // 獲取符合條件的過期短網址：
        // 1. 已過期
        // 2. 過期時間超過指定天數
        $expiredUrls = ShortUrl::where('expires_at', '<', Carbon::now())
            ->where('expires_at', '<', $expiredDate)
            ->get();
            
        $count = $expiredUrls->count();
        
        if ($count === 0) {
            $this->info('沒有找到符合條件的過期短網址。');
            return;
        }
        
        $this->info("找到 $count 個過期短網址。");
        
        $bar = $this->output->createProgressBar($count);
        $bar->start();
        
        $recycledCount = 0;
        $failedCount = 0;
        
        foreach ($expiredUrls as $url) {
            try {
                // 先刪除相關的點擊記錄
                Click::where('short_url_id', $url->id)->delete();
                UrlClick::where('short_url_id', $url->id)->delete();
                
                // 然後刪除短網址記錄
                $url->delete();
                
                $recycledCount++;
            } catch (\Exception $e) {
                $this->error("處理短網址 ID: {$url->id} 時發生錯誤：{$e->getMessage()}");
                $failedCount++;
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        
        $this->info("操作完成！成功回收 $recycledCount 個短網址編碼，失敗 $failedCount 個。");
        
        // 可選：使短碼有效性檢查更有效率
        if ($recycledCount > 0) {
            $this->call('cache:clear');
            $this->info('已清除快取以確保短碼可立即重用。');
        }
    }
} 