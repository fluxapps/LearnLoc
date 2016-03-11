<?php

require_once('./Services/Repository/classes/class.ilRepositoryObjectPlugin.php');

/**
 * Class ilLearnLocPlugin
 *
 * @author        Fabian Schmid <fs@studer-raimann.ch>
 * @version       $Id$
 */
class ilLearnLocPlugin extends ilRepositoryObjectPlugin {

	const TYPE = 'xlel';
	/**
	 * @var ilLearnLocPlugin
	 */
	protected static $instance;


	protected function uninstallCustom() {
		// TODO
	}


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
		return self::TYPE;
	}


//	public function txt($a_var) {
//		require_once('./Customizing/global/plugins/Libraries/PluginTranslator/class.sragPluginTranslator.php');
//		return sragPluginTranslator::getInstance($this)->active()->write()->txt($a_var);
//	}
}
