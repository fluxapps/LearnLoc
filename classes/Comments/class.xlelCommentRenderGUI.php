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
		global $tpl;
		$tpl->addCss('./Customizing/global/plugins/Services/Repository/RepositoryObject/LearnLoc/templates/default.css');

		$template = ilLearnLocPlugin::getInstance()->getTemplate("ilias5/tpl.comments.html", true, true);
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
		$template->setVariable('TOOLBAR', $toolbar->getHTML());
		foreach ($this->getComments() as $comment) {
			$template->touchBlock('comment');
			$template->setVariable('TITLE', $comment->getTitle());
			$template->setVariable('USER', ilObjUser::_lookupLogin($comment->getUserId()));
			$template->setVariable('DATE', $comment->getCreationDate());
			$template->setVariable('BODY', $comment->getBody());
			$ilCtrl->setParameterByClass('ilLearnLocCommentGUI', 'comment_id', $comment->getId());

			$b = xlelIconButton::getInstance();
			$b->setIcon('share-alt');
			$b->setUrl($ilCtrl->getLinkTargetByClass('ilLearnLocCommentGUI', 'addComment'));
			$template->setVariable('BUTTON_RESPONSE', $b->render());

			if ($ilUser->getId() == $comment->getUserId()) {
				$b = xlelIconButton::getInstance();
				$b->setIcon('remove');
				$b->setUrl($ilCtrl->getLinkTargetByClass('ilLearnLocCommentGUI', 'confirmDeleteComment'));
				$template->setVariable('BUTTON_DELETE', $b->render());
			}
			if ($comment->getMediaId()) {
				$img = new ilLearnLocMedia($comment->getMediaId());
				$img->setOptions(array(
					'w'            => self::SIZE,
					'h'            => self::SIZE,
					'crop'         => true,
					'scale'        => false,
					'canvas-color' => '#ffffff',
				));
				$template->setVariable('IMG_SRC', $img->getFirstImageForImgTag());
			} else {
				$template->setVariable('IMG_SRC', './Customizing/global/plugins/Services/Repository/RepositoryObject/LearnLoc/templates/images/placeholder.png');
			}

			if ($comment->hasChildren()) {
				$count = count($comment->getChildren());
				foreach ($comment->getChildren() as $i => $child) {
					if ($child->getMediaId()) {
						$img = new ilLearnLocMedia($child->getMediaId());
						$img->setOptions(array(
							'w'            => self::SIZE,
							'h'            => self::SIZE,
							'crop'         => true,
							'scale'        => false,
							'canvas-color' => '#ffffff',
						));
						$template->setVariable('RESPONSE_IMG_SRC', $img->getFirstImageForImgTag());
					} else {
						$template->setVariable('RESPONSE_IMG_SRC', './Customizing/global/plugins/Services/Repository/RepositoryObject/LearnLoc/templates/images/placeholder.png');
					}
					if ($ilUser->getId() == $child->getUserId()) {
						$ilCtrl->setParameterByClass('ilLearnLocCommentGUI', 'comment_id', $child->getId());
						$b = xlelIconButton::getInstance();
						$b->setIcon('remove');
						$b->setUrl($ilCtrl->getLinkTargetByClass('ilLearnLocCommentGUI', 'confirmDeleteComment'));
						$template->setVariable('BUTTON_DELETE_CHILD', $b->render());
					}

					$template->setVariable('RESPONSE_TITLE', $child->getTitle());
					$template->setVariable('RESPONSE_BODY', $child->getBody());
					$template->setVariable('RESPONSE_USER', ilObjUser::_lookupLogin($child->getUserId()));
					$template->setVariable('RESPONSE_DATE', $child->getCreationDate());
					if ($i < ($count - 1)) {
						$template->touchBlock('response');
					}
				}
			} else {
				//				$tpl->touchBlock('response');
			}
		}

		return $template->get();
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
		$ti = new ilTextInputGUI($this->pl->txt("common_title"), "title");
		$ti->setRequired(true);
		$cmform->addItem($ti);

		// Description
		$ta = new ilTextAreaInputGUI($this->pl->txt("common_body"), "body");
		$ta->setRequired(true);
		$cmform->addItem($ta);

		if ($parent) {
			$hi = new ilHiddenInputGUI("parent_id");
			$hi->setValue($parent);
			$cmform->addItem($hi);
		}

		$imgs = new ilImageFileInputGUI($this->pl->txt("common_image"), "image");
		$imgs->setSuffixes(array(
			"jpg",
			"jpeg",
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
