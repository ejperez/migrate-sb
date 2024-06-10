<?php

class ModuleHeroBannerFifty extends Module
{
	public function __construct(array $data, WP_Post $post, array $translations)
	{
		$this->component = 'hero-50';
		parent::__construct($data, $post, $translations);

		$this->localizeFields(0);

		if (count($data['content']) > 1) {
			$this->localizeFields(1);
		}
	}

	private function localizeFields($index)
	{
		$placement = $index === 0 ? 'Left' : 'Right';
		$content = (object) $this->data['content'][$index];

		$this->localizeField('title' . $placement, function ($post, $data) use ($index) {
			return $data['content'][$index]['section_title'];
		});

		if ($content->image) {
			$image = $this->mapImage($content->image);
			$this->block['image' . $placement] = $image;
			$this->block['image' . $placement . 'Mobile'] = $image;
		}

		if ($content->image_mobile && $content->image !== $content->image_mobile) {
			$this->block['image' . $placement . 'Mobile'] = $this->mapImage($content->image_mobile);
		}

		$this->localizeField('button' . $placement, function ($post, $data) use ($index) {
			$content = (object) $data['content'][$index];
			$url = '';

			if ($content->link_type === 'product-category') {
				$url = get_term_link($content->link_product_category);
			} elseif ($content->link_type === 'post') {
				$url = get_permalink($content->link_post);
			} elseif ($content->link_type === 'url') {
				$url = $content->link_url;
			}

			return [
				[
					'link' => [
						'url' => $url,
						'linktype' => 'url',
						'fieldtype' => 'multilink',
						'cached_url' => $url
					],
					'label' => $content->link_text,
					'component' => 'button',
					'buttonVariant' => 'button-primary'
				]
			];
		});

		$this->localizeField('description' . $placement, function ($post, $data) use ($index) {
			return [
				'type' => 'doc',
				'content' => [
					[
						'type' => 'paragraph',
						'content' => [
							[
								'text' => $data['content'][$index]['sub_text'],
								'type' => 'text'
							]
						]
					]
				]
			];
		});
	}
}
