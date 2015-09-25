<?php
@include_once('./classes/class.ilLink.php');
@include_once('./Services/Link/classes/class.ilLink.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/LearnLoc/classes/Folder/class.ilLearnLocFolder.php');


/**
 * Class ilLearnLocFoldObj
 *
 * @author       Fabian Schmid <fs@studer-raimann.ch>
 * @version      $Id$
 *
 * @ilCtrl_Calls ilLearnLocFolderGUI: ilObjFileGUI, ilObjFolderGUI
 */
class ilLearnLocFolderGUI {

	/**
	 * @param ilObjLearnLocGUI $a_parent
	 * @param int              $a_id
	 */
	function __construct(ilObjLearnLocGUI $a_parent, $a_id = 0) {
		$this->object = new ilLearnLocFolder($a_parent->object, $a_id);
		$this->parent_obj = $a_parent;
		$this->id = $a_id;
		$this->pl = new ilLearnLocPlugin();
	}


	public function getHTML() {
		global $ilAccess;

		require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/LearnLoc/classes/Block/class.ilLearnLocBlockGUI.php');
		$block = new ilLearnLocBlockGUI();
		$block->setTitle($this->pl->txt('folder_content'));

		$html = $this->pl->getTemplate('tpl.mat_list.html', false, true);
		//		$html->setVariable("XLEL_MATLIST_TITEL", $this->pl->txt('folder_content'));
		$childs = $this->getChildrenList();

		if (count($childs) > 0) {
			foreach ($childs as $mat) {
				$mat = (object)$mat;
				$html->setCurrentBlock("matlist_row");
				$html->setVariable("XLEL_FILE", $mat->title);
				$html->setVariable("XLEL_FILE_LINK", ilLink::_getStaticLink($mat->ref_id, $mat->type));
				if (file_exists(ilUtil::getImagePath("icon_" . $mat->type . ".svg"))) {
					$icon = ilUtil::getImagePath("icon_" . $mat->type . ".svg");
				} else {
					$icon = ilUtil::getImagePath("icon_" . $mat->type . ".png");
				}

				$html->setVariable("XLEL_FILE_ICON", $icon);
				$html->setVariable("DESC", $mat->description);
				$html->parseCurrentBlock();
			}
		}
		if ($ilAccess->checkAccess("write", "", $this->parent_obj->object->getRefId())) {
			$html->setCurrentBlock("no_items");
			$html->setVariable("XLEL_FILE", $this->parent_obj->lng->txt('rep_robj_xlel_add_new_items'));
			$html->setVariable("XLEL_FILE_LINK", ilLink::_getStaticLink($this->id, "fold"));
			$html->parseCurrentBlock();
		}
		return $html->get();

		$block->setContentHtml($html->get());

		return $block->getHTML();
	}


	//
	// Helper
	//

	public function getChildrenList() {
		$subitems = $this->object->getSubItems($this->object->getRefId());

		return (array)$subitems['_all'];
	}
}

?>
