<?php

namespace Neptune\Command;

use Neptune\Command\Command;
use Neptune\Console\Console;

use \DirectoryIterator;

/**
 * EnvListCommand
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class EnvListCommand extends Command {

	protected $name = 'env:list';
	protected $description = 'List all application environments';

	public function go(Console $console) {
		foreach ($this->getEnvsHighlightCurrent() as $env) {
			$console->writeln($env);
		}
	}

	protected function getEnvs() {
		$envs = array();
		$env_dir = $this->getRootDirectory() . 'config/env';
		$i = new DirectoryIterator($env_dir);
		foreach ($i as $file) {
			if($file->isFile()) {
				$envs[] = $file->getBasename('.php');
			}
		}
		sort($envs);
		return $envs;
	}

	protected function getEnvsHighlightCurrent() {
		$current_env = $this->config->get('env');
		return array_map(function($env) use ($current_env) {
			if($env === $current_env) {
				return "<info>$env</info>";
			}
			return $env;
		}, $this->getEnvs());
	}

}
