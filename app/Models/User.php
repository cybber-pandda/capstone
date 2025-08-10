<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\OTPVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'profile',
        'username',
        'email_verified_at',
        'email',
        'password',
        'role',
        'otp_code',
        'otp_expire',
        'credit_limit'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public static function getCurrentUser()
    {
        return auth()->user();
    }

    public function sendEmailVerificationNotification()
    {

        if ($this->otp_code && $this->otp_expire > now()) {
            // OTP is already set and valid, skip generating a new one
            return;
        }

        // Generate a unique OTP code
        $otpCode = rand(100000, 999999);

        // Ensure the OTP code is unique
        while (User::where('otp_code', $otpCode)->exists()) {
            $otpCode = rand(100000, 999999);
        }

        $this->otp_code = $otpCode;
        $this->otp_expire = now()->addMinutes(10);
        $this->save();

        // Send the OTP notification
        $this->notify(new OTPVerifyEmail($otpCode));
    }

    public function purchaseRequests()
    {
        return $this->hasMany(PurchaseRequest::class, 'customer_id');
    }

    public function userLog()
    {
        return $this->hasMany(UserLog::class);
    }
}
