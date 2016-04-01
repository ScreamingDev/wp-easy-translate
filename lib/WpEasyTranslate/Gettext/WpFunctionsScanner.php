<?php

namespace WpEasyTranslate\Gettext;

use Gettext\Translation;
use Gettext\Translations;
use Gettext\Utils\PhpFunctionsScanner;

class WpFunctionsScanner extends PhpFunctionsScanner {
	public $textDomain;

	public function saveGettextFunctions( array $functions, Translations $translations, $file = '', $textDomain = '' ) {
		foreach ( $this->getFunctions() as $function ) {
			list( $name, $line, $args ) = $function;

			if ( ! isset( $args[1] ) ) {
				continue;
			}

			$name = str_replace( '__', '', $name );
			$name = str_replace( '_noop', '', $name );
			$name = ltrim( $name, '_' );

			$parser_name = 'parse' . $name;
			if ( ! method_exists( $this, $parser_name ) ) {
				continue;
			}

			$translation = $this->$parser_name( $args, $translations );

			if ( ! $translation ) {
				continue;
			}

			/** @var Translation $translation */

			$translation->addReference( $file, $line );
		}
	}

	protected function parse_e( $args, Translations $translations ) {
		return $this->parse( $args, $translations );
	}

	/**
	 * @param              $args
	 * @param Translations $translations
	 *
	 * @return \Gettext\Translation
	 */
	protected function parse( $args, Translations $translations ) {
		if ( ! isset( $args[0] ) || ! $args[0] ) {
			// skip empty translations
			return;
		}

		if ( ! isset( $args[1] ) || ! $args[1] ) {
			// skip unknown text-domains
			return;
		}

		$translation = $translations->insert( '', $args[0] );

		if ( isset( $function[3] ) ) {
			foreach ( $function[3] as $extractedComment ) {
				$translation->addExtractedComment( $extractedComment );
			}
		}


		return $translation;
	}

	protected function parse_esc_attr( $args, Translations $translations ) {
		return $this->parse( $args, $translations );
	}

	protected function parse_esc_attr_e( $args, Translations $translations ) {
		return $this->parse( $args, $translations );
	}

	protected function parse_esc_html( $args, Translations $translations ) {
		return $this->parse( $args, $translations );
	}

	protected function parse_esc_html_e( $args, Translations $translations ) {
		return $this->parse( $args, $translations );
	}

	protected function parse_n( $args, Translations $translations ) {
		if ( count( $args ) < 4 ) {
			// not enough data
			return;
		}

		$singular = [ $args[0], $args[3] ];

		$this->parse( $singular, $translations );

		$plural = [ $args[1], $args[3] ];

		$this->parse( $plural, $translations );
	}

	// todo _x
	// todo _ex
	// todo _nx

}
