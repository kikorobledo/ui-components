<?php

namespace App\Concerns;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;

trait CreatedByTrait
{

    public function createdPor(){
        return $this->belongsTo(User::class, 'created_by');
    }

    protected function createdAt(): Attribute
    {
        return Attribute::make(
            get: fn (Carbon $value) => $value->format('d-m-Y'),
        );
    }

}
