<?php

namespace Gettext\WordPressExtractor;


use Gettext\Translations;
use WpEasyTranslate\Gettext\WordPressExtractor;
use WpEasyTranslate\PHPUnit\TestCase;

class WpFunctionsScanner extends TestCase
{
    /**
     * All known translation functions are covered.
     */
    public function testAllKnownTranslationFunctionsAreCovered()
    {
        $extractor = new WordPressExtractor();

        $translations = new Translations();
        $translations->setDomain('test');

        $translations = $extractor->fromDirectory($this->getResourcesPath().'commonSources', $translations);

        // file_put_contents($this->getResourcesPath() . 'commonSources.php', var_export($arrayCopy, true));

        $poContent = $translations->toPoString();

        // strip base path for better comparison
        $poContent = str_replace($this->getResourcesPath(), '', $poContent);

        $this->assertContains('"translate"', $poContent);
        $this->assertContains('"translate_with_gettext_context"', $poContent);

        $this->assertContains('"__"', $poContent);
        $this->assertContains('"_x"', $poContent);
        $this->assertContains('"_e"', $poContent);
        $this->assertContains('"_ex"', $poContent);

        $this->assertContains('"esc_attr__"', $poContent);
        $this->assertContains('"esc_attr_e"', $poContent);
        $this->assertContains('"esc_attr_x"', $poContent);

        $this->assertContains('"esc_html__"', $poContent);
        $this->assertContains('"esc_html_e"', $poContent);
        $this->assertContains('"esc_html_x"', $poContent);

        $this->assertContains('"_n-single"', $poContent);
        $this->assertContains('"_n-plural"', $poContent);

        $this->assertContains('"_nx-context"', $poContent);
        $this->assertContains('"_nx-single"', $poContent);
        $this->assertContains('"_nx-plural"', $poContent);

        $this->assertContains('"_n_noop-singular"', $poContent);
        $this->assertContains('"_n_noop-plural"', $poContent);

        $this->assertContains('"_nx_noop-context"', $poContent);
        $this->assertContains('"_nx_noop-singular"', $poContent);
        $this->assertContains('"_nx_noop-plural"', $poContent);
    }

    /**
     * Plural translations functions are correctly parsed.
     */
    public function testPluralTranslationsFunctionsAreCorrectlyParsed()
    {
        $this->markTestIncomplete('todo');
    }
}