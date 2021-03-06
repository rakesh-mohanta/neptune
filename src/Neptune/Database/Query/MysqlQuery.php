<?php

namespace Neptune\Database\Query;

use Neptune\Database\Query\AbstractQuery;

use Crutches\ItemList;

/**
 * MysqlQuery
 * @author Glynn Forrest me@glynnforrest.com
 **/
class MysqlQuery extends AbstractQuery {

	protected function getSelectSQL() {
		$query = 'SELECT';
		if (!isset($this->query['FIELDS'])) {
			$query .= ' *';
		}
		foreach ($this->select_verbs as $verb) {
			if (isset($this->query[$verb])) {
				switch ($verb) {
				case 'FROM':
					$this->addFrom($query);
					break;
				case 'FIELDS':
					$this->addFields($query);
					break;
				case 'WHERE':
					$this->addWhere($query);
					break;
				case 'ORDER BY':
					$this->addOrderBy($query);
					break;
				case 'DISTINCT':
					$this->addDistinct($query);
					break;
				case 'OFFSET':
					if(isset($this->query['LIMIT'])) {
						$query .= ' OFFSET ' . $this->query['OFFSET'];
					}
					break;
				default:
					$query .= ' ' . $verb . ' ' . $this->query[$verb];
					break;
				}
			}
		}
		return $query;
	}

	protected function getInsertSQL() {
		$query = 'INSERT';
		foreach ($this->insert_verbs as $verb) {
			if (isset($this->query[$verb])) {
				switch ($verb) {
				case 'FIELDS':
					$this->addInsertFields($query);
					break;
				case 'INTO':
					$query .= ' INTO `' . $this->query[$verb] . '`';
					break;
				default:
					$query .= ' ' . $verb . ' ' . $this->query[$verb];
				}
			}
		}
		return $query;
	}

	protected function getUpdateSQL() {
		$query = 'UPDATE';
		foreach ($this->update_verbs as $verb) {
			if (isset($this->query[$verb])) {
				switch ($verb) {
				case 'TABLES':
					$this->addTables($query);
					break;
				case 'FIELDS':
					$this->addUpdateFields($query);
					break;
				case 'WHERE':
					$this->addWhere($query);
					break;
				default:
					$query .= ' ' . $verb . ' ' . $this->query[$verb];
				}
			}
		}
		return $query;
	}

	protected function getDeleteSQL() {
		$query = 'DELETE';
		foreach ($this->delete_verbs as $verb) {
			if (isset($this->query[$verb])) {
				switch ($verb) {
				case 'FROM':
					$this->addFrom($query);
					break;
				case 'WHERE':
					$this->addWhere($query);
					break;
				default:
					$query .= ' ' . $verb . ' ' . $this->query[$verb];
					break;
				}
			}
		}
		return $query;
	}

	protected function addFrom(&$query) {
		$query .= ' FROM ' . $this->createList($this->query['FROM']);
	}

	protected function addFields(&$query) {
		$query .= ' ' . $this->createList($this->query['FIELDS']);
	}

	protected function addWhere(&$query) {
        /**
         * $this->query['WHERE'] is of the form
         * array(
         *     array($expression, $has_value, $logic),
         *     //etc
         * )
         * e.g. array('id = ', true, 'AND')
         */
		$query .= ' WHERE ';
        //for the first where, there is no AND / OR logic
		$query .= $this->query['WHERE'][0][0];
        if ($this->query['WHERE'][0][1] === true) {
            $query .= ' ?';
        }
        //for each of the subsequent expressions, add the logic
        //(AND/OR), the expression and then a ? for the value, if
        //there is one
		for ($i = 1; $i < count($this->query['WHERE']); $i++) {
			$query .= ' ' . $this->query['WHERE'][$i][2] . ' ';
            $query .= $this->query['WHERE'][$i][0];
            if ($this->query['WHERE'][$i][1] === true) {
                $query .= ' ?';
            }
		}
	}

	protected function addOrderBy(&$query) {
		$query .= ' ORDER BY ' . $this->query['ORDER BY'][0][0];
		$query .= ' ' . $this->query['ORDER BY'][0][1];
		for ($i = 1; $i < count($this->query['ORDER BY']); $i++) {
			$query .= ', ' . $this->query['ORDER BY'][$i][0];
			$query .= ' ' . $this->query['ORDER BY'][$i][1];
		}
	}

	protected function addInsertFields(&$query) {
		$query .= ' (' . $this->createList($this->query['FIELDS']);
		$query .= ') VALUES (';
		for ($i = 0; $i < count($this->query['FIELDS']) - 1; $i++) {
			$query .= '?, ';
		}
		$query .= '?)';
	}

	protected function addUpdateFields(&$query) {
		$query .= ' SET '. $this->createList($this->query['FIELDS'], '` = ?');
	}

	protected function addTables(&$query) {
		$query .= ' ' . $this->createList($this->query['TABLES']);
	}

	protected function addDistinct(&$query) {
		$query .= ' DISTINCT';
	}

	protected function createList($list, $suffix = '`') {
		return Itemlist::create($list)->stringify(', ', '`', $suffix);
	}

}
