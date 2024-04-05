<?php

require __DIR__ . '/../vendor/autoload.php';
require 'class-migrate-sb-mapper.php';

use Storyblok\ManagementClient;

class Migrate_Sb_Storyblok
{
	private $managementClient;
	private $settings;

	public function __construct()
	{
		$this->settings = (object) get_option('migrate_sb_settings');
		$this->managementClient = new ManagementClient($this->settings->api_token);
	}

	public function getFolders()
	{
		try {
			return $this->managementClient->get('spaces/' . $this->settings->space_id . '/stories', [
				'folder_only' => 1,
				'sort_by' => 'name'
			])->getBody()['stories'];
		} catch (Exception $ex) {
			return [];
		}
	}

	public function getAssetFolders()
	{
		try {
			return $this->managementClient->get('spaces/' . $this->settings->space_id . '/asset_folders')->getBody()['asset_folders'];
		} catch (Exception $ex) {
			return [];
		}
	}

	public function postStories($args)
	{
		$args = wp_parse_args($args, [
			'post' => null,
			'type' => 'post'
		]);

		if (empty($args['post'])) {
			throw new Exception('No post selected.');
		}

		$postId = $args['post'];
		$post = get_post($postId);

		echo "$post->post_title ";

		$blocks = Mapper::mapSectionsToBlocks(get_field('sections', $postId), $post);

		if ($GLOBALS['msb_test_mode'] ?? false) {
			echo '<pre>' . json_encode($blocks, JSON_PRETTY_PRINT) . '</pre>';

			return;
		}

		$story = [
			"name" => $post->post_title,
			"slug" => $post->post_name,
			"parent_id" => $this->settings->folder,
			"content" => [
				"component" => "page",
				"body" => $blocks
			]
		];

		try {
			$storyResult = $this->managementClient->post(
				'spaces/' . $this->settings->space_id . '/stories/',
				['story' => $story]
			)->getBody();

			$id = $storyResult['story']['id'];

			echo "<span style='color:green'>Created $id!</span><br>";
		} catch (Exception $exception) {
			$message = $exception->getMessage();
			echo "<span style='color:red'>$message</span><br>";
		}

	}
}
