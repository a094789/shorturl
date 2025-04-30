<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * 鎖定時間（秒）
     */
    private const LOCKOUT_SECONDS = 86400; // 24小時

    /**
     * 最大嘗試次數
     */
    private const MAX_ATTEMPTS = 5; // 改回5次

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * 取得驗證錯誤訊息
     *
     * @return array
     */
    public function messages()
    {
        $throttleKey = $this->throttleKey();
        $remainingAttempts = self::MAX_ATTEMPTS - RateLimiter::attempts($throttleKey);
        $remainingText = $remainingAttempts > 0 
            ? ' ' . trans('auth.remaining_attempts', ['attempts' => $remainingAttempts])
            : '';
        
        return [
            'password.required' => trans('validation.required', ['attribute' => '密碼']) . $remainingText,
            'email.required' => trans('validation.required', ['attribute' => '電子郵件']),
            'email.email' => trans('validation.email', ['attribute' => '電子郵件']),
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $throttleKey = $this->throttleKey();
        
        // 首先檢查是否被鎖定
        if ($this->hasTooManyLoginAttempts()) {
            event(new Lockout($this));
            $this->throwLockoutException();
        }

        // 檢查憑證是否正確
        $credentialsValid = Auth::attempt($this->only('email', 'password'), $this->boolean('remember'));
        
        if (!$credentialsValid) {
            // 增加失敗嘗試次數
            RateLimiter::hit($throttleKey, self::LOCKOUT_SECONDS);
            
            // 獲取剩餘嘗試次數
            $remainingAttempts = self::MAX_ATTEMPTS - RateLimiter::attempts($throttleKey);
            
            // 如果剩餘嘗試次數小於等於0，表示已達到最大嘗試次數，拋出鎖定異常
            if ($remainingAttempts <= 0) {
                event(new Lockout($this));
                $this->throwLockoutException();
            }
            
            // 否則顯示剩餘嘗試次數
            $message = trans('auth.password');
            if ($remainingAttempts > 0) {
                $message .= ' ' . trans('auth.remaining_attempts', ['attempts' => $remainingAttempts]);
            }

            throw ValidationException::withMessages([
                'email' => $message,
            ]);
        }

        // 登入成功，清除嘗試記錄
        RateLimiter::clear($throttleKey);
    }

    /**
     * 檢查是否有太多登入嘗試
     */
    private function hasTooManyLoginAttempts(): bool
    {
        return RateLimiter::tooManyAttempts(
            $this->throttleKey(), 
            self::MAX_ATTEMPTS
        );
    }

    /**
     * 拋出鎖定異常
     */
    private function throwLockoutException(): void
    {
        $seconds = self::LOCKOUT_SECONDS;
        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
                'hours' => ceil($seconds / 3600),
            ]),
        ]);
    }

    /**
     * 確認登入請求未被限制
     * 
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (!$this->hasTooManyLoginAttempts()) {
            return;
        }

        event(new Lockout($this));
        $this->throwLockoutException();
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
