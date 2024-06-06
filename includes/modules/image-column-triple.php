<?php

class ModuleImageColumnTriple extends Module
{
	public function __construct(array $data, WP_Post $post, array $translations)
	{
		$this->component = 'triple-image-block';
		parent::__construct($data, $post, $translations);

		$this->block = array_merge($this->block, [
			'displayOption' => $this->data['mobile_display_option']
		]);

		$this->localizeField('items', function ($post, $section) {
			$items = [];

			foreach ($section['items'] as $item) {
				$actionField = 'click_action_2';

				if ($section['mobile_display_option'] === 'horizontal') {
					$actionField = 'click_action';
				}

				$items[] = [
					'component' => 'triple-image-item',
					'title' => $item['title'],
					'image' => $this->mapImage($item['image']),
					'link' => $item['link'] ? $this->mapLink($item['link']) : [],
					'textColor' => 'text-' . $item['text_color'],
					'clickAction' => $item[$actionField],
					'titlePosition' => $item['title_position']
				];
			}

			return $items;
		});
	}
}
