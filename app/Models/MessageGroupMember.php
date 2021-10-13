<?php

namespace App\Models;

use App\User;

use Illuminate\Database\Eloquent\Model;

class MessageGroupMember extends Model
{
    protected $table = "message_group_member";
    
    protected $guarded = [];

    public function message_group() {
        return $this->belongsTo(MessageGroup::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
