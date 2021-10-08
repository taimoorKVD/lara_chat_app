<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageGroupMember extends Model
{
    protected $fiilable = ['user_id', 'message_group_id', 'status'];

    public function message_group() {
        return $this->belongsTo(MessageGroup::class);
    }
}
