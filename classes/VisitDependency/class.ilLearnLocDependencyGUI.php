<?php

require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("./Services/Form/classes/class.ilRepositorySelector2InputGUI.php");
require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/LearnLoc/classes/VisitDependency/class.ilLearnLocDependency.php");
require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/LearnLoc/classes/VisitDependency/class.ilLearnLocDependencyTableGUI.php");

/**
 * Class LearnLocDependencyGUI
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @ilCtrl_Calls      ilLearnLocDependencyGUI: ilPropertyFormGUI
 */
class ilLearnLocDependencyGUI {

	protected static $CMD_SHOW = 'show';
	protected static $CMD_EDIT_PARENT = 'editParent';
	protected static $CMD_SAVE_PARENT = 'saveParent';

	/** @var ilCtrl */
	protected $ctrl;

	/** @var  ilTemplate */
	protected $tpl;

	/** @var  ilToolbarGUI */
	protected $toolbar;

	/** @var  ilLearnLocPlugin */
	protected $pl;

	/** @var int  */
	protected $ref_id = 0;

	public function __construct($ref_id) {
		global $ilCtrl, $tpl, $ilToolbar;
		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->toolbar = $ilToolbar;
		$this->ref_id = $ref_id;
		$this->pl = new ilLearnLocPlugin();
	}

	public function executeCommand() {
		$cmd = $this->ctrl->getCmd();
		switch ($cmd) {
			case self::$CMD_SHOW:
			case self::$CMD_EDIT_PARENT:
			case self::$CMD_SAVE_PARENT:
				$this->{$cmd}();
				break;
		}
	}

	public function show() {
		$button = ilLinkButton::getInstance();
		$button->setCaption($this->pl->txt('common_editParent'), false);
		$button->setUrl($this->ctrl->getLinkTarget($this, self::$CMD_EDIT_PARENT));
		$this->toolbar->addButtonInstance($button);

		$table = new ilLearnLocDependencyTableGUI($this->ref_id, $this, 'show');

		$table->setData(ilLearnLocDependency::getPaths($this->ref_id));
		$this->tpl->setContent($table->getHTML());
	}

	public function editParent() {
		$form = $this->initForm();
		$form = $this->fillForm($form);
		$this->tpl->setContent($form->getHtml());
	}

	public function saveParent() {
		$form = $this->initForm();
		if(!$form->checkInput()) {
			$form->setValuesByPost();
			$this->tpl->setContent($form->getHTML());
			return;
		}
		$parent_id = $form->getInput('parent_id');
		if($parent_id == $this->ref_id) {
			ilUtil::sendFailure($this->pl->txt('common_cannot_save_same_parent_as_child'));
			$this->tpl->setContent($form->getHTML());
			return;
		}
		if($this->doUpdateParent($parent_id))
			ilUtil::sendSuccess($this->pl->txt('common_parent_saved'), true);
		else {
			ilUtil::sendFailure($this->pl->txt('common_cannot_save_circular_dependencies'));
			$this->tpl->setContent($form->getHTML());
			return;
		}
		$this->ctrl->redirect($this, self::$CMD_SHOW);
	}

	/**
	 * @return ilPropertyFormGUI
	 */
	private function initForm() {
		$form = new ilPropertyFormGUI();
		$item = new ilRepositorySelector2InputGUI($this->pl->txt('dependency_parent'), 'parent_id');
		$item->setParent($this);
		$item->getExplorerGUI()->setSelectableTypes(array ('xlel') );
		$form->addItem($item);

		$form->setFormAction($this->ctrl->getFormAction($this, 'saveParent'));
		$form->addCommandButton(self::$CMD_SAVE_PARENT, $this->pl->txt('common_save'));

		return $form;
	}

	/**
	 * @param $parent_id int
	 * @return bool|void
	 */
	private function doUpdateParent($parent_id) {
		/** @var ilLearnLocDependency[] $dependencies */
		$dependencies = ilLearnLocDependency::where(array('child' => $this->ref_id))->get();
		foreach ($dependencies as $dep) {
			$dep->delete();
		}
		if(!$parent_id)
			return;
		$dependency = new ilLearnLocDependency();
		$dependency->setParent($parent_id);
		$dependency->setChild($this->ref_id);
		if($dependency->checkForCircle()) {
			return false;
		}
		$dependency->create();
		return true;
	}

	/**
	 * @param $form ilPropertyFormGUI
	 * @return ilPropertyFormGUI
	 */
	private function fillForm($form) {
		/** @var ilLearnLocDependency $dependency */
		$dependency = ilLearnLocDependency::where(array('child' => $this->ref_id))->first();
		if($dependency)
			$form->setValuesByArray(array("parent_id" => $dependency->getParent()));
		return $form;
	}

}