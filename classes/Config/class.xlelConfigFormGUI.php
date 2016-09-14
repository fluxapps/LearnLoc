<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('class.xlelConfig.php');

/**
 * Form-Class xlelConfigFormGUI
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 * @version           1.0.00
 *
 */
class xlelConfigFormGUI extends ilPropertyFormGUI {

	const A_COLS = 60;
	const A_ROWS = 5;
	const F_LIMIT = 'limit';
	/**
	 * @var xlelConfigGUI
	 */
	protected $parent_gui;
	/**
	 * @var  ilCtrl
	 */
	protected $ctrl;


	/**
	 * @param xlelConfigGUI $parent_gui
	 */
	public function __construct(xlelConfigGUI $parent_gui) {
		global $ilCtrl;
		$this->parent_gui = $parent_gui;
		$this->ctrl = $ilCtrl;
		$this->pl = ilLearnLocPlugin::getInstance();
		$this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
		$this->initForm();
	}


	/**
	 * @param $field
	 *
	 * @return string
	 */
	public function txt($field) {
		return $this->pl->txt('admin_' . $field);
	}


	protected function initForm() {
		$this->setTitle($this->pl->txt('admin_form_title'));

		ilUtil::sendInfo('Currently there are no settings for this plugin');

		$cb = new ilCheckboxInputGUI($this->txt(xlelConfig::F_DEPENDENCIES), xlelConfig::F_DEPENDENCIES);
//		$this->addItem($cb);

		$range = new ilNumberInputGUI($this->txt(xlelConfig::F_RANGE), xlelConfig::F_RANGE);
		//		$this->addItem($range);
		$cb = new ilCheckboxInputGUI($this->txt(xlelConfig::F_RANGE_ALLOW_OVERRIDE), xlelConfig::F_RANGE_ALLOW_OVERRIDE);
		//		$this->addItem($cb);
		/*
				$title = new ilFormSectionHeaderGUI();
				$title->setTitle($this->txt('campus_tour_header'));
				$this->addItem($title);
				$cb = new ilCheckboxInputGUI($this->txt(xlelConfig::F_CAMPUS_TOUR), xlelConfig::F_CAMPUS_TOUR);
				$node = new ilNumberInputGUI($this->txt(xlelConfig::F_CAMPUS_TOUR_NODE), xlelConfig::F_CAMPUS_TOUR_NODE);
				$cb->addSubItem($node);
				$username = new ilTextInputGUI($this->txt(xlelConfig::F_CAMPUS_TOUR_USERNAME), xlelConfig::F_CAMPUS_TOUR_USERNAME);
				$cb->addSubItem($username);
				$password = new ilTextInputGUI($this->txt(xlelConfig::F_CAMPUS_TOUR_PASSWORD), xlelConfig::F_CAMPUS_TOUR_PASSWORD);
				$cb->addSubItem($password);
				$this->addItem($cb);
		*/
		$this->addCommandButtons();
	}


	public function fillForm() {
		$array = array();
		foreach ($this->getItems() as $item) {
			$this->getValuesForItem($item, $array);
		}
		$this->setValuesByArray($array);
	}


	/**
	 * @param $item
	 * @param $array
	 *
	 * @internal param $key
	 */
	private function getValuesForItem($item, &$array) {
		if (self::checkItem($item)) {
			$key = $item->getPostVar();
			$array[$key] = xlelConfig::getWithName($key);
			//			echo '<pre>' . print_r($array, 1) . '</pre>';
			if (self::checkForSubItem($item)) {
				foreach ($item->getSubItems() as $subitem) {
					$this->getValuesForItem($subitem, $array);
				}
			}
		}
	}


	/**
	 * @return bool
	 */
	public function saveObject() {
		if (!$this->checkInput()) {
			return false;
		}
		foreach ($this->getItems() as $item) {
			$this->saveValueForItem($item);
		}
		xlelConfig::set(xlelConfig::F_CONFIG_VERSION, xlelConfig::CONFIG_VERSION);

		return true;
	}


	/**
	 * @param $item
	 */
	private function saveValueForItem($item) {
		if (self::checkItem($item)) {
			$key = $item->getPostVar();
			xlelConfig::set($key, $this->getInput($key));
			if (self::checkForSubItem($item)) {
				foreach ($item->getSubItems() as $subitem) {
					$this->saveValueForItem($subitem);
				}
			}
		}
	}


	/**
	 * @param $item
	 *
	 * @return bool
	 */
	public static function checkForSubItem($item) {
		return !$item instanceof ilFormSectionHeaderGUI AND !$item instanceof ilMultiSelectInputGUI;
	}


	/**
	 * @param $item
	 *
	 * @return bool
	 */
	public static function checkItem($item) {
		return !$item instanceof ilFormSectionHeaderGUI;
	}


	protected function addCommandButtons() {
		$this->addCommandButton('save', $this->pl->txt('admin_form_button_save'));
		$this->addCommandButton('cancel', $this->pl->txt('admin_form_button_cancel'));
	}


	/**
	 * @param int $filter
	 * @param bool $with_text
	 *
	 * @return array
	 */
	public static function getRoles($filter, $with_text = true) {
		global $rbacreview;
		$opt = array();
		$role_ids = array();
		foreach ($rbacreview->getRolesByFilter($filter) as $role) {
			$opt[$role['obj_id']] = $role['title'] . ' (' . $role['obj_id'] . ')';
			$role_ids[] = $role['obj_id'];
		}
		if ($with_text) {
			return $opt;
		} else {
			return $role_ids;
		}
	}
}