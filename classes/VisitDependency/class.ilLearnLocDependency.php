<?php

require_once("./Services/ActiveRecord/class.ActiveRecord.php");

/**
 * Class LearnLocDependency
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
class ilLearnLocDependency extends ActiveRecord {
	/** @var string  */
	static protected $TABLE_NAME = 'xlel_dependency';
	/** @var int
	 * @db_has_field        true
	 * @db_is_primary       true
	 * @con_sequence        true
	 * @db_is_notnull       true
	 * @db_fieldtype        integer
	 * @db_length           8
	 */
	protected $id = 0;

	/** @var int
	 * @db_has_field        true
	 * @db_is_notnull       true
	 * @db_fieldtype        integer
	 * @db_length           8
	 */
	protected $parent = 0;

	/** @var int
	 * @db_has_field        true
	 * @db_is_notnull       true
	 * @db_fieldtype        integer
	 * @db_length           8
	 */
	protected $child = 0;

	public static function returnDbTableName() {
		return self::$TABLE_NAME;
	}

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param int $id
	 */
	public function setId($id) {
		$this->id = $id;
	}

	/**
	 * @return int
	 */
	public function getParent() {
		return $this->parent;
	}

	/**
	 * @param int $parent
	 */
	public function setParent($parent) {
		$this->parent = $parent;
	}

	/**
	 * @return int
	 */
	public function getChild() {
		return $this->child;
	}

	/**
	 * @param int $child
	 */
	public function setChild($child) {
		$this->child = $child;
	}

	/**
	 * @return bool returns true iff dependencies contain a circle
	 */
	public function checkForCircle() {
		return $this->checkCircle($this->getChild(), $this->getParent());
	}

	/**
	 * @param $ref_id
	 * @return ilLearnLocDependency[][]
	 */
	public static function getPaths($ref_id) {
		$current_id = $ref_id;
		$path = array($ref_id);
		while($dependency = ilLearnLocDependency::where(array('child' => $current_id))->first()) {
			array_unshift($path, $dependency->getParent());
			$current_id = $dependency->getParent();
		}

		$paths = array($ref_id => $path);
		$pointers = array($ref_id);
		while($pointer = array_shift($pointers)) {
			$currentPath = $paths[$pointer];
			foreach (ilLearnLocDependency::where(array('parent' => $pointer))->get() as $dependency) {
				unset($paths[$pointer]);
				array_push($pointers, $dependency->getChild());
				$newPath = $currentPath;
				array_push($newPath, $dependency->getChild());
				$paths[$dependency->getChild()] = $newPath;
			}
		}

		return $paths;
	}


	public function checkCircle($originalId, $currentId) {
		if($originalId == $currentId)
			return true;
		if($currentId === null)
			return false;
		/** @var ilLearnLocDependency $next */
		$next = ilLearnLocDependency::where(array('child' => $currentId))->first();
		if(!$next)
			return false;
		return $this->checkCircle($originalId, $next->getParent());

	}
}