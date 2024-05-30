<?php

class ModuleImageFull extends Module
{
	public function __construct(array $data, WP_Post $post, array $translations)
	{
		$this->component = 'hero';
		parent::__construct($data, $post, $translations);

		$this->block['image'] = empty($this->data['images']) || $this->data['images'][0] === 0 ? [] : $this->mapImage($this->data['images'][0]);
		$this->block['imageMobile'] = empty($this->data['image_mobile']) || $this->data['image_mobile'][0] === 0 ? [] : $this->mapImage($this->data['image_mobile'][0]);
	}
}
