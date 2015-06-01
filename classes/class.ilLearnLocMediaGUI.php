<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once('class.ilLearnLocMedia.php');
require_once './Services/Utilities/classes/class.ilConfirmationGUI.php';

/**
 * Class ilLearnLocMediaGUI
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 * @version           $Id:
 *
 * @ilCtrl_isCalledBy ilLearnLocCommentGUI: ilObjLearnLocGUI
 * @ilCtral_Calls     ilLearnLocMediaGUI
 */
class ilLearnLocMediaGUI {

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;


	/**
	 * @param $absolut_path
	 *
	 * @return string
	 */
	protected static function getRelativePath($absolut_path) {
		return 'data' . DIRECTORY_SEPARATOR . strstr($absolut_path, $_COOKIE['ilClientId']);
	}


	/**
	 * @param ilObjLearnLocGUI $parent_obj
	 * @param int              $id
	 */
	public function __construct(ilObjLearnLocGUI $parent_obj, $id = 0) {
		global $tpl, $ilCtrl;

		$this->id = $id;
		$this->ref_id = $parent_obj->object->getId();
		$this->parent_obj = $parent_obj;
		$this->pl = ilLearnLocPlugin::getInstance();
		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->parent_obj->tpl->setTitleIcon($this->pl->_getIcon('xlel', 'b'));

		if ($this->id) {
			$this->object = new ilLearnLocMedia($this->id);
			$this->object->read();
		}

		self::_setCssAndJs();
	}


	public function executeCommand() {
		global $ilCtrl;

		$cmd = $ilCtrl->getCmd();
		$this->$cmd();

		return true;
	}


	public function _setCssAndJs() {
		global $tpl;
		$pl = ilLearnLocPlugin::getInstance();
		$dir = $pl->getDirectory() . "/templates/";

		$tpl->addJavaScript($dir . 'js/jquery-ui-1.8.20.custom/js/jquery-1.7.2.min.js');
		$tpl->addJavaScript($dir . 'js/jquery-ui-1.8.20.custom/js/jquery-ui-1.8.20.custom.min.js');
		$tpl->addCss($dir . "js/coin-slider/coin-slider-styles.css");
		$tpl->addJavaScript($dir . "js/coin-slider/coin-slider.min.js");
		$tpl->addCss($dir . "main.css");
		$tpl->addCss($dir . "js/lightbox/css/lightbox.css");
		$tpl->addJavaScript($dir . "js/lightbox/js/lightbox.js");
	}


	/**
	 * getLearnLocGallery
	 */
	public function getOverviewHTML() {
		global $ilCtrl;
		if (!is_object($this->object)) {
			return "<div class='no_image'></div>";
		}

		self::_setCssAndJs();

		$html = $this->pl->getTemplate('tpl.gallery.html', false, true);
		$data = $this->object->getImages();

		//		var_dump($data); // FSX

		if (is_array($data) && count($data) > 0) {
			foreach ($data as $k => $img) {
				$html->setCurrentBlock("coin_image");
				$html->setVariable("XLEL_GALLERY_LINK", $ilCtrl->getLinkTarget($this->parent_obj, "showMedia"));
				$this->object->setOptions(array(
					'w' => 300,
					'h' => 200,
					'crop' => true,
					'scale' => false,
					'canvas-color' => '#ffffff',
				));

				$html->setVariable("XLEL_GALLERY_SRC", self::getRelativePath($this->object->resize($k)));
				$html->parseCurrentBlock();
			}

			return $html->get();
		} else {
			return "<div class='no_image'></div>";
		}
	}


	public function confirmDeleteImage() {
		global $ilCtrl, $lng, $tpl;

		if (!$_POST['media_ids'] OR count($_POST['media_ids']) == 0) {
			ilUtil::sendFailure($this->pl->txt('msg_ failure_no_images_selected'), true);
			$this->ctrl->redirect($this->parent_obj, 'showMedia');
		}

		$conf = new ilConfirmationGUI();
		$conf->setFormAction($ilCtrl->getFormAction($this));
		$conf->setHeaderText($this->pl->txt('confirm_delete_header'));
		foreach ($_POST['media_ids'] as $media_id) {
			$id = explode('-', $media_id);
			$this->object = new ilLearnLocMedia($id[0]);
			$this->object->read();

			$this->object->setOptions(array( 'w' => 64, 'h' => 64, 'crop' => true ));
			$conf->addItem('media_ids[]', $media_id, '<div style="float:left;"><img src="' . self::getRelativePath($this->object->resize($id[1]))
				. '"/></div>'); //<div style="float:left; margin-left:12px;">'.$this->pl->txt('confirm_delete_body').'</div>');

		}
		$conf->addHiddenItem('part', $_GET['part']);

		$conf->setConfirm($lng->txt('delete'), 'deleteImage');
		$conf->setCancel($lng->txt('cancel'), 'cancelDeleteImage');
		$tpl->setContent($conf->getHTML());
	}


