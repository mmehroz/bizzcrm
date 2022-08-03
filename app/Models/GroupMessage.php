<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupMessage extends Model
{
    use HasFactory;

    protected $table = 'groupmessage';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'group_id', 'groupmessage_body', 'groupmessage_attachment', 'groupmessage_originalname', 'status_id', 'groupmessage_quoteid', 'groupmessage_quotebody', 'groupmessage_quoteuser',
    ];
}
