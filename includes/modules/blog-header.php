<?php

class ModuleBlogHeader extends Module
{
	public function map(): array
	{
		$categories = wp_get_post_categories($this->currentPost->ID);
		$tags = $categories ? array_map(function ($category) {
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

		return [
			'component' => 'tmp_blog_header',
			'title' => $this->currentPost->post_title,
			'publish_date' => date('Y-m-d h:m', strtotime($this->currentPost->post_date)),
			'tags' => $tags,
			'author' => get_field('author', $this->currentPost->ID)
		];
	}
}
