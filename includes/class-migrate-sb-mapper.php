<?php

require __DIR__ . '/../vendor/autoload.php';

class Migrate_Sb_Mapper
{
	private $managementClient;
	private $spaceId;
	private $assetFolder;

	public function __construct($managementClient, $spaceId, $assetFolder)
	{
		$this->managementClient = $managementClient;
		$this->spaceId = $spaceId;
		$this->assetFolder = $assetFolder;
	}

	public function mapSectionToBlocks($sections, $postId)
	{
		$blocks = [];
		$currentPost = get_post($postId);
		$categories = wp_get_post_categories($postId);
		$tags = $categories ? array_map(function ($category) {
			$category = get_term($category, 'category');
			$link = get_term_link($category);

			return [
				'link' => [
					'url' => $link,
					'linktype' => 'url',
					'fieldtype' => 'multilink',
					'cached_url' => $link
				],
				'label' => $category->name,
				'component' => 'link',
				'linkVariant' => ''
			];
		}, $categories) : [];

		// Blog header
		$blocks[] = [
			'component' => 'tmp_blog_header',
			'title' => $currentPost->post_title,
			'publish_date' => date('Y-m-d h:m', strtotime($currentPost->post_date)),
			'tags' => $tags,
			'author' => get_field('author', $postId)
		];

		// Featured image
		if (has_post_thumbnail($postId)) {
			$blocks[] = [
				'component' => 'hero',
				'image' => $this->mapImage(get_post_thumbnail_id($postId)),
				'description' => get_field('preamble', $postId)
			];
		}

		foreach ($sections as $section) {
			switch ($section['acf_fc_layout']) {
				case 'text-editor':
					$editor = new Tiptap\Editor([
						'content' => $section['content'],
						'extensions' => [
							new Tiptap\Extensions\StarterKit,
							new Tiptap\Nodes\Image,
							new Tiptap\Marks\Link
						]
					]);

					$editor->descendants(function (&$node) {
						$node->type = $this->camelToSnake($node->type);
					});

					$blocks[] = [
						'component' => 'richContent',
						'heading' => $section['title'],
						'content' => $editor->getDocument(),
						'width' => 'w-full',
						'backgroundColor' => [
							'color' => $section['background_color'],
							'plugin' => 'native-color-picker'
						]
					];

					break;
				case 'image-full':
					$blocks[] = [
						'component' => 'hero',
						'image' => $this->mapImage($section['images'][0]),
						'imageMobile' => empty($section['image_mobile']) ? [] : $this->mapImage($section['image_mobile'][0])
					];

					break;
				case 'image-column-triple':
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

					$blocks[] = [
						'component' => 'triple-image-block',
						'items' => $items,
						'displayOption' => $section['mobile_display_option']
					];

					break;
				case 'popular-products':
					$products = [];
					$categories = [];
					$translations = get_field('translate_selected_product', pll_default_language());

					if ($section['section_displays'] === 'handpick') {
						$products = [
							'plugin' => 'centra-product-selector-new',
							'selected' => array_map(function ($product) {
								return get_post_meta($product->ID, 'product_id', true);
							}, $section['handpicked_products'])
						];
					} else {
						$categories = [
							'plugin' => 'centra-category-selector-new',
							'selected' => [$section['category'] ? get_term_meta($section['category'], 'category_id', true) : null]
						];
					}

					$blocks[] = [
						'title' => $section['header'],
						'newLabel' => $translations['product_states']['new'] ?: 'NEW',
						'preamble' => $section['text'],
						'products' => $products,
						'component' => 'product-slider',
						'saleLabel' => $translations['product_states']['sale'] ?: 'SALE',
						'categories' => $categories,
						'showAsGrid' => $section['display_option'] === 'list',
						'hideContent' => false,
						'productsLimit' => $section['count'],
						'slidesToScroll' => 4,
						'productsInSlide' => 4,
						'showProductsFromCategory' => $section['section_displays'] === 'category'
					];

					break;
			}
		}

		return $blocks;
	}

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

	private function mapImage($id)
	{
		$uploadedImage = $this->uploadImage($id);

		return [
			'id' => $uploadedImage['id'],
			'filename' => $uploadedImage['filename'],
			'fieldtype' => 'asset'
		];
	}

	private function uploadImage($imageId)
	{
		if ($GLOBALS['msb_test_mode'] ?? false) {
			return ['id' => 1, 'filename' => 'test.jpg'];
		}

		$image = wp_get_attachment_image_src($imageId, 'full');
		$imagePath = wp_get_original_image_path($imageId);
		$mime = mime_content_type($imagePath);
		$info = pathinfo($imagePath);
		$name = $info['basename'];

		$signedResponseObject = $this->managementClient->post(
			'spaces/' . $this->spaceId . '/assets/',
			[
				'filename' => $name,
				'size' => $image[1] . 'x' . $image[2],
				'asset_folder_id' => $this->assetFolder
			]
		)->getBody();

		$cFile = new CURLFile($imagePath, $mime, $name);
		$post = [];

		foreach ($signedResponseObject['fields'] as $key => $field) {
			$post[$key] = $field;
		}

		$post['file'] = $cFile;
		$ch = curl_init($signedResponseObject['post_url']);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		$result = curl_exec($ch);
		curl_close($ch);

		if (!$result) {
			return;
		}

		return $this->managementClient->get(
			'spaces/' . $this->spaceId . '/assets/' . $signedResponseObject['id'] . '/finish_upload',
			[]
		)->getBody();
	}

	private function camelToSnake($camelCase)
	{
		$result = '';

		for ($i = 0; $i < strlen($camelCase); $i++) {
			$char = $camelCase[$i];

			if (ctype_upper($char)) {
				$result .= '_' . strtolower($char);
			} else {
				$result .= $char;
			}
		}

		return ltrim($result, '_');
	}
}
