<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

final class Media extends SpatieMedia
{
    use HasUlids;
}
