<?php

namespace hypeJunction\Scraper;

use ElggEntity;
use Guzzle\Http\Message\Response;
use Guzzle\Service\Client;
use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass hypeJunction\Scraper\UrlHandler
 * 
 */
class UrlHandlerTest extends PHPUnit_Framework_TestCase {

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
				->setMethods(array('getHead', 'getBody', 'getHasher', 'getParser'))
				->getMock();

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
				->method('getHead')
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
				->method('getHead')
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
		$this->assertFalse($this->object->getContentType());
	}

	/**
	 * @covers ::getContent
	 * @dataProvider providerGetContent
	 */
	public function testGetContent($url, $content) {

		$response = $this->getMockBuilder('Guzzle\\Http\\Message\\Response')->disableOriginalConstructor()->getMock();

		$response->expects($this->any())
				->method('getBody')
				->willReturn($content);

		$this->object
				->expects($this->any())
				->method('getBody')
				->willReturn($response);

		$this->object->setURL($url);
		$this->assertEquals($this->object->getContent(), $content);
	}

	/**
	 * Assertions for getContent()
	 * @return array
	 */
	public function providerGetContent() {

		return array(
			array('invalid', false),
			array('http://foo.bar/', '<html></html>'),
			array('http://foo.bar/', json_encode(array('foo' => 'bar'))),
		);
	}

	/**
	 * @covers ::getContent
	 */
	public function testGetContentNoReponse() {
		$this->assertEquals('', $this->object->getContent());
	}

	/**
	 * @covers ::isReachable
	 */
	public function testIsReachableNullHead() {
		$this->object->setURL('http://foo.bar/');
		// Call to requestHead() will return null
		$this->assertEquals(false, $this->object->isReachable());
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
				->method('getHead')
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
	public function testGetMetaFromCacheWithData() {
		$hasher = $this->getMock('hypeJunction\\Scraper\\Hasher');
		$hasher->expects($this->any())
				->method('getMetadata')
				->willReturn(array('title' => 'Foo'));

		$this->object->expects($this->any())
				->method('getHasher')
				->willReturn($hasher);

		$this->object->setURL('http://localhost/url1');
		$this->assertInstanceOf('hypeJunction\\Scraper\\MetaHandler', $this->object->getMeta());

		// check static cache
		$this->assertInstanceOf('hypeJunction\\Scraper\\MetaHandler', $this->object->getMeta());
	}

	/**
	 * @covers ::getMeta
	 */
	public function testGetMetaFromCacheNoData() {

		$hasher = $this->getMock('hypeJunction\\Scraper\\Hasher');
		$this->object->expects($this->any())
				->method('getHasher')
				->willReturn($hasher);

		$parser = $this->getMock('hypeJunction\\Scraper\\Parser');
		$parser->expects($this->any())
				->method('getMetadata')
				->willReturn(array('title' => 'Foo'));
		$this->object->expects($this->any())
				->method('getParser')
				->willReturn($parser);

		$this->object->setURL('http://localhost/url2');
		$this->assertInstanceOf('hypeJunction\\Scraper\\MetaHandler', $this->object->getMeta());
	}

	/**
	 * @covers ::getMeta
	 */
	public function testGetMetaNoCache() {
		$parser = $this->getMock('hypeJunction\\Scraper\\Parser');
		$parser->expects($this->any())
				->method('getMetadata')
				->willReturn(array('title' => 'Foo'));

		$this->object->expects($this->any())
				->method('getParser')
				->willReturn($parser);

		$this->object->setURL('http://localhost/url3');
		$this->assertInstanceOf('hypeJunction\\Scraper\\MetaHandler', $this->object->getMeta(false));
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

		$this->object->setURL('http://localhost/');
		$this->assertEquals($this->object->getEntity('http://localhost/'), false);

		$this->object->setURL('http://localhost/');
		$this->assertEquals($this->object->getEntity('http://bar/'), false);

		/** @todo: mock get_entity() * */
		// $this->object->setURL('http://localhost/foo/123');
		// $this->assertInstanceOf('ElggEntity', $this->object->getEntity('http://localhost/'));
	}

	/**
	 * @covers ::analyze()
	 */
	public function testAnalyze() {
		$this->object->setURL('http://localhost/123');
		$this->assertInternalType('array', $this->object->analyze('http://localhost/'));
	}

	/**
	 * @covers ::getHasher
	 */
	public function testGetHasher() {
		$handler = new UrlHandler;
		return $this->assertInstanceOf('hypeJunction\\Scraper\\Hasher', $handler->getHasher());
	}

	/**
	 * @covers ::getParser
	 */
	public function testGetParser() {
		$handler = new UrlHandler;
		return $this->assertInstanceOf('hypeJunction\\Scraper\\Parser', $handler->getParser());
	}

	/**
	 * @covers ::getHead
	 * @covers ::getBody
	 */
	public function testGetHeadAndBody() {

		$response = $this->getMockBuilder('Guzzle\\Http\\Message\\Response')
				->disableOriginalConstructor()
				->getMock();

		$request = $this->getMockBuilder('Guzzle\\Http\\Message\\Request')
				->disableOriginalConstructor()
				->setMethods(array('send'))
				->getMock();
		$request->expects($this->any())
				->method('send')
				->willReturn($response);

		$client = $this->getMockBuilder('Guzzle\\Service\\Client')
				->disableOriginalConstructor()
				->setMethods(array('head', 'get'))
				->getMock();
		$client->expects($this->any())
				->method('head')
				->willReturn($request);
		$client->expects($this->any())
				->method('get')
				->willReturn($request);

		$stub = $this->getMockBuilder('hypeJunction\\Scraper\\UrlHandler')
				->setMethods(array('getClient'))
				->getMock();
		$stub->expects($this->any())
				->method('getClient')
				->willReturn($client);

		$this->assertInstanceOf('Guzzle\\Http\\Message\\Response', $stub->getHead());
		$this->assertInstanceOf('Guzzle\\Http\\Message\\Response', $stub->getBody());
	}

}
