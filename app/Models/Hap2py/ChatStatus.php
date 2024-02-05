<?php

namespace App\Models\Hap2py;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatStatus extends Model
{
    use HasFactory;

    protected $table = '_chat_status';

    protected $connection = 'hap2py';
}
