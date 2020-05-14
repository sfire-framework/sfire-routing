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

use sFire\Routing\Exception\InvalidArgumentException;
use sFire\Routing\Exception\LogicException;


/**
 * Class Route
 * @package sFire\Routing
 */
class Route {


    /**
     * Contains the attributes for the current route
     * @var array
     */
    private array $attributes = [

        'action'     => null,
        'assign'     => [],
        'controller' => '',
        'domains'    => [],
        'identifier' => null,
        'method'     => null,
        'middleware' => [],
        'locale'     => ['variableName' => null, 'default' => null],
        'prefix'     => [],
        'strict'     => false,
        'type'       => 'route',
        'url'        => '',
        'viewable'   => true,
        'where'      => []
    ];


    /**
     * Constructor.
     * @param array $attributes [optional] An array with predefined attributes which will be merged with existing attributes when grouping routes
     */
    public function __construct(array $attributes = []) {
        $this -> attributes = array_merge($this -> attributes, $attributes);
    }


    /**
     * Adds a new GET route listener
     * @param string $url The url of the route
     * @param string $identifier A unique identifier for the route
     * @return self
     */
    public function get(string $url, string $identifier): self {
        return $this -> listenTo('get', $url, $identifier);
    }


    /**
     * Adds a new POST route listener
     * @param string $url The url of the route
     * @param string $identifier A unique identifier for the route
     * @return self
     */
    public function post(string $url, string $identifier): self {
        return $this -> listenTo('post', $url, $identifier);
    }


    /**
     * Adds a new DELETE route listener
     * @param string $url The url of the route
     * @param string $identifier A unique identifier for the route
     * @return self
     */
    public function delete(string $url, string $identifier): self {
        return $this -> listenTo('delete', $url, $identifier);
    }


    /**
     * Adds a new PUT route listener
     * @param string $url The url of the route
     * @param string $identifier A unique identifier for the route
     * @return self
     */
    public function put(string $url, string $identifier): self {
        return $this -> listenTo('put', $url, $identifier);
    }


    /**
     * Adds a new HEAD route listener
     * @param string $url The url of the route
     * @param string $identifier A unique identifier for the route
     * @return self
     */
    public function head(string $url, string $identifier): self {
        return $this -> listenTo('head', $url, $identifier);
    }


    /**
     * Adds a new CONNECT route listener
     * @param string $url The url of the route
     * @param string $identifier A unique identifier for the route
     * @return self
     */
    public function connect(string $url, string $identifier): self {
        return $this -> listenTo('connect', $url, $identifier);
    }


    /**
     * Adds a new OPTIONS route listener
     * @param string $url The url of the route
     * @param string $identifier A unique identifier for the route
     * @return self
     */
    public function options(string $url, string $identifier): self {
        return $this -> listenTo('options', $url, $identifier);
    }


    /**
     * Adds a new TRACE route listener
     * @param string $url The url of the route
     * @param string $identifier A unique identifier for the route
     * @return self
     */
    public function trace(string $url, string $identifier): self {
        return $this -> listenTo('trace', $url, $identifier);
    }


    /**
     * Adds a new PATCH route listener
     * @param string $url The url of the route
     * @param string $identifier A unique identifier for the route
     * @return self
     */
    public function patch(string $url, string $identifier): self {
        return $this -> listenTo('patch', $url, $identifier);
    }


    /**
     * Adds a new any route listener
     * @param string $url The url of the route
     * @param string $identifier A unique identifier for the route
     * @return self
     */
    public function any(string $url, string $identifier): self {
        return $this -> listenTo('any', $url, $identifier);
    }


    /**
     * Adds an array with HTTP methods route listener for combining get, post, put etc. to one route
     * @param array $methods An array with HTTP methods
     * @param string $url The url of the route
     * @param string $identifier A unique identifier for the route
     * @return self
     * @throws InvalidArgumentException
     */
    public function methods(array $methods, string $url, string $identifier) {

        foreach($methods as $index => $method) {

            if(false === is_string($method)) {
                throw new InvalidArgumentException(sprintf('All HTTP methods passed as argument 1 to %s() should be of the type string, "%s" given', __METHOD__, gettype($method)));
            }
        }

        return $this -> listenTo($methods, $url, $identifier);
    }


    /**
     * Adds a locale language to the route
     * @param string $variableName
     * @param null|string $defaultLanguage
     * @return self
     */
    public function locale(string $variableName, string $defaultLanguage = null): self {

        $this -> attributes['locale'] = ['variableName' => $variableName, 'default' => $defaultLanguage];
        return $this;
    }


    /**
     * Adds middleware to a route
     * @param string[] $middleware
     * @return self
     */
    public function middleware(string ...$middleware): self {

        $group = Router :: getGroup();

        if(true === isset($group['middleware'])) {
            $this -> attributes['middleware'] = array_replace_recursive($middleware, $group['middleware']);
        }
        else {
            $this -> attributes['middleware'] = $middleware;
        }

        return $this;
    }


    /**
     * Set the action
     * @param string $action The name of the action
     * @return self
     */
    public function action(string $action): self {

        $this -> attributes['action'] = $action;
        return $this;
    }


    /**
     * Assign new variable to the route
     * @param string|array $key The name of the variable
     * @param mixed $value The value of the variable
     * @param bool $merge True if the existing assigned variables needs to be merged into the new assigned variable, false if the new variable needs to clear every other previous assigned variable
     * @return self
     * @throws InvalidArgumentException
     */
    public function assign($key, $value = null, bool $merge = true): self {

        if(false === is_string($key) && false === is_array($key)) {
            throw new InvalidArgumentException(sprintf('Argument 1 passed to %s() must be of the type string or array, "%s" given', __METHOD__, gettype($key)));
        }

        if(true === is_string($key)) {
            $key = [$key => $value];
        }

        $group = Router :: getGroup();

        if(true === $merge && true === isset($group['assign'])) {
            $this -> attributes['assign'] = array_replace_recursive($key, $group['assign']);
        }
        elseif(true === $merge) {
            $this -> attributes['assign'] = array_replace_recursive($key, $this -> attributes['assign'] ?? []);
        }
        else {
            $this -> attributes['assign'] = $key;
        }

        return $this;
    }


    /**
     * Set the controller
     * @param string $controller The name of the controller
     * @return self
     */
    public function controller(string $controller): self {

        $this -> attributes['controller'] = $controller;
        return $this;
    }


    /**
     * Set one or multiple domains to listen to for the current route
     * @param array $domains A list of domain names with extension (.com, .org, etc.)
     * @return self
     * @throws InvalidArgumentException
     */
    public function domains(array $domains): self {

        foreach($domains as $domain) {

            if(false === is_string($domain)) {
                throw new InvalidArgumentException(sprintf('All domain names given to %s() must be of the type string, "%s" given', __METHOD__, gettype($domain)));
            }
        }

        $this -> attributes['domains'] = $domains;
        return $this;
    }


    /**
     * Set or prepend the prefix
     * @param null|string $url
     * @param bool $prepend
     * @return self
     */
    public function prefix(?string $url = null, bool $prepend = true) {

        $prefix = $this -> attributes['prefix'];

        if(0 !== count($prefix)) {

            if(true === $prefix['prepend'] && true === $prepend) {
                $url = $prefix['url'] . $url;
            }
        }

        $this -> attributes['prefix'] = ['url' => $url, 'prepend' => $prepend];
        return $this;
    }


    /**
     * Set if the route may be viewable from the browser or only be redirected internally to
     * @param bool $viewable
     * @return self
     */
    public function viewable(bool $viewable): self {

        $this -> attributes['viewable'] = $viewable;
        return $this;
    }


    /**
     * Set if query in the url should be ignored or not
     * @param bool $strict
     * @return self
     */
    public function strict(bool $strict): self {

        $this -> attributes['strict'] = $strict;
        return $this;
    }


    /**
     * Set the route module
     * @param string $module The name of the module
     * @return self
     */
    public function module(string $module): self {

        $this -> attributes['module'] = $module;
        return $this;
    }


    /**
     * Inherit assigned route variables from another route by giving the route identifier
     * @param string|array $identifier A single or multiple route identifier to inherit assigned variables from
     * @param bool $merge Merge or overwrite variables
     * @return self
     * @throws InvalidArgumentException
     */
    public function uses($identifier, bool $merge = true): self {

        if(false === is_string($identifier) && false === is_array($identifier)) {
            throw new InvalidArgumentException(sprintf('Argument 1 passed to %s() must be of the type string or array, "%s" given', __METHOD__, gettype($identifier)));
        }

        if(true === is_string($identifier)) {
            $identifier = [$identifier];
        }

        foreach($identifier as $id) {

            if(false === is_string($id)) {
                throw new InvalidArgumentException(sprintf('One or more identifiers passed to the %s() method, are not of the type string, "%s" given', __METHOD__, gettype($id)));
            }
        }

        $this -> attributes['uses'] = $identifier;

        $group = Router :: getGroup();

        if(true === $merge && true === isset($group['uses'])) {
            $this -> attributes['uses'] = array_replace_recursive($this -> attributes['uses'], $group['uses']);
        }

        return $this;
    }


    /**
     * Define route params with regular expressions
     * @param string|array $key
     * @param mixed $value
     * @param bool $merge
     * @return self
     * @throws InvalidArgumentException
     */
    public function where($key, $value = null, bool $merge = true): self {

        if(false === is_string($key) && false === is_array($key)) {
            throw new InvalidArgumentException(sprintf('Argument 1 passed to %s() must be of the type string or array, "%s" given', __METHOD__, gettype($key)));
        }

        if(null !== $value && false === is_string($value)) {
            throw new InvalidArgumentException(sprintf('Argument 2 passed to %s() must be of the type string, "%s" given', __METHOD__, gettype($value)));
        }

        if(true === is_string($key)) {
            $key = [$key => $value];
        }

        $this -> attributes['where'] = $key;

        $group = Router :: getGroup();

        if(true === $merge && true === isset($group['where'])) {
            $this -> attributes['where'] = array_replace_recursive($this -> attributes['where'], $group['where']);
        }

        return $this;
    }


    /**
     * Start a new group for adding routes
     * @param callable $closure A callable function which will be injected with the grouped route
     * @return Route
     */
    public function group(callable $closure) {

        Router :: addGroup($this -> attributes);
        $route = new Route(Router :: getGroup());
        $closure($route);
        $this -> attributes = Router :: closeGroup();

        return $route;
    }


    /**
     * Set a new error route
     * @param int $type The HTTP error type, i.e 404 or 403
     * @param string $identifier A unique identifier for the route
     * @return self
     */
    public function error(int $type, string $identifier): self {

        $this -> attributes['method'] 	  = ['any'];
        $this -> attributes['type']       = $type;
        $this -> attributes['identifier'] = $identifier;

        $attributes = array_merge(Router :: getGroup(), $this -> attributes);
        $route = new Route($attributes);
        $route -> viewable(false);

        Router :: addRoute($route, $identifier);

        return $route;
    }


    /**
     * Returns all the attributes for the current route
     * @return array
     */
    public function getAttributes(): array {
        return $this -> attributes;
    }


    /**
     * Sets a new route listener
     * @param string|array $method A single or an array with HTTP methods
     * @param string $url The url of the route
     * @param string $identifier A unique identifier for the route
     * @return Route
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    private function listenTo($method, string $url, string $identifier): Route {

        if(false === is_string($method) && false === is_array($method)) {
            throw new InvalidArgumentException(sprintf('Argument 1 passed to %s() must be of the type string or array, "%s" given', __METHOD__, gettype($method)));
        }

        if(true === Router :: routeExists($identifier)) {
            throw new LogicException(sprintf('Route with identifier "%s" already exists', $identifier));
        }

        if(true === is_string($method)) {
            $method = [$method];
        }

        $this -> attributes['method'] 	  = $method;
        $this -> attributes['url'] 		  = $url;
        $this -> attributes['identifier'] = $identifier;

        $attributes = array_merge(Router :: getGroup(), $this -> attributes);
        $route = new Route($attributes);

        Router :: addRoute($route, $identifier);

        return $route;
    }
}