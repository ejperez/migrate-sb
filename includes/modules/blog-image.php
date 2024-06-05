<?php

class ModuleBlogImage extends Module
{
	public function __construct(array $data, WP_Post $post, array $translations)
	{
		$this->component = 'blogImage';
		parent::__construct($data, $post, $translations);

		$this->block['image'] = empty($this->data['image']) ? [] : $this->mapImage($this->data['image']);
	}
}
