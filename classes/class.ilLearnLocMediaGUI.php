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


	/**
	 * @deprecated
	 */
	public function _setCssAndJs() {
	}


	/**
	 * getLearnLocGallery
	 */
	public function getOverviewHTML() {

		if (! is_object($this->object)) {
			return "<div class='no_image'></div>";
		}

		require_once('./Services/MediaObjects/classes/class.ilMediaPlayerGUI.php');

		$data = $this->object->getImages();

		$ilMediaPlayerGUI = new ilMediaPlayerGUI();
		if (is_array($data) && count($data) > 0) {

			$this->object->setOptions(array(
				'w' => 900,
				'h' => 600,
				'crop' => true,
				'scale' => false,
				'canvas-color' => '#ffffff',
			));

			$ilMediaPlayerGUI->setFile(self::getRelativePath($this->object->resizeFirstImage()));

			return $ilMediaPlayerGUI->getPreviewHtml();
		} else {
			return "<div class='no_image'></div>";
		}
	}


	public function confirmDeleteImage() {
		global $ilCtrl, $lng, $tpl;

		if (! $_POST['media_ids'] OR count($_POST['media_ids']) == 0) {
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

			$this->object->setOptions(array(
				'w' => 64,
				'h' => 64,
				'crop' => true
			));
			$conf->addItem('media_ids[]', $media_id, '<div style="float:left;"><img src="' . self::getRelativePath($this->object->resize($id[1]))
				. '"/></div>'); //<div style="float:left; margin-left:12px;">'.$this->pl->txt('confirm_delete_body').'</div>');

		}
		$conf->addHiddenItem('part', $_GET['part']);

		$conf->setConfirm($lng->txt('common_delete'), 'deleteImage');
		$conf->setCancel($lng->txt('common_cancel'), 'cancelDeleteImage');
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

		require_once('./Services/MediaObjects/classes/class.ilMediaPlayerGUI.php');

		$html = '';
		foreach ($images_small as $k => $image) {
			$ilMediaPlayerGUI = new ilMediaPlayerGUI();
			$ilMediaPlayerGUI->setVideoPreviewPic(self::getRelativePath($images_small[$k]));
			$ilMediaPlayerGUI->setFile(self::getRelativePath($images_big[$k]));

			$html .= $ilMediaPlayerGUI->getPreviewHtml();

			if ($del_img[$k]) {
				$sorthtml->setVariable('IMG_ID', $k);
			}
		}

		if ($deletable AND count($images_small) > 1) {
			$ilCtrl->setParameterByClass('ilLearnLocMediaGUI', 'part', $part);
			$sorthtml->setCurrentBlock('delete_picture');
			$sorthtml->setVariable('BUTTON_NEW', $pl->txt('delete_images'));
			$sorthtml->setVariable('FORM_TARGET', $ilCtrl->getFormActionByClass('ilLearnLocMediaGUI', 'confirmDeleteImage'));
			$sorthtml->parseCurrentBlock();
		}

		$block->setContentHtml($sorthtml->get());
		$block->setContentHtml($html);

		return $block->getHTML();
	}


	public function confirmDelete() {
	}
}
