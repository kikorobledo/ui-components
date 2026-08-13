<?php

namespace App\Models;

use App\Models\Group;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Phase extends Model
{

    protected $guarded = ['id'];

    public function group():BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

}
