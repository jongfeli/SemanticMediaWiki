<?php

namespace SMW\MediaWiki\Content;

use Content;
use MediaWiki\Content\Renderer\ContentParseParams;
use MediaWiki\Content\Transform\PreSaveTransformParams;
use JsonContentHandler;
use SMW\ParserData;
use Title;
use ParserOutput;

/**
 * @license GNU GPL v2+
 * @since 3.0
 *
 * @author mwjames
 */
class SchemaContentHandler extends JsonContentHandler {

	public function __construct() {
		parent::__construct( CONTENT_MODEL_SMW_SCHEMA, [ CONTENT_FORMAT_JSON ] );
	}

	/**
	 * Returns true, because wikitext supports caching using the
	 * ParserCache mechanism.
	 *
	 * @since 1.21
	 *
	 * @return bool Always true.
	 *
	 * @see ContentHandler::isParserCacheSupported
	 */
	public function isParserCacheSupported() {
		return true;
	}

	/**
	 * @since 3.0
	 *
	 * {@inheritDoc}
	 */
	protected function getContentClass() {
		return SchemaContent::class;
	}

	/**
	 * @since 3.0
	 *
	 * {@inheritDoc}
	 */
	public function supportsSections() {
		return false;
	}

	/**
	 * @since 3.0
	 *
	 * {@inheritDoc}
	 */
	public function supportsCategories() {
		return false;
	}

	/**
	 * @since 3.0
	 *
	 * {@inheritDoc}
	 */
	public function supportsRedirects() {
		return false;
	}

	/**
	 *
	 * {@inheritDoc}
	 */
	public function preSaveTransform( Content $content, PreSaveTransformParams $pstParams ): Content {
		return $content->preSaveTransform(
			$pstParams->getPage(),
			$pstParams->getUser(),
			$pstParams->getParserOptions()
		);
	}

	/**
	 *
	 * {@inheritDoc}
	 */
	protected function fillParserOutput(
		Content $content,
		ContentParseParams $cpoParams,
		ParserOutput &$output
	) {
		$title = Title::castFromPageReference( $cpoParams->getPage() );

		if ( !$cpoParams->getGenerateHtml() || !$content->isValid() ) {
			return;
		}

		$content->initServices();
		$contentFormatter = $content->getContentFormatter();
		$schemaFactory = $content->getSchemaFactory();

		$output->addModuleStyles(
			$contentFormatter->getModuleStyles()
		);

		$output->addModules(
			$contentFormatter->getModules()
		);

		$parserData = new ParserData( $title, $output );
		$schema = null;

		$contentFormatter->isYaml(
			$content->isYaml()
		);

		$content->setTitlePrefix( $title );

		try {
			$schema = $schemaFactory->newSchema(
				$title->getDBKey(),
				$content->toJson()
			);
		} catch ( SchemaTypeNotFoundException $e ) {

			$contentFormatter->setUnknownType(
				$e->getType()
			);

			$output->setText(
				$contentFormatter->getText( $content->getText() )
			);

			$parserData->addError(
				[ [ 'smw-schema-error-type-unknown', $e->getType() ] ]
			);

			$parserData->copyToParserOutput();
		}

		if ( $schema === null ) {
			return;
		}

		$output->setIndicator(
			'mw-helplink',
			$contentFormatter->getHelpLink( $schema )
		);

		$errors = $schemaFactory->newSchemaValidator()->validate(
			$schema
		);

		foreach ( $errors as $error ) {
			if ( isset( $error['property'] ) && isset( $error['message'] ) ) {

				if ( $error['property'] === 'title_prefix' ) {
					if ( isset( $error['enum'] ) ) {
						$group = end( $error['enum'] );
					} elseif ( isset( $error['const'] ) ) {
						$group = $error['const'];
					} else {
						continue;
					}

					$error['message'] = Message::get( [ 'smw-schema-error-title-prefix', $group ] );
				}

				$parserData->addError(
					[ [ 'smw-schema-error-violation', $error['property'], $error['message'] ] ]
				);
			} else {
				$parserData->addError( (array)$error );
			}
		}

		$contentFormatter->setType(
			$schemaFactory->getType( $schema->get( 'type' ) )
		);

		$output->setText(
			$contentFormatter->getText( $content->getText(), $schema, $errors )
		);

		$parserData->copyToParserOutput();
	}
}
