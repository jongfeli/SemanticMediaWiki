<?php

namespace SMW\Indicator\IndicatorProviders;

use SMW\Indicator\IndicatorProvider;

/**
 * Identifies indicator providers that are assumed to cause relative expensive
 * examination or validation tasks therefore should only be carried out after the
 * page has been rendered.
 *
 * @license GPL-2.0-or-later
 * @since 3.2
 *
 * @author mwjames
 */
interface DeferrableIndicatorProvider extends IndicatorProvider {

	/**
	 * @since 3.2
	 *
	 * @param bool $deferredMode
	 */
	public function setDeferredMode( bool $deferredMode );

	/**
	 * Indicates that the indicator is running in a deferred mode hereby allowing
	 * to resolve expensive tasks.
	 *
	 * @since 3.2
	 *
	 * @return bool
	 */
	public function isDeferredMode(): bool;

}
