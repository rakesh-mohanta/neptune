<?php

namespace Neptune\Service;

use Neptune\Core\Neptune;
use Neptune\Core\Config;
use Neptune\Form\FormCreator;

/**
 * FormService
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class FormService implements ServiceInterface
{

    protected $config;

    public function __construct(Config $config = null)
    {
        $this->config = $config;
    }

    public function register(Neptune $neptune)
    {
        //if no config was supplied, grab the default
        if (!$this->config) {
            $config = $neptune['config'];
        }

        $neptune['form'] = function($neptune) use ($config) {
            $creator = new FormCreator($neptune, $neptune['dispatcher']);

            foreach ($config->get('forms', array()) as $name => $class) {
                $creator->register($name, $class);
            }
            return $creator;
        };
    }

    public function boot(Neptune $neptune)
    {
    }

}
