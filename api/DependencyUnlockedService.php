<?php

namespace LearnLocApi;

require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/LearnLoc/classes/VisitDependency/class.ilLearnLocDependency.php");

/**
 * Class DependencyUnlocked
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
class DependencyUnlockedService implements Service{

	protected $ref_id;

	public function __construct($ref_id) {
		$this->ref_id = $ref_id;
	}

	/**
	 * @return mixed
	 */
	public function getResponse() {
		global $ilUser;
		/** @var \ilLearnLocDependency $dependency */
		$dependency = \ilLearnLocDependency::where(array('child' => $this->ref_id))->first();
		// no dependencies no problems.
		if(!$dependency)
			return array("unlocked" => true);
		return array("unlocked" => $dependency->resolved($ilUser->getId()));
	}
}