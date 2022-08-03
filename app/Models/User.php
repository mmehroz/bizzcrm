<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\GroupMember;

class User// extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $guard_name = 'api';
    protected $table = 'user';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_name', 'user_email', 'user_officenumberext', 'user_phonenumber', 'user_username', 'user_target', 'user_targetmonth', 'user_password', 'user_picture', 'user_loginstatus', 'status_id', 'updated_by',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'user_password', 
        //'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        // 'email_verified_at' => 'datetime',
    ];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
        public function groups()
    {
        return $this->belongsToMany(GroupMember::class, 'groupmember', 'user_id', 'group_id');
    }
}
