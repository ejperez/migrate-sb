<?php

class ModuleImageFifty extends Module
{
	public function __construct(array $data, WP_Post $post, array $translations)
	{
		$this->component = 'image-columns';
		parent::__construct($data, $post, $translations);

		$this->block['removeSpaceTop'] = count(array_filter($data['custom_margin_and_padding'], function ($item) {
			return $item['value'] === 'no_desktop_top';
		})) > 0;

		$this->block['removeSpaceBottom'] = count(array_filter($data['custom_margin_and_padding'], function ($item) {
			return $item['value'] === 'no_desktop_bottom';
		})) > 0;

		$this->block['layout'] = $data['image_ratio'] === '16x9' ? 'rectangle' : 'square';

		$this->localizeField('images', function ($post, $data) {
			if (empty($data['images'])) {
				return [];
			}

			$images = [];

			foreach ($data['images'] as $item) {
				$item = (object) $item;
				$image = [];
				$url = '';

				if ($item->link_type === 'product-category' && $item->link_product_category) {
					$url = get_term_link($item->link_product_category);
				} elseif ($item->link_type === 'post' && $item->link_post) {
					$url = get_permalink($item->link_post);
				} elseif ($item->link_type === 'url' && $item->link_url) {
					$url = $item->link_url;
				}

				if ($item->image) {
					$image = $this->mapImage($item->image);
				}

				$images[] = [
					'link' => [
						'url' => $url,
						'linktype' => 'url',
						'fieldtype' => 'multilink',
						'cached_url' => $url,
					],
					'title' => $item->link_text,
					'image' => $image,
					'component' => 'image-column-item'
				];
			}

			return $images;
		});
	}
}
