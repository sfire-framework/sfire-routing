<?php
/**
 * sFire Framework
 *
 * @link      https://sfire.io
 * @copyright Copyright (c) 2014-2020 sFire Framework.
 * @license   https://sfire.io/license BSD 3-CLAUSE LICENSE
 */
 
namespace sFire\Routing;

use sFire\Http\UrlParser;
use sFire\Http\Response;
use sFire\Http\Request;
use sFire\Routing\Exception\RuntimeException;


/**
 * Class Forward
 * @package sFire\Routing
 */
class Forward {


	/**
     * The route identifier
	 * @var string
	 */
	private ?string $identifier = null;


	/**
     * Hold the parameters for the current route
	 * @var array
	 */
	private ?array $params = [];
	

	/**
     * Hold the domain for the current route
	 * @var string
	 */
	private ?string $domain = null;


	/**
     * Holds the amount of seconds before the forward will be taken place
	 * @var float
	 */
	private ?float $seconds = null;


	/**
     * Holds the URL to be forwarded to
	 * @var string
	 */
	private ?string $url = null;


    /**
     * Constructor
     * @param string $identifier
     * @throws RuntimeException
     */
	public function __construct(string $identifier) {

		//Check if identifier exists
		if(false === Router :: routeExists($identifier)) {
			throw new RuntimeException(sprintf('Identifier "%s" does not exists', $identifier));
		}

		$this -> identifier = $identifier;
	}


    /**
     * Set the parameters
     * @param array $params
     * @return self
     */
	public function params(array $params): self {

		$this -> params = $params;
		return $this;
	}


    /**
     * Set the domain name with HTTP protocol
     * @param string $domain
     * @param string $protocol
     * @return self
     * @throws RuntimeException
     */
	public function domain(string $domain, string $protocol = null): self {

		//Check if identifier exists
		if(false === Router :: routeExists($this -> identifier, $domain)) {
			throw new RuntimeException(sprintf('Identifier "%s" with domain "%s" does not exists', $this -> identifier, $domain));
		}

		if(null === $protocol) {
			$protocol = Request :: getScheme();
		}

		$this -> domain = sprintf('%s://%s', $protocol, $domain);

		return $this;
	}


	/**
	 * Set the amount of seconds before the redirect needs to be placed
	 * @param float $seconds
	 * @return self
	 */
	public function after(float $seconds): self {

		$this -> seconds = $seconds;
		return $this;
	}


    /**
     * Execute forward, with optional HTTP status code
     * @param int $code [optional]
     * @return void
     */
	public function exec(int $code = null): void {

		$this -> setUrl();

		if(null !== $this -> seconds) {

		    Response :: addHeader('refresh', sprintf('%d;url=%s', $this -> seconds, $this -> url), $code);
			return;
		}

		Response :: addHeader('Location', $this -> url, $code);
	}


    /**
     * Set the url based on the identifier, parameters and domain
     * @return void
     */
	private function setUrl(): void {

		$this -> url = Router :: url($this -> identifier, $this -> params, $this -> domain);

		$url = new UrlParser($this -> url);

		if(null === $url -> getScheme() && strlen($this -> url) > 0) {
			$this -> url = '/' . $this -> url;
		}

	        if(0 === strlen($this -> url)) {
	            $this -> url = '/';
	        }
	}
}