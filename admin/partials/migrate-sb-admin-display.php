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
	'post_status' => 'publish',
	'orderby' => 'post_title',
	'order' => 'ASC',
	'lang' => pll_default_language()
]);

$postsOptions = implode('', array_map(function ($item) {
	return "<option value='$item->ID'>$item->post_title</option>";
}, $posts ?? []));
?>

<div class="wrap">
	<h1>Migrate SB</h1>

	<form target="_blank" method="post" action="<?= home_url() ?>?_storyblok=1">
		<p>
			Post<br>
			<select name="post" required>
				<?= $postsOptions ?>
			</select>
		</p>
		<p>
			Test mode<br>
			<label for="test_mode"><input type="checkbox" name="test_mode" id="test_mode" value="1" checked> Yes</label>
		</p>

		<input type="hidden" name="action" value="do_sb_migration">
		<input type="hidden" name="lang" value="<?= pll_current_language() ?>">
		<input type="hidden" name="type" value="post">
		<button class="button button-primary" type="submit">Submit</button>
	</form>
</div>