<?php

namespace Neptune\Database\Statements;

use Neptune\Database\Statements\DatabaseStatement;
use Neptune\Database\Statements\DebugStatement;
use Neptune\Database\DatabaseFactory;
use Neptune\Core\Config;

require_once dirname(__FILE__) . '/../../test_bootstrap.php';

/**
 * DebugStatementTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class DebugStatementTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {
		Config::create('testing');
		Config::set('database', array(
			'debug' => array(
				'driver' => 'debug',
				'database' => 'debug')
			));
	}

	public function tearDown() {
		Config::unload();
	}

	public function testConstruct() {
		$db = DatabaseFactory::getDriver('debug');
		$stmt = $db->prepare('SELECT * FROM test');
		$this->assertTrue($stmt instanceof DatabaseStatement);
	}

	public function testQueryIsParsedSimple() {
		$db = DatabaseFactory::getDriver('debug');
		$stmt = $db->prepare('SELECT * FROM test WHERE id = ?');
		$stmt->execute(array(2));
		$this->assertEquals('SELECT * FROM test WHERE id = 2', $stmt->getExecutedQuery());
	}

	public function testQueryIsParsedComplex() {
		$db = DatabaseFactory::getDriver('debug');
		$stmt = $db->prepare('SELECT * FROM test WHERE id = ? AND count > ? LIMIT ?');
		$stmt->execute(array(2,10,1));
		$this->assertEquals('SELECT * FROM test WHERE id = 2 AND count > 10 LIMIT 1', $stmt->getExecutedQuery());
	}


}
?>