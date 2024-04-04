<?php

require_once __DIR__ . '/../../vendor/autoload.php';

class ModuleTextEditor extends Module
{
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

	public function map(): array
	{
		$editor = new Tiptap\Editor([
			'content' => $this->data['content'],
			'extensions' => [
				new Tiptap\Extensions\StarterKit,
				new Tiptap\Nodes\Image,
				new Tiptap\Marks\Link
			]
		]);

		$editor->descendants(function (&$node) {
			$node->type = $this->camelToSnake($node->type);
		});

		return [
			'component' => 'richContent',
			'heading' => $this->data['title'],
			'content' => $editor->getDocument(),
			'width' => 'w-full',
			'backgroundColor' => [
				'color' => $this->data['background_color'],
				'plugin' => 'native-color-picker'
			]
		];
	}
}
