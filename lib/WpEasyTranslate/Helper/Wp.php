<?php

namespace WpEasyTranslate\Helper;


class Wp {
	public static function load() {
		$path = static::get_path();

		if ( ! $path ) {
			throw new \Exception( 'Could resolve find WordPress path' );
		}

		require_once $path . DIRECTORY_SEPARATOR . 'wp-load.php';
	}

	/**
	 * Find the directory that contains the WordPress files.
	 * Defaults to the current working dir.
	 *
	 * @return string An absolute path
	 */
	public static function get_path() {
		// children
		$nodes = glob( '*/wp-load.php' );

		if ( count( $nodes ) == 1 ) {
			return dirname( current( $nodes ) );
		}

		$dir = getcwd();

		// parents
		while ( is_readable( $dir ) ) {
			if ( file_exists( "$dir/wp-load.php" ) ) {
				return $dir;
			}

			if ( file_exists( "$dir/index.php" ) ) {
				if ( $path = self::extract_subdir_path( "$dir/index.php" ) ) {
					return $path;
				}
			}

			$parent_dir = dirname( $dir );
			if ( empty( $parent_dir ) || $parent_dir === $dir ) {
				break;
			}
			$dir = $parent_dir;
		}
	}
}