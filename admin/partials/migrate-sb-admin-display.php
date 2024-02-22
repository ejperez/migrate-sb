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
require __DIR__ . '/../../vendor/autoload.php';

use Storyblok\ManagementClient;

$posts = get_posts([
	'post_type' => 'post',
	'posts_per_page' => -1,
	'orderby' => 'post_title',
	'order' => 'ASC',
	'lang' => pll_default_language()
]);

$postsOptions = implode('', array_map(function ($item) {
	return "<option value='$item->ID'>$item->post_title</option>";
}, $posts ?? []));

$settings = get_option('migrate_sb_settings');
$managementClient = new ManagementClient($settings['api_token']);
$folders = $managementClient->get('spaces/' . $settings['space_id'] . '/stories', [
	'folder_only' => 1,
	'sort_by' => 'name'
])->getBody()['stories'];
$foldersOptions = implode('', array_map(function ($item) {
	return sprintf("<option value='%s'>%s</option>", $item['id'], $item['name']);
}, $folders ?? []));
?>

<div class="wrap">
	<h1>Migrate SB</h1>

	<form target="_blank" method="post" action="<?= home_url() ?>">
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
		<button class="button button-primary" type="submit">Submit</button>
	</form>
</div>