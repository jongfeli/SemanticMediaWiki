<?php

namespace SMW\Tests;

use SMW\DataItemFactory;
use SMW\Settings;
use SMW\WantedPropertiesQueryPage;

/**
 * @covers \SMW\WantedPropertiesQueryPage
 * @group semantic-mediawiki
 *
 * @license GPL-2.0-or-later
 * @since 1.9
 *
 * @author mwjames
 */
class WantedPropertiesQueryPageTest extends \PHPUnit\Framework\TestCase {

	use PHPUnitCompat;

	private $store;
	private $skin;
	private $settings;
	private $dataItemFactory;

	protected function setUp(): void {
		parent::setUp();

		$this->store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$this->skin = $this->getMockBuilder( '\Skin' )
			->disableOriginalConstructor()
			->getMock();

		$this->settings = Settings::newFromArray( [] );

		$this->dataItemFactory = new DataItemFactory();
	}

	public function testCanConstruct() {
		$this->assertInstanceOf(
			'\SMW\WantedPropertiesQueryPage',
			new WantedPropertiesQueryPage( $this->store, $this->settings )
		);
	}

	public function testFormatResultDIError() {
		$error = $this->dataItemFactory->newDIError( 'Foo' );

		$instance = new WantedPropertiesQueryPage(
			$this->store,
			$this->settings
		);

		$result = $instance->formatResult(
			$this->skin,
			[ $error, null ]
		);

		$this->assertIsString(

			$result
		);

		$this->assertEmpty(
			$result
		);
	}

	public function testFormatPropertyItemOnUserDefinedProperty() {
		$property = $this->dataItemFactory->newDIProperty( 'Foo' );

		$instance = new WantedPropertiesQueryPage(
			$this->store,
			$this->settings
		);

		$result = $instance->formatResult(
			$this->skin,
			[ $property, 42 ]
		);

		$this->assertContains(
			'Foo',
			$result
		);
	}

	public function testFormatPropertyItemOnPredefinedProperty() {
		$property = $this->dataItemFactory->newDIProperty( '_MDAT' );

		$instance = new WantedPropertiesQueryPage(
			$this->store,
			$this->settings
		);

		$result = $instance->formatResult(
			$this->skin,
			[ $property, 42 ]
		);

		$this->assertEmpty(
			$result
		);
	}

}
