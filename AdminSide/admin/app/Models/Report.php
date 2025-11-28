<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $primaryKey = 'report_id';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'report_type',
        'location_id',
        'assigned_station_id',
        'status',
        'is_anonymous',
        'date_reported',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'date_reported' => 'datetime',
    ];

    /**
     * Get the user that created the report
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the location of the report
     */
    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id', 'location_id');
    }

    /**
     * Get the police station assigned to handle this report
     */
    public function policeStation()
    {
        return $this->belongsTo(PoliceStation::class, 'assigned_station_id', 'station_id');
    }

    /**
     * Get the messages related to this report
     */
    public function messages()
    {
        return $this->hasMany(Message::class, 'report_id');
    }

    /**
     * Get the media files for this report
     */
    public function media()
    {
        return $this->hasMany(ReportMedia::class, 'report_id', 'report_id');
    }
}