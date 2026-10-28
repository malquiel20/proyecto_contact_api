<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Todas las pruebas de tests/Feature usan el TestCase de Laravel.
|
*/

pest()->extend(Tests\TestCase::class)
    // ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');
