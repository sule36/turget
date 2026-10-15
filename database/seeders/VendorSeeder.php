<?php

namespace Database\Seeders;

use App\Enums\ListingStatus;
use App\Enums\VendorCategory;
use App\Enums\VendorVerificationStatus;
use App\Models\AdministrativeRegion;
use App\Models\Country;
use App\Models\Destination;
use App\Models\TourPackage;
use App\Models\User;
use App\Models\VendorProduct;
use App\Models\VendorProfile;
use App\Models\VendorVerification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        $indonesia = Country::where('code', 'ID')->first() ?? Country::first();
        $admin = User::where('email', 'admin@turget.id')->first();

        // 6 Realistic Vendors across Indonesia
        $vendorsData = [
            [
                'email' => 'bali.villa@turget.id',
                'name' => 'Wayan Sudarma',
                'business_name' => 'Uluwatu Sunset Ocean Villa & Suites',
                'business_type' => 'cv',
                'owner_name' => 'I Wayan Sudarma, S.E.',
                'phone' => '+6281234567801',
                'category' => VendorCategory::Accommodation,
                'region_query' => 'Bali',
                'destination_query' => 'uluwatu',
                'address' => 'Jl. Pantai Suluban No. 18, Pecatu, Kuta Selatan, Kabupaten Badung, Bali',
                'latitude' => -8.8310000,
                'longitude' => 115.0880000,
                'description' => [
                    'id' => 'Resor villa mewah tebing karang di Uluwatu dengan kolam renang infinity menghadap Samudra Hindia. Menyajikan panorama matahari terbenam spektakuler, privasi tinggi, dan layanan butler 24 jam.',
                    'en' => 'Luxury cliffside ocean villa in Uluwatu with an infinity pool overlooking the Indian Ocean. Unrivaled sunset panoramas, supreme privacy, and 24-hour dedicated butler service.',
                ],
                'cover_image_url' => 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?w=1200&auto=format&fit=crop&q=80',
                'avatar_url' => 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?w=300&auto=format&fit=crop&q=80',
                'rating_avg' => 4.95,
                'reviews_count' => 84,
                'bookings_completed_count' => 142,
                'products' => [
                    [
                        'name' => [
                            'id' => 'Cliffside Oceanview Villa with Private Infinity Pool',
                            'en' => 'Cliffside Oceanview Villa with Private Infinity Pool',
                        ],
                        'description' => [
                            'id' => 'Villa 1 kamar seluas 180m² di tepi tebing karang. Fasilitas termasuk floating breakfast, private sunbed, bathtub marmer, dan pemandangan sunset langsung.',
                            'en' => '180sqm 1-bedroom clifftop villa. Includes floating breakfast, private sunbed, marble bathtub, and unobstructed sunset views.',
                        ],
                        'price' => 3500000,
                        'price_unit' => 'per_night',
                        'capacity' => 2,
                        'stock_quantity' => 4,
                        'booking_model' => 'instant',
                        'inclusions' => [
                            'id' => ['Sarapan pagi ala carte / floating breakfast', 'Welcome drink kelapa muda & buah musiman', 'Afternoon tea pukul 16:00 WITA', 'Akses Wi-Fi 100 Mbps'],
                            'en' => ['A la carte / floating breakfast', 'Welcome young coconut & seasonal fruit', 'Daily afternoon tea at 4 PM', 'High-speed 100 Mbps Wi-Fi'],
                        ],
                        'cover_image' => 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?w=800&auto=format&fit=crop&q=80',
                    ],
                    [
                        'name' => [
                            'id' => 'Deluxe Garden Terrace Suite',
                            'en' => 'Deluxe Garden Terrace Suite',
                        ],
                        'description' => [
                            'id' => 'Suite bernuansa tropis dengan teras menghadap taman asri Frangipani. Cocok untuk pasangan dan traveler solo yang mencari ketenangan.',
                            'en' => 'Tropical suite with a private terrace facing blooming frangipani gardens. Ideal for couples and serene solo travelers.',
                        ],
                        'price' => 1200000,
                        'price_unit' => 'per_night',
                        'capacity' => 2,
                        'stock_quantity' => 8,
                        'booking_model' => 'instant',
                        'inclusions' => [
                            'id' => ['Sarapan prasmanan harian', 'Akses kolam renang utama', 'Amenitas spa organik'],
                            'en' => ['Daily buffet breakfast', 'Access to main infinity pool', 'Organic spa amenities'],
                        ],
                        'cover_image' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800&auto=format&fit=crop&q=80',
                    ],
                ],
            ],
            [
                'email' => 'bali.transport@turget.id',
                'name' => 'Made Artawa',
                'business_name' => 'Dewata Premium Transport & Car Rental',
                'business_type' => 'pt',
                'owner_name' => 'I Made Artawa',
                'phone' => '+6281234567802',
                'category' => VendorCategory::VehicleRental,
                'region_query' => 'Bali',
                'destination_query' => 'uluwatu',
                'address' => 'Jl. Bypass Ngurah Rai No. 88, Jimbaran, Kuta Selatan, Kabupaten Badung, Bali',
                'latitude' => -8.7900000,
                'longitude' => 115.1600000,
                'description' => [
                    'id' => 'Penyedia transportasi wisata berlisensi resmi di Bali. Armada baru keluaran 2024-2025, pengemudi lokal ramah berlisensi pariwisata, full AC, dan asuransi all-risk komprehensif.',
                    'en' => 'Licensed tourist transport operator in Bali. Fleet of new 2024-2025 vehicles, polite licensed English-speaking local drivers, full AC, and comprehensive all-risk insurance.',
                ],
                'cover_image_url' => 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?w=1200&auto=format&fit=crop&q=80',
                'avatar_url' => 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?w=300&auto=format&fit=crop&q=80',
                'rating_avg' => 4.98,
                'reviews_count' => 112,
                'bookings_completed_count' => 278,
                'products' => [
                    [
                        'name' => [
                            'id' => 'Toyota Innova Zenix Hybrid + Pengemudi Wisata & BBM (10 Jam)',
                            'en' => 'Toyota Innova Zenix Hybrid + Driver & Fuel (10 Hours)',
                        ],
                        'description' => [
                            'id' => 'Kendaraan hybrid senyap dan nyaman untuk keliling Bali Selatan, Ubud, atau Bedugul. Termasuk pengemudi lokal berpengalaman, BBM, dan biaya parkir destinasi utama.',
                            'en' => 'Smooth and eco-friendly hybrid MPV for Bali day tours. Includes experienced driver, fuel, and destination parking.',
                        ],
                        'price' => 950000,
                        'price_unit' => 'per_day',
                        'capacity' => 6,
                        'stock_quantity' => 10,
                        'booking_model' => 'instant',
                        'inclusions' => [
                            'id' => ['Mobil Toyota Innova Zenix Hybrid 2024', 'Driver ramah & memahami rute wisata', 'BBM selama 10 jam operasional', 'Air mineral dingin di kendaraan'],
                            'en' => ['2024 Toyota Innova Zenix Hybrid', 'English-speaking driver', 'Fuel for 10 hours', 'Cold bottled water'],
                        ],
                        'cover_image' => 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?w=800&auto=format&fit=crop&q=80',
                    ],
                ],
            ],
            [
                'email' => 'bromo.jeep@turget.id',
                'name' => 'Bambang Sutrisno',
                'business_name' => 'Bromo Tengger 4x4 Adventure Club',
                'business_type' => 'koperasi',
                'owner_name' => 'Bambang Sutrisno (Ketua Koperasi)',
                'phone' => '+6281234567803',
                'category' => VendorCategory::Activity,
                'region_query' => 'Jawa Timur',
                'destination_query' => 'bromo',
                'address' => 'Jl. Raya Bromo KM 24, Sukapura, Kabupaten Probolinggo, Jawa Timur',
                'latitude' => -7.9250000,
                'longitude' => 112.9800000,
                'description' => [
                    'id' => 'Koperasi resmi pemilik armada Jeep 4x4 Toyota Land Cruiser Hardtop di kawasan Taman Nasional Bromo Tengger Semeru. Melayani rute Sunrise Penanjakan, Kawah Bromo, Pasir Berbisik, dan Bukit Teletubbies.',
                    'en' => 'Official cooperative of 4x4 Toyota Land Cruiser Hardtops in Bromo Tengger Semeru National Park. Covering Sunrise Penanjakan, Bromo Crater, Whispering Sands, and Teletubbies Hill.',
                ],
                'cover_image_url' => 'https://images.unsplash.com/photo-1588668214407-6ea9a6d8c272?w=1200&auto=format&fit=crop&q=80',
                'avatar_url' => 'https://images.unsplash.com/photo-1588668214407-6ea9a6d8c272?w=300&auto=format&fit=crop&q=80',
                'rating_avg' => 4.92,
                'reviews_count' => 140,
                'bookings_completed_count' => 310,
                'products' => [
                    [
                        'name' => [
                            'id' => 'Sewa Jeep Hardtop 4x4 Bromo Sunrise & 4 Spot Wisata',
                            'en' => 'Bromo Sunrise 4x4 Hardtop Jeep Charter (4 Spots)',
                        ],
                        'description' => [
                            'id' => 'Jeep 4x4 kapasitas maksimal 6 orang. Rute lengkap: Sunrise Point (Penanjakan 1 / Kingkong Hill), Kawah Bromo & Pura Poten, Pasir Berbisik, dan Savana Bukit Teletubbies.',
                            'en' => '4x4 Jeep charter for up to 6 people. Complete circuit: Sunrise Viewpoint, Bromo Crater, Whispering Sands, and Teletubbies Savanna.',
                        ],
                        'price' => 750000,
                        'price_unit' => 'per_session',
                        'capacity' => 6,
                        'stock_quantity' => 25,
                        'booking_model' => 'instant',
                        'inclusions' => [
                            'id' => ['Unit Jeep Hardtop 4x4 + Driver Lokal Berpengalaman', 'BBM seluruh rute Bromo', 'Retribusi parkir jeep di area TNBTS'],
                            'en' => ['Hardtop 4x4 Jeep + Local driver', 'Fuel for all 4 spots', 'TNBTS parking fees'],
                        ],
                        'cover_image' => 'https://images.unsplash.com/photo-1588668214407-6ea9a6d8c272?w=800&auto=format&fit=crop&q=80',
                    ],
                ],
            ],
            [
                'email' => 'jogja.heritage@turget.id',
                'name' => 'Raden Mas Haryo',
                'business_name' => 'Ndalem Heritage Homestay & Cultural Stays',
                'business_type' => 'individual',
                'owner_name' => 'RM Haryo Danudiningrat',
                'phone' => '+6281234567804',
                'category' => VendorCategory::Accommodation,
                'region_query' => 'Yogyakarta',
                'destination_query' => 'kraton',
                'address' => 'Jl. Magangan Kulon No. 12, Panembahan, Kraton, Kota Yogyakarta',
                'latitude' => -7.8050000,
                'longitude' => 110.3640000,
                'description' => [
                    'id' => 'Penginapan rumah tradisional Jawa (Joglo) peninggalan era Sri Sultan HB VII di dalam lingkungan Benteng Keraton Yogyakarta. Menghadirkan ketenangan budaya Jawa otentik, teh poci gula batu, dan sarapan gudeg pawon.',
                    'en' => 'Authentic Javanese Joglo heritage home located within the royal Kraton fortress walls of Yogyakarta. Experience traditional hospitality, hot jasmine tea, and homemade Gudeg breakfast.',
                ],
                'cover_image_url' => 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=1200&auto=format&fit=crop&q=80',
                'avatar_url' => 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=300&auto=format&fit=crop&q=80',
                'rating_avg' => 4.90,
                'reviews_count' => 46,
                'bookings_completed_count' => 95,
                'products' => [
                    [
                        'name' => [
                            'id' => 'Kamar Tradisional Ndalem Joglo Heritage',
                            'en' => 'Traditional Ndalem Joglo Heritage Room',
                        ],
                        'description' => [
                            'id' => 'Kamar tidur luas dengan ranjang kayu jati ukir kuno, kamar mandi modern shower air hangat, dan teras pendopo pribadi.',
                            'en' => 'Spacious bedroom featuring antique carved teakwood furniture, modern hot-shower bathroom, and pendopo terrace.',
                        ],
                        'price' => 450000,
                        'price_unit' => 'per_night',
                        'capacity' => 2,
                        'stock_quantity' => 5,
                        'booking_model' => 'instant',
                        'inclusions' => [
                            'id' => ['Sarapan khas Gudeg Pawon Kraton', 'Wedang ronde / teh tubruk sore hari', 'Wi-Fi gratis di seluruh area'],
                            'en' => ['Traditional Gudeg breakfast', 'Evening herbal tea / Wedang', 'Complimentary Wi-Fi'],
                        ],
                        'cover_image' => 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=800&auto=format&fit=crop&q=80',
                    ],
                ],
            ],
            [
                'email' => 'komodo.liveaboard@turget.id',
                'name' => 'Kapten Frans',
                'business_name' => 'Flores Phinisi Charter & Diving Cruises',
                'business_type' => 'pt',
                'owner_name' => 'Fransiscus Xaverius',
                'phone' => '+6281234567805',
                'category' => VendorCategory::Activity,
                'region_query' => 'Nusa Tenggara Timur',
                'destination_query' => 'komodo',
                'address' => 'Kawasan Marina Waterfront No. 04, Labuan Bajo, Kabupaten Manggarai Barat, NTT',
                'latitude' => -8.4900000,
                'longitude' => 119.8800000,
                'description' => [
                    'id' => 'Operator kapal wisata Phinisi dan speedboat charter di Labuan Bajo. Dilengkapi peralatan navigasi satelit maritim, kru bersertifikat keselamatan laut, dan pemandu selam (divemaster) profesional.',
                    'en' => 'Premier Phinisi sailing yacht and speedboat charter operator in Labuan Bajo. Equipped with marine satellite navigation, safety-certified crew, and certified PADI divemasters.',
                ],
                'cover_image_url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=1200&auto=format&fit=crop&q=80',
                'avatar_url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=300&auto=format&fit=crop&q=80',
                'rating_avg' => 4.96,
                'reviews_count' => 68,
                'bookings_completed_count' => 120,
                'products' => [
                    [
                        'name' => [
                            'id' => 'Speedboat Charter Full Day: Padar - Komodo - Pink Beach - Manta Point',
                            'en' => 'Full Day Speedboat Charter: Padar - Komodo - Pink Beach - Manta Point',
                        ],
                        'description' => [
                            'id' => 'Charter speedboat cepat bermesin ganda untuk 1-12 orang. Kunjungi destinasi terbaik Taman Nasional Komodo dalam satu hari tanpa terburu-buru.',
                            'en' => 'Twin-engine private speedboat charter for up to 12 passengers. Explore the iconic Komodo National Park spots in comfort.',
                        ],
                        'price' => 6500000,
                        'price_unit' => 'per_day',
                        'capacity' => 12,
                        'stock_quantity' => 3,
                        'booking_model' => 'request_to_book',
                        'inclusions' => [
                            'id' => ['Speedboat privat + BBM', 'Kapten & 2 kru kapal', 'Alat snorkeling (masker & snorkel)', 'Makan siang box & buah segar', 'Air mineral & minuman dingin'],
                            'en' => ['Private speedboat + fuel', 'Captain & 2 boat crew', 'Snorkeling gear (mask & snorkel)', 'Boxed lunch & fresh fruit', 'Cold beverages & water'],
                        ],
                        'cover_image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=800&auto=format&fit=crop&q=80',
                    ],
                ],
            ],
            [
                'email' => 'rajaampat.homestay@turget.id',
                'name' => 'Elieser Dimara',
                'business_name' => 'Piaynemo Eco Water Villas & Boat Transport',
                'business_type' => 'koperasi',
                'owner_name' => 'Elieser Dimara (Tokoh Masyarakat Pam)',
                'phone' => '+6281234567806',
                'category' => VendorCategory::Accommodation,
                'region_query' => 'Papua Barat Daya',
                'destination_query' => 'raja',
                'address' => 'Gugusan Pulau Pam, Kepulauan Fam, Distrik Waigeo Barat Kepulauan, Raja Ampat',
                'latitude' => -0.5600000,
                'longitude' => 130.2700000,
                'description' => [
                    'id' => 'Penginapan ramah lingkungan di atas air (overwater bungalow) tepat di jantung kepulauan karst Piaynemo. Langsung menghadap terumbu karang alami dan laguna toska.',
                    'en' => 'Eco-friendly overwater bungalows nestled right in the heart of the iconic Piaynemo karst islands. Direct access to pristine coral reefs and turquoise lagoons.',
                ],
                'cover_image_url' => 'https://images.unsplash.com/photo-1518548419970-58e3b4079ab2?w=1200&auto=format&fit=crop&q=80',
                'avatar_url' => 'https://images.unsplash.com/photo-1518548419970-58e3b4079ab2?w=300&auto=format&fit=crop&q=80',
                'rating_avg' => 4.97,
                'reviews_count' => 39,
                'bookings_completed_count' => 88,
                'products' => [
                    [
                        'name' => [
                            'id' => 'Overwater Eco Bungalow with Ocean Balcony & Reef Access',
                            'en' => 'Overwater Eco Bungalow with Ocean Balcony & Reef Access',
                        ],
                        'description' => [
                            'id' => 'Bungalow kayu di atas laut dengan balkon pribadi menghadap laguna. Tangga langsung menuju terumbu karang untuk snorkeling kapan saja.',
                            'en' => 'Wooden overwater bungalow with private lagoon balcony and ladder straight into the vibrant coral reef.',
                        ],
                        'price' => 1100000,
                        'price_unit' => 'per_night',
                        'capacity' => 2,
                        'stock_quantity' => 6,
                        'booking_model' => 'request_to_book',
                        'inclusions' => [
                            'id' => ['Makan 3x sehari (masakan ikan segar lokal)', 'Kopi & teh sepanjang hari', 'Penggunaan jaket pelampung & alat snorkeling'],
                            'en' => ['3 daily homecooked meals (fresh local fish)', 'All-day coffee and tea', 'Life jacket & snorkel gear usage'],
                        ],
                        'cover_image' => 'https://images.unsplash.com/photo-1518548419970-58e3b4079ab2?w=800&auto=format&fit=crop&q=80',
                    ],
                ],
            ],
        ];

        foreach ($vendorsData as $data) {
            $user = User::firstOrCreate([
                'email' => $data['email'],
            ], [
                'name' => $data['name'],
                'password' => Hash::make('password'),
                'locale' => 'id',
                'status' => 'active',
                'email_verified_at' => now(),
            ]);

            if (! $user->hasRole('vendor')) {
                $user->assignRole('vendor');
            }

            // Region resolution
            $region = AdministrativeRegion::where('name', 'like', '%'.$data['region_query'].'%')->first()
                ?? AdministrativeRegion::first();

            // Closest primary destination resolution
            $destination = Destination::where('slug', 'like', '%'.$data['destination_query'].'%')->first();

            $slug = Str::slug($data['business_name']);

            $vendor = VendorProfile::firstOrNew(['user_id' => $user->id]);
            $vendor->fill([
                'slug' => $slug,
                'business_name' => $data['business_name'],
                'business_type' => $data['business_type'],
                'owner_name' => $data['owner_name'],
                'phone' => $data['phone'],
                'email' => $data['email'],
                'address' => $data['address'],
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'country_id' => $indonesia->id,
                'region_id' => $region->id,
                'service_areas' => [$region->id],
                'category' => $data['category'],
                'description' => $data['description'],
                'cover_image_url' => $data['cover_image_url'],
                'avatar_url' => $data['avatar_url'],
                'bank_name' => 'BCA',
                'bank_account_number' => '8830'.rand(100000, 999999),
                'bank_account_holder' => $data['owner_name'],
                'cancellation_policy' => [
                    'id' => 'Pembatalan gratis hingga 48 jam sebelum tanggal jadwal layanan.',
                    'en' => 'Free cancellation up to 48 hours prior to service date.',
                ],
                'opening_hours' => [
                    'id' => 'Setiap Hari, 07:00 - 21:00',
                    'en' => 'Daily, 07:00 - 21:00',
                ],
                'is_active' => true,
            ]);

            $vendor->forceFill([
                'verification_status' => VendorVerificationStatus::Verified,
                'badge_verified' => true,
                'verified_at' => now(),
                'verified_by' => $admin?->id,
                'rating_avg' => $data['rating_avg'],
                'reviews_count' => $data['reviews_count'],
                'bookings_completed_count' => $data['bookings_completed_count'],
            ])->save();

            // Seed KYC verification record
            VendorVerification::firstOrCreate([
                'vendor_profile_id' => $vendor->id,
            ], [
                'nib' => '9120'.rand(100000000, 999999999),
                'nib_file_path' => '/storage/kyc/nib_'.$vendor->slug.'.pdf',
                'siup' => '503/'.rand(100, 999).'/BPPT/2023',
                'siup_file_path' => '/storage/kyc/siup_'.$vendor->slug.'.pdf',
                'npwp' => '01.'.rand(100, 999).'.'.rand(100, 999).'.1-0'.rand(10, 99).'.000',
                'npwp_file_path' => '/storage/kyc/npwp_'.$vendor->slug.'.pdf',
                'ktp_owner' => '5171'.rand(100000000000, 999999999999),
                'ktp_file_path' => '/storage/kyc/ktp_'.$vendor->slug.'.jpg',
                'status' => VendorVerificationStatus::Verified,
                'notes' => 'Dokumen legalitas badan usaha dan izin operasional pariwisata telah divalidasi lengkap.',
                'reviewed_by' => $admin?->id,
                'reviewed_at' => now(),
            ]);

            // Seed products
            foreach ($data['products'] as $prodData) {
                $prodSlug = Str::slug($prodData['name']['id'] ?? $prodData['name']['en']);
                $product = VendorProduct::firstOrNew([
                    'vendor_profile_id' => $vendor->id,
                    'slug' => $prodSlug,
                ]);

                $product->fill([
                    'destination_id' => $destination?->id,
                    'name' => $prodData['name'],
                    'description' => $prodData['description'],
                    'category' => $data['category'],
                    'price' => $prodData['price'],
                    'price_unit' => $prodData['price_unit'],
                    'currency' => 'IDR',
                    'capacity' => $prodData['capacity'],
                    'stock_quantity' => $prodData['stock_quantity'],
                    'booking_model' => $prodData['booking_model'],
                    'inclusions' => $prodData['inclusions'] ?? null,
                    'cover_image' => $prodData['cover_image'],
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                    'address' => $data['address'],
                ]);

                $product->forceFill([
                    'status' => ListingStatus::Published,
                    'approved_at' => now(),
                    'approved_by' => $admin?->id,
                    'rating_avg' => 5.00,
                    'reviews_count' => rand(8, 45),
                    'is_featured' => true,
                ])->save();

                // Link as add-on to Tour Packages if destination matches (PRD §8.2)
                if ($destination) {
                    $packages = TourPackage::where('destination_id', $destination->id)->get();
                    foreach ($packages as $pkg) {
                        $pkg->vendorAddons()->syncWithoutDetaching([
                            $product->id => [
                                'custom_price' => $prodData['price'],
                                'is_required' => false,
                                'notes' => json_encode(['id' => 'Add-on opsional untuk kenyamanan trip', 'en' => 'Optional add-on for trip comfort']),
                            ],
                        ]);
                    }
                }
            }
        }
    }
}
