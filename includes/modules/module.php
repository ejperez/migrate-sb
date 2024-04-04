<?php

use Storyblok\ManagementClient;

class Module
{
	protected $data;
	protected $currentPost;

	public function __construct(array $data, WP_Post $post)
	{
		$this->data = $data;
		$this->currentPost = $post;
	}

	protected function mapImage($id)
	{
		$uploadedImage = $this->uploadImage($id);

		return [
			'id' => $uploadedImage['id'],
			'filename' => $uploadedImage['filename'],
			'fieldtype' => 'asset'
		];
	}

	private function uploadImage($imageId)
	{
		if ($GLOBALS['msb_test_mode'] ?? false) {
			return ['id' => 1, 'filename' => 'test.jpg'];
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

	/**
	 * Child class should override this
	 */
	public function map(): array
	{
		return $this->data;
	}
}
