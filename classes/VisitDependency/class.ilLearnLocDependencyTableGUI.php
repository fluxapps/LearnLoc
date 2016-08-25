<?php

require_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Class ilLearnLocDependencyTableGUI
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
class ilLearnLocDependencyTableGUI extends ilTable2GUI{
	const TBL_ID = 'tbl_xlel_dep';
	const LENGTH = 100;
	/**
	 * @var ilLearnLocPlugin
	 */
	protected $pl;
	/**
	 * @var ilLearnLocDependencyGUI
	 */
	protected $parent_gui;
	/**
	 * @var array
	 */
	protected $filter = array();
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var int
	 */
	protected $ref_id;


	public function __construct($ref_id, ilLearnLocDependencyGUI $a_parent_obj, $a_parent_cmd) {
		/**
		 * @var $ilCtrl    ilCtrl
		 * @var $ilToolbar ilToolbarGUI
		 */
		global $ilCtrl;
		$this->parent_gui = $a_parent_obj;
		$this->ctrl = $ilCtrl;
		$this->pl = new ilLearnLocPlugin();
		$this->ref_id = $ref_id;

		$this->setId(self::TBL_ID);
		$this->setPrefix(self::TBL_ID);
		$this->setFormName(self::TBL_ID);

		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->setTitle($this->pl->txt("common_dependency_title"));
		$this->setDescription($this->pl->txt("common_dependency_description"));
		$this->setRowTemplate('tpl.dependency_list.html', $this->pl->getDirectory());
		$this->initColumns();
	}

	protected function initColumns() {
		$this->addColumn($this->pl->txt("common_dependency_chain"));
	}

	/**
	 * @param int[] $set
	 */
	public function fillRow($set) {
		$items = count($set);
		$i = 0;
		foreach ($set as $dependency) {
			$this->tpl->setCurrentBlock("learn_loc");
			$this->ctrl->setParameter($this->parent_gui, "ref_id", $dependency);
			$this->tpl->setVariable("LINK", $this->ctrl->getLinkTarget($this->parent_gui, $this->parent_cmd));
			$this->tpl->setVariable("TITLE", ilObject::_lookupTitle(ilObject::_lookupObjectId($dependency)));
			if($dependency == $this->ref_id)
				$this->tpl->setVariable("CLASS", "bold");
			//not last item.
			if(++$i !== $items)
				$this->tpl->setVariable("SEPARATOR", "»");

			$this->tpl->parseCurrentBlock();
		}

	}
}