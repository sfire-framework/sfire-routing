<?php
/**
 * sFire Framework (https://sfire.io)
 *
 * @link      https://github.com/sfire-framework/ for the canonical source repository
 * @copyright Copyright (c) 2014-2020 sFire Framework.
 * @license   http://sfire.io/license BSD 3-CLAUSE LICENSE
 */

declare(strict_types=1);

namespace sFire\Routing;

use sFire\Http\Request;
use sFire\Http\UrlParser;
use sFire\Routing\Exception\InvalidArgumentException;
use sFire\Routing\Exception\RuntimeException;


/**
 * Class Router
 * @package sFire\Routing
 */
class Router {


    /**
     * Holds the Router instance
     * @var self
     */
    private static ?self $instance = null;


    /**
     * Contains all the grouped routes
     * @var array
     */
    private static array $group = [];


    /**
     * Contains all the found routes
     * @var Route[]
     */
    private static array $routes = [];


    /**
     * Contains the current route
     * @var RouteEntity
     */
    public static ?RouteEntity $route = null;


    /**
     * Contains all parsed route entity objects
     * @var RouteEntity[]
     */
    public static array $routeEntities = [];


    /**
     * Create and store new Route instance
     * @return self
     */
    public static function getInstance(): self {

        if(null === static::$instance) {
            static::$instance = new self();
        }

        return static::$instance;
    }


    /**
     * Returns a RouteEntity based on a given identifier
     * @param string $identifier The identifier of the route
     * @return RouteEntity
     * @throws RuntimeException
     */
    public static function getRoute(?string $identifier = null): RouteEntity {

        if(null !== $identifier && false === isset(static::$routes[$identifier])) {
            throw new RuntimeException(sprintf('Route with identifier "%s" not found in routes.php', $identifier));
        }

        if(null === $identifier) {
            return static::$route;
        }

        return static :: createRouteEntity(static::$routes[$identifier]);
    }


    /**
     * Returns all routes as an array of RouteEntities
     * @return RouteEntity[]
     */
    public static function getRoutes(): array {

        $routes = [];

        foreach(static::$routes as $route) {
            $routes[] = static :: createRouteEntity($route);
        }

        return $routes;
    }


    /**
     * Set the route module
     * @param string $module The name of the module
     * @return Route
     */
    public static function module(string $module): Route {

        $route = new Route();
        $route -> module($module);

        return $route;
    }


    /**
     * Add a new group element to the stack
     * @param array $group
     * @return void
     */
    public static function addGroup(array $group): void {
        static::$group[] = $group;
    }


    /**
     * Remove last group element from the stack and returns the attributes of the previous route (if there is one)
     * @return array
     */
    public static function closeGroup(): array {

        array_pop(static::$group);
        $attributes = end(static::$group);

        return ($attributes ?: []);
    }


    /**
     * Return all the groups as one merged array
     * @return array
     */
    public static function getGroup(): array {

        $attr = [];

        foreach(static::$group as $group) {
            $attr = array_replace_recursive($attr, $group);
        }

        return $attr;
    }


    /**
     * Add or update a new or current route based on identifier
     * @param Route $route
     * @param string $identifier
     * @return void
     */
    public static function addRoute(Route $route, string $identifier): void {
        static::$routes[$identifier] = $route;
    }


    /**
     * Convert a route based on a given identifier to a URL
     * @param string $identifier The unique identifier of a existing route
     * @param null|array $variables [optional] Variables to be inserted into the route URL
     * @param null|string $domain [optional] A single domain/host that will be prepended to the route URL
     * @return string
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public static function url(string $identifier, ?array $variables = [], string $domain = null): string {

        $route = static::$routes[$identifier] ?? null;

        if(null === $route) {
            throw new RuntimeException(sprintf('Route with identifier "%s" not found in routes.php', $identifier));
        }

        $url 		= $route['url'];
        $parameters = 0;
        $optionals  = 0;

        //Match all variables from the route url
        preg_match_all('#{(.*?)(\?)?}#i', $url, $vars);

        //Count the amount of found variables and optional variables
        foreach($vars[2] as $optional) {

            if('?' === $optional) {
                $optionals++;
            }
            else {
                $parameters++;
            }
        }

        //Check if the amount of found variables matches the amount of given variables
        if(count($variables) < $parameters) {
            throw new InvalidArgumentException(sprintf('Incorrect number of parameters given for Router url with identifier "%s". Expecting %s with %s optional variables, but received %s.', $route['identifier'], $parameters, $optionals, count($variables)));
        }

        foreach($vars[0] as $index => $variable) {

            $match    = $vars[0][$index];
            $type     = $vars[1][$index];
            $optional = $vars[2][$index];
            $where    = $route['where'];
            $replace  = '(.*?)';

            if(true === isset($where[$type])) {
                $replace = $where[$type];
            }

            //Check if the variable is given in the variables array
            if(false === isset($variables[$type])) {

                if('?' !== $optional) {
                    throw new InvalidArgumentException(sprintf('Parameter "%s" not found in given variables array with router url with "%s" as identifier', $type, $route['identifier']));
                }

                $url = preg_replace('#'. '/?' . preg_quote($match) .'#i', '', $url, 1);
                continue;
            }

            //Check if the given variable matches the type of required variable
            if(false === (bool) preg_match('#^' . str_replace('#', '\#', $replace) . '$#i', (string) $variables[$type])) {
                throw new InvalidArgumentException(sprintf('Parameters given to router url width id "%s" do not match. Trying to match regular expression pattern "%s" with "%s" as subject', $route['identifier'], $replace, $variables[$type]));
            }

            //Create a URL with the given variable
            $url = preg_replace('#'. preg_quote($match) .'#i', (string) $variables[$type], $url, 1);
        }

        //Check if routes has a prefix and add it if it does
        $url = ($route['prefix']['url'] ?? '') . $url;

        //Add the domain to the url
        if(null !== $domain) {

            $domain = new UrlParser($domain);

            //Check if the scheme is set, otherwise set the current scheme
            if(null === $domain -> getScheme()) {
                $domain -> setScheme(Request::getScheme());
            }

            $domain -> setPath($url);
            $url = $domain -> generate();
        }

        return $url;
    }


    /**
     * Check if a Route exists by identifier with optional domain
     * @param string $identifier
     * @param null|string $domain
     * @return bool
     */
    public static function routeExists(string $identifier, string $domain = null): bool {

        //Check if route exists based on the given identifier
        if(false === isset(static::$routes[$identifier])) {
            return false;
        }

        //Check if domain exists in found route
        if(null !== $domain) {

            $domain  = new UrlParser($domain);
            $domains = static::$routes[$identifier]['domains'] ?? [];

            foreach($domains as $url) {

                if(true === (bool) preg_match('#^'. str_replace('#', '\#', $url) .'$#', $domain -> getHost()) || $url === $domain) {
                    return true;
                }
            }

            return false;
        }

        return true;
    }


    /**
     * Try to match the current request uri to a route and returns a new instance of RouteEntity if it does
     * @param null|string $url
     * @return null|RouteEntity
     */
    public static function getMatchedRoute(string $url = null): ?RouteEntity {

        $url = $url ?? Request :: getUrl() ?? null;

        if(null !== $url) {

            $url    = new UrlParser($url);
            $domain = $url -> filter([UrlParser::SUBDOMAIN, UrlParser::DOMAIN, UrlParser::EXTENSION]);

            foreach(static::$routes as $identifier => $route) {

                if(count($route['domains']) > 0) {

                    if(false === static :: matchDomain($route, $domain)) {
                        continue;
                    }
                }

                //Check if the current method matches the route method
                if(false === static :: isMethod($route, Request :: getMethod())) {
                    continue;
                }

                //Check if route is viewable and should be executed
                if(false === $route['viewable']) {
                    continue;
                }

                //Check if url matches the route url
                $uri = $url -> getTrimmedPath();

                if(true === $route['strict']) {
                    $uri .= $url -> getQuery();
                }

                if(false === static :: matchUrl($route, $uri)) {
                    continue;
                }

                $variables = static :: parseUrlVariables($uri, $route);
                $route['variables'] = $variables;

                return static :: createRouteEntity($route);
            }
        }

        return null;
    }


    /**
     * Returns a matching route based on a given url path (without the scheme, domain, subdomain, ports, etc.)
     * @param string $url
     * @return null|RouteEntity
     */
    public static function urlToRoute(string $url): ?RouteEntity {

        foreach(static::$routes  as $identifier => $route) {

            if(false === static :: matchUrl($route, $url)) {
                continue;
            }

            $variables = static :: parseUrlVariables($url, $route);
            $route['variables'] = $variables;

            return static :: createRouteEntity($route);
        }

        return null;
    }


