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

require __DIR__ . '/../../includes/class-migrate-sb-storyblok.php';

$posts = get_posts([
	'post_type' => 'post',
	'posts_per_page' => -1,
	'orderby' => 'post_title',
	'order' => 'ASC',
	'lang' => pll_current_language()
]);

$postsOptions = implode('', array_map(function ($item) {
	return "<option value='$item->ID'>$item->post_title</option>";
}, $posts ?? []));

$sb = new Migrate_Sb_Storyblok(get_option('migrate_sb_settings'));
$foldersOptions = implode('', array_map(function ($item) {
	return sprintf("<option value='%s'>%s</option>", $item['id'], $item['name']);
}, $sb->getFolders() ?? []));
?>

<div class="wrap">
	<h1>Migrate SB</h1>

	<form target="_blank" method="post" action="<?= home_url() ?>?_storyblok=1">
		<p>
			Posts (<?= count($posts) ?>)<br>
			<select name="posts[]" multiple size="20">
				<?= $postsOptions ?>
			</select>
		</p>

		<p>
			Storyblok folder<br>
			<select name="folder">
				<?= $foldersOptions ?>
			</select>
		</p>

		<input type="hidden" name="action" value="do_sb_migration">
		<input type="hidden" name="lang" value="<?= pll_current_language() ?>">
		<input type="hidden" name="type" value="post">		
		<button class="button button-primary" type="submit">Submit</button>
	</form>
</div>