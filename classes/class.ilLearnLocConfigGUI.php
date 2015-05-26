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

	const PREFIX = '';

	/**
	 * @var array
	 */
	protected $checkboxes = array('campus_tour' => array('node' => 'ilTextInputGUI','username' => 'ilTextInputGUI','password' => 'ilTextInputGUI',),);


	function __construct() {
		$this->pl = new ilLearnLocPlugin();
	}

	function performCommand($cmd) {
		switch ($cmd) {
			case 'configure':
			case 'save':
				$this->$cmd();
				break;

		}
	}

	function configure() {
		global $tpl;

		$this->initConfigurationForm();
		$this->getValues();
		$tpl->setContent($this->form->getHTML());
	}

	public function getValues() {
		foreach ($this->checkboxes as $key => $cb) {
			if (!is_array($cb)) {
				$values[$cb] = $this->_getValue($cb);
			} else {
				$values[$key] = $this->_getValue($key);
				foreach ($cb as $field => $gui) {
					$values[$key . '_' . $field] = $this->_getValue($key . '_' . $field);
				}
			}

		}
		$this->form->setValuesByArray($values);
	}

	public function initConfigurationForm() {
		global $lng, $ilCtrl;

		require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
		$this->form = new ilPropertyFormGUI();

		foreach ($this->checkboxes as $key => $cb) {
			if (!is_array($cb)) {
				$checkbox = new ilCheckboxInputGUI($this->plugin_object->txt($cb), $cb);
				$this->form->addItem($checkbox);
			} else {
				$checkbox = new ilCheckboxInputGUI($this->plugin_object->txt($key), $key);
				foreach ($cb as $field => $gui) {
					$sub = new $gui($this->plugin_object->txt($key . '_' . $field), $key . '_' . $field);
					$checkbox->addSubItem($sub);
				}
				$this->form->addItem($checkbox);
			}

		}

		$this->form->addCommandButton('save', $lng->txt('save'));
		$this->form->setTitle($this->plugin_object->txt('configuration'));
		$this->form->setFormAction($ilCtrl->getFormAction($this));

		return $this->form;
	}

	public function save() {
		global $tpl, $ilCtrl;

		$this->initConfigurationForm();
		if ($this->form->checkInput()) {
			foreach ($this->checkboxes as $key => $cb) {
				if (!is_array($cb)) {
					$this->setValue($cb, $this->form->getInput($cb));
				} else {
					$this->setValue($key, $this->form->getInput($key));
					foreach ($cb as $field => $gui) {
						$this->setValue($key . '_' . $field, $this->form->getInput($key . '_' . $field));
					}
				}
			}
			$ilCtrl->redirect($this, 'configure');
		} else {
			$this->form->setValuesByPost();
			$tpl->setContent($this->form->getHtml());
		}
	}

	/**
	 * @param $key
	 * @param $value
	 */
	public function setValue($key, $value) {
		global $ilDB;

		if (!is_string($this->_getValue($key))) {
			$ilDB->insert('rep_robj_'.ilLearnLocPlugin::_getType().'_conf', array(''.ilLearnLocPlugin::_getType().'_key' => array('text', $key), ''.ilLearnLocPlugin::_getType().'_value' => array('text', $value)));
		} else {
			$ilDB->update('rep_robj_'.ilLearnLocPlugin::_getType().'_conf', array(''.ilLearnLocPlugin::_getType().'_key' => array('text', $key), ''.ilLearnLocPlugin::_getType().'_value' => array('text', $value)), array(''.ilLearnLocPlugin::_getType().'_key' => array('text', $key)));
		}
	}

	/**
	 * @param $key
	 *
	 * @return bool|string
	 */
	public static function _getValue($key) {
		global $ilDB;

		$result = $ilDB->query('SELECT '.ilLearnLocPlugin::_getType().'_value FROM rep_robj_'.ilLearnLocPlugin::_getType().'_conf WHERE '.ilLearnLocPlugin::_getType().'_key = ' . $ilDB->quote($key, 'text'));
		if ($result->numRows() == 0) {
			return false;
		}
		$record = $ilDB->fetchAssoc($result);

		return (string)$record[ilLearnLocPlugin::_getType().'_value'];
	}
}

?>
