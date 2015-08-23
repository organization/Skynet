<?php

namespace Skynet\task;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use Skynet\Skynet;
use Skynet\database\Updater;

class UpdateAsyncTask extends AsyncTask {
	private $pharName;
	private $pluginPharPath;
	private $pharUrl;
	public function __construct($pharName, $pluginPharPath, $pharUrl) {
		$this->pharName = $pharName;
		$this->pluginPharPath = $pluginPharPath;
		$this->pharUrl = $pharUrl;
	}
	public function onRun() {
		$phar = $this->download ( $this->pharUrl );
		unlink ( $this->pluginPharPath . $this->pharName );
		
		file_put_contents ( $this->pluginPharPath . $this->pharName, $phar );
	}
	public function onCompletion(Server $server) {
		$plugin = $server->getPluginManager ()->getPlugin ( "Skynet" );
		if (! $plugin instanceof Skynet)
			return;
		$updater = $plugin->getUpdater ();
		if (! $updater instanceof Updater)
			return;
		
		$updater->complete ( $this->pharName );
	}
	public function download($page, $timeout = 10, array $extraHeaders = []) {
		$ch = curl_init ( $page );
		curl_setopt ( $ch, CURLOPT_HTTPHEADER,\array_merge ( [ 
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