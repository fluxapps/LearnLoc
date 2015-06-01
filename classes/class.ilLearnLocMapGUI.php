<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */
//require_once('class.ilLearnLocPlugin.php');
//require_once('./Services/UICore/classes/class.ilCtrl.php');

if (is_file('./Services/GoogleMaps/classes/class.ilGoogleMapGUI.php')) {
    require_once('./Services/GoogleMaps/classes/class.ilGoogleMapGUI.php');
} else {
    require_once('./Services/Maps/classes/class.ilGoogleMapGUI.php');
}

/**
 * Class ilLearnLocMapGUI
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version $Id:
 *
 * @ilCtrl_isCalledBy ilLearnLocCommentGUI: ilObjLearnLocGUI
 */



class ilLearnLocMapGUI {

	/**
	 * @param ilObjLearnLocGUI $parent_obj
	 */
	public  function __construct(ilObjLearnLocGUI $parent_obj) {
		global $ilTpl;
		$this->ref_id = $parent_obj->object->getId();
		$this->parent_obj = $parent_obj;
		$this->pl = new ilLearnLocPlugin();
		$this->tpl = $ilTpl;
		$this->parent_obj->tpl->setTitleIcon($this->pl->_getIcon('xlel', 'b'));
	}

	/**
	 * execute command
	 */
	public function executeCommand() {
		global $ilCtrl;

		$cmd = $ilCtrl->getCmd();
		$this->$cmd();

		return true;
	}


	public function getHTML(){

		$map = new ilGoogleMapGUI();
		$map->setMapId("xlel_map");
		$map->setWidth("200px");
		$map->setHeight("200px");
		$map->setLatitude($this->parent_obj->object->getLatitude());
		$map->setLongitude($this->parent_obj->object->getLongitude());
		$map->setZoom(15);
		$map->setEnableTypeControl(false);
		$map->setEnableNavigationControl(false);
		$map->setEnableCentralMarker(true);
		$map->setEnableUpdateListener("");

		return $map->getHTML();
	}
}
