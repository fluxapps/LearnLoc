<?php
require_once('./Services/Block/classes/class.ilBlockGUI.php');

/**
 * Class ilLearnLocBlockGUI
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilLearnLocBlockGUI extends ilBlockGUI {

	/**
	 * @var string
	 */
	protected $content_html = '';


	/**
	 * @return string
	 */
	static function getBlockType() {
		return 'xlel_b';
	}


	/**
	 * @return bool
	 */
	static function isRepositoryObject() {
		return false;
	}


	/**
	 * @return string
	 */
	public function getContentHtml() {
		return $this->content_html;
	}


	/**
	 * @param string $content_html
	 */
	public function setContentHtml($content_html) {
		$this->content_html = $content_html;
	}


	public function fillDataSection() {
		$this->setDataSection($this->getContentHtml());
	}
}

?>
