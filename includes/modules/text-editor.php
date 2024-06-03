<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once 'image-full.php';

class ModuleTextEditor extends Module
{
	public function __construct(array $data, WP_Post $post, array $translations)
	{
		$this->component = 'richContent';
		parent::__construct($data, $post, $translations);

		$this->block = array_merge($this->block, [
			'width' => 'w-full',
			'backgroundColor' => [
				'color' => $this->data['background_color'],
				'plugin' => 'native-color-picker'
			]
		]);

		$this->localizeField('heading', function ($post, $section) {
			return $section['title'];
		});

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
				$node->type = $this->camelToSnake($node->type);
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

	private static function getDivider()
	{
		return [
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
		$removeHeading = false;

		foreach ($block['content']['content'] as $index => $content) {
			if (!isset($content['content'])) {
				continue;
			}

			$containsImage = count(array_filter($content['content'], function ($content) {
				return $content['type'] === 'image';
			})) > 0;

			if ($containsImage || count($block['content']['content']) - 1 === $index) {
				$currentBlock = $block;
				$currentBlock['content']['content'] = $currentContent;

				if (count($block['content']['content']) - 1 === $index && !$containsImage) {
					$currentBlock['content']['content'] = empty($currentContent) ? [$content] : array_merge($currentContent, [$content]);
				}

				if ($removeHeading) {
					unset($currentBlock['heading']);
				}

				$output[] = self::getDivider();
				$output[] = $currentBlock;
				$removeHeading = true;

				if ($containsImage) {
					$currentContent = [];
					$imagePath = self::getImageFullURL($content['content'][0]['attrs']['src']);
					$id = attachment_url_to_postid($imagePath);

					// Try adding "-scaled" to image path
					if ($id === 0) {
						$index = strrpos($imagePath, '.');
						$imagePath = substr($imagePath, 0, $index) . '-scaled' . substr($imagePath, $index);
						$id = attachment_url_to_postid($imagePath);
					}

					if ($id !== 0) {
						$output[] = self::getDivider();
						$output[] = (new ModuleImageFull([
							'images' => [$id],
							'image_mobile' => [$id]
						], get_post($id), []))->getBlock();
					}
				}
			} else {
				$currentContent[] = $content;
			}
		}

		$output[] = self::getDivider();

		return $output;
	}
}
