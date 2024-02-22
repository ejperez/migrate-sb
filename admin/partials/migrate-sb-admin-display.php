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
	'posts_per_page' => -1,
	'orderby' => 'post_title',
	'order' => 'ASC',
	'lang' => pll_default_language()
]);
$postsOptions = implode('', array_map(function ($item) {
	return "<option value='$item->ID'>$item->post_title</option>";
}, $posts ?? []))
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap">
	<h1>Migrate SB</h1>

	<form target="_blank" method="post" action="<?= home_url() ?>">
		<p>
			Posts (<?= count($posts) ?>)<br>
			<select name="posts[]" multiple size="20">
				<?= $postsOptions ?>
			</select>
		</p>

		<button class="button button-primary" type="submit">Submit</button>
	</form>
</div>