<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderHour extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'captain_id',
        'trip_type_id',
        'order_code',
        'total_price',
        'lat_user',
        'long_user',
        'status',
        'payments',
        'chat_id',
        'address_now',
        'data',
        'hours_from',
        'hour_id',
        'commit',
        'date_created',
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function captain()
    {
        return $this->belongsTo(Captain::class,'captain_id');
    }

    public function trip_type()
    {
        return $this->belongsTo(TripType::class,'trip_type_id');
    }
    public function hour()
    {
        return $this->belongsTo(Hour::class,'hour_id');
    }

    public function scopeByCaptain($query, $captainId)
    {
        return $query->where('captain_id', $captainId);
    }
}
