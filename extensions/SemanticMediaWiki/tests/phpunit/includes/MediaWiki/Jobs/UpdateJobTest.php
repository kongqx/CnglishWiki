<?php

namespace SMW\Tests\MediaWiki\Jobs;

use SMW\MediaWiki\Jobs\UpdateJob;
use SMW\Settings;
use SMW\Application;

use Title;

/**
 * @covers \SMW\MediaWiki\Jobs\UpdateJob
 *
 * @ingroup Test
 *
 * @group SMW
 * @group SMWExtension
 *
 * @license GNU GPL v2+
 * @since 1.9
 *
 * @author mwjames
 */
class UpdateJobTest extends \PHPUnit_Framework_TestCase {

	private $application;

	protected function setUp() {
		parent::setUp();

		$this->application = Application::getInstance();

		$settings = Settings::newFromArray( array(
			'smwgCacheType'        => 'hash',
			'smwgEnableUpdateJobs' => false
		) );

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$this->application->registerObject( 'Store', $store );
		$this->application->registerObject( 'Settings', $settings );
	}

	protected function tearDown() {
		$this->application->clear();

		parent::tearDown();
	}

	public function testCanConstruct() {

		$title = $this->getMockBuilder( 'Title' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'SMW\MediaWiki\Jobs\UpdateJob',
			new UpdateJob( $title )
		);

		// FIXME Delete SMWUpdateJob assertion after all
		// references to SMWUpdateJob have been removed
		$this->assertInstanceOf(
			'SMW\MediaWiki\Jobs\UpdateJob',
			new \SMWUpdateJob( $title )
		);
	}

	public function testJobWithMissingParserOutput() {

		$title = $this->getMockBuilder( 'Title' )
			->disableOriginalConstructor()
			->getMock();

		$title->expects( $this->any() )
			->method( 'exists' )
			->will( $this->returnValue( true ) );

		$instance = new UpdateJob( $title );
		$instance->setJobQueueEnabledState( false );

		$this->assertFalse(	$instance->run() );
	}

	public function testJobWithInvalidTitle() {

		$title = $this->getMockBuilder( 'Title' )
			->disableOriginalConstructor()
			->getMock();

		$title->expects( $this->once() )
			->method( 'exists' )
			->will( $this->returnValue( false ) );

		$this->application->registerObject( 'ContentParser', null );

		$instance = new UpdateJob( $title );
		$instance->setJobQueueEnabledState( false );

		$this->assertTrue( $instance->run() );
	}

	public function testJobWithNoRevisionAvailable() {

		$title = $this->getMockBuilder( 'Title' )
			->disableOriginalConstructor()
			->getMock();

		$title->expects( $this->once() )
			->method( 'exists' )
			->will( $this->returnValue( true ) );

		$contentParser = $this->getMockBuilder( '\SMW\ContentParser' )
			->disableOriginalConstructor()
			->getMock();

		$contentParser->expects( $this->once() )
			->method( 'getOutput' )
			->will( $this->returnValue( null ) );

		$this->application->registerObject( 'ContentParser', $contentParser );

		$instance = new UpdateJob( $title );
		$instance->setJobQueueEnabledState( false );

		$this->assertFalse( $instance->run() );
	}

	public function testJobWithValidRevision() {

		$title = $this->getMockBuilder( 'Title' )
			->disableOriginalConstructor()
			->getMock();

		$title->expects( $this->once() )
			->method( 'getDBkey' )
			->will( $this->returnValue( __METHOD__ ) );

		$title->expects( $this->once() )
			->method( 'getNamespace' )
			->will( $this->returnValue( 0 ) );

		$title->expects( $this->once() )
			->method( 'exists' )
			->will( $this->returnValue( true ) );

		$contentParser = $this->getMockBuilder( '\SMW\ContentParser' )
			->disableOriginalConstructor()
			->getMock();

		$contentParser->expects( $this->atLeastOnce() )
			->method( 'getOutput' )
			->will( $this->returnValue( new \ParserOutput ) );

		$this->application->registerObject( 'ContentParser', $contentParser );

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->setMethods( array( 'updateData' ) )
			->getMockForAbstractClass();

		$store->expects( $this->once() )
			->method( 'updateData' );

		$this->application->registerObject( 'Store', $store );

		$instance = new UpdateJob( $title );
		$instance->setJobQueueEnabledState( false );

		$this->assertTrue( $instance->run() );
	}

}
