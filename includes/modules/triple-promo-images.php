<?php

class ModuleTriplePromoImages extends Module
{
	public function __construct(array $data, WP_Post $post, array $translations)
	{
		$this->component = 'promoTriple';
		parent::__construct($data, $post, $translations);

		// echo '<pre>' . json_encode($data, JSON_PRETTY_PRINT) . '</pre>';

		$this->block['removeSpaceTop'] = count(array_filter($data['custom_margin_and_padding'], function ($item) {
			return $item['value'] === 'no_desktop_top';
		})) > 0;

		$this->block['removeSpaceBottom'] = count(array_filter($data['custom_margin_and_padding'], function ($item) {
			return $item['value'] === 'no_desktop_bottom';
		})) > 0;

		$this->localizeField('items', function ($post, $data) {
			if (empty($data['items'])) {
				return [];
			}

			$items = [];

			foreach ($data['items'] as $item) {
				$item = (object) $item;

				$image = [];
				$url = '';
				$text = '';

				if ($item->get_content_from === 'post' && $item->post) {
					$image = has_post_thumbnail($item->post) ? get_post_thumbnail_id($item->post->ID) : [];
					$text = $item->post->post_title;
					$url = get_permalink($item->post->ID);
				} elseif ($item->category) {
					$text = $item->category->name;
					$url = get_term_link($item->category, $item->category->taxonomy);
				}

				if ($item->image) {
					$image = $item->image['ID'];
				}

				if ($item->link_type === 'product-category' && $item->link_product_category) {
					$url = get_term_link($item->link_product_category);
				} elseif ($item->link_type === 'post' && $item->link_post) {
					$url = get_permalink($item->link_post);
				} elseif ($item->link_type === 'url' && $item->link_url) {
					$url = $item->link_url;
				}

				if ($item->title) {
					$text = $item->title;
				}

				if ($image) {
					$image = $this->mapImage($image);
				}

				$items[] = [
					'link' => [
						'url' => $url,
						'linktype' => 'url',
						'fieldtype' => 'multilink',
						'cached_url' => $url,
					],
					'text' => $text,
					'image' => $image,
					'component' => 'PromoTripleItem',
					'textColor' => 'text-' . $item->text_color
				];
			}

			return $items;
		});
	}
}
