<?php

namespace Neptune\Core;

use Neptune\Exceptions\ConfigKeyException;
use Neptune\Exceptions\ConfigFileException;

use Crutches\DotArray;

/**
 * Config
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class Config {

	protected static $instances = array();
	protected $name;
	protected $filename;
	protected $modified = false;
	protected $dot_array;

	protected function __construct($name, $filename = null) {
		if($filename) {
			if(!file_exists($filename)) {
				throw new ConfigFileException(
					'Configuration file ' . $filename . ' not found');
			}
			$values = include $filename;
			if (!is_array($values)) {
				throw new ConfigFileException(
					'Configuration file ' . $filename . ' does not return a php array');
			}
			$this->filename = $filename;
		} else {
			$values = array();
		}
		$this->name = $name;
		$this->dot_array = new DotArray($values);
		return true;
	}

	/**
	 * Get a configuration value that matches $key.
	 * $key uses the dot array syntax: parent.child.child
	 * If the key matches an array the whole array will be returned.
	 * If no key is specified the entire configuration array will be
	 * returned.
	 * $default will be returned (null unless specified) if the key is
	 * not found.
	 */
	public function get($key = null, $default = null) {
		return $this->dot_array->get($key, $default);
	}

	/**
	 * Get the first value from an array of configuration values that
	 * matches $key.
	 * $default will be returned (null unless specified) if the key is
	 * not found or does not contain an array.
	 */
	public function getFirst($key = null, $default = null) {
		return $this->dot_array->getFirst($key, $default);
	}

	/**
	 * Get a configuration value that matches $key in the same way as
	 * get(), but a ConfigKeyException will be thrown if
	 * the key is not found.
	 */
	public function getRequired($key) {
		$value = $this->get($key);
		if ($value) {
			return $value;
		}
		throw new ConfigKeyException("Required value not found in Config instance '$this->name': $key");
	}

	/**
	 * Get a directory path from the configuration value that matches
	 * $key. The value will be added to dir.root to form a complete
	 * directory path. If the value begins with a slash it will be
	 * treated as an absolute path and returned explicitly. A
	 * ConfigKeyException will be thrown if the path can't be resolved.
	 *
	 * @param string $key The key in the config file
	 */
	public function getPath($key) {
		$path = $this->getRequired($key);
		if(substr($path, 0, 1) === '/') {
			return $path;
		}
		$root = self::load('neptune')->getRequired('dir.root');
		return $root . $path;
	}

	/**
	 * Get a directory path from the configuration value that matches
	 * $key. The value will be added to the directory of this module
	 * to form a complete directory path. If the value begins
	 * with a slash it will be treated as an absolute path and
	 * returned explicitly. If this config instance is
	 * 'neptune', the result will be the same as getPath(). A
	 * ConfigKeyException will be thrown if the path can't be
	 * resolved.
	 *
	 * @param string $key The key in the config file
	 */
	public function getModulePath($key) {
		if($this->name === 'neptune') {
			return $this->getPath($key);
		} else {
			$path = $this->getRequired($key);
			if(substr($path, 0, 1) === '/') {
				return $path;
			}
			return dirname($this->filename) . '/' . $path;
		}
	}

	/**
	 * Get the first value from an array of configuration values that
	 * matches $key in the same way as getFirst(), but a
	 * ConfigKeyException will be thrown if the key is not found.
	 */
	public function getFirstRequired($key) {
		$value = $this->dot_array->getFirst($key);
		if ($value) {
			return $value;
		}
		throw new ConfigKeyException("Required first value not found in Config instance '$this->name': $key");
	}

	/**
	 * Set a configuration value with $key.
	 * $key uses the dot array syntax: parent.child.child.
	 * If $value is an array this will also be accessible using the
	 * dot array syntax.
	 */
	public function set($key, $value) {
		$this->dot_array->set($key, $value);
		$this->modified = true;
	}

	/**
	 * Create config settings with $name.
	 * $filename must be specified (or set with setFilename) if the
	 * settings are intended to be saved.
	 * Giving a $name that already exists will overwrite the settings
	 * with that name.
	 */
	public static function create($name, $filename = null) {
		if (array_key_exists($name, self::$instances)) {
			return self::$instances[$name];
		}
		self::$instances[$name] = new self($name);
		self::$instances[$name]->setFilename($filename);
		return self::$instances[$name];
	}

	/**
	 * Load config settings with $name from $filename.
	 * If $name is loaded, the same Config instance will be
	 * returned if $filename is not specified.
	 * If $name is loaded and $filename does not match with $name
	 * the instance with that name will be overwritten.
	 * If $name is not specified, the first loaded config file will be
	 * returned, or an exception thrown if no Config instances are
	 * set.
	 * If $override_name is supplied and matches the name of a loaded
	 * config file, the values of that Config instance will be
	 * overwritten with the values of the new file.
	 */
	public static function load($name = null, $filename = null, $override_name = null) {
		if (array_key_exists($name, self::$instances)){
			$instance = self::$instances[$name];
			if(!$filename || $instance->getFileName() === $filename) {
				return $instance;
			}
		}
		if(!$name) {
			if(empty(self::$instances)) {
				throw new ConfigFileException(
					'No Config instance loaded, unable to get default');
			}
			reset(self::$instances);
			return self::$instances[key(self::$instances)];
		}
		if(!$filename) {
			//attempt to load the file as a module, but only if the
			//neptune config has been loaded
			if(isset(self::$instances['neptune']) && self::loadModule($name)) {
				return self::$instances[$name];
			}
			//if it isn't a module, we can't do anything without a file
			throw new ConfigFileException(
				"No filename specified for Config instance $name"
			);
		}
		self::$instances[$name] = new self($name, $filename);
		if($override_name && isset(self::$instances[$override_name])) {
			Config::load($override_name)->override(
				self::$instances[$name]->get());
		}
		return self::$instances[$name];
	}

	/**
	 * Override values in this Config instance with values from
	 * $array.
	 */
	public function override(array $array) {
		$this->dot_array->merge($array);
	}

	/**
	 * Load the configuration for a module with $name.
	 * This will load the configuration file for the module and also
	 * override that configuration with anything found in
	 * config/modules/$name.php
	 */
	public static function loadModule($name) {
		try {
			$neptune = self::load('neptune');
		} catch (ConfigFileException $e){
			//neptune config not loaded
			//rethrow a ConfigFileException with a more useful message
			throw new ConfigFileException(
				"Neptune config not loaded, unable to load module $name.");
		}
		//fetch the module path and load the config file
		$module_config_file = $neptune->get('dir.root') . $neptune->getRequired('modules.' . $name) . 'config.php';
		$module_instance = self::load($name, $module_config_file);
		//check for a local config to override the module. It should
		//have the path config/modules/<modulename>.php
		$local_config_file = $neptune->getRequired('dir.root') .
			'config/modules/' . $name . '.php';
		try {
			//prepend _ to give it a unique name so it can be used individually.
			self::load('_' . $name, $local_config_file, $name);
		} catch (ConfigFileException $e) {
			//do nothing if there is no config file defined.
		}
		return $module_instance;
	}

	/**
	 * Load the environment configuration called $name. The
	 * environment should be located at config/env/$name.php. The
	 * values in this file will then be merged into the 'neptune'
	 * config instance.
	 */
	public static function loadEnv($name) {
		$file = self::load('neptune')->get('dir.root') .
			'config/env/' . $name . '.php';
		//load $name as a config file, merging into neptune
		return self::load($name, $file, 'neptune');
	}

	/**
	 * Unload configuration settings with $name, requiring them to be
	 * reloaded if they are to be used again.
	 * If $name is not specified, all configuration files will be
	 * unloaded.
	 */
	public static function unload($name=null) {
		if ($name) {
			unset(self::$instances[$name]);
		} else {
			self::$instances = array();
		}
	}

	/**
	 * Save the current configuration instance.
	 * A ConfigFileException will be thrown if filename is not set or
	 * if php can't write to the file.
	 */
	public function save() {
		$values = $this->dot_array->get();
		if (!$this->modified || empty($values)) {
			return true;
		}
		if(!$this->filename) {
			throw new ConfigFileException(
				"Unable to save Config instance '$this->name', \$filename is not set"
			);
		}
		if(!file_exists($this->filename) && !@touch($this->filename)){
			throw new ConfigFileException(
				"Unable to create configuration file
						$this->filename. Check file paths and permissions
						are correct."
			);
		};
		if(!is_writable($this->filename)) {
			throw new ConfigFileException(
				"Unable to write to configuration file
						$this->filename. Check file paths and permissions
						are correct."
			);
		}
		$content = '<?php return ' . var_export($values, true) . '?>';
		file_put_contents($this->filename, $content);
		return true;
	}

	/**
	 * Call save() on all configuration instances.
	 */
	public static function saveAll() {
		foreach(self::$instances as $instance) {
			$instance->save();
		}
		return true;
	}

	/**
	 * Set the filename for the current configuration instance.
	 *
	 * @param string $filename The name of the file
	 */
	public function setFileName($filename) {
		$this->filename = $filename;
	}

	/**
	 * Get the filename of the current configuration instance.
	 */
	public function getFileName() {
		return $this->filename;
	}

}
