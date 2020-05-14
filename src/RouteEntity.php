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


/**
 * Class RouteEntity
 * @package sFire\Routing
 */
class RouteEntity {


    /**
     * Contains the route action
     * @var null|string
     */
    private ?string $action = null;


    /**
     * Contains the route assigned variables
     * @var array
     */
    private ?array $assign = [];


    /**
     * Contains the route controller
     * @var null|string
     */
    private ?string $controller = null;


    /**
     * Contains the route domains
     * @var array
     */
    private ?array $domains = [];


    /**
     * Contains the route unique identifier
     * @var null|string
     */
    private ?string $identifier = null;


    /**
     * Contains an array with HTTP methods that the route should listen to
     * @var array
     */
    private array $method = [];


    /**
     * Contains the route URL prefix and if the prefix should be appended to another prefix
     * @var array
     */
    private array $prefix = [];


    /**
     * Contains the type of route (i.e. normal or error route)
     * @var null|string
     */
    private ?string $type = null;


    /**
     * Contains the route URL to listen to
     * @var null|string
     */
    private ?string $url = null;


    /**
     * Contains the route locale
     * @var null|string
     */
    private ?string $locale = null;


    /**
     * Contains an array with regular expressions that matches the route parameters
     * @var array
     */
    private ?array $where = [];


    /**
     * Contains an array with assigned route variables
     * @var array
     */
    private ?array $variables = [];


    /**
     * Contains all the route middleware
     * @var array
     */
    private ?array $middleware = [];


    /**
     * Contains the route module
     * @var null|string
     */
    private ?string $module = null;


    /**
     * Set the action for the route
     * @param null|string $action
     * @return void
     */
    public function setAction(?string $action): void {
        $this -> action = $action;
    }


    /**
     * Returns the route action
     * @return null|string
     */
    public function getAction(): ?string {
        return $this -> action;
    }


    /**
     * Set an array with assigned route variables
     * @param array $variables
     * @return void
     */
    public function setAssign(?array $variables): void {
        $this -> assign = $variables;
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

        if(true === $merge) {
            $this -> assign = array_replace_recursive($key, $this -> assign ?? []);
        }
        else {
            $this -> assign = $key;
        }

        return $this;
    }


    /**
     * Returns an array with assigned route variables
     * @return array
     */
    public function getAssign(): array {
        return $this -> assign;
    }


    /**
     * Set the route controller
     * @param string $controller The name of the controller
     * @return void
     */
    public function setController(?string $controller): void {
        $this -> controller = $controller;
    }


    /**
     * Returns the route controller
     * @return null|string
     */
    public function getController(): ?string {
        return $this -> controller;
    }


    /**
     * Set an array with domains where the route should listen to
     * @param null|array $domains
     * @return void
     */
    public function setDomains(?array $domains): void {
        $this -> domains = $domains;
    }


    /**
     * Returns a array of domains where the route listen to
     * @return null|array
     */
    public function getDomains(): ?array {
        return $this -> domains;
    }


    /**
     * Set the route unique identifier
     * @param string $identifier A unique name for the route
     * @return void
     */
    public function setIdentifier(?string $identifier): void {
        $this -> identifier = $identifier;
    }


    /**
     * Returns the route unique identifier
     * @return null|string
     */
    public function getIdentifier(): ?string {
        return $this -> identifier;
    }


    /**
     * Returns all the HTTP methods (GET, POST, DELETE, etc.) that the route is listening on
     * @return array
     */
    public function getMethod(): array {
        return $this -> method;
    }


    /**
     * Sets all the HTTP methods (GET, POST, DELETE, etc.) that the route should listen on
     * @param null|array $method
     * @return void
     */
    public function setMethod(?array $method): void {
        $this -> method = $method ?? [];
    }


    /**
     * Set the route prefix
     * @param null|array $prefix
     * @return void
     */
    public function setPrefix(?array $prefix): void {
        $this -> prefix = $prefix ?? [];
    }


    /**
     * Returns the route URL prefix
     * @return array
     */
    public function getPrefix(): array {
        return $this -> prefix;
    }


    /**
     * Returns the locale
     * @return null|string
     */
    public function getLocale(): ?string {
       return $this -> locale;
    }


    /**
     * Set the locale
     * @param string $locale
     * @return void
     */
    public function setLocale(?string $locale): void {
        $this -> locale = $locale;
    }


    /**
     * Returns the route type (i.e. route or error)
     * @return null|string
     */
    public function getType(): ?string {
        return $this -> type;
    }


    /**
     * Sets the route type (i.e. route or error)
     * @param null|string $type
     * @return void
     */
    public function setType(?string $type): void {
        $this -> type = $type;
    }


    /**
     * Returns the route URL
     * @return null|string
     */
    public function getUrl(): ?string {
        return $this -> url;
    }


    /**
     * Sets the route URL
     * @param null|string $url
     * @return void
     */
    public function setUrl(?string $url): void {
        $this -> url = $url;
    }


    /**
     * Returns an array with regular expressions that matches the route parameters
     * @return null|array
     */
    public function getWhere(): ?array {
        return $this -> where;
    }


    /**
     * Sets an array with regular expressions that matches the route parameters
     * @param null|array $where
     * @return void
     */
    public function setWhere(?array $where): void {
        $this -> where = $where;
    }


    /**
     * Sets the parsed variables that matches the route variables with the url variables
     * @param null|array $variables
     * @return void
     */
    public function setVariables(?array $variables): void {
        $this -> variables = $variables;
    }


    /**
     * Returns all the parsed variables that matches the route variables with the url variables
     * @return array
     */
    public function getUrlVariables(): ?array {
        return $this -> variables;
    }


    /**
     * Returns a single URL variable that matches the route variables with the url variables
     * @param string $name The name of the variable
     * @return string
     */
    public function getUrlVariable(string $name): ?string {
        return $this -> variables[$name] ?? null;
    }


    /**
     * Returns a single variable that has been set with the assign method
     * @param string $name The name of the variable
     * @return mixed
     */
    public function getParam(string $name) {
        return $this -> assign[$name] ?? null;
    }


    /**
     * Returns all variables that has been set with the assign method
     * @return array
     */
    public function getParams(): array {
        return $this -> getAssign();
    }


    /**
     * Set the route middleware
     * @param null|array $middleware
     * @return void
     */
    public function setMiddleware(?array $middleware): void {
        $this -> middleware = $middleware;
    }


    /**
     * Returns the route middleware
     * @return null|array
     */
    public function getMiddleware(): ?array {
        return $this -> middleware;
    }


    /**
     * Sets the route module
     * @param null|string $module The name of the module
     * @return void
     */
    public function setModule(?string $module): void {
        $this -> module = $module;
    }


    /**
     * Returns the route module
     * @return null|string
     */
    public function getModule(): ?string {
        return $this -> module;
    }


    /**
     * Convert the current route to a URL
     * @param null|array $variables [optional] Variables to be inserted into the current route URL
     * @param null|string $domain [optional] A single domain/host that will be prepended to the current route URL
     * @return string
     */
    public function toUrl(?array $variables = [], string $domain = null): string {
        return Router :: url($this -> getIdentifier(), $variables, $domain);
    }


    /**
     * Forward browser/client to the current route
     * @return Forward
     */
    public function forward(): Forward {
        return new Forward($this -> getIdentifier());
    }
}