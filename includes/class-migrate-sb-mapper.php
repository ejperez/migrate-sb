<?php

require __DIR__ . '/../vendor/autoload.php';

class Migrate_Sb_Mapper
{
	public function mapSectionToBlocks($sections)
	{
		$blocks = [];

		foreach ($sections as $section) {
			switch ($section['acf_fc_layout']) {
				case 'text-editor':
					$editor = new Tiptap\Editor(['content' => $section['content']]);

					$editor->descendants(function (&$node) {
						$node->type = $this->camelToSnake($node->type);
					});

					$blocks[] = [
						'component' => 'richContent',
						'heading' => $section['title'],
						'content' => $editor->getDocument(),
						'width' => 'w-full',
					];

					break;
				case 'image-full':
					break;
			}
		}

		return $blocks;
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
