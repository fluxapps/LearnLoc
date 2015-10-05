<?php
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/LearnLoc/classes/class.ilLearnLocCommentGUI.php');

/**
 * Class xlelCommentRenderGUI
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class xlelCommentRenderGUI {

	const SIZE = 32;
	/**
	 * @var ilLearnLocComment[]
	 */
	protected $comments = array();


	/**
	 * xlelCommentRenderGUI constructor.
	 *
	 * @param ilLearnLocComment[] $comments
	 */
	public function __construct(array $comments = array()) {
		$this->comments = $comments;
		$this->pl = ilLearnLocPlugin::getInstance();
	}


	/**
	 * @return string
	 */
	public function getHTML() {
		$tpl = ilLearnLocPlugin::getInstance()->getTemplate("ilias5/tpl.comments.html", true, true);
		global $ilCtrl, $ilUser;
		/**
		 * @var $ilCtrl ilCtrl
		 */
		require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/LearnLoc/classes/Form/class.xlelIconButton.php');
		$toolbar = new ilToolbarGUI();
		$b = ilLinkButton::getInstance();
		$b->setCaption('rep_robj_xlel_new_comment');
		$b->setUrl($ilCtrl->getLinkTargetByClass('ilLearnLocCommentGUI', 'addComment'));
		$toolbar->addButtonInstance($b);
		$tpl->setVariable('TOOLBAR', $toolbar->getHTML());
		foreach ($this->getComments() as $comment) {
			$tpl->touchBlock('comment');
			$tpl->setVariable('TITLE', $comment->getTitle());
			$tpl->setVariable('BODY', $comment->getBody());
			$ilCtrl->setParameterByClass('ilLearnLocCommentGUI', 'comment_id', $comment->getId());

			$b = xlelIconButton::getInstance();
			$b->setIcon('share-alt');
			$b->setUrl($ilCtrl->getLinkTargetByClass('ilLearnLocCommentGUI', 'addComment'));
			$tpl->setVariable('BUTTON_RESPONSE', $b->render());

			if ($ilUser->getId() == $comment->getUserId()) {
				$b = xlelIconButton::getInstance();
				$b->setIcon('remove');
				$b->setUrl($ilCtrl->getLinkTargetByClass('ilLearnLocCommentGUI', 'delete'));
				$tpl->setVariable('BUTTON_DELETE', $b->render());
			}
			if ($comment->getMediaId()) {
				$img = new ilLearnLocMedia($comment->getMediaId());
				$img->setOptions(array(
					'w' => self::SIZE,
					'h' => self::SIZE,
					'crop' => true,
					'scale' => false,
					'canvas-color' => '#ffffff'
				));
				$tpl->setVariable('IMG_SRC', $img->getFirstImageForImgTag());
			}

			if ($comment->hasChildren()) {
				foreach ($comment->getChildren() as $child) {
					if ($child->getMediaId()) {
						$img = new ilLearnLocMedia($child->getMediaId());
						$img->setOptions(array(
							'w' => self::SIZE,
							'h' => self::SIZE,
							'crop' => true,
							'scale' => false,
							'canvas-color' => '#ffffff'
						));
						$tpl->setVariable('RESPONSE_IMG_SRC', $img->getFirstImageForImgTag());
					}
					if ($ilUser->getId() == $child->getUserId()) {
						$ilCtrl->setParameterByClass('ilLearnLocCommentGUI', 'comment_id', $child->getId());
						$b = xlelIconButton::getInstance();
						$b->setIcon('remove');
						$b->setUrl($ilCtrl->getLinkTargetByClass('ilLearnLocCommentGUI', 'confirmDeleteComment'));
						$tpl->setVariable('BUTTON_DELETE_CHILD', $b->render());
					}

					//					$tpl->touchBlock('response');
					$tpl->setVariable('RESPONSE_TITLE', $child->getTitle());
					$tpl->setVariable('RESPONSE_BODY', $child->getBody());
				}
			} else {
				//				$tpl->touchBlock('response');
			}
		}

		return $tpl->get();
	}


	/**
	 * @param bool $parent
	 *
	 * @return string
	 */
	public function getCommentForm($parent = false) {
		global $ilCtrl, $tpl;

		require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
		$cmform = new ilPropertyFormGUI();

		$cmform->setTableWidth('100%');

		// Title
		$ti = new ilTextInputGUI($this->pl->txt("title"), "title");
		$ti->setRequired(true);
		$cmform->addItem($ti);

		// Description
		$ta = new ilTextAreaInputGUI($this->pl->txt("body"), "body");
		$ta->setRequired(true);
		$cmform->addItem($ta);

		if ($parent) {
			$hi = new ilHiddenInputGUI("parent_id");
			$hi->setValue($parent);
			$cmform->addItem($hi);
		}

		$imgs = new ilImageFileInputGUI($this->pl->txt("image"), "image");
		$imgs->setSuffixes(array(
			"jpg",
			"jpeg"
		));
		$cmform->addItem($imgs);

		$cmform->addCommandButton("saveComment", $this->pl->txt("save_new_comment"));

		$cmform->setTitle($this->pl->txt("new_comment"));
		$cmform->setFormAction($ilCtrl->getFormAction($this));

		return $cmform->getHTML();
	}


	/**
	 * @return ilLearnLocComment[]
	 */
	public function getComments() {
		return $this->comments;
	}


	/**
	 * @param ilLearnLocComment[] $comments
	 */
	public function setComments($comments) {
		$this->comments = $comments;
	}
}

?>
