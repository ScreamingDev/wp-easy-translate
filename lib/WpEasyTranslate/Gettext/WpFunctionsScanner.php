<?php

namespace WpEasyTranslate\Gettext;

use Gettext\Translation;
use Gettext\Translations;
use Gettext\Utils\PhpFunctionsScanner;

class WpFunctionsScanner extends PhpFunctionsScanner
{
    public $textDomain;

    public function saveGettextFunctions(array $functions, Translations $translations, $file = '', $textDomain = '')
    {
        foreach ($this->getFunctions() as $function) {
            list( $name, $line, $args ) = $function;

            if ( ! isset( $args[1] )) {
                continue;
            }

            $name = str_replace('_', ' ', $name);
            $name = ucwords($name);
            $name = str_replace(' ', '', $name);

            $parser_name = 'parse'.$name;
            if ( ! method_exists($this, $parser_name)) {
                continue;
            }

            $translation = $this->$parser_name($args, $translations);

            if ( ! $translation) {
                continue;
            }

            /** @var Translation $translation */

            $translation->addReference($file, $line);
        }
    }

    protected function parseE($args, Translations $translations)
    {
        return $this->parse($args, $translations);
    }

    protected function parseNNoop($args, Translations $translations)
    {
        if (count($args) < 2) {
            throw new \DomainException(
                'Invalid num of args for _n_noop: '.PHP_EOL.implode(PHP_EOL, $args)
            );
        }
        
        $translations->insert('', $args[0], $args[1]);
    }

    /**
     * @param              $args
     * @param Translations $translations
     *
     * @return \Gettext\Translation
     */
    protected function parse($args, Translations $translations)
    {
        if ( ! isset( $args[0] ) || ! $args[0]) {
            // skip empty translations
            return;
        }

        if ( ! isset( $args[1] ) || ! $args[1]) {
            // skip unknown text-domains
            return;
        }

        $translation = $translations->insert('', $args[0]);

        if (isset( $function[3] )) {
            foreach ($function[3] as $extractedComment) {
                $translation->addExtractedComment($extractedComment);
            }
        }


        return $translation;
    }

    protected function parseEscAttr($args, Translations $translations)
    {
        return $this->parse($args, $translations);
    }

    protected function parseEscAttrE($args, Translations $translations)
    {
        return $this->parse($args, $translations);
    }

    protected function parseEscAttrX($args, Translations $translations)
    {
        if (count($args) != 3) {
            throw new \DomainException(
                'Invalid num of args for esc_attr_x: '.PHP_EOL.implode(PHP_EOL, $args)
            );
        }

        $translations->insert($args[1], $args[0]);
    }

    protected function parseEscHtml($args, Translations $translations)
    {
        return $this->parse($args, $translations);
    }

    protected function parseEscHtmlE($args, Translations $translations)
    {
        return $this->parse($args, $translations);
    }

    /**
     * esc_html_x
     *
     * @param              $args
     * @param Translations $translations
     */
    protected function parseEscHtmlX($args, Translations $translations)
    {
        if (count($args) != 3) {
            throw new \DomainException(
                'Invalid num of args for esc_attr_x: '.PHP_EOL.implode(PHP_EOL, $args)
            );
        }

        $translations->insert($args[1], $args[0]);
    }

    protected function parseEx($args, Translations $translations)
    {
        if (count($args) < 3) {
            throw new \DomainException(
                'Invalid num of args for _ex: '.PHP_EOL.implode(PHP_EOL, $args)
            );
        }

        $translations->insert($args[1], $args[0]);
    }

    protected function parseN($args, Translations $translations)
    {
        if (count($args) < 4) {
            // not enough data
            throw new \DomainException('Not enough data for ngettext translation: '.var_export($args, true));

            return;
        }

        $translations->insert('', $args[0], $args[1]);
    }

    protected function parseTranslate($args, Translations $translations)
    {
        $this->parse($args, $translations);
    }

    protected function parseTranslateWithGettextContext($args, Translations $translations)
    {
        if (count($args) < 2) {
            throw new \DomainException(
                'Invalid num of args for translate_with_gettext_context: '.PHP_EOL.implode(PHP_EOL, $args)
            );
        }

        $translations->insert($args[1], $args[0]);
    }

    protected function parseX($args, Translations $translations)
    {
        if (count($args) < 3) {
            throw new \DomainException(
                'Invalid num of args for _x: '.PHP_EOL.implode(PHP_EOL, $args)
            );
        }

        $translations->insert($args[1], $args[0]);
    }

    protected function parseNx($args, Translations $translations)
    {
        if (count($args) < 4) {
            throw new \DomainException(
                'Invalid num of args for _nx: '.PHP_EOL.implode(PHP_EOL, $args)
            );
        }

        $translations->insert($args[3], $args[0], $args[1]);
    }

    protected function parseNxNoop($args, Translations $translations)
    {
        if (count($args) < 3) {
            throw new \DomainException(
                'Invalid num of args for _nx: '.PHP_EOL.implode(PHP_EOL, $args)
            );
        }

        $translations->insert($args[2], $args[0], $args[1]);
    }


    /**
     * It does not complain about missing optional arguments.
     */
    public function testItDoesNotComplainAboutMissingOptionalArguments()
    {
        $this->markTestIncomplete('todo');
    }
}
