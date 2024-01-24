<?php

namespace Vyuldashev\LaravelOpenApi\Builders;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Components;
use Illuminate\Support\Collection;
use Vyuldashev\LaravelOpenApi\Builders\Components\CallbacksBuilder;
use Vyuldashev\LaravelOpenApi\Builders\Components\RequestBodiesBuilder;
use Vyuldashev\LaravelOpenApi\Builders\Components\ResponsesBuilder;
use Vyuldashev\LaravelOpenApi\Builders\Components\SchemasBuilder;
use Vyuldashev\LaravelOpenApi\Builders\Components\SecuritySchemesBuilder;
use Vyuldashev\LaravelOpenApi\Generator;

class ComponentsBuilder
{
    private string $collection;

    protected CallbacksBuilder $callbacksBuilder;
    protected RequestBodiesBuilder $requestBodiesBuilder;
    protected ResponsesBuilder $responsesBuilder;
    protected SchemasBuilder $schemasBuilder;
    protected SecuritySchemesBuilder $securitySchemesBuilder;

    public function __construct(
        string $collection = Generator::COLLECTION_DEFAULT
    ) {
        $this->collection = $collection;

        $this->callbacksBuilder = new CallbacksBuilder($this->getPathsFromConfig('callbacks'));
        $this->requestBodiesBuilder = new RequestBodiesBuilder($this->getPathsFromConfig('request_bodies'));
        $this->responsesBuilder = new ResponsesBuilder($this->getPathsFromConfig('responses'));
        $this->schemasBuilder = new SchemasBuilder($this->getPathsFromConfig('schemas'));
        $this->securitySchemesBuilder = new SecuritySchemesBuilder($this->getPathsFromConfig('security_schemes'));
    }

    public function build(
        array $middlewares = []
    ): ?Components {
        $callbacks = $this->callbacksBuilder->build($this->collection);
        $requestBodies = $this->requestBodiesBuilder->build($this->collection);
        $responses = $this->responsesBuilder->build($this->collection);
        $schemas = $this->schemasBuilder->build($this->collection);
        $securitySchemes = $this->securitySchemesBuilder->build($this->collection);

        $components = Components::create();

        $hasAnyObjects = false;

        if (count($callbacks) > 0) {
            $hasAnyObjects = true;

            $components = $components->callbacks(...$callbacks);
        }

        if (count($requestBodies) > 0) {
            $hasAnyObjects = true;

            $components = $components->requestBodies(...$requestBodies);
        }

        if (count($responses) > 0) {
            $hasAnyObjects = true;
            $components = $components->responses(...$responses);
        }

        if (count($schemas) > 0) {
            $hasAnyObjects = true;
            $components = $components->schemas(...$schemas);
        }

        if (count($securitySchemes) > 0) {
            $hasAnyObjects = true;
            $components = $components->securitySchemes(...$securitySchemes);
        }

        if (! $hasAnyObjects) {
            return null;
        }

        foreach ($middlewares as $middleware) {
            app($middleware)->after($components);
        }

        return $components;
    }

    private function getPathsFromConfig(string $type): array
    {
        $directories = config('openapi.collections.'.$this->collection.'.locations.'.$type, []);

        foreach ($directories as &$directory) {
            $directory = glob($directory, GLOB_ONLYDIR);
        }

        return (new Collection($directories))
            ->flatten()
            ->unique()
            ->toArray();
    }
}
