<?php

class ModulePopularProducts extends Module
{
	public function __construct(array $data, WP_Post $post, array $translations)
	{
		$this->component = 'product-slider';
		parent::__construct($data, $post, $translations);

		$products = [];
		$categories = [];
		$translations = get_field('translate_selected_product', pll_default_language());

		if ($this->data['section_displays'] === 'handpick') {
			$products = [
				'plugin' => 'centra-product-selector-new',
				'selected' => array_map(function ($product) {
					return get_post_meta($product->ID, 'product_id', true);
				}, $this->data['handpicked_products'])
			];
		} else {
			$categories = [
				'plugin' => 'centra-category-selector-new',
				'selected' => [$this->data['category'] ? get_term_meta($this->data['category'], 'category_id', true) : null]
			];
		}

		$this->block = array_merge($this->block, [
			'products' => $products,
			'categories' => $categories,
			'showAsGrid' => $this->data['display_option'] === 'list',
			'hideContent' => false,
			'productsLimit' => $this->data['count'],
			'slidesToScroll' => 4,
			'productsInSlide' => 4,
			'showProductsFromCategory' => $this->data['section_displays'] === 'category'
		]);

		$this->localizeField('title', function ($post, $section) {
			return $section['header'];
		});

		$this->localizeSitewide('newLabel', function ($lang) {
			$translations = get_field('translate_selected_product', $lang);

			return $translations['product_states']['new'] ?: 'NEW';
		});

		$this->localizeField('preamble', function ($post, $section) {
			return $section['text'];
		});

		$this->localizeSitewide('saleLabel', function ($lang) {
			$translations = get_field('translate_selected_product', $lang);

			return $translations['product_states']['sale'] ?: 'SALE';
		});
	}
}
