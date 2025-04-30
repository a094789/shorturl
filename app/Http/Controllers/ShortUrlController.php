<?php

namespace App\Http\Controllers;

use App\Models\ShortUrl;
use App\Models\UrlClick;
use App\Models\Click;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\LabelAlignment;
use Endroid\QrCode\Label\Font\OpenSans;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Geometry\Factories\RectangleFactory;
use Illuminate\Support\Facades\Log;

class ShortUrlController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except('redirect');
    }

    public function index(Request $request)
    {
        $query = ShortUrl::query();

        // 如果不是管理員，只顯示自己的短網址
        if (!optional(Auth::user())->isAdmin()) {
            $query->where('user_id', Auth::id());
        }

        // 關鍵字篩選
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function($q) use ($keyword) {
                $q->where('original_url', 'like', "%{$keyword}%")
                  ->orWhere('short_code', 'like', "%{$keyword}%");
            });
        }

        // 過期狀態篩選
        if ($request->filled('status')) {
            $status = $request->input('status');
            
            if ($status === 'active') {
                $query->where(function($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
                });
            } elseif ($status === 'expired') {
                $query->where('expires_at', '<', now());
            } elseif ($status === 'permanent') {
                $query->whereNull('expires_at');
            }
        }

        // 日期範圍篩選 - 建立時間
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // 排序功能
        $sortBy = $request->input('sort_by', 'created_at'); // 預設按建立時間排序
        $sortOrder = $request->input('sort_order', 'desc'); // 預設降序排列
        
        // 確保排序欄位是允許的
        $allowedSortFields = ['clicks', 'created_at', 'expires_at'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'created_at';
        }
        
        // 確保排序方向是允許的
        $allowedSortOrders = ['asc', 'desc'];
        if (!in_array($sortOrder, $allowedSortOrders)) {
            $sortOrder = 'desc';
        }
        
        // 應用排序
        $query->orderBy($sortBy, $sortOrder);
        
        // 特殊處理 expires_at 為 null (永久有效) 的排序情況
        if ($sortBy === 'expires_at') {
            if ($sortOrder === 'desc') {
                // 降序時，null 值放在最上面
                $query->orderByRaw('CASE WHEN expires_at IS NULL THEN 0 ELSE 1 END');
            } else {
                // 升序時，null 值放在最下面
                $query->orderByRaw('CASE WHEN expires_at IS NULL THEN 1 ELSE 0 END');
            }
        }

        // 獲取每頁顯示筆數，預設為 10 筆
        $perPage = (int)$request->input('per_page', 10);
        
        // 設定合法的每頁筆數選項
        $validPerPageOptions = [5, 10, 20, 30, 9999];
        
        // 如果不在合法選項中，預設為 10 筆
        if (!in_array($perPage, $validPerPageOptions)) {
            $perPage = 10;
        }
        
        // 分頁並帶上篩選和排序參數
        $urls = $query->paginate($perPage)->appends($request->except('page'));

        return view('short-urls.index', [
            'urls' => $urls, 
            'perPage' => $perPage,
            'keyword' => $request->input('keyword', ''),
            'status' => $request->input('status', ''),
            'date_from' => $request->input('date_from', ''),
            'date_to' => $request->input('date_to', ''),
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder
        ]);
    }

    public function create()
    {
        return view('short-urls.create');
    }

    public function store(Request $request)
    {
        // 基本驗證
        $isAdmin = optional(Auth::user())->isAdmin();
        
        // 驗證規則
        $rules = [
            'original_url' => ['required', 'string', 'max:2048'],
            'expire_type' => ['required', 'string'],
            'custom_expires_at' => ['nullable', 'date', 'after:now'],
        ];
        
        // 根據是否為管理員設置不同的驗證規則
        if ($isAdmin) {
            $rules['expire_type'][] = 'in:1_day,1_week,1_month,permanent,custom';
        } else {
            $rules['expire_type'][] = 'in:1_day,1_week,1_month,custom';
            
            // 對於一般用戶，自訂過期時間不能超過一年
            if ($request->expire_type === 'custom') {
                $rules['custom_expires_at'][] = 'before_or_equal:'.now()->addYear()->format('Y-m-d H:i:s');
            }
        }
        
        // 自訂過期時間在選擇 custom 時是必填的
        if ($request->expire_type === 'custom') {
            $rules['custom_expires_at'][] = 'required';
        }
        
        // 驗證請求
        $this->validate($request, $rules);

        // 進階 URL 驗證
        $url = $request->original_url;
        
        // 1. 確保 URL 有效格式
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['original_url' => '請輸入有效的網址格式，例如：https://example.com']);
        }
        
        // 2. 確保包含通訊協定（加上 http 如果沒有）
        if (!preg_match('/^https?:\/\//i', $url)) {
            $url = 'http://' . $url;
        }
        
        // 3. 解析 URL 並檢查網域名稱格式
        $parsedUrl = parse_url($url);
        if (!isset($parsedUrl['host']) || empty($parsedUrl['host'])) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['original_url' => '無法識別網域名稱，請輸入完整網址']);
        }
        
        // 4. 確保網域名稱不是純數字 IP 或無效格式
        $host = $parsedUrl['host'];
        if (preg_match('/^\d+$/', str_replace('.', '', $host))) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['original_url' => '不接受純數字 IP 格式，請使用網域名稱']);
        }
        
        // 5. 嘗試 DNS 查詢驗證網域名稱是否存在
        if (!checkdnsrr($host, 'A') && !checkdnsrr($host, 'AAAA')) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['original_url' => '網域名稱似乎不存在或無法解析，請確認網址正確性']);
        }
        
        // 6. 檢查是否是本地 IP 地址
        if (filter_var($host, FILTER_VALIDATE_IP) && (
            preg_match('/^(127\.|10\.|172\.(1[6-9]|2[0-9]|3[0-1])\.|192\.168\.)/', $host) || 
            $host === '::1' || 
            $host === 'localhost'
        )) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['original_url' => '不接受本地網址，請使用公開可訪問的網址']);
        }

        // 設定過期時間
        $expiresAt = null;
        switch ($request->expire_type) {
            case '1_day':
                $expiresAt = now()->addDay();
                break;
            case '1_week':
                $expiresAt = now()->addWeek();
                break;
            case '1_month':
                $expiresAt = now()->addMonth();
                break;
            case 'custom':
                $expiresAt = Carbon::parse($request->custom_expires_at);
                
                // 再次檢查非管理員的過期時間不超過一年
                if (!$isAdmin && $expiresAt->gt(now()->addYear())) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['custom_expires_at' => '一般用戶的自訂過期時間不能超過一年']);
                }
                break;
            // permanent 時 $expiresAt 保持為 null
        }

        // 創建短網址
        $shortUrl = ShortUrl::create([
            'original_url' => $url, // 使用經過驗證和可能修改的 URL
            'short_code' => ShortUrl::generateUniqueCode(),
            'user_id' => Auth::id(),
            'expires_at' => $expiresAt,
        ]);

        return redirect()->route('short-urls.index')
            ->with('success', '短網址已成功建立！');
    }

    public function redirect($shortCode)
    {
        // 如果是訪問主域名，則重定向到登入頁面
        if ($shortCode === '' || empty($shortCode)) {
            return redirect()->route('login');
        }

        $shortUrl = ShortUrl::where('short_code', $shortCode)->firstOrFail();

        // 檢查是否過期
        if ($shortUrl->isExpired()) {
            abort(404, '此短網址已過期');
        }

        // 嘗試從 X-Forwarded-For 標頭獲取真實 IP
        $realIpAddress = request()->header('X-Forwarded-For');
        
        // 如果標頭不存在，則使用請求的 IP
        if (!$realIpAddress) {
            $realIpAddress = request()->ip();
        } else {
            // X-Forwarded-For 可能包含多個 IP，取第一個作為客戶端 IP
            $ips = explode(',', $realIpAddress);
            $realIpAddress = trim($ips[0]);
        }
        
        // 記錄點擊 - 同時儲存到新舊兩個表中以確保兼容性
        // 儲存到url_clicks表
        $shortUrl->clicks()->create([
            'ip_address' => $realIpAddress,
            'user_agent' => request()->userAgent(),
            'referer' => request()->header('referer'),
        ]);
        
        // 儲存到clicks表
        Click::create([
            'short_url_id' => $shortUrl->id,
            'ip_address' => $realIpAddress,
            'user_agent' => request()->userAgent(),
            'referer' => request()->header('referer'),
        ]);

        // 更新點擊次數
        $shortUrl->increment('clicks');

        return redirect($shortUrl->original_url);
    }

    public function destroy(ShortUrl $shortUrl)
    {
        if ($shortUrl->user_id !== Auth::id() && !optional(Auth::user())->isAdmin()) {
            abort(403);
        }

        $shortUrl->delete();

        return redirect()->route('short-urls.index')
            ->with('success', '短網址已刪除成功！');
    }

    /**
     * 生成唯一的短網址代碼
     */
    private function generateUniqueCode($length = 6)
    {
        do {
            $code = Str::random($length);
        } while (ShortUrl::where('short_code', $code)->exists());

        return $code;
    }

    public function generateQrCode(Request $request)
    {
        $url = $request->query('url');
        return app('qrcode')->size(200)->generate($url);
    }

    public function showClicks(ShortUrl $shortUrl, Request $request)
    {
        if ($shortUrl->user_id !== Auth::id() && !optional(Auth::user())->isAdmin()) {
            abort(403);
        }

        // 獲取每頁顯示筆數，預設為 10 筆
        $perPage = (int)$request->input('per_page', 10);
        
        // 設定合法的每頁筆數選項
        $validPerPageOptions = [5, 10, 20, 30, 9999];
        
        // 如果不在合法選項中，預設為 10 筆
        if (!in_array($perPage, $validPerPageOptions)) {
            $perPage = 10;
        }

        // 嘗試從clicks表獲取數據
        $clicks = Click::where('short_url_id', $shortUrl->id)
            ->latest()
            ->paginate($perPage)
            ->appends(['per_page' => $perPage]);
            
        // 如果clicks表沒有數據，則從url_clicks表獲取
        if ($clicks->isEmpty()) {
            $clicks = $shortUrl->clicks()
                ->latest()
                ->paginate($perPage)
                ->appends(['per_page' => $perPage]);
        }

        return view('short-urls.clicks', compact('clicks', 'shortUrl', 'perPage'));
    }

    public function qrcode(Request $request, ShortUrl $shortUrl)
    {
        $url = route('short-url.redirect', $shortUrl->short_code);

        $qrResult = Builder::create()
            ->writer(new PngWriter())
            ->data($url)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size(600)
            ->margin(20)
            ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->backgroundColor(new Color(255, 255, 255))
            ->build();

        $manager = new ImageManager(new Driver());
        $qrImage = $manager->read($qrResult->getString());

        $logoOption = $request->input('logo_option', 'default'); // 預設為 default
        $logo = null;

        if ($logoOption === 'custom') {
            if ($request->hasFile('custom_logo')) {
                try {
                    // 從上傳檔案中讀取圖片
                    $logoFile = $request->file('custom_logo');
                    
                    // 特別處理 SVG 格式
                    if (strtolower($logoFile->getClientOriginalExtension()) === 'svg') {
                        // 將 SVG 轉換為 PNG
                        $tempPngPath = tempnam(sys_get_temp_dir(), 'svg_converted_') . '.png';
                        
                        // 使用 Imagick 轉換 SVG 為 PNG（如果可用）
                        if (extension_loaded('imagick')) {
                            try {
                                $imagick = new \Imagick();
                                $imagick->readImageBlob($logoFile->getContent());
                                $imagick->setImageFormat('png');
                                $imagick->writeImage($tempPngPath);
                                $logo = $manager->read($tempPngPath);
                                @unlink($tempPngPath);
                            } catch (\Exception $e) {
                                Log::error('SVG conversion error: ' . $e->getMessage());
                                // 如果無法轉換，使用預設 logo
                                $logoPath = public_path('images/logo.png');
                                if (file_exists($logoPath)) {
                                    $logo = $manager->read($logoPath);
                                }
                            }
                        } else {
                            // 如果無法使用 Imagick，使用預設 logo
                            $logoPath = public_path('images/logo.png');
                            if (file_exists($logoPath)) {
                                $logo = $manager->read($logoPath);
                            }
                            Log::warning('Imagick extension not available for SVG conversion');
                        }
                    } else {
                        // 處理其他格式的圖片
                        $logo = $manager->read($logoFile->getContent());
                    }
                } catch (\Exception $e) {
                    // 忽略錯誤，避免破壞整體流程
                    Log::error('Custom logo error: ' . $e->getMessage());
                }
            }
        } elseif ($logoOption === 'default') {
            $logoPath = public_path('images/logo.png');
            if (file_exists($logoPath)) {
                $logo = $manager->read($logoPath);
            }
        }

        if ($logo) {
            // 增加 Logo 尺寸，以便更好地覆蓋底部點陣圖
            $logoSize = intval($qrImage->width() * 0.3);
            $logo = $logo->scale(width: $logoSize);

            $x = intval(($qrImage->width() - $logo->width()) / 2);
            $y = intval(($qrImage->height() - $logo->height()) / 2);

            // 增加白色背景區塊的填充，使其更大
            $padding = 18;
            $qrImage->drawRectangle($x - $padding / 2, $y - $padding / 2, function (RectangleFactory $rectangle) use ($logo, $padding) {
                $rectangle->size($logo->width() + $padding, $logo->height() + $padding);
                $rectangle->background('white');
            });

            // 合成 logo 到中央
            $qrImage->place($logo, 'center');
        }

        return response($qrImage->toPng())->header('Content-Type', 'image/png');
    }

    public function downloadQrCode(Request $request, ShortUrl $shortUrl)
    {
        try {
            $url = route('short-url.redirect', $shortUrl->short_code);

            $qrResult = Builder::create()
                ->writer(new PngWriter())
                ->data($url)
                ->encoding(new Encoding('UTF-8'))
                ->errorCorrectionLevel(ErrorCorrectionLevel::High)
                ->size(600) // 使用較大尺寸以提高品質
                ->margin(20)
                ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
                ->backgroundColor(new Color(255, 255, 255))
                ->build();

            $manager = new ImageManager(new Driver());
            $qrImage = $manager->read($qrResult->getString());

            $logoOption = $request->input('logo_option', 'default'); // 預設為 default
            $logo = null;

            if ($logoOption === 'custom') {
                // 檢查是否為 POST 請求且有文件上傳
                if ($request->isMethod('POST') && $request->hasFile('custom_logo')) {
                    try {
                        // 檢查檔案大小
                        $logoFile = $request->file('custom_logo');
                        if ($logoFile->getSize() > 1024 * 1024) { // 1MB
                            return response()->json(['error' => '圖片檔案過大，請選擇小於 1MB 的圖片'], 422);
                        }
                        
                        // 特別處理 SVG 格式
                        if (strtolower($logoFile->getClientOriginalExtension()) === 'svg') {
                            // 將 SVG 轉換為 PNG
                            $tempPngPath = tempnam(sys_get_temp_dir(), 'svg_converted_') . '.png';
                            
                            // 使用 Imagick 轉換 SVG 為 PNG（如果可用）
                            if (extension_loaded('imagick')) {
                                try {
                                    $imagick = new \Imagick();
                                    $imagick->readImageBlob($logoFile->getContent());
                                    $imagick->setImageFormat('png');
                                    $imagick->writeImage($tempPngPath);
                                    $logo = $manager->read($tempPngPath);
                                    @unlink($tempPngPath);
                                } catch (\Exception $e) {
                                    Log::error('SVG conversion error: ' . $e->getMessage());
                                    return response()->json(['error' => '無法處理 SVG 格式的圖片，請使用 PNG 或 JPG 格式'], 422);
                                }
                            } else {
                                Log::warning('Imagick extension not available for SVG conversion');
                                return response()->json(['error' => '伺服器不支持 SVG 格式的圖片，請使用 PNG 或 JPG 格式'], 422);
                            }
                        } else {
                            // 從上傳檔案中讀取圖片
                            $logo = $manager->read($logoFile->getContent());
                        }
                        
                        // 驗證是否為有效的圖片
                        if (!$logo) {
                            return response()->json(['error' => '無法讀取上傳的圖片，請確保是有效的圖片格式'], 422);
                        }
                    } catch (\Exception $e) {
                        Log::error('QR code logo error: ' . $e->getMessage());
                        return response()->json(['error' => '處理自訂logo時發生錯誤: ' . $e->getMessage()], 500);
                    }
                } else if ($request->isMethod('GET')) {
                    // 對於 GET 請求，自訂 logo 無法直接獲取，返回錯誤
                    return redirect()->back()->with('error', '無法下載帶有自訂 logo 的 QR 碼，請使用下載按鈕');
                } else {
                    // 沒有上傳檔案但選擇了自訂logo
                    return response()->json(['error' => '請上傳自訂logo圖片'], 422);
                }
            } elseif ($logoOption === 'default') {
                $logoPath = public_path('images/logo.png');
                if (file_exists($logoPath)) {
                    try {
                        $logo = $manager->read($logoPath);
                    } catch (\Exception $e) {
                        Log::error('Default logo read error: ' . $e->getMessage());
                        // 使用預設logo失敗時，繼續而不添加logo
                    }
                }
            }
            // 如果 logoOption 是 'none' 或其他值，則不添加 logo

            if ($logo) {
                // 增加 Logo 尺寸，以便更好地覆蓋底部點陣圖
                $logoSize = intval($qrImage->width() * 0.3);
                $logo = $logo->scale(width: $logoSize);

                $x = intval(($qrImage->width() - $logo->width()) / 2);
                $y = intval(($qrImage->height() - $logo->height()) / 2);

                // 增加白色背景區塊的填充，使其更大
                $padding = 18;
                $qrImage->drawRectangle($x - $padding / 2, $y - $padding / 2, function (RectangleFactory $rectangle) use ($logo, $padding) {
                    $rectangle->size($logo->width() + $padding, $logo->height() + $padding);
                    $rectangle->background('white');
                });

                // 合成 logo 到中央
                $qrImage->place($logo, 'center');
            }

            // 若非AJAX請求，使用傳統下載方式
            if (!$request->ajax() && $request->isMethod('GET')) {
                return response($qrImage->toPng())
                    ->header('Content-Type', 'image/png')
                    ->header('Content-Disposition', 'attachment; filename="qrcode_' . $shortUrl->short_code . '.png"');
            }
            
            // 若是AJAX請求，正常返回圖片內容
            return response($qrImage->toPng())
                ->header('Content-Type', 'image/png');
                
        } catch (\Exception $e) {
            Log::error('QR code generation error: ' . $e->getMessage());
            
            // 若是AJAX請求，返回JSON錯誤
            if ($request->ajax() || $request->isMethod('POST')) {
                return response()->json(['error' => '生成QR碼時發生錯誤: ' . $e->getMessage()], 500);
            }
            
            // 否則重定向回上一頁
            return redirect()->back()->with('error', '生成QR碼時發生錯誤，請稍後再試');
        }
    }
}
