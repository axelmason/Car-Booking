<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Car extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['name', 'booking_time', 'booking_date', 'seat_price'];
    protected $dates = ['deleted_at'];

    public function seats()
    {
        return $this->hasMany(Seat::class);
    }

}
