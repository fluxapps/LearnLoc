<?php

require_once('./Services/Repository/classes/class.ilRepositoryObjectPlugin.php');

/**
 * LearnLoc repository object plugin
 *
 * @author        Fabian Schmid <fs@studer-raimann.ch>
 * @version       $Id$
 *
 */
class ilLearnLocPlugin extends ilRepositoryObjectPlugin {

	/**
	 * @var ilLearnLocPlugin
	 */
	protected static $instance;


	/**
	 * @return ilLearnLocPlugin
	 */
	public static function getInstance() {
		if (!isset(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	function getPluginName() {
		return "LearnLoc";
	}


	public static function _getType() {
		return 'xlel';
	}


	/**
	 * @param $key_language
	 */
	public function generateLanguageCSV($key_language, $additional_language = '') {
		global $ilDB;
		/**
		 * @var ilDB $ilDB
		 */

		$additional_languages[] = $key_language;

		$q = 'SELECT * FROM lng_data WHERE module = ' . $ilDB->quote($this->getPrefix());
		$res = $ilDB->query($q);
		$sets = array();
		while ($rec = $ilDB->fetchObject($res)) {
			$rec->identifier = str_replace($this->getPrefix() . '_', '', $rec->identifier);
			$sets[$rec->lang_key][$rec->identifier] = $rec;
		}

		$lines = array();
		$lines[] = 'part;var;' . $key_language . ';' . $additional_language;
		foreach ($sets[$key_language] as $id => $rec) {
			$lines[] = implode(';', array( $rec->identifier, '', $rec->value, $sets[$additional_language][$rec->identifier]->value ));
		}
		$path = substr(__FILE__, 0, strpos(__FILE__, 'classes')) . 'lang/lang.csv';
		file_put_contents($path, implode("\n", $lines));
	}


	public function updateLanguageFiles() {
		setlocale(LC_ALL, 'de_DE.utf8');
		ini_set('auto_detect_line_endings', true);
		$path = substr(__FILE__, 0, strpos(__FILE__, 'classes')) . 'lang/';
		if (file_exists($path . 'lang_custom.csv')) {
			$file = $path . 'lang_custom.csv';
		} else {
			$file = $path . 'lang.csv';
		}
		$keys = array();
		$new_lines = array();

		foreach (file($file) as $n => $row) {
			//			$row = utf8_encode($row);
			if ($n == 0) {
				$keys = str_getcsv($row, ";");
				continue;
			}
			$data = str_getcsv($row, ";");;
			foreach ($keys as $i => $k) {
				if ($k != 'var' AND $k != 'part') {
					if ($data[1] != '') {
						$new_lines[$k][] = $data[0] . '_' . $data[1] . '#:#' . $data[$i];
					} else {
						$new_lines[$k][] = $data[0] . '#:#' . $data[$i];
					}
				}
			}
		}
		$start = '<!-- language file start -->' . PHP_EOL;
		$status = true;

		foreach ($new_lines as $lng_key => $lang) {
			$status = file_put_contents($path . 'ilias_' . $lng_key . '.lang', $start . implode(PHP_EOL, $lang));
		}

		if (!$status) {
			ilUtil::sendFailure('Language-Files coul\'d not be written');
		}
		$this->updateLanguages();
	}
}
