<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

require_once('./Services/Repository/classes/class.ilObjectPluginGUI.php');
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('class.ilObjLearnLoc.php');
require_once('class.ilLearnLocCommentGUI.php');
require_once('class.ilLearnLocMapGUI.php');
require_once('class.ilLearnLocMediaGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/LearnLoc/classes/Folder/class.ilLearnLocFolderGUI.php');
require_once('class.ilLearnLocExportGUI.php');
require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/LearnLoc/classes/VisitDependency/class.ilLearnLocDependencyGUI.php");

/**
 * User Interface class for LearnLoc repository object.
 *
 * User interface classes process GET and POST parameter and call
 * application classes to fulfill certain tasks.
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 *
 * $Id$
 *
 * Integration into control structure:
 * - The GUI class is called by ilRepositoryGUI
 * - GUI classes used by this class are ilPermissionGUI (provides the rbac
 *   screens) and ilInfoScreenGUI (handles the info screen).
 *
 * @ilCtrl_isCalledBy ilObjLearnLocGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls      ilObjLearnLocGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI, ilLearnLocMediaGUI,
 *                    ilLearnLocMediaGUI, ilLearnLocDependencyGUI
 *
 */
class ilObjLearnLocGUI extends ilObjectPluginGUI {

	/**
	 * @var ilPropertyFormGUI
	 */
	protected $form;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var HTML_Template_ITX|ilTemplate
	 */
	public $tpl;
	/**
	 * @var ilObjLearnLoc
	 */
	public $object;
	/**
	 * @var $ilTabsGUI
	 */
	protected $tabs;


	/**
	 * @param int $a_ref_id
	 * @param int $a_id_type
	 * @param int $a_parent_node_id
	 */
	public function __construct($a_ref_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0) {
		global $tpl, $ilCtrl, $ilTabs;
		parent::__construct($a_ref_id, $a_id_type, $a_parent_node_id);
		$this->pl = ilLearnLocPlugin::getInstance();
		//		$this->pl->updateLanguageFiles();
		$this->ctrl = $ilCtrl;
		$this->tpl = $tpl;
		$this->tabs = $ilTabs;
	}


	/**
	 * @return string
	 */
	final function getType() {
		return 'xlel';
	}


	/**
	 * @return bool|void
	 */
	public function executeCommand() {

		switch ($this->ctrl->getNextClass($this)) {
			case 'illearnloccommentgui':
				$this->prepareOutput();
				$this->tabs->setTabActive('content');
				$cgui = new ilLearnLocCommentGUI($this);
				$this->ctrl->forwardCommand($cgui);
				$this->tpl->show();
				break;

			case 'illearnlocexportgui':
				$this->prepareOutput();
				$this->tabs->setTabActive('export_arcgis');
				$cgui = new ilLearnLocExportGUI($this);
				$this->ctrl->forwardCommand($cgui);
				$this->tpl->show();
				break;

			case 'illearnlocmediagui':
				$this->prepareOutput();
				$this->tabs->setTabActive('media');
				$id = explode('-', ($_POST['media_id'] ? $_POST['media_id'] : $_GET['media_id']));
				$mgui = new ilLearnLocMediaGUI($this, $id[0]);
				$this->ctrl->forwardCommand($mgui);
				$this->tpl->show();
				break;

			case 'illearnlocdependencygui':
				$this->prepareOutput();
				$this->tabs->setTabActive('dependency');
				$gui = new ilLearnLocDependencyGUI($this->ref_id);
				$this->ctrl->forwardCommand($gui);
				$this->tpl->show();
				break;
			default:
				return parent::executeCommand();
		}

		return true;
	}


	/**
	 * @param $cmd
	 */
	function performCommand($cmd) {
		switch ($cmd) {
			case "editProperties":
			case "updateProperties":
				$this->checkPermission("write");
				$this->$cmd();
				break;

			case "showContent":
			case "showMedia":
			case "saveComment":
				$this->checkPermission("read");
				$this->$cmd();
				break;
		}
	}


	/**
	 * @return string
	 */
	public function getAfterCreationCmd() {
		return "editProperties";
	}


	/**
	 * Get standard command
	 */
	function getStandardCmd() {
		return "showContent";
	}

	//
	// DISPLAY TABS
	//

	public function setTabs() {
		global $ilAccess;
		/**
		 * @var $ilAccess ilAccessHandler
		 */
		require_once('include/inc.ilias_version.php');
		require_once('Services/Component/classes/class.ilComponent.php');

		if ($ilAccess->checkAccess('read', '', $this->object->getRefId())) {
			$this->tabs->addTab('content', $this->txt('common_content'), $this->ctrl->getLinkTarget($this, 'showContent'));
			$this->tabs->addTab('media', $this->txt('common_media'), $this->ctrl->getLinkTarget($this, 'showMedia'));
		}

		if ($ilAccess->checkAccess('write', '', $this->object->getRefId())
		    AND ilComponent::isVersionGreaterString(ILIAS_VERSION_NUMERIC, '4.2.999')
		) {
			$this->tabs->addTab('export_arcgis', $this->txt('export_arcgis'), $this->ctrl->getLinkTargetByClass('ilLearnLocExportGUI', 'listExportFiles'));
		}

		if ($ilAccess->checkAccess('write', '', $this->object->getRefId())) {
			$this->tabs->addTab('properties', $this->txt('common_properties'), $this->ctrl->getLinkTarget($this, 'editProperties'));
		}
		require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/LearnLoc/classes/Config/class.xlelConfig.php');
		if (xlelConfig::getWithName(xlelConfig::F_DEPENDENCIES) && $ilAccess->checkAccess('write', '', $this->object->getRefId())) {
			$this->tabs->addTab('dependency', $this->txt('common_dependencies'), $this->ctrl->getLinkTargetByClass('ilLearnLocDependencyGUI', 'show'));
		}

		$this->addInfoTab();
		$this->addPermissionTab();
	}

	//
	// Edit properties form
	//

	protected function editProperties() {

		$this->tabs->activateTab("properties");
		$this->initPropertiesForm();
		$this->setPropertiesValues();
		$this->tpl->setContent($this->form->getHTML());
	}


	/**
	 * @param string $a_mode
	 */
	public function initPropertiesForm($a_mode = 'create') {
		global $ilCtrl;

		$this->form = new ilPropertyFormGUI();

		// title
		$ti = new ilTextInputGUI($this->txt("common_title"), "title");
		$ti->setRequired(true);
		$this->form->addItem($ti);

		// description
		$ta = new ilTextAreaInputGUI($this->txt("common_description"), "desc");
		$this->form->addItem($ta);

		// online
		$cb = new ilCheckboxInputGUI($this->txt("common_online"), "online");
		$this->form->addItem($cb);

		// location property
		$this->lng->loadLanguageModule("gmaps");

		$loc_prop = new ilLocationInputGUI($this->txt("location_selector"), "location");
		$loc_prop->setZoom(10);
		$this->form->addItem($loc_prop);

		// Image
		$imgs = new ilImageFileInputGUI($this->txt("init_mob_id_selector"), "image");
		$imgs->setSuffixes(array(
			"jpg",
			"jpeg",
			'png',
		));
		$this->form->addItem($imgs);

		$this->form->addCommandButton("updateProperties", $this->txt("common_save"));

		$this->form->setTitle($this->txt("edit_properties"));
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	}


	/**
	 *
	 */
	protected function setPropertiesValues() {
		if (!$this->form) {
			return false;
		}
		$values["title"] = $this->object->getTitle();
		$values["desc"] = $this->object->getLongDescription();
		$values["online"] = $this->object->getOnline();
		$values["location"]["latitude"] = $this->object->getLatitude();
		$values["location"]["longitude"] = $this->object->getLongitude();
		$values["location"]["zoom"] = $this->object->getElevation();
		$values["address"] = $this->object->getAddress();
		$values["init_mob_id"] = $this->object->getInitMobId();

		$this->form->setValuesByArray($values);
	}


