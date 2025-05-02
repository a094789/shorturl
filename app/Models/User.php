<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'name_id',
        'email',
        'password',
        'department_id',
        'role_id',
        'is_admin'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function shortUrls()
    {
        return $this->hasMany(ShortUrl::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function isAdmin(): bool
    {
        return $this->is_admin === true;
    }

    public function hasPermission(string $permission): bool
    {
        // 如果是系統管理員，擁有所有權限
        if ($this->isAdmin()) {
            return true;
        }

        // 如果沒有角色，沒有任何權限
        if (!$this->role) {
            return false;
        }

        // 檢查角色的權限
        $permissions = json_decode($this->role->permissions, true) ?? [];
        return in_array($permission, $permissions);
    }

    public function isInDepartment(?int $departmentId): bool
    {
        return $this->department_id === $departmentId;
    }
}
