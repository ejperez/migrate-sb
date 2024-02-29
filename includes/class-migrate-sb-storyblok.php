<?php

require __DIR__ . '/../vendor/autoload.php';
require 'class-migrate-sb-mapper.php';

use Storyblok\ManagementClient;
use Storyblok\Client;

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
			$sbId = get_post_meta($postId, 'storyblok_id', true);
			$existing = false;

			echo "WP: $currentPost->post_title ";

			if ($sbId) {
				try {
					$existing = $this->managementClient->get('spaces/' . $this->settings['space_id'] . '/stories/' . $sbId)->getBody();
				} catch (Exception $exception) {
				}
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
					$this->managementClient->put(
						'spaces/' . $this->settings['space_id'] . '/stories/' . $sbId,
						['story' => $story]
					)->getBody();

					echo "<span style='color:blue'>Updated $sbId!</span><br>";
				} catch (Exception $exception) {
					$message = $exception->getMessage();
					echo "<span style='color:red'>$message</span><br>";
				}
			} else {
				try {
					$storyResult = $this->managementClient->post(
						'spaces/' . $this->settings['space_id'] . '/stories/',
						['story' => $story]
					)->getBody();

					$id = $storyResult['story']['id'];

					echo "<span style='color:green'>Created $id!</span><br>";
					update_post_meta($postId, 'storyblok_id', $id);
				} catch (Exception $exception) {
					$message = $exception->getMessage();
					echo "<span style='color:red'>$message</span><br>";
				}
			}
		}
	}
}
