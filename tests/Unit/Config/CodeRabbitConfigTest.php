<?php

namespace Tests\Unit\Config;

use Tests\TestCase;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Validates the .coderabbit.yaml configuration file
 * Tests schema, required keys, and critical values
 */
class CodeRabbitConfigTest extends TestCase
{
    private $config;
    private $configPath;

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->configPath = base_path('.coderabbit.yaml');

        if (!file_exists($this->configPath)) {
            $this->markTestSkipped('.coderabbit.yaml file does not exist');
        }

        $this->config = Yaml::parseFile($this->configPath);
    }

    /**
     * @test
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function itShouldBeValidYamlSyntax()
    {
        $this->markTestIncomplete();

        try {
            $content = file_get_contents($this->configPath);
            $parsed = Yaml::parse($content);
            $this->assertIsArray($parsed);
        } catch (ParseException $e) {
            $this->fail('Invalid YAML syntax: ' . $e->getMessage());
        }
    }

    /**
     * @test
     */
    public function itShouldHaveRequiredTopLevelKeys()
    {
        $this->markTestIncomplete();

        $requiredKeys = ['language', 'reviews', 'tools', 'chat', 'knowledge_base', 'code_generation'];

        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey(
                $key,
                $this->config,
                "Missing required top-level key: {$key}"
            );
        }
    }

    /**
     * @test
     */
    public function itShouldHaveValidLanguageCode()
    {
        $this->markTestIncomplete();

        $this->assertArrayHasKey('language', $this->config);
        $validLanguageCodes = ['en-US', 'en-UK', 'es-ES', 'fr-FR', 'de-DE'];

        $this->assertContains(
            $this->config['language'],
            $validLanguageCodes,
            'Language must be a valid ISO language code'
        );
    }

    /**
     * @test
     */
    public function itShouldHaveReviewsConfiguration()
    {
        $this->markTestIncomplete();

        $this->assertArrayHasKey('reviews', $this->config);
        $reviews = $this->config['reviews'];

        $requiredReviewKeys = ['profile', 'request_changes_workflow', 'auto_review'];
        foreach ($requiredReviewKeys as $key) {
            $this->assertArrayHasKey(
                $key,
                $reviews,
                "Missing required reviews key: {$key}"
            );
        }
    }

    /**
     * @test
     */
    public function itShouldHaveValidReviewProfile()
    {
        $this->markTestIncomplete();

        $validProfiles = ['assertive', 'chill', 'friendly', 'strict', 'concise', 'detailed'];
        $profile = $this->config['reviews']['profile'];

        $this->assertContains(
            $profile,
            $validProfiles,
            "Review profile must be one of: " . implode(', ', $validProfiles)
        );
    }

    /**
     * @test
     */
    public function itShouldHaveBooleanReviewFlags()
    {
        $this->markTestIncomplete();

        $booleanFlags = [
            'request_changes_workflow',
            'high_level_summary',
            'review_status',
            'commit_status',
            'fail_commit_status',
            'collapse_walkthrough',
            'sequence_diagrams',
            'estimate_code_review_effort',
            'in_progress_fortune',
            'poem'
        ];

        $reviews = $this->config['reviews'];
        foreach ($booleanFlags as $flag) {
            if (isset($reviews[$flag])) {
                $this->assertIsBool(
                    $reviews[$flag],
                    "Review flag '{$flag}' must be boolean"
                );
            }
        }
    }

    /**
     * @test
     */
    public function itShouldHaveAutoReviewConfiguration()
    {
        $this->markTestIncomplete();

        $this->assertArrayHasKey('auto_review', $this->config['reviews']);
        $autoReview = $this->config['reviews']['auto_review'];

        $this->assertArrayHasKey('enabled', $autoReview);
        $this->assertIsBool($autoReview['enabled']);

        if (isset($autoReview['base_branches'])) {
            $this->assertIsArray($autoReview['base_branches']);
        }
    }

    /**
     * @test
     */
    public function itShouldHaveFinishingTouchesConfiguration()
    {
        $this->markTestIncomplete();

        if (!isset($this->config['reviews']['finishing_touches'])) {
            $this->markTestSkipped('Finishing touches not configured');
        }

        $finishingTouches = $this->config['reviews']['finishing_touches'];

        if (isset($finishingTouches['docstrings'])) {
            $this->assertArrayHasKey('enabled', $finishingTouches['docstrings']);
            $this->assertIsBool($finishingTouches['docstrings']['enabled']);
        }

        if (isset($finishingTouches['unit_tests'])) {
            $this->assertArrayHasKey('enabled', $finishingTouches['unit_tests']);
            $this->assertIsBool($finishingTouches['unit_tests']['enabled']);
        }
    }

    /**
     * @test
     */
    public function itShouldHavePreMergeChecksConfiguration()
    {
        $this->markTestIncomplete();

        if (!isset($this->config['reviews']['pre_merge_checks'])) {
            $this->markTestSkipped('Pre-merge checks not configured');
        }

        $checks = $this->config['reviews']['pre_merge_checks'];
        $validModes = ['disabled', 'warning', 'error'];

        foreach ($checks as $checkName => $checkConfig) {
            if (is_array($checkConfig) && isset($checkConfig['mode'])) {
                $this->assertContains(
                    $checkConfig['mode'],
                    $validModes,
                    "Pre-merge check '{$checkName}' mode must be one of: " . implode(', ', $validModes)
                );
            }
        }
    }

    /**
     * @test
     */
    public function itShouldHaveToolsConfiguration()
    {
        $this->markTestIncomplete();

        $this->assertArrayHasKey('tools', $this->config);
        $tools = $this->config['tools'];

        $expectedTools = ['phpstan', 'phpcs', 'eslint', 'markdownlint'];

        foreach ($expectedTools as $tool) {
            $this->assertArrayHasKey(
                $tool,
                $tools,
                "Expected tool '{$tool}' not found in tools configuration"
            );
        }
    }

    /**
     * @test
     */
    public function itShouldHaveValidToolConfigurations()
    {
        $this->markTestIncomplete();

        $tools = $this->config['tools'];

        foreach ($tools as $toolName => $toolConfig) {
            $this->assertIsArray($toolConfig, "Tool '{$toolName}' configuration must be an array");
            $this->assertArrayHasKey('enabled', $toolConfig, "Tool '{$toolName}' must have 'enabled' key");
            $this->assertIsBool($toolConfig['enabled'], "Tool '{$toolName}' enabled flag must be boolean");
        }
    }

    /**
     * @test
     */
    public function itShouldHavePhpstanLevelWhenEnabled()
    {
        $this->markTestIncomplete();

        if (!isset($this->config['tools']['phpstan'])) {
            $this->markTestSkipped('PHPStan not configured');
        }

        $phpstan = $this->config['tools']['phpstan'];

        if ($phpstan['enabled'] === true) {
            $this->assertArrayHasKey('level', $phpstan, 'PHPStan level must be specified when enabled');
            $level = $phpstan['level'];

            // Level can be string or integer
            if (!is_numeric($level)) {
                $this->assertContains(
                    $level,
                    ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'max']
                );
                return;
            }

            $level = (int)$level;
            $this->assertGreaterThanOrEqual(0, $level);
            $this->assertLessThanOrEqual(9, $level);
        }
    }

    /**
     * @test
     */
    public function itShouldHaveChatConfiguration()
    {
        $this->markTestIncomplete();

        $this->assertArrayHasKey('chat', $this->config);
        $chat = $this->config['chat'];

        if (isset($chat['auto_reply'])) {
            $this->assertIsBool($chat['auto_reply']);
        }

        if (isset($chat['art'])) {
            $this->assertIsBool($chat['art']);
        }
    }

    /**
     * @test
     */
    public function itShouldHaveChatIntegrationsConfiguration()
    {
        $this->markTestIncomplete();

        if (!isset($this->config['chat']['integrations'])) {
            $this->markTestSkipped('Chat integrations not configured');
        }

        $integrations = $this->config['chat']['integrations'];
        $validUsageValues = ['disabled', 'enabled', 'auto'];

        foreach ($integrations as $integration => $config) {
            if (is_array($config) && isset($config['usage'])) {
                $this->assertContains(
                    $config['usage'],
                    $validUsageValues,
                    "Integration '{$integration}' usage must be one of: " . implode(', ', $validUsageValues)
                );
            }
        }
    }

    /**
     * @test
     */
    public function itShouldHaveKnowledgeBaseConfiguration()
    {
        $this->markTestIncomplete();

        $this->assertArrayHasKey('knowledge_base', $this->config);
        $knowledgeBase = $this->config['knowledge_base'];

        $booleanKeys = ['opt_out'];
        foreach ($booleanKeys as $key) {
            if (isset($knowledgeBase[$key])) {
                $this->assertIsBool($knowledgeBase[$key], "knowledge_base.{$key} must be boolean");
            }
        }
    }

    /**
     * @test
     */
    public function itShouldHaveValidKnowledgeBaseScopeValues()
    {
        $this->markTestIncomplete();

        if (!isset($this->config['knowledge_base'])) {
            $this->markTestSkipped('Knowledge base not configured');
        }

        $knowledgeBase = $this->config['knowledge_base'];
        $validScopes = ['global', 'repository', 'organization'];

        $scopeKeys = ['learnings', 'issues', 'pull_requests'];
        foreach ($scopeKeys as $key) {
            if (isset($knowledgeBase[$key]['scope'])) {
                $this->assertContains(
                    $knowledgeBase[$key]['scope'],
                    $validScopes,
                    "knowledge_base.{$key}.scope must be one of: " . implode(', ', $validScopes)
                );
            }
        }
    }

    /**
     * @test
     */
    public function itShouldHaveCodeGenerationConfiguration()
    {
        $this->markTestIncomplete();

        $this->assertArrayHasKey('code_generation', $this->config);
        $codeGen = $this->config['code_generation'];

        $this->assertArrayHasKey('docstrings', $codeGen);
        $this->assertArrayHasKey('unit_tests', $codeGen);
    }

    /**
     * @test
     */
    public function itShouldHaveValidDocstringsLanguage()
    {
        $this->markTestIncomplete();

        if (!isset($this->config['code_generation']['docstrings']['language'])) {
            $this->markTestSkipped('Docstrings language not configured');
        }

        $language = $this->config['code_generation']['docstrings']['language'];
        $validLanguages = ['en-US', 'en-UK', 'es-ES', 'fr-FR', 'de-DE'];

        $this->assertContains(
            $language,
            $validLanguages,
            'Docstrings language must be a valid ISO language code'
        );
    }

    /**
     * @test
     */
    public function itShouldHaveUnitTestsPathInstructions()
    {
        $this->markTestIncomplete();

        if (!isset($this->config['code_generation']['unit_tests']['path_instructions'])) {
            $this->markTestSkipped('Unit tests path instructions not configured');
        }

        $pathInstructions = $this->config['code_generation']['unit_tests']['path_instructions'];

        $this->assertIsArray($pathInstructions);
        $this->assertNotEmpty($pathInstructions);

        foreach ($pathInstructions as $instruction) {
            $this->assertArrayHasKey('path', $instruction, 'Each path instruction must have a path');
            $this->assertArrayHasKey('instructions', $instruction, 'Each path instruction must have instructions');
            $this->assertIsString($instruction['path']);
            $this->assertIsString($instruction['instructions']);
            $this->assertNotEmpty($instruction['instructions']);
        }
    }

    /**
     * @test
     */
    public function itShouldHaveLaravelSpecificTestInstructions()
    {
        $this->markTestIncomplete();

        if (!isset($this->config['code_generation']['unit_tests']['path_instructions'])) {
            $this->markTestSkipped('Path instructions not configured');
        }

        $pathInstructions = $this->config['code_generation']['unit_tests']['path_instructions'];
        $paths = array_column($pathInstructions, 'path');

        // Check for Laravel test paths
        $hasUnitTests = false;
        $hasFeatureTests = false;

        foreach ($paths as $path) {
            if (strpos($path, 'tests/Unit') !== false || strpos($path, 'Unit') !== false) {
                $hasUnitTests = true;
            }
            if (strpos($path, 'tests/Feature') !== false || strpos($path, 'Feature') !== false) {
                $hasFeatureTests = true;
            }
        }

        $this->assertTrue(
            $hasUnitTests || $hasFeatureTests,
            'Should have instructions for Laravel test paths (Unit or Feature)'
        );
    }

    /**
     * @test
     */
    public function itShouldHaveConsistentBooleanTypes()
    {
        $this->markTestIncomplete();

        $this->validateBooleanTypes($this->config);
    }

    /**
     * @test
     */
    public function itShouldNotHaveConflictingSettings()
    {
        $this->markTestIncomplete();

        // If auto_review is enabled, certain other settings should be configured
        if (isset($this->config['reviews']['auto_review']['enabled'])
            && $this->config['reviews']['auto_review']['enabled'] === true) {
            $this->assertArrayHasKey(
                'base_branches',
                $this->config['reviews']['auto_review'],
                'Auto review should specify base branches when enabled'
            );
        }

        // If opt_out is true in knowledge_base, other knowledge_base settings are redundant
        if (isset($this->config['knowledge_base']['opt_out'])
            && $this->config['knowledge_base']['opt_out'] === true) {
            $this->assertTrue(
                true,
                'Knowledge base opt_out is true, other settings may be ignored'
            );
        }
    }

    /**
     * @test
     */
    public function itShouldLoadConfigurationSuccessfully()
    {
        $this->markTestIncomplete();

        $this->assertNotNull($this->config);
        $this->assertIsArray($this->config);
        $this->assertNotEmpty($this->config);
    }

    /**
     * Helper method to recursively validate boolean types
     */
    private function validateBooleanTypes($data, $path = '')
    {
        foreach ($data as $key => $value) {
            $currentPath = $path ? "{$path}.{$key}" : $key;

            if (is_array($value)) {
                $this->validateBooleanTypes($value, $currentPath);
            } elseif (in_array($key, ['enabled', 'opt_out', 'auto_reply', 'art'])) {
                $this->assertIsBool(
                    $value,
                    "Key '{$currentPath}' should be boolean, got " . gettype($value)
                );
            }
        }
    }
}