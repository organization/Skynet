<?php

namespace LevelUpper\database;

use pocketmine\utils\Utils;
use pocketmine\Server;
use LevelUpper\task\UpdateAsyncTask;
use LevelUpper\LevelUpper;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use LevelUpper\task\CheckAsyncTask;

class Updater {
	private $list = [ ];
	private $server;
	private $plugin;
	public function __construct(LevelUpper $plugin) {
		$this->server = Server::getInstance ();
		$this->plugin = $plugin;
	}
	public function setList(array $list) {
		$this->list = $list;
	}
	public function getList() {
		return $this->list;
	}
	/**
	 *
	 * @param string $pluginName        	
	 * @param string $pluginYmlLink        	
	 * @param string $pharLink        	
	 */
	public function addList($pluginName, $pluginYmlLink, $pharLink) {
		$this->list [$pluginName] = [ 
				"pluginYmlLink" => $pluginYmlLink,
				"pharLink" => $pharLink 
		];
	}
	public function run() {
		foreach ( $this->list as $pluginName => $plugin ) {
			if (! isset ( $plugin ["pluginYmlLink"] ) or ! isset ( $plugin ["pharLink"] ))
				continue;
			$pluginYmlLink = $plugin ["pluginYmlLink"];
			$pharLink = $plugin ["pharLink"];
			$this->server->getScheduler ()->scheduleAsyncTask ( new CheckAsyncTask ( $pluginName, $pluginYmlLink, $pharLink ) );
		}
	}
	public function asyncUpdate($pluginName, $gitPluginYml, $pharLink) {
		if ($this->isNeedUpdate ( $pluginName, $gitPluginYml ))
			$this->update ( $pluginName, $pharLink );
	}
	public function download($pharLink) {
		return Utils::getURL ( $pharLink );
	}
	public function isNeedUpdate($pluginName, $gitPluginYml) {
		if (! isset ( explode ( "version: ", $gitPluginYml )[1] ))
			return false;
		$gitPluginVersion = explode ( "version: ", $gitPluginYml )[1];
		
		if (! isset ( explode ( "\n", $gitPluginVersion )[0] ))
			return false;
		$gitPluginVersion = explode ( "\n", $gitPluginVersion )[0];
		$gitPluginVersion = trim ( $gitPluginVersion );
		
		if (! ($plugin = $this->server->getPluginManager ()->getPlugin ( $pluginName )) instanceof Plugin)
			return false;
		$onPluginVersion = $plugin->getDescription ()->getVersion ();
		
		if ($gitPluginVersion == $onPluginVersion)
			return false;
		if (is_numeric ( $gitPluginVersion ) and is_numeric ( $onPluginVersion ))
			if ($gitPluginVersion < $onPluginVersion)
				return false;
		return true;
	}
	public function update($pluginName, $pharLink) {
		$plugin = $this->server->getPluginManager ()->getPlugin ( $pluginName );
		$reflection_class = new \ReflectionClass ( $plugin );
		$property = $reflection_class->getParentClass ()->getProperty ( 'file' );
		$property->setAccessible ( true );
		$file = $property->getValue ( $plugin );
		
		if (\substr ( $file, 0, 7 ) !== "phar://")
			return;
		
		$file = trim ( $file, "\\/" );
		$pharName = explode ( $this->server->getDataPath () . "plugins", $file )[1];
		$pharName = trim ( $pharName, "\\/" );
		
		$this->server->getScheduler ()->scheduleAsyncTask ( new UpdateAsyncTask ( $pharName, $this->server->getDataPath () . "plugins/", $pharLink ) );
	}
	public function complete($pluginName) {
		$this->plugin->getLogger ()->info ( TextFormat::DARK_AQUA . $pluginName . $this->plugin->getDataBase ()->get ( "updated" ) );
	}
}
?>