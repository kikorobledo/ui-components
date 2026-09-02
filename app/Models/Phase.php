<?php

namespace App\Models;

use App\Models\Group;
use App\Models\Task;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Phase extends Model
{

    protected $guarded = ['id'];

    public function group():BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function tasks():HasMany
    {
        return $this->hasmany(Task::class);
    }

}
