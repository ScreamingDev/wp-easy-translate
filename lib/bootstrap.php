<?php

define( 'EASYTRANSLATE_LIB_PATH', __DIR__ );

$composerDir = EASYTRANSLATE_LIB_PATH . '/../';
if ( file_exists( __DIR__ . '/../../../../composer.json' ) ) {
	$composerDir = __DIR__ . '/../../../../';
}

$parentComposer = json_decode( file_get_contents( $composerDir . 'composer.json' ) );

if ( ! isset( $parentComposer->config->{'vendor-dir'} ) || ! $parentComposer->config->{'vendor-dir'} ) {
	$parentComposer->config->{'vendor-dir'} = 'vendor';
}

$ownComposer = json_decode( file_get_contents( __DIR__ . '/../composer.json' ) );

define( 'EASYTRANSLATE_VERSION', $ownComposer->version );

// highest composer.json

require_once $composerDir . $parentComposer->config->{'vendor-dir'} . '/autoload.php';
