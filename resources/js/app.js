import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// 註冊表單名字欄位驗證
document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.querySelector('form[action="/register"]');
    if (registerForm) {
        const nameInput = registerForm.querySelector('#name');
        
        nameInput.addEventListener('input', function() {
            const value = this.value;
            const regex = /^[\u4e00-\u9fa5a-zA-Z\-]+$/;
            
            // 計算中文和英文字符數量
            const chineseChars = value.match(/[\u4e00-\u9fa5]/g) || [];
            const englishChars = value.match(/[a-zA-Z]/g) || [];
            const chineseCount = chineseChars.length;
            const englishCount = englishChars.length;
            
            if (value.length > 0 && !regex.test(value)) {
                this.setCustomValidity('名字只能包含中文、英文和連字符(-)');
            } else if (value.length > 0 && value.length < 2) {
                this.setCustomValidity('名字至少需要2個字元');
            } else if (chineseCount > 10) {
                this.setCustomValidity('中文字符不能超過10個');
            } else if (englishCount > 20) {
                this.setCustomValidity('英文字符不能超過20個');
            } else {
                this.setCustomValidity('');
            }
        });
    }
});

// 註冊表單密碼欄位驗證
document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.querySelector('form[action="/register"]');
    if (registerForm) {
        const passwordInput = registerForm.querySelector('#password');
        
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                const value = this.value;
                const hasLowercase = /[a-z]/.test(value);
                const hasUppercase = /[A-Z]/.test(value);
                const hasNumber = /\d/.test(value);
                
                if (value.length > 0 && value.length < 6) {
                    this.setCustomValidity('密碼至少需要6個字元');
                } else if (value.length > 50) {
                    this.setCustomValidity('密碼不能超過50個字元');
                } else if (value.length >= 6 && (!hasLowercase || !hasUppercase || !hasNumber)) {
                    this.setCustomValidity('密碼必須包含至少一個小寫字母、一個大寫字母和一個數字');
                } else {
                    this.setCustomValidity('');
                }
            });
        }
    }
});
