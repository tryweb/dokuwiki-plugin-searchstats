<?php
/**
 * SearchStats Plugin: This plugin records the search words and displays stats in the admin section
 *
 * @license		GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author		Michael Schuh <mike.schuh@gmx.at>
 */

use dokuwiki\Form\Form;

class admin_plugin_searchstats extends DokuWiki_Admin_Plugin {

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

	function getMenuSort() { return 200; }
	function forAdminOnly() { return false; }

	//Carry out any processing required by the plugin.
	function handle() {
		$dataObject = new action_plugin_searchstats();
		$this->wordArray = $dataObject->getSearchWordArray();
	}
	
	//Render html output for the plugin.
	function html() {
		$form = new Form();
		
		if(is_array($this->wordArray) && count($this->wordArray) > 0) {
			// 添加標題
			$form->addHTML('<h1>'.$this->getLang('menu').'</h1>');
			
			// 添加表格
			$tableHTML = '<table class="inline">' . "\n";
			$tableHTML .= '<tr class="row0">' . "\n";
			$tableHTML .= '<th class="col0 leftalign">'.$this->getLang('th_word').'</th>' . "\n";
			$tableHTML .= '<th class="col1">'.$this->getLang('th_count').'</th>' . "\n";
			$tableHTML .= '</tr>' . "\n";
			
			foreach($this->wordArray as $word => $count) {
				$tableHTML .= '<tr>' . "\n";
				$tableHTML .= '<td class="col0">'.htmlspecialchars($word).'</td>' . "\n";
				$tableHTML .= '<td class="col1">'.htmlspecialchars($count).'</td>' . "\n";
				$tableHTML .= '</tr>' . "\n";
			}
			$tableHTML .= '</table>' . "\n";
			
			$form->addHTML($tableHTML);
		}
		else {
			$form->addHTML('<h1>'.$this->getLang('nosearchwords').'</h1>');
		}
		
		echo $form->toHTML();
	}


	var $wordArray = array();

}

?>