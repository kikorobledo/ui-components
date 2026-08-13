<?php

namespace App\Models;

use App\Concerns\CreatedByTrait;
use App\Models\Phase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{

    protected $guarded = ['id'];

    use CreatedByTrait;

    public function phases():HasMany
    {
        return $this->hasMany(Phase::class);
    }

}
