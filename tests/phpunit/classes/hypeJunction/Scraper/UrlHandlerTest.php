<?php

namespace hypeJunction\Scraper;

use ElggEntity;
use Guzzle\Service\Client;
use Guzzle\Http\Message\Response;

/**
 * @coversDefaultClass hypeJunction\Scraper\UrlHandler
 * 
 */
class UrlHandlerTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var UrlHandler
	 */
	protected $object;

	/**
	 *
	 * @var ElggEntity
	 */
	protected $entity;

	/**
	 *
	 * @var MetaHandler
	 */
	protected $metahandler;

	/**
	 *
	 * @var Client
	 */
	protected $client;

	/**
	 *
	 * @var Response
	 */
	protected $response;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {

		$stub = $this->getMockBuilder('hypeJunction\\Scraper\\UrlHandler')
				->setMethods(array('getMeta', 'getEntity', 'requestHead'))
				->getMock();

		$stub->expects($this->any())
				->method('getMeta')
				->willReturn($this->getMock('hypeJunction\\Scraper\\MetaHandler'));

		$this->object = $stub;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() {
		
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstructorNotNull() {
		$this->assertNotNull(new UrlHandler);
		$this->assertNotNull(new UrlHandler('http://localhost/'));
	}

	/**
	 * @covers ::setURL
	 * @covers ::getURL
	 */
	public function testSetURL() {
		$url = 'http://example.com';
		$this->object->setURL($url);
		$this->assertEquals($this->object->getURL(), $url);
	}

	/**
	 * @covers ::setClient
	 * @covers ::getClient
	 */
	public function testGetClientWithSet() {
		$client = $this->getMockBuilder('Guzzle\\Service\\Client')->disableOriginalConstructor()->getMock();
		$this->object->setClient($client);
		$this->assertSame($this->object->getClient(), $client);
	}

	/**
	 * @covers ::getClient
	 */
	public function testGetClientWithoutSet() {
		$this->assertInstanceOf('Guzzle\\Service\\Client', $this->object->getClient());
	}

	/**
	 * @covers ::isValid
	 * @dataProvider providerIsValid
	 */
	public function testIsValid($input, $output) {
		$this->object->setURL($input);
		$this->assertEquals($this->object->isValid(), $output);
	}

	/**
	 * Assertions for testIsValid()
	 * @return array
	 */
	public function providerIsValid() {
		return array(
			array(null, false),
			array(15, false),
			array('http://foo.bar/foo-bar?foo=bar#foo', true),
			array('foo.com', false),
			array('ftp://173.194.34.5', true),
		);
	}

	/**
	 * @covers ::isImageFile
	 * @dataProvider providerGetContentType
	 */
	public function testIsImageFile($url, $mime, $expected) {

		$response = $this->getMockBuilder('Guzzle\\Http\\Message\\Response')->disableOriginalConstructor()->getMock();

		$response->expects($this->any())
				->method('getContentType')
				->willReturn($mime);

		$this->object
				->expects($this->any())
				->method('requestHead')
				->willReturn($response);

		$this->object->setURL($url);
		$this->assertEquals($this->object->isImageFile(), $expected);
	}

	/**
	 * @covers ::getContentType
	 * @dataProvider providerGetContentType
	 */
	public function testGetContentType($url, $mime, $expected) {

		$response = $this->getMockBuilder('Guzzle\\Http\\Message\\Response')->disableOriginalConstructor()->getMock();

		$response->expects($this->any())
				->method('getContentType')
				->willReturn($mime);

		$this->object
				->expects($this->any())
				->method('requestHead')
				->willReturn($response);

		$this->object->setURL($url);
		$this->assertEquals($this->object->getContentType(), $mime);
	}

	/**
	 * Assertions for isImageFile()
	 * @return array
	 */
	public function providerGetContentType() {

		return array(
			array('invalid', false, false),
			array('http://foo.bar/', 'image/jpeg', true),
			array('http://foo.bar/', 'text/html', false),
			array('http://foo.bar/', false, false),
		);
	}

	/**
	 * @covers ::getContentType
	 */
	public function testGetContentTypeNoReponse() {
		$this->assertFalse($this->object->getContentType(), false);
	}

	/**
	 * @covers ::isReachable
	 */
	public function testIsReachableNullHead() {
		$this->object->setURL('http://foo.bar/');
		// Call to requestHead() will return null
		$this->assertEquals($this->object->isReachable(), false);
	}

	/**
	 * @covers ::isReachable
	 * @dataProvider providerIsReachable
	 */
	public function testIsReachable($url, $is_successful, $expected) {

		$response = $this->getMockBuilder('Guzzle\\Http\\Message\\Response')->disableOriginalConstructor()->getMock();

		$response->expects($this->any())
				->method('isSuccessful')
				->willReturn($is_successful);

		$this->object
				->expects($this->any())
				->method('requestHead')
				->willReturn($response);

		$this->object->setURL($url);
		$this->assertEquals($this->object->isReachable(), $expected);
	}

	public function providerIsReachable() {

		return array(
			array('http://foo.bar/', true, true),
			array('http://bar.foo/', null, false),
			array('http://bar.foo/', false, false),
		);
	}

	/**
	 * @covers ::getMeta
	 */
	public function testGetMeta() {
		$this->assertInstanceOf('hypeJunction\\Scraper\\MetaHandler', $this->object->getMeta());
	}

	/**
	 * @covers ::isInSite
	 */
	public function testIsInSite() {

		$this->object->setURL('http://localhost/foo');
		$this->assertTrue($this->object->isInSite('http://localhost/'));
		$this->assertFalse($this->object->isInSite('http://bar/'));
	}

	/**
	 * @covers ::getGuid
	 */
	public function testGetGuid() {

		$this->object->setURL('http://localhost/');
		$this->assertEquals($this->object->getGuid('http://localhost/'), 0);

		$this->object->setURL('http://localhost/');
		$this->assertEquals($this->object->getGuid('http://bar/'), 0);

		$this->object->setURL('http://localhost/foo/123');
		$this->assertEquals($this->object->getGuid('http://localhost/'), 123);
	}

	/**
	 * @covers ::getEntity
	 */
	public function testGetEntity() {

		$entity = $this->getMock('ElggEntity');

		$this->object->expects($this->any())
				->method('getEntity')
				->willReturnOnConsecutiveCalls(false, false, $entity);

		$this->object->setURL('http://localhost/');
		$this->assertEquals($this->object->getEntity('http://localhost/'), false);

		$this->object->setURL('http://localhost/');
		$this->assertEquals($this->object->getEntity('http://bar/'), false);

		$this->object->setURL('http://localhost/foo/123');
		$this->assertEquals($this->object->getEntity('http://localhost/'), $entity);
	}

}
