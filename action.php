<?php
/**
 * SearchStats Plugin: This plugin records the search words and displays stats in the admin section
 *
 * @license		GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author		Michael Schuh <mike.schuh@gmx.at>
 */

 
class action_plugin_searchstats extends DokuWiki_Action_Plugin {

    function getInfo() {
      return array(
              'author' => 'Michael Schuh',
              'email'  => 'mike.schuh@gmx.at',
              'date'   => @file_get_contents(DOKU_PLUGIN.'searchstats/VERSION'),
              'name'   => 'Searchstats plugin (action, admin component)',
              'desc'   => 'This plugin records the search words and displays stats in the admin section',
              'url'    => 'http://blog.imho.at/20100902/artikel/dokuwiki-plugin-searchstats',
              );
    }

		function register(Doku_Event_Handler $controller) {
				$controller->register_hook('SEARCH_QUERY_FULLPAGE', 'BEFORE', $this,
																	 '_getSearchWords');
		}

		function getSearchWordArray($amount = false) {
			$helper = plugin_load('helper', 'searchstats');
			if(is_object($helper)) {
			 $wordArray = $helper->getSearchWordArray($amount);
			 return $wordArray;
			}
			return array();
		}

		/**
		 * Gets searchwords 
		 *
		 * @author		 Michael Schuh <mike.schuh@gmx.at>
		 */
		function _getSearchWords(&$event, $param) {
			if(function_exists('idx_get_indexer')) {
				$q = ft_queryParser(idx_get_indexer(),$event->data['query']);
			}
			else {
				$q = ft_queryParser($event->data['query']);
			}
			if(is_array($q['highlight'])) {
				$this->_checkSaveFolder();
				foreach($q['words'] as $saveWord) {
					if(strlen(trim($saveWord)) > 0) {
						//remove ;
						$saveWord = str_replace(';', '', $saveWord);
						$this->_saveSearchWord($saveWord);
					}
				}
			}
		}

		function _getSaveFolder() {
			$helper = plugin_load('helper', 'searchstats');
			return $helper->_getSaveFolder();
		}

		function _checkSaveFolder() {
			io_mkdir_p($this->_getSaveFolder());
		}
		function _getIndexFileName($saveWord) {
			return $this->_getSaveFolder().'/'.strlen($saveWord);
		}
		/**
		 * Adds searchword in index file
		 *
		 * @author		 Michael Schuh <mike.schuh@gmx.at>
		 */
		function _saveSearchWord($saveWord) {
			$fn = $this->_getIndexFileName($saveWord);
			$writeF = @fopen($fn.'.tmp', 'w');
			if(!$writeF) {
				return false;
			}
			$readF = @fopen($fn.'.idx', 'r');
			$wordArray = array();
			if($readF) {
				while (!feof($readF)) {
					$line = fgets($readF, 4096);
					$lineArray = explode(';', $line);
					if(is_array($lineArray) && strlen($lineArray[0]) > 0 && $lineArray[1]) 
						$wordArray[$lineArray[0]] = $lineArray[1];
				}
			}
			if(isset($wordArray[$saveWord])) {
				$wordArray[$saveWord] = $wordArray[$saveWord]+1;
			}
			else {
				$wordArray[$saveWord] = 1;
			}
			foreach($wordArray as $word => $count) {
				if(strlen($word) > 0) {
					$line = $word.";".$count;
					if(substr($line,-1) != "\n") $line .= "\n";
					fwrite($writeF, $line);
				}
			}
			fclose($writeF);
			global $conf;
			if($conf['fperm']) chmod($fn.'.tmp', $conf['fperm']);
			io_rename($fn.'.tmp', $fn.'.idx');
			return true;
		}
}