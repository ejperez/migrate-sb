<?php

class ModuleFeaturedImage extends Module
{
	public function __construct(array $data, WP_Post $post, array $translations)
	{
		if (!has_post_thumbnail($post)) {
			return;
		}

		$this->component = 'hero';
		parent::__construct($data, $post, $translations);

		$this->block['image'] = $this->mapImage(get_post_thumbnail_id($this->post->ID));

		$this->localizeField('description', function ($post) {
			return get_field('preamble', $post->ID);
		});
	}
}
