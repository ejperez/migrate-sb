<?php

class ModuleHeroBanner extends Module
{
	public function __construct(array $data, WP_Post $post, array $translations)
	{
		$this->component = 'hero';
		parent::__construct($data, $post, $translations);

		// echo '<pre>' . json_encode($data, JSON_PRETTY_PRINT) . '</pre>';

		if ($data['video_url']) {
			$this->block['vimeoId'] = substr($data['video_url'], strrpos($data['video_url'], '/') + 1);
		}

		$this->block['horizontalAligment'] = $data['content_position'];

		$this->localizeField('description', function ($post, $data) {
			return $data['sub_text'];
		});

		$this->localizeField('title', function ($post, $data) {
			return $data['section_title'];
		});

		if ($data['image']) {
			$image = $this->mapImage($data['image']);
			$this->block['image'] = $image;
			$this->block['imageMobile'] = $image;
		}

		if ($data['image_mobile'] && $data['image'] !== $data['image_mobile']) {
			$this->block['imageMobile'] = $this->mapImage($data['image_mobile']);
		}

		$this->localizeField('buttons', function ($post, $data) {
			if (empty($data['button'])) {
				return [];
			}

			$buttons = [];

			foreach ($data['button'] as $button) {
				$url = '';

				if ($button['link_type'] === 'product-category') {
					$url = get_term_link($button['link_product_category']);
				} elseif ($button['link_type'] === 'post') {
					$url = get_permalink($button['link_post']);
				} elseif ($button['link_type'] === 'url') {
					$url = $button['link_url'];
				}

				$buttons[] = [
					'link' => [
						'url' => $url,
						'linktype' => 'url',
						'fieldtype' => 'multilink',
						'cached_url' => $url,
					],
					'label' => $button['link_text'],
					'component' => 'button',
					'buttonVariant' => 'button-primary'
				];
			}

			return $buttons;
		});
	}
}