	public function deleteImage() {
		global $ilCtrl;
		foreach ($_POST['media_ids'] as $media_id) {
			$id = explode('-', $media_id);
			$this->object = new ilLearnLocMedia($id[0]);
			$this->object->read();
			$this->object->removeImage($id[1]);
			if ($_POST['part'] == 'comments') {
				$ob = ilLearnLocComment::_getInstanceByMediaId($id[0]);
				$ob->setMediaId(NULL);
				$ob->update();
			}
		}

		ilUtil::sendSuccess($this->pl->txt("msg_images_deleted"), true);
		$ilCtrl->redirectByClass('ilObjLearnLocGUI', "showMedia");
	}


	public function cancelDeleteImage() {
	}


	/**
	 * @param                  $a_mobids
	 * @param                  $part
	 * @param ilObjLearnLocGUI $parent
	 *
	 * @return string
	 */
	public static function getGalleryHTML($a_mobids, $part, ilObjLearnLocGUI $parent = NULL) {
		if (count($a_mobids) == 0) {
			return "";
		}
		global $ilCtrl, $ilAccess;
		$pl = ilLearnLocPlugin::getInstance();
		require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/LearnLoc/classes/Block/class.ilLearnLocBlockGUI.php');

		$block = new ilLearnLocBlockGUI();
		$block->setTitle($pl->txt('block_title_' . $part));

		self::_setCssAndJs();

		$sz = 200;
		$wz = 300;
		$bsz = 960;

		$images_small = array();
		$images_big = array();

		$checkAccess = $ilAccess->checkAccess('write', '', $parent->object->getRefId());
		$deletable = false;
		if ($checkAccess AND ($part == 'init' OR $part == 'comments2')) {
			$deletable = true;
		}
		$del_img = array();
		$first = true;

		foreach ($a_mobids as $media) {
			$obj = new ilLearnLocMedia($media);
			$images = $obj->getImages();
			if (is_array($images) AND count($images) > 0) {
				foreach ($images as $k => $img) {
					$obj->setOptions(array(
						'w' => $wz,
						'h' => $sz,
						'crop' => false,
						'scale' => false,
						'canvas-color' => '#ffffff'
					));

					$key = $media . '-' . $k;
					$images_small[$key] = $obj->resize($k);
					$obj->setOptions(array(
						'w' => $bsz,
						'h' => $bsz,
						'crop' => false,
						'scale' => true,
						'canvas-color' => '#ffffff'
					));
					$images_big[$key] = $obj->resize($k);
					if ($media == $parent->object->getInitMobId() AND $first) {
						$del_img[$key] = false;
						$first = false;
					} else {
						$del_img[$key] = $deletable;
					}
				}
			} else {
				return '';
			}
		}
		$sorthtml = $pl->getTemplate("tpl.media_gallery.html", true, true);

		foreach ($images_small as $k => $image) {
			$sorthtml->touchBlock('movable_block');
			$sorthtml->setVariable('SRC', self::getRelativePath($images_small[$k]));
			$sorthtml->setVariable('SRC2', self::getRelativePath($images_big[$k]));
			$sorthtml->setVariable('LIGHTBOX', $part);
			$sorthtml->setVariable('SZ', $sz);
			$sorthtml->setVariable('WZ', $wz);
			$sorthtml->setVariable('VALUE', $k);
			if ($del_img[$k]) {
				$sorthtml->setVariable('IMG_ID', $k);
			}
		}

		if ($deletable AND count($images_small) > 1) {
			$ilCtrl->setParameterByClass('ilLearnLocMediaGUI', 'part', $part);
			$sorthtml->setVariable('BUTTON_NEW', $pl->txt('delete_images'));
			$sorthtml->setVariable('FORM_TARGET', $ilCtrl->getFormActionByClass('ilLearnLocMediaGUI', 'confirmDeleteImage'));
		}

		$block->setContentHtml($sorthtml->get());

		return $block->getHTML();
	}


	public function confirmDelete() {
	}
}
