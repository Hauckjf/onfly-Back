<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\TripStatus;

class Trip extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'destination',
        'startDate',
        'endDate',
        'status'
    ];

    protected $casts = [
        'startDate' => 'datetime:Y-m-d\TH:i',
        'endDate' => 'datetime:Y-m-d\TH:i',
        'status' => TripStatus::class
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
