<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Enums\OrganizerStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\TicketStatus;
use App\Models\Coupon;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventOccurrence;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Organizer;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\TicketDownloadLink;
use App\Models\TicketType;
use App\Models\User;
use App\Models\Venue;
use App\Notifications\CustomNotification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * The catalogue the public site is demonstrated with.
 *
 * Everything here is written by hand rather than generated: faker gives an
 * American concert in an American city at an American price, which tells you
 * nothing about how the site reads in Lomé. Venues carry their real
 * coordinates so the map on an event page points somewhere, organizers carry
 * Togolese names, and prices are in the range people actually pay in XOF.
 *
 * Images come from `database/seeders/assets` and are attached with
 * `preservingOriginal()` — without it Media Library *moves* the source file and
 * the second run of this seeder finds an empty directory.
 *
 * Idempotent: it stops if the catalogue is already there.
 */
final class PlatformDemoSeeder extends Seeder
{
    /** @var array<int, string> */
    private array $usedOrderNumbers = [];

    private const ASSETS = __DIR__.'/assets';

    /**
     * Categories, with the slug the front-end maps its icon from.
     * Changing a slug here means changing `CATEGORY_ICONS` on the front.
     *
     * @var array<int, array{name: string, slug: string}>
     */
    private const CATEGORIES = [
        ['name' => 'Concert', 'slug' => 'concert'],
        ['name' => 'Festival', 'slug' => 'festival'],
        ['name' => 'Culture & Arts', 'slug' => 'culture'],
        ['name' => 'Théâtre', 'slug' => 'theatre'],
        ['name' => 'Sport', 'slug' => 'sport'],
        ['name' => 'Formation', 'slug' => 'formation'],
        ['name' => 'Conférence', 'slug' => 'conference'],
        ['name' => 'Soirée', 'slug' => 'soiree'],
        ['name' => 'Gastronomie', 'slug' => 'gastronomie'],
        ['name' => 'Tourisme', 'slug' => 'tourisme'],
        ['name' => 'Science & Tech', 'slug' => 'science'],
        ['name' => 'Religieux', 'slug' => 'religieux'],
        ['name' => 'Cinéma', 'slug' => 'cinema'],
        ['name' => 'Business', 'slug' => 'business'],
    ];

    /**
     * Real venues with their real coordinates, so "Itinéraire" on an event page
     * opens the right place instead of the Gulf of Guinea.
     *
     * @var array<int, array{name: string, address: string, city: string, capacity: int, latitude: float, longitude: float}>
     */
    private const VENUES = [
        ['name' => 'Palais des Congrès de Lomé', 'address' => 'Boulevard du Mono', 'city' => 'Lomé', 'capacity' => 3000, 'latitude' => 6.1319, 'longitude' => 1.2228],
        ['name' => 'Stade de Kégué', 'address' => 'Route de Kpalimé', 'city' => 'Lomé', 'capacity' => 30000, 'latitude' => 6.2019, 'longitude' => 1.2247],
        ['name' => 'Palais des Sports de Lomé', 'address' => 'Quartier Bè-Kpota', 'city' => 'Lomé', 'capacity' => 5000, 'latitude' => 6.1750, 'longitude' => 1.2100],
        ['name' => 'Institut Français du Togo', 'address' => '31 Rue de Kouloumbeng', 'city' => 'Lomé', 'capacity' => 400, 'latitude' => 6.1289, 'longitude' => 1.2183],
        ['name' => 'Hôtel 2 Février', 'address' => 'Place de l’Indépendance', 'city' => 'Lomé', 'capacity' => 1200, 'latitude' => 6.1310, 'longitude' => 1.2210],
        ['name' => 'Université de Lomé — Amphi 1000', 'address' => 'Boulevard Eyadéma', 'city' => 'Lomé', 'capacity' => 1000, 'latitude' => 6.1731, 'longitude' => 1.2136],
        ['name' => 'Centre Culturel Denyigba', 'address' => 'Avenue de la Libération', 'city' => 'Lomé', 'capacity' => 600, 'latitude' => 6.1401, 'longitude' => 1.2295],
        ['name' => 'Stade Municipal de Kara', 'address' => 'Avenue de l’Amitié', 'city' => 'Kara', 'capacity' => 15000, 'latitude' => 9.5511, 'longitude' => 1.1861],
        ['name' => 'Centre Communautaire de Kpalimé', 'address' => 'Route de Klouto', 'city' => 'Kpalimé', 'capacity' => 800, 'latitude' => 6.9000, 'longitude' => 0.6300],
        ['name' => 'Espace Culturel de Sokodé', 'address' => 'Quartier Komah', 'city' => 'Sokodé', 'capacity' => 700, 'latitude' => 8.9833, 'longitude' => 1.1333],
    ];

