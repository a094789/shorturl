<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * 添加安全相關標頭到所有響應
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // 內容安全策略 (CSP)
        $response->headers->set('Content-Security-Policy', 
            "default-src 'self'; " .
            "script-src 'self' https://cdn.jsdelivr.net https://www.google.com https://www.gstatic.com 'unsafe-inline'; " .
            "style-src 'self' https://cdn.jsdelivr.net 'unsafe-inline'; " .
            "img-src 'self' data: https:; " .
            "font-src 'self' https://cdn.jsdelivr.net; " .
            "connect-src 'self'; " .
            "frame-src https://www.google.com; " .
            "object-src 'none'; " .
            "base-uri 'self'; " .
            "form-action 'self';"
        );
        
        // 阻止點擊劫持
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        
        // 防止MIME類型嗅探攻擊
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        
        // 啟用XSS過濾器
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        
        // 啟用 HSTS
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        
        // 阻止瀏覽器顯示敏感信息
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        // 禁止將網站內容嵌入到第三方場景中（可選）
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        
        return $response;
    }
} 