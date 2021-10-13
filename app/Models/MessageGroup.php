<?php

namespace App\Models;

use App\User;

use Illuminate\Database\Eloquent\Model;

class MessageGroup extends Model
{
    protected $table = 'message_group';
    
    protected $guarded = [];

    public function message_group_member() {
        return $this->hasMany(MessageGroupMember::class);
    }

    public function user_messages() {
        return $this->hasMany(UserMessage::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class,'user_id');
    }
}
