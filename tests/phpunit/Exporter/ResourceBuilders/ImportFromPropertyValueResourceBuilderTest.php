<?php

namespace SMW\Tests\Exporter\ResourceBuilders;

use SMW\DataItemFactory;
use SMW\Exporter\Element\ExpNsResource;
use SMW\Exporter\ResourceBuilders\ImportFromPropertyValueResourceBuilder;
use SMW\Tests\TestEnvironment;
use SMWExpData as ExpData;

/**
 * @covers \SMW\Exporter\ResourceBuilders\ImportFromPropertyValueResourceBuilder
 * @group semantic-mediawiki
 *
 * @license GPL-2.0-or-later
 * @since 2.5
 *
 * @author mwjames
 */
class ImportFromPropertyValueResourceBuilderTest extends \PHPUnit\Framework\TestCase {

	private $dataItemFactory;
	private $testEnvironment;

	protected function setUp(): void {
		parent::setUp();
		$this->dataItemFactory = new DataItemFactory();
		$this->testEnvironment = new TestEnvironment();

		$this->testEnvironment->resetPoolCacheById( \SMWExporter::POOLCACHE_ID );
	}

	protected function tearDown(): void {
		$this->testEnvironment->tearDown();
		parent::tearDown();
	}

	public function testCanConstruct() {
		$this->assertInstanceof(
			ImportFromPropertyValueResourceBuilder::class,
			new ImportFromPropertyValueResourceBuilder()
		);
	}

	public function testIsNotResourceBuilderForNonImpoProperty() {
		$property = $this->dataItemFactory->newDIProperty( 'Foo' );

		$instance = new ImportFromPropertyValueResourceBuilder();

		$this->assertFalse(
			$instance->isResourceBuilderFor( $property )
		);
	}

	public function testAddResourceValueForImpoProperty() {
		$property = $this->dataItemFactory->newDIProperty( '_IMPO' );
		$dataItem = $this->dataItemFactory->newDIWikiPage( 'Foo', NS_MAIN );

		$expData = new ExpData(
			new ExpNsResource( 'Foobar', 'Bar', 'Mo', $this->dataItemFactory->newDIWikiPage( 'Bar', NS_MAIN ) )
		);

		$instance = new ImportFromPropertyValueResourceBuilder();

		$instance->addResourceValue(
			$expData,
			$property,
			$dataItem
		);

		$this->assertTrue(
			$instance->isResourceBuilderFor( $property )
		);
	}

}
