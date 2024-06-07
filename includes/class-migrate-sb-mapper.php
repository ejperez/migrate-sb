<?php

require 'class-migrate-sb-mapper-factory.php';

class Mapper
{
	public static function mapSectionsToBlocks(array $sections, WP_Post $post)
	{
		ModuleFactory::setPost($post);

		$blocks = [];

		foreach ($sections as $section) {
			try {
				// echo $section['acf_fc_layout'];
				$block = (ModuleFactory::build($section['acf_fc_layout'], $section))->getBlock();				

				if (empty($block)) {
					continue;
				}

				if ($section['acf_fc_layout'] === 'text-editor') {
					$output = ModuleTextEditor::splitImages($block);
					$blocks = array_merge($blocks, $output);
				} else {
					$blocks[] = $block;
				}
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
