<?php

class ModuleImageColumnTriple extends Module
{
	private function mapLink($link)
	{
		return [
			'url' => $link['url'],
			'linktype' => 'url',
			'fieldtype' => 'multilink',
			'cached_url' => $link['url'],
			'target' => $link['target']
		];
	}

	public function map(): array
	{
		$items = [];

		foreach ($this->data['items'] as $item) {
			$actionField = 'click_action_2';

			if ($this->data['mobile_display_option'] === 'horizontal') {
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

		return [
			'component' => 'triple-image-block',
			'items' => $items,
			'displayOption' => $this->data['mobile_display_option']
		];
	}
}
