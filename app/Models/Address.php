<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $table = 'user_addresses';

    protected $fillable = [
        'user_id', 'label', 'recipient_name', 'phone',
        'address_line1', 'address_line2', 'city', 'state', 'postal_code', 'country',
        'is_default', 'address_type', 'latitude', 'longitude'
    ];

    protected $appends = ['building', 'apartment', 'notes', 'type', 'title', 'street', 'building_no'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getBuildingAttribute()
    {
        $data = json_decode($this->address_line2, true);
        return is_array($data) ? ($data['building'] ?? '') : '';
    }

    public function getApartmentAttribute()
    {
        $data = json_decode($this->address_line2, true);
        return is_array($data) ? ($data['apartment'] ?? '') : '';
    }

    public function getNotesAttribute()
    {
        $data = json_decode($this->address_line2, true);
        return is_array($data) ? ($data['notes'] ?? '') : ($this->address_line2 ?? '');
    }

    public function getTypeAttribute()
    {
        $data = json_decode($this->address_line2, true);
        return is_array($data) ? ($data['type'] ?? 'home') : 'home';
    }

    public function getTitleAttribute()
    {
        return $this->label;
    }

    public function getStreetAttribute()
    {
        return $this->address_line1;
    }

    public function getBuildingNoAttribute()
    {
        return $this->building;
    }

    public function getStateIdAttribute()
    {
        $data = json_decode($this->address_line2, true);
        return is_array($data) ? ($data['state_id'] ?? null) : null;
    }

    public function getCityIdAttribute()
    {
        $data = json_decode($this->address_line2, true);
        return is_array($data) ? ($data['city_id'] ?? null) : null;
    }

    public function getAreaIdAttribute()
    {
        $data = json_decode($this->address_line2, true);
        return is_array($data) ? ($data['area_id'] ?? null) : null;
    }

    public function getCityNameAttribute()
    {
        $cityId = $this->city_id;
        if ($cityId) {
            $city = \App\Models\City::find($cityId);
            return $city ? ($city->name_ar ?? $city->name_en) : $this->city;
        }
        return $this->city;
    }

    public function getStateNameAttribute()
    {
        $stateId = $this->state_id;
        if ($stateId) {
            $state = \App\Models\State::find($stateId);
            return $state ? ($state->name_ar ?? $state->name_en) : $this->state;
        }
        return $this->state;
    }
}