    /**
     * Creates a RouteEntity object and returns this based on route array values
     * @param array $route
     * @return RouteEntity
     * @throws RuntimeException
     */
    public static function createRouteEntity(array $route): RouteEntity {

        if(false === isset($route['identifier'])) {
            throw new RuntimeException('Could not create a new RouteEntity without a valid route identifier');
        }

        if(true === isset(static::$routeEntities[$route['identifier']])) {
            return static::$routeEntities[$route['identifier']];
        }

        $entity = new RouteEntity();
        $entity -> setAction($route['action'] ?? null);
        $entity -> setAssign($route['assign'] ?? null);
        $entity -> setController($route['controller'] ?? null);
        $entity -> setDomains($route['domains'] ?? null);
        $entity -> setIdentifier($route['identifier'] ?? null);
        $entity -> setMethod($route['method'] ?? null);
        $entity -> setPrefix($route['prefix'] ?? null);
        $entity -> setType((true === isset($route['type']) ? (string) $route['type'] : null));
        $entity -> setUrl($route['url'] ?? null);
        $entity -> setWhere($route['where'] ?? null);
        $entity -> setMiddleware($route['middleware'] ?? null);
        $entity -> setModule($route['module'] ?? null);

        if($variableName = ($route['locale']['variableName'] ?? null)) {

            if($locale = ($route['variables'][$variableName] ?? $route['locale']['default'] ?? null)) {
                $entity -> setLocale($locale);
            }
        }

        $entity -> setVariables($route['variables'] ?? null);

        static::$routeEntities[$entity -> getIdentifier()] = $entity;
        return $entity;
    }


    /**
     * Returns a route with the type error based on a given type i.e. 404, 403.
     * If a error route could not be found, null is returned
     * @param string $type The type of error route i.e. 404, 403.
     * @return null|RouteEntity
     */
    public static function getErrorRoute(string $type) {

        foreach(static::$routes as $route) {

            if($route['type'] === $type) {
                return static :: createRouteEntity($route);
            }
        }

        return null;
    }


    /**
     * Sets the current route
     * @param RouteEntity $route
     * @return void
     */
    public static function setRoute(RouteEntity $route): void {
        static::$route = $route;
    }


    /**
     * Formats all the routes urls to a regex pattern that sFire understands to match the current url to the route url
     * @return void
     */
    public static function formatRoutes(): void {

        $entities = [];

        foreach(static::$routes as $identifier => $route) {

            $entity = $route -> getAttributes();
            $entity['variables'] = [];
            $url = preg_quote($entity['url'], '/');

            //Prepend prefix for url
            if($prefix = $entity['prefix']) {
                $url = $prefix['url'] . $url;
            }

            //Matches forward slashes, variables and if the found variables are optional or not
            if(preg_match_all('#(\\\/)*\\\{([a-zA-Z0-9_.]+)(\\\\\?)?\\\}#i', (string) $url, $matches)) {

                foreach($matches[0] as $index => $match) {

                    $match 		= $matches[0][$index];
                    $slash 		= $matches[1][$index];
                    $type 		= $matches[2][$index];
                    $optional 	= $matches[3][$index];
                    $where 		= $entity['where'];
                    $variable 	= '';

                    if('' !== $slash) {
                        $variable .= '\/';
                    }

                    $variable .= (true === isset($where[$type]) ? $where[$type] : '[^?/]+');
                    $variable  = ('' !== $optional ? '('. $variable .')?' : '('. $variable .')');
                    $url       = preg_replace('#' . preg_quote($match, '/') . '#', $variable, $url, 1);

                    //Add the found variable name
                    $entity['variables'][] = $type;
                }
            }

            $entity['match'] = $url;
            $entities[$entity['identifier']] = $entity;
        }

        static::$routes = $entities;
    }


    /**
     * Returns if a given route matches a given host
     * @param array $route The route array
     * @param string $host A domain name with extension
     * @return bool
     */
    private static function matchDomain(array $route, string $host): bool {

        foreach($route['domains'] as $domain) {

            if($domain === $host || true === (bool) preg_match('#^'. str_replace('#', '\#', $domain) .'$#', (string) $host)) {
                return true;
            }
        }

        return false;
    }


    /**
     * Checks if the current route method matches a given method or that the current route method is "any"
     * @param array $route
     * @param string $method The HTTP method, i.e. PUT or GET
     * @return bool
     */
    public static function isMethod(array $route, string $method): bool {
        return true === in_array('any', $route['method']) || true === in_array(strtolower($method), $route['method']);
    }


    /**
     * Check if an url matches the current route url
     * @param array $route
     * @param string $url
     * @return bool True if current route url matches the given url, false if not
     */
    public static function matchUrl(array $route, string $url): bool {
        return (bool) preg_match('#^'. str_replace('#', '\#', (string) $route['match']) .'$#', $url);
    }


    /**
     * Extracts route variables and returns these
     * @param string $url The URL that contains route variables that needs to be extracted
     * @param array $route
     * @return array
     */
    private static function parseUrlVariables(string $url, array $route): array {

        $variables = [];

        if(true === (bool) preg_match('#^'. str_replace('#', '\#', $route['match']) .'$#', (string) $url, $matches)) {

            array_shift($matches);

            foreach($route['variables'] as $index => $variable) {
                $variables[$variable] = isset($matches[$index]) ? ltrim($matches[$index], '\/') : null;
            }
        }

        return $variables;
    }
}