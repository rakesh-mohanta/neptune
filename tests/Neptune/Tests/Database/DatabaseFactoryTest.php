<?php

namespace Neptune\Tests\Database;

require_once __DIR__ . '/../../../bootstrap.php';

use Neptune\Database\DatabaseFactory;
use Neptune\Core\Config;
use Neptune\Database\Drivers\DebugDriver;

/**
 * DatabaseFactoryTest
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class DatabaseFactoryTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {
		Config::create('unittest');
		Config::set('database', array(
			'unittest' => array(
				'driver' => 'debug',
				'database' => 'unittest'
			),
			'unittest2' => array(
				'driver' => 'debug',
				'database' => 'unittest',
				'builder' => 'debug'
			),
			'incomplete' => array(
				'driver' => 'debug',
			),
			'fake' => array(
				'driver' => 'fake',
				'database' => 'database'
			)
		));
	}

	public function tearDown() {
		Config::unload();
	}

	public function testGetDriver() {
		$this->assertTrue(DatabaseFactory::getDriver() instanceof DebugDriver);
		$this->assertTrue(DatabaseFactory::getDriver('unittest') instanceof DebugDriver);
	}

	public function testGetDriverBadConfig() {
		$this->setExpectedException('\\Neptune\\Exceptions\\ConfigKeyException');
		DatabaseFactory::getDriver('wrong');
		$this->setExpectedException('\\Neptune\\Exceptions\\ConfigKeyException');
		DatabaseFactory::getDriver('incomplete');
	}

	public function testGetDriverUndefinedDriver() {
		$this->setExpectedException('\\Neptune\\Exceptions\\DriverNotFoundException');
		DatabaseFactory::getDriver('fake');
	}

	public function testGetBuilder() {
		$db = DatabaseFactory::getDriver();
		$this->assertEquals('\\Neptune\\Database\\Builders\\GenericSQLBuilder', $db->getBuilderName());
	}

	public function testGetBuilderOverride() {
		$db = DatabaseFactory::getDriver('unittest2');
		$this->assertEquals('debug', $db->getBuilderName());
	}

}

?>
