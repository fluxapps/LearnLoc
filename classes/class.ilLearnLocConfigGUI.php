<?php

require_once('./Services/Component/classes/class.ilPluginConfigGUI.php');
require_once('class.ilLearnLocPlugin.php');

/**
 * LearnLoc configuration user interface class
 *
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version $Id$
 *
 */
class ilLearnLocConfigGUI extends ilPluginConfigGUI {

	public function executeCommand() {
		global $ilCtrl, $ilTabs, $lng, $tpl;
		/**
		 * @var $ilCtrl ilCtrl
		 */

		$ilCtrl->redirectByClass(array(
			'ilUIPluginRouterGUI',
			'xlelMainGUI'
		));
		$ilCtrl->setParameterByClass("ilobjcomponentsettingsgui", "ctype", $_GET["ctype"]);
		$ilCtrl->setParameterByClass("ilobjcomponentsettingsgui", "cname", $_GET["cname"]);
		$ilCtrl->setParameterByClass("ilobjcomponentsettingsgui", "slot_id", $_GET["slot_id"]);
		$ilCtrl->setParameterByClass("ilobjcomponentsettingsgui", "plugin_id", $_GET["plugin_id"]);
		$ilCtrl->setParameterByClass("ilobjcomponentsettingsgui", "pname", $_GET["pname"]);

		$tpl->setTitle($lng->txt("cmps_plugin") . ": " . $_GET["pname"]);
		$tpl->setDescription("");

		$ilTabs->clearTargets();

		if ($_GET["plugin_id"]) {
			$ilTabs->setBackTarget($lng->txt("cmps_plugin"), $ilCtrl->getLinkTargetByClass("ilobjcomponentsettingsgui", "showPlugin"));
		} else {
			$ilTabs->setBackTarget($lng->txt("cmps_plugins"), $ilCtrl->getLinkTargetByClass("ilobjcomponentsettingsgui", "listPlugins"));
		}

		$a_gui_object = new xlelMainGUI();
		$a_gui_object->executeCommand();
	}


	public function performCommand($cmd) {
	}
}

?>
