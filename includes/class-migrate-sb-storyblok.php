<?php

require __DIR__ . '/../vendor/autoload.php';

use Storyblok\ManagementClient;

class Migrate_Sb_Storyblok
{
	private $managementClient;
	private $spaceId;

	public function __construct($apiToken, $spaceId)
	{
		$this->managementClient = new ManagementClient($apiToken);
		$this->spaceId = $spaceId;
	}

	public function getFolders()
	{
		return $this->managementClient->get('spaces/' . $this->spaceId . '/stories', [
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

		foreach ($args['posts'] as $postId) {
			$currentPost = get_post($postId);

			echo "WP: $currentPost->post_title ";

			$story = [
				"name" => $currentPost->post_title,
				"slug" => $currentPost->post_name,
				"parent_id" => $args['folder'],
				"content" =>  [
					"component" =>  "page",
					"body" =>  []
				]
			];

			try {
				$storyResult = $this->managementClient->post(
					'spaces/' . $this->spaceId . '/stories/',
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
