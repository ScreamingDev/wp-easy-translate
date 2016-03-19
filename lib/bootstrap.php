<?php

define( 'EASYTRANSLATE_LIB_PATH', __DIR__ );

$composer = json_decode( file_get_contents( EASYTRANSLATE_LIB_PATH . '/../composer.json' ) );

define( 'EASYTRANSLATE_VERSION', $composer->version );

require_once __DIR__ . '/../' . $composer->config->{'vendor-dir'} . '/autoload.php';
