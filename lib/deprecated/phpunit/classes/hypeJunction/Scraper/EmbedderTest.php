<?php

namespace hypeJunction\Scraper;

use PHPUnit_Framework_TestCase;
use stdClass;

/**
 * @coversDefaultClass hypeJunction\Scraper\Embedder
 */
class EmbedderTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var Embedder
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		$stub = $this->getMockBuilder('hypeJunction\\Scraper\\Embedder')
				->setMethods(array('getMeta'))
				->getMock();

		$stub->expects($this->any())
				->method('getMeta')
				->willReturn(new MetaHandler);

		$this->object = $stub;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() {
		
	}

	/**
	 * @covers ::getInstance
	 */
	public function testGetInstance() {
		$this->assertInstanceOf('hypeJunction\\Scraper\\Embedder', Embedder::getInstance());
		$embedder = new Embedder('http://localhost/test');
		$instance = Embedder::getInstance();
		$this->assertEquals($embedder->getURL(), $instance->getURL());
	}
	
	/**
	 * @covers ::__construct
	 */
	public function testConstructorNotNull() {
		$this->assertNotNull(new Embedder);
	}

	/**
	 * @covers ::getEmbedView
	 * @covers ::getView
	 */
	public function testGetEmbedViewEntity() {
		$entity = $this->getMockBuilder('ElggEntity')->disableOriginalConstructor()->getMock();
		$this->assertInternalType('string', $this->object->getEmbedView($entity));
	}

	/**
	 * @covers ::getEmbedView
	 * @covers ::getView
	 */
	public function testGetEmbedViewURL() {
		$stub = $this->getMockBuilder('hypeJunction\\Scraper\\UrlHandler')->getMock();
		$stub->expects($this->any())
				->method('getMeta')
				->willReturn(new MetaHandler);

		$this->assertInternalType('string', $this->object->getEmbedView($stub));
	}

	/**
	 * @covers ::setURL
	 * @covers ::getURL
	 */
	public function testSetURL() {
		$url = 'http://localhost/';

		$this->object->setURL($url);
		$this->assertInstanceOf('hypeJunction\\Scraper\\UrlHandler', $this->object->getURL());

		$this->object->setURL(new UrlHandler($url));
		$this->assertInstanceOf('hypeJunction\\Scraper\\UrlHandler', $this->object->getURL());

		$this->object->setURL(new stdClass());
		$this->assertInstanceOf('hypeJunction\\Scraper\\UrlHandler', $this->object->getURL());
	}

	/**
	 * @covers ::setEntity
	 * @covers ::getEntity
	 * @covers ::getURL
	 */
	public function testGetEntityWithSet() {
		$url = 'http://localhost/view/123';

		$stub = $this->getMockBuilder('ElggEntity')->disableOriginalConstructor()->getMock();
		$stub->expects($this->once())
				->method('getURL')
				->willReturn($url);

		$this->object->setEntity($stub);

		$this->assertInstanceOf('ElggEntity', $this->object->getEntity());
		$this->assertEquals($url, $this->object->getURL()->getURL());
	}

	/**
	 * @covers ::getEntity
	 * @covers ::setURL
	 */
	public function testGetEntityWithoutSet() {
		$entity = $this->getMockBuilder('ElggEntity')->disableOriginalConstructor()->getMock();
		$stub = $this->getMock('hypeJunction\\Scraper\\UrlHandler');
		$stub->expects($this->once())
				->method('getEntity')
				->willReturn($entity);

		$this->object->setURL($stub);
		$this->assertInstanceOf('ElggEntity', $this->object->getEntity());
	}

	/**
	 * @covers ::getMeta
	 */
	public function testGetMeta() {
		$stub = $this->getMock('hypeJunction\\Scraper\\UrlHandler');
		$stub->expects($this->any())
				->method('getMeta')
				->willReturn(new MetaHandler);

		$this->object->setURL($stub);
		$this->assertInstanceOf('hypeJunction\\Scraper\\MetaHandler', $this->object->getMeta());
	}

	/**
	 * @covers ::extractMeta
	 */
	public function testExtractMeta() {
		$stub = $this->getMock('hypeJunction\\Scraper\\UrlHandler');
		$stub->expects($this->any())
				->method('getMeta')
				->willReturn(new MetaHandler);

		$this->object->setURL($stub);
		$this->assertInstanceOf('hypeJunction\\Scraper\\MetaHandler', $this->object->extractMeta());
	}

	/**
	 * @covers ::getType
	 */
	public function testGetTypeEntity() {
		$entity = $this->getMockBuilder('ElggEntity')->disableOriginalConstructor()->getMock();

		$this->object->setEntity($entity);
		$this->assertEquals(Embedder::TYPE_ENTITY, $this->object->getType());
	}

	/**
	 * @covers ::getType
	 */
	public function testGetTypeImageAndSrc() {
		$stub = $this->getMock('hypeJunction\\Scraper\\UrlHandler');
		$stub->expects($this->any())
				->method('isValid')
				->willReturn(true);
		$stub->expects($this->any())
				->method('isReachable')
				->willReturn(true);
		$stub->expects($this->any())
				->method('getMeta')
				->willReturn(new MetaHandler);

		$this->object->setURL($stub);
		$this->assertEquals(Embedder::TYPE_SRC, $this->object->getType());

		$stub->expects($this->once())
				->method('isImageFile')
				->willReturn(true);

		$this->object->setURL($stub);
		$this->assertEquals(Embedder::TYPE_IMAGE, $this->object->getType());
	}

	/**
	 * @covers ::getType
	 */
	public function testGetTypeDefault() {
		$stub = $this->getMock('hypeJunction\\Scraper\\UrlHandler');
		$stub->expects($this->any())
				->method('isValid')
				->willReturn(false);

		$this->object->setURL($stub);
		$this->assertEquals(Embedder::TYPE_DEFAULT, $this->object->getType());
	}

	/**
	 * @covers ::prepareParams
	 */
	public function testPrepareParams() {

		$stub = $this->getMock('hypeJunction\\Scraper\\UrlHandler');
		$this->object->setURL($stub);

		$params = $this->object->prepareParams();

		$this->assertInternalType('array', $params);
	}

}
