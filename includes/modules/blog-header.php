<?php

class ModuleBlogHeader extends Module
{
	public function __construct(array $data, WP_Post $post, array $translations)
	{
		$this->component = 'tmp_blog_header';
		parent::__construct($data, $post, $translations);

		$this->localizeField('title', function ($post) {
			return $post->post_title;
		});

		$this->localizeField('publish_date', function ($post) {
			return date('Y-m-d h:m', strtotime($post->post_date));
		});

		$this->localizeField('tags', function ($post) {
			$categories = wp_get_post_categories($post->ID);

			return $categories ? array_map(function ($category) {
				$category = get_term($category, 'category');
				$link = get_term_link($category);

				return [
					'link' => [
						'url' => $link,
						'linktype' => 'url',
						'fieldtype' => 'multilink',
						'cached_url' => $link
					],
					'label' => $category->name,
					'component' => 'link',
					'linkVariant' => ''
				];
			}, $categories) : [];
		});

		$this->localizeField('author', function ($post) {
			return get_field('author', $post->ID);
		});
	}
}
