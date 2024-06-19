<?php

require_once ('modules/module.php');

class ModuleFactory
{
	private static ?WP_Post $post = null;
	private static array $translations = [];

	public static function setPost(WP_Post $post)
	{
		self::$post = $post;
		self::$translations = [];

		foreach (array_keys(pll_the_languages(['raw' => true])) as $language) {
			if ($language === pll_default_language())
				continue;

			$translatedPost = get_post(pll_get_post($post->ID, $language));

			if (!$translatedPost) {
				continue;
			}

			self::$translations[$language] = [
				'post' => $translatedPost,
				'sections' => get_field('sections', $translatedPost->ID)
			];
		}
	}

	public static function build(string $moduleName, array $data, int $index = 0): Module
	{
		$file = dirname(__FILE__) . "/modules/$moduleName.php";

		if (!is_readable($file)) {
			throw new Exception("File not found: $file");
		}

		require_once ($file);

		$module = 'Module' . str_replace('-', '', ucwords($moduleName, '-'));

		if (!class_exists($module)) {
			throw new Exception("Module class not found: $module");
		}

		if (self::$post === null) {
			throw new Exception("Current post not provided.");
		}

		// Pass only the current section's fields
		$currentTranslations = [];

		foreach (self::$translations as $lang => $postSections) {

			$currentTranslations[$lang] = [
				'post' => $postSections['post'],
				'section' => $postSections['sections'][$index] ?? []
			];
		}

		return new $module($data, self::$post, $currentTranslations);
	}
}
