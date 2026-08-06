<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Cache;

abstract class TestCase extends BaseTestCase
{
    /**
     * The public catalogue is cached (see {@see \App\Support\CatalogueCache}),
     * and the test cache store is `array` — which lives for the whole process,
     * not for one test. `RefreshDatabase` rolls the database back but leaves
     * that cache behind, so without this a test could be served the previous
     * test's catalogue and pass or fail depending on the order it ran in.
     */
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }
}
