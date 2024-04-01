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

$settings = get_option('migrate_sb_settings');

if ($settings) {
	$sb = new Migrate_Sb_Storyblok($settings);
	$foldersOptions = implode('', array_map(function ($item) use ($settings) {
		return sprintf("<option value='%s' %s>%s</option>", $item['id'], $item['id'] === intval($settings['folder'] ?? null) ? 'selected' : '', $item['name']);
	}, $sb->getFolders() ?? []));
	$assetFoldersOptions = implode('', array_map(function ($item) use ($settings) {
		return sprintf("<option value='%s' %s>%s</option>", $item['id'], $item['id'] === intval($settings['asset_folder'] ?? null) ? 'selected' : '', $item['name']);
	}, $sb->getAssetFolders() ?? []));
}
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
	<h1>Storyblok Settings</h1>
	<form name="migrate-sb-form" method="post" action="options.php">
		<?php settings_fields('migrate_sb_settings_group'); ?>
		<p>
			API Token<br>
			<input class="all-options code" name="migrate_sb_settings[api_token]" type="text"
				value="<?= $settings['api_token'] ?? '' ?>" required>
		</p>

		<p>
			Space ID<br>
			<input class="all-options code" name="migrate_sb_settings[space_id]" type="text"
				value="<?= $settings['space_id'] ?? '' ?>" required>
		</p>

		<?php if ($foldersOptions ?? false): ?>
			<p>
				Post import folder<br>
				<select required name="migrate_sb_settings[folder]">
					<?= $foldersOptions ?>
				</select>
			</p>
		<?php endif ?>

		<?php if ($assetFoldersOptions ?? false): ?>
			<p>
				Asset folder<br>
				<select required name="migrate_sb_settings[asset_folder]">
					<?= $assetFoldersOptions ?>
				</select>
			</p>
		<?php endif ?>

		<?php submit_button(); ?>
	</form>
</div>