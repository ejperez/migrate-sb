<?php

require __DIR__ . '/../vendor/autoload.php';

class Migrate_Sb_Mapper
{
	public function mapSectionToBlocks($sections)
	{
		$blocks = [];
		$assets = [];

		foreach ($sections as $section) {
			switch ($section['acf_fc_layout']) {
				case 'text-editor':
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

					$blocks[] = [
						'component' => 'richContent',
						'heading' => $section['title'],
						'content' => $editor->getDocument(),
						'width' => 'w-full',
					];

					break;
				case 'image-full':
					if(empty($section['images'])) {
						break;
					}

					// $blocks[] = [
					// 	'component' => 'wp-image',
					// 	'image' => [
					// 		'filename' => wp_get_attachment_image_url($section['images'][0], 'full')
					// 	]
					// ];

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
