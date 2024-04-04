<?php

require 'class-migrate-sb-mapper-factory.php';

class Mapper
{
	public static function mapSectionToBlocks($sections, $postId)
	{
		ModuleFactory::$currentPost = get_post($postId);

		$blocks = [(ModuleFactory::build('blog-header', []))->map()];

		if (has_post_thumbnail($postId)) {
			$blocks[] = (ModuleFactory::build('featured-image', []))->map();
		}

		foreach ($sections as $section) {
			try {
				$blocks[] = (ModuleFactory::build($section['acf_fc_layout'], $section))->map();
			} catch (Exception $ex) {
				continue;
			}
		}

		return $blocks;
	}
}
