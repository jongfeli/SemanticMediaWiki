<?php

namespace SMW\Tests\Query\Language;

use SMW\DIWikiPage;
use SMW\Localizer;
use SMW\Query\Language\ConceptDescription;
use SMW\Query\Language\ThingDescription;

/**
 * @covers \SMW\Query\Language\ConceptDescription
 * @group semantic-mediawiki
 *
 * @license GPL-2.0-or-later
 * @since 2.1
 *
 * @author mwjames
 */
class ConceptDescriptionTest extends \PHPUnit\Framework\TestCase {

	public function testCanConstruct() {
		$concept = $this->getMockBuilder( '\SMW\DIWikiPage' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'SMW\Query\Language\ConceptDescription',
			new ConceptDescription( $concept )
		);

		// Legacy
		$this->assertInstanceOf(
			'SMW\Query\Language\ConceptDescription',
			new \SMWConceptDescription( $concept )
		);
	}

	public function testCommonMethods() {
		$ns = Localizer::getInstance()->getNsText( SMW_NS_CONCEPT );

		$concept = new DIWikiPage( 'Foo', SMW_NS_CONCEPT );
		$instance = new ConceptDescription( $concept );

		$this->assertEquals( $concept, $instance->getConcept() );

		$this->assertEquals( "[[{$ns}:Foo]]", $instance->getQueryString() );
		$this->assertEquals( " <q>[[{$ns}:Foo]]</q> ", $instance->getQueryString( true ) );

		$this->assertFalse( $instance->isSingleton() );
		$this->assertEquals( [], $instance->getPrintRequests() );

		$this->assertSame( 1, $instance->getSize() );
		$this->assertSame( 0, $instance->getDepth() );
		$this->assertEquals( 4, $instance->getQueryFeatures() );
	}

	public function testGetFingerprint() {
		$instance = new ConceptDescription(
			new DIWikiPage( 'Foo', SMW_NS_CONCEPT )
		);

		$expected = $instance->getFingerprint();

		$instance = new ConceptDescription(
			new DIWikiPage( 'Bar', SMW_NS_CONCEPT )
		);

		$this->assertNotSame(
			$expected,
			$instance->getFingerprint()
		);
	}

	public function testPrune() {
		$instance = new ConceptDescription( new DIWikiPage( 'Foo', SMW_NS_CONCEPT ) );

		$maxsize  = 1;
		$maxDepth = 1;
		$log      = [];

		$this->assertEquals(
			$instance,
			$instance->prune( $maxsize, $maxDepth, $log )
		);

		$maxsize  = 0;
		$maxDepth = 1;
		$log      = [];

		$this->assertEquals(
			new ThingDescription(),
			$instance->prune( $maxsize, $maxDepth, $log )
		);
	}

}
