<?php

namespace App\Models\Hap2py;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CmsUser extends Model
{
    use HasFactory;

    protected $connection = 'hap2py';

    /* -------------------------------------------------------------------------- */
    /*                                Relationships                               */
    /* -------------------------------------------------------------------------- */
    public function chatStatus(): BelongsTo
    {
        return $this->belongsTo(ChatStatus::class, 'chat_status');
    }
}
