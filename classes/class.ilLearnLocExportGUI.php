<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilLearnLocArcgisGUI
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version $Id:
 *
 * @ilCtrl_isCalledBy ilLearnLocExportGUI: ilObjLearnLocGUI
 */

require_once('class.ilLearnLocExporter.php');
require_once('class.ilLearnLocGpxExport.php');
require_once('class.ilLearnLocCsvExport.php');
//require_once('class.ilLearnLocPlugin.php');
require_once('class.ilLearnLocMedia.php');
//require_once('./Services/UICore/classes/class.ilCtrl.php');
require_once('./Services/Export/classes/class.ilExportGUI.php');
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('./Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php');

class ilLearnLocExportGUI extends ilExportGUI {
	/**
	 * @var int
	 */
	protected $ref_id;

	/**
	 * @var ilPropertyFormGUI
	 */
	protected $form;

	/**
	 * @var array
	 */
	protected $export_formats = array('csv', 'gpx');

	/**
	 * @param ilObjLearnLocGUI $parent_obj
	 */
	function __construct(ilObjLearnLocGUI $parent_obj) {
		global $tpl, $ilToolbar, $ilCtrl;

		parent::__construct($parent_obj);

		$this->ref_id     = $parent_obj->object->getId();
		$this->parent_obj = $parent_obj;
		$this->pl         = new ilLearnLocPlugin();
		$this->tpl        = $tpl;
		$this->parent_obj->tpl->setTitleIcon($this->pl->_getIcon('xlel', 'b'));
		$this->exporter = new ilLearnLocExporter();

		foreach($this->export_formats as $format) {
			if($format == 'xml') {
				$this->addFormat($format);
			}
			else {
				$this->addFormat($format, $this->pl->txt('export_' . $format), $this, 'create' . ucfirst($format) . "File");
			}
		}

		if($this->parent_obj->object->getExportKeywords() && $ilCtrl->getCmd() != 'editKeywords') {
			$ilToolbar->addButton($this->pl->txt("keywords_edit"), $ilCtrl->getLinkTarget($this, 'editKeywords'));
		}
	}

	public function createGpxFile() {
		if($this->parent_obj->object->getExportKeywords())
			$form = $this->initCustomFieldsForm('gpx');
		else
			$form = $this->initKeyWordsForm();
		$this->tpl->setContent($form->getHTML());
	}


	public function createCsvFile() {
		if($this->parent_obj->object->getExportKeywords())
			$form = $this->initCustomFieldsForm('csv');
		else
			$form = $this->initKeyWordsForm();
		$this->tpl->setContent($form->getHTML());
	}

	public function editKeywords() {
		$form = $this->initKeyWordsForm('edit');
		$form->setValuesByArray(array('keywords' => implode(',',json_decode($this->parent_obj->object->getExportKeywords()))));
		$this->tpl->setContent($form->getHTML());
	}


	/**
	 * @param string $mode
	 *
	 * @return ilPropertyFormGUI
	 */
	public function initKeyWordsForm($mode = 'create') {
		global $ilCtrl;

		$this->form = new ilPropertyFormGUI();
		$this->form->setTitle($this->pl->txt('keywords_export'));
		$this->form->setFormAction($ilCtrl->getFormAction($this));

		$ti = new ilTextAreaInputGUI($this->pl->txt('keywords'), 'keywords');
		$ti->setInfo($this->pl->txt('keywords_info'));
		$this->form->addItem($ti);
		$this->form->addCommandButton('returnToList', $this->pl->txt('cancel'));

		if($mode == 'create') {
			$this->form->addCommandButton('saveKeywords', $this->pl->txt('keywords_save'));
		} else {
			$this->form->addCommandButton('saveKeywords', $this->pl->txt('keywords_update'));
		}


		return $this->form;
	}

	public function saveKeywords() {
		global $ilCtrl;
		$this->initKeyWordsForm();
		if($this->form->checkInput()) {

			$keywords = str_replace(" ", "_", $this->form->getInput('keywords'));
			$keywords = str_replace(";", ",", $keywords);
			$keywords = json_encode($this->clean_array(explode(',', $keywords)));

			if($keywords != "[]") {
				$this->parent_obj->object->setExportKeywords($keywords);
			}else {
				$this->parent_obj->object->setExportKeywords(NULL);
			}

			$this->parent_obj->object->doUpdate();
		}
		$ilCtrl->redirect($this, "createGpxFile");
	}

	/**
	 * @param string $type
	 *
	 * @return ilPropertyFormGUI
	 */
	public function initCustomFieldsForm($type = 'gpx') {
		global $ilCtrl;

		$this->form = new ilPropertyFormGUI();
		$this->form->setTitle($this->pl->txt('export_keywords'));
		$this->form->setFormAction($ilCtrl->getFormAction($this));

		foreach(json_decode($this->parent_obj->object->getExportKeywords()) as $keyword) {
			$ti = new ilTextInputGUI($keyword, $keyword);
			$this->form->addItem($ti);
		}

		$this->form->addCommandButton('returnToList', $this->pl->txt('cancel'));
		$this->form->addCommandButton('export'.ucfirst($type).'File', $this->pl->txt('export_'.$type));

		return $this->form;
	}

	public function exportGpxFile() {
		global $ilCtrl;

		$this->initCustomFieldsForm();
		if($this->form->checkInput()) {

			$data = array();

			foreach(json_decode($this->parent_obj->object->getExportKeywords()) as $keyword) {
				$data[$keyword] = $this->form->getInput($keyword);
			}
			$gpx = new ilLearnLocGpxExport($this->parent_obj->object, 'gpx');
			$gpx->buildExportFile($data);
		}
		$ilCtrl->redirect($this, "listExportFiles");
	}

	public function exportCsvFile() {
		global $ilCtrl;

		$this->initCustomFieldsForm();
		if($this->form->checkInput()) {

			$data = array();

			foreach(json_decode($this->parent_obj->object->getExportKeywords()) as $keyword) {
				$data[$keyword] = $this->form->getInput($keyword);
			}
			$gpx = new ilLearnLocCsvExport($this->parent_obj->object, 'csv');
			$gpx->buildExportFile($data);
		}
		$ilCtrl->redirect($this, "listExportFiles");
	}

	public function returnToList() {
		global $ilCtrl;
		$ilCtrl->redirect($this, "listExportFiles");
	}


//
// Override
//


	/**
	 *
	 */
	function createExportFile() {
		global $ilCtrl;

		if($ilCtrl->getCmd() == "createExportFile") {
			$format = ilUtil::stripSlashes($_POST["format"]);
		}
		else {
			$format = substr($ilCtrl->getCmd(), 7);
		}
		foreach($this->getFormats() as $f) {
			if($f["key"] == $format) {
				if(is_object($f["call_obj"])) {
					$f["call_obj"]->$f["call_func"]();
				}
			}
		}
		//$ilCtrl->redirect($this, "listExportFiles");
	}

//
// Helper
//

	function clean_array($array){
		foreach($array as $key => $value) {
			if($value == '') {
				unset($array[$key]);
			}
		}
		return $array;
	}
}