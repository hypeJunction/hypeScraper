<?php

namespace hypeJunction\Scraper;

use hypeJunction\Scraper\Qualifiers\Qualifier;
use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass hypeJunction\Scraper\Extractor
 */
class ExtractorTest extends PHPUnit_Framework_TestCase {

	/**
	 *
	 * @var Extractor 
	 */
	protected $object;

	protected function setUp() {

		//_elgg_services()->setValue('session', new ElggSession(new MockSessionStorage()));

		$this->object = $this->getMockBuilder(__NAMESPACE__ . '\\Extractor')
				->setMethods(array('linkifyQualifier'))
				->getMock();

		$this->object->expects($this->any())
				->method('linkifyQualifier')
				->willReturnCallback(array($this, 'linkifyQualifierCallback'));
	}

	protected function tearDown() {
		
	}

	/**
	 * Callback for linkifying qualifiers in mock method
	 */
	public function linkifyQualifierCallback($type, $base_url, $vars) {
		switch ($type) {
			case Qualifier::TYPE_HASHTAG:
				return '<a>#foobar</a>';
			case Qualifier::TYPE_URL :
				return '<a>http://foo.bar/</a>';
			case Qualifier::TYPE_USERNAME :
				return '<a>@foobar</a>';
			case Qualifier::TYPE_EMAIL :
				return '<a>foo@bar.com</a>';
			default :
				return '';
		}
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstructorNotNull() {
		$this->assertNotNull(new Extractor);
		$this->assertNotNull(new Extractor('Lorem ipsum'));
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstructorAttributes() {
		$extractor = new Extractor;
		$this->assertAttributeEquals('', 'text', $extractor);
		$this->assertAttributeEquals('', 'html', $extractor);
		$this->assertAttributeInternalType('array', 'hashtags', $extractor);
		$this->assertAttributeInternalType('array', 'emails', $extractor);
		$this->assertAttributeInternalType('array', 'urls', $extractor);
		$this->assertAttributeInternalType('array', 'usernames', $extractor);
	}

	/**
	 * @covers ::setText
	 * @covers ::getText
	 */
	public function testSetText() {
		$text = 'Hello world!';
		$this->assertEquals($text, $this->object->setText($text)->getText($text));
	}

	/**
	 * @covers ::extract
	 */
	public function testExtract() {
		$this->assertInstanceOf('hypeJunction\\Scraper\\Extractor', Extractor::extract());
	}

	/**
	 * @covers ::render
	 */
	public function testRender() {
		$this->assertNotEmpty(Extractor::render('Lorem ipsum'));
	}

	/**
	 * @covers ::extractAll
	 */
	public function testExtractAll() {
		$this->object->setText('Hello world!');
		$this->assertSame($this->object->extractAll(), $this->object);
		$this->assertEquals($this->object->hashtags, array());
		$this->assertEquals($this->object->emails, array());
		$this->assertEquals($this->object->urls, array());
		$this->assertEquals($this->object->usernames, array());
	}

	/**
	 * @covers ::extractHashtags
	 * @dataProvider providerExtractHashtags
	 */
	public function testExtractHashtags($input, $output) {
		$this->assertEquals($this->object->extractHashtags($input), $output);
	}

	/**
	 * Assertions for hashtag extraction
	 * @return array
	 */
	public function providerExtractHashtags() {
		return array(
			array(
				"#hashTag",
				array(
					'#hashTag',
				)
			),
			array(
				"#hash_tag #foobar123",
				array(
					'#hash_tag',
					'#foobar123',
				)
			),
			array(
				" #foobar!#foobar.",
				array(
					'#foobar',
					'#foobar',
				)
			),
			array(
				" <a href=\"http://localhost/#foobar\">#foo</a>",
				array(
					'#foo'
				)
			),
			array(
				" <a href=\"http://localhost/#foobar\"> #443_hash (#H123tag)/#\$bd </a>",
				array(
					'#443_hash',
					'#H123tag',
				)
			),
		);
	}

	/**
	 * @covers ::extractURLs
	 * @dataProvider providerExtractURLs
	 */
	public function testExtractURLs($input, $output) {
		$this->assertEquals($this->object->extractURLs($input), $output);
	}

	/**
	 * Assertions for extract url tests
	 * @return array
	 */
	public function providerExtractURLs() {
		return array(
			array(
				"<a>bar.com</a>",
				array(),
			),
			array(
				"<a href=\"http://bar.com/foo?q=123\">http://bar.com</a>",
				array(
					'http://bar.com/foo?q=123',
					'http://bar.com'
				),
			),
			array(
				" http://bar.com <a href=\"ftp://foo.com\">https://bar.com/</a>",
				array(
					'http://bar.com',
					'ftp://foo.com',
					'https://bar.com/',
				)
			),
			array(
				"<a href=\"http://bar.com#foobar\" style=\"background-image:url(http://bar.com?query)\">http://bar.com</a>",
				array(
					'http://bar.com#foobar',
					'http://bar.com?query',
					'http://bar.com'
				),
			),
		);
	}

	/**
	 * @covers ::extractUsernames
	 * @dataProvider providerExtractUsernames
	 */
	public function testExtractUsernames($input, $output) {
		$this->assertEquals($this->object->extractUsernames($input), $output);
	}

	/**
	 * Assertions for email extractions
	 */
	public function providerExtractUsernames() {

		return array(
			array(
				"@user_name",
				array(
					"@user_name"
				)
			),
			array(
				"@123@foo_123!",
				array(
					"@123",
					"@foo_123",
				)
			),
			array(
				" @Bar() <a>@foo&</a>",
				array(
					"@Bar",
					"@foo"
				)
			),
			array(
				"<a href=\"http://foo.com/@FooBar\">@Bar_</a>",
				array(
					"@Bar_"
				)
			),
			array(
				" me@foobar.com @foobar",
				array(
					"@foobar",
				)
			)
		);
	}

	/**
	 * @covers ::extractEmails
	 * @dataProvider providerExtractEmails
	 */
	public function testExtractEmails($input, $output) {
		$this->assertEquals($this->object->extractEmails($input), $output);
	}

	public function providerExtractEmails() {

		return array(
			array(
				"foo@bar.bar.com",
				array(
					"foo@bar.bar.com"
				)
			),
			array(
				" foo_bar@bar1.com <a>fooBar#@bar.com</a>",
				array(
					"foo_bar@bar1.com"
				)
			),
			array(
				" bar.com @foo",
				array()
			),
			array(
				" foo@bar.com <a href=\"mailto:foo@bar.com\"> foo@bar.com </a >",
				array(
					"foo@bar.com",
					"foo@bar.com",
					"foo@bar.com",
				)
			),
			array(
				" @bar.com foo@bar.com",
				array(
					"foo@bar.com"
				)
			),
			array(
				"<span> foo@bar.com</span>",
				array(
					"foo@bar.com"
				)
			),
		);
	}

	/**
	 * @covers ::linkifyAll
	 * @covers ::getHTML
	 * @dataProvider providerLinkifyAll
	 */
	public function testLinkifyAll($input, $output) {
		$this->object->setText($input);
		$this->assertEquals($this->object->linkifyAll()->getHTML(), $output);
	}

	/**
	 * Assertions for linkifying all qualifiers
	 * @return array
	 */
	public function providerLinkifyAll() {

		return array(
			array(
				"@foobar, check out this #foo http://foobar.com/?foo=bar#foo email me at me@foobar.com",
				"<a>@foobar</a>, check out this <a>#foobar</a> <a>http://foo.bar/</a> email me at <a>foo@bar.com</a>",
			),
			array(
				"<a>@foobar</a>, check out this <a>#foobar</a> <a>http://foo.bar/</a> email me at <a>foo@bar.com</a>",
				"<a>@foobar</a>, check out this <a>#foobar</a> <a>http://foo.bar/</a> email me at <a>foo@bar.com</a>",
			),
		);
	}

	/**
	 * @covers ::linkifyHashtags
	 * @covers ::pregReplaceHashtagCallback
	 * @dataProvider providerLinkifyHashtags
	 */
	public function testLinkifyHashtags($input, $output) {
		$this->assertEquals($this->object->linkifyHashtags($input), $output);
	}

	/**
	 * Assertions for hashtag matching
	 * @return array
	 */
	public function providerLinkifyHashtags() {

		return array(
			array(
				"#foobar",
				"<a>#foobar</a>"
			),
			array(
				"#foobar #foobar",
				"<a>#foobar</a> <a>#foobar</a>"
			),
			array(
				" #foobar#foobar",
				" <a>#foobar</a><a>#foobar</a>"
			),
			array(
				" <a href=\"http://localhost/#foobar\">#foobar</a>",
				" <a href=\"http://localhost/#foobar\">#foobar</a>"
			),
			array(
				" <a href=\"http://localhost/#foobar\"> #foobar (#foobar)/#foobar </a>",
				" <a href=\"http://localhost/#foobar\"> #foobar (#foobar)/#foobar </a>"
			),
			array(
				" <a rel=\"#foobar\"> #foobar </a > #foobar ",
				" <a rel=\"#foobar\"> #foobar </a > <a>#foobar</a> "),
			array(
				" <a>#foobar</a> <a style=\"background-image:url(http://localhost/#foobar)\"></a> <span>#foobar</span> #foobar",
				" <a>#foobar</a> <a style=\"background-image:url(http://localhost/#foobar)\"></a> "
				. "<span><a>#foobar</a></span> <a>#foobar</a>"
			),
			array(
				"http://foo.bar?foo=bar#bar",
				"http://foo.bar?foo=bar#bar"
			)
		);
	}

	/**
	 * @covers ::linkifyURLs
	 * @covers ::pregReplaceUrlCallback
	 * @dataProvider providerLinkifyURLs
	 */
	public function testLinkifyURLs($input, $output) {
		$this->assertEquals($this->object->linkifyURLs($input), $output);
	}

	/**
	 * Assertions for linkifying URLs
	 * @return array
	 */
	public function providerLinkifyURLs() {
		return array(
			array(
				"<a>bar.com</a>",
				"<a>bar.com</a>"
			),
			array(
				"<a href=\"http://bar.com\">http://bar.com</a>",
				"<a href=\"http://bar.com\">http://bar.com</a>",
			),
			array(
				" http://bar.com <a href>http://bar.com/</a>",
				" <a>http://foo.bar/</a> <a href>http://bar.com/</a>"
			),
			array(
				"<a href=\"http://bar.com#foobar\" style=\"background-image:url(http://bar.com?query)\">http://bar.com</a>",
				"<a href=\"http://bar.com#foobar\" style=\"background-image:url(http://bar.com?query)\">http://bar.com</a>",
			),
		);
	}

	/**
	 * @covers ::linkifyUsernames
	 * @covers ::pregReplaceUsernameCallback
	 * @dataProvider providerLinkifyUsernames
	 */
	public function testLinkifyUsernames($input, $output) {
		$this->assertEquals($this->object->linkifyUsernames($input), $output);
	}

	/**
	 * Assertions for linkifying usernames
	 * @return array
	 */
	public function providerLinkifyUsernames() {
		return array(
			array(
				"@foobar",
				"<a>@foobar</a>"
			),
			array(
				"@foobar@foobar",
				"<a>@foobar</a><a>@foobar</a>",
			),
			array(
				" @foobar <a>@foobar</a>",
				" <a>@foobar</a> <a>@foobar</a>",
			),
			array(
				"<a href=\"http://foo.com/@foobar\">@foobar</a>",
				"<a href=\"http://foo.com/@foobar\">@foobar</a>",
			),
			array(
				"<span >@foobar @foobar@foobar</span>",
				"<span ><a>@foobar</a> <a>@foobar</a><a>@foobar</a></span>",
			),
			array(
				" me@foobar.com @foobar",
				" me@foobar.com <a>@foobar</a>",
			),
			array(
				"http://foo.bar/@foo",
				"http://foo.bar/@foo",
			),
		);
	}

	/**
	 * @covers ::linkifyEmails
	 * @covers ::pregReplaceEmailCallback
	 * @dataProvider providerLinkifyEmails
	 */
	public function testLinkifyEmails($input, $output) {
		$this->assertEquals($this->object->linkifyEmails($input), $output);
	}

	/**
	 * Assertions for email matching
	 * @return array
	 */
	public function providerLinkifyEmails() {

		return array(
			array(
				"foo@bar.com",
				"<a>foo@bar.com</a>"
			),
			array(
				" foo@bar.com <a>foo@bar.com</a>",
				" <a>foo@bar.com</a> <a>foo@bar.com</a>"
			),
			array(
				" bar.com @foo",
				" bar.com @foo",
			),
			array(
				" foo@bar.com <a href=\"mailto:foo@bar.com\"> foo@bar.com </a >",
				" <a>foo@bar.com</a> <a href=\"mailto:foo@bar.com\"> foo@bar.com </a >"
			),
			array(
				" @bar.com foo@bar.com",
				" @bar.com <a>foo@bar.com</a>"
			),
			array(
				"<span> foo@bar.com</span>",
				"<span> <a>foo@bar.com</a></span>"
			),
		);
	}

}
