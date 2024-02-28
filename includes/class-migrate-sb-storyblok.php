<?php

require __DIR__ . '/../vendor/autoload.php';

use Storyblok\ManagementClient;

class Migrate_Sb_Storyblok
{
	private $managementClient;
	private $spaceId;

	public function __construct($apiToken, $spaceId)
	{
		$this->managementClient = new ManagementClient($apiToken);
		$this->spaceId = $spaceId;
	}

	public function getFolders()
	{
		return $this->managementClient->get('spaces/' . $this->spaceId . '/stories', [
			'folder_only' => 1,
			'sort_by' => 'name'
		])->getBody()['stories'];
	}
}
