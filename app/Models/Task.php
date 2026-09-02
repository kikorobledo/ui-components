<?php

namespace App\Models;

use App\Concerns\CreatedByTrait;
use App\Concerns\UpdatedByTrait;
use App\Models\Phase;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{

    use CreatedByTrait;
    use UpdatedByTrait;

    protected $guarded = ['id'];

    public function phase():BelongsTo
    {
        return $this->belongsTo(Phase::class);
    }

    public function updatedBy():BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

}
