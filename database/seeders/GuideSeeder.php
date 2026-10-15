<?php

namespace Database\Seeders;

use App\Enums\GuideVerificationStatus;
use App\Enums\ListingStatus;
use App\Models\AdministrativeRegion;
use App\Models\Country;
use App\Models\Destination;
use App\Models\GuideProfile;
use App\Models\GuideVerification;
use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GuideSeeder extends Seeder
{
    public function run(): void
    {
        $indonesia = Country::where('code', 'ID')->firstOrFail();
        $adminUser = User::where('email', 'admin@turget.id')->first();

        // 1. I Wayan Sukadana (Bali)
        $baliRegion = AdministrativeRegion::where('slug', 'badung')->first()
            ?? AdministrativeRegion::where('slug', 'bali')->first();

        $userWayan = User::firstOrCreate(['email' => 'guide.bali@turget.id'], [
            'name' => 'I Wayan Sukadana',
            'password' => Hash::make('password'),
            'locale' => 'id',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $userWayan->syncRoles(['tour_guide']);

        $guideWayan = GuideProfile::updateOrCreate(['user_id' => $userWayan->id], [
            'slug' => 'wayan-sukadana-bali',
            'display_name' => 'I Wayan Sukadana',
            'phone' => '+62 812-3849-1029',
            'country_id' => $indonesia->id,
            'region_id' => $baliRegion->id,
            'service_areas' => [$baliRegion->id],
            'languages' => ['id', 'en', 'ja'],
            'specializations' => ['cultural', 'photography', 'historical'],
            'experience_years' => 8,
            'price_per_day' => 650000,
            'tagline' => [
                'id' => 'Pemandu budaya Bali bersertifikat HPI & penutur filosofi Tri Hita Karana',
                'en' => 'Certified HPI Bali cultural guide & storyteller of Tri Hita Karana philosophy',
            ],
            'bio' => [
                'id' => "Om Swastyastu! Nama saya Wayan Sukadana, lahir dan dibesarkan di Bali. Selama lebih dari 8 tahun, saya telah memandu ribuan wisatawan mancanegara dan domestik memahami lebih dari sekadar pemandangan indah Bali—melainkan keheningan pura sakral, upacara adat, dan keramahan masyarakat desa.\n\nSaya menguasai Bahasa Indonesia, Inggris, dan Jepang dengan fasih. Bagi saya, memandu bukan sekadar pekerjaan, tetapi jembatan budaya.",
                'en' => "Om Swastyastu! My name is Wayan Sukadana, born and raised in Bali. For over 8 years, I have guided international and domestic travelers to appreciate the soul of Bali beyond postcard views—unveiling sacred temple rituals, traditional architecture, and authentic village warmth.\n\nI speak fluent Indonesian, English, and conversational Japanese. Let's explore Bali with respect and wonder.",
            ],
            'avatar_url' => '/images/destinations/uluwatu.jpg',
            'cover_image_url' => '/images/destinations/bali_hero.jpg',
            'bank_name' => 'BCA',
            'bank_account_number' => '7720194821',
            'bank_account_holder' => 'I Wayan Sukadana',
            'verification_status' => GuideVerificationStatus::Verified,
            'badge_verified' => true,
            'verified_at' => now()->subMonths(3),
            'verified_by' => $adminUser?->id,
            'rating_avg' => 4.98,
            'reviews_count' => 48,
            'trips_completed_count' => 124,
            'response_rate_percent' => 100,
            'response_time_minutes' => 10,
        ]);

        // KYC Wayan
        GuideVerification::updateOrCreate([
            'guide_profile_id' => $guideWayan->id,
            'document_type' => 'id_card',
        ], [
            'document_number' => '510301xxxxxxxxxx',
            'document_file_path' => '/storage/kyc/ktp_wayan.jpg',
            'status' => 'verified',
            'reviewed_by' => $adminUser?->id,
            'reviewed_at' => now()->subMonths(3),
        ]);

        GuideVerification::updateOrCreate([
            'guide_profile_id' => $guideWayan->id,
            'document_type' => 'guide_license',
        ], [
            'document_number' => 'HPI-BALI-2018-0914',
            'document_file_path' => '/storage/kyc/license_wayan.jpg',
            'status' => 'verified',
            'reviewed_by' => $adminUser?->id,
            'reviewed_at' => now()->subMonths(3),
        ]);

        // Destinations link
        $destUluwatu = Destination::where('slug', 'pura-luhur-uluwatu')->first();
        $destUbud = Destination::where('slug', 'sacred-monkey-forest-ubud')->first();
        if ($destUluwatu && $destUbud) {
            $guideWayan->destinations()->sync([$destUluwatu->id, $destUbud->id]);
        }

        // Tour Packages Wayan
        TourPackage::updateOrCreate(['slug' => 'uluwatu-sunset-kecak-fire-dance-tour'], [
            'guide_profile_id' => $guideWayan->id,
            'destination_id' => $destUluwatu?->id,
            'title' => [
                'id' => 'Tur Sunset Pura Luhur Uluwatu & Tari Kecak Samudra Hindia',
                'en' => 'Uluwatu Sunset Cliff Temple & Ocean Kecak Fire Dance Tour',
            ],
            'tagline' => [
                'id' => 'Eksplorasi tebing suci 70 meter, interaksi satwa monyet, dan tontonan tari sakral matahari terbenam',
                'en' => 'Explore the 70m sacred cliff, friendly macaques, and dramatic sunset fire dance',
            ],
            'description' => [
                'id' => "Rasakan kemegahan Pura Luhur Uluwatu bersama pemandu lokal berlisensi. Kami akan menjemput Anda, memandu etika berpakaian kain kamen, mengisahkan sejarah Dang Hyang Nirartha abad ke-11, hingga mengamankan kursi terbaik untuk pementasan tari kolosal Kecak dengan latar siluet matahari tenggelam ke Samudra Hindia.",
                'en' => "Experience the sublime majesty of Uluwatu Temple with a licensed native guide. We arrange respectful temple visits, narrate 11th-century spiritual lore, and secure prime seating for the world-renowned Kecak Fire Dance as the crimson sun dips into the Indian Ocean.",
            ],
            'tour_type' => 'cultural',
            'price_type' => 'per_person',
            'base_price' => 450000,
            'duration_hours' => 6,
            'duration_days' => 1,
            'min_participants' => 1,
            'max_participants' => 8,
            'meeting_point' => 'Lobi Hotel Anda di area Kuta / Seminyak / Nusa Dua / Jimbaran',
            'end_point' => 'Kembali ke Hotel Anda',
            'cover_image' => '/images/destinations/uluwatu.jpg',
            'itinerary' => [
                ['time' => '14:30 - 15:30', 'title' => 'Penjemputan & Perjalanan', 'description' => 'Penjemputan ramah di lobi akomodasi menuju Semenanjung Bukit Selatan.'],
                ['time' => '15:30 - 17:30', 'title' => 'Jelajah Tebing & Pura Uluwatu', 'description' => 'Mengenakan kain kamen, menyusuri jalan setapak tebing batu karang kapur, dan mempelajari filosofi mandala pura.'],
                ['time' => '17:30 - 19:00', 'title' => 'Pementasan Tari Kecak & Sunset', 'description' => 'Menyaksikan 60 penari melantunkan paduan suara vokal cak-cak membawakan fragmen epos Ramayana di panggung terbuka tebing samudra.'],
                ['time' => '19:00 - 20:30', 'title' => 'Opsi Makan Malam Jimbaran & Antar Pulang', 'description' => 'Opsi singgah santap boga bahari segar di pesisir Teluk Jimbaran sebelum kembali ke hotel.'],
            ],
            'inclusions' => [
                'id' => ['Jasa pemandu wisata berlisensi resmi', 'Tiket masuk Pura Luhur Uluwatu', 'Tiket pertunjukan Tari Kecak reguler', 'Kain kamen & selendang adat', 'Air mineral dingin'],
                'en' => ['Licensed professional tour guide service', 'Uluwatu Temple admission ticket', 'Kecak Fire Dance reserved ticket', 'Traditional sarong & sash attire', 'Chilled mineral water'],
            ],
            'exclusions' => [
                'id' => ['Makan malam boga bahari di Jimbaran (pilihan pribadi)', 'Tipping sukarela untuk pemandu'],
                'en' => ['Seafood dinner in Jimbaran Bay (personal order)', 'Voluntary guide gratuities'],
            ],
            'status' => ListingStatus::Published,
            'approved_at' => now()->subMonths(2),
            'approved_by' => $adminUser?->id,
        ]);

        // 2. Raden Mas Danang (Yogyakarta)
        $jogjaRegion = AdministrativeRegion::where('slug', 'kota-yogyakarta')->first()
            ?? AdministrativeRegion::where('slug', 'daerah-istimewa-yogyakarta')->first();

        $userDanang = User::firstOrCreate(['email' => 'guide.jogja@turget.id'], [
            'name' => 'Raden Mas Danang',
            'password' => Hash::make('password'),
            'locale' => 'id',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $userDanang->syncRoles(['tour_guide']);

        $guideDanang = GuideProfile::updateOrCreate(['user_id' => $userDanang->id], [
            'slug' => 'danang-wijaya-yogyakarta',
            'display_name' => 'Raden Mas Danang',
            'phone' => '+62 813-2918-4720',
            'country_id' => $indonesia->id,
            'region_id' => $jogjaRegion->id,
            'service_areas' => [$jogjaRegion->id],
            'languages' => ['id', 'en', 'nl'],
            'specializations' => ['historical', 'cultural', 'culinary'],
            'experience_years' => 12,
            'price_per_day' => 550000,
            'tagline' => [
                'id' => 'Pemerhati warisan Wangsa Mataram & pemandu istana resmi Keraton Yogyakarta',
                'en' => 'Mataram royal heritage specialist & official court guide of Yogyakarta Palace',
            ],
            'bio' => [
                'id' => "Sugeng rawuh ing Ngayogyakarta Hadiningrat. Keluarga kami mengabdi di lingkungan budaya Keraton selama beberapa generasi. Memandu wisatawan menjelajahi Yogyakarta bagi saya adalah tentang membuka pintu filosofi Jawa—dari sumbu filosofis Tugu hingga Pantai Parangkusumo.",
                'en' => "Welcome to Yogyakarta. With 12 years of specialized guiding and family roots in the royal court, I illuminate the profound symbolism of Javanese architecture, court music, and ancient temple geometry.",
            ],
            'avatar_url' => '/images/destinations/yogyakarta.jpg',
            'cover_image_url' => '/images/destinations/borobudur.jpg',
            'bank_name' => 'Mandiri',
            'bank_account_number' => '1370018920192',
            'bank_account_holder' => 'Danang Wijaya',
            'verification_status' => GuideVerificationStatus::Verified,
            'badge_verified' => true,
            'verified_at' => now()->subMonths(4),
            'verified_by' => $adminUser?->id,
            'rating_avg' => 5.00,
            'reviews_count' => 62,
            'trips_completed_count' => 180,
            'response_rate_percent' => 98,
            'response_time_minutes' => 12,
        ]);

        $destKeraton = Destination::where('slug', 'keraton-ngayogyakarta-hadiningrat')->first();
        $destBorobudur = Destination::where('slug', 'candi-borobudur')->first();
        if ($destKeraton && $destBorobudur) {
            $guideDanang->destinations()->sync([$destKeraton->id, $destBorobudur->id]);
        }

        TourPackage::updateOrCreate(['slug' => 'borobudur-sunrise-royal-keraton-day-tour'], [
            'guide_profile_id' => $guideDanang->id,
            'destination_id' => $destBorobudur?->id,
            'title' => [
                'id' => 'Tur Mahakarya Borobudur & Filosofi Adiluhung Keraton Jogja',
                'en' => 'Borobudur Monolithic Wonder & Royal Palace Heritage Day Tour',
            ],
            'tagline' => [
                'id' => 'Eksplorasi relief candi Buddha terbesar di dunia dilanjutkan santap siang khas istana Keraton',
                'en' => 'Unravel ancient Buddhist reliefs followed by authentic royal palace gastronomy',
            ],
            'description' => [
                'id' => "Hari penuh inspirasi mengkaji relief Lalitavistara di Candi Borobudur dengan pemahaman arkeologis mendalam, dilanjutkan ziarah budaya ke bangsal kencana Keraton Ngayogyakarta Hadiningrat.",
                'en' => "A transformative journey through the largest Buddhist monument on earth, guided with rich archaeological insight, continuing to the living sovereign court of Yogyakarta.",
            ],
            'tour_type' => 'historical',
            'price_type' => 'per_person',
            'base_price' => 600000,
            'duration_hours' => 9,
            'duration_days' => 1,
            'min_participants' => 1,
            'max_participants' => 6,
            'meeting_point' => 'Lobi Hotel Anda di Yogyakarta',
            'end_point' => 'Kembali ke Hotel Anda',
            'cover_image' => '/images/destinations/borobudur.jpg',
            'status' => ListingStatus::Published,
            'approved_at' => now()->subMonths(3),
            'approved_by' => $adminUser?->id,
        ]);

        // 3. Bima Prasetyo (Bromo & East Java)
        $bromoRegion = AdministrativeRegion::where('slug', 'probolinggo')->first()
            ?? AdministrativeRegion::where('slug', 'jawa-timur')->first();

        $userBima = User::firstOrCreate(['email' => 'guide.bromo@turget.id'], [
            'name' => 'Bima Prasetyo',
            'password' => Hash::make('password'),
            'locale' => 'id',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $userBima->syncRoles(['tour_guide']);

        $guideBima = GuideProfile::updateOrCreate(['user_id' => $userBima->id], [
            'slug' => 'bima-prasetyo-bromo',
            'display_name' => 'Bima Prasetyo',
            'phone' => '+62 821-4412-9831',
            'country_id' => $indonesia->id,
            'region_id' => $bromoRegion->id,
            'service_areas' => [$bromoRegion->id],
            'languages' => ['id', 'en', 'fr'],
            'specializations' => ['trekking', 'nature_adventure', 'photography'],
            'experience_years' => 6,
            'price_per_day' => 700000,
            'tagline' => [
                'id' => 'Pemandu ekspedisi gunung api Bromo Tengger & fotografi kaldera',
                'en' => 'Bromo Tengger volcanic expedition guide & caldera photographer',
            ],
            'bio' => [
                'id' => "Petualang alam bebas berdarah Jawa-Tengger. Menguasai jalur sunrise rahasia King Kong Hill dan penjelajahan lautan pasir kaldera purba.",
                'en' => "Passionate wilderness adventurer specializing in Mount Bromo alpine expeditions, sunrise astrophotography, and Tengger indigenous lore.",
            ],
            'avatar_url' => '/images/destinations/bromo.jpg',
            'cover_image_url' => '/images/destinations/bromo.jpg',
            'bank_name' => 'BNI',
            'bank_account_number' => '0839182910',
            'bank_account_holder' => 'Bima Prasetyo',
            'verification_status' => GuideVerificationStatus::Verified,
            'badge_verified' => true,
            'verified_at' => now()->subMonths(2),
            'verified_by' => $adminUser?->id,
            'rating_avg' => 4.96,
            'reviews_count' => 37,
            'trips_completed_count' => 95,
        ]);

        $destBromo = Destination::where('slug', 'gunung-bromo-tengger')->first();
        if ($destBromo) {
            $guideBima->destinations()->sync([$destBromo->id]);
        }

        // 4. Kornelis Doren (Labuan Bajo & Komodo)
        $komodoRegion = AdministrativeRegion::where('slug', 'manggarai-barat')->first()
            ?? AdministrativeRegion::where('slug', 'nusa-tenggara-timur')->first();

        $userKornelis = User::firstOrCreate(['email' => 'guide.komodo@turget.id'], [
            'name' => 'Kornelis Doren',
            'password' => Hash::make('password'),
            'locale' => 'id',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $userKornelis->syncRoles(['tour_guide']);

        $guideKornelis = GuideProfile::updateOrCreate(['user_id' => $userKornelis->id], [
            'slug' => 'kornelis-doren-komodo',
            'display_name' => 'Kornelis Doren',
            'phone' => '+62 852-3901-8273',
            'country_id' => $indonesia->id,
            'region_id' => $komodoRegion->id,
            'service_areas' => [$komodoRegion->id],
            'languages' => ['id', 'en', 'de'],
            'specializations' => ['marine', 'nature_adventure', 'trekking'],
            'experience_years' => 9,
            'price_per_day' => 850000,
            'tagline' => [
                'id' => 'Pemandu naturalist resmi Taman Nasional Komodo & pelaut Flores',
                'en' => 'Official certified Komodo National Park naturalist & Flores sea voyager',
            ],
            'bio' => [
                'id' => "Putra asli Manggarai Barat yang telah mengarungi perairan Selat Sape dan kepulauan Komodo sejak kecil. Pemandu tersertifikasi satwa purba Varanus komodoensis.",
                'en' => "Native son of West Manggarai with lifelong maritime experience across Komodo archipelago. Certified wildlife naturalist ensuring safe close encounters.",
            ],
            'avatar_url' => '/images/destinations/padar.jpg',
            'cover_image_url' => '/images/destinations/padar.jpg',
            'bank_name' => 'BRI',
            'bank_account_number' => '462901829101531',
            'bank_account_holder' => 'Kornelis Doren',
            'verification_status' => GuideVerificationStatus::Verified,
            'badge_verified' => true,
            'verified_at' => now()->subMonths(5),
            'verified_by' => $adminUser?->id,
            'rating_avg' => 5.00,
            'reviews_count' => 54,
            'trips_completed_count' => 140,
        ]);

        $destPadar = Destination::where('slug', 'pulau-padar-komodo')->first();
        if ($destPadar) {
            $guideKornelis->destinations()->sync([$destPadar->id]);
        }

        // 5. Yulius Manupapami (Raja Ampat)
        $rajaAmpatRegion = AdministrativeRegion::where('slug', 'raja-ampat')->first()
            ?? AdministrativeRegion::where('slug', 'papua-barat-daya')->first();

        $userYulius = User::firstOrCreate(['email' => 'guide.rajaampat@turget.id'], [
            'name' => 'Yulius Manupapami',
            'password' => Hash::make('password'),
            'locale' => 'id',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $userYulius->syncRoles(['tour_guide']);

        $guideYulius = GuideProfile::updateOrCreate(['user_id' => $userYulius->id], [
            'slug' => 'yulius-manupapami-raja-ampat',
            'display_name' => 'Yulius Manupapami',
            'phone' => '+62 811-4820-1928',
            'country_id' => $indonesia->id,
            'region_id' => $rajaAmpatRegion->id,
            'service_areas' => [$rajaAmpatRegion->id],
            'languages' => ['id', 'en'],
            'specializations' => ['marine', 'photography', 'nature_adventure'],
            'experience_years' => 7,
            'price_per_day' => 1200000,
            'tagline' => [
                'id' => 'Pemandu konservasi bahari Kepulauan Karst Piaynemo & hutan Cendrawasih',
                'en' => 'Piaynemo marine conservation guide & Wilson’s Bird of Paradise tracker',
            ],
            'bio' => [
                'id' => "Menjaga dan memperkenalkan keajaiban Raja Ampat sebagai surga biodiversitas laut dunia. Bersama saya, Anda akan melihat lumba-lumba, pari manta, dan mendaki puncak karst Piaynemo dengan aman.",
                'en' => "Dedicated to sharing the global epicenter of marine biodiversity. We navigate limestone labyrinths, manta ray cleaning stations, and pristine jungle canopies.",
            ],
            'avatar_url' => '/images/destinations/raja_ampat.jpg',
            'cover_image_url' => '/images/destinations/raja_ampat.jpg',
            'bank_name' => 'Bank Papua',
            'bank_account_number' => '1029381920',
            'bank_account_holder' => 'Yulius Manupapami',
            'verification_status' => GuideVerificationStatus::Verified,
            'badge_verified' => true,
            'verified_at' => now()->subMonths(1),
            'verified_by' => $adminUser?->id,
            'rating_avg' => 5.00,
            'reviews_count' => 29,
            'trips_completed_count' => 70,
        ]);

        $destPiaynemo = Destination::where('slug', 'piaynemo-raja-ampat')->first();
        if ($destPiaynemo) {
            $guideYulius->destinations()->sync([$destPiaynemo->id]);
        }

        // 6. Agus Salim (PENDING APPLICATION - for Admin verification testing)
        $tobaRegion = AdministrativeRegion::where('slug', 'samosir')->first()
            ?? AdministrativeRegion::where('slug', 'sumatera-utara')->first();

        $userAgus = User::firstOrCreate(['email' => 'guide.pending@turget.id'], [
            'name' => 'Agus Salim Situmorang',
            'password' => Hash::make('password'),
            'locale' => 'id',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $userAgus->syncRoles(['tour_guide']);

        $guideAgus = GuideProfile::updateOrCreate(['user_id' => $userAgus->id], [
            'slug' => 'agus-salim-toba',
            'display_name' => 'Agus Salim Situmorang',
            'phone' => '+62 821-6510-9482',
            'country_id' => $indonesia->id,
            'region_id' => $tobaRegion->id,
            'service_areas' => [$tobaRegion->id],
            'languages' => ['id', 'en'],
            'specializations' => ['cultural', 'historical'],
            'experience_years' => 4,
            'price_per_day' => 450000,
            'tagline' => [
                'id' => 'Pemandu adat Batak Toba & penjelajah Pulau Samosir',
                'en' => 'Batak Toba indigenous culture guide & Samosir Island explorer',
            ],
            'bio' => [
                'id' => "Horas! Saya pemandu lokal di Danau Toba yang siap mengantarkan wisatawan mengenal rumah adat Batak bolon, tarian Sigale-gale, dan situs megalitikum Tomok.",
                'en' => "Horas! Local guide at Lake Toba ready to introduce traditional Batak bolon houses, Sigale-gale puppets, and Tomok stone heritage.",
            ],
            'avatar_url' => '/images/destinations/toba.jpg',
            'cover_image_url' => '/images/destinations/toba.jpg',
            'bank_name' => 'Bank Sumut',
            'bank_account_number' => '2019481920',
            'bank_account_holder' => 'Agus Salim Situmorang',
            'verification_status' => GuideVerificationStatus::Submitted, // Pending review!
            'badge_verified' => false,
        ]);

        GuideVerification::updateOrCreate([
            'guide_profile_id' => $guideAgus->id,
            'document_type' => 'id_card',
        ], [
            'document_number' => '1217018291029102',
            'document_file_path' => '/storage/kyc/ktp_agus.jpg',
            'status' => 'pending',
        ]);
    }
}