    /**
     * @var array<int, array{first: string, last: string, company: string, description: string, website: string, logo: string}>
     */
    private const ORGANIZERS = [
        ['first' => 'Kossi', 'last' => 'Amégan', 'company' => 'Lomé Live Productions', 'description' => 'Production de concerts et de festivals à Lomé depuis 2014. Nous accompagnons les artistes togolais de la scène au public.', 'website' => 'https://lomelive.tg', 'logo' => 'avatar-org-1.webp'],
        ['first' => 'Afiwa', 'last' => 'Adjovi', 'company' => 'Afiwa Événements', 'description' => 'Agence événementielle spécialisée dans les soirées privées, mariages et galas d’entreprise.', 'website' => 'https://afiwa-events.tg', 'logo' => 'avatar-org-2.webp'],
        ['first' => 'Komlan', 'last' => 'Dossou', 'company' => 'Dossou Spectacles', 'description' => 'Théâtre, humour et arts vivants. Nous portons la création togolaise sur les grandes scènes du pays.', 'website' => 'https://dossou-spectacles.tg', 'logo' => 'avatar-org-3.webp'],
        ['first' => 'Ayélé', 'last' => 'Bawa', 'company' => 'Bawa Culture & Arts', 'description' => 'Expositions, résidences d’artistes et rencontres culturelles entre Lomé, Kpalimé et Sokodé.', 'website' => 'https://bawa-culture.tg', 'logo' => 'avatar-org-4.webp'],
        ['first' => 'Sena', 'last' => 'Agbeko', 'company' => 'Agbeko Sport Management', 'description' => 'Organisation de compétitions sportives et de tournois inter-régionaux au Togo.', 'website' => 'https://agbeko-sport.tg', 'logo' => 'avatar-org-5.webp'],
        ['first' => 'Mawuli', 'last' => 'Kpodo', 'company' => 'Kpodo Formation Pro', 'description' => 'Formations professionnelles certifiantes en numérique, gestion et entrepreneuriat.', 'website' => 'https://kpodo-formation.tg', 'logo' => 'avatar-org-6.webp'],
    ];

