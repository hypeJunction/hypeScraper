<?php

class ElggUrlTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var UFCOE\Elgg\Url
	 */
	protected $url;

	public function setUp() {
		$this->url = new UFCOE\Elgg\Url('http://example.org/base/path/');
	}

	protected function analyze($url) {
		return $this->url->analyze($url);
	}

	public function testInvalidUrl() {
		$this->assertFalse($this->analyze('ftp://example.org/'));
		$this->assertFalse($this->analyze('http://example.org'));

		$this->setExpectedException('InvalidArgumentException');
		$url = new \UFCOE\Elgg\Url('ftp://example.org/');
	}

	public function testInSite() {
		$check = 'in_site';
		$data = array(
			'http://example.org/base/path' => false,
			'http://example.org/base/path/' => true,
			'https://example.org/base/path/foo' => true,
		);
		foreach ($data as $url => $val) {
			$url = $this->analyze($url);
			$this->assertEquals($val, $url[$check]);
		}
	}

	public function testAction() {
		$check = 'action';
		$data = array(
			'http://example.org/base/path' => null,
			'https://example.org/base/path/action/foo/bar' => 'foo/bar',
			'https://example.org/base/path/action' => null,
			'http://example.org/base/path/action/' => null,
		);
		foreach ($data as $url => $val) {
			$url = $this->analyze($url);
			$this->assertEquals($val, $url[$check]);
		}
	}

	public function testHandler() {
		$check = 'handler';
		$data = array(
			'http://example.org/base/path' => null,
			'https://example.org/base/path/h' => 'h',
			'https://example.org/base/path/h/f?123' => 'h',
		);
		foreach ($data as $url => $val) {
			$url = $this->analyze($url);
			$this->assertEquals($val, $url[$check]);
		}
	}

	public function testSegments() {
		$check = 'handler_segments';
		$data = array(
			'http://example.org/base/path' => array(),
			'https://example.org/base/path/h' => array(),
			'https://example.org/base/path/h/foo' => array('foo'),
			'https://example.org/base/path/h/foo/ba?re' => array('foo', 'ba'),
		);
		foreach ($data as $url => $val) {
			$url = $this->analyze($url);
			$this->assertEquals($val, $url[$check]);
		}
	}

	public function testGuid() {
		$check = 'guid';
		$data = array(
			'http://example.org/base/path' => null,
			'https://example.org/base/path/h' => null,
			'https://example.org/base/path/h/123?234' => 123,
			'https://example.org/base/path/h/view/023/not-real-guid' => null,
			'https://example.org/base/path/h/foo/12-4/123/hello' => 123,
			'https://example.org/base/path/profile/123' => null,
			'http://example.org/base/path/file/view/61/123' => 61,
		);
		foreach ($data as $url => $val) {
			$url = $this->analyze($url);
			$this->assertEquals($val, $url[$check]);
		}
	}

	public function testGetGuid() {
		$this->assertEquals(0, $this->url->getGuid('ftp://foo.bar/'));
		$this->assertEquals(123, $this->url->getGuid('https://example.org/base/path/h/foo/12-4/123/hello'));
	}

}
