<?php

namespace App\Models;

use App\Models\Phase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{

    protected $guarded = ['id'];

    public function phase():BelongsTo
    {
        return $this->belongsTo(Phase::class);
    }

}