    /**
     * The catalogue. `days` is the offset from today, so the demo always has a
     * believable mix of upcoming and past events however long after seeding it
     * is looked at.
     *
     * @var array<int, array{title: string, category: string, organizer: int, venue: int|null, days: int, image: string, online?: bool, recurring?: bool, tiers: array<int, array{name: string, price: int, quantity: int}>, promo?: bool}>
     */
    private const EVENTS = [
        ['title' => 'Afrobeats Sunset Live', 'category' => 'concert', 'organizer' => 0, 'venue' => 1, 'days' => 12, 'image' => 'event-concert-live.webp', 'promo' => true,
            'tiers' => [['name' => 'Standard', 'price' => 5000, 'quantity' => 800], ['name' => 'VIP', 'price' => 20000, 'quantity' => 150], ['name' => 'Carré Or', 'price' => 50000, 'quantity' => 40]]],
        ['title' => 'Nuit du Jazz de Lomé', 'category' => 'concert', 'organizer' => 0, 'venue' => 3, 'days' => 26, 'image' => 'event-jazz.webp',
            'tiers' => [['name' => 'Standard', 'price' => 7500, 'quantity' => 250], ['name' => 'Carré VIP', 'price' => 25000, 'quantity' => 50]]],
        ['title' => 'Festival Togo Vibes', 'category' => 'festival', 'organizer' => 0, 'venue' => 1, 'days' => 45, 'image' => 'event-festival-sunset.webp', 'recurring' => true, 'promo' => true,
            'tiers' => [['name' => 'Pass 1 jour', 'price' => 10000, 'quantity' => 1500], ['name' => 'Pass 3 jours', 'price' => 25000, 'quantity' => 600], ['name' => 'Pass VIP', 'price' => 60000, 'quantity' => 120]]],
        ['title' => 'Concert Symphonique de l’Orchestre National', 'category' => 'concert', 'organizer' => 3, 'venue' => 0, 'days' => 33, 'image' => 'event-symphonie.webp',
            'tiers' => [['name' => 'Balcon', 'price' => 6000, 'quantity' => 400], ['name' => 'Orchestre', 'price' => 15000, 'quantity' => 200]]],
        ['title' => 'Soirée Électro Beach', 'category' => 'soiree', 'organizer' => 1, 'venue' => 4, 'days' => 8, 'image' => 'event-electro.webp',
            'tiers' => [['name' => 'Entrée', 'price' => 5000, 'quantity' => 500], ['name' => 'Table VIP (4 pers.)', 'price' => 80000, 'quantity' => 25]]],
        ['title' => 'Gala de Charité Éducation pour Tous', 'category' => 'soiree', 'organizer' => 1, 'venue' => 4, 'days' => 54, 'image' => 'event-galerie.webp',
            'tiers' => [['name' => 'Couvert', 'price' => 35000, 'quantity' => 300], ['name' => 'Table entreprise', 'price' => 300000, 'quantity' => 20]]],
        ['title' => 'Rétrospective Art Contemporain Togolais', 'category' => 'culture', 'organizer' => 3, 'venue' => 6, 'days' => 19, 'image' => 'event-expo.webp', 'recurring' => true,
            'tiers' => [['name' => 'Entrée simple', 'price' => 2000, 'quantity' => 900], ['name' => 'Visite guidée', 'price' => 6000, 'quantity' => 120]]],
        ['title' => 'Exposition Lumières d’Afrique', 'category' => 'culture', 'organizer' => 3, 'venue' => 8, 'days' => 61, 'image' => 'event-lumieres.webp',
            'tiers' => [['name' => 'Entrée', 'price' => 3000, 'quantity' => 500]]],
        ['title' => 'Les Fables de Lomé — Pièce en 3 actes', 'category' => 'theatre', 'organizer' => 2, 'venue' => 6, 'days' => 15, 'image' => 'event-theatre-rideau.webp', 'recurring' => true,
            'tiers' => [['name' => 'Placement libre', 'price' => 4000, 'quantity' => 350], ['name' => 'Première catégorie', 'price' => 9000, 'quantity' => 120]]],
        ['title' => 'Soirée Humour — Rires du Golfe', 'category' => 'theatre', 'organizer' => 2, 'venue' => 2, 'days' => 40, 'image' => 'event-broadway.webp', 'promo' => true,
            'tiers' => [['name' => 'Standard', 'price' => 6000, 'quantity' => 700], ['name' => 'Carré VIP', 'price' => 18000, 'quantity' => 100]]],
        ['title' => 'Derby National Lomé — Kara', 'category' => 'sport', 'organizer' => 4, 'venue' => 1, 'days' => 22, 'image' => 'event-football.webp',
            'tiers' => [['name' => 'Populaire', 'price' => 2000, 'quantity' => 12000], ['name' => 'Tribune couverte', 'price' => 7000, 'quantity' => 3000], ['name' => 'Loge', 'price' => 40000, 'quantity' => 60]]],
        ['title' => 'Open International de Tennis du Togo', 'category' => 'sport', 'organizer' => 4, 'venue' => 2, 'days' => 37, 'image' => 'event-tennis-court.webp', 'recurring' => true,
            'tiers' => [['name' => 'Journée', 'price' => 3000, 'quantity' => 800], ['name' => 'Pass tournoi', 'price' => 15000, 'quantity' => 200]]],
        ['title' => 'Marathon de Lomé', 'category' => 'sport', 'organizer' => 4, 'venue' => 7, 'days' => 70, 'image' => 'event-stade.webp',
            'tiers' => [['name' => 'Course 10 km', 'price' => 5000, 'quantity' => 2000], ['name' => 'Semi-marathon', 'price' => 8000, 'quantity' => 900], ['name' => 'Marathon', 'price' => 12000, 'quantity' => 400]]],
        ['title' => 'Formation Développement Web Full-Stack', 'category' => 'formation', 'organizer' => 5, 'venue' => null, 'days' => 10, 'image' => 'event-tech.webp', 'online' => true, 'recurring' => true,
            'tiers' => [['name' => 'Session complète', 'price' => 75000, 'quantity' => 60], ['name' => 'Tarif étudiant', 'price' => 40000, 'quantity' => 40]]],
        ['title' => 'Masterclass UI/UX Design', 'category' => 'formation', 'organizer' => 5, 'venue' => null, 'days' => 29, 'image' => 'event-art-digital.webp', 'online' => true, 'promo' => true,
            'tiers' => [['name' => 'Accès live', 'price' => 25000, 'quantity' => 200], ['name' => 'Live + replay', 'price' => 40000, 'quantity' => 150]]],
        ['title' => 'Certification Gestion de Projet', 'category' => 'formation', 'organizer' => 5, 'venue' => 5, 'days' => 48, 'image' => 'event-formation-salle.webp',
            'tiers' => [['name' => 'Participant', 'price' => 90000, 'quantity' => 80]]],
        ['title' => 'Sommet du Numérique Togolais', 'category' => 'conference', 'organizer' => 5, 'venue' => 0, 'days' => 31, 'image' => 'event-business.webp',
            'tiers' => [['name' => 'Visiteur', 'price' => 10000, 'quantity' => 1200], ['name' => 'Professionnel', 'price' => 45000, 'quantity' => 300], ['name' => 'Partenaire', 'price' => 150000, 'quantity' => 40]]],
        ['title' => 'Forum Entrepreneuriat & Financement', 'category' => 'business', 'organizer' => 5, 'venue' => 4, 'days' => 57, 'image' => 'event-forum-entrepreneuriat.webp',
            'tiers' => [['name' => 'Entrée', 'price' => 15000, 'quantity' => 600], ['name' => 'Pitch session', 'price' => 60000, 'quantity' => 50]]],
        ['title' => 'Semaine du Cinéma Africain', 'category' => 'cinema', 'organizer' => 3, 'venue' => 3, 'days' => 24, 'image' => 'event-orchestre.webp', 'recurring' => true,
            'tiers' => [['name' => 'Séance', 'price' => 2500, 'quantity' => 400], ['name' => 'Pass semaine', 'price' => 12000, 'quantity' => 150]]],
        ['title' => 'Festival des Saveurs du Togo', 'category' => 'gastronomie', 'organizer' => 1, 'venue' => 6, 'days' => 35, 'image' => 'event-gastro.webp', 'promo' => true,
            'tiers' => [['name' => 'Dégustation', 'price' => 5000, 'quantity' => 800], ['name' => 'Atelier cuisine', 'price' => 20000, 'quantity' => 100]]],
        ['title' => 'Escapade Cascades de Kpalimé', 'category' => 'tourisme', 'organizer' => 3, 'venue' => 8, 'days' => 18, 'image' => 'event-festival.webp',
            'tiers' => [['name' => 'Journée', 'price' => 18000, 'quantity' => 120], ['name' => 'Week-end', 'price' => 55000, 'quantity' => 60]]],
        ['title' => 'Nuit des Sciences et de l’Innovation', 'category' => 'science', 'organizer' => 5, 'venue' => 5, 'days' => 43, 'image' => 'event-concert.webp',
            'tiers' => [['name' => 'Entrée', 'price' => 2000, 'quantity' => 1000], ['name' => 'Atelier robotique', 'price' => 8000, 'quantity' => 80]]],
        ['title' => 'Concert Gospel de l’Avent', 'category' => 'religieux', 'organizer' => 0, 'venue' => 2, 'days' => 66, 'image' => 'event-concert-rouge.webp',
            'tiers' => [['name' => 'Entrée', 'price' => 3000, 'quantity' => 2000], ['name' => 'Place réservée', 'price' => 10000, 'quantity' => 300]]],
        // Second event of the categories that would otherwise hold only one.
        // Every category needs at least two, or its tile leads to a page with a
        // single card and the filter looks broken.
        ['title' => 'Dîner-Concert Saveurs & Rythmes', 'category' => 'gastronomie', 'organizer' => 1, 'venue' => 4, 'days' => 50, 'image' => 'poster-anniversaire.webp', 'promo' => true,
            'tiers' => [['name' => 'Couvert', 'price' => 25000, 'quantity' => 200], ['name' => 'Table VIP', 'price' => 120000, 'quantity' => 20]]],
        ['title' => 'Circuit Découverte Koutammakou', 'category' => 'tourisme', 'organizer' => 3, 'venue' => 9, 'days' => 63, 'image' => 'event-koutammakou.webp',
            'tiers' => [['name' => 'Séjour 2 jours', 'price' => 65000, 'quantity' => 80]]],
        ['title' => 'Hackathon Innovation Togo', 'category' => 'science', 'organizer' => 5, 'venue' => 5, 'days' => 27, 'image' => 'event-tech.webp', 'promo' => true,
            'tiers' => [['name' => 'Participant', 'price' => 5000, 'quantity' => 300], ['name' => 'Équipe (4 pers.)', 'price' => 16000, 'quantity' => 50]]],
        ['title' => 'Veillée de Louange et d’Adoration', 'category' => 'religieux', 'organizer' => 0, 'venue' => 6, 'days' => 20, 'image' => 'poster-reggae.webp',
            'tiers' => [['name' => 'Entrée', 'price' => 1500, 'quantity' => 1500]]],
        ['title' => 'Ciné-Débat : Mémoires d’Afrique', 'category' => 'cinema', 'organizer' => 3, 'venue' => 3, 'days' => 46, 'image' => 'poster-concert.webp',
            'tiers' => [['name' => 'Séance + débat', 'price' => 3500, 'quantity' => 300]]],
        ['title' => 'Salon des PME et de l’Export', 'category' => 'business', 'organizer' => 5, 'venue' => 0, 'days' => 72, 'image' => 'event-salon-pme.webp', 'promo' => true,
            'tiers' => [['name' => 'Visiteur', 'price' => 5000, 'quantity' => 1500], ['name' => 'Exposant', 'price' => 200000, 'quantity' => 60]]],
        ['title' => 'Tournoi de Handball Inter-Régions', 'category' => 'sport', 'organizer' => 4, 'venue' => 2, 'days' => 52, 'image' => 'event-tennis.webp',
            'tiers' => [['name' => 'Journée', 'price' => 2000, 'quantity' => 2500]]],
        ['title' => 'Comédie musicale « Lomé Mon Amour »', 'category' => 'theatre', 'organizer' => 2, 'venue' => 0, 'days' => 58, 'image' => 'event-theatre.webp', 'promo' => true,
            'tiers' => [['name' => 'Standard', 'price' => 8000, 'quantity' => 600], ['name' => 'Premium', 'price' => 22000, 'quantity' => 150]]],

        // Past events, so the "Passés" tabs and the ticket history are not empty.
        ['title' => 'Afro Sound Festival — Édition précédente', 'category' => 'festival', 'organizer' => 0, 'venue' => 1, 'days' => -40, 'image' => 'event-edm.webp',
            'tiers' => [['name' => 'Pass 1 jour', 'price' => 9000, 'quantity' => 1200]]],
        ['title' => 'Conférence Climat & Agriculture', 'category' => 'conference', 'organizer' => 5, 'venue' => 5, 'days' => -18, 'image' => 'event-saxo.webp',
            'tiers' => [['name' => 'Participant', 'price' => 12000, 'quantity' => 400]]],
    ];