	public function updateProperties() {
		global $tpl, $lng, $ilCtrl;

		if ($_FILES['image']['name']) {
			if (!$this->object->getInitMobId()) {
				$mob = new ilLearnLocMedia();
				$mob->setTitle('lelinitmob');
				$mob->create($_GET['ref_id'], true);
				$mob->setFile($_FILES);
				$mob->addImage();
				$mob_id = $mob->getId();
			} else {
				$mob = new ilLearnLocMedia($this->object->getInitMobId());
				$mob->setFile($_FILES);
				$mob->addImage();
				$mob_id = $mob->getId();
			}
		}

		$this->initPropertiesForm();
		if ($this->form->checkInput()) {
			$this->object->setTitle($this->form->getInput("title"));
			$this->object->setDescription($this->form->getInput("desc"));
			$this->object->setOnline($this->form->getInput("online"));
			$location = $this->form->getInput("location");
			$this->object->setLatitude((float)$location['latitude']);
			$this->object->setLongitude((float)$location['longitude']);
			$this->object->setElevation((int)$location['zoom']);
			$this->object->setAddress($this->form->getInput("address"));
			if ($mob_id) {
				$this->object->setInitMobId($mob_id);
			}

			$this->object->update();
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "editProperties");
		}

		$this->form->setValuesByPost();
		$tpl->setContent($this->form->getHtml());
	}

	//
	// Show content
	//

	protected function showContent() {
		$html = $this->pl->getTemplate('tpl.lel_main.html', false, false);
		$this->tpl->addCss($this->pl->getDirectory() . "/templates/main.css");
		$this->tabs->activateTab("content");

		// GUIS
		//		$new_comments = new ilLearnLocCommentGUI($this);
		$new_map = new ilLearnLocMapGUI($this);
		$new_gallery = new ilLearnLocMediaGUI($this, $this->object->getInitMobId());
		$folder = new ilLearnLocFolderGUI($this, $this->object->getContainerId());

		require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/LearnLoc/classes/Comments/class.xlelCommentRenderGUI.php');
		$xlelCommentRenderGUI = new xlelCommentRenderGUI();
		$comments = ilLearnLocComment::_getAllForRefId($this->obj_id);
		$xlelCommentRenderGUI->setComments($comments);

		// Set Content
		$html->setVariable("LEL_CONTENT", $folder->getHTML()); // ok
		$html->setVariable("LEL_COMMENTS", $xlelCommentRenderGUI->getHTML()); //ok
		$html->setVariable("LEL_MAP", $new_map->getHTML()); // ok
		$html->setVariable("LEL_GALLERY", $new_gallery->getOverviewHTML()); // ok
		$html->setVariable("LEL_TITLE", $this->object->getTitle()); //ok
		$html->setVariable("LEL_DESC", $this->object->getLongDescription()); //ok

		$this->tpl->setContent($html->get());
	}


	protected function showMedia() {
		global $ilAccess;
		$this->tabs->activateTab("media");

		//		if ($ilAccess->checkAccess("write", "", $this->object->getRefId() . rand(99999999, 1000000))) {
		//			$this->tpl->addJavaScript("./Customizing/global/plugins/Services/Repository/RepositoryObject/LearnLoc/templates/default/movable.js", 3, true);
		//			$this->admin = true;
		//		}

		$gal1 = ilLearnLocMediaGUI::getGalleryHTML(array( $this->object->getInitMobId() ), 'init', $this);
		$gal2 = ilLearnLocMediaGUI::getGalleryHTML(ilLearnLocComment::_getAllMediaForRefId($this->object->getId()), 'comments', $this);

		$this->tpl->setContent($gal1 . $gal2);
	}


	protected function initCreationForms($a_new_type) {
		$forms = array(
			self::CFORM_NEW => $this->initCreateForm($a_new_type),
			// self::CFORM_IMPORT => $this->initImportForm($a_new_type),
			//			self::CFORM_CLONE => $this->fillCloneTemplate(null, $a_new_type)
		);

		return $forms;
	}
}
