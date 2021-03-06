<?php

namespace Neptune\Tests\View;

use Neptune\View\Skeleton;
use Neptune\Core\Config;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * SkeletonTest
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SkeletonTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {
		$c = Config::create('neptune');
		$c->set('dir.neptune', '/root/to/neptune/');
	}

	public function tearDown() {
		Config::load('neptune')->unload();
	}

	public function testConstruct() {
		$this->assertTrue(Skeleton::loadAbsolute('dummy') instanceof Skeleton);
	}

	public function testSetView() {
		$skeleton = Skeleton::load('model');
		$this->assertEquals('/root/to/neptune/skeletons/model.php',
							$skeleton->getView());
		$skeleton = Skeleton::loadAbsolute('/home/model');
		$this->assertEquals('/home/model.php',
							$skeleton->getView());
	}

	public function testGetAndSetNamespace() {
		$skeleton = Skeleton::load(null);
		$this->assertInstanceOf('\Neptune\View\Skeleton', $skeleton->setNamespace('Foo'));
		$this->assertSame('Foo', $skeleton->getNamespace());
	}

}
