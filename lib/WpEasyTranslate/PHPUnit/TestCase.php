<?php

namespace WpEasyTranslate\PHPUnit;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    public static function getBaseDir()
    {
        return __DIR__;
    }

    public static function getResourcesPath()
    {
        return static::getBaseDir() . '/resources/';
    }

    public static function getResource($fileName)
    {
        return file_get_contents(static::getResourcesPath() . $fileName);
    }
}