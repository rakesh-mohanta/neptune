<?php

namespace Neptune\Tests\Assets;

use Neptune\Assets\Asset;

use Temping\Temping;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * AssetTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class AssetTest extends \PHPUnit_Framework_TestCase {

	protected $file;

	public function setUp() {
		$id = Temping::getInstance()->create('test_asset', 'content');
		$this->file = Temping::getInstance()->getPathname($id);
	}

	public function tearDown() {
		Temping::getInstance()->reset();
	}

	public function testConstruct() {
		$a = new Asset();
		$this->assertTrue($a instanceof Asset);
	}

	public function testSetAndGetContent() {
		$a = new Asset();
		$a->setContent('source');
		$this->assertEquals('source', $a->getContent());
	}

	public function testAssetFromFile() {
		$a = new Asset();
		$this->assertNull($a->getContent());
		$a->loadFile($this->file);
		$this->assertEquals('content', $a->getContent());
	}

	public function testAssetFromFileConstruct() {
		$a = new Asset($this->file);
		$this->assertEquals('content', $a->getContent());
	}

	public function testExceptionThrownWhenFileNotFound() {
		$this->setExpectedException('\\Neptune\\Exceptions\\FileException');
		$a = new Asset('not_a_file');
	}

}
