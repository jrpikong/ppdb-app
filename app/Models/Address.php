<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'registration_id',
        'province',
        'province_code',
        'regency',
        'regency_code',
        'district',
        'district_code',
        'village',
        'village_code',
        'street_address',
        'rt',
        'rw',
        'postal_code',
        'coordinates',
    ];

    /**
     * Get the registration that owns this address.
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    /**
     * Get full address as single string.
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->street_address,
            $this->rt ? "RT {$this->rt}" : null,
            $this->rw ? "RW {$this->rw}" : null,
            $this->village,
            $this->district,
            $this->regency,
            $this->province,
            $this->postal_code,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get coordinates as array [lat, lng].
     */
    public function getCoordinatesArrayAttribute(): ?array
    {
        if (!$this->coordinates) {
            return null;
        }

        $coords = explode(',', $this->coordinates);
        return [
            'lat' => (float) ($coords[0] ?? 0),
            'lng' => (float) ($coords[1] ?? 0),
        ];
    }

    /**
     * Set coordinates from array.
     */
    public function setCoordinatesFromArray(float $lat, float $lng): void
    {
        $this->coordinates = "{$lat},{$lng}";
    }
}
