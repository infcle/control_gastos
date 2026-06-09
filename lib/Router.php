<?php

class Router
{
    private array $routes = [];

    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    /**
     * Resuelve una URI amigable y devuelve controller, action y params.
     *
     * @param string $uri Ej: 'user/edit/5'
     * @return array ['controller' => string, 'action' => string, 'params' => array]
     * @throws Exception Si no hay match
     */
    public function resolve(string $uri): array
    {
        $uri = trim(parse_url($uri, PHP_URL_PATH), '/');

        foreach ($this->routes as $pattern => $handler) {
            $regex = $this->patternToRegex($pattern);

            if (preg_match($regex, $uri, $matches)) {
                array_shift($matches); // sacar el match completo
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $params = array_values($params);

                return [
                    'controller' => $handler[0],
                    'action'     => $handler[1],
                    'params'     => $params,
                ];
            }
        }

        throw new Exception("No route found for: $uri", 404);
    }

    /**
     * Convierte un pattern con :param a regex.
     * Ej: 'user/edit/:id' → /^user\/edit\/(?P<id>[^\/]+)$/
     */
    private function patternToRegex(string $pattern): string
    {
        $regex = preg_replace('/:([a-zA-Z_]+)/', '(?P<$1>[^/]+)', $pattern);
        return '/^' . str_replace('/', '\/', $regex) . '$/';
    }

    /**
     * Genera una URL amigable a partir de una ruta nombre y parámetros.
     *
     * @param string $name Nombre de la ruta (key en routes)
     * @param array $params ['id' => 5] para reemplazar :params
     * @return string URL relativa
     */
    public function generate(string $name, array $params = []): string
    {
        if (!isset($this->routes[$name])) {
            throw new Exception("Route not found: $name");
        }

        $pattern = $name;

        foreach ($params as $key => $value) {
            $pattern = str_replace(":$key", $value, $pattern);
        }

        return $pattern;
    }
}
