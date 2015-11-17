<?php

namespace LearnLocApi;

/**
 * Class CoursesService
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class CoursesService implements Service {

	/**
	 * @var int
	 */
	protected $user_id = 0;
	/**
	 * @var bool
	 */
	protected $all = false;


	/**
	 * @param int        $user_id
	 * @param bool|false $all
	 */
	public function __construct($user_id = 0, $all = false) {
		$this->all = $all;
		if ($user_id > 0) {
			$this->user_id = $user_id;
		} else {
			global $ilUser;
			$this->user_id = $ilUser->getId();
		}
	}


	public function getResponse() {
		if (! $this->all) {
			return $this->getXlelCourses();
		} else {
			return $this->getAllCourses();
		}
	}


	/**
	 * @return array
	 */
	protected function getAllCourses() {
		global $ilDB, $ilAccess;

		/**
		 * @var $ilDB \ilDB
		 */
		$res = $ilDB->query('SELECT DISTINCT ref_id
						FROM obj_members AS mem
						JOIN object_reference AS ref ON ref.obj_id = mem.obj_id
						JOIN object_data AS obj ON obj.obj_id =  mem.obj_id
						WHERE mem.usr_id = '.$ilDB->quote($this->user_id, 'integer').' ORDER BY obj.title ASC;');
		$courses = array();

		while ($ref_id = $ilDB->fetchObject($res)) {
			//$courses[] = $ref_id->ref_id;

			$obj = \ilObjectFactory::getInstanceByRefId($ref_id->ref_id, false);

			if ($obj instanceof \ilObjCourse) {
				$write_access = $ilAccess->checkAccessOfUser($this->user_id, "read", "", $obj->getRefId(), "xlel") ? 1 : 0;
				if ($write_access) {
					$courses[] = array(
						"title" => $obj->getTitle(),
						"id" => $obj->getRefId(),
						"write-permission" => ($ilAccess->checkAccessOfUser($this->user_id, "create", "", $obj->getRefId(), "xlel") ? 1 : 0),
						"loc_count" => $this->getLearnLocCountForRefId($obj->getRefId())
					);
				}
			}
		}

		return array(
			"user" => array(
				"id" => $this->user_id,
				"courses" => array( "course" => $courses )
			)
		);
	}


	/**
	 * @return array
	 */
	protected function getXlelCourses() {
		global $ilAccess, $tree;

		$ref_ids = array();
		$courses = array();
		foreach (\ilObject2::_getObjectsByType(\ilLearnLocPlugin::_getType()) as $xlel) {
			foreach (\ilObject2::_getAllReferences($xlel['obj_id']) as $xlel_ref) {
				if ($ilAccess->checkAccessOfUser($this->user_id, "read", "", $xlel_ref, \ilLearnLocPlugin::_getType())
					AND $ilAccess->checkAccessOfUser($this->user_id, "read", "view", $tree->getParentId($xlel_ref))
				) {
					$obj = \ilObjectFactory::getInstanceByRefId($tree->getParentId($xlel_ref));
					$x = 0;
					while ($obj && $obj->getType() != 'crs' && $x < 3) {
						$obj = \ilObjectFactory::getInstanceByRefId($tree->getParentId($obj->getRefId()), false);
						$x ++;
					}
					if ($obj && ! in_array($obj->getRefId(), $ref_ids) && $obj->getType() == 'crs') {
						$courses[] = array(
							"title" => $obj->getTitle(),
							"id" => $obj->getRefId(),
							"write-permission" => ($ilAccess->checkAccessOfUser($this->user_id, "create", "", $obj->getRefId(), "xlel") ? 1 : 0),
							"loc_count" => $this->getLearnLocCountForRefId($obj->getRefId())
						);
						$ref_ids[] = $obj->getRefId();
					}
				}
			}
		}

		return array(
			"user" => array(
				"id" => $this->user_id,
				"courses" => array( "course" => $courses )
			)
		);
	}


	/**
	 * @param $a_ref_id
	 *
	 * @return int
	 */
	protected function getLearnLocCountForRefId($a_ref_id) {
		global $tree;
		$count = count($tree->getChildsByType($a_ref_id, 'xlel'));
		foreach ($tree->getChildsByType($a_ref_id, 'fold') as $ref) {
			$count += $this->getLearnLocCountForRefId($ref['ref_id']);
		}

		return $count;
	}
}