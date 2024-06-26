<?php

use Storyblok\ManagementClient;

class Module
{
	protected $data;
	protected $post;
	protected $translations;
	protected $block;
	protected $component;

	public function __construct(array $data, WP_Post $post, array $translations)
	{
		$this->data = $data;
		$this->post = $post;
		$this->translations = $translations;
		$this->block = ['component' => $this->component];
	}

	protected function localizeSitewide(string $fieldName, callable $getValue)
	{
		foreach (array_keys(pll_the_languages(['raw' => true])) as $lang) {
			if ($lang !== pll_default_language()) {
				$fieldName = "{$fieldName}__i18n__{$lang}";
			}

			$this->block[$fieldName] = $getValue($lang);
		}
	}

	protected function localizeField(string $fieldName, callable $getValue)
	{
		$this->block[$fieldName] = $getValue($this->post, $this->data);

		if (empty($this->translations)) {
			return;
		}

		foreach ($this->translations as $lang => $postSection) {
			$this->block["{$fieldName}__i18n__{$lang}"] = $getValue($postSection['post'], $postSection['section']);
		}
	}

	protected function mapImage(int $id): array
	{
		$imageCache = get_option('sb_image_cache', []);

		if ($imageCache) {
			$cache = $imageCache[$id] ?? null;

			if ($cache) {
				return $cache;
			}
		}

		$uploadedImage = $this->uploadImage($id);
		$imageCache[$id] = [
			'id' => $uploadedImage['id'],
			'filename' => str_replace('s3.amazonaws.com/', '', $uploadedImage['filename']),
			'fieldtype' => 'asset'
		];
		update_option('sb_image_cache', $imageCache);

		return $imageCache[$id];
	}

	private function uploadImage(int $imageId)
	{
		$image = wp_get_attachment_image_src($imageId, 'full');

		if ($GLOBALS['msb_test_mode'] ?? false) {
			return ['id' => $imageId, 'filename' => $image[0]];
		}

		$settings = (object) get_option('migrate_sb_settings');
		$managementClient = new ManagementClient($settings->api_token);
		$image = wp_get_attachment_image_src($imageId, 'full');
		$imagePath = wp_get_original_image_path($imageId);
		$mime = mime_content_type($imagePath);
		$info = pathinfo($imagePath);
		$name = $info['basename'];

		$signedResponseObject = $managementClient->post(
			'spaces/' . $settings->space_id . '/assets/',
			[
				'filename' => $name,
				'size' => $image[1] . 'x' . $image[2],
				'asset_folder_id' => $settings->asset_folder
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

		return $managementClient->get(
			'spaces/' . $settings->space_id . '/assets/' . $signedResponseObject['id'] . '/finish_upload',
			[]
		)->getBody();
	}

	public function getBlock(): array
	{
		if (!$this->component) {
			return [];
		}

		return $this->block;
	}

	protected function mapLink($link)
	{
		return [
			'url' => $link['url'],
			'linktype' => 'url',
			'fieldtype' => 'multilink',
			'cached_url' => $link['url'],
			'target' => $link['target']
		];
	}
}
