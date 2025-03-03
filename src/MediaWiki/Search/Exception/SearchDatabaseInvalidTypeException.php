<?php

namespace SMW\MediaWiki\Search\Exception;

use RuntimeException;

/**
 * @license GPL-2.0-or-later
 * @since 3.1
 *
 * @author mwjames
 */
class SearchDatabaseInvalidTypeException extends RuntimeException {

	/**
	 * @since  3.1
	 *
	 * @param string $type
	 */
	public function __construct( $type ) {
		parent::__construct( "$type is not a valid fallback search engine database type." );
	}

}
