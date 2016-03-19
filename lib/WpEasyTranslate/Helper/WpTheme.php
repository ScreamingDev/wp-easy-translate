<?php

namespace WpEasyTranslate\Helper;


class WpTheme {
	public static function resolveLanguagesPath( \WP_Theme $theme ) {

		// as taken from wp-includes/class-wp-theme.php:1114
		$path = $theme->get_template_directory();

		if ( $domainpath = $theme->get( 'DomainPath' ) ) {
			$path .= $domainpath;
		} else {
			$path .= '/languages';
		}

		return $path;
	}
}