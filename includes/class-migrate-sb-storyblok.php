<?php

require __DIR__ . '/../vendor/autoload.php';
require 'class-migrate-sb-mapper.php';

use Storyblok\ManagementClient;
use Storyblok\Client;

class Migrate_Sb_Storyblok
{
	private $managementClient;
	private $contentDeliveryClient;
	private $settings;

	public function __construct($settings)
	{
		$this->settings = wp_parse_args($settings, [
			'api_token' => null,
			'preview_access_token' => null,
			'space_id'
		]);

		$this->managementClient = new ManagementClient($this->settings['api_token']);
		$this->contentDeliveryClient = new Client($this->settings['preview_access_token']);
	}

	public function getFolders()
	{
		return $this->managementClient->get('spaces/' . $this->settings['space_id'] . '/stories', [
			'folder_only' => 1,
			'sort_by' => 'name'
		])->getBody()['stories'];
	}

	public function postStories($args)
	{
		$args = wp_parse_args($args, [
			'posts' => [],
			'type' => 'post',
			'lang' => 'en',
			'folder' => null
		]);

		if (!$args['folder'] || empty($args['posts'])) {
			return ['error' => 'No Storyblok folder or posts.'];
		}

		$mapper = new Migrate_Sb_Mapper();

		foreach ($args['posts'] as $postId) {
			$currentPost = get_post($postId);
			list($folderId, $folderSlug) = explode('|', $args['folder']);

			echo "WP: $currentPost->post_title ";

			try {
				$existing = $this->contentDeliveryClient->getStoryBySlug($folderSlug . '/' . $currentPost->post_name)->getBody();
			} catch (Exception $exception) {
				$existing = false;
			}

			$blocks = $mapper->mapSectionToBlocks(get_fields($postId)['sections']);

			$story = [
				"name" => $currentPost->post_title,
				"slug" => $currentPost->post_name,
				"parent_id" => $args['folder'],
				"content" =>  [
					"component" =>  "page",
					"body" =>  $blocks
				]
			];

			if ($existing) {
				try {
					$storyResult = $this->managementClient->put(
						'spaces/' . $this->settings['space_id'] . '/stories/' . $existing['story']['id'],
						['story' => $story]
					)->getBody();

					$id = $storyResult['story']['id'];
					echo "<span style='color:blue'>SB: $id</span><br>";
				} catch (Exception $exception) {
					$message = $exception->getMessage();
					echo "<span style='color:red'>SB: $message</span><br>";
				}
			} else {
				try {
					$storyResult = $this->managementClient->post(
						'spaces/' . $this->settings['space_id'] . '/stories/',
						['story' => $story]
					)->getBody();

					$id = $storyResult['story']['id'];
					echo "<span style='color:green'>SB: $id</span><br>";
				} catch (Exception $exception) {
					$message = $exception->getMessage();
					echo "<span style='color:red'>SB: $message</span><br>";
				}
			}
		}
	}
}
