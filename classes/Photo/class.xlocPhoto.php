<?php

/**
 * Class xlocPhoto
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class xlocPhoto extends ActiveRecord{

	const SIZE_PREVIEW = 70;
	const SIZE_MOSAIC = 300;
	const SIZE_PRESENTATION = 1000;
	const DPI = 72;
	const TITLE_PREVIEW = 'preview';
	const TITLE_MOSAIC = 'mosaic';
	const TITLE_PRESENTATION = 'presentation';
	const TITLE_ORIGINAL = 'original';


	/**
	 * @return string
	 */
	public static function returnDbTableName() {
		return 'sr_obj_pg_pic';
	}


	/**
	 * @var string
	 *
	 * @db_has_field        true
	 * @db_fieldtype        text
	 * @db_length           256
	 */
	protected $title = '';
	/**
	 * @var string
	 *
	 * @db_has_field        true
	 * @db_fieldtype        integer
	 * @db_length           8
	 * @db_is_primary       true
	 * @con_sequence        true
	 */
	protected $id;
	/**
	 * @var int
	 *
	 * @db_has_field  true
	 * @db_fieldtype  integer
	 * @db_length     4
	 * @db_is_notnull true
	 */
	protected $user_id = 0;
	/**
	 * @var string
	 *
	 * @db_has_field        true
	 * @db_fieldtype        text
	 * @db_length           256
	 */
	protected $suffix = '';
	/**
	 * @var int
	 *
	 * @db_has_field  true
	 * @db_fieldtype  integer
	 * @db_length     4
	 * @db_is_notnull true
	 */
	protected $album_id;
	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_fieldtype        date
	 * @db_length           4
	 */
	protected $create_date;
	/**
	 * @var string
	 *
	 * @db_has_field        true
	 * @db_fieldtype        text
	 * @db_length           4000
	 */
	protected $description = '';


	/**
	 * @return string
	 */
	public function getSuffix() {
		return $this->suffix;
	}


	/**
	 * @param string $suffix
	 */
	public function setSuffix($suffix) {
		$this->suffix = $suffix;
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
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}


	/**
	 * @param string $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}


	/**
	 * @return int
	 */
	public function getAlbumId() {
		return $this->album_id;
	}


	/**
	 * @param int $album_id
	 */
	public function setAlbumId($album_id) {
		$this->album_id = $album_id;
	}


	/**
	 * @return string create_date
	 */
	public function getCreateDate() {
		return $this->create_date;
	}


	/**
	 * @param $create_date
	 */
	public function setCreateDate($create_date) {
		$this->create_date = $create_date;
	}


	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}


	/**
	 * @param string $description
	 */
	public function setDescription($description) {
		$this->description = $description;
	}


	/**
	 * @return string
	 */
	public function getPicturePath() {
		return CLIENT_DATA_DIR . '/xpho/album_' . $this->getAlbumId() . '/picture_' . $this->getId();
	}


	/**
	 * @param $usage
	 *
	 * @return string
	 */
	public function getSrc($usage) {
		return $this->getPicturePath() . '/' . $usage . '.' . $this->getSuffix();
	}


	/**
	 * @param string $tmp_path
	 *
	 * @return bool
	 */
	public function uploadPicture($tmp_path) {
		$destination_path = $this->getPicturePath();
		$this->recursiveMkdir($destination_path);
		$this->cropImage($tmp_path, $destination_path . '/' . self::TITLE_PREVIEW . '.'
		                            . $this->getSuffix(), self::SIZE_PREVIEW, self::SIZE_PREVIEW, true);
		$this->cropImage($tmp_path, $destination_path . '/' . self::TITLE_MOSAIC . '.' . $this->getSuffix(), self::SIZE_MOSAIC, self::SIZE_MOSAIC);
		$this->resizeImage($tmp_path, $destination_path . '/' . self::TITLE_PRESENTATION . '.'
		                              . $this->getSuffix(), self::SIZE_PRESENTATION, self::SIZE_PRESENTATION, true, self::DPI);
		move_uploaded_file($tmp_path, $destination_path . '/' . self::TITLE_ORIGINAL . '.' . $this->getSuffix());

		return true;
	}


	/**
	 * @param $path
	 *
	 * @return bool
	 */
	protected function recursiveMkdir($path) {
		$dirs = explode(DIRECTORY_SEPARATOR, $path);
		$count = count($dirs);
		$path = '';
		for ($i = 0; $i < $count; ++ $i) {
			if ($path != '/') {
				$path .= DIRECTORY_SEPARATOR . $dirs[$i];
			} else {
				$path .= $dirs[$i];
			}
			if (!is_dir($path)) {
				ilUtil::makeDir(($path));
			}
		}

		return true;
	}


	public function delete() {
		parent::delete();
		ilUtil::delDir($this->getPicturePath());
	}


	/**
	 * @param $a_from
	 * @param $a_to
	 * @param $a_width
	 * @param $a_height
	 */
	public static function cropImage($a_from, $a_to, $a_width, $a_height) {
		$crop = "-resize " . $a_width . "x" . $a_height . "^ -gravity Center -crop " . $a_width . "x" . $a_height . "+0+0 +repage ";
		$convert_cmd = ilUtil::escapeShellArg($a_from) . " " . $crop . ilUtil::escapeShellArg($a_to);
		ilUtil::execConvert($convert_cmd);
	}


	/**
	 * @param      $a_from
	 * @param      $a_to
	 * @param      $a_width
	 * @param      $a_height
	 * @param bool $a_constrain_prop
	 * @param      $dpi
	 */
	public static function resizeImage($a_from, $a_to, $a_width, $a_height, $a_constrain_prop = false, $dpi) {
		$density = '';
		if ($a_constrain_prop) {
			$size = " -geometry " . $a_width . "x" . $a_height . " ";
		} else {
			$size = " -resize " . $a_width . "x" . $a_height . "! ";
		}
		if ($dpi) {
			$density = " -density " . $dpi . " ";
		}
		$convert_cmd = ilUtil::escapeShellArg($a_from) . " " . $size . $density . ilUtil::escapeShellArg($a_to);
		ilUtil::execConvert($convert_cmd);
	}
}
