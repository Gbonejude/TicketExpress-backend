<?php

declare(strict_types=1);

namespace App\Actions\V1\Review;

use App\Actions\Contracts\Action;
use App\Models\Review;

final class DeleteReviewAction implements Action
{
    /**
     * @param  array{model: Review}  $data
     */
    public function execute(array $data): mixed
    {
        return (bool) $data['model']->delete();
    }
}
