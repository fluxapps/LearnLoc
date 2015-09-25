<?php

/**
 * Class xlelCommentRenderGUI
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class xlelCommentRenderGUI {

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
	}


	/**
	 * @return string
	 */
	public function getHTML() {
		$tpl = ilLearnLocPlugin::getInstance()->getTemplate("ilias5/tpl.comments.html", true, true);

		foreach ($this->getComments() as $comment) {
			$tpl->touchBlock('comment');
			$tpl->setVariable('TITLE', $comment->getTitle());
			$tpl->setVariable('BODY', $comment->getBody());

			if ($comment->getMediaId()) {
				$img = new ilLearnLocMedia($comment->getMediaId());
				$img->setOptions(array(
					'w' => 64,
					'h' => 64,
					'crop' => true,
					'scale' => false,
					'canvas-color' => '#ffffff'
				));
				$tpl->setVariable('IMG_SRC', $img->getFirstImageForImgTag());
			}

			if ($comment->hasChildren()) {
				foreach ($comment->getChildren() as $child) {
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
