<?php

require __DIR__ . '/../vendor/autoload.php';

class Migrate_Sb_Mapper
{
	private $managementClient;
	private $spaceId;
	private $assetFolder;

	public function __construct($managementClient, $spaceId, $assetFolder)
	{
		$this->managementClient = $managementClient;
		$this->spaceId = $spaceId;
		$this->assetFolder = $assetFolder;
	}

	public function mapSectionToBlocks($sections)
	{
		$blocks = [];

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
						'backgroundColor' => [
							'color' => $section['background_color'],
							'plugin' => 'native-color-picker'
						]
					];

					break;
				case 'image-full':
					if (empty($section['images'])) {
						break;
					}

					$uploadedImage = $this->uploadImage($section['images'][0]);

					$blocks[] = [
						'component' => 'wp-image',
						'image' => [
							'id' => $uploadedImage['id'],
							'filename' => $uploadedImage['filename'],
							'fieldtype' => 'asset'
						]
					];

					break;
			}
		}

		return $blocks;
	}

	private function uploadImage($imageId)
	{
		$image = wp_get_attachment_image_src($imageId, 'full');
		$imagePath = wp_get_original_image_path($imageId);
		$mime = mime_content_type($imagePath);
		$info = pathinfo($imagePath);
		$name = $info['basename'];

		$signedResponseObject = $this->managementClient->post(
			'spaces/' . $this->spaceId . '/assets/',
			[
				'filename' => $name,
				'size' => $image[1] . 'x' . $image[2],
				'asset_folder_id' => $this->assetFolder
			]
		)->getBody();

		$cFile = new CURLFile($imagePath, $mime, $name);
		$post = [];

		foreach ($signedResponseObject['fields'] as $key => $field) {
			$post[$key] = $field;
		}

		$post['file'] = $cFile;
		$ch = curl_init($signedResponseObject['post_url']);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		$result = curl_exec($ch);
		curl_close($ch);

		if (!$result) {
			return;
		}

		return $this->managementClient->get(
			'spaces/' . $this->spaceId . '/assets/' . $signedResponseObject['id'] . '/finish_upload',
			[]
		)->getBody();
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
