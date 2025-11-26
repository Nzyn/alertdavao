<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barangay extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'station_id',
    ];

    /**
     * Get the police station assigned to this barangay
     */
    public function policeStation()
    {
        return $this->belongsTo(PoliceStation::class, 'station_id', 'station_id');
    }

    /**
     * Get locations within this barangay
     */
    public function locations()
    {
        return $this->hasMany(Location::class, 'barangay_id');
    }
}
