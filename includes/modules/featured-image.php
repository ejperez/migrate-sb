<?php

class ModuleFeaturedImage extends Module
{
	public function map(): array
	{
		return [
			'component' => 'hero',
			'image' => $this->mapImage(get_post_thumbnail_id($this->currentPost->ID)),
			'description' => get_field('preamble', $this->currentPost->ID)
		];
	}
}
