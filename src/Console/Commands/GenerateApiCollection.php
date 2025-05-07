<?php

namespace JudeUfuoma\ApiToolkit\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionMethod;

class GenerateApiCollection extends Command
{
    protected $signature = 'api-toolkit:generate';
    protected $description = 'Generate a API Client collection from your routes';


    public function handle()
    {
        $routes = collect(Route::getRoutes())->filter(function ($route) {
            return str_starts_with($route->uri(), 'api/');
        });

        $items = [];

        foreach ($routes as $route) {
            $methods = array_diff($route->methods(), ['HEAD']);
            foreach ($methods as $method) {
                $items[] = [
                    'name' => $route->uri(),
                    'request' => [
                        'method' => $method,
                        'header' => array_merge(
                            $this->getAuthHeader($route),
                            $this->getJsonHeader($method)
                        ),
                        'url' => [
                            'raw' => '{{base_url}}/' . $route->uri(),
                            'host' => ['{{base_url}}'],
                            'path' => explode('/', $route->uri()),
                            'query' => $this->getQueryParams($route, $method),
                        ],
                        'description' => $this->getRouteDocComment($route),
                        'body' => $this->getRequestBody($route, $method),
                    ],
                ];
            }
        }

        $collection = [
            'info' => [
                'name' => config('app.name') . ' API',
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
            ],
            'item' => $items,
        ];

        $json = json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents(base_path('postman_collection.json'), $json);

        $this->info('Postman collection generated at: postman_collection.json');
    }

    protected function getAuthHeader($route): array
    {
        $middlewares = $route->gatherMiddleware();
        return collect($middlewares)->contains('auth:api') || collect($middlewares)->contains('auth')
            ? [[
                'key' => 'Authorization',
                'value' => 'Bearer {{token}}',
                'type' => 'text',
            ]]
            : [];
    }

    protected function getJsonHeader($method): array
    {
        return in_array($method, ['POST', 'PUT', 'PATCH']) ? [[
            'key' => 'Content-Type',
            'value' => 'application/json',
            'type' => 'text',
        ]] : [];
    }

    protected function getRouteDocComment($route): string
    {
        if (!isset($route->action['controller'])) {
            return '';
        }

        [$controller, $method] = explode('@', $route->action['controller']);

        try {
            $refMethod = new ReflectionMethod($controller, $method);
            // Initialize the DocBlockFactory
            $docBlockFactory = DocBlockFactory::createInstance();

            // Example usage with a method reflection
            $docBlock = $docBlockFactory->create($refMethod->getDocComment());
            return $docBlock->getSummary();
            // return (new DocBlockFactory)->create($refMethod->getDocComment())->getSummary();
        } catch (\Exception) {
            return '';
        }
    }

    protected function getQueryParams($route, $method): array
    {
        if (!in_array($method, ['GET', 'DELETE'])) {
            return [];
        }

        $doc = $this->getRawDocComment($route);
        preg_match_all('/@queryParam\s+(\w+)\s+\w+\s+(.*?)\s+(Example:\s+(.*))?$/im', $doc, $matches, PREG_SET_ORDER);

        $query = [];

        foreach ($matches as $match) {
            $key = $match[1];
            $example = $match[4] ?? 'sample';
            $query[] = [
                'key' => $key,
                'value' => $example,
            ];
        }

        return $query;
    }

    protected function getRequestBody($route, $method): ?array
    {
        if (!in_array($method, ['POST', 'PUT', 'PATCH'])) {
            return null;
        }

        $doc = $this->getRawDocComment($route);
        preg_match_all('/@bodyParam\s+(\w+)\s+\w+\s+(.*?)\s+(Example:\s+(.*))?$/im', $doc, $matches, PREG_SET_ORDER);

        $params = [];

        foreach ($matches as $match) {
            $key = $match[1];
            $example = $match[4] ?? 'sample_value';
            $params[$key] = is_numeric($example) ? (int) $example : $example;
        }

        if (empty($params)) {
            $params = ['sample_key' => 'sample_value'];
        }

        return [
            'mode' => 'raw',
            'raw' => json_encode($params, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        ];
    }

    protected function getRawDocComment($route): string
    {
        if (!isset($route->action['controller'])) {
            return '';
        }

        [$controller, $method] = explode('@', $route->action['controller']);

        try {
            $refMethod = new ReflectionMethod($controller, $method);
            return $refMethod->getDocComment() ?: '';
        } catch (\Exception) {
            return '';
        }
    }
}
