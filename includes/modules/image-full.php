<?php

class ModuleImageFull extends Module
{
	public function map(): array
	{
		return [
			'component' => 'hero',
			'image' => $this->mapImage($this->data['images'][0]),
			'imageMobile' => empty($this->data['image_mobile']) ? [] : $this->mapImage($this->data['image_mobile'][0])
		];
	}
}
