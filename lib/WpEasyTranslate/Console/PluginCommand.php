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
class PluginCommand extends AbstractCommand
{
    const CURRENT_PLUGIN = 'the currently active theme';

    protected function configure()
    {
        $this->addArgument(
            'plugin',
            InputArgument::REQUIRED,
            'Plugin to translate'
        );

        $this->addOption(
            'format',
            null,
            InputOption::VALUE_OPTIONAL,
            'Format to fetch translations from. Can be "csv", "json", "po" or "php".',
            'po'
        );

        parent::configure();
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        Wp::load();

        $this->verbose('WP loaded' . PHP_EOL);

        $targetPluginSlug = $input->getArgument('plugin');

        require_once ABSPATH . '/wp-admin/includes/plugin.php';

        $targetPluginDir = $this->resolvePluginDir($targetPluginSlug);

        $targetPluginRealPath = WP_CONTENT_DIR . '/plugins/' . $targetPluginDir;

        if (!is_readable($targetPluginRealPath)) {
            throw new \DomainException('Path to plugin can not be resolved.');
        }

        // resolve readable name
        $name = $targetPluginSlug;

        $allPlugins = get_plugins();
        $pluginData = $allPlugins[$targetPluginDir];


        if (isset($pluginData['Name']) && $pluginData['Name']) {
            $name = $pluginData['Name'];
        }

        $this->verbose('Translating ' . $name);

        // resolve theme text domain
        $textDomain = $name;
        if (isset($pluginData['TextDomain']) && $pluginData['TextDomain']) {
            $textDomain = $pluginData['TextDomain'];
        }

        $this->verbose(' (TextDomain: ' . $textDomain . ')' . PHP_EOL);

        $langDir = $this->resolveLanguagesDir($pluginData);

        $langPath = dirname($targetPluginRealPath) . '/' . $langDir;

        if ($langPath && !is_dir($langPath)) {
            $this->debug('Creating ' . $langPath . PHP_EOL);

            // not yet translated => create translation
            mkdir($langPath, 0777, true);
        }

        // fetch strings
        $extractor = new WordPressExtractor();
        $extractor::$extractComments = true;
        $extractor::$textDomain = $textDomain;
        $translatable = $extractor->fromDirectory(dirname($targetPluginRealPath), null, $langDir);

        $translatable->setDomain($textDomain);
        $translatable->ksort();

        $this->sanitizeTranslation($translatable, $textDomain, dirname($targetPluginRealPath));

        // create skeleton
        // file name is same as the slug which is a common way if you look at twenty* themes.
        $skeletonBasename = $textDomain . '.' . $input->getOption('format');

        if ('po' == $input->getOption('format')) {
            // po templates have the pot extension in the filename
            $skeletonBasename .= 't';
        }

        // merge with existing template
        $skeletonPath = $langPath . '/' . $skeletonBasename;

        $skeletonTranslation = new Translations();
        if (file_exists($skeletonPath)) {
            $extractMethod = $this->getExtractMethod( $input );

            /** @var Translations $skeletonTranslation */
            $skeletonTranslation = call_user_func(['\\Gettext\\Translations', $extractMethod], [$skeletonPath]);

            $this->sanitizeTranslation($skeletonTranslation, $textDomain, dirname($targetPluginRealPath));
        }

        // todo B fetch and merge translations from po file

        $skeletonTranslation->mergeWith($translatable, $translatable::MERGE_ADD | $translatable::MERGE_REFERENCES);

        $this->writeTranslation($input, $output, $skeletonPath, $skeletonTranslation);

        $this->updateTranslations($input, $output, $pluginData, $skeletonTranslation, $targetPluginSlug, $textDomain);
    }

	/**
	 * @param InputInterface $input
	 *
	 * @return string
	 */
	protected function getExtractMethod( InputInterface $input ) {
		$format = ucfirst( $input->getOption( 'format' ) );

		if ($format == 'Csv') {
			$format .= 'Dictionary';
		}

		return 'from' . $format . 'File';
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
    )
    {
        $morphMethod = $this->getMorphMethod($input);

        file_put_contents($skeletonPath, $skeletonTranslation->$morphMethod());

        $this->debug('Wrote ' . basename($skeletonPath) . ' file.' . PHP_EOL);
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
            'po' => 'toPoString',
            'php' => 'toPhpArrayString',
	        'csv' => 'toCsvDictionaryString'
        ];

        if (!isset($morphMapping[$input->getOption('format')])) {
            throw new \InvalidArgumentException(
                'Unknown format: ' . $input->getOption('format')
            );
        }

        $morphMethod = $morphMapping[$input->getOption('format')];

        return $morphMethod;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param \WP_Theme $targetTheme
     * @param Translations $skeletonTranslation
     * @param                 $textDomain
     */
    protected function updateTranslations(
        InputInterface $input,
        OutputInterface $output,
        $pluginData,
        Translations $skeletonTranslation,
        $targetPluginSlug,
        $textDomain
    )
    {
        $this->verbose('Updating translations ...' . PHP_EOL);

        $targetPluginDir = $this->resolvePluginDir($targetPluginSlug);

        $targetPluginRealPath = WP_CONTENT_DIR . '/plugins/' . $targetPluginDir;

        $langDir = $this->resolveLanguagesDir($pluginData);

        $langPath = dirname($targetPluginRealPath) . '/' . $langDir;

        $parseMethod = $this->getExtractMethod( $input );

        foreach (glob($langPath . '/*.' . $input->getOption('format')) as $langFile) {
            $lang = basename($langFile, '.' . $input->getOption('format'));

            if ($textDomain == $lang) {
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

            $moFile = dirname($langFile) . '/' . $lang . '.mo';

            $targetTranslations->toMoFile($moFile);

            $this->debug('Wrote ' . basename($moFile) . PHP_EOL);
        }
    }

    /**
     * @param $pluginData
     * @return array
     */
    protected function resolveLanguagesDir($pluginData)
    {
        if (isset($pluginData['DomainPath']) && $pluginData['DomainPath']) {
            return $pluginData['DomainPath'];
        }

        return 'languages';
    }

    /**
     * @param $targetPluginSlug
     * @return mixed
     */
    protected function resolvePluginDir($targetPluginSlug)
    {
        $plugins = Wp::getPlugins();

        if (!array_key_exists($targetPluginSlug, $plugins)) {
            throw new \DomainException(
                'Plugin ' . $targetPluginSlug . ' does not exist.'
            );
        }

        $targetPluginDir = $plugins[$targetPluginSlug];
        return $targetPluginDir;
    }
}