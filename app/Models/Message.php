<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $table = 'message';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'message_from', 'message_to', 'message_body', 'message_attachment', 'message_originalname', 'message_seen', 'status_id', 'message_quoteid', 'message_quotebody', 'message_quoteuser',
    ];

    public function user()
    {
    	return $this->belongsTo(User::class);
    }
}
