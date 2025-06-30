<?php
/**
 * SearchStats Plugin: This plugin records the search words and displays stats in the admin section
 *
 * @license		GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author		Michael Schuh <mike.schuh@gmx.at>
 */

class helper_plugin_searchstats extends DokuWiki_Plugin {

	function getInfo() {
		return array(
							'author' => 'Michael Schuh',
							'email'	=> 'mike.schuh@gmx.at',
							'date'	 => @file_get_contents(DOKU_PLUGIN.'searchstats/VERSION'),
							'name'	 => 'Searchstats plugin (action, admin component)',
							'desc'	 => 'This plugin records the search words and displays stats in the admin section',
							'url'		=> 'http://blog.imho.at/20100902/artikel/dokuwiki-plugin-searchstats',
							);
	}

	function getMethods() {
			$result = array();
			$result[] = array(
							'name'	 => 'getSearchWordArray',
							'desc'	 => 'returns search word array',
							'params' => array(
									'number of words' => 'integer'),
							'return' => array('words' => 'array'),
							);
			$result[] = array(
							'name'	 => '_getSaveFolder',
							'desc'	 => 'returns folder where data is saved',
							'params' => array(),
							'return' => array('savefolder' => 'string'),
							);
			return $result;
	}

	function getSearchWordArray($amount = false) {
		$wordArray = array();
		$dir = $this->_getSaveFolder();
		if (!is_dir($dir)) {
			return $wordArray; // 回傳空陣列如果目錄不存在
		}
		if ($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				if(is_file($dir.'/'.$file) && strstr($file, 'idx')) {
					$handle = @fopen($dir.'/'.$file, "r");
					if ($handle) {
						while (!feof($handle)) {
							$lines[] = rtrim(fgets($handle, 4096));
						}
						fclose($handle);
					}
					foreach($lines as $line) {
						$lineArray = explode(';', $line);
						if($lineArray[0] != '') {
							$wordArray[$lineArray[0]] = $lineArray[1];
						}
					}
				}
			}
			closedir($dh);
		}
		arsort($wordArray);
		if($amount && is_numeric($amount)) {
			$wordArray = array_slice($wordArray, 0, $amount);
		}
		return $wordArray;
	}

	function _getSaveFolder() {
		return $this->getConf('searchstats_savefolder');
	}

}

?>
