<?php

/**
 * Class xlelCheckIn
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class xlelCheckIn extends ActiveRecord {

	const TABLE_NAME = 'xlel_check_in';


	/**
	 * @return string
	 * @description Return the Name of your Database Table
	 * @deprecated
	 */
	static function returnDbTableName() {
		return self::TABLE_NAME;
	}


	/**
	 * @var int
	 *
	 * @con_has_field  true
	 * @con_is_primary true
	 * @con_is_unique  true
	 * @con_sequence   true
	 * @con_fieldtype  integer
	 * @con_length     8
	 */
	protected $id = 0;
	/**
	 * @var int
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     8
	 */
	protected $usr_id = 0;
	/**
	 * @var int
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     8
	 */
	protected $obj_id = 0;
	/**
	 * @var DateTime
	 *
	 * @con_has_field  true
	 * @con_fieldtype  timestamp
	 */
	protected $check_in_date = null;


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
	public function getUsrId() {
		return $this->usr_id;
	}


	/**
	 * @param int $usr_id
	 */
	public function setUsrId($usr_id) {
		$this->usr_id = $usr_id;
	}


	/**
	 * @return int
	 */
	public function getObjId() {
		return $this->obj_id;
	}


	/**
	 * @param int $obj_id
	 */
	public function setObjId($obj_id) {
		$this->obj_id = $obj_id;
	}


	/**
	 * @return DateTime
	 */
	public function getCheckInDate() {
		return $this->check_in_date;
	}


	/**
	 * @param DateTime $check_in_date
	 */
	public function setCheckInDate($check_in_date) {
		$this->check_in_date = $check_in_date;
	}
}

?>