    public function run(): void
    {
        if (Event::count() > 0) {
            $this->command->info('Catalogue déjà présent — seeder ignoré.');

            return;
        }

        // Les conversions (miniatures) partent en file d'attente par défaut. Un
        // `migrate:fresh --seed` sans worker laissait donc chaque `logoThumbnail`
        // et `bannerThumbnail` pointer sur un fichier absent : les logos des
        // organisateurs s'affichaient cassés sur toutes les cartes. Le seeder
        // doit produire un catalogue utilisable à lui seul, quitte à être plus
        // lent — on les génère ici de façon synchrone.
        config(['media-library.queue_conversions_by_default' => false]);

        $categories = $this->seedCategories();
        $venues = $this->seedVenues();
        $organizers = $this->seedOrganizers();

        $client = User::where('email', 'judasgbone@gmail.com')->first()
            ?? User::factory()->create(['email' => 'client@test.tg']);

        $paidTiers = collect();

        foreach (self::EVENTS as $definition) {
            $tiers = $this->seedEvent($definition, $categories, $venues, $organizers);

            if ($paidTiers->count() < 6 && $definition['days'] > 0) {
                $paidTiers->push($tiers);
            }
        }

        $this->seedOrders($paidTiers, $client);
        $this->seedFavorites($client);

        Coupon::factory()->count(4)->create();

        $organizers[0]->user->notify(
            new CustomNotification('Bienvenue', 'Votre espace organisateur est actif.', 'success'),
        );
        $client->notify(
            new CustomNotification('Billet disponible', 'Votre billet pour Afrobeats Sunset Live est prêt.', 'success'),
        );
        $client->notify(
            new CustomNotification('Rappel', 'Votre événement commence dans 3 jours.', 'info'),
        );

        $this->command->info(sprintf(
            '✅ Catalogue : %d catégories, %d lieux, %d organisateurs, %d événements.',
            count(self::CATEGORIES),
            count(self::VENUES),
            count(self::ORGANIZERS),
            count(self::EVENTS),
        ));
    }

    /** @return Collection<string, EventCategory> keyed by slug */
    private function seedCategories(): Collection
    {
        return collect(self::CATEGORIES)
            ->mapWithKeys(fn (array $row) => [
                $row['slug'] => EventCategory::firstOrCreate(['slug' => $row['slug']], ['name' => $row['name']]),
            ]);
    }

    /** @return Collection<int, Venue> */
    private function seedVenues(): Collection
    {
        return collect(self::VENUES)->map(fn (array $row) => Venue::firstOrCreate(
            ['name' => $row['name']],
            [...$row, 'country' => 'Togo'],
        ));
    }

    /** @return Collection<int, Organizer> */
    private function seedOrganizers(): Collection
    {
        return collect(self::ORGANIZERS)->map(function (array $row, int $index): Organizer {
            // The first profile takes over the `organizer@test.tg` account that
            // TestUsersSeeder creates. Otherwise that account keeps its empty
            // "Test Event Company" — no logo, no events — which shows up in the
            // public directory as a broken profile, and the one login used to
            // demo the back-office has nothing to manage.
            $user = ($index === 0 ? User::where('email', 'organizer@test.tg')->first() : null)
                ?? $this->organizerUser($row);

            if (! $user->hasRole('organizer-manager')) {
                $user->assignRole('organizer-manager');
            }

            $organizer = Organizer::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'company_name' => $row['company'],
                    'description' => $row['description'],
                    'website' => $row['website'],
                    'status' => OrganizerStatus::APPROVED,
                    'is_active' => true,
                ],
            );

            $this->attachImage($organizer, self::ASSETS.'/organizers/'.$row['logo'], 'organizers');

            return $organizer;
        });
    }

    /** @param array{first: string, last: string} $row */
    private function organizerUser(array $row): User
    {
        $slug = Str::slug($row['first'].'.'.$row['last']);

        return User::firstOrCreate(
            ['email' => $slug.'@ticketexpress.tg'],
            [
                'first_name' => $row['first'],
                'last_name' => $row['last'],
                'phone' => '+2289'.random_int(0, 9).str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT),
                'password' => bcrypt('Password123!'),
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $definition
     * @param  Collection<string, EventCategory>  $categories
     * @param  Collection<int, Venue>  $venues
     * @param  Collection<int, Organizer>  $organizers
     * @return Collection<int, TicketType>
     */
    private function seedEvent(
        array $definition,
        Collection $categories,
        Collection $venues,
        Collection $organizers,
    ): Collection {
        $isOnline = $definition['online'] ?? false;
        $start = now()->addDays($definition['days'])->setTime(19, 0);

        /** @var Event $event */
        $event = Event::create([
            'organizer_id' => $organizers[$definition['organizer']]->id,
            'category_id' => $categories[$definition['category']]->id,
            'venue_id' => $isOnline ? null : $venues[$definition['venue']]->id,
            'title' => $definition['title'],
            'slug' => Str::slug($definition['title']).'-'.Str::lower(Str::random(5)),
            'description' => $this->describe($definition['title'], $definition['category']),
            'start_date' => $start,
            'end_date' => (clone $start)->addHours(4),
            'max_attendees' => collect($definition['tiers'])->sum('quantity'),
            'status' => EventStatus::PUBLISHED,
            'event_type' => $isOnline ? EventType::ONLINE : EventType::PHYSICAL,
            'online_url' => $isOnline ? 'https://live.ticketexpress.tg/'.Str::lower(Str::random(8)) : null,
            'refund_allowed' => true,
            'refund_days_before' => random_int(2, 14),
        ]);

        $this->attachImage($event, self::ASSETS.'/events/'.$definition['image'], 'events');

        // Recurring events get three dates; the first ticket tier is tied to the
        // first one so per-date pricing has something to show.
        $occurrence = null;

        if ($definition['recurring'] ?? false) {
            foreach ([0, 1, 2] as $offset) {
                $occurrenceStart = (clone $start)->addDays($offset);

                $created = EventOccurrence::create([
                    'event_id' => $event->id,
                    'start_date' => $occurrenceStart,
                    'end_date' => (clone $occurrenceStart)->addHours(4),
                    'max_attendees' => $event->max_attendees,
                    'current_attendees' => 0,
                    'status' => 'scheduled',
                ]);

                $occurrence ??= $created;
            }
        }

        $tiers = collect($definition['tiers'])->values()->map(function (array $tier, int $index) use ($event, $occurrence): TicketType {
            return TicketType::create([
                'event_id' => $event->id,
                'occurrence_id' => $index === 0 ? $occurrence?->id : null,
                'name' => $tier['name'],
                'description' => 'Accès '.Str::lower($tier['name']).' à l’événement.',
                'price' => $tier['price'],
                'quantity' => $tier['quantity'],
                // A believable spread so some tiers read "dernières places".
                'sold_quantity' => (int) round($tier['quantity'] * (random_int(5, 85) / 100)),
                'sale_start_date' => now()->subWeeks(2),
                'sale_end_date' => $event->start_date,
                'benefits' => $this->benefits($tier['name']),
                'is_featured' => $index === 1,
                'sort_order' => $index,
            ]);
        });

        if ($definition['promo'] ?? false) {
            $first = $tiers->first();

            $first->update([
                'promotional_price' => round(((float) $first->price) * 0.75),
                'promotion_start_date' => now()->subDay(),
                'promotion_end_date' => now()->addWeeks(3),
            ]);
        }

        return $tiers;
    }

    /** @return array<int, string> */
    private function benefits(string $tier): array
    {
        return match (true) {
            str_contains(Str::lower($tier), 'vip'), str_contains(Str::lower($tier), 'or') => [
                '✨ Accès prioritaire', '🥂 Boisson de bienvenue', '🪑 Places réservées',
            ],
            str_contains(Str::lower($tier), 'pass') => ['🎫 Accès multi-dates', '🎁 Goodies offerts'],
            default => ['🎫 Entrée générale'],
        };
    }

    private function describe(string $title, string $category): string
    {
        $intro = match ($category) {
            'concert', 'festival' => 'Une soirée musicale portée par les meilleurs artistes de la scène togolaise et ouest-africaine.',
            'sport' => 'Une rencontre sportive de haut niveau, dans une ambiance de stade comme on les aime.',
            'formation', 'conference', 'business', 'science' => 'Un rendez-vous professionnel pour monter en compétence et rencontrer les acteurs du secteur.',
            'theatre', 'cinema', 'culture' => 'Une programmation exigeante qui met la création locale à l’honneur.',
            'gastronomie' => 'Une célébration des saveurs togolaises, entre producteurs, chefs et curieux.',
            'tourisme' => 'Une échappée organisée, transport et accompagnement compris.',
            default => 'Un événement à ne pas manquer.',
        };

        return $intro.' '.$title.' vous accueille avec une organisation soignée : billetterie en ligne, '
            .'accès par QR code et assistance sur place. Réservez tôt, les places partent vite.';
    }

    /**
     * Attaches a seed image, leaving the source file where it is.
     *
     * @param  \Spatie\MediaLibrary\HasMedia  $model
     */
    private function attachImage(object $model, string $path, string $collection): void
    {
        if (! is_file($path)) {
            return;
        }

        $model->addMedia($path)
            ->preservingOriginal()
            ->toMediaCollection($collection);
    }

    /**
     * @param  Collection<int, Collection<int, TicketType>>  $tierSets
     */
    private function seedOrders(Collection $tierSets, User $client): void
    {
        foreach ($tierSets as $index => $tiers) {
            $this->createPaidOrder($tiers, $client);

            // One unpaid order so the "En attente" tab has a row.
            if ($index === 0) {
                $this->createPendingOrder($tiers, $client);
            }
        }
    }

    private function seedFavorites(User $client): void
    {
        $participants = User::factory()->count(5)->create()->push($client);

        foreach (Event::query()->inRandomOrder()->limit(12)->get() as $event) {
            $event->favoritedBy()->syncWithoutDetaching(
                $participants->random(random_int(1, $participants->count()))->pluck('id')->all(),
            );
        }

        // The demo client keeps a predictable set, so "Mes favoris" is never empty.
        $client->favoriteEvents()->syncWithoutDetaching(
            Event::query()->limit(4)->pluck('id')->all(),
        );
    }

    /** @param Collection<int, TicketType> $ticketTypes */
    private function createPaidOrder(Collection $ticketTypes, User $client): void
    {
        /** @var Order $order */
        $order = Order::factory()->paid()->create([
            'user_id' => $client->id,
            'order_number' => $this->nextOrderNumber(),
            'first_name' => $client->first_name ?? 'Client',
            'last_name' => $client->last_name ?? 'Test',
            'email' => $client->email,
            'phone' => $client->phone ?? '+22890000000',
            'payment_method' => fake()->randomElement(['flooz', 'tmoney']),
            'paid_at' => now()->subDays(random_int(1, 20)),
        ]);

        $total = 0.0;

        foreach ($ticketTypes->take(2) as $tier) {
            $quantity = random_int(1, 3);
            $unit = (float) $tier->currentPrice();

            OrderItem::create([
                'order_id' => $order->id,
                'ticket_type_id' => $tier->id,
                'quantity' => $quantity,
                'unit_price' => $unit,
                'subtotal' => $unit * $quantity,
            ]);

            $total += $unit * $quantity;

            foreach (range(1, $quantity) as $ignored) {
                $ticket = Ticket::factory()->create([
                    'order_id' => $order->id,
                    'ticket_type_id' => $tier->id,
                ]);

                if (random_int(0, 3) === 0) {
                    $ticket->update([
                        'status' => TicketStatus::USED,
                        'checked_in_at' => now()->subHours(random_int(1, 48)),
                    ]);
                }
            }
        }

        $order->update(['total_amount' => $total]);

        Payment::factory()->create([
            'order_id' => $order->id,
            'amount' => $total,
            'method' => fake()->randomElement([PaymentMethod::FLOOZ, PaymentMethod::TMONEY]),
            'status' => PaymentStatus::PAYE,
            'paid_at' => now(),
        ]);

        TicketDownloadLink::create([
            'token' => Str::random(40),
            'order_id' => $order->id,
            'expires_at' => now()->addDays(30),
            'download_count' => random_int(0, 6),
            'max_downloads' => 50,
        ]);
    }

    /** @param Collection<int, TicketType> $ticketTypes */
    private function createPendingOrder(Collection $ticketTypes, User $client): void
    {
        /** @var Order $order */
        $order = Order::factory()->pending()->create([
            'user_id' => $client->id,
            'order_number' => $this->nextOrderNumber(),
            'first_name' => $client->first_name ?? 'Client',
            'last_name' => $client->last_name ?? 'Test',
            'email' => $client->email,
            'phone' => $client->phone ?? '+22890000000',
            'payment_method' => 'flooz',
        ]);

        $tier = $ticketTypes->first();
        $quantity = random_int(1, 2);
        $unit = (float) $tier->currentPrice();

        OrderItem::create([
            'order_id' => $order->id,
            'ticket_type_id' => $tier->id,
            'quantity' => $quantity,
            'unit_price' => $unit,
            'subtotal' => $unit * $quantity,
        ]);

        $order->update(['total_amount' => $unit * $quantity]);

        Payment::factory()->pending()->create([
            'order_id' => $order->id,
            'amount' => $unit * $quantity,
            'method' => PaymentMethod::FLOOZ,
        ]);
    }

    /** A unique 7-digit order number, matching `Order::generateOrderNumber`. */
    private function nextOrderNumber(): string
    {
        do {
            $number = (string) random_int(1000000, 9999999);
        } while (in_array($number, $this->usedOrderNumbers, true));

        $this->usedOrderNumbers[] = $number;

        return $number;
    }
}
