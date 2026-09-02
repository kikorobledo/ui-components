<?php

namespace App\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

trait UpdatedByTrait
{

    public function updatedBy(){
        return $this->belongsTo(User::class, 'updated_by');
    }

    protected function updatedAtFormatted(): Attribute
    {
       return Attribute::make(
            get: fn ($value, $attributes) => Carbon::parse($attributes['updated_at'])->format('d-m-Y H:i:s'),
        );
    }

}
