<?php

namespace App\Models;

use App\Models\City;
use App\Models\Country;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class State extends Model
{
    use HasFactory;
    
    protected $fillable =[
        'name', 'country_id',
    ];

    public function country(){
        return $this->belongsTo(Country::class);
    }

    public function employees(): HasMany{
        return $this->hasMany(Employee::class);
    }

    public function cities(): HasMany{
        return $this->hasMany(City::class);
    }
}

