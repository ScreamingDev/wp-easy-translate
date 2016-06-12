<?php

namespace WpEasyTranslate\Console;


use Gettext\Translation;
use Gettext\Translations;
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

        $targetTheme     = null;
        $targetThemeSlug = $input->getArgument('theme');

        if (static::CURRENT_THEME == $input->getArgument('theme')) {
            // no user chosen theme: load current

            /** @var \WP_Theme $targetTheme */
            $targetTheme     = wp_get_theme();
            $targetThemeSlug = $targetTheme->get_stylesheet();
        }

        if ( ! $targetTheme) {
            // load user chosen theme
            $targetTheme = wp_get_theme($targetThemeSlug);
        }

        if ( ! $targetTheme->exists()) {
            throw new \DomainException('Theme '.$targetThemeSlug.' does not exist.');
        }

        if ($output->isDebug()) {
            $output->writeln('Theme: '.$targetTheme->get_stylesheet());
        }


        $morphMethod = $this->getMorphMethod($input);


        // resolve readable name
        $name = $targetThemeSlug;

        if ($targetTheme->get('Name')) {
            $name = $targetTheme->get('Name');
        }

        $this->verbose('Checking '.$name);

        // resolve theme text domain
        $textDomain = $name;
        if ($targetTheme->get('TextDomain')) {
            $textDomain = $targetTheme->get('TextDomain');
        }

        $this->verbose(' (TextDomain: '.$textDomain.')'.PHP_EOL);

        $langPath = WpTheme::resolveLanguagesPath($targetTheme);


        // fetch strings
        $extractor                   = new WordPressExtractor();
        $extractor::$extractComments = true;
        $extractor::$textDomain      = $textDomain;
        $translatable                = $extractor->fromDirectory($targetTheme->get_stylesheet_directory());

        // todo B fetch and merge translations from po file

        $translatable->setDomain($textDomain);
        $translatable->ksort();

        // skeleton created
        // file name is same as the slug which is a common way if you look at twenty* themes.
        $skeletonBasename = $targetThemeSlug.'.'.$input->getOption('format');

        if ('po' == $input->getOption('format')) {
            // po templates have the pot extension in the filename
            $skeletonBasename .= 't';
        }

        // merge with existing template
        $skeletonPath  = $langPath.'/'.$skeletonBasename;
        $extractMethod = 'from'.ucfirst($input->getOption('format')).'File';

        /** @var Translations $skeletonTranslation */
        $skeletonTranslation = call_user_func(['\\Gettext\\Translations', $extractMethod], [$skeletonPath]);

        $this->sanitizeTranslation($translatable, $textDomain, $targetTheme->get_stylesheet_directory());

        $skeletonTranslation->mergeWith(
            $translatable,
            $translatable::MERGE_ADD | $translatable::MERGE_REFERENCES
        );

        $this->writeTranslation($input, $output, $skeletonPath, $skeletonTranslation);

        $this->updateTranslations($input, $output, $targetTheme, $skeletonTranslation, $textDomain);
    }

    /**
     * @param InputInterface $input
     *
     * @return mixed
     */
    protected function getMorphMethod(InputInterface $input)
    {
        $morphMapping = [
            'json' => 'toJsonDictionaryString',
            'po'   => 'toPoString',
            'php'  => 'toPhpArrayString',
        ];

        if ( ! isset( $morphMapping[$input->getOption('format')] )) {
            throw new \InvalidArgumentException(
                'Unknown format: '.$input->getOption('format')
            );
        }

        $morphMethod = $morphMapping[$input->getOption('format')];

        return $morphMethod;
    }

    private function sanitizeTranslation($translatable, $textDomain, $basePath)
    {
        foreach ($translatable as $key => $translation) {
            /** @var Translation $translation */

            // cut off the base path
            $references = $translation->getReferences();
            $translation->deleteReferences();

            foreach ($references as $reference) {
                $filename = str_replace($basePath, '', $reference[0]);
                $filename = ltrim($filename, '/');

                $translation->addReference($filename, $reference[1]);
            }
        }
    }

    private function writeTranslation(
        InputInterface $input,
        OutputInterface $output,
        $skeletonPath,
        Translations $skeletonTranslation
    ) {
        $morphMethod = $this->getMorphMethod($input);

        file_put_contents($skeletonPath, $skeletonTranslation->$morphMethod());

        $this->debug('Wrote '.basename($skeletonPath).' file.'.PHP_EOL);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param \WP_Theme       $targetTheme
     * @param Translations    $skeletonTranslation
     * @param                 $textDomain
     */
    protected function updateTranslations(
        InputInterface $input,
        OutputInterface $output,
        \WP_Theme $targetTheme,
        Translations $skeletonTranslation,
        $textDomain
    ) {
        $this->verbose('Updating translations ...' . PHP_EOL);

        $langPath = WpTheme::resolveLanguagesPath($targetTheme);
        $parseMethod = 'from' . ucfirst($input->getOption('format')) . 'File';

        foreach (glob($langPath.'/*.'.$input->getOption('format')) as $langFile) {
            $lang = basename($langFile, '.'.$input->getOption('format'));

            if ($targetTheme->get_stylesheet() == $lang) {
                continue;
            }

            $this->debug('Updating ' . $lang . PHP_EOL);

            /** @var Translations $targetTranslations */
            $targetTranslations = call_user_func(['\\Gettext\\Translations', $parseMethod], [$langFile]);

            $targetTranslations->mergeWith(
                $skeletonTranslation,
                $targetTranslations::MERGE_ADD | $targetTranslations::MERGE_REFERENCES
            );

            $this->writeTranslation($input, $output, $langFile, $targetTranslations);
        }
    }
}