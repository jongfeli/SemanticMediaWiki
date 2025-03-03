<?php

namespace SMW\Tests\Listener\EventListener\EventListeners;

use Onoi\EventDispatcher\DispatchContext;
use SMW\Listener\EventListener\EventListeners\InvalidateResultCacheEventListener;
use SMW\Tests\TestEnvironment;

/**
 * @covers \SMW\Listener\EventListener\EventListeners\InvalidateResultCacheEventListener
 * @group semantic-mediawiki
 *
 * @license GPL-2.0-or-later
 * @since 3.1
 *
 * @author mwjames
 */
class InvalidateResultCacheEventListenerTest extends \PHPUnit\Framework\TestCase {

	private $resultCache;
	private $spyLogger;

	protected function setUp(): void {
		parent::setUp();

		$this->spyLogger = TestEnvironment::newSpyLogger();

		$this->resultCache = $this->getMockBuilder( '\SMW\Query\Cache\ResultCache' )
			->disableOriginalConstructor()
			->getMock();
	}

	protected function tearDown(): void {
		parent::tearDown();
	}

	public function testCanConstruct() {
		$this->assertInstanceOf(
			InvalidateResultCacheEventListener::class,
			new InvalidateResultCacheEventListener( $this->resultCache )
		);
	}

	public function testExecute() {
		$context = DispatchContext::newFromArray(
			[
				'subject' => 'Foo',
				'context' => 'Bar',
				'dependency_list' => []
			]
		);

		$this->resultCache->expects( $this->once() )
			->method( 'invalidateCache' );

		$instance = new InvalidateResultCacheEventListener(
			$this->resultCache
		);

		$instance->setLogger(
			$this->spyLogger
		);

		$instance->execute( $context );
	}

}
