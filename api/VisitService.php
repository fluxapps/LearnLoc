<?php

namespace LearnLocApi;

/**
 * Class VisitService
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
class VisitService implements Service {

	protected $ref_id;

	public function __construct($ref_id) {
		$this->ref_id = $ref_id;
	}

	/**
	 * @return mixed
	 */
	public function getResponse() {
		global $ilUser;
		$location = new \ilObjLearnLoc($this->ref_id);
		$location->visitLocation($ilUser->getId());
		return array("success" => true);
	}
}