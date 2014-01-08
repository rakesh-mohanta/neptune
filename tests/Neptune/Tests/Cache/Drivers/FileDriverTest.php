<?php

namespace Neptune\Tests\Cache\Drivers;

use Neptune\Tests\Cache\Drivers\CacheDriverTest;
use Neptune\Cache\Drivers\FileDriver;

use Temping\Temping;

require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * FileDriverTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class FileDriverTest extends CacheDriverTest {

	public function setup() {
		$this->temp = new Temping();
		$config = array(
			'dir' => $this->temp->getDirectory(),
			'prefix' => 'testing_'
		);
		$this->driver = new FileDriver($config);
	}

	public function tearDown() {
		$this->temp->reset();
	}

	protected function setContents($key, $value) {
		$this->temp->create($key, serialize($value));
	}

	protected function getContents($key) {
		return unserialize($this->temp->getContents($key));
	}

	public function testNoConfigThrowsException() {
		$this->setExpectedException('\Exception');
		$driver = new FileDriver(array());
	}

	public function testNonWritableDirectoryThrowsException() {
		$this->setExpectedException('\Exception');
		$driver = new FileDriver(array(
			'dir' => 'some/dir',
			'prefix' => 'testing_'
		));
	}

	/**
	 * @dataProvider cacheDataProvider()
	 */
	public function testAdd($key, $val) {
		$this->driver->add($key, $val);
		$this->assertTrue($this->temp->exists('testing_' . $key));
		$this->assertEquals($val, $this->getContents('testing_' . $key));
	}

	/**
	 * @dataProvider cacheDataProvider()
	 */
	public function testAddNoPrefix($key, $val) {
		$this->driver->add($key, $val, null, false);
		$this->assertTrue($this->temp->exists($key));
		$this->assertEquals($val, $this->getContents($key));
	}

	/**
	 * @dataProvider cacheDataProvider()
	 */
	public function testSet($key, $val) {
		$this->driver->set($key, $val);
		$this->assertTrue($this->temp->exists('testing_' . $key));
		$this->assertEquals($val, $this->getContents('testing_' . $key));
	}

	/**
	 * @dataProvider cacheDataProvider()
	 */
	public function testSetNoPrefix($key, $val) {
		$this->driver->set($key, $val, null, false);
		$this->assertTrue($this->temp->exists($key));
		$this->assertEquals($val, $this->getContents($key));
	}

	/**
	 * @dataProvider cacheDataProvider()
	 */
	public function testGet($key, $val) {
		$this->setContents('testing_' . $key, $val);
		$this->assertEquals($val, $this->driver->get($key));
	}

	/**
	 * @dataProvider cacheDataProvider()
	 */
	public function testGetNoPrefix($key, $val) {
		$this->setContents($key, $val);
		$this->assertEquals($val, $this->driver->get($key, false));
	}

	public function testGetReturnsNullOnMiss() {
		$this->assertNull($this->driver->get('foo'));
		$this->assertNull($this->driver->get('foo', false));
	}

	/**
	 * @dataProvider cacheDataProvider()
	 */
	public function testGetAndSet($key, $val) {
		$this->driver->set($key, $val);
		$this->assertEquals($val, $this->driver->get($key));
	}

	/**
	 * @dataProvider cacheDataProvider()
	 */
	public function testGetAndSetNoPrefix($key, $val) {
		$this->driver->set($key, $val, null, false);
		$this->assertEquals($val, $this->driver->get($key, false));
	}

	/**
	 * @dataProvider cacheDataProvider()
	 */
	public function testDelete($key, $val) {
		$this->driver->set($key, $val);
		$this->assertTrue($this->temp->exists('testing_' . $key));
		$this->assertTrue($this->driver->delete($key));
		$this->assertFalse($this->temp->exists('testing_' . $key));
	}

	/**
	 * @dataProvider cacheDataProvider()
	 */
	public function testDeleteNoPrefix($key, $val) {
		$this->driver->set($key, $val, null, false);
		$this->assertTrue($this->temp->exists($key));
		$this->assertTrue($this->driver->delete($key, null, false));
		$this->assertFalse($this->temp->exists($key));
	}

	public function testDeleteNonExistent() {
		$this->assertTrue($this->driver->delete('not_here'));
		$this->assertFalse($this->temp->exists('not_here'));
	}

	/**
	 * @dataProvider cacheDataProvider()
	 */
	public function testFlush($key, $val) {
		$this->driver->set($key,$val);
		$this->assertFalse($this->temp->isEmpty());
		$this->driver->flush();
		$this->assertTrue($this->temp->isEmpty());
		$this->assertTrue($this->temp->exists());
	}

}