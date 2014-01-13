<?php

namespace Neptune\Cache\Driver;

use Neptune\Cache\Driver\CacheDriverInterface;

use Temping\Temping;

/**
 * FileDriver
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class FileDriver implements CacheDriverInterface {

	protected $temping;
	protected $prefix;

	public function __construct($prefix, Temping $temping) {
		$this->prefix = $prefix;
		$this->temping = $temping;
	}

	public function set($key, $value, $time = null, $use_prefix = true) {
		if($use_prefix) {
			$key = $this->prefix . $key;
		}
		return $this->temping->create($key, serialize($value));
	}

	public function get($key, $use_prefix = true) {
		if($use_prefix) {
			$key = $this->prefix . $key;
		}
		try {
			return unserialize($this->temping->getContents($key));
		} catch (\Exception $e) {
			return null;
		}
	}

	public function delete($key, $use_prefix = true) {
		if($use_prefix) {
			$key = $this->prefix . $key;
		}
		$this->temping->delete($key);
		return true;
	}

	public function flush($time = null, $use_prefix = true) {
		$this->temping->reset();
		$this->temping->init();
	}

	public function getDirectory() {
		return $this->temping->getDirectory();
	}

}