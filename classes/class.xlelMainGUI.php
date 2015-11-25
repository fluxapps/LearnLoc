<?php
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/LearnLoc/classes/class.ilLearnLocPlugin.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/LearnLoc/classes/Config/class.xlelConfigGUI.php');

/**
 * Class xlelMainGUI
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 * @version           1.0.0
 *
 * @ilCtrl_IsCalledBy xlelMainGUI : ilRouterGUI, ilUIPluginRouterGUI
 * @ilCtrl_IsCalledBy xlelMainGUI : ilLearnLocConfigGUI
 */
class xlelMainGUI {

	const TAB_SETTINGS = 'settings';
	/**
	 * @var ilTabsGUI
	 */
	protected $tabs;
	/**
	 * @var ilToolbarGUI
	 */
	protected $toolbar;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilTemplate
	 */
	protected $tpl;


	public function __construct() {
		global $tpl, $ilCtrl, $ilTabs;
		$this->tpl = $tpl;
		$this->tabs = $ilTabs;
		$this->ctrl = $ilCtrl;
		$this->pl = ilLearnLocPlugin::getInstance();
	}


	/**
	 * @return bool
	 */
	public function executeCommand() {
		$xlelConfigGUI = new xlelConfigGUI();
		$this->tabs->addTab(self::TAB_SETTINGS, $this->pl->txt('tab_' . self::TAB_SETTINGS), $this->ctrl->getLinkTarget($xlelConfigGUI));

		$nextClass = $this->ctrl->getNextClass();

		switch ($nextClass) {
			case 'xlelconfiggui';
				$this->tabs->setTabActive(self::TAB_SETTINGS);
				$this->ctrl->forwardCommand($xlelConfigGUI);

				break;
			default:
				$this->tabs->setTabActive(self::TAB_SETTINGS);
				$this->ctrl->forwardCommand($xlelConfigGUI);

				break;
		}

		$this->tpl->getStandardTemplate();
		$this->tpl->show();
	}
}

?>
