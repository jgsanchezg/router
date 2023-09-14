<?php

namespace Rareloop\Router;

use Spatie\Macroable\Macroable;

class RouteGroup implements Routable
{
    use VerbShortcutsTrait, Macroable;

    protected $router;
    protected $prefix;
    protected $middleware = [];
    protected $suffix;

    public function __construct($params, $router)
    {
        $prefix = null;
        $middleware = [];
        $suffix = [];

        if (is_string($params)) {
            $prefix = $params;
        }

        if (is_array($params)) {
            $prefix = $params['prefix'] ?? null;
            $suffix = $params['sufix'] ?? null;

            $middleware = $params['middleware'] ?? [];

            if (!is_array($middleware)) {
                $middleware = [$middleware];
            }

            $this->middleware += $middleware;
        }

        $this->prefix = is_string($prefix) ? trim($prefix, ' /') : null;
        $this->suffix = $suffix;

        $this->router = $router;
    }

    private function appendPrefixToUri(string $uri)
    {
        return $this->prefix . '/' . ltrim($uri, '/');
    }

    private function appendSuffixToUri() {
		return $this->suffix;
	}

    public function map(array $verbs, string $uri, $callback): Route
    {
        return $this->router->map($verbs, $this->appendPrefixToUri($uri) . $this->appendSuffixToUri(), $callback)->middleware($this->middleware);
    }

    public function group($params, $callback): RouteGroup
    {
        if (is_string($params)) {
            $params = $this->appendPrefixToUri($params);
        } elseif (is_array($params)) {
            $params['prefix'] = $params['prefix'] ? $this->appendPrefixToUri($params['prefix']) : null;
            $params['suffix'] = $params['sufix'] ?? $this->appendSuffixToUri();
        }

        $group = new RouteGroup($params, $this->router);

        call_user_func($callback, $group);

        return $this;
    }
}
