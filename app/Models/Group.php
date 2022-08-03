<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Group extends Model
{
    use HasFactory;

    protected $table = 'group';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'group_name', 'group_image', 'status_id', 'created_by',
    ];
    
    public function users()
    {
        return $this->belongsToMany(User::class, 'groupmember', 'group_id', 'user_id');
    }
}
