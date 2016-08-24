<?php

require_once("./Services/ActiveRecord/class.ActiveRecord.php");

/**
 * Class LearnLocDependency
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
class LearnLocDependency extends ActiveRecord {
	/** @var string  */
	static protected $TABLE_NAME = 'xlel_dependency';
	/** @var int
	 * @db_has_field        true
	 * @db_is_primary       true
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
}