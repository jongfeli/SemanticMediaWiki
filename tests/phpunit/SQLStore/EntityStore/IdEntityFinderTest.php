<?php

namespace SMW\Tests\SQLStore\EntityStore;

use SMW\DIWikiPage;
use SMW\IteratorFactory;
use SMW\MediaWiki\Connection\Database;
use SMW\SQLStore\EntityStore\IdEntityFinder;
use SMW\Tests\TestEnvironment;

/**
 * @covers \SMW\SQLStore\EntityStore\IdEntityFinder
 * @group semantic-mediawiki
 *
 * @license GPL-2.0-or-later
 * @since   2.1
 *
 * @author mwjames
 */
class IdEntityFinderTest extends \PHPUnit\Framework\TestCase {

	private $testEnvironment;
	private $cache;
	private $iteratorFactory;
	private $idCacheManager;
	private $store;
	private Database $connection;

	protected function setUp(): void {
		$this->testEnvironment = new TestEnvironment();

		$this->cache = $this->getMockBuilder( '\Onoi\Cache\Cache' )
			->disableOriginalConstructor()
			->getMock();

		$this->idCacheManager = $this->getMockBuilder( '\SMW\SQLStore\EntityStore\IdCacheManager' )
			->disableOriginalConstructor()
			->getMock();

		$this->idCacheManager->expects( $this->any() )
			->method( 'get' )
			->willReturn( $this->cache );

		$this->iteratorFactory = $this->getMockBuilder( '\SMW\IteratorFactory' )
			->disableOriginalConstructor()
			->getMock();

		$this->connection = $this->getMockBuilder( Database::class )
			->disableOriginalConstructor()
			->getMock();

		$this->store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->setMethods( [ 'getConnection' ] )
			->getMockForAbstractClass();

		$this->store->expects( $this->any() )
			->method( 'getConnection' )
			->willReturn( $this->connection );
	}

	public function testCanConstruct() {
		$this->assertInstanceOf(
			IdEntityFinder::class,
			new IdEntityFinder( $this->store, $this->iteratorFactory, $this->idCacheManager )
		);
	}

	public function testGetDataItemForNonCachedId() {
		$row = new \stdClass;
		$row->smw_id = 42;
		$row->smw_title = 'Foo';
		$row->smw_namespace = 0;
		$row->smw_iw = '';
		$row->smw_subobject = '';
		$row->smw_sortkey = '';
		$row->smw_sort = '';
		$row->smw_hash = 'x99w';

		$this->cache->expects( $this->once() )
			->method( 'save' )
			->with(
				42,
				$this->anything() );

		$this->cache->expects( $this->once() )
			->method( 'fetch' )
			->willReturn( false );

		$this->connection->expects( $this->once() )
			->method( 'selectRow' )
			->with(
				$this->anything(),
				$this->anything(),
				[ 'smw_id' => 42 ] )
			->willReturn( $row );

		$instance = new IdEntityFinder(
			$this->store,
			$this->iteratorFactory,
			$this->idCacheManager
		);

		$this->assertInstanceOf(
			'\SMW\DIWikiPage',
			$instance->getDataItemById( 42 )
		);
	}

	public function testGetDataItemForCachedId() {
		$this->cache->expects( $this->once() )
			->method( 'fetch' )
			->willReturn( new DIWikiPage( 'Foo', NS_MAIN ) );

		$this->connection->expects( $this->never() )
			->method( 'selectRow' );

		$instance = new IdEntityFinder(
			$this->store,
			$this->iteratorFactory,
			$this->idCacheManager
		);

		$this->assertInstanceOf(
			'\SMW\DIWikiPage',
			$instance->getDataItemById( 42 )
		);
	}

	public function testPredefinedPropertyItem() {
		$dataItem = new DIWikiPage( '_MDAT', SMW_NS_PROPERTY );
		$dataItem->setId( 42 );
		$dataItem->setSortKey( 'bar' );
		$dataItem->setOption( 'sort', 'BAR' );

		$row = new \stdClass;
		$row->smw_id = 42;
		$row->smw_title = '_MDAT';
		$row->smw_namespace = SMW_NS_PROPERTY;
		$row->smw_iw = '';
		$row->smw_subobject = '';
		$row->smw_sortkey = 'bar';
		$row->smw_sort = 'BAR';
		$row->smw_hash = 'x99w';

		$this->cache->expects( $this->once() )
			->method( 'fetch' )
			->willReturn( false );

		$this->connection->expects( $this->once() )
			->method( 'selectRow' )
			->with(
				$this->anything(),
				$this->anything(),
				[ 'smw_id' => 42 ] )
			->willReturn( $row );

		$instance = new IdEntityFinder(
			$this->store,
			$this->iteratorFactory,
			$this->idCacheManager
		);

		$this->assertEquals(
			$dataItem,
			$instance->getDataItemById( 42 )
		);
	}

	public function testNullForUnknownId() {
		$this->cache->expects( $this->once() )
			->method( 'fetch' )
			->willReturn( false );

		$this->connection->expects( $this->once() )
			->method( 'selectRow' )
			->willReturn( false );

		$instance = new IdEntityFinder(
			$this->store,
			$this->iteratorFactory,
			$this->idCacheManager
		);

		$this->assertNull(
			$instance->getDataItemById( 42 )
		);
	}

	public function testGetDataItemsFromList() {
		$expected = new DIWikiPage( 'Foo', 0, '', '' );
		$expected->setId( 42 );
		$expected->setSortKey( '...' );
		$expected->setOption( 'sort', '...' );

		$row = new \stdClass;
		$row->smw_id = 42;
		$row->smw_title = 'Foo';
		$row->smw_namespace = 0;
		$row->smw_iw = '';
		$row->smw_subobject = '';
		$row->smw_sortkey = '...';
		$row->smw_sort = '...';
		$row->smw_hash = 'x99w';

		$this->connection->expects( $this->once() )
			->method( 'select' )
			->with(
				$this->anything(),
				$this->anything(),
				[ 'smw_id' => [ 42 ] ] )
			->willReturn( [ $row ] );

		$instance = new IdEntityFinder(
			$this->store,
			new IteratorFactory(),
			$this->idCacheManager
		);

		foreach ( $instance->getDataItemsFromList( [ 42 ] ) as $value ) {
			$this->assertEquals(
				$expected,
				$value
			);
		}
	}

}
