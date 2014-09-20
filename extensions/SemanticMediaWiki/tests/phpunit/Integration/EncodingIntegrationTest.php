<?php

namespace SMW\Tests\Integration;

use SMW\MediaWiki\Hooks\BaseTemplateToolbox;
use SMW\Application;
use SMW\Settings;

use Title;

/**
 * @covers \SMW\MediaWiki\Hooks\BaseTemplateToolbox
 * @covers \SMWInfolink
 *
 * @ingroup Test
 *
 * @group SMW
 * @group SMWExtension
 * @group semantic-mediawiki-integration
 * @group mediawiki-databaseless
 *
 * @license GNU GPL v2+
 * @since 1.9
 *
 * @author mwjames
 */
class EncodingIntegrationTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider baseTemplateToolboxDataProvider
	 */
	public function testBaseTemplateToolboxURLEncoding( $setup, $expected ) {

		$toolbox  = '';

		Application::getInstance()->registerObject(
			'Settings',
			Settings::newFromArray( $setup['settings'] )
		);

		$instance = new BaseTemplateToolbox( $setup['skinTemplate'], $toolbox );

		$instance->process();

		$this->assertContains(
			$expected,
			$toolbox['smw-browse']['href']
		);

		Application::clear();
	}

	public function baseTemplateToolboxDataProvider() {

		$provider = array();

		$provider[] = array( $this->newBaseTemplateToolboxSetup( '2013/11/05' ), 'Special:Browse/2013-2F11-2F05' );
		$provider[] = array( $this->newBaseTemplateToolboxSetup( '2013-06-30' ), 'Special:Browse/2013-2D06-2D30' );
		$provider[] = array( $this->newBaseTemplateToolboxSetup( '2013$06&30' ), 'Special:Browse/2013-2406-2630' );

		return $provider;
	}

	private function newBaseTemplateToolboxSetup( $text ) {

		$settings = array(
			'smwgNamespacesWithSemanticLinks' => array( NS_MAIN => true ),
			'smwgToolboxBrowseLink'           => true
		);

		$message = $this->getMockBuilder( '\Message' )
			->disableOriginalConstructor()
			->getMock();

		$skin = $this->getMockBuilder( '\Skin' )
			->disableOriginalConstructor()
			->getMock();

		$skin->expects( $this->atLeastOnce() )
			->method( 'getTitle' )
			->will( $this->returnValue( Title::newFromText( $text, NS_MAIN ) ) );

		$skin->expects( $this->atLeastOnce() )
			->method( 'msg' )
			->will( $this->returnValue( $message ) );

		$skinTemplate = $this->getMockBuilder( '\SkinTemplate' )
			->disableOriginalConstructor()
			->getMock();

		$skinTemplate->expects( $this->atLeastOnce() )
			->method( 'getSkin' )
			->will( $this->returnValue( $skin ) );

		$skinTemplate->data['isarticle'] = true;

		return array( 'settings' => $settings, 'skinTemplate' => $skinTemplate );
	}

}