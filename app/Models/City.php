<?php

namespace App\Models;

use App\Models\State;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'state_id', 'name'
    ];

    public function state(){
        return $this->belongsTo(State::class);
    }

    public function employees(): HasMany{
        return $this->hasMany(Employee::class);
    }

    protected static function boot()
    {
        parent::boot();

        // Apply a global scope to limit the number of records
        static::addGlobalScope('limit', function (Builder $builder) {
            $builder->limit(50); // Limit to 50 records
        });
    }
}
