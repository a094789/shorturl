<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => [
                'required', 
                'string', 
                'min:2',
                'regex:/^[\x{4e00}-\x{9fa5}a-zA-Z\-]+$/u', // 只允許中文、英文和連字符
                function ($attribute, $value, $fail) {
                    // 中文字符計數
                    $chineseCount = preg_match_all('/[\x{4e00}-\x{9fa5}]/u', $value);
                    // 英文字符計數
                    $englishCount = preg_match_all('/[a-zA-Z]/', $value);
                    
                    if ($chineseCount > 10) {
                        $fail('中文字符不能超過10個');
                    }
                    
                    if ($englishCount > 20) {
                        $fail('英文字符不能超過20個');
                    }
                },
            ],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => [
                'required', 
                'confirmed', 
                'min:6',
                'max:50',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
                Rules\Password::defaults()
            ],
            'g-recaptcha-response' => ['required', 'recaptcha'],
        ], [
            'name.regex' => '名字只能包含中文、英文和連字符(-)。',
            'name.min' => '名字至少需要2個字元。',
            'password.regex' => '密碼必須包含至少一個小寫字母、一個大寫字母和一個數字。',
            'g-recaptcha-response.required' => '請完成人機驗證。',
            'g-recaptcha-response.recaptcha' => '人機驗證失敗，請重試。',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
