<?php

class ModuleTextAndImage extends Module
{
	public function __construct(array $data, WP_Post $post, array $translations)
	{
		$this->component = 'promo-50';
		parent::__construct($data, $post, $translations);

		echo '<pre>' . json_encode($data, JSON_PRETTY_PRINT) . '</pre>';

		$this->localizeField('title', function ($post, $data) {
			return $data['section_title'];
		});

		$this->block['addTopMargin'] = count(array_filter($data['custom_margin_and_padding'], function ($item) {
			return $item['value'] === 'no_desktop_top';
		})) === 0;

		$this->block['addBottomMargin'] = count(array_filter($data['custom_margin_and_padding'], function ($item) {
			return $item['value'] === 'no_desktop_bottom';
		})) === 0;

		$this->block['imagePosition'] = $data['layout'] === 'image_first' ? 'left' : 'right';

		if ($data['video_url']) {
			$this->block['vimeoId'] = substr($data['video_url'], strrpos($data['video_url'], '/') + 1);
		}

		$this->localizeField('description', function ($post, $data) {
			return [
				'type' => 'doc',
				'content' => [
					[
						'type' => 'paragraph',
						'content' => [
							[
								'text' => $data['text'],
								'type' => 'text'
							]
						]
					]
				]
			];
		});

		if ($data['image']) {
			$image = $this->mapImage($data['image']);
			$this->block['image'] = $image;
			$this->block['imageMobile'] = $image;
		}

		if ($data['image_mobile'] && $data['image'] !== $data['image_mobile']) {
			$this->block['imageMobile'] = $this->mapImage($data['image_mobile']);
		}

		if ($data['link_type'] !== 'none') {
			$this->localizeField('button', function ($post, $data) {
				$url = '';

				if ($data['link_type'] === 'product-category') {
					$url = get_term_link($data['link_product_category']);
				} elseif ($data['link_type'] === 'post') {
					$url = get_permalink($data['link_post']);
				} elseif ($data['link_type'] === 'url') {
					$url = $data['link_url'];
				}

				return [
					[
						'link' => [
							'url' => $url,
							'linktype' => 'url',
							'fieldtype' => 'multilink',
							'cached_url' => $url,
						],
						'label' => $data['link_text'],
						'component' => 'button',
						'buttonVariant' => 'button-primary'
					]
				];
			});
		}

		if ($data['image_link_type'] !== 'none') {
			$this->localizeField('imageLink', function ($post, $data) {
				$url = '';

				if ($data['image_link_type'] === 'product-category') {
					$url = get_term_link($data['image_link_product_category']);
				} elseif ($data['image_link_type'] === 'post') {
					$url = get_permalink($data['image_link_post']);
				} elseif ($data['image_link_type'] === 'url') {
					$url = $data['image_link_url'];
				}

				return [
					'url' => $url,
					'linktype' => 'url',
					'fieldtype' => 'multilink',
					'cached_url' => $url
				];
			});
		}

		/*
		{
                    "_uid": "5f967b80-e165-4c7a-89bf-197d47e7992d",
                    "image": {
                        "id": 16083131,
                        "alt": "",
                        "name": "",
                        "focus": "",
                        "title": "",
                        "source": "",
                        "filename": "https://a.storyblok.com/f/289181/1440x1125/035af82330/blog-post-hero-a-mediterranean-union-morjas1.jpg",
                        "copyright": "",
                        "fieldtype": "asset",
                        "meta_data": {},
                        "is_private": false,
                        "is_external_url": false
                    },
                    "title": "Promo 50-50",
                    "button": [
                        {
                            "_uid": "54179ec3-8205-4b64-88f3-acf429357dca",
                            "link": {
                                "id": "df778639-9d5a-4a18-bc46-5efc84e57d04",
                                "url": "",
                                "linktype": "story",
                                "fieldtype": "multilink",
                                "cached_url": "eu/the-archive"
                            },
                            "label": "View more",
                            "component": "button",
                            "buttonVariant": "button-primary"
                        }
                    ],
                    "vimeoId": "",
                    "component": "promo-50",
                    "imageLink": {
                        "id": "573a0900-ccd1-4ddd-8eff-c03258e4d85a",
                        "url": "",
                        "linktype": "story",
                        "fieldtype": "multilink",
                        "cached_url": "eu/impact"
                    },
                    "description": {
                        "type": "doc",
                        "content": [
                            {
                                "type": "paragraph",
                                "content": [
                                    {
                                        "text": "Vestibulum dapibus nunc ac augue. Vestibulum purus quam, scelerisque ut, mollis sed, nonummy id, metus. Integer ante arcu, accumsan a, consectetuer eget, posuere ut, mauris. Curabitur suscipit suscipit tellus. Donec vitae orci sed dolor rutrum auctor.",
                                        "type": "text"
                                    }
                                ]
                            }
                        ]
                    },
                    "imageMobile": {
                        "id": null,
                        "alt": null,
                        "name": "",
                        "focus": null,
                        "title": null,
                        "source": null,
                        "filename": "",
                        "copyright": null,
                        "fieldtype": "asset",
                        "meta_data": {}
                    },
                    "addTopMargin": true,
                    "imagePosition": "left",
                    "addBottomMargin": true,
                    "buttonFullWidth": true
                }
		*/

		// $this->block['image'] = empty($this->data['image']) ? [] : $this->mapImage($this->data['image']);
	}
}
