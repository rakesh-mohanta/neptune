<?php

namespace Neptune\View;

use Neptune\View\View;
use Neptune\Format\Json;
use Neptune\Database\Entity\Entity;
use Neptune\Database\EntityCollection;

use Crutches\Inflector;

/**
 * JsonView
 * @author Glynn Forrest me@glynnforrest.com
 **/
class JsonView extends View {

	public function getPreferredVars() {
		$vars = array();
		foreach($this->vars as $k => $v) {
			if($v instanceof Entity) {
				$v->_type = Inflector::locale()->single($v->getTable());
				$vars[$k] = $v->getValues();
			}
			if($v instanceof EntityCollection) {
				$vars[$k] = $v->getValues();
			}
		}
		if(!empty($vars)) {
			return $vars;
		}
		return $this->vars;
	}

	public function render() {
		return Json::create($this->getPreferredVars())->encode();
	}

}
