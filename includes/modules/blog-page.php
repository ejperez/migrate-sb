<?php

class ModuleBlogPage extends Module
{
	public function __construct(array $data, WP_Post $post, array $translations)
	{
		$this->component = 'blogpage';
		parent::__construct($data, $post, $translations);
		$image = '';

		if (has_post_thumbnail($post)) {
			$image = $this->mapImage(get_post_thumbnail_id($post))['filename'];
		}

		$this->localizeField('title', function ($post) {
			return $post->post_title;
		});

		$this->localizeField('excerpt', function ($post) {
			return get_field('preamble', $post->ID);
		});

		$this->localizeField('datePublished', function ($post) {
			return date('Y-m-d h:m', strtotime($post->post_date));
		});

		$this->localizeField('BlogCategories', function ($post) {
			$categories = wp_get_post_categories($post->ID);

			return $categories ? array_map(function ($category) {
				$category = get_term($category, 'category');

				return $category->slug;
			}, $categories) : [];
		});

		$this->localizeField('author', function ($post) {
			return get_field('author', $post->ID);
		});

		$this->localizeField('Meta', function ($post) use ($image) {
			$preamble = get_field('preamble', $post->ID);

			return [
				'title' => $post->post_title,
				'plugin' => 'seo_metatags',
				'og_image' => $image,
				'og_title' => $post->post_title,
				'description' => $preamble,
				'twitter_image' => $image,
				'twitter_title' => $post->post_title,
				'og_description' => $preamble,
				'twitter_description' => $preamble
			];
		});

		$this->block['thumbnail'] = has_post_thumbnail($post->ID) ? $this->mapImage(get_post_thumbnail_id($post->ID)) : [];
	}
}
