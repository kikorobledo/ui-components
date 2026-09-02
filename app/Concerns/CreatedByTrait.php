<?php

namespace App\Concerns;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;

trait CreatedByTrait
{

    public function createdBy(){
        return $this->belongsTo(User::class, 'created_by');
    }

    protected function createdAtFormatted(): Attribute
    {
       return Attribute::make(
            get: fn ($value, $attributes) => Carbon::parse($attributes['created_at'])->format('d-m-Y H:i:s'),
        );
    }

}
