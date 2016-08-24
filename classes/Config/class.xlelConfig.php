<?php
require_once('./Services/ActiveRecord/class.ActiveRecord.php');

/**
 * Class xlelConfig
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.00
 */
class xlelConfig extends ActiveRecord {

	const CONFIG_VERSION = 2;
	const F_CONFIG_VERSION = 'config_version';
	const F_CAMPUS_TOUR = 'campus_tour';
	const F_CAMPUS_TOUR_NODE = 'campus_tour_node';
	const F_CAMPUS_TOUR_USERNAME = 'campus_tour_username';
	const F_CAMPUS_TOUR_PASSWORD = 'campus_tour_password';
	const F_RANGE = 'range';
	const F_RANGE_ALLOW_OVERRIDE = 'range_allow_override';
	/**
	 * @var array
	 */
	protected static $cache = array();
	/**
	 * @var array
	 */
	protected static $cache_loaded = array();
	/**
	 * @var bool
	 */
	protected $ar_safe_read = false;


	/**
	 * @return bool
	 */
	public static function isConfigUpToDate() {
		return self::getWithName(self::F_CONFIG_VERSION) == self::CONFIG_VERSION;
	}


	/**
	 * @param $name
	 *
	 * @return mixed
	 */
	public static function getWithName($name) {
		if (!self::$cache_loaded[$name]) {
			$obj = new self($name);
			self::$cache[$name] = json_decode($obj->getValue());
			self::$cache_loaded[$name] = true;
		}

		return self::$cache[$name];
	}


	/**
	 * @param $name
	 * @param $value
	 */
	public static function set($name, $value) {
		$obj = new self($name);
		$obj->setValue(json_encode($value));

		if (self::where(array( 'name' => $name ))->hasSets()) {
			$obj->update();
		} else {
			$obj->create();
		}
	}


	/**
	 * @var string
	 *
	 * @db_has_field        true
	 * @db_is_unique        true
	 * @db_is_primary       true
	 * @db_is_notnull       true
	 * @db_fieldtype        text
	 * @db_length           250
	 */
	protected $name;
	/**
	 * @var string
	 *
	 * @db_has_field        true
	 * @db_fieldtype        text
	 * @db_length           4000
	 */
	protected $value;


	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}


	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}


	/**
	 * @param string $value
	 */
	public function setValue($value) {
		$this->value = $value;
	}


	/**
	 * @return string
	 */
	public function getValue() {
		return $this->value;
	}


	/**
	 * @return string
	 */
	static function returnDbTableName() {
		return 'xlel_config';
	}
}

?>
