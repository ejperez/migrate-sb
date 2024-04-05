<?php

require 'class-migrate-sb-mapper-factory.php';

class Mapper
{
	public static function mapSectionsToBlocks(array $sections, WP_Post $post)
	{
		ModuleFactory::setPost($post);

		$fixedSections = [
			['acf_fc_layout' => 'blog-header'],
			['acf_fc_layout' => 'featured-image'],
		];

		foreach (array_merge($fixedSections, $sections) as $section) {
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
}
