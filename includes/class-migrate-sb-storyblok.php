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
		$isSuccess = true;

		ob_start();

		$story = [
			'story' => [
				'name' => $post->post_title,
				'slug' => $post->post_name,
				'parent_id' => $this->settings->folder,
				'translated_slugs' => [],
				'tag_list' => [$post->post_name],
				'localized_paths' => [],
				'content' => array_merge(
					Mapper::mapComponent($post),
					[
						'body' =>
							array_merge(
								[['component' => 'blogPage']],
								Mapper::mapSectionsToBlocks(get_field('sections', $postId), $post),
								[
									[
										'component' => 'blogPagination',
										'newerPost' => 'newer post',
										'olderPost' => 'older post'
									],
									[
										'title' => 'Related articles',
										'component' => 'relatedArticles',
										'linkLabel' => 'read more'
									]
								],
							)
					]
				)
			]
		];

		foreach (array_keys(pll_the_languages(['raw' => true])) as $language) {
			if ($language === pll_default_language())
				continue;

			$translatedPost = get_post(pll_get_post($post->ID, $language));

			if (!$translatedPost) {
				continue;
			}

			$story['story']['translated_slugs'][] = [
				'lang' => $language,
				'slug' => $translatedPost->post_name,
				'name' => $translatedPost->post_name,
				'published' => false,
			];

			$story['story']['localized_paths'][] = [
				'lang' => $language,
				'name' => $translatedPost->post_name,
				'published' => false,
			];

			$story['story']['tag_list'][] = $translatedPost->post_name;
		}

		if ($GLOBALS['msb_test_mode'] ?? false) {
			$body = $story['story']['content']['body'];
			return compact('story', 'body');
		}

		// Check if blog exists, update if it exists, create if not
		$storyExists = [];

		try {
			$storyExists = $this->managementClient->get(
				'spaces/' . $this->settings->space_id . '/stories',
				[
					'by_slugs' => '*' . $post->post_name,
					'with_parent' => $this->settings->folder
				]
			)->getBody();
		} catch (Exception $exception) {
			echo $exception->getMessage();
		}

		if (isset($storyExists['stories']) && !empty($storyExists['stories'])) {
			try {
				$storyResult = $this->managementClient->put(
					'spaces/' . $this->settings->space_id . '/stories/' . $storyExists['stories'][0]['id'],
					$story
				)->getBody();

				echo 'Updated: ' . $storyResult['story']['id'];
			} catch (Exception $exception) {
				echo $exception->getMessage();
				$isSuccess = false;
			}
		} else {
			try {
				$storyResult = $this->managementClient->post(
					'spaces/' . $this->settings->space_id . '/stories/',
					$story
				)->getBody();

				echo 'Created: ' . $storyResult['story']['id'];
			} catch (Exception $exception) {
				echo $exception->getMessage();
				$isSuccess = false;
			}
		}

		$logs = ob_get_clean();

		return compact('logs', 'story', 'isSuccess');
	}
}
