<?php

namespace  WpEasyTranslate\Gettext;

use Gettext\Translations;
use Gettext\Utils\PhpFunctionsScanner;

class WpFunctionsScanner extends PhpFunctionsScanner {
	public $textDomain;

	public function saveGettextFunctions( array $functions, Translations $translations, $file = '', $textDomain = '' ) {
		foreach ( $this->getFunctions() as $function ) {
			list( $name, $line, $args ) = $function;

			if ( ! isset( $functions[ $name ] ) ) {
				continue;
			}

			if ( isset( $args[1] ) && $textDomain != $args[1] ) {
				continue;
			}

			$translation = null;

			switch ( $functions[ $name ] ) {
				case '__':
					if ( ! isset( $args[0] ) ) {
						continue 2;
					}
					$original = $args[0];
					if ( $original !== '' ) {
						$translation = $translations->insert( '', $original );
					}
					break;

				case 'n__':
					if ( ! isset( $args[1] ) ) {
						continue 2;
					}
					$original = $args[0];
					$plural   = $args[1];
					if ( $original !== '' ) {
						$translation = $translations->insert( '', $original, $plural );
					}
					break;

				case 'p__':
					if ( ! isset( $args[1] ) ) {
						continue 2;
					}
					$context  = $args[0];
					$original = $args[1];
					if ( $original !== '' ) {
						$translation = $translations->insert( $context, $original );
					}
					break;

				default:
					throw new \Exception( 'Not valid functions' );
			}

			if ( isset( $translation ) ) {
				$translation->addReference( $file, $line );
				if ( isset( $function[3] ) ) {
					foreach ( $function[3] as $extractedComment ) {
						$translation->addExtractedComment( $extractedComment );
					}
				}
			}
		}
	}
}
