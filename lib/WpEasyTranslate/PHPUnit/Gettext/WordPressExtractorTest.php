<?php
/**
 * Created by PhpStorm.
 * User: mike
 * Date: 12.06.16
 * Time: 16:45
 */

namespace Gettext;


use WpEasyTranslate\Gettext\WordPressExtractor;

class WordPressExtractorTest extends \PHPUnit_Framework_TestCase
{
    public function testItExtendsGettextGettext()
    {
        $this->assertInstanceOf('\Gettext\Extractors\PhpCode', new WordPressExtractor());
    }
}
