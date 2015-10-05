<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once('class.ilLearnLocComment.php');
//require_once('class.ilLearnLocPlugin.php');
require_once('./Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php');
require_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
require_once('class.ilLearnLocMedia.php');

/**
 * Class ilLearnLocCommentGUI
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 * @version           $Id:
 *
 * @ilCtrl_isCalledBy ilLearnLocCommentGUI: ilObjLearnLocGUI
 */
class ilLearnLocCommentGUI {

	/**
	 * @var int
	 */
	protected $ref_id;
	/**
	 * @var array
	 */
	protected $comments;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;


	/**
	 * @param ilObjLearnLocGUI $parent_obj
	 */
	public function __construct(ilObjLearnLocGUI $parent_obj) {
		global $ilTpl, $ilCtrl;

		$this->ref_id = $parent_obj->object->getId();
		$this->parent_obj = $parent_obj;
		$this->pl = ilLearnLocPlugin::getInstance();
		$this->ctrl = $ilCtrl;
		$this->tpl = $ilTpl;
		$this->comments = ilLearnLocComment::_getAllForRefId($this->ref_id);
		$this->parent_obj->tpl->setTitleIcon($this->pl->_getIcon('xlel', 'b'));
	}


	public function executeCommand() {
		global $ilCtrl;

		$cmd = $ilCtrl->getCmd();
		$this->$cmd();

		return true;
	}


	public function confirmDeleteComment() {
		global $ilCtrl, $lng, $tpl, $ilUser;

		$conf = new ilConfirmationGUI();
		$conf->setFormAction($ilCtrl->getFormAction($this));
		$conf->setHeaderText($this->pl->txt('confirm_delete_header'));
		$newComment = new ilLearnLocComment($_GET['comment_id']);
		if ($ilUser->getId() != $newComment->getUserId()) {
			ilUtil::sendFailure('Access denied', true);
			/**
			 * @var $ilCtrl ilCtrl
			 */
			$ilCtrl->redirect($this->parent_obj);
		}
		$conf->addItem('comment_id', $_GET['comment_id'], $newComment->getTitle() . ': ' . $newComment->getBody());

		$conf->setConfirm($lng->txt('delete'), 'deleteComment');
		$conf->setCancel($lng->txt('cancel'), 'cancelDeleteComment');

		$tpl->setContent($conf->getHTML());
	}


	public function saveComment() {
		global $ilUser, $ilCtrl;
		$this->getInitCommentForm();

		if ($this->cmform->checkInput($_POST['parent_id'])) {
			$newComment = new ilLearnLocComment();
			if ($_FILES['image']['name']) {
				$mo = new ilLearnLocMedia();
				$mo->setTitle('LearnLocCommentMedia');
				$mo->create($_GET['ref_id'], true);
				$mo->setFile($_FILES);
				$mo->addImage();
				$newComment->setMediaId($mo->getId());
			}
			$newComment->setRefId($this->ref_id);
			$newComment->setParentId($this->cmform->getInput('parent_id'));
			$newComment->setUserId($ilUser->getId());
			$newComment->setTitle($this->cmform->getInput('title'));
			//$newComment->setDescreption($this->cmform->getInput('description'));
			$newComment->setBody($this->cmform->getInput('body'));
			$newComment->setCreationDate(time());
			$newComment->create();

			ilUtil::sendSuccess($this->pl->txt("comment_saved"));
			$ilCtrl->redirect($this->parent_obj, "");
		}
		$this->cmform->setValuesByPost();
	}


	public function deleteComment() {
		global $ilCtrl, $ilUser;

		$comment = new ilLearnLocComment($_POST['comment_id']);
		if ($ilUser->getId() != $comment->getUserId()) {
			ilUtil::sendFailure('Access denied', true);
			/**
			 * @var $ilCtrl ilCtrl
			 */
			$ilCtrl->redirect($this->parent_obj);
		}
		$comment->delete();
		ilUtil::sendSuccess($this->pl->txt('comment_deleted'));
		$ilCtrl->redirect($this->parent_obj);
	}


	public function cancelDeleteComment() {
		global $ilCtrl;
		$ilCtrl->redirect($this->parent_obj);
	}


	public function addComment() {
		global $tpl;
		$tpl->setContent($this->getInitCommentForm($_GET['comment_id']));
	}



	/**
	 * @param bool $parent
	 *
	 * @return string
	 */
	public function getInitCommentForm($parent = false) {
		global $ilCtrl, $tpl;

		require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
		$this->cmform = new ilPropertyFormGUI();

		$this->cmform->setTableWidth('100%');

		// Title
		$ti = new ilTextInputGUI($this->pl->txt("title"), "title");
		$ti->setRequired(true);
		$this->cmform->addItem($ti);

		// Description
		$ta = new ilTextAreaInputGUI($this->pl->txt("body"), "body");
		$ta->setRequired(true);
		$this->cmform->addItem($ta);

		if ($parent) {
			$hi = new ilHiddenInputGUI("parent_id");
			$hi->setValue($parent);
			$this->cmform->addItem($hi);
		}

		$imgs = new ilImageFileInputGUI($this->pl->txt("image"), "image");
		$imgs->setSuffixes(array(
			"jpg",
			"jpeg"
		));
		$this->cmform->addItem($imgs);

		$this->cmform->addCommandButton("saveComment", $this->pl->txt("save_new_comment"));

		$this->cmform->setTitle($this->pl->txt("new_comment"));
		$this->cmform->setFormAction($ilCtrl->getFormAction($this));

		return $this->cmform->getHTML();
	}


	/**
	 * @return string
	 */
	public function getHTML() {
		require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/LearnLoc/classes/Block/class.ilLearnLocBlockGUI.php');
		$block = new ilLearnLocBlockGUI();
		$block->setTitle($this->pl->txt('comments'));

		require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/LearnLoc/classes/Comments/class.xlelCommentRenderGUI.php');
		//
		//		$this->html = $this->pl->getTemplate("tpl.com_row.html", true, false);
		//		$this->html->setVariable("XLEL_COMMENTLIST_TITEL", $this->pl->txt('comments'));
		//
		//		if (count($this->comments) > 0) {
		//			foreach ($this->comments as $comment) {
		//				$this->setCommentRow($comment);
		//				if (is_array($comment->children)) {
		//					foreach ($comment->children as $child) {
		//						$this->setCommentRow($child, true);
		//					}
		//					$this->html->touchBlock("xlel_thread");
		//				}
		//			}
		//		} else {
		//			$this->html->setCurrentBlock("xlel_comment_noitem");
		//			$this->html->setVariable("XLEL_COMMENT_NOITEM", $this->parent_obj->object->lng->txt('rep_robj_xlel_no_items'));
		//			$this->html->parseCurrentBlock();
		//		}

		$xlelCommentRenderGUI = new xlelCommentRenderGUI($this->comments);
		$block->setContentHtml($xlelCommentRenderGUI->getHTML());
		$block->addHeaderCommand($this->ctrl->getLinkTarget($this, 'addComment'), $this->pl->txt('new_comment'));

		return $block->getHTML();
	}
}