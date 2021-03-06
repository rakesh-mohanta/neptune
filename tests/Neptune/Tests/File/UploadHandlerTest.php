<?php

namespace Neptune\Tests\File;

use Neptune\File\UploadHandler;
use Neptune\Helpers\String;

use Temping\Temping;

use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * UploadHandlerTest
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class UploadHandlerTest extends \PHPUnit_Framework_TestCase {

	protected $filename = 'file.txt';
	protected $files_index = 'file';
	protected $object;
	protected $temp;

	public function setUp() {
		$this->temp = new Temping();
		$this->temp->create($this->filename);
		$_FILES = array();
		$_FILES[$this->files_index] = array(
			'name' => 'file.txt',
			'type' => 'text/plain',
			'tmp_name' => $this->filename,
			'error' => 0,
			'size' => 0
		);
		$request = new Request();
		$request->setMethod('post');
		$this->object = new UploadHandler($request, $this->files_index);
	}

	public function tearDown() {
		$this->temp->reset();
	}

	public function testConstruct() {
		$this->assertTrue($this->object instanceof UploadHandler);
	}

}
