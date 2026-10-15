<?php

namespace App\Enums;

/**
 * Vendor categories (PRD §9.2):
 * - Accommodation: Hotel, Villa, Homestay, Resort, Guesthouse.
 * - Vehicle Rental: Car, Motorbike, Van with Driver.
 * - Activity: Snorkeling, Diving, Rafting, Hiking, Boat.
 * - Travel Support: Photographer, Equipment Rental, Catering.
 */
enum VendorCategory: string
{
    case Accommodation = 'accommodation';
    case VehicleRental = 'vehicle_rental';
    case Activity = 'activity';
    case TravelSupport = 'travel_support';

    public function label(string $locale = 'id'): string
    {
        $labels = [
            'id' => [
                'accommodation' => 'Penginapan & Villa',
                'vehicle_rental' => 'Rental Kendaraan',
                'activity' => 'Aktivitas & Petualangan',
                'travel_support' => 'Dukungan Perjalanan & Foto',
            ],
            'en' => [
                'accommodation' => 'Accommodation & Villas',
                'vehicle_rental' => 'Vehicle Rental',
                'activity' => 'Activities & Adventures',
                'travel_support' => 'Travel Support & Photo',
            ],
        ];

        return $labels[$locale][$this->value] ?? $this->value;
    }

    public function icon(): string
    {
        return match ($this) {
            self::Accommodation => '🏨',
            self::VehicleRental => '🚗',
            self::Activity => '🤿',
            self::TravelSupport => '📷',
        };
    }

    public function defaultRadiusKm(): float
    {
        return match ($this) {
            self::Accommodation => 8.0,
            self::VehicleRental => 15.0,
            self::Activity => 12.0,
            self::TravelSupport => 10.0,
        };
    }
}
