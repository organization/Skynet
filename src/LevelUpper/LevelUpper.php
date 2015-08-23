<?php

namespace LevelUpper;

use LevelUpper\database\PluginData;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\command\PluginCommand;
use pocketmine\utils\TextFormat;
use LevelUpper\task\AutoSaveTask;
use LevelUpper\database\Updater;

class LevelUpper extends PluginBase {
	private $database;
	private $listenerLoader;
	/**
	 *
	 * @var Updater
	 */
	private $updater;
	/**
	 * Called when the plugin is enabled
	 *
	 * @see \pocketmine\plugin\PluginBase::onEnable()
	 */
	public function onEnable() {
		$this->database = new PluginData ( $this );
		$this->updater = new Updater ( $this );
		$this->getServer ()->getScheduler ()->scheduleRepeatingTask ( new AutoSaveTask ( $this ), 12000 );
		
		$this->updater->setList ( $this->database->db );
		$this->updater->run ();
	}
	/**
	 * Called when the plugin is disabled Use this to free open things and finish actions
	 *
	 * @see \pocketmine\plugin\PluginBase::onDisable()
	 */
	public function onDisable() {
		$this->save ();
	}
	/**
	 * Save plug-in configs
	 *
	 * @param string $async        	
	 */
	public function save($async = false) {
		$this->database->db = $this->updater->getList ();
		$this->database->save ( $async );
	}
	/**
	 * Return Plug-in Database
	 */
	public function getDataBase() {
		return $this->database;
	}
	/**
	 * Return Updater
	 *
	 * @return Updater
	 */
	public function getUpdater() {
		return $this->updater;
	}
}

?>