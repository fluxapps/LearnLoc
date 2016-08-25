<?php

require_once("./Services/ActiveRecord/class.ActiveRecord.php");

/**
 * Class LearnLocVisit
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
class ilLearnLocVisit extends ActiveRecord {

	/** @var string  */
	protected static $TABLE_NAME = "xlel_visit";

	/** @var int
	 * @db_has_field        true
	 * @db_is_primary       true
	 * @db_is_notnull       true
	 * @con_sequence        true
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
	protected $user_id = 0;

	/** @var int
	 * @db_has_field        true
	 * @db_is_notnull       true
	 * @db_fieldtype        integer
	 * @db_length           8
	 */
	protected $learn_loc_id = 0;

	/** @var int
	 * @db_has_field        true
	 * @db_is_notnull       true
	 * @db_fieldtype        integer
	 * @db_length           8
	 */
	protected $timestamp = null;

	/**
	 * @return string
	 */
	public static function returnDbTableName() {
		return self::$TABLE_NAME;
	}

	/**
	 * @return int
	 */
	public function getUserId() {
		return $this->user_id;
	}

	/**
	 * @param int $user_id
	 */
	public function setUserId($user_id) {
		$this->user_id = $user_id;
	}

	/**
	 * @return int
	 */
	public function getLearnLocId() {
		return $this->learn_loc_id;
	}

	/**
	 * @param int $learn_loc_id
	 */
	public function setLearnLocId($learn_loc_id) {
		$this->learn_loc_id = $learn_loc_id;
	}

	/**
	 * @return int
	 */
	public function getTimestamp() {
		return $this->timestamp;
	}

	/**
	 * @param int $timestamp
	 */
	public function setTimestamp($timestamp) {
		$this->timestamp = $timestamp;
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
}