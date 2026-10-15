<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\AdministrativeRegion;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Country;
use App\Models\Destination;
use App\Models\DestinationCategory;
use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Seeder;

class DestinationHierarchySeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();

        // 1. Destination Categories
        $categories = [
            'budaya-sejarah' => [
                'name' => ['id' => 'Wisata Budaya & Sejarah', 'en' => 'Culture & Heritage'],
                'description' => [
                    'id' => 'Peninggalan candi megah, keraton kerajaan, arsitektur masa lampau, dan tradisi lokal.',
                    'en' => 'Ancient temples, royal palaces, historic monuments, and living indigenous traditions.',
                ],
                'icon' => 'landmark',
                'sort' => 1,
            ],
            'alam-pantai' => [
                'name' => ['id' => 'Wisata Alam & Bahari', 'en' => 'Nature & Coastal'],
                'description' => [
                    'id' => 'Pantai pasir putih, tebing karang dramatis, laguna jernih, dan kekayaan laut tropis.',
                    'en' => 'Pristine beaches, dramatic sea cliffs, turquoise lagoons, and world-class marine life.',
                ],
                'icon' => 'waves',
                'sort' => 2,
            ],
            'gunung-petualangan' => [
                'name' => ['id' => 'Gunung & Petualangan', 'en' => 'Volcanoes & Adventure'],
                'description' => [
                    'id' => 'Trekking kaldera aktif, fenomena api biru, sunrise di atas awan, dan jelajah savana.',
                    'en' => 'Active volcanic calderas, rare blue flames, sunrise peaks, and wild savanna trails.',
                ],
                'icon' => 'mountain',
                'sort' => 3,
            ],
            'kuliner' => [
                'name' => ['id' => 'Wisata Kuliner Otentik', 'en' => 'Authentic Culinary'],
                'description' => [
                    'id' => 'Rempah nusantara, makanan tradisional pasar, kopi lokal, dan resep warisan leluhur.',
                    'en' => 'Archipelago spice trails, traditional night markets, single-origin coffees, and royal recipes.',
                ],
                'icon' => 'utensils',
                'sort' => 4,
            ],
            'hidden-gems' => [
                'name' => ['id' => 'Hidden Gems & Desa Wisata', 'en' => 'Hidden Gems & Eco Villages'],
                'description' => [
                    'id' => 'Destinasi tenang jauh dari keramaian, kearifan lokal, dan ekowisata berkelanjutan.',
                    'en' => 'Serene uncrowded sanctuaries, sustainable eco-tourism, and authentic rural hospitality.',
                ],
                'icon' => 'sparkles',
                'sort' => 5,
            ],
        ];

        $catModels = [];
        foreach ($categories as $slug => $data) {
            $catModels[$slug] = DestinationCategory::updateOrCreate(['slug' => $slug], [
                'name' => $data['name'],
                'description' => $data['description'],
                'icon' => $data['icon'],
                'sort_order' => $data['sort'],
            ]);
        }

        // 2. Country: Indonesia
        $indonesia = Country::updateOrCreate(['code' => 'ID'], [
            'slug' => 'indonesia',
            'name' => ['id' => 'Indonesia', 'en' => 'Indonesia'],
            'tagline' => [
                'id' => 'Zamrud Khatulistiwa dengan 17.000+ Pulau dan Budaya Tak Tertandingi',
                'en' => 'The Emerald of the Equator: 17,000+ Islands of Wonder and Ancient Heritage',
            ],
            'summary' => [
                'id' => 'Indonesia adalah negara kepulauan terbesar di dunia yang membentang di sepanjang garis khatulistiwa. Dari keindahan terumbu karang segitiga koral dunia hingga puncak vulkanik yang megah, Indonesia menawarkan keberagaman hayati, ratusan suku bangsa, serta tradisi keramahan yang hangat.',
                'en' => 'Indonesia is the world’s largest archipelagic state, spanning across the equator with over 17,000 islands. Offering rich biodiversity, towering volcanoes, ancient Buddhist and Hindu sanctuaries, vibrant royal sultanates, and hospitable local communities.',
            ],
            'sections' => [
                'history' => [
                    'id' => 'Perjalanan sejarah Indonesia berakar dari peradaban bahari kuno seperti Kerajaan Sriwijaya dan Majapahit yang menyatukan jalur perdagangan rempah dunia. Pengaruh Hindu-Buddha, disusul masuknya Islam dan interaksi dengan penjelajah Eropa, melahirkan mozaik arsitektur dan falsafah hidup yang kaya di setiap pulau.',
                    'en' => 'Indonesia’s history is rooted in great maritime empires like Srivijaya and Majapahit, which commanded global spice trading routes. Centuries of Hindu, Buddhist, Islamic, and European encounters have woven a deeply multifaceted cultural tapestry across the archipelago.',
                ],
                'culture' => [
                    'id' => 'Dengan lebih dari 300 kelompok etnis dan 700 bahasa daerah, falsafah "Bhinneka Tunggal Ika" (Berbeda-beda tetapi tetap satu) tercermin dalam kehidupan sehari-hari, gotong royong, upacara adat sakral, serta seni musik gamelan dan tarian tradisi.',
                    'en' => 'With more than 300 distinct ethnic groups and 700 spoken dialects, the national motto "Unity in Diversity" (Bhinneka Tunggal Ika) is woven into daily life, community collaboration (gotong royong), sacred rituals, and world-renowned artistic crafts.',
                ],
                'cuisine' => [
                    'id' => 'Kaya akan rempah seperti pala, cengkih, serai, dan lengkuas. Cicipi Rendang Minangkabau (salah satu hidangan terlezat di dunia), Sate, Nasi Goreng, Gado-Gado, serta aneka olahan sambal segar khas masing-masing daerah.',
                    'en' => 'Celebrated for its complex spice palettes of nutmeg, clove, lemongrass, and galangal. Savor Minangkabau Rendang, grilled Satay, aromatic Nasi Goreng, fresh Gado-Gado, and regionally distinct sambal chilis.',
                ],
                'climate' => [
                    'id' => 'Beriklim tropis sepanjang tahun dengan dua musim: musim kemarau (Mei–Oktober) yang ideal untuk hiking dan diving, serta musim penghujan (November–April) yang menyuburkan lanskap perbukitan hijau.',
                    'en' => 'Tropical climate year-round with two primary seasons: dry season (May to October), optimal for outdoor trekking and scuba diving; and the wet season (November to April), which turns lush rice terraces vibrant emerald green.',
                ],
                'safety' => [
                    'id' => 'Masyarakat lokal sangat ramah dan menghormati wisatawan. Jagalah kesopanan berpakaian saat memasuki area tempat ibadah atau pura, minum air kemasan terpercaya, dan gunakan jasa pemandu lokal berlisensi resmi Turget.id demi kenyamanan dan perlindungan menyeluruh.',
                    'en' => 'Locals are exceptionally welcoming to respectful visitors. Dress modestly when entering temples or sacred royal sites, consume bottled or filtered water, and always book verified local guides on Turget.id for safe, transparent pricing and reliable guidance.',
                ],
            ],
            'facts' => [
                'currency' => 'IDR (Rupiah)',
                'languages' => 'Bahasa Indonesia (official), English widely understood in tourist centers',
                'timezones' => 'WIB (UTC+7), WITA (UTC+8), WIT (UTC+9)',
                'capital' => 'Jakarta (Nusantara in transition)',
                'visa' => 'Visa on Arrival (VoA) available for 90+ nationalities',
                'emergency_phone' => '112 (National Emergency), 110 (Police), 118 (Ambulance)',
            ],
            'cover_image' => '/images/destinations/bali_hero.jpg',
            'cover_alt' => ['id' => 'Lanskap terasering sawah hijau Bali saat matahari terbit', 'en' => 'Sunrise over lush green rice terraces in Bali, Indonesia'],
            'cover_credit' => 'Turget.id Visual Archive',
            'status' => ContentStatus::Published,
            'is_featured' => true,
            'published_at' => now(),
            'facts_verified_at' => now(),
            'meta' => [
                'title' => ['id' => 'Panduan Wisata Indonesia — Destinasi & Guide Lokal | Turget.id', 'en' => 'Indonesia Travel Guide — Destinations & Local Guides | Turget.id'],
                'description' => ['id' => 'Temukan keindahan destinasi terbaik di Indonesia, dari Bali, Yogyakarta, Bromo hingga Raja Ampat bersama tour guide lokal terverifikasi di Turget.id.', 'en' => 'Discover signature destinations across Indonesia, from Bali and Yogyakarta to Bromo and Raja Ampat with verified local guides on Turget.id.'],
            ],
            'created_by' => $admin?->id,
            'updated_by' => $admin?->id,
        ]);

        // 3. Administrative Regions (Provinces & Cities)
        $regionsData = [
            // Bali
            [
                'type' => 'province',
                'level' => 1,
                'slug' => 'bali',
                'name' => ['id' => 'Bali', 'en' => 'Bali'],
                'tagline' => ['id' => 'Pulau Dewata dengan Harmoni Alam, Pura Sakral, dan Seni', 'en' => 'The Island of the Gods: Sacred Temples, Art, and Coastal Splendor'],
                'summary' => [
                    'id' => 'Bali adalah pusat spiritual dan kebudayaan Hindu Dharma yang terkenal di seluruh dunia. Dikelilingi pantai berpasir putih, tebing laut karang, serta persawahan berundak bertingkat warisan sistem subak.',
                    'en' => 'Bali is a world-renowned spiritual and cultural jewel steeped in Balinese Hindu traditions, iconic sea temples, UNESCO-protected Subak irrigation terraces, and world-class hospitality.',
                ],
                'latitude' => -8.4095178,
                'longitude' => 115.1889160,
                'cover_image' => '/images/destinations/bali_hero.jpg',
                'children' => [
                    [
                        'type' => 'regency',
                        'level' => 2,
                        'slug' => 'badung',
                        'name' => ['id' => 'Kabupaten Badung', 'en' => 'Badung Regency'],
                        'tagline' => ['id' => 'Jantung Pariwisata Pesisir Selatan Bali', 'en' => 'The Epicenter of Southern Bali Coastlines'],
                        'summary' => [
                            'id' => 'Membentang dari pusat pariwisata Kuta, Seminyak, Canggu hingga semenanjung Bukit Uluwatu dengan tebing megah.',
                            'en' => 'Stretching from vibrant Canggu and Seminyak to the soaring ocean cliffs of Uluwatu on the Bukit Peninsula.',
                        ],
                        'latitude' => -8.5833,
                        'longitude' => 115.1833,
                        'cover_image' => '/images/destinations/uluwatu.jpg',
                    ],
                    [
                        'type' => 'regency',
                        'level' => 2,
                        'slug' => 'gianyar',
                        'name' => ['id' => 'Kabupaten Gianyar', 'en' => 'Gianyar Regency'],
                        'tagline' => ['id' => 'Ibukota Seni, Budaya, dan Ketenangan Ubud', 'en' => 'Cultural and Artistic Sanctuary of Ubud'],
                        'summary' => [
                            'id' => 'Pusat kesenian Bali, galeri lukisan, museum, hutan monyet sakral, dan lembah hijau Ubud.',
                            'en' => 'The artistic heart of Bali, home to Ubud’s monkey forest sanctuaries, royal palaces, and sacred ravines.',
                        ],
                        'latitude' => -8.5333,
                        'longitude' => 115.3167,
                        'cover_image' => '/images/destinations/ubud.jpg',
                    ],
                ],
            ],

            // DI Yogyakarta
            [
                'type' => 'special_region',
                'level' => 1,
                'slug' => 'di-yogyakarta',
                'name' => ['id' => 'Daerah Istimewa Yogyakarta', 'en' => 'Special Region of Yogyakarta'],
                'tagline' => ['id' => 'Jiwa Kebudayaan Jawa dengan Keraton yang Agung', 'en' => 'The Soul of Javanese Culture and Royal Dynasties'],
                'summary' => [
                    'id' => 'Daerah istimewa yang dipimpin oleh Sultan Hamengkubuwono, terkenal dengan candi-candi megah peninggalan Mataram Kuno, kesenian batik, kuliner gudeg, dan atmosfer ramah nan bersahaja.',
                    'en' => 'A semi-autonomous special province headed by the Javanese Sultanate, celebrated for ancient temple wonders, courtly batik arts, sweet gudeg culinary heritage, and welcoming academic culture.',
                ],
                'latitude' => -7.7955798,
                'longitude' => 110.3694896,
                'cover_image' => '/images/destinations/yogyakarta.jpg',
                'children' => [
                    [
                        'type' => 'city',
                        'level' => 2,
                        'slug' => 'kota-yogyakarta',
                        'name' => ['id' => 'Kota Yogyakarta', 'en' => 'Yogyakarta City'],
                        'tagline' => ['id' => 'Kawasan Bersejarah Malioboro dan Keraton Sultan', 'en' => 'Historic Malioboro & Royal Palace Heartlands'],
                        'summary' => [
                            'id' => 'Pusat kota dengan Keraton Ngayogyakarta, Taman Sari, jalan legendaris Malioboro, dan sentra kerajinan perak Kotagede.',
                            'en' => 'Urban core housing the Sultan’s Palace (Keraton), the Water Castle (Taman Sari), vibrant Malioboro street, and Kotagede silversmiths.',
                        ],
                        'latitude' => -7.8014,
                        'longitude' => 110.3644,
                        'cover_image' => '/images/destinations/yogyakarta.jpg',
                    ],
                    [
                        'type' => 'regency',
                        'level' => 2,
                        'slug' => 'sleman',
                        'name' => ['id' => 'Kabupaten Sleman', 'en' => 'Sleman Regency'],
                        'tagline' => ['id' => 'Gerbang Candi Prambanan dan Kaki Gunung Merapi', 'en' => 'Gateway to Prambanan Temples and Mount Merapi'],
                        'summary' => [
                            'id' => 'Terletak di utara Yogyakarta, menaungi kompleks candi Hindu terbesar di Indonesia dan jalur wisata Merapi Lava Tour.',
                            'en' => 'Located north of the city, hosting the UNESCO-listed Prambanan Hindu temple complex and Mount Merapi volcano slopes.',
                        ],
                        'latitude' => -7.7167,
                        'longitude' => 110.3556,
                        'cover_image' => '/images/destinations/borobudur.jpg',
                    ],
                ],
            ],

            // Jawa Timur
            [
                'type' => 'province',
                'level' => 1,
                'slug' => 'jawa-timur',
                'name' => ['id' => 'Jawa Timur', 'en' => 'East Java'],
                'tagline' => ['id' => 'Lanskap Gunung Api Spektakuler dan Lautan Pasir Bromo', 'en' => 'Epic Volcanic Landscapes and the Bromo Sand Sea'],
                'summary' => [
                    'id' => 'Provinsi dengan panorama vulkanik paling dramatis di Indonesia, dari kaldera Bromo Tengger Semeru hingga api biru Kawah Ijen.',
                    'en' => 'Home to Indonesia’s most jaw-dropping volcanic scenery, spanning the active craters of Mount Bromo and the mystical blue fire of Ijen Crater.',
                ],
                'latitude' => -7.5360639,
                'longitude' => 112.2384017,
                'cover_image' => '/images/destinations/bromo.jpg',
                'children' => [
                    [
                        'type' => 'regency',
                        'level' => 2,
                        'slug' => 'probolinggo',
                        'name' => ['id' => 'Kabupaten Probolinggo', 'en' => 'Probolinggo Regency'],
                        'tagline' => ['id' => 'Gerbang Utama Menuju Kaldera Gunung Bromo', 'en' => 'Primary Gateway into the Bromo Caldera'],
                        'summary' => [
                            'id' => 'Akses utama menuju Desa Sukapura dan bibir kaldera Bromo dengan pemandangan kabut pagi legendaris.',
                            'en' => 'Key transit region for accessing Sukapura village, viewpoints, and the legendary Bromo sea of sand at daybreak.',
                        ],
                        'latitude' => -7.7547,
                        'longitude' => 113.2159,
                        'cover_image' => '/images/destinations/bromo.jpg',
                    ],
                ],
            ],

            // Nusa Tenggara Timur
            [
                'type' => 'province',
                'level' => 1,
                'slug' => 'nusa-tenggara-timur',
                'name' => ['id' => 'Nusa Tenggara Timur', 'en' => 'East Nusa Tenggara'],
                'tagline' => ['id' => 'Rumah Kadal Raksasa Komodo dan Teluk Eksotis Padar', 'en' => 'Realm of the Komodo Dragon and Padar’s Tri-Color Bays'],
                'summary' => [
                    'id' => 'Gugusan pulau di timur Indonesia dengan kontur savana gersang yang memesona, biota laut kelas dunia, dan habitat kadal purba Komodo.',
                    'en' => 'An exotic island chain characterized by dramatic savanna ridges, world-class diving currents, and the prehistoric Komodo dragon.',
                ],
                'latitude' => -8.6573819,
                'longitude' => 121.0793705,
                'cover_image' => '/images/destinations/padar.jpg',
                'children' => [
                    [
                        'type' => 'regency',
                        'level' => 2,
                        'slug' => 'manggarai-barat',
                        'name' => ['id' => 'Kabupaten Manggarai Barat', 'en' => 'West Manggarai Regency'],
                        'tagline' => ['id' => 'Pintu Gerbang Labuan Bajo dan Taman Nasional Komodo', 'en' => 'Gateway Harbor of Labuan Bajo & Komodo National Park'],
                        'summary' => [
                            'id' => 'Pusat marina Labuan Bajo, titik tolak liveaboard menuju Pulau Padar, Pantai Pink, dan Pulau Rinca.',
                            'en' => 'Harbor town of Labuan Bajo, launching point for liveaboard voyages to Padar Island, Pink Beach, and Komodo sanctuary.',
                        ],
                        'latitude' => -8.5000,
                        'longitude' => 119.8833,
                        'cover_image' => '/images/destinations/padar.jpg',
                    ],
                ],
            ],

            // Papua Barat Daya
            [
                'type' => 'province',
                'level' => 1,
                'slug' => 'papua-barat-daya',
                'name' => ['id' => 'Papua Barat Daya', 'en' => 'Southwest Papua'],
                'tagline' => ['id' => 'Gugusan Karst Terindah di Jantung Segitiga Karang Dunia', 'en' => 'Crown Jewel of the Coral Triangle & Karst Labyrinths'],
                'summary' => [
                    'id' => 'Ibu kota keanekaragaman hayati laut dunia dengan 1.500+ spesies ikan dan gugusan bukit karst di atas air laut sebening kristal.',
                    'en' => 'The ultimate marine biodiversity paradise on Earth, sheltering more than 1,500 reef fish species amidst emerald karst atolls.',
                ],
                'latitude' => -1.1350,
                'longitude' => 131.2500,
                'cover_image' => '/images/destinations/raja_ampat.jpg',
                'children' => [
                    [
                        'type' => 'regency',
                        'level' => 2,
                        'slug' => 'raja-ampat',
                        'name' => ['id' => 'Kabupaten Raja Ampat', 'en' => 'Raja Ampat Regency'],
                        'tagline' => ['id' => 'Empat Raja: Waigeo, Misool, Salawati, dan Batanta', 'en' => 'The Four Kings: Waigeo, Misool, Salawati, and Batanta'],
                        'summary' => [
                            'id' => 'Kawasan konservasi bahari dengan laguna Piaynemo, Pasir Timbul, dan terumbu karang tak terjamah.',
                            'en' => 'Marine protected sanctuary famous for the Piaynemo viewpoint, Wayag lagoons, and pristine diving reefs.',
                        ],
                        'latitude' => -0.5000,
                        'longitude' => 130.5000,
                        'cover_image' => '/images/destinations/raja_ampat.jpg',
                    ],
                ],
            ],

            // Sumatera Utara
            [
                'type' => 'province',
                'level' => 1,
                'slug' => 'sumatera-utara',
                'name' => ['id' => 'Sumatera Utara', 'en' => 'North Sumatra'],
                'tagline' => ['id' => 'Kaldera Danau Vulkanik Terbesar di Dunia dan Budaya Batak', 'en' => 'World’s Largest Volcanic Caldera Lake & Batak Heritage'],
                'summary' => [
                    'id' => 'Menyimpan Danau Toba yang tercipta dari letusan supervulkan dahsyat ribuan tahun silam, dikelilingi perbukitan pinus dan perkampungan adat Batak.',
                    'en' => 'Home to Lake Toba, the largest volcanic lake on Earth created by a supervolcanic eruption, wrapped in pine ridges and Batak culture.',
                ],
                'latitude' => 2.1153547,
                'longitude' => 99.5450974,
                'cover_image' => '/images/destinations/toba.jpg',
                'children' => [
                    [
                        'type' => 'regency',
                        'level' => 2,
                        'slug' => 'samosir',
                        'name' => ['id' => 'Kabupaten Samosir', 'en' => 'Samosir Regency'],
                        'tagline' => ['id' => 'Pulau di Tengah Danau Toba dengan Rumah Adat Bolon', 'en' => 'The Island Within Lake Toba: Heritage of Bolon Houses'],
                        'summary' => [
                            'id' => 'Pulau vulkanik di tengah Danau Toba yang menyimpan peninggalan Raja Sidabutar, tarian Sigale-gale, dan kerajinan tenun Ulos.',
                            'en' => 'Volcanic island in the center of Lake Toba, home to ancient stone tombs, traditional Sigale-gale puppet rituals, and handwoven Ulos.',
                        ],
                        'latitude' => 2.6375,
                        'longitude' => 98.7153,
                        'cover_image' => '/images/destinations/toba.jpg',
                    ],
                ],
            ],

            // DKI Jakarta
            [
                'type' => 'special_region',
                'level' => 1,
                'slug' => 'dki-jakarta',
                'name' => ['id' => 'DKI Jakarta', 'en' => 'Jakarta Special Capital Region'],
                'tagline' => ['id' => 'Metropolis Dinamis Perpaduan Warisan Kolonial dan Modernitas', 'en' => 'Dynamic Metropolis Blending Colonial Heritage and Modern Energy'],
                'summary' => [
                    'id' => 'Ibukota negara yang kaya sejarah maritim Sunda Kelapa, museum nasional, Kota Tua peninggalan era Batavia, dan kuliner Betawi yang lezat.',
                    'en' => 'Vibrant economic and cultural hub home to historic Batavia old town, the National Monument, colonial museums, and Betawi street food.',
                ],
                'latitude' => -6.2087634,
                'longitude' => 106.8455990,
                'cover_image' => '/images/destinations/jakarta.jpg',
                'children' => [
                    [
                        'type' => 'city',
                        'level' => 2,
                        'slug' => 'jakarta-pusat',
                        'name' => ['id' => 'Jakarta Pusat', 'en' => 'Central Jakarta'],
                        'tagline' => ['id' => 'Pusat Pemerintahan, Monas, dan Museum Bersejarah', 'en' => 'Monas National Monument & Civic Center'],
                        'summary' => [
                            'id' => 'Kawasan jantung kota dengan Monas, Masjid Istiqlal, Katedral Jakarta, dan museum-museum nasional.',
                            'en' => 'Heart of the capital featuring the National Monument (Monas), Istiqlal Mosque, Jakarta Cathedral, and national galleries.',
                        ],
                        'latitude' => -6.1805,
                        'longitude' => 106.8284,
                        'cover_image' => '/images/destinations/jakarta.jpg',
                    ],
                ],
            ],
        ];

        $regModels = [];
        foreach ($regionsData as $r) {
            $children = $r['children'] ?? [];
            unset($r['children']);

            $region = AdministrativeRegion::updateOrCreate(
                ['country_id' => $indonesia->id, 'slug' => $r['slug']],
                array_merge($r, [
                    'status' => ContentStatus::Published,
                    'is_featured' => true,
                    'published_at' => now(),
                    'created_by' => $admin?->id,
                    'updated_by' => $admin?->id,
                ])
            );
            $regModels[$r['slug']] = $region;

            foreach ($children as $c) {
                $child = AdministrativeRegion::updateOrCreate(
                    ['country_id' => $indonesia->id, 'slug' => $c['slug']],
                    array_merge($c, [
                        'parent_id' => $region->id,
                        'status' => ContentStatus::Published,
                        'is_featured' => true,
                        'published_at' => now(),
                        'created_by' => $admin?->id,
                        'updated_by' => $admin?->id,
                    ])
                );
                $regModels[$c['slug']] = $child;
            }
        }

        // 4. Destinations with authentic details and real GPS coordinates
        $destinations = [
            [
                'slug' => 'pura-luhur-uluwatu',
                'region_slug' => 'badung',
                'categories' => ['budaya-sejarah', 'alam-pantai'],
                'name' => ['id' => 'Pura Luhur Uluwatu', 'en' => 'Uluwatu Temple (Pura Luhur Uluwatu)'],
                'tagline' => [
                    'id' => 'Kuil Tebing Laut Keramat dengan Panorama Sunset dan Tari Kecak Spektakuler',
                    'en' => 'Sacred Cliff-Edge Temple with Dramatic Sunsets and Hypnotic Kecak Dance',
                ],
                'summary' => [
                    'id' => 'Pura Luhur Uluwatu berdiri kokoh di puncak tebing karang setinggi 70 meter yang menjorok langsung ke Samudra Hindia. Dibangun pada abad ke-11 oleh Mpu Kuturan dan diperluas oleh Dang Hyang Nirartha, pura ini merupakan salah satu Pura Sad Kahyangan penjaga spiritual pulau Bali.',
                    'en' => 'Perched atop a 70-meter steep limestone cliff dropping straight into the Indian Ocean, Uluwatu Temple is one of Bali’s nine directional spiritual pillars (Sad Kahyangan), revered for its dramatic sunsets and open-air sunset Kecak fire dance performances.',
                ],
                'sections' => [
                    'history' => [
                        'id' => 'Didirikan oleh Mpu Kuturan pada abad ke-11 dan disempurnakan oleh Dang Hyang Nirartha pada abad ke-16. Di tempat inilah Dang Hyang Nirartha dipercaya mencapai "Moksa" (kebebasan spiritual tertinggi). Arsitektur gapura candi bentar yang menghadap ke laut dirancang selaras dengan tebing alami.',
                        'en' => 'Originally established by Mpu Kuturan in the 11th century and later expanded by sage Dang Hyang Nirartha in the 16th century, who is believed to have achieved liberation (Moksha) on these sacred cliffs. The ancient stone shrines harmonize directly with the limestone precipice.',
                    ],
                    'culture' => [
                        'id' => 'Hutan kecil di sekitarnya dihuni oleh ratusan kera abu-abu yang dianggap sebagai penjaga pura. Setiap sore menjelang matahari terbenam, panggung terbuka menampilkan Tari Kecak Ramayana berlatar cahaya senja laut selatan yang memukau.',
                        'en' => 'The surrounding coastal forest is inhabited by monkeys considered temple guardians. Every evening before twilight, the amphitheater hosts the mesmerizing Ramayana Kecak fire performance against the glowing ocean sunset.',
                    ],
                    'tips' => [
                        'id' => 'Pengunjung diwajibkan mengenakan kain sarung dan selendang (disediakan di loket). Amankan kacamata, topi, dan barang berkilau dari keusilan kera penunggu.',
                        'en' => 'Modest dress is required; traditional sarongs and sashes are provided at the entrance. Keep eyewear, hats, and small loose items securely stowed away from playful resident monkeys.',
                    ],
                ],
                'highlights' => [
                    'id' => ['Panorama tebing laut Samudra Hindia 70 meter', 'Pertunjukan Tari Kecak Api saat sunset', 'Arsitektur batu karang hitam kuno abad ke-11', 'Spot foto matahari terbenam terbaik di Bali Selatan'],
                    'en' => ['70-meter vertical ocean cliff edge', 'Sunset Kecak fire dance amphitheater', 'Ancient 11th-century black stone architecture', 'Premier sunset viewpoint in southern Bali'],
                ],
                'activities' => [
                    'id' => ['Menonton Tari Kecak saat senja', 'Menyusuri jalur setapak di bibir tebing', 'Fotografi lanskap sunset samudra', 'Mempelajari nilai filosofi Sad Kahyangan bersama guide lokal'],
                    'en' => ['Watch the twilight Kecak dance', 'Stroll the paved cliff-edge promenade', 'Sunset seascape photography', 'Learn Balinese sacred philosophies with a licensed guide'],
                ],
                'address' => 'Pecatu, Kec. Kuta Selatan, Kabupaten Badung, Bali 80361',
                'latitude' => -8.8291000,
                'longitude' => 115.0849000,
                'opening_hours' => ['id' => 'Setiap hari: 07.00 - 19.00 WITA (Tari Kecak: 18.00 WITA)', 'en' => 'Daily: 07:00 AM - 07:00 PM (Kecak dance at 06:00 PM)'],
                'ticket_info' => ['id' => 'Tiket masuk Rp50.000 (Domestik), Tiket Tari Kecak Rp150.000', 'en' => 'Temple entrance IDR 50,000; Kecak dance ticket IDR 150,000'],
                'visit_duration_minutes' => 120,
                'popularity_score' => 98,
                'cover_image' => '/images/destinations/uluwatu.jpg',
                'cover_alt' => ['id' => 'Pura Luhur Uluwatu di bibir tebing laut saat matahari terbenam', 'en' => 'Uluwatu Temple cliff face overlooking the sunset ocean'],
                'cover_credit' => 'Turget.id Photography Archive',
            ],

            [
                'slug' => 'sacred-monkey-forest-ubud',
                'region_slug' => 'gianyar',
                'categories' => ['budaya-sejarah', 'alam-pantai', 'hidden-gems'],
                'name' => ['id' => 'Sacred Monkey Forest Ubud', 'en' => 'Sacred Monkey Forest Sanctuary Ubud'],
                'tagline' => [
                    'id' => 'Cagar Alam Suci dan Suaka Kera Ekor Panjang di Jantung Hutan Tropis Ubud',
                    'en' => 'Ancient Forest Sanctuary & Sacred Balinese Macaque Haven'],
                'summary' => [
                    'id' => 'Cagar alam seluas 12,5 hektar di pusat Ubud yang menaungi lebih dari 1.000 kera ekor panjang (Macaca fascicularis) serta tiga pura kuno abad ke-14 yang diselimuti lumut hijau dan pohon beringin raksasa.',
                    'en' => 'A 12.5-hectare sacred forest sanctuary in downtown Ubud, home to over 1,000 Balinese long-tailed macaques and three 14th-century moss-draped temples rooted in the Hindu philosophy of Tri Hita Karana.',
                ],
                'sections' => [
                    'history' => [
                        'id' => 'Berdasarkan prasasti Pura Purantara, kawasan suci ini telah digunakan sejak pertengahan abad ke-14 pada era Dinasti Pejeng. Pohon-pohon tua berusia ratusan tahun dijaga sebagai bagian dari pelestarian hutan suci mandala.',
                        'en' => 'Historical inscriptions trace the temples back to the mid-14th century during the Pejeng Dynasty. Massive centuries-old banyan trees and moss-covered stone carvings create an ethereal atmosphere.',
                    ],
                    'culture' => [
                        'id' => 'Kawasan ini mempraktikkan filosofi Tri Hita Karana: menjaga keharmonisan antara manusia dengan Tuhan (Pura Dalem Agung Padangtegal), sesama manusia, dan alam lingkungan (konservasi flora dan fauna).',
                        'en' => 'The sanctuary is managed under the Tri Hita Karana doctrine: maintaining spiritual harmony with the Divine, community harmony with people, and ecological harmony with nature.',
                    ],
                ],
                'highlights' => [
                    'id' => ['Interaksi dengan 1.000+ kera ekor panjang dalam habitat alaminya', 'Tiga pura kuno berarsitektur khas Bali', 'Jembatan kayu di atas ngarai hutan tropis yang rimbun', 'Pohon beringin raksasa dan patung naga legendaris'],
                    'en' => ['Observe 1,000+ macaques in their natural forest home', 'Three ancient 14th-century Hindu temples', 'Suspended wooden walkways over lush stream ravines', 'Centuries-old banyans and mossy guardian dragon statues'],
                ],
                'activities' => [
                    'id' => ['Jalan santai di bawah kanopi hutan sejuk', 'Fotografi satwa dan arsitektur kuno', 'Eksplorasi jembatan kayu dan ngarai', 'Belajar filosofi Tri Hita Karana'],
                    'en' => ['Nature walk under cool jungle canopies', 'Wildlife and mossy temple photography', 'Explore forest ravine boardwalks', 'Discover Tri Hita Karana conservation philosophy'],
                ],
                'address' => 'Jl. Monkey Forest, Ubud, Kecamatan Gianyar, Bali 80571',
                'latitude' => -8.5186000,
                'longitude' => 115.2585000,
                'opening_hours' => ['id' => 'Setiap hari: 09.00 - 18.00 WITA (Loket tutup 17.00)', 'en' => 'Daily: 09:00 AM - 06:00 PM (Ticket booth closes 05:00 PM)'],
                'ticket_info' => ['id' => 'Dewasa: Rp80.000 (weekday) / Rp100.000 (weekend)', 'en' => 'Adults: IDR 80,000 (weekdays) / IDR 100,000 (weekends)'],
                'visit_duration_minutes' => 90,
                'popularity_score' => 94,
                'cover_image' => '/images/destinations/ubud.jpg',
                'cover_alt' => ['id' => 'Candi batu berlumut dan kera di Monkey Forest Ubud', 'en' => 'Moss-covered stone temple and macaque in Monkey Forest Ubud'],
                'cover_credit' => 'Turget.id Archive',
            ],

            [
                'slug' => 'candi-borobudur',
                'region_slug' => 'sleman',
                'categories' => ['budaya-sejarah'],
                'name' => ['id' => 'Candi Borobudur', 'en' => 'Borobudur Temple'],
                'tagline' => [
                    'id' => 'Monumen Buddha Terbesar di Dunia dan Mahakarya Warisan Dunia UNESCO',
                    'en' => 'World’s Largest Buddhist Monument and UNESCO World Heritage Masterpiece',
                ],
                'summary' => [
                    'id' => 'Candi Borobudur dibangun pada abad ke-8 dan ke-9 Masehi oleh Dinasti Syailendra. Struktur piramida berundak ini tersusun dari lebih dari 2 juta blok batu andesit dengan 2.672 panel relief naratif dan 504 arca Buddha.',
                    'en' => 'Built in the 8th and 9th centuries by the Sailendra Dynasty, Borobudur is the world’s largest Buddhist monument. Crafted from over two million andesite stone blocks, it features 2,672 narrative bas-relief panels and 504 Buddha statues crowned by concentric stupas.',
                ],
                'sections' => [
                    'history' => [
                        'id' => 'Candi ini sempat tertimbun abu vulkanik Gunung Merapi dan vegetasi lebat selama berabad-abad sebelum ditemukan kembali pada tahun 1814 oleh Sir Thomas Stamford Raffles. Pemugaran berskala besar dilakukan oleh Pemerintah Indonesia bersama UNESCO pada tahun 1973–1983.',
                        'en' => 'The monument lay buried beneath Merapi volcanic ash and dense jungle for centuries until rediscovery in 1814 by Sir Thomas Stamford Raffles. A monumental restoration campaign spearheaded by the Indonesian government and UNESCO took place between 1973 and 1983.',
                    ],
                    'culture' => [
                        'id' => 'Struktur candi mencerminkan kosmologi Buddhis dalam tiga tingkatan: Kamadhatu (dunia hasrat), Rupadhatu (dunia berwujud), dan Arupadhatu (dunia tanpa wujud/pencerahan tertinggi). Setiap tahun menjadi pusat perayaan Hari Raya Waisak internasional.',
                        'en' => 'The architectural layout maps Buddhist cosmology across three realms: Kamadhatu (desire), Rupadhatu (form), and Arupadhatu (formlessness/nirvana). It serves as the spiritual epicenter for the international Vesak full moon procession every year.',
                    ],
                ],
                'highlights' => [
                    'id' => ['504 patung Buddha dan stupa utama yang megah', '2.672 panel relief pahatan batu terpanjang di dunia', 'Pemandangan sunrise spektakuler berlatar Gunung Merapi', 'Kompleks cagar budaya warisan dunia UNESCO'],
                    'en' => ['504 Buddha images under bell-shaped stupas', '2,672 intricate narrative stone relief panels', 'Sunrise over misty hills with Mount Merapi in the distance', 'UNESCO World Heritage monument'],
                ],
                'activities' => [
                    'id' => ['Tur pemandu edukasi relief (naik monumen)', 'Sunrise tour pagi hari dari Bukit Punthuk Setumbu', 'Menjelajah desa wisata di sekitar candi dengan sepeda ontel', 'Belajar memahat batu dan membuat gerabah tradisional'],
                    'en' => ['Guided architectural and relief study tours', 'Sunrise photography from nearby Punthuk Setumbu hill', 'Vintage bicycle rides through surrounding heritage villages', 'Traditional pottery workshops'],
                ],
                'address' => 'Jl. Badrawati, Borobudur, Magelang, Jawa Tengah (Akses utama via Yogyakarta)',
                'latitude' => -7.6079000,
                'longitude' => 110.2038000,
                'opening_hours' => ['id' => 'Setiap hari: 06.30 - 17.00 WIB (Kunjungan naik candi dengan kuota terbatas)', 'en' => 'Daily: 06:30 AM - 05:00 PM (Monument climb requires pre-booked slot)'],
                'ticket_info' => ['id' => 'Halaman candi: Rp50.000 / Naik ke monumen (kuota khusus): Rp150.000 (Domestik)', 'en' => 'Grounds pass: IDR 50,000 / Monument climb ticket: IDR 450,000 (Foreigners)'],
                'visit_duration_minutes' => 150,
                'popularity_score' => 99,
                'cover_image' => '/images/destinations/borobudur.jpg',
                'cover_alt' => ['id' => 'Deretan stupa Candi Borobudur berkabut saat matahari terbit', 'en' => 'Sunrise stupas of Borobudur temple shrouded in morning mist'],
                'cover_credit' => 'Turget.id Archive',
            ],

            [
                'slug' => 'keraton-ngayogyakarta-hadiningrat',
                'region_slug' => 'kota-yogyakarta',
                'categories' => ['budaya-sejarah', 'kuliner'],
                'name' => ['id' => 'Keraton Ngayogyakarta Hadiningrat', 'en' => 'The Royal Palace of Yogyakarta (Keraton)'],
                'tagline' => [
                    'id' => 'Istana Kerajaan Jawa yang Hidup, Sakral, dan Pusat Kebudayaan Mataram',
                    'en' => 'Living Palace of the Javanese Sultanate and Heart of Mataram Culture',
                ],
                'summary' => [
                    'id' => 'Keraton Yogyakarta didirikan oleh Sri Sultan Hamengkubuwono I pada tahun 1755. Istana ini bukan sekadar museum, melainkan tempat tinggal resmi Sultan yang masih menjalankan fungsi kepemimpinan budaya dan adat Jawa hingga saat ini.',
                    'en' => 'Founded in 1755 by Sultan Hamengkubuwono I following the Giyanti Treaty, the Keraton of Yogyakarta remains an active, living royal residence and the guardian of Javanese classical arts, courtly etiquette, and philosophy.',
                ],
                'sections' => [
                    'history' => [
                        'id' => 'Tata ruang keraton dirancang berdasarkan garis poros imajiner (Sumbu Filosofi) yang menghubungkan Gunung Merapi di utara, Keraton di tengah, dan Laut Selatan (Samudra Hindia), yang telah resmi diakui UNESCO sebagai Warisan Dunia pada tahun 2023.',
                        'en' => 'The royal complex sits on Yogyakarta’s sacred Cosmological Axis (Sumbu Filosofi), linking Mount Merapi in the north to the South Sea. This living urban axis was inscribed as a UNESCO World Heritage site in 2023.',
                    ],
                    'culture' => [
                        'id' => 'Setiap pagi hari, pengunjung dapat menyaksikan pertunjukan kesenian klasik seperti karawitan gamelan, tari beksan kraton, dan wayang kulit yang dibawakan oleh abdi dalem berbusana tradisional peranakan.',
                        'en' => 'Daily morning cultural showcases take place within open pavilions (Bangsal), featuring live gamelan music, courtly dances, and shadow puppetry presented by traditional royal courtiers (Abdi Dalem).',
                    ],
                ],
                'highlights' => [
                    'id' => ['Koleksi pusaka, batik keraton, dan kereta kencana kesultanan', 'Kompleks paviliun berarsitektur Joglo klasik yang megah', 'Pertunjukan gamelan dan tari tradisional setiap hari', 'Sumbu Filosofi Yogyakarta warisan dunia UNESCO'],
                    'en' => ['Royal heirlooms, court batiks, and ornate gilded carriages', 'Monumental teakwood pavilions (Bangsal) and carved pillars', 'Daily morning classical gamelan performances', 'UNESCO-inscribed Cosmological Axis heritage'],
                ],
                'activities' => [
                    'id' => ['Tur keliling museum dipandu abdi dalem', 'Menyaksikan pertunjukan gamelan di Bangsal Sri Manganti', 'Mencicipi kuliner favorit raja di Bale Raos', 'Berfoto dengan arsitektur klasik Jawa'],
                    'en' => ['Palace museum tour guided by traditional courtiers', 'Attend morning court music in the pavilion', 'Taste royal cuisine at nearby Bale Raos', 'Classical Javanese architectural photography'],
                ],
                'address' => 'Jl. Rotowijayan Blok No. 1, Panembahan, Kraton, Kota Yogyakarta 55132',
                'latitude' => -7.8053000,
                'longitude' => 110.3642000,
                'opening_hours' => ['id' => 'Selasa - Minggu: 08.30 - 14.30 WIB (Senin tutup untuk pemeliharaan ritual)', 'en' => 'Tuesday - Sunday: 08:30 AM - 02:30 PM (Closed Mondays)'],
                'ticket_info' => ['id' => 'Tiket masuk Rp15.000 (Domestik) / Rp25.000 (Mancanegara)', 'en' => 'Entrance IDR 15,000 (Domestic) / IDR 25,000 (International)'],
                'visit_duration_minutes' => 120,
                'popularity_score' => 93,
                'cover_image' => '/images/destinations/yogyakarta.jpg',
                'cover_alt' => ['id' => 'Pendopo berarsitektur Joglo klasik di Keraton Yogyakarta malam hari', 'en' => 'Classical Joglo royal pavilion in Yogyakarta at blue hour'],
                'cover_credit' => 'Turget.id Photography Archive',
            ],

            [
                'slug' => 'gunung-bromo-tengger',
                'region_slug' => 'probolinggo',
                'categories' => ['gunung-petualangan', 'budaya-sejarah'],
                'name' => ['id' => 'Taman Nasional Bromo Tengger Semeru', 'en' => 'Mount Bromo & Tengger Caldera'],
                'tagline' => [
                    'id' => 'Matahari Terbit di Atas Lautan Pasir Kaldera dan Kawah Berasap Magis',
                    'en' => 'Iconic Volcanic Caldera, Vast Sea of Sand, and Mystical Sunrise Over the Clouds',
                ],
                'summary' => [
                    'id' => 'Gunung Bromo (2.329 mdpl) adalah gunung api aktif yang terletak di dalam kaldera raksasa Tengger seluas 10 kilometer persegi. Lanskap gurun pasir vulkanik berpadu dengan pemandangan Gunung Batok yang bergelombang dan Gunung Semeru yang menjulang tinggi di kejauhan.',
                    'en' => 'Mount Bromo (2,329m) is an active volcano nestled inside the ancient 10-kilometer-wide Tengger caldera in East Java. Famous for its lunar-like sea of sand, smoking sulfur crater, and otherworldly sunrise panoramas framed by towering Mount Semeru.',
                ],
                'sections' => [
                    'history' => [
                        'id' => 'Masyarakat suku asli Tengger meyakini kawah Bromo sebagai tempat suci persembahan Roro Anteng dan Joko Seger. Setiap tahun digelar upacara Yadnya Kasada, di mana warga melarung hasil bumi dan ternak ke dalam kawah sebagai wujud syukur.',
                        'en' => 'The indigenous Hindu Tenggerese people hold Mount Bromo sacred, tracing their lineage to Princess Roro Anteng and Joko Seger. The annual Yadnya Kasada festival witnesses pilgrims climbing the crater rim to cast offerings into the smoking caldera.',
                    ],
                    'tips' => [
                        'id' => 'Suhu udara dini hari bisa mencapai 3–10°C. Siapkan jaket tebal, sarung tangan, syal, dan masker penutup debu pasir vulkanik. Gunakan jeep 4x4 terverifikasi Turget.id untuk melintasi lautan pasir dengan aman.',
                        'en' => 'Early morning temperatures plunge to 3–10°C (37–50°F). Pack thermal layers, gloves, and a dust mask for the volcanic ash. Always book verified 4WD Jeep shuttles on Turget.id for safe dune crossings.',
                    ],
                ],
                'highlights' => [
                    'id' => ['Sunrise legendaris dari Puncak Penanjakan / Bukit Kingkong', 'Mendaki 250 anak tangga menuju bibir kawah aktif', 'Sensasi melintasi Lautan Pasir (Pasir Berbisik) dengan Jeep 4x4', 'Bukit Teletubbies dan padang savana hijau'],
                    'en' => ['Sunrise from Penanjakan Viewpoint over misty caldera', 'Climb 250 stairs to the edge of the roaring active crater', '4WD Jeep safari across the whispering sea of sand', 'Lush green rolling hills of Teletubbies Savanna'],
                ],
                'activities' => [
                    'id' => ['Jeep sunrise tour dini hari', 'Trekking ke bibir kawah aktif', 'Fotografi astrofotografi bintang Bima Sakti (Milky Way)', 'Mengenal adat istiadat warga Tengger'],
                    'en' => ['Early morning 4x4 Jeep sunrise tour', 'Hike up to the active crater rim', 'Milky Way astrophotography before dawn', 'Experience authentic Tenggerese community hospitality'],
                ],
                'address' => 'Kawasan Bromo Tengger Semeru, Sukapura, Kabupaten Probolinggo, Jawa Timur',
                'latitude' => -7.9425000,
                'longitude' => 112.9530000,
                'opening_hours' => ['id' => 'Buka 24 jam (Aktivitas sunrise dimulai pukul 03.00 WIB)', 'en' => 'Open 24 hours (Sunrise tours depart around 03:00 AM)'],
                'ticket_info' => ['id' => 'Tiket TNBTS: Rp29.000 (weekday) / Rp34.000 (weekend) Domestik', 'en' => 'National Park permit: IDR 220,000 (weekday) / IDR 320,000 (weekend) Foreigners'],
                'visit_duration_minutes' => 300,
                'popularity_score' => 97,
                'cover_image' => '/images/destinations/bromo.jpg',
                'cover_alt' => ['id' => 'Matahari terbit di kaldera Bromo dengan kawah berasap', 'en' => 'Mount Bromo sunrise with steaming active crater and sea of sand'],
                'cover_credit' => 'Turget.id Archive',
            ],

            [
                'slug' => 'pulau-padar-komodo',
                'region_slug' => 'manggarai-barat',
                'categories' => ['alam-pantai', 'gunung-petualangan'],
                'name' => ['id' => 'Pulau Padar — Taman Nasional Komodo', 'en' => 'Padar Island — Komodo National Park'],
                'tagline' => [
                    'id' => 'Ikon Savana Eksotis dengan Tiga Teluk Berpasir Warna-warni',
                    'en' => 'Iconic Jagged Ridges Framing Three Tri-Color Sand Bays',
                ],
                'summary' => [
                    'id' => 'Pulau Padar adalah pulau terbesar ketiga di kawasan Taman Nasional Komodo. Dari puncak bukitnya, tersaji panorama magis tiga teluk melengkung dengan warna pasir berbeda: pasir putih, pasir arang gelap vulkanik, dan pasir merah muda (pink sand).',
                    'en' => 'Padar is the third-largest island in the UNESCO-recognized Komodo National Park. A ridge hike leads to one of Southeast Asia’s most iconic viewpoints, revealing three curving natural bays each featuring distinct sand tones: white, charcoal black, and rare rose-pink.',
                ],
                'sections' => [
                    'history' => [
                        'id' => 'Taman Nasional Komodo didirikan pada tahun 1980 dan ditetapkan sebagai Situs Warisan Dunia UNESCO pada tahun 1991 untuk melindungi kadal terbesar di dunia, Varanus komodoensis, serta ekosistem terumbu karang tropisnya yang sangat kaya.',
                        'en' => 'Established in 1980 and inscribed as a UNESCO World Heritage site in 1991, the park protects the endangered Komodo dragon (Varanus komodoensis) and one of the world’s most biologically rich coral reef currents.',
                    ],
                    'tips' => [
                        'id' => 'Pendakian menuju puncak membutuhkan waktu sekitar 30–45 menit menaiki tangga kayu dan batu. Disarankan naik saat pagi hari (06.00 WITA) sebelum matahari terik menyengat bukit savana.',
                        'en' => 'The trek up consists of roughly 800 stepped stones taking 30 to 45 minutes. Ascend at dawn (06:00 AM) to catch gentle morning golden hour light and avoid the midday equatorial heat.',
                    ],
                ],
                'highlights' => [
                    'id' => ['Panorama tiga teluk melengkung dari puncak bukit', 'Pantai Pink (Pink Beach) dengan pasir merah alami', 'Habitat bawah laut pari manta dan terumbu karang', 'Matahari terbit keemasan di atas savana tropis'],
                    'en' => ['Panoramic view over three crescent bays from the ridge summit', 'Access to rare natural Pink Beach', 'World-class manta ray snorkeling nearby', 'Golden sunrise illuminating wild savanna contours'],
                ],
                'activities' => [
                    'id' => ['Trekking sunrise ke puncak bukit Padar', 'Snorkeling di Pink Beach dan Manta Point', 'Liveaboard trip menginap di kapal pinisi tradisional', 'Fotografi lanskap kepulauan tropis'],
                    'en' => ['Sunrise ridge hike', 'Snorkel at Pink Beach and Manta Point', 'Multi-day sailing expedition on a handcrafted Phinisi boat', 'Aerial and panoramic photography'],
                ],
                'address' => 'Taman Nasional Komodo, Kabupaten Manggarai Barat, Nusa Tenggara Timur (Akses via Labuan Bajo)',
                'latitude' => -8.6533000,
                'longitude' => 119.5719000,
                'opening_hours' => ['id' => 'Setiap hari: 06.00 - 18.00 WITA (Tergantung jadwal kapal pelayaran)', 'en' => 'Daily: 06:00 AM - 06:00 PM (Subject to sailing schedules)'],
                'ticket_info' => ['id' => 'Tiket masuk Taman Nasional Komodo terpadu via Labuan Bajo', 'en' => 'Integrated Komodo National Park conservation pass'],
                'visit_duration_minutes' => 180,
                'popularity_score' => 96,
                'cover_image' => '/images/destinations/padar.jpg',
                'cover_alt' => ['id' => 'Pemandangan spektakuler tiga teluk melengkung Pulau Padar', 'en' => 'Aerial view of Padar Island curved bays and turquoise ocean'],
                'cover_credit' => 'Turget.id Archive',
            ],

            [
                'slug' => 'piaynemo-raja-ampat',
                'region_slug' => 'raja-ampat',
                'categories' => ['alam-pantai', 'hidden-gems'],
                'name' => ['id' => 'Kepulauan Piaynemo Raja Ampat', 'en' => 'Piaynemo Karst Islands — Raja Ampat'],
                'tagline' => [
                    'id' => 'Gugusan Bukit Karst Jamur Zamrud di Atas Laguna Sebening Kaca',
                    'en' => 'Emerald Karst Islets Rising from Crystalline Turquoise Lagoons',
                ],
                'summary' => [
                    'id' => 'Piaynemo sering disebut sebagai "Mini Wayag" yang dapat diakses lebih mudah. Ratusan pulau karang karst hijau menyembul dari laut dangkal dengan gradasi warna toska dan biru safir yang menghipnotis mata.',
                    'en' => 'Known as the iconic viewpoint of Raja Ampat, Piaynemo features scores of mushroom-shaped limestone karst islets rising dramatically out of shallow crystal-clear lagoons of azure and sapphire.',
                ],
                'sections' => [
                    'culture' => [
                        'id' => 'Dikelola bersama oleh masyarakat adat setempat melalui sistem sasi laut (tradisi adat menjaga dan membatasi penangkapan ikan demi keberlanjutan sumber daya alam).',
                        'en' => 'Protected collaboratively by indigenous Papuan communities under the traditional Sasi conservation law, ensuring fish and reef sustainability across generations.',
                    ],
                ],
                'highlights' => [
                    'id' => ['Tangga kayu 320 anak tangga menuju gardu pandang puncak', 'Teluk Bintang (Star Lagoon) yang berbentuk bintang alami', 'Snorkeling di terumbu karang terpadat di dunia', 'Suasana tenang jauh dari polusi kota'],
                    'en' => ['Comfortable 320-step wooden walkway to panoramic viewing deck', 'Natural Star-shaped marine lagoon nearby', 'Snorkel untouched coral gardens with tropical reef fish', 'Pristine, untouched natural environment'],
                ],
                'activities' => [
                    'id' => ['Mendaki ke gardu pandang panorama Piaynemo', 'Bermain kano/kayak di antara pulau karang', 'Diving dan snorkeling bersama penyu dan ikan karang', 'Membeli kelapa muda dari warga lokal'],
                    'en' => ['Climb to Piaynemo viewpoint summit', 'Sea kayaking through karst corridors', 'Snorkel and scuba dive world-class coral walls', 'Sip fresh young coconuts prepared by local islanders'],
                ],
                'address' => 'Distrik Waigeo Barat Kepulauan, Kabupaten Raja Ampat, Papua Barat Daya',
                'latitude' => -0.5630000,
                'longitude' => 130.2720000,
                'opening_hours' => ['id' => 'Setiap hari: 07.00 - 17.00 WIT', 'en' => 'Daily: 07:00 AM - 05:00 PM'],
                'ticket_info' => ['id' => 'Retribusi Desa Wisata Piaynemo Rp300.000/kapal + Kartu Tarif Layanan Konservasi BLUD', 'en' => 'Village entry fee + Raja Ampat Marine Conservation Card'],
                'visit_duration_minutes' => 180,
                'popularity_score' => 95,
                'cover_image' => '/images/destinations/raja_ampat.jpg',
                'cover_alt' => ['id' => 'Gugusan pulau karang karst Piaynemo Raja Ampat di atas laut toska', 'en' => 'Aerial view of Piaynemo karst islands over turquoise lagoon in Raja Ampat'],
                'cover_credit' => 'Turget.id Archive',
            ],

            [
                'slug' => 'danau-toba-samosir',
                'region_slug' => 'samosir',
                'categories' => ['budaya-sejarah', 'alam-pantai', 'kuliner'],
                'name' => ['id' => 'Danau Toba & Pulau Samosir', 'en' => 'Lake Toba & Samosir Island'],
                'tagline' => [
                    'id' => 'Keajaiban Kaldera Vulkanik Terbesar Dunia dan Tradisi Leluhur Batak',
                    'en' => 'World’s Largest Volcanic Caldera & Living Batak Heritage',
                ],
                'summary' => [
                    'id' => 'Danau Toba memiliki panjang 100 kilometer dan kedalaman hingga 505 meter. Di tengahnya bertengger Pulau Samosir dengan perkampungan kuno Batak Toba, rumah panggung beratap tanduk kerbau (Rumah Bolon), dan makam batu tua.',
                    'en' => 'Stretching 100 kilometers long and over 500 meters deep, Lake Toba is the Earth’s largest volcanic caldera lake. In its center lies Samosir Island, preserving centuries of Batak Toba traditions, iconic horned-roof Bolon timber houses, and sacred stone tomb monoliths.',
                ],
                'sections' => [
                    'history' => [
                        'id' => 'Terbentuk dari letusan dahsyat supervulkan Toba sekitar 74.000 tahun lalu yang mengubah iklim global. Di Desa Tomok, Samosir, terdapat Makam Batu Raja Sidabutar yang telah berusia ratusan tahun tanpa sambungan semen.',
                        'en' => 'Formed by a colossal supervolcanic explosion roughly 74,000 years ago that altered global climate patterns. In Tomok village, monolithic stone sarcophagi of King Sidabutar stand intact after centuries.',
                    ],
                ],
                'highlights' => [
                    'id' => ['Pemandangan danau kaldera biru luas berpagar perbukitan hijau', 'Desa Wisata Tomok dan Ambarita dengan kursi sidang batu kuno', 'Pertunjukan tarian boneka mistis Sigale-gale', 'Sentra tenun kain Ulos tradisional'],
                    'en' => ['Vast caldera lake surrounded by cool pine-clad ridges', 'Tomok & Ambarita heritage stone courts of justice', 'Traditional mystical Sigale-gale puppet dance performances', 'Centuries-old authentic Ulos handweaving'],
                ],
                'activities' => [
                    'id' => ['Menyeberang danau dengan kapal feri tradisional', 'Menyewa skuter keliling Pulau Samosir', 'Menikmati kuliner ikan arsik dan kopi Lintong Toba', 'Mempelajari aksara dan falsafah Batak'],
                    'en' => ['Scenic ferry boat crossing across calm deep waters', 'Scooter tour circling peaceful Samosir lake roads', 'Sample traditional Arsik fish and single-origin Lintong coffee', 'Learn ancient Batak script and clan philosophies'],
                ],
                'address' => 'Kabupaten Samosir, Sumatera Utara (Akses via Parapat / Bandara Silangit)',
                'latitude' => 2.6500000,
                'longitude' => 98.7000000,
                'opening_hours' => ['id' => 'Buka sepanjang tahun (Wisata desa 08.00 - 17.00 WIB)', 'en' => 'Open year round (Village attractions 08:00 AM - 05:00 PM)'],
                'ticket_info' => ['id' => 'Tiket kapal penyeberangan Rp15.000 - Rp25.000, Tiket masuk desa budaya Rp10.000', 'en' => 'Ferry crossing IDR 20,000; Cultural village entry IDR 10,000'],
                'visit_duration_minutes' => 240,
                'popularity_score' => 91,
                'cover_image' => '/images/destinations/toba.jpg',
                'cover_alt' => ['id' => 'Danau Toba dengan rumah adat Batak di Pulau Samosir', 'en' => 'Lake Toba caldera and traditional Batak house on Samosir island'],
                'cover_credit' => 'Turget.id Archive',
            ],
        ];

        foreach ($destinations as $destData) {
            $catSlugs = $destData['categories'];
            $regSlug = $destData['region_slug'];
            unset($destData['categories'], $destData['region_slug']);

            $region = $regModels[$regSlug] ?? null;

            $dest = Destination::updateOrCreate(
                ['slug' => $destData['slug']],
                array_merge($destData, [
                    'country_id' => $indonesia->id,
                    'region_id' => $region?->id ?? 1,
                    'status' => ContentStatus::Published,
                    'is_featured' => true,
                    'published_at' => now(),
                    'facts_verified_at' => now(),
                    'created_by' => $admin?->id,
                    'updated_by' => $admin?->id,
                    'meta' => [
                        'title' => [
                            'id' => $destData['name']['id'].' — Panduan, Jam Buka, Tiket & Guide | Turget.id',
                            'en' => $destData['name']['en'].' — Guide, Hours, Tickets & Local Tours | Turget.id',
                        ],
                        'description' => [
                            'id' => $destData['tagline']['id'].'. Simak sejarah, tiket, aktivitas, dan pesan pemandu lokal di Turget.id.',
                            'en' => $destData['tagline']['en'].'. Discover history, practical tips, and verified local tours on Turget.id.',
                        ],
                    ],
                ])
            );

            // Sync categories
            $catIds = [];
            foreach ($catSlugs as $cSlug) {
                if (isset($catModels[$cSlug])) {
                    $catIds[] = $catModels[$cSlug]->id;
                }
            }
            $dest->categories()->sync($catIds);

            // Create media gallery entry
            Media::firstOrCreate([
                'mediable_type' => Destination::class,
                'mediable_id' => $dest->id,
                'path' => $destData['cover_image'],
            ], [
                'type' => 'image',
                'alt' => $destData['cover_alt'],
                'caption' => $destData['name'],
                'credit' => $destData['cover_credit'] ?? 'Turget.id',
                'sort_order' => 1,
            ]);
        }

        // 5. Travel Articles / Editorial Guides
        $artCategory = ArticleCategory::firstOrCreate(['slug' => 'panduan-perjalanan'], [
            'name' => ['id' => 'Panduan Perjalanan', 'en' => 'Travel Guides'],
            'sort_order' => 1,
        ]);

        $articles = [
            [
                'slug' => 'panduan-pertama-kali-ke-bali',
                'title' => [
                    'id' => 'Panduan Lengkap Wisata Pertama Kali ke Bali: Rute, Etika Budaya & Tips Memilih Guide',
                    'en' => 'First-Timer’s Complete Guide to Bali: Itineraries, Cultural Etiquette & Local Guides',
                ],
                'excerpt' => [
                    'id' => 'Semua yang perlu Anda ketahui sebelum mendarat di Bali: dari kawasan menginap terbaik, etika berpakaian di pura suci, hingga cara menyusun itinerary bersama guide lokal terpercaya.',
                    'en' => 'Everything you need to know before touching down in Bali: choosing the right neighborhood, temple etiquette, avoiding tourist traps, and building flexible itineraries with licensed guides.',
                ],
                'body' => [
                    'id' => "Bali selalu memikat hati setiap pelancong yang menginjakkan kakinya di Pulau Dewata. Namun, bagi wisatawan yang baru pertama kali berkunjung, menentukan rute perjalanan yang seimbang bisa menjadi tantangan tersendiri.\n\n### 1. Pahami Geografi dan Kawasan Wisata Bali\nBali terbagi menjadi beberapa karakter kawasan:\n- **Bali Selatan (Kuta, Seminyak, Canggu, Uluwatu):** Pusat pantai, selancar ombak, kafe kekinian, dan tebing sunset spektakuler.\n- **Bali Tengah (Ubud):** Jantung seni, persawahan subak hijau, cagar kera suci, museum lukisan, dan retreat kesehatan jiwa.\n- **Bali Utara & Timur (Amed, Tulamben, Lovina):** Suasana tenang, terumbu karang kaya untuk diving, dan lumba-lumba di laut tenang.\n\n### 2. Etika Mengunjungi Pura Sakral\nPura adalah tempat ibadah suci umat Hindu Bali. Selalu kenakan kain kamen (sarung) dan selendang di pinggang. Jangan melintas di depan orang yang sedang khusyuk sembahyang, dan hormati aturan pantangan yang tertera di papan informasi pura.\n\n### 3. Mengapa Perlu Tour Guide Lokal Terverifikasi?\nMenjelajah Bali bersama pemandu lokal berlisensi membuka cerita di balik setiap ornamen canang sari dan filosofi pura yang tak tertulis di buku panduan biasa.",
                    'en' => "Bali enchants millions of global travelers every year. Yet, for first-time visitors, balancing culture, beaches, and travel times can be overwhelming without local guidance.\n\n### 1. Understanding Bali’s Distinct Regions\n- **South Coast (Canggu, Seminyak, Uluwatu):** Surf breaks, chic beach clubs, and towering limestone sunset cliffs.\n- **Central Highlands (Ubud):** The cultural capital surrounded by Subak rice terraces, monkey sanctuaries, and yoga retreats.\n- **North & East (Amed, Lovina, Sidemen):** Relaxed coastal hamlets, world-class shipwreck diving, and traditional village life.\n\n### 2. Temple Etiquette to Remember\nBalinese temples are sacred living sanctuaries. Always wear a sarong and waist sash (kain & selendang). Never walk directly in front of praying devotees or step on daily street offerings (canang sari).\n\n### 3. The Power of a Verified Local Guide\nA licensed local guide unlocks the living myths, architectural secrets, and family compound stories behind every village gate.",
                ],
                'reading_minutes' => 6,
                'cover_image' => '/images/destinations/bali_hero.jpg',
                'cover_alt' => ['id' => 'Pemandangan sawah berundak hijau di Bali', 'en' => 'Lush terraced green rice fields in Bali'],
                'cover_credit' => 'Turget.id Editorial',
                'is_featured' => true,
                'sources' => [
                    ['title' => 'Dinas Pariwisata Provinsi Bali', 'url' => 'https://disparda.baliprov.go.id'],
                    ['title' => 'Buku Profil Kebudayaan Bali — Kemendikbud', 'url' => 'https://kebudayaan.kemdikbud.go.id'],
                ],
            ],
            [
                'slug' => 'menjelajah-borobudur-prambanan-jogja',
                'title' => [
                    'id' => 'Menyusuri Jejak Peradaban Mataram Kuno: Eksplorasi Borobudur, Prambanan & Keraton Jogja',
                    'en' => 'Tracing Ancient Mataram Dynasties: Exploring Borobudur, Prambanan & Yogyakarta’s Palace',
                ],
                'excerpt' => [
                    'id' => 'Kisah epik kerukunan dua wangsa besar di tanah Jawa yang melahirkan mahakarya candi Buddha dan Hindu terbesar di Asia Tenggara.',
                    'en' => 'An epic historical journey through Central Java: how two great dynasties coexisted and built the greatest Buddhist and Hindu temple complexes in Southeast Asia.',
                ],
                'body' => [
                    'id' => "Yogyakarta dan sekitarnya bukan sekadar tujuan berlibur; ia adalah ruang belajar sejarah peradaban Asia Tenggara yang agung.\n\n### Borobudur: Mahakarya Dinasti Syailendra\nDibangun pada abad ke-8 oleh Dinasti Syailendra yang menganut agama Buddha Mahayana, Borobudur dirancang sebagai buku bergambar raksasa berisi ajaran Dharma dan perjalanan spiritual menuju pencerahan.\n\n### Prambanan: Kemegahan Trimurti Dinasti Sanjaya\nHanya berjarak sekitar 40 kilometer ke arah timur, berdiri Candi Prambanan yang dipersembahkan bagi Trimurti (Siwa, Wisnu, Brahma) oleh Dinasti Sanjaya. Relief Ramayana yang dipahat halus di dinding candi Siwa menjadi bukti puncak keahlian pemahat batu Jawa Kuno.\n\n### Harmoni Dua Peradaban\nKehadiran kedua candi megah ini dalam kurun waktu berdekatan membuktikan tingginya toleransi keagamaan dan kerja sama sosial masyarakat Nusantara sejak ribuan tahun lampau.",
                    'en' => "Yogyakarta and Central Java offer an unparalleled window into Southeast Asia’s classical golden age.\n\n### Borobudur: The Sailendra Mountain Sanctuary\nErected in the 8th century under the Mahayana Buddhist Sailendra dynasty, Borobudur is structured as a physical mountain of contemplation guiding pilgrims toward nirvana.\n\n### Prambanan: Sanjaya’s Tribute to the Trimurti\nA short journey eastward stands Prambanan, the tallest Hindu temple complex in Indonesia dedicated to Shiva, Vishnu, and Brahma. The detailed Ramayana stone carvings along the balustrades showcase the pinnacle of ancient Javanese craftsmanship.\n\n### Enduring Religious Harmony\nThe coexistence of these two colossal spiritual monuments stands as living proof of religious harmony and mutual collaboration across early Indonesian history.",
                ],
                'reading_minutes' => 7,
                'cover_image' => '/images/destinations/borobudur.jpg',
                'cover_alt' => ['id' => 'Stupa Candi Borobudur saat fajar', 'en' => 'Borobudur stupas at dawn'],
                'cover_credit' => 'Turget.id Editorial',
                'is_featured' => true,
                'sources' => [
                    ['title' => 'Balai Konservasi Borobudur', 'url' => 'https://borobudurpedia.id'],
                    ['title' => 'UNESCO World Heritage Centre', 'url' => 'https://whc.unesco.org/en/list/592'],
                ],
            ],
            [
                'slug' => 'surga-bahari-raja-ampat-tips',
                'title' => [
                    'id' => 'Surga Bahari Terakhir di Bumi: Panduan Ekologis Liburan Bertanggung Jawab ke Raja Ampat',
                    'en' => 'Earth’s Last Coral Paradise: An Eco-Traveler’s Guide to Raja Ampat',
                ],
                'excerpt' => [
                    'id' => 'Menikmati keajaiban bawah laut terkaya di planet bumi sambil mendukung pelestarian terumbu karang dan ekonomi masyarakat adat.',
                    'en' => 'Experience the world’s most biodiverse coral reefs while actively championing marine conservation and community-led ecotourism.',
                ],
                'body' => [
                    'id' => "Terletak di jantung Segitiga Terumbu Karang Dunia, Raja Ampat menyimpan lebih dari 75% spesies karang keras dunia dan 1.500 jenis ikan laut.\n\n### Musim Terbaik untuk Menyelam\nWaktu terbaik mengunjungi Raja Ampat adalah antara bulan Oktober hingga April, ketika angin tenang dan jarak pandang bawah laut (visibility) mencapai puncaknya hingga 30 meter.\n\n### Tips Ekowisata Bertanggung Jawab\n- Gunakan tabir surya aman karang (reef-safe sunscreen) bebas oxybenzone.\n- Jangan pernah menyentuh atau menginjak terumbu karang hidup.\n- Gunakan jasa pemandu lokal dan perahu warga kampung untuk memastikan manfaat pariwisata langsung dinikmati masyarakat pemilik hak ulayat.",
                    'en' => "Resting at the epicenter of the Coral Triangle, Raja Ampat shelters over 75% of all known coral species and more than 1,500 species of reef fish.\n\n### Best Travel Windows\nThe peak scuba diving and cruising season spans October through April, when sea conditions are exceptionally calm with underwater visibility often reaching 30 meters.\n\n### Eco-Travel Best Practices\n- Only apply mineral reef-safe sunscreen free from oxybenzone and octinoxate.\n- Practice neutral buoyancy; never step on or touch living coral formations.\n- Always contract local Papuan guides and community homestays to foster regenerative indigenous stewardship.",
                ],
                'reading_minutes' => 5,
                'cover_image' => '/images/destinations/raja_ampat.jpg',
                'cover_alt' => ['id' => 'Gugusan pulau karang toska Raja Ampat', 'en' => 'Turquoise karst lagoons of Raja Ampat'],
                'cover_credit' => 'Turget.id Conservation Team',
                'is_featured' => true,
                'sources' => [
                    ['title' => 'BLUD UPTD Pengelolaan Kawasan Konservasi Perairan Kepulauan Raja Ampat', 'url' => 'https://kkprajaampat.com'],
                ],
            ],
        ];

        foreach ($articles as $artData) {
            Article::updateOrCreate(['slug' => $artData['slug']], array_merge($artData, [
                'article_category_id' => $artCategory->id,
                'author_id' => $admin?->id,
                'status' => ContentStatus::Published,
                'published_at' => now(),
                'facts_verified_at' => now(),
                'created_by' => $admin?->id,
                'updated_by' => $admin?->id,
                'meta' => [
                    'title' => [
                        'id' => $artData['title']['id'].' | Editorial Turget.id',
                        'en' => $artData['title']['en'].' | Turget.id Editorial',
                    ],
                    'description' => [
                        'id' => $artData['excerpt']['id'],
                        'en' => $artData['excerpt']['en'],
                    ],
                ],
            ]));
        }
    }
}
