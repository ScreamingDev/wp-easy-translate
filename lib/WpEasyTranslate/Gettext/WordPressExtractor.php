<?php

namespace WpEasyTranslate\Gettext;

use Gettext\Extractors\PhpCode;
use Gettext\Translations;

class WordPressExtractor extends PhpCode
{
    public static $textDomain = '';

    /**
     * {@inheritdoc}
     */
    public static function fromString($string, Translations $translations = null, $file = '')
    {
        if ($translations === null) {
            $translations = new Translations();
        }

        $functions = new WpFunctionsScanner($string);

        if (self::$extractComments !== false) {
            $functions->enableCommentsExtraction(self::$extractComments);
        }

        $functions->saveGettextFunctions(self::$functions, $translations, $file, static::$textDomain);

        return $translations;
    }

    /**
     * @param       $directory
     * @param array $exclude
     * @param array $pattern
     *
     * @return Translations
     */
    public function fromDirectory($directory, $translations = null, $exclude = ['languages'], $pattern = ['*.php', '*.phtml'])
    {
        if (!$translations) {
            $translations = new Translations();
        }

        // fetch strings
        $sourcesFinder = new \Symfony\Component\Finder\Finder();
        $sourcesFinder->files()
                      ->in($directory)
                      ->exclude($exclude);

        foreach ($pattern as $filePattern) {
            $sourcesFinder->name($filePattern);
        }

        $sourcesList = iterator_to_array($sourcesFinder->getIterator());
        $sourcesList = array_map('strval', $sourcesList);

        foreach ($sourcesList as $sourceFile) {
            $this->fromFile($sourceFile, $translations);
        }

        return $translations;
    }
}
