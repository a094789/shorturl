<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;

class ShortUrl extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'original_url',
        'short_code',
        'user_id',
        'expires_at',
        'auto_cleanup_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'auto_cleanup_at' => 'datetime'
    ];

    /**
     * Get the user that owns the short URL.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the clicks for the short URL.
     */
    public function clicks()
    {
        return $this->hasMany(UrlClick::class);
    }

    /**
     * Increment the click count.
     */
    public function incrementClicks()
    {
        $this->increment('clicks');
    }

    /**
     * Check if the short URL is expired.
     */
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Get the short URL.
     */
    public function getShortUrlAttribute()
    {
        return url("/s/{$this->short_code}");
    }

    /**
     * Get the QR code.
     */
    public function getQrCodeAttribute()
    {
        return QrCode::size(200)->generate($this->short_url);
    }

    /**
     * Generate a unique short code.
     */
    public static function generateUniqueCode($length = 6)
    {
        // 定義字元集（排除易混淆字元）
        $characters = '23456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
        
        // 敏感字詞黑名單
        $blacklist = ['sex', 'xxx', 'fuck', 'shit', 'dick', 'porn'];

        do {
            // 生成隨機代碼
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= $characters[random_int(0, strlen($characters) - 1)];
            }

            // 檢查是否包含敏感字詞
            $containsBlacklist = false;
            foreach ($blacklist as $word) {
                if (stripos($code, $word) !== false) {
                    $containsBlacklist = true;
                    break;
                }
            }

            // 檢查是否存在於資料庫中（考慮過期時間）
            $exists = static::where('short_code', $code)
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                })
                ->exists();

        } while ($containsBlacklist || $exists);

        return $code;
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($shortUrl) {
            // 驗證原始URL是否是有效URL
            if (!filter_var($shortUrl->original_url, FILTER_VALIDATE_URL)) {
                throw new \Exception('無效的URL格式');
            }
            
            // 檢查URL是否包含危險協議
            $disallowedProtocols = ['javascript:', 'data:', 'vbscript:', 'file:'];
            $lowerUrl = strtolower($shortUrl->original_url);
            foreach ($disallowedProtocols as $protocol) {
                if (strpos($lowerUrl, $protocol) === 0) {
                    throw new \Exception('不允許的URL協議');
                }
            }
        });

        static::deleting(function ($shortUrl) {
            // 只在軟刪除時設置自動清理時間
            if (!$shortUrl->isForceDeleting()) {
                $shortUrl->auto_cleanup_at = Carbon::now()->addWeek();
                $shortUrl->save();
            }
        });
    }

    /**
     * 檢查是否可以復原刪除
     */
    public function canRestore()
    {
        return $this->auto_cleanup_at && $this->auto_cleanup_at->isFuture();
    }
} 