<?php

namespace LevelUpper\task;

use pocketmine\scheduler\AsyncTask;
use LevelUpper\LevelUpper;
use LevelUpper\database\Updater;
use pocketmine\Server;

class CheckAsyncTask extends AsyncTask {
	private $pluginName, $pluginYmlLink, $pharLink, $result;
	public function __construct($pluginName, $pluginYmlLink, $pharLink) {
		$this->pluginName = $pluginName;
		$this->pluginYmlLink = $pluginYmlLink;
		$this->pharLink = $pharLink;
	}
	public function onRun() {
		$this->result = $this->download ( $this->pluginYmlLink );
	}
	public function onCompletion(Server $server) {
		$plugin = $server->getPluginManager ()->getPlugin ( "LevelUpper" );
		if (! $plugin instanceof LevelUpper)
			return;
		$updater = $plugin->getUpdater ();
		if (! $updater instanceof Updater)
			return;
		
		$updater->asyncUpdate ( $this->pluginName, $this->result, $this->pharLink );
	}
	public function download($page, $timeout = 10, array $extraHeaders = []) {
		$ch = curl_init ( $page );
		curl_setopt ( $ch, CURLOPT_HTTPHEADER, \array_merge ( [ 
				"User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:12.0) Gecko/20100101 Firefox/12.0 PocketMine-MP" 
		], $extraHeaders ) );
		curl_setopt ( $ch, CURLOPT_AUTOREFERER, \true );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, \false );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 2 );
		curl_setopt ( $ch, CURLOPT_FORBID_REUSE, 1 );
		curl_setopt ( $ch, CURLOPT_FRESH_CONNECT, 1 );
		curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, \true );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, \true );
		curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, ( int ) $timeout );
		curl_setopt ( $ch, CURLOPT_TIMEOUT, ( int ) $timeout );
		$ret = curl_exec ( $ch );
		curl_close ( $ch );
		
		return $ret;
	}
}

?>