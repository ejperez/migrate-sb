<?php

class ModuleBlogPage extends Module
{
	public function __construct(array $data, WP_Post $post, array $translations)
	{
		$this->component = 'blogpage';
		parent::__construct($data, $post, $translations);

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

		$this->block['thumbnail'] = $this->mapImage(get_post_thumbnail_id($post->ID));
	}
}
