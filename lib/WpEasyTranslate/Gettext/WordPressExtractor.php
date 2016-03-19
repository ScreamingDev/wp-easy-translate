<?php

namespace WpEasyTranslate\Gettext;

use Gettext\Extractors\PhpCode;
use Gettext\Translations;
use WpEasyTranslate\Gettext\WpFunctionsScanner;

class WordPressExtractor extends PhpCode {
	public static $textDomain = '';
	/**
	 * {@inheritdoc}
	 */
	public static function fromString( $string, Translations $translations = null, $file = '' ) {
		if ( $translations === null ) {
			$translations = new Translations();
		}

		$functions = new WpFunctionsScanner( $string );

		if ( self::$extractComments !== false ) {
			$functions->enableCommentsExtraction( self::$extractComments );
		}

		$functions->saveGettextFunctions( self::$functions, $translations, $file, static::$textDomain );

		return $translations;
	}
}
