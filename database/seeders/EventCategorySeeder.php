<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\EventCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

final class EventCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Concert',
            'Conférence',
            'Festival',
            'Sport',
            'Théâtre',
            'Cinéma',
            'Exposition',
            'Networking',
            'Formation',
            'Gala',
            'Spectacle',
            'Comédie',
            'Danse',
            'Workshop',
            'Séminaire',
        ];

        foreach ($categories as $category) {
            EventCategory::create([
                'name' => $category,
                'slug' => Str::slug($category),
            ]);
        }

        $this->command->info('✅ '.count($categories).' categories created!');
    }
}
