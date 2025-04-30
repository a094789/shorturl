<?php

namespace App\Console\Commands;

use App\Models\ShortUrl;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateTimezoneCommand extends Command
{
    protected $signature = 'timezone:update';
    protected $description = '將資料庫中的時間從 UTC 轉換為台北時間';

    public function handle()
    {
        $this->info('開始更新時間...');

        $urls = ShortUrl::all();
        $count = 0;

        foreach ($urls as $url) {
            // 更新建立時間
            if ($url->created_at) {
                $url->created_at = Carbon::parse($url->created_at)->addHours(8);
            }

            // 更新過期時間
            if ($url->expires_at) {
                $url->expires_at = Carbon::parse($url->expires_at)->addHours(8);
            }

            // 更新更新時間
            if ($url->updated_at) {
                $url->updated_at = Carbon::parse($url->updated_at)->addHours(8);
            }

            $url->save();
            $count++;
        }

        $this->info("完成更新！共更新了 {$count} 筆記錄。");
    }
} 