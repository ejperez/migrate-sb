<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://github.com/ejperez
 * @since      1.0.0
 *
 * @package    Migrate_Sb
 * @subpackage Migrate_Sb/admin/partials
 */
$posts = get_posts([
	'post_type' => 'post',
	'posts_per_page' => 1000,
	'post_status' => 'publish,draft,private',
	'orderby' => 'post_title',
	'order' => 'ASC',
	'lang' => pll_default_language()
]);

$postsOptions = implode('', array_map(function ($item) {
	return "<option value='$item->ID'>$item->post_title</option>";
}, $posts ?? []));

if (isset($_GET['clear_image_cache'])) {
	update_option('sb_image_cache', []);
}

$imageCache = get_option('sb_image_cache', []);
?>

<div class="wrap">
	<h1>Migrate SB</h1>

	<form id="js-migrate-sb-form" target="_blank" method="post" action="<?= home_url() ?>?_storyblok=1">
		<p>
			Post (<?= count($posts) ?>)<br>
			<select name="posts" required multiple size="20">
				<?= $postsOptions ?>
			</select>
		</p>
		<p>
			Test mode<br>
			<label for="test_mode"><input type="checkbox" name="test_mode" id="test_mode" value="1" checked> Yes</label>
		</p>
		<button class="button button-primary" type="submit">Submit</button>
	</form>

	<br>

	<div>
		<?= count($imageCache) ?> item(s) in cache. <a href="<?= $_SERVER['REQUEST_URI'] ?>&clear_image_cache=1">Clear
			image cache</a>
	</div>
</div>