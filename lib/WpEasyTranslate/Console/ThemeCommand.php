<?php

namespace WpEasyTranslate\Console;


use Gettext\Generators\PhpArray;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use WpEasyTranslate\Gettext\WordPressExtractor;
use WpEasyTranslate\Helper\Wp;
use WpEasyTranslate\Helper\WpTheme;

/**
 * Generate translations for a theme.
 *
 * @package WpEasyTranslate\Console
 */
class ThemeCommand extends AbstractCommand
{
    const CURRENT_THEME = 'the currently active theme';

    protected function configure()
    {
        $this->addArgument(
            'theme',
            InputArgument::OPTIONAL,
            'Theme to translate',
            self::CURRENT_THEME
        );

        $this->addOption(
            'format',
            null,
            InputOption::VALUE_OPTIONAL,
            'Format to fetch translations from. Can be "json", "po" or "php".',
            'po'
        );

        parent::configure();
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        Wp::load();

        $themes = wp_get_themes();

        $morphMapping = [
            'json' => 'toJsonDictionaryFile',
            'po'   => 'toPoFile',
            'php'  => 'toPhpArrayFile',
        ];

        if ( ! isset( $morphMapping[$input->getOption('format')] )) {
            throw new \InvalidArgumentException(
                'Unknown format: '.$input->getOption('format')
            );
        }

        $morphMethod = $morphMapping[$input->getOption('format')];

        foreach ($themes as $slug => $theme) {
            /** @var \WP_Theme $theme */

            // resolve readable name
            $name = $slug;

            if ($theme->get('Name')) {
                $name = $theme->get('Name');
            }

            $this->verbose('Checking '.$name);

            // resolve theme text domain
            $textDomain = $name;
            if ($theme->get('TextDomain')) {
                $textDomain = $theme->get('TextDomain');
            }

            $this->verbose(' (TextDomain: '.$textDomain.')'.PHP_EOL);

            $langPath = WpTheme::resolveLanguagesPath($theme);

            // fetch strings
            $extractor                   = new WordPressExtractor();
            $extractor::$extractComments = true;
            $extractor::$textDomain      = $textDomain;
            $translatable                = $extractor->fromDirectory($theme->get_stylesheet_directory());

            // todo B fetch and merge translations from po file

            $translatable->setDomain($textDomain);
            $translatable->ksort();

            $this->updateTranslations($input, $morphMethod, $translatable, $langPath, $textDomain);
        }
    }

    /**
     * @param InputInterface $input
     * @param                $morphMethod
     * @param                $translatable
     * @param                $langPath
     * @param                $textDomain
     */
    protected function updateTranslations(
        InputInterface $input,
        $morphMethod,
        $translatable,
        $langPath,
        $textDomain
    ) {
        $translatable->$morphMethod($langPath.'/empty.'.$input->getOption('format'));

        // iterate over languages
        foreach (glob($langPath.'/*.'.$input->getOption('format')) as $lang_file) {
            $lang = basename($lang_file, '.'.$input->getOption('format'));

            if ('empty' == $lang) {
                continue;
            }

            // merge and push in php array
            $lang_php = $langPath.DIRECTORY_SEPARATOR.$lang.'.'.$input->getOption('format');
            if (file_exists($lang_php)) {
                $current_translation = \Gettext\Extractors\PhpArray::fromFile($lang_file);
                $current_translation->mergeWith($translatable);
            }

            $current_translation->setDomain($textDomain);
            $current_translation->ksort();

            // json
            $array = PhpArray::toArray($current_translation);

            $values = current($array);
            if (array_key_exists('', $values)) {
                unset( $values[''] );
            }

            $current_translation->$morphMethod($langPath.DIRECTORY_SEPARATOR.$lang.'.'.$input->getOption('format'));

            // merge and push in mo file
            $current_translation->toMoFile($langPath.DIRECTORY_SEPARATOR.$lang.'.mo');
            $this->verbose('    Updated '.$lang.PHP_EOL);
        }
    }
}