<?php

require_once('modules/module.php');

class ModuleFactory
{
	public static ?WP_Post $currentPost = null;

	public static function build(string $moduleName, array $data): Module
	{
		$file = dirname(__FILE__) . "/modules/$moduleName.php";

		if (!is_readable($file)) {
			throw new Exception("File not found.");
		}

		require_once($file);

		$module = 'Module' . str_replace('-', '', ucwords($moduleName, '-'));

		if (!class_exists($module)) {
			throw new Exception("Invalid module type given.");
		}

		if (self::$currentPost === null) {
			throw new Exception("Current post not provided.");
		}

		return new $module($data, self::$currentPost);
	}
}
