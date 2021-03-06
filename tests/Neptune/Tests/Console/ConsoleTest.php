<?php

namespace Neptune\Tests\Console;

use Neptune\Console\Console;
use Neptune\Console\DialogHelper;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\TableHelper;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * ConsoleTest
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class ConsoleTest extends \PHPUnit_Framework_TestCase {

	protected $c;
	protected $input;
	protected $output;

	public function setUp() {
		$this->input = new ArrayInput(array());
		$this->output = new StreamOutput(fopen('php://memory', 'w', false));
		$this->c = new Console($this->input, $this->output);
		Console::outputNormal();
	}

	protected function getOutput() {
		rewind($this->output->getStream());
		$display = stream_get_contents($this->output->getStream());
		$display = str_replace(PHP_EOL, "\n", $display);
		return $display;
	}

	public function testWrite() {
		$this->c->write('Hello <info>world</info>');
		$this->assertSame('Hello world', $this->getOutput());
	}

	public function testWriteRaw() {
		$this->c->write('Hello <info>world</info>', false, StreamOutput::OUTPUT_RAW);
		$this->assertSame('Hello <info>world</info>', $this->getOutput());
	}

	public function testWritePlain() {
		$this->c->write('Hello <info>world</info>', false, StreamOutput::OUTPUT_PLAIN);
		$this->assertSame('Hello world', $this->getOutput());
	}

	public function testWriteNewline() {
		$this->c->write('Hello world', true);
		$this->assertSame("Hello world\n", $this->getOutput());
	}

	public function testWriteln() {
		$this->c->writeln('Hello world');
		$this->assertSame("Hello world\n", $this->getOutput());
	}

	public function testWritelnRaw() {
		$this->c->writeln('Hello <info>world</info>', StreamOutput::OUTPUT_RAW);
		$this->assertSame("Hello <info>world</info>\n", $this->getOutput());
	}

	public function testWritelnPlain() {
		$this->c->writeln('Hello <info>world</info>', false, StreamOutput::OUTPUT_PLAIN);
		$this->assertSame("Hello world\n", $this->getOutput());
	}

	public function testWritelnQuiet() {
		$this->output->setVerbosity(StreamOutput::VERBOSITY_QUIET);
		$this->c->writeln('Hello world');
		$this->assertSame('', $this->getOutput());
	}

	public function verboseProvider() {
		return array(
			array(StreamOutput::VERBOSITY_QUIET, false),
			array(StreamOutput::VERBOSITY_NORMAL, false),
			array(StreamOutput::VERBOSITY_VERBOSE, true),
			array(StreamOutput::VERBOSITY_VERY_VERBOSE, true),
			array(StreamOutput::VERBOSITY_DEBUG, true),
		);
	}

	/**
	 * @dataProvider verboseProvider()
	 */
	public function testVerbose($verbosity, $should_write) {
		$this->output->setVerbosity($verbosity);
		$this->c->verbose('Verbose message');
		if($should_write) {
			$this->assertSame("Verbose message\n", $this->getOutput());
		} else {
			$this->assertSame('', $this->getOutput());
		}
	}

	/**
	 * @dataProvider verboseProvider()
	 */
	public function testVerboseRaw($verbosity, $should_write) {
		$this->output->setVerbosity($verbosity);
		$this->c->verbose('Verbose <info>message</info>', true, StreamOutput::OUTPUT_RAW);
		if($should_write) {
			$this->assertSame("Verbose <info>message</info>\n", $this->getOutput());
		} else {
			$this->assertSame('', $this->getOutput());
		}
	}

	/**
	 * @dataProvider verboseProvider()
	 */
	public function testVerbosePlain($verbosity, $should_write) {
		$this->output->setVerbosity($verbosity);
		$this->c->verbose('Verbose <info>message</info>', true, StreamOutput::OUTPUT_PLAIN);
		if($should_write) {
			$this->assertSame("Verbose message\n", $this->getOutput());
		} else {
			$this->assertSame('', $this->getOutput());
		}
	}

	public function veryVerboseProvider() {
		return array(
			array(StreamOutput::VERBOSITY_QUIET, false),
			array(StreamOutput::VERBOSITY_NORMAL, false),
			array(StreamOutput::VERBOSITY_VERBOSE, false),
			array(StreamOutput::VERBOSITY_VERY_VERBOSE, true),
			array(StreamOutput::VERBOSITY_DEBUG, true),
		);
	}

	/**
	 * @dataProvider veryVerboseProvider()
	 */
	public function testVeryVerbose($verbosity, $should_write) {
		$this->output->setVerbosity($verbosity);
		$this->c->veryVerbose('Very verbose message');
		if($should_write) {
			$this->assertSame("Very verbose message\n", $this->getOutput());
		} else {
			$this->assertSame('', $this->getOutput());
		}
	}

	/**
	 * @dataProvider veryVerboseProvider()
	 */
	public function testVeryVerboseRaw($verbosity, $should_write) {
		$this->output->setVerbosity($verbosity);
		$this->c->veryVerbose('Very verbose <info>message</info>', true, StreamOutput::OUTPUT_RAW);
		if($should_write) {
			$this->assertSame("Very verbose <info>message</info>\n", $this->getOutput());
		} else {
			$this->assertSame('', $this->getOutput());
		}
	}

	/**
	 * @dataProvider veryVerboseProvider()
	 */
	public function testVeryVerbosePlain($verbosity, $should_write) {
		$this->output->setVerbosity($verbosity);
		$this->c->veryVerbose('Very verbose <info>message</info>', true, StreamOutput::OUTPUT_PLAIN);
		if($should_write) {
			$this->assertSame("Very verbose message\n", $this->getOutput());
		} else {
			$this->assertSame('', $this->getOutput());
		}
	}

	public function debugProvider() {
		return array(
			array(StreamOutput::VERBOSITY_QUIET, false),
			array(StreamOutput::VERBOSITY_NORMAL, false),
			array(StreamOutput::VERBOSITY_VERBOSE, false),
			array(StreamOutput::VERBOSITY_VERY_VERBOSE, false),
			array(StreamOutput::VERBOSITY_DEBUG, true),
		);
	}

	/**
	 * @dataProvider debugProvider()
	 */
	public function testDebug($verbosity, $should_write) {
		$this->output->setVerbosity($verbosity);
		$this->c->debug('Debug message');
		if($should_write) {
			$this->assertSame("Debug message\n", $this->getOutput());
		} else {
			$this->assertSame('', $this->getOutput());
		}
	}

	/**
	 * @dataProvider debugProvider()
	 */
	public function testDebugRaw($verbosity, $should_write) {
		$this->output->setVerbosity($verbosity);
		$this->c->debug('Debug <info>message</info>', true, StreamOutput::OUTPUT_RAW);
		if($should_write) {
			$this->assertSame("Debug <info>message</info>\n", $this->getOutput());
		} else {
			$this->assertSame('', $this->getOutput());
		}
	}

	/**
	 * @dataProvider debugProvider()
	 */
	public function testDebugPlain($verbosity, $should_write) {
		$this->output->setVerbosity($verbosity);
		$this->c->debug('Debug <info>message</info>', true, StreamOutput::OUTPUT_PLAIN);
		if($should_write) {
			$this->assertSame("Debug message\n", $this->getOutput());
		} else {
			$this->assertSame('', $this->getOutput());
		}
	}

	public function testGetAndSetHelperSet() {
		$helper_set = new HelperSet();
		$this->c->setHelperSet($helper_set);
		$this->assertSame($helper_set, $this->c->getHelperSet());
	}

	public function testGetHelperSetThrowsException() {
		$this->setExpectedException('\Exception', 'HelperSet instance not defined for this Console instance');
		$this->c->getHelperSet();
	}

	public function outputTypesHelloProvider() {
		return array(
			array(StreamOutput::OUTPUT_NORMAL, 'Hello world'),
			array(StreamOutput::OUTPUT_RAW, 'Hello <comment>world</comment>'),
			array(StreamOutput::OUTPUT_PLAIN, 'Hello world'),
		);
	}

	public function testOutputNormal() {
		Console::outputNormal();
		$this->c->write('Hello <comment>world</comment>');
		$this->assertSame('Hello world', $this->getOutput());
	}

	/**
	 * @dataProvider outputTypesHelloProvider()
	 */
	public function testOutputNormalWithTypes($type, $expected) {
		Console::outputNormal();
		$this->c->write('Hello <comment>world</comment>', false, $type);
		$this->assertSame($expected, $this->getOutput());
	}

	public function testOutputRaw() {
		Console::outputRaw();
		$this->c->write('Hello <comment>world</comment>');
		$this->assertSame('Hello <comment>world</comment>', $this->getOutput());
	}

	/**
	 * @dataProvider outputTypesHelloProvider()
	 */
	public function testOutputRawWithTypes($type, $expected) {
		Console::outputRaw();
		$this->c->write('Hello <comment>world</comment>', false, $type);
		$this->assertSame($expected, $this->getOutput());
	}

	public function testOutputPlain() {
		Console::outputPlain();
		$this->c->write('Hello <comment>world</comment>');
		$this->assertSame('Hello world', $this->getOutput());
	}

	/**
	 * @dataProvider outputTypesHelloProvider()
	 */
	public function testOutputPlainWithTypes($type, $expected) {
		Console::outputPlain();
		$this->c->write('Hello <comment>world</comment>', false, $type);
		$this->assertSame($expected, $this->getOutput());
	}

}
