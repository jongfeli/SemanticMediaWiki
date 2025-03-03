<?php

namespace SMW\MediaWiki\Hooks;

use Skin;
use SMW\MediaWiki\HookListener;
use SMW\NamespaceExaminer;
use SMW\OptionsAwareTrait;
use SMWInfolink as Infolink;
use SpecialPage;
use Title;

/**
 * Called at the end of Skin::buildSidebar().
 *
 * @see https://www.mediawiki.org/wiki/Manual:Hooks/SidebarBeforeOutput
 *
 * @license GPL-2.0-or-later
 *
 * @author StarHeartHunt
 */
class SidebarBeforeOutput implements HookListener {

	use OptionsAwareTrait;

	/**
	 * @var NamespaceExaminer
	 */
	private $namespaceExaminer;

	/**
	 *
	 * @param NamespaceExaminer $namespaceExaminer
	 */
	public function __construct( NamespaceExaminer $namespaceExaminer ) {
		$this->namespaceExaminer = $namespaceExaminer;
	}

	/**
	 *
	 * @param $skin
	 * @param &$sidebar
	 *
	 * @return bool
	 */
	public function process( $skin, &$sidebar ) {
		$title = $skin->getTitle();

		if ( $this->canProcess( $title, $skin ) ) {
			$this->performUpdate( $title, $skin, $sidebar );
		}

		return true;
	}

	private function canProcess( Title $title, Skin $skin ) {
		if ( $title->isSpecialPage() || !$this->namespaceExaminer->isSemanticEnabled( $title->getNamespace() ) ) {
			return false;
		}

		if ( !$skin->getOutput()->isArticle() || !$this->isFlagSet( 'smwgBrowseFeatures', SMW_BROWSE_TLINK ) ) {
			return false;
		}

		return true;
	}

	private function performUpdate( Title $title, Skin $skin, &$sidebar ) {
		$link = Infolink::encodeParameters(
			[
				$title->getPrefixedDBkey()
			],
			true
		);

		$sidebar["TOOLBOX"]['smwbrowselink'] = [
			'text' => $skin->msg( 'smw_browselink' )->text(),
			'href' => SpecialPage::getTitleFor( 'Browse', ':' . $link )->getLocalUrl(),
			'icon' => 'database',
			'id'   => 't-smwbrowselink',
			'rel'  => 'search'
		];
	}

}
