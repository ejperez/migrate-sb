<?php

require_once __DIR__ . '/../../vendor/autoload.php';

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
}
