<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Vyuldashev\LaravelOpenApi\Generator;

Route::group(['as' => 'openapi.'], function () {
    foreach (config('openapi.collections', []) as $name => $config) {
        $uri = Arr::get($config, 'route.uri');

        if (! $uri) {
            continue;
        }

        Route::get($uri, function (Generator $generator) use ($name) {
            return $generator->generate($name);
        })
            ->where('name', $name)
            ->name($name.'.specification')
            ->defaults('collection', $name)
            ->middleware(Arr::get($config, 'route.middleware'));
    }
});
