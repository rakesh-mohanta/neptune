<?php

namespace Neptune\Http;

/**
 * Request
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class Request {

	protected static $instance;
	protected $path;
	protected $uri;
	protected $format;

	public static function getInstance() {
		if(!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	protected function __construct() {
	}

	/**
	 * Resets the cached values of $path, $uri and $format.
	 * This should only really be used for unit testing purposes.
	 */
	public function resetStoredVars() {
		$this->path = null;
		$this->uri = null;
		$this->format = null;
	}

	/**
	 * Get the url path for the current request. This is the uri
	 * without extension and can be used to route the current request.
	 */
	public function path() {
		if($this->path) {
			return $this->path;
		}
		$path = $this->uri();
		$dot = strrpos($path, '.');
		if ($dot) {
			$path = substr($path, 0, $dot);
		}
		$this->path = $path;
		return $path;
	}

	/**
	 * Get the uniform resource identifier for the current request,
	 * but with query and fragment parameters stripped (anything after
	 * '?' and '#'). Use the get() method to access variables after the
	 * question mark. Anything after a hash should not be passed to
	 * the server side.
	 * See http://en.wikipedia.org/wiki/URI_scheme
	 */
	public function uri() {
		if($this->uri) {
			return $this->uri;
		}
		if (isset($_SERVER['REQUEST_URI'])) {
			$uri = $_SERVER['REQUEST_URI'];
			$mark = strpos($_SERVER['REQUEST_URI'], '?');
			if ($mark) {
				$uri = substr($uri, 0, $mark);
			}
			$this->uri = $uri;
			return $uri;
		}
		return null;
	}

	/**
	 * Get the ip address of the current request.
	 */
	public function ip() {
		return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
	}

	/**
	 * Get the method of the current request as a string.
	 */
	public function method() {
		return isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : null;
	}

	/**
	 * Returns true if the current request method is POST.
	 */
	public function isPost() {
		return $this->method() === 'POST';
	}

	/**
	 * Returns true if the current request method is GET.
	 */
	public function isGet() {
		return $this->method() === 'GET';
	}

	/**
	 * Returns the file format of the current request as a string.
	 */
	public function format() {
		if($this->format) {
			return $this->format;
		}
		$format = $this->uri();
		if ($format) {
			$dot = strrpos($format, '.');
			if ($dot && $dot != strlen($format) - 1) {
				$format = substr($format, $dot + 1);
				$this->format = $format;
				return $format;
			}
		}
		$this->format = 'html';
		return 'html';
	}

	/**
	 * Set the format of the current request. This will orverride any
	 * file format supplied in the url
	 */
	public function setFormat($format) {
		$this->format = $format;
	}

	/**
	 * Get the value of $_GET['$key'].
     * If no key is specified the entire $_GET array will be returned.
	 * $default will be returned (null unless specified) if
	 * $_GET[$key] doesn't exist.
	 */
	public function get($key = null, $default = null) {
		if (!$key) {
			return $_GET;
		}
		return isset($_GET[$key]) ? $_GET[$key] : $default;
	}

	/**
	 * Get the value of $_POST['$key'].
     * If no key is specified the entire $_POST array will be returned.
	 * $default will be returned (null unless specified) if
	 * $_POST[$key] doesn't exist.
	 */
	public function post($key = null, $default = null) {
		if (!$key) {
			return $_POST;
		}
		return isset($_POST[$key]) ? $_POST[$key] : $default;
	}

}
