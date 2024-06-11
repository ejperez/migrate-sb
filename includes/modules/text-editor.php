<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once 'blog-image.php';

class ModuleTextEditor extends Module
{
	public function __construct(array $data, WP_Post $post, array $translations)
	{
		$this->component = 'richContent';
		parent::__construct($data, $post, $translations);

		$this->block = array_merge($this->block, [
			'backgroundColor' => [
				'color' => $this->data['background_color'],
				'plugin' => 'native-color-picker'
			]
		]);

		$this->localizeField('content', function ($post, $section) {
			$editor = new Tiptap\Editor([
				'content' => $section['content'],
				'extensions' => [
					new Tiptap\Extensions\StarterKit,
					new Tiptap\Nodes\Image,
					new Tiptap\Marks\Link
				]
			]);

			$editor->descendants(function (&$node) {
				if($node) {
					$node->type = $this->camelToSnake($node->type);
				}				
			});

			return $editor->getDocument();
		});
	}

	private function camelToSnake($camelCase)
	{
		$result = '';

		for ($i = 0; $i < strlen($camelCase); $i++) {
			$char = $camelCase[$i];

			if (ctype_upper($char)) {
				$result .= '_' . strtolower($char);
			} else {
				$result .= $char;
			}
		}

		return ltrim($result, '_');
	}

	private static function getImageFullURL($input)
	{
		$splitted = explode('-', $input);
		$lastItem = array_pop($splitted);

		if (strpos($lastItem, 'x') === false) {
			return $input;
		}

		$splitted2 = explode('.', $lastItem);
		array_shift($splitted2);

		return implode('-', $splitted) . '.' . reset($splitted2);
	}

	private static function addDivider(&$output)
	{
		if (empty($output)) {
			return;
		}

		$output[] = [
			'component' => 'divider',
			'heightMobile' => 24,
			'heightDesktop' => 32
		];
	}

	public static function splitImages($block)
	{
		if (!isset($block['content']) || !isset($block['content']['content'])) {
			return $block;
		}

		$output = [];
		$currentContent = [];

		foreach ($block['content']['content'] as $index => $content) {

			if (!isset($content['content'])) {
				continue;
			}

			$containsImage = count(array_filter($content['content'], function ($content) {
				return $content['type'] === 'image';
			})) > 0;

			if (!$containsImage && count($block['content']['content']) - 1 > $index) {
				$currentContent[] = $content;

				continue;
			}

			$currentBlock = $block;
			$currentBlock['content']['content'] = $currentContent;

			if (count($block['content']['content']) - 1 === $index && !$containsImage) {
				$currentBlock['content']['content'] = empty($currentContent) ? [$content] : array_merge($currentContent, [$content]);
			}

			self::addDivider($output);

			if (!empty($currentBlock['content']['content'])) {
				$output[] = $currentBlock;
			}

			if ($containsImage) {
				$currentContent = [];
				self::addBlockImage($output, $content);
			}
		}

		return $output;
	}

	private static function addBlockImage(&$output, $content)
	{
		$imagePath = self::getImageFullURL($content['content'][0]['attrs']['src']);
		$id = attachment_url_to_postid($imagePath);

		// Try adding "-scaled" to image path
		if ($id === 0) {
			$index = strrpos($imagePath, '.');
			$imagePath = substr($imagePath, 0, $index) . '-scaled' . substr($imagePath, $index);
			$id = attachment_url_to_postid($imagePath);
		}

		if ($id === 0) {
			return;
		}

		self::addDivider($output);

		$output[] = (new ModuleBlogImage(['image' => $id], get_post($id), []))->getBlock();
	}
}
