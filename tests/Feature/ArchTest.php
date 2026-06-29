<?php

use App\Actions\Contracts\Action;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;

arch('no debugging statements are left in the codebase')
    ->expect(['dd', 'dump', 'var_dump', 'ray', 'die'])
    ->not->toBeUsed();

arch('all V1 actions implement the Action contract')
    ->expect('App\Actions\V1')
    ->toImplement(Action::class);

arch('all controllers extend the base Controller')
    ->expect('App\Http\Controllers\Api\V1')
    ->toExtend(Controller::class);

arch('all contracts are interfaces')
    ->expect('App\Contracts')
    ->toBeInterfaces();

arch('all models extend the Eloquent Model')
    ->expect('App\Models')
    ->toExtend(Model::class);

arch('all exceptions extend RuntimeException or its parents')
    ->expect('App\Exceptions')
    ->toExtend(RuntimeException::class);

arch('services are plain classes with no framework coupling')
    ->expect('App\Services')
    ->toBeClasses();
