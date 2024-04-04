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
			'posts' => [],
			'type' => 'post'
		]);

		if (empty($args['posts'])) {
			throw new Exception('No posts selected.');
		}

		foreach ($args['posts'] as $postId) {
			$currentPost = get_post($postId);

			echo "$currentPost->post_title ";

			$blocks = Mapper::mapSectionToBlocks(get_fields($postId)['sections'], $postId);

			if ($GLOBALS['msb_test_mode'] ?? false) {
				echo '<pre>' . json_encode($blocks, JSON_PRETTY_PRINT) . '</pre>';
				break;
			}

			$story = [
				"name" => $currentPost->post_title,
				"slug" => $currentPost->post_name,
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
}
