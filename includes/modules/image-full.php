<?php

class ModuleImageFull extends Module
{
	public function __construct(array $data, WP_Post $post, array $translations)
	{
		if (!has_post_thumbnail($post)) {
			return;
		}

		$this->component = 'hero';
		parent::__construct($data, $post, $translations);

		$this->block['image'] = $this->mapImage($this->data['images'][0]);
		$this->block['imageMobile'] = empty($this->data['image_mobile']) ? [] : $this->mapImage($this->data['image_mobile'][0]);
	}
}
