<?php

require 'class-migrate-sb-mapper-factory.php';

class Mapper
{
	public static function mapSectionsToBlocks(array $sections, WP_Post $post)
	{
		ModuleFactory::setPost($post);

		foreach ($sections as $section) {
			try {
				$block = (ModuleFactory::build($section['acf_fc_layout'], $section))->getBlock();

				if (empty($block)) {
					continue;
				}

				$blocks[] = $block;
			} catch (Exception $ex) {
				continue;
			}
		}

		return $blocks;
	}

	public static function mapComponent(WP_Post $post)
	{
		ModuleFactory::setPost($post);

		return (ModuleFactory::build('blog-page', []))->getBlock();
	}
}
