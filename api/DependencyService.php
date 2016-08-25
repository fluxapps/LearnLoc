<?php
namespace LearnLocApi;

require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/LearnLoc/classes/VisitDependency/class.ilLearnLocVisit.php");

/**
 * Class DependencyService
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
class DependencyService implements Service{

	protected $ref_id;

	public function __construct($id) {
		$this->ref_id = $id;
	}

	/**
	 * @return mixed
	 */
	public function getResponse() {
		global $ilUser;
		require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/LearnLoc/classes/VisitDependency/class.ilLearnLocDependency.php");
		$paths = \ilLearnLocDependency::getPaths($this->ref_id);
		$namepaths = array();
		foreach ($paths as $path) {
			$namepath = array();
			foreach ($path as $node) {
				$resolved = $this->isResolved($node);
				$visited = \ilLearnLocVisit::where(array(
					'user_id' => $ilUser->getId(),
					'learn_loc_id' => $node
				))->count();
				$namepath[$node] = array(
					'name' => \ilObject::_lookupTitle(\ilObject::_lookupObjectId($node)),
					'visited' => $visited,
					'unlocked' => $resolved
				);
			}
			$namepaths[] = $namepath;
		}


		return $namepaths;
	}

	private function isResolved($ref_id) {
		global $ilUser;
		/** @var \ilLearnLocDependency $dependency */
		$dependency = \ilLearnLocDependency::where(array('child' => $ref_id))->first();
		// no dependencies no problems.
		if(!$dependency)
			return array("unlocked" => true);
		return $dependency->resolved($ilUser->getId());
	}
}