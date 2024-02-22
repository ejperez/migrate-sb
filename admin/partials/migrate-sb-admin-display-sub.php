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

$settings = get_option('migrate_sb_settings');
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
	<h1>Storyblok Settings</h1>
	<form name="migrate-sb-form" method="post" action="options.php">
		<?php settings_fields('migrate_sb_settings_group'); ?>
		<p>
			API Token<br>
			<input class="all-options code" name="migrate_sb_settings[api_token]" type="text" value="<?= $settings['api_token'] ?? '' ?>">
		</p>

		<p>
			Space ID<br>
			<input class="all-options code" name="migrate_sb_settings[space_id]" type="text" value="<?= $settings['space_id'] ?? '' ?>">
		</p>

		<?php submit_button(); ?>
	</form>
</div>