<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Console\Commands\CancelUnpaidOrdersCommand;
use App\Enums\CouponType;
use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Enums\OrderStatus;
use App\Enums\OrganizerStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\TicketStatus;
use App\Enums\WithdrawalStatus;
use App\Models\CheckIn;
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
use App\Models\Withdrawal;
use App\Notifications\CustomNotification;
use App\Support\CatalogueCache;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
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

    /**
     * Les événements dont tous les tarifs sont posés à « épuisé ».
     *
     * Retenus parce qu'un seul geste peut défaire ce réglage : l'annulation
     * d'une commande impayée rend ses places au stock, et
     * `orders:cancel-unpaid` passe chaque minute sur les commandes en attente
     * que ce seeder vient de créer. Un événement marqué complet se retrouvait
     * ainsi à « 1 tarif sur 2 épuisé » quelques minutes après le seeding.
     *
     * @var array<int, string>
     */
    private array $soldOutEventIds = [];

    private const ASSETS = __DIR__.'/assets';

    /**
     * Nombre de commandes d'historique.
     *
     * Assez pour que les courbes des rapports aient une forme sur huit mois,
     * pas assez pour que le seeder — déjà lent à cause des images — double sa
     * durée. Chacune produit un à six billets.
     */
    private const HISTORY_ORDERS = 90;

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
        // Les logos sont réutilisés au-delà du sixième : six visuels suffisent à
        // montrer que la colonne est remplie, en ajouter n'apprendrait rien.
        ['first' => 'Édem', 'last' => 'Tsogbe', 'company' => 'Golfe Nightlife', 'description' => 'Clubbing, afterworks et soirées à thème sur le littoral togolais.', 'website' => 'https://golfe-nightlife.tg', 'logo' => 'avatar-org-2.webp'],
        ['first' => 'Akouvi', 'last' => 'Lawson', 'company' => 'Lawson Kids & Family', 'description' => 'Spectacles jeune public, ateliers créatifs et sorties familiales.', 'website' => 'https://lawson-family.tg', 'logo' => 'avatar-org-4.webp'],
        ['first' => 'Yao', 'last' => 'Sossou', 'company' => 'Sossou Congrès', 'description' => 'Congrès médicaux, séminaires et rencontres institutionnelles clés en main.', 'website' => 'https://sossou-congres.tg', 'logo' => 'avatar-org-1.webp'],
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
        // Premier des trois événements complets — tous les tarifs épuisés. Sans
        // eux, « Complet » n'existe nulle part dans la démonstration : ni sur une
        // carte du catalogue, ni sur le bouton d'une fiche, ni au refus de la
        // caisse. Trois, dans trois catégories différentes, pour qu'on tombe
        // dessus en parcourant le site et pas seulement en le cherchant.
        //
        // Aucun n'est pris parmi les six premiers événements à venir : ce sont
        // eux qui alimentent les commandes du participant de démonstration.
        ['title' => 'Soirée Humour — Rires du Golfe', 'category' => 'theatre', 'organizer' => 2, 'venue' => 2, 'days' => 40, 'image' => 'event-broadway.webp', 'soldout' => true,
            'tiers' => [['name' => 'Standard', 'price' => 6000, 'quantity' => 700], ['name' => 'Carré VIP', 'price' => 18000, 'quantity' => 100]]],
        // Un seul tarif épuisé : la fiche reste vendable, et la carte du tarif
        // épuisé s'affiche grisée à côté des autres. C'est le cas courant.
        ['title' => 'Derby National Lomé — Kara', 'category' => 'sport', 'organizer' => 4, 'venue' => 1, 'days' => 22, 'image' => 'event-football.webp', 'soldout_tier' => 2,
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
        // Deuxième complet, sur trois tarifs : la fiche montre alors une colonne
        // entière d'« Épuisé », ce qu'un événement à deux tarifs ne rend pas.
        ['title' => 'Sommet du Numérique Togolais', 'category' => 'conference', 'organizer' => 5, 'venue' => 0, 'days' => 31, 'image' => 'event-business.webp', 'soldout' => true,
            'tiers' => [['name' => 'Visiteur', 'price' => 10000, 'quantity' => 1200], ['name' => 'Professionnel', 'price' => 45000, 'quantity' => 300], ['name' => 'Partenaire', 'price' => 150000, 'quantity' => 40]]],
        ['title' => 'Forum Entrepreneuriat & Financement', 'category' => 'business', 'organizer' => 5, 'venue' => 4, 'days' => 57, 'image' => 'event-forum-entrepreneuriat.webp',
            'tiers' => [['name' => 'Entrée', 'price' => 15000, 'quantity' => 600], ['name' => 'Pitch session', 'price' => 60000, 'quantity' => 50]]],
        ['title' => 'Semaine du Cinéma Africain', 'category' => 'cinema', 'organizer' => 3, 'venue' => 3, 'days' => 24, 'image' => 'event-orchestre.webp', 'recurring' => true,
            'tiers' => [['name' => 'Séance', 'price' => 2500, 'quantity' => 400], ['name' => 'Pass semaine', 'price' => 12000, 'quantity' => 150]]],
        ['title' => 'Festival des Saveurs du Togo', 'category' => 'gastronomie', 'organizer' => 1, 'venue' => 6, 'days' => 35, 'image' => 'event-gastro.webp', 'promo' => true,
            'tiers' => [['name' => 'Dégustation', 'price' => 5000, 'quantity' => 800], ['name' => 'Atelier cuisine', 'price' => 20000, 'quantity' => 100]]],
        // Troisième complet, et le plus proche des trois (dans dix-huit jours) :
        // il apparaît donc haut dans un catalogue trié par date, là où on le voit
        // sans chercher.
        ['title' => 'Escapade Cascades de Kpalimé', 'category' => 'tourisme', 'organizer' => 3, 'venue' => 8, 'days' => 18, 'image' => 'event-festival.webp', 'soldout' => true,
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

        // Événements des trois nouveaux organisateurs.
        ['title' => 'Golfe Beach Party — Édition Harmattan', 'category' => 'soiree', 'organizer' => 6, 'venue' => 4, 'days' => 16, 'image' => 'event-edm.webp', 'promo' => true,
            'tiers' => [['name' => 'Entrée', 'price' => 4000, 'quantity' => 900], ['name' => 'Carré VIP', 'price' => 30000, 'quantity' => 80]]],
        ['title' => 'Contes et Marionnettes du Golfe', 'category' => 'theatre', 'organizer' => 7, 'venue' => 6, 'days' => 23, 'image' => 'event-theatre-rideau.webp', 'recurring' => true,
            'tiers' => [['name' => 'Enfant', 'price' => 1500, 'quantity' => 400], ['name' => 'Adulte', 'price' => 3000, 'quantity' => 400]]],
        ['title' => 'Congrès National de Médecine Générale', 'category' => 'conference', 'organizer' => 8, 'venue' => 0, 'days' => 39, 'image' => 'event-business.webp',
            'tiers' => [['name' => 'Praticien', 'price' => 55000, 'quantity' => 500], ['name' => 'Interne', 'price' => 20000, 'quantity' => 200], ['name' => 'Partenaire', 'price' => 250000, 'quantity' => 30]]],

        // Événements en cours *aujourd'hui* : sans eux, l'écran « Événements en
        // cours » et le portique de contrôle d'accès s'ouvrent sur du vide, et
        // rien ne permet de démontrer le scan de billets.
        ['title' => 'Festival Harmattan — Journée 2', 'category' => 'festival', 'organizer' => 0, 'venue' => 1, 'days' => 0, 'ongoing' => true, 'image' => 'event-festival-sunset.webp', 'promo' => true,
            'tiers' => [['name' => 'Pass journée', 'price' => 8000, 'quantity' => 1200], ['name' => 'Pass VIP', 'price' => 35000, 'quantity' => 150]]],
        ['title' => 'Salon de l’Artisanat Togolais', 'category' => 'culture', 'organizer' => 3, 'venue' => 6, 'days' => 0, 'ongoing' => true, 'image' => 'event-expo.webp',
            'tiers' => [['name' => 'Entrée', 'price' => 1000, 'quantity' => 2000], ['name' => 'Visite guidée', 'price' => 5000, 'quantity' => 150]]],
        ['title' => 'Tournoi de Maracana du Quartier', 'category' => 'sport', 'organizer' => 4, 'venue' => 7, 'days' => 0, 'ongoing' => true, 'image' => 'event-football.webp',
            'tiers' => [['name' => 'Entrée', 'price' => 1000, 'quantity' => 3000]]],

        // Past events, so the "Passés" tabs and the ticket history are not empty.
        ['title' => 'Afro Sound Festival — Édition précédente', 'category' => 'festival', 'organizer' => 0, 'venue' => 1, 'days' => -40, 'image' => 'event-edm.webp',
            'tiers' => [['name' => 'Pass 1 jour', 'price' => 9000, 'quantity' => 1200]]],
        ['title' => 'Conférence Climat & Agriculture', 'category' => 'conference', 'organizer' => 5, 'venue' => 5, 'days' => -18, 'image' => 'event-saxo.webp',
            'tiers' => [['name' => 'Participant', 'price' => 12000, 'quantity' => 400]]],
        // Plus loin dans le passé, pour que les rapports aient de quoi tracer
        // une courbe sur plusieurs mois plutôt que sur trois semaines.
        ['title' => 'Nuit Blanche de Lomé — Édition précédente', 'category' => 'soiree', 'organizer' => 1, 'venue' => 4, 'days' => -95, 'image' => 'event-electro.webp',
            'tiers' => [['name' => 'Entrée', 'price' => 5000, 'quantity' => 900], ['name' => 'Carré VIP', 'price' => 25000, 'quantity' => 90]]],
        ['title' => 'Coupe Scolaire d’Athlétisme', 'category' => 'sport', 'organizer' => 4, 'venue' => 7, 'days' => -140, 'image' => 'event-stade.webp',
            'tiers' => [['name' => 'Entrée', 'price' => 1000, 'quantity' => 2500]]],
        ['title' => 'Rencontres du Théâtre Amateur', 'category' => 'theatre', 'organizer' => 2, 'venue' => 6, 'days' => -200, 'image' => 'event-theatre.webp', 'recurring' => true,
            'tiers' => [['name' => 'Placement libre', 'price' => 2500, 'quantity' => 350]]],
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
        $this->seedPendingApplications();

        $client = User::where('email', 'judasgbone@gmail.com')->first()
            ?? User::factory()->create(['email' => 'client@test.tg']);

        $paidTiers = collect();
        $ongoingTiers = collect();

        foreach (self::EVENTS as $definition) {
            $tiers = $this->seedEvent($definition, $categories, $venues, $organizers);

            if ($definition['ongoing'] ?? false) {
                $ongoingTiers->push($tiers);

                continue;
            }

            if ($paidTiers->count() < 6 && $definition['days'] > 0) {
                $paidTiers->push($tiers);
            }
        }

        $this->seedOrders($paidTiers, $ongoingTiers, $client);
        $this->seedHistory();
        $this->seedFavorites($client);
        $this->seedWithdrawals();

        $this->seedCoupons();

        // Les billets d'événements passés que personne n'a présentés doivent
        // porter « expiré », comme après une heure d'exploitation normale. Sans
        // ce passage, le filtre « Expiré » de la liste des billets vendus
        // n'aurait aucune ligne à montrer le jour du seeding.
        Artisan::call('tickets:expire');

        $organizers[0]->user->notify(
            new CustomNotification('Bienvenue', 'Votre espace organisateur est actif.', 'success'),
        );
        $client->notify(
            new CustomNotification('Billet disponible', 'Votre billet pour Afrobeats Sunset Live est prêt.', 'success'),
        );
        $client->notify(
            new CustomNotification('Rappel', 'Votre événement commence dans 3 jours.', 'info'),
        );

        // Le catalogue est en cache, et le seeding dure près de deux minutes :
        // une page d'accueil ouverte pendant ce temps voit les catégories déjà
        // créées mais aucun événement encore rattaché, et met en cache des
        // compteurs à zéro — pour une heure, c'est le TTL des catégories. Les
        // vignettes annonçaient donc « 0 événement » longtemps après la fin du
        // seeding, sans que rien dans la base ne soit faux.
        //
        // `migrate:fresh` vide bien le cache au départ (il est en base), mais
        // c'est la fin du seeding qui compte.
        CatalogueCache::flush();

        $this->command->info(sprintf(
            '✅ Catalogue : %d catégories, %d lieux, %d organisateurs, %d événements '
            .'(dont %d en cours aujourd’hui), %d billets émis, %d entrées scannées, %d retraits.',
            EventCategory::count(),
            Venue::count(),
            Organizer::count(),
            Event::count(),
            Event::where('start_date', '<=', now())->where('end_date', '>=', now())->count(),
            Ticket::count(),
            CheckIn::count(),
            Withdrawal::count(),
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

    /**
     * Les dossiers d'organisateur encore à traiter.
     *
     * Sans eux, « Organisateurs » ne montre que des profils déjà approuvés :
     * l'écran d'approbation n'a rien à approuver, et le motif de refus rendu au
     * demandeur n'apparaît nulle part. Ces comptes n'ont volontairement aucun
     * événement — un dossier en attente n'a pas encore le droit de publier.
     */
    private function seedPendingApplications(): void
    {
        // Le dossier refusé est le seul sans logo : c'est ce que dit son motif de
        // refus — des pièces manquantes. Les deux autres ont déposé leur visuel,
        // comme le fait un demandeur sérieux, et l'écran d'approbation les montre.
        $applications = [
            ['first' => 'Nadège', 'last' => 'Amouzou', 'company' => 'Amouzou Live', 'description' => 'Jeune structure de production de concerts acoustiques à Lomé. Première demande d’ouverture d’espace organisateur.', 'website' => 'https://amouzou-live.tg', 'status' => OrganizerStatus::PENDING, 'reason' => null, 'logo' => 'avatar-org-3.webp'],
            ['first' => 'Rachid', 'last' => 'Ouro-Bang’na', 'company' => 'Sokodé Événements', 'description' => 'Organisation de festivals régionaux dans la Centrale. Dossier déposé avec pièces justificatives.', 'website' => 'https://sokode-events.tg', 'status' => OrganizerStatus::PENDING, 'reason' => null, 'logo' => 'avatar-org-5.webp'],
            ['first' => 'Prosper', 'last' => 'Katanga', 'company' => 'Katanga Promo', 'description' => 'Demande d’ouverture pour des soirées privées.', 'website' => '', 'status' => OrganizerStatus::REJECTED, 'reason' => 'Pièces justificatives incomplètes : registre de commerce et pièce d’identité du gérant manquants.', 'logo' => null],
        ];

        foreach ($applications as $row) {
            $user = $this->organizerUser($row);

            if (! $user->hasRole('organizer-manager')) {
                $user->assignRole('organizer-manager');
            }

            $organizer = Organizer::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'company_name' => $row['company'],
                    'description' => $row['description'],
                    'website' => $row['website'] !== '' ? $row['website'] : null,
                    'status' => $row['status'],
                    'is_active' => true,
                    'rejection_reason' => $row['reason'],
                ],
            );

            if ($row['logo'] !== null) {
                $this->attachImage($organizer, self::ASSETS.'/organizers/'.$row['logo'], 'organizers');
            }
        }
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

        // Un événement « en cours » a commencé et n'est pas fini : c'est ce que
        // l'écran d'exploitation cherche, et la seule façon d'avoir un portique
        // ouvert au moment où l'on regarde la démonstration. Les autres partent
        // à 19 h, l'heure des spectacles.
        $start = ($definition['ongoing'] ?? false)
            ? now()->subHours(2)
            : now()->addDays($definition['days'])->setTime(19, 0);

        $end = ($definition['ongoing'] ?? false)
            ? now()->addHours(4)
            : (clone $start)->addHours(4);

        /** @var Event $event */
        $event = Event::create([
            'organizer_id' => $organizers[$definition['organizer']]->id,
            'category_id' => $categories[$definition['category']]->id,
            'venue_id' => $isOnline ? null : $venues[$definition['venue']]->id,
            'title' => $definition['title'],
            'slug' => Str::slug($definition['title']).'-'.Str::lower(Str::random(5)),
            'description' => $this->describe($definition['title'], $definition['category']),
            'start_date' => $start,
            'end_date' => $end,
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

        // Épuisé : `soldout` vide tout l'événement, `soldout_tier` un seul rang.
        // Écrit ici plutôt que laissé au hasard du tirage ci-dessous, qui ne monte
        // jamais à 100 % — c'est ce qui faisait qu'aucun « Complet » n'existait
        // dans la démonstration.
        $soldOutAll = $definition['soldout'] ?? false;
        $soldOutTier = $definition['soldout_tier'] ?? null;

        if ($soldOutAll) {
            $this->soldOutEventIds[] = $event->id;
        }

        // L'ouverture des ventes : huit semaines avant l'événement, mais jamais
        // dans le futur. Sur un événement à plus de deux mois, la règle des huit
        // semaines plaçait l'ouverture après aujourd'hui : treize tarifs
        // affichaient « Bientôt en vente » et l'événement n'était pas achetable.
        // Un organisateur ouvre ses ventes dès qu'il publie — et le seeder publie
        // tout maintenant.
        $saleStart = (clone $event->start_date)->subWeeks(8);

        if ($saleStart->isFuture()) {
            $saleStart = now();
        }

        $tiers = collect($definition['tiers'])->values()->map(function (array $tier, int $index) use ($event, $occurrence, $soldOutAll, $soldOutTier, $saleStart): TicketType {
            $isSoldOut = $soldOutAll || $index === $soldOutTier;

            return TicketType::create([
                'event_id' => $event->id,
                'occurrence_id' => $index === 0 ? $occurrence?->id : null,
                'name' => $tier['name'],
                'description' => 'Accès '.Str::lower($tier['name']).' à l’événement.',
                'price' => $tier['price'],
                'quantity' => $tier['quantity'],
                // A believable spread so some tiers read "dernières places".
                'sold_quantity' => $isSoldOut
                    ? $tier['quantity']
                    : (int) round($tier['quantity'] * (random_int(5, 85) / 100)),
                // Huit semaines avant l'événement, jamais dans le futur — voir
                // au-dessus. Et surtout pas « il y a deux semaines » : sur un
                // événement d'il y a six mois, cela aurait daté l'ouverture des
                // ventes après la représentation.
                'sale_start_date' => $saleStart,
                // Elle ferme à la **fin** de l'événement, pas à son ouverture :
                // un retardataire achète sa place au guichet une fois le concert
                // commencé, et c'est même le cas où il en a le plus besoin. Caler
                // la fermeture sur le début rendait « Vente fermée » les trois
                // événements en cours — ceux qu'on veut justement pouvoir
                // démontrer.
                'sale_end_date' => $event->end_date,
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
     * Les commandes du participant de démonstration.
     *
     * Volontairement peu nombreuses et sur des événements à venir : c'est le
     * compte qu'on ouvre pour montrer « Mes billets », et une poignée de billets
     * qu'on peut parcourir vaut mieux qu'une liste où l'on cherche le QR code.
     * Le volume qui alimente les graphes vient d'ailleurs, voir seedHistory().
     *
     * Cinq billets, chacun avec son propre QR : deux commandes de deux billets
     * sur des événements à venir, plus un billet sur un événement **en cours**.
     * Ce dernier est le seul qui rend la démonstration du portique possible sans
     * rien préparer — un billet valide, sur un événement dont la fenêtre de
     * contrôle est ouverte maintenant, au nom du compte qu'on montre.
     *
     * @param  Collection<int, Collection<int, TicketType>>  $tierSets
     * @param  Collection<int, Collection<int, TicketType>>  $ongoingTierSets
     */
    private function seedOrders(Collection $tierSets, Collection $ongoingTierSets, User $client): void
    {
        // Deux commandes payées de deux billets chacune : quatre QR codes à
        // montrer, répartis sur deux événements différents.
        foreach ($tierSets->take(2) as $tiers) {
            $this->createPaidOrder($tiers, $client, tickets: 2);
        }

        if ($ongoingTierSets->isNotEmpty()) {
            $this->createPaidOrder($ongoingTierSets->first(), $client, tickets: 1);
        }

        // One unpaid order so the "En attente" tab has a row.
        $this->createPendingOrder($tierSets->first(), $client);
    }

    /**
     * L'historique qui donne du relief au tableau de bord et aux rapports.
     *
     * Les rapports agrègent sur `orders.created_at` : sans commandes étalées, la
     * courbe du chiffre d'affaires est un pic sur aujourd'hui et les
     * comparaisons de période ne comparent rien. On étale donc sur huit mois,
     * avec une pente — les mois récents pèsent plus que les anciens, ce qui
     * ressemble à une plateforme qui démarre.
     *
     * Ces commandes appartiennent à une foule de participants, pas au compte de
     * démonstration : un tableau de bord où tout est acheté par la même personne
     * ne montre rien de la répartition.
     */
    private function seedHistory(): void
    {
        $buyers = User::factory()->count(18)->create();

        $events = Event::query()
            // L'organisateur est chargé ici parce que c'est son compte qu'on
            // inscrit comme agent de contrôle sur les entrées scannées : une
            // validation sans auteur ne ressemble à rien dans le journal.
            ->with(['ticketTypes', 'organizer'])
            ->where('status', EventStatus::PUBLISHED)
            ->get()
            ->filter(fn (Event $event) => $event->ticketTypes->isNotEmpty());

        if ($events->isEmpty()) {
            return;
        }

        // Le catalogue penche vers l'avenir — il doit remplir la page d'accueil.
        // L'historique, lui, doit peser sur ce qui a déjà eu lieu : c'est là que
        // se trouvent les entrées scannées, sans lesquelles « Entrées
        // enregistrées » reste à zéro dans les rapports. D'où un tirage qui vise
        // le passé deux fois sur cinq, alors qu'il ne représente qu'un dixième
        // du catalogue.
        $past = $events->filter(fn (Event $event) => $event->end_date?->isPast() ?? false)->values();
        $upcoming = $events->reject(fn (Event $event) => $event->end_date?->isPast() ?? false)->values();

        foreach (range(1, self::HISTORY_ORDERS) as $ignored) {
            $pool = ($past->isNotEmpty() && random_int(1, 100) <= 40) ? $past : $upcoming;

            if ($pool->isEmpty()) {
                continue;
            }

            /** @var Event $event */
            $event = $pool->random();

            // Une commande se passe avant l'événement, et jamais dans le futur.
            $latest = min($event->start_date->getTimestamp(), now()->getTimestamp());
            $earliest = now()->subMonths(8)->getTimestamp();

            if ($latest <= $earliest) {
                continue;
            }

            // La racine carrée tire les tirages vers la borne haute : les mois
            // récents se remplissent davantage que les premiers.
            $placedAt = now()->parse('@'.(int) ($earliest + (($latest - $earliest) * sqrt(random_int(0, 10000) / 10000))));

            $this->createHistoricalOrder($event, $buyers->random(), $placedAt);
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

    /**
     * @param  Collection<int, TicketType>  $ticketTypes
     * @param  int|null  $tickets  Nombre exact de billets voulu, réparti sur les
     *                             tarifs. `null` laisse le hasard décider.
     */
    private function createPaidOrder(Collection $ticketTypes, User $client, ?int $tickets = null): void
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
        $tiers = $ticketTypes->take(2);

        // Le total demandé est réparti sur les tarifs, et il est **exact** : le
        // reste se distribue un par un sur les premiers tarifs plutôt que par un
        // arrondi au plafond, qui rendait trois billets quand on en demandait
        // deux, et deux quand on en demandait un.
        $remaining = $tickets;

        foreach ($tiers->values() as $index => $tier) {
            $left = $tiers->count() - $index;

            $quantity = $remaining !== null
                ? intdiv($remaining, $left) + ($remaining % $left > 0 ? 1 : 0)
                : random_int(1, 3);

            if ($quantity < 1) {
                continue;
            }

            $remaining = $remaining !== null ? $remaining - $quantity : null;

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
                Ticket::factory()->create([
                    'order_id' => $order->id,
                    'ticket_type_id' => $tier->id,
                ]);
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

    /**
     * Une commande de l'historique, datée du jour où elle a été passée.
     *
     * `created_at` est écrit explicitement : c'est la colonne sur laquelle les
     * rapports agrègent, et la laisser à `now()` empilerait huit mois de ventes
     * sur la journée du seeding.
     */
    private function createHistoricalOrder(Event $event, User $buyer, CarbonInterface $placedAt): void
    {
        $tiers = $event->ticketTypes->shuffle()->take(random_int(1, 2));

        // Une minorité de commandes n'aboutit pas : sans elles, le camembert des
        // statuts n'a qu'une part et les rapports de remboursement sont vides.
        $status = match (true) {
            random_int(1, 100) <= 80 => OrderStatus::PAID,
            random_int(1, 100) <= 50 => OrderStatus::PENDING,
            random_int(1, 100) <= 50 => OrderStatus::CANCELLED,
            default => OrderStatus::REFUNDED,
        };

        // Jamais de commande en attente sur un événement affiché complet.
        //
        // Elle sera annulée par `orders:cancel-unpaid` dans les minutes qui
        // suivent le seeding, et l'annulation rend ses places au stock : le
        // tarif cesse alors d'être épuisé et l'événement n'affiche plus
        // « Complet ». Le réglage se défaisait tout seul, sans que rien dans le
        // seeder ne soit faux au moment où il tournait.
        if ($status === OrderStatus::PENDING && in_array($event->id, $this->soldOutEventIds, true)) {
            $status = OrderStatus::PAID;
        }

        // Une commande « en attente » ne peut plus être vieille : passé quinze
        // minutes, `orders:cancel-unpaid` l'annule et rend ses places. En la
        // datant de plusieurs mois, le seeder produisait un état que la
        // production ne connaît pas — et le planificateur les balayait toutes
        // dans la minute suivant le seeding, déplaçant les chiffres des rapports
        // sous les yeux de qui venait de les ouvrir.
        //
        // On la ramène donc dans la fenêtre. Elle pèse un peu moins sur les
        // courbes des mois anciens, ce qui est le prix d'une donnée vraie.
        if ($status === OrderStatus::PENDING) {
            $placedAt = now()->subMinutes(random_int(1, CancelUnpaidOrdersCommand::GRACE_MINUTES - 2));
        }

        /** @var Order $order */
        $order = Order::factory()->create([
            'user_id' => $buyer->id,
            'order_number' => $this->nextOrderNumber(),
            'first_name' => $buyer->first_name,
            'last_name' => $buyer->last_name,
            'email' => $buyer->email,
            'phone' => $buyer->phone ?? '+22890000000',
            'status' => $status,
            'payment_method' => fake()->randomElement(['flooz', 'tmoney']),
            'paid_at' => $status === OrderStatus::PENDING ? null : $placedAt->copy()->addMinutes(random_int(1, 30)),
            'created_at' => $placedAt,
            'updated_at' => $placedAt,
        ]);

        $total = 0.0;
        $isPast = $event->end_date !== null && $event->end_date->isPast();

        foreach ($tiers as $tier) {
            $quantity = random_int(1, 3);
            $unit = (float) $tier->currentPrice();

            $this->createDated(OrderItem::class, [
                'order_id' => $order->id,
                'ticket_type_id' => $tier->id,
                'quantity' => $quantity,
                'unit_price' => $unit,
                'subtotal' => $unit * $quantity,
            ], $placedAt);

            $total += $unit * $quantity;

            // Les billets ne sont émis qu'une fois la commande payée — c'est le
            // listener OrderPaid qui s'en charge en production.
            if ($status === OrderStatus::PENDING) {
                continue;
            }

            foreach (range(1, $quantity) as $ignored) {
                $ticket = Ticket::factory()->create([
                    'order_id' => $order->id,
                    'ticket_type_id' => $tier->id,
                    'created_at' => $placedAt,
                    'updated_at' => $placedAt,
                ]);

                if ($status === OrderStatus::CANCELLED) {
                    $ticket->update(['status' => TicketStatus::CANCELLED]);

                    continue;
                }

                if ($status === OrderStatus::REFUNDED) {
                    $ticket->update([
                        'status' => TicketStatus::REFUNDED,
                        'refunded_at' => $placedAt->copy()->addDays(random_int(1, 10)),
                        'refund_reason' => 'Demande du participant',
                    ]);

                    continue;
                }

                // Sur un événement passé, la plupart des porteurs sont entrés.
                // C'est ce qui remplit « Entrées enregistrées » ; le reste
                // deviendra « expiré » au prochain passage de tickets:expire.
                if ($isPast && random_int(1, 100) <= 70) {
                    $this->checkIn($ticket, $event, $event->start_date->copy()->addMinutes(random_int(-60, 120)));
                }
            }
        }

        $order->update(['total_amount' => $total]);

        Payment::factory()->create([
            'order_id' => $order->id,
            'amount' => $total,
            'method' => fake()->randomElement([PaymentMethod::FLOOZ, PaymentMethod::TMONEY]),
            'status' => $status === OrderStatus::PENDING ? PaymentStatus::NON_PAYE : PaymentStatus::PAYE,
            'paid_at' => $status === OrderStatus::PENDING ? null : $placedAt,
            'created_at' => $placedAt,
            'updated_at' => $placedAt,
        ]);

        // En production, toute commande payée reçoit son lien de téléchargement —
        // c'est par lui que passent le PDF et l'image du QR. Le seeder ne le
        // posait que sur les commandes du compte de démonstration, ce qui laissait
        // l'écran « Liens de téléchargement » à deux lignes et rendait les billets
        // de l'historique impossibles à ouvrir depuis le back-office.
        if ($status === OrderStatus::PAID) {
            $this->createDated(TicketDownloadLink::class, [
                'token' => Str::random(40),
                'order_id' => $order->id,
                // Sept jours après l'événement, comme le calcule
                // TicketDownloadService : un lien d'un concert de mars ne doit
                // pas être encore ouvert aujourd'hui, sinon le filtre « expiré »
                // de l'écran n'a rien à montrer.
                'expires_at' => ($event->end_date ?? $event->start_date)->copy()->addDays(7),
                'download_count' => random_int(0, 8),
                'max_downloads' => 50,
            ], $placedAt);
        }
    }

    /**
     * Fait entrer un porteur, comme le portique l'aurait fait.
     *
     * Le billet passe à « utilisé » **et** laisse une ligne dans `check_ins` :
     * les deux ne sont pas redondants. Le statut du billet répond à « peut-il
     * encore entrer ? », la ligne de journal répond à « qui l'a laissé entrer,
     * quand, avec quel appareil ? » — c'est elle que lit l'écran « Historique des
     * validations », qui restait vide quand le seeder ne posait que la date.
     *
     * L'agent est le compte de l'organisateur de l'événement : c'est lui qui
     * tient l'entrée, et un scan sans auteur n'est pas opposable.
     */
    private function checkIn(Ticket $ticket, Event $event, CarbonInterface $scannedAt): void
    {
        $agentId = $event->organizer?->user_id;

        $ticket->update([
            'status' => TicketStatus::USED,
            'checked_in_at' => $scannedAt,
            'checked_in_by' => $agentId,
        ]);

        $this->createDated(CheckIn::class, [
            'ticket_id' => $ticket->id,
            'scanned_by' => $agentId,
            'scanned_at' => $scannedAt,
            'device_info' => fake()->randomElement([
                'Portique A — Android 13',
                'Portique B — iPhone 14',
                'Contrôle mobile — Android 12',
                'Entrée VIP — iPad',
            ]),
        ], $scannedAt);
    }

    /**
     * Les demandes de retrait des organisateurs.
     *
     * Chaque organisateur qui a réellement encaissé quelque chose a un historique
     * de retraits : sans lui, l'écran « Retraits » s'ouvre vide et rien ne permet
     * de montrer le circuit approuver → payer, ni le solde engagé.
     *
     * Les montants sont bornés par le revenu net réellement disponible et
     * demandés dans cet ordre — payé, puis approuvé, puis en attente. C'est
     * l'ordre chronologique d'une plateforme qui tourne, et il garantit surtout
     * que la somme reste sous le solde : `availableBalance()` déduit les demandes
     * en attente, donc un jeu de retraits qui le dépasserait rendrait un solde
     * négatif à l'écran.
     */
    private function seedWithdrawals(): void
    {
        $admin = User::where('email', 'admin@test.tg')->first()
            ?? User::where('email', 'superadmin@test.tg')->first();

        $rejectedIssued = false;

        foreach (Organizer::with('user')->get() as $organizer) {
            $net = $organizer->netRevenue();

            // Sous ce seuil, un retrait n'aurait pas de sens : on ne demande pas
            // un virement mobile money de trois mille francs.
            if ($net < 100000) {
                continue;
            }

            $phone = $organizer->user?->phone ?? '+22890000000';

            // 60 % du net au maximum, réparti sur trois demandes : il doit rester
            // un solde disponible affiché à l'écran.
            $budget = $net * 0.6;

            $plan = [
                ['status' => WithdrawalStatus::PAID, 'share' => 0.5, 'weeks' => 10],
                ['status' => WithdrawalStatus::APPROVED, 'share' => 0.3, 'weeks' => 4],
                ['status' => WithdrawalStatus::PENDING, 'share' => 0.2, 'weeks' => 0],
            ];

            foreach ($plan as $row) {
                $amount = round($budget * $row['share'], -2);

                if ($amount < 5000) {
                    continue;
                }

                $requestedAt = now()->subWeeks($row['weeks'])->subDays(random_int(0, 6));
                $decided = $row['status'] !== WithdrawalStatus::PENDING;

                $this->createDated(Withdrawal::class, [
                    'organizer_id' => $organizer->id,
                    'requester_phone' => $phone,
                    'amount' => $amount,
                    'status' => $row['status'],
                    'payment_method' => fake()->randomElement(['flooz', 'tmoney']),
                    'processed_at' => $decided ? $requestedAt->copy()->addDays(random_int(1, 3)) : null,
                    'processed_by' => $decided ? $admin?->id : null,
                    'notes' => $decided ? 'Demande vérifiée : recettes encaissées, identité du bénéficiaire confirmée.' : null,
                    // La référence n'existe que quand l'argent est parti : c'est
                    // ce qu'on présente à un organisateur qui dit n'avoir rien
                    // reçu. Un retrait approuvé mais pas encore payé n'en a pas.
                    'payout_reference' => $row['status'] === WithdrawalStatus::PAID
                        ? strtoupper(fake()->randomElement(['FLZ', 'TMY'])).'-'.fake()->numerify('##########')
                        : null,
                ], $requestedAt);
            }

            // Un seul refus, sur le premier organisateur qui s'y prête : c'est le
            // cas qui montre le motif rendu à l'organisateur. Il est daté d'avant
            // les autres demandes et n'engage rien — un retrait refusé ne pèse
            // pas sur le solde, d'où un montant qui peut dépasser le budget.
            if (! $rejectedIssued) {
                $rejectedIssued = true;

                $this->createDated(Withdrawal::class, [
                    'organizer_id' => $organizer->id,
                    'requester_phone' => $phone,
                    'amount' => round($net * 0.9, -2),
                    'status' => WithdrawalStatus::REJECTED,
                    'payment_method' => 'flooz',
                    'processed_at' => now()->subWeeks(6),
                    'processed_by' => $admin?->id,
                    'notes' => 'Montant supérieur aux recettes encaissées à la date de la demande.',
                ], now()->subWeeks(6)->subDays(2));
            }
        }
    }

    /**
     * Les coupons, rattachés aux événements sur lesquels ils s'appliquent.
     *
     * La table pivot `event_coupon` restait vide : un coupon sans événement ne
     * peut être appliqué nulle part, donc l'écran des coupons montrait des codes
     * qui ne servaient à rien. Deux codes lisibles à la main en plus des codes
     * générés — on ne dicte pas « X7K2P9QA » au téléphone.
     */
    private function seedCoupons(): void
    {
        $events = Event::query()
            ->where('end_date', '>=', now())
            ->inRandomOrder()
            ->limit(12)
            ->get();

        if ($events->isEmpty()) {
            return;
        }

        $coupons = Coupon::factory()->count(4)->create()
            ->push(Coupon::create([
                'code' => 'BIENVENUE10',
                'type' => CouponType::PERCENT,
                'value' => 10,
                'max_usage' => 500,
                'used_count' => random_int(20, 120),
                'start_date' => now()->subMonth(),
                'end_date' => now()->addMonths(3),
            ]))
            ->push(Coupon::create([
                'code' => 'HARMATTAN5000',
                'type' => CouponType::FIXED,
                'value' => 5000,
                'max_usage' => 100,
                'used_count' => random_int(5, 40),
                'start_date' => now()->subWeeks(2),
                'end_date' => now()->addMonth(),
            ]));

        foreach ($coupons as $coupon) {
            $coupon->events()->syncWithoutDetaching(
                $events->random(random_int(1, 3))->pluck('id')->all(),
            );
        }
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

    /**
     * Crée une ligne en lui imposant sa date.
     *
     * `create()` laisse tomber `created_at` en silence quand la colonne n'est pas
     * dans `$fillable` — et elle ne l'est dans aucun de ces modèles, à raison :
     * en production personne ne choisit la date d'une écriture. Un seeder, si :
     * une ligne d'historique datée d'aujourd'hui n'a plus rien d'un historique,
     * et les courbes des rapports se réduisent à un pic sur le jour du seeding.
     *
     * D'où `unguarded()`, qui suspend la protection le temps de l'écriture plutôt
     * que d'ouvrir `$fillable` pour de bon.
     *
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  class-string<TModel>  $class
     * @param  array<string, mixed>  $attributes
     * @return TModel
     */
    private function createDated(string $class, array $attributes, CarbonInterface $at)
    {
        return EloquentModel::unguarded(fn () => $class::create([
            ...$attributes,
            'created_at' => $at,
            'updated_at' => $at,
        ]));
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
