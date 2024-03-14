<?php

require __DIR__ . '/../vendor/autoload.php';
require 'class-migrate-sb-mapper.php';

use Storyblok\ManagementClient;

class Migrate_Sb_Storyblok
{
	private $managementClient;
	private $settings;

	public function __construct($settings)
	{
		$this->settings = wp_parse_args($settings, [
			'api_token' => null,
			'space_id'
		]);

		$this->managementClient = new ManagementClient($this->settings['api_token']);
	}

	public function getFolders()
	{
		return $this->managementClient->get('spaces/' . $this->settings['space_id'] . '/stories', [
			'folder_only' => 1,
			'sort_by' => 'name'
		])->getBody()['stories'];
	}

	public function getAssetFolders()
	{
		return $this->managementClient->get('spaces/' . $this->settings['space_id'] . '/asset_folders')->getBody()['asset_folders'];
	}

	public function postStories($args)
	{
		$args = wp_parse_args($args, [
			'posts' => [],
			'type' => 'post',
			'lang' => 'en',
			'folder' => null,
			'assetFolder' => null
		]);

		if (!$args['folder'] || empty($args['posts']) || empty($args['assetFolder'])) {
			throw new Exception('No Storyblok folder/asset folder or posts.');
		}

		$mapper = new Migrate_Sb_Mapper($this->managementClient, $this->settings['space_id'], $args['assetFolder']);

		foreach ($args['posts'] as $postId) {
			$currentPost = get_post($postId);

			echo "$currentPost->post_title ";

			$blocks = $mapper->mapSectionToBlocks(get_fields($postId)['sections']);

			$story = [
				"name" => $currentPost->post_title,
				"slug" => $currentPost->post_name,
				"parent_id" => $args['folder'],
				"content" => [
					"component" => "page",
					"body" => $blocks
				]
			];

			try {
				$storyResult = $this->managementClient->post(
					'spaces/' . $this->settings['space_id'] . '/stories/',
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
