<?php

class ModulePopularProducts extends Module
{
	public function map(): array
	{
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

		return [
			'title' => $this->data['header'],
			'newLabel' => $translations['product_states']['new'] ?: 'NEW',
			'preamble' => $this->data['text'],
			'products' => $products,
			'component' => 'product-slider',
			'saleLabel' => $translations['product_states']['sale'] ?: 'SALE',
			'categories' => $categories,
			'showAsGrid' => $this->data['display_option'] === 'list',
			'hideContent' => false,
			'productsLimit' => $this->data['count'],
			'slidesToScroll' => 4,
			'productsInSlide' => 4,
			'showProductsFromCategory' => $this->data['section_displays'] === 'category'
		];
	}
}
