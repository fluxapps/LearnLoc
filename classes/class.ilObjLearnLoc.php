<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

require_once('./Services/Repository/classes/class.ilObjectPlugin.php');
require_once('./Services/Link/classes/class.ilLink.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/LearnLoc/classes/Folder/class.ilLearnLocFolder.php');
require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/LearnLoc/classes/VisitDependency/class.ilLearnLocVisit.php");

/**
 * Class ilObjLearnLoc
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilObjLearnLoc extends ilObjectPlugin {

	const TABLE_NAME = 'rep_robj_xlel_data';
	/**
	 * @var int
	 */
	protected $online;
	/**
	 * @var float
	 */
	protected $latitude;
	/**
	 * @var float
	 */
	protected $longitude;
	/**
	 * @var int
	 */
	protected $elevation = 16;
	/**
	 * @var string
	 */
	protected $address;
	/**
	 * @var int
	 */
	protected $init_mob_id;
	/**
	 * @var int
	 */
	protected $comment_mob_id;
	/**
	 * @var int
	 */
	protected $container_id;
	/**
	 * @var string
	 */
	protected $export_keywords;
	/**
	 * @var ilObjLearnLoc[]
	 */
	protected static $instances = array();


	/**
	 * @param $ref_id
	 *
	 * @return ilObjLearnLoc
	 */
	public static function getInstance($ref_id) {
		if (!isset(self::$instances[$ref_id])) {
			self::$instances[$ref_id] = new self($ref_id);
		}

		return self::$instances[$ref_id];
	}


	/**
	 * @param int $a_ref_id
	 */
	public function __construct($a_ref_id = 0) {
		parent::__construct($a_ref_id);
	}


	final function initType() {
		$this->setType("xlel");
	}


	public function visitLocation($userId) {
		$now = new DateTime();
		$visit = new ilLearnLocVisit();
		$visit->setLearnLocId($this->getRefId());
		$visit->setUserId($userId);
		$visit->setTimestamp($now->getTimestamp());
		$visit->create();
	}


	/**
	 * @param bool $insert
	 * @return array
	 */
	protected function returnArrayForDB($insert = false) {
		$data = array(
			'id'             => array( 'integer', $this->getId() ),
			'is_online'      => array( 'integer', $this->getOnline() ? $this->getOnline() : 0 ),
			'latitude'       => array( 'float', $this->getLatitude() ),
			'longitude'      => array( 'float', $this->getLongitude() ),
			'elevation'      => array( 'float', $this->getElevation() ),
			'address'        => array( 'text', $this->getAddress() ),
			'init_mob_id'    => array( 'integer', $this->getInitMobId() ),
			'comment_mob_id' => array( 'integer', $this->getCommentMobId() ),
			'container_id'   => array( 'integer', $this->getContainerId() ),
			'export_kw'      => array( 'text', $this->getExportKeywords() ),
		);

		return $data;
	}


	public function doCreate() {
		global $ilDB;
		/**
		 * @var $ilDB ilDB
		 */
		$this->createFolder();
		$data = $this->returnArrayForDB(true);
		$ilDB->insert(self::TABLE_NAME, $data);
	}


	/**
	 * @return bool
	 */
	public function createFolder() {
		$folder = new ilLearnLocFolder($this);
		$folder_id = $folder->doCreate();
		$this->setContainerId($folder_id);
		$this->doUpdate();

		return true;
	}


	function doRead() {
		global $ilDB, $tree;
		/**
		 * @var $tree ilTree
		 * @var $ilDB ilDB
		 */
		$set = $ilDB->query("SELECT * FROM rep_robj_xlel_data WHERE id = " . $ilDB->quote($this->getId(), "integer"));
		$rec = $ilDB->fetchObject($set);

		$this->setOnline($rec->is_online);
		$this->setLatitude($rec->latitude);
		$this->setLongitude($rec->longitude);
		$this->setElevation($rec->elevation);
		$this->setAddress($rec->address);
		$this->setInitMobId($rec->init_mob_id);
		$this->setCommentMobId($rec->comment_mob_id);
		$this->setContainerId($rec->container_id);
		$this->setExportKeywords($rec->export_kw);

		if ($this->getRefId() AND !$tree->isSaved($this->getRefId())) {
			if (!$this->getContainerId() OR $this->getContainerId() == 0) {
//				 $this->createFolder();
			}
			if ($this->getContainerId() AND $tree->isSaved($this->getContainerId())) {
				// ilUtil::sendInfo('folder was deleted');
//				 $this->createFolder();
			}
		}
	}


	/**
	 * @return array
	 */
	public function getImagesDataAsArray() {
		$media = new \ilLearnLocMedia($this->getInitMobId());
		$media->setOptions(array(
			'w'    => 960,
			'h'    => 240,
			'crop' => true,
		));

		$header = base64_encode(@file_get_contents($media->resizeFirstImage()));
		$media->setOptions(array(
			'w'    => 960,
			'h'    => 960,
			'crop' => true,
		));

		$std = base64_encode(@file_get_contents($media->resizeFirstImage()));

		$media->setOptions(array(
			'w'    => 64,
			'h'    => 64,
			'crop' => true,
		));

		$thumb = base64_encode(@file_get_contents($media->resizeFirstImage()));

		return array(
			'header' => 'data:image/jpg;base64,' . $header,
			'std'    => 'data:image/jpg;base64,' . $std,
			'thumb'  => 'data:image/jpg;base64,' . $thumb,
		);
	}


	public function doUpdate() {
		global $ilDB;
		/**
		 * @var $ilDB ilDB
		 */
		$ilDB->update(self::TABLE_NAME, $this->returnArrayForDB(), array( 'id' => array( 'integer', $this->getId() ) ));
		if ($this->getContainerId() AND ilObject2::_lookupType($this->getContainerId(), true) == 'fold') {
			$fold = new ilLearnLocFolder($this, $this->getContainerId());
			$fold->update();
		}
	}


	public function doDelete() {
		global $ilDB;
		/**
		 * @var $tree
		 */
		$ilDB->manipulate("DELETE FROM rep_robj_xlel_data WHERE id = " . $ilDB->quote($this->getId(), "integer"));
		// We do not detele the folder here since this leads to problems in system check
	}


	/**
	 * @param $a_target_id
	 * @param $a_copy_id
	 * @param $new_obj
	 */
	public function doClone($a_target_id, $a_copy_id, $new_obj) {
	}


	//
	// Static
	//
	/**
	 * @param $id
	 *
	 * @return bool
	 */
	public static function _isPool($id) {
		global $ilDB;

		$set = $ilDB->query('SELECT container_id FROM rep_robj_xlel_data ');
		while ($rec = $ilDB->fetchObject($set)) {
			if ($rec->container_id == $id) {
				return true;
			}
		}

		return false;
	}


	//
	// Setter/Getter
	//
	/**
	 * @param string $address
	 */
	public function setAddress($address) {
		$this->adress = $address;
	}


	/**
	 * @return string
	 */
	public function getAddress() {
		return $this->address;
	}


	/**
	 * @param int $comment_mob_id
	 */
	public function setCommentMobId($comment_mob_id) {
		$this->comment_mob_id = $comment_mob_id;
	}


	/**
	 * @return int
	 */
	public function getCommentMobId() {
		return $this->comment_mob_id;
	}


	/**
	 * @param int $container_id
	 */
	public function setContainerId($container_id) {
		$this->container_id = $container_id;
	}


	/**
	 * @return int
	 */
	public function getContainerId() {
		return $this->container_id;
	}


	/**
	 * @param int $elevation
	 */
	public function setElevation($elevation) {
		$this->elevation = $elevation;
	}


	/**
	 * @return int
	 */
	public function getElevation() {
		return $this->elevation;
	}


	/**
	 * @param int $init_mob_id
	 */
	public function setInitMobId($init_mob_id) {
		$this->init_mob_id = $init_mob_id;
	}


	/**
	 * @return int
	 */
	public function getInitMobId() {
		return $this->init_mob_id;
	}


	/**
	 * @param float $latitude
	 */
	public function setLatitude($latitude) {
		$this->latitude = $latitude;
	}


	/**
	 * @return float
	 */
	public function getLatitude() {
		return $this->latitude;
	}


	/**
	 * @param float $longitude
	 */
	public function setLongitude($longitude) {
		$this->longitude = $longitude;
	}


	/**
	 * @return float
	 */
	public function getLongitude() {
		return $this->longitude;
	}


	/**
	 * @param int $online
	 */
	public function setOnline($online) {
		$this->online = $online;
	}


	/**
	 * @return int
	 */
	public function getOnline() {
		return $this->online;
	}


	/**
	 * @param string $export_keywords
	 */
	public function setExportKeywords($export_keywords) {
		$this->export_keywords = $export_keywords;
	}


	/**
	 * @return string
	 */
	public function getExportKeywords() {
		return $this->export_keywords;
	}


	/**
	 * @param      $imagePath
	 * @param null $opts
	 *
	 * @return bool|string
	 * @deprecated
	 */
	public static function resize($imagePath, $opts = null, $absolute = false) {
		$base = dirname($imagePath);
		if (!is_dir($base . "/cache")) {
			mkdir($base . "/cache");
			chmod($base . "/cache", 0755);
		}
		$cacheFolder = $base . "/cache/";
		$defaults = array(
			'crop'               => false,
			'scale'              => false,
			'thumbnail'          => false,
			'maxOnly'            => false,
			'canvas-color'       => 'transparent',
			'output-filename'    => false,
			'cacheFolder'        => $cacheFolder,
			//			'remoteFolder' => $remoteFolder,
			'quality'            => 90,
			'cache_http_minutes' => 20,
		);
		$opts = array_merge($defaults, $opts);
		$cacheFolder = $opts['cacheFolder'];
		$remoteFolder = $opts['remoteFolder'];
		$path_to_convert = 'convert'; # this could be something like /usr/bin/convert or /opt/local/share/bin/convert
		## you shouldn't need to configure anything else beyond this point
		$purl = parse_url($imagePath);
		$finfo = pathinfo($imagePath);
		$ext = $finfo['extension'];
		# check for remote image..
		if (isset($purl['scheme']) && $purl['scheme'] == 'http'): # grab the image, and cache it so we have something to work with..
		{
			list($filename) = explode('?', $finfo['basename']);
			$local_filepath = $remoteFolder . $filename;
			$download_image = true;
			if (file_exists($local_filepath)):
				if (filemtime($local_filepath) < strtotime('+' . $opts['cache_http_minutes'] . ' minutes')):
					$download_image = false;
				endif;
			endif;
			if ($download_image == true):
				$img = file_get_contents($imagePath);
				file_put_contents($local_filepath, $img);
			endif;
			$imagePath = $local_filepath;
		}
		endif;
		if (file_exists($imagePath) == false):
			$imagePath = $_SERVER['DOCUMENT_ROOT'] . $imagePath;
			if (file_exists($imagePath) == false):
				return 'image not found';
			endif;
		endif;
		if (isset($opts['w'])): $w = $opts['w']; endif;
		if (isset($opts['h'])): $h = $opts['h']; endif;
		$filename = md5_file($imagePath);
		// If the user has requested an explicit output-filename, do not use the cache directory.
		if (false !== $opts['output-filename']) {
			$newPath = $opts['output-filename'];
		} else {
			if (!empty($w) and !empty($h)):
				$newPath = $cacheFolder . $filename . '_w' . $w . '_h' . $h . (isset($opts['crop'])
				                                                               && $opts['crop'] == true ? "_cp" : "") . (isset($opts['scale'])
				                                                                                                         && $opts['scale']
				                                                                                                            == true ? "_sc" : "")
				           . '.' . $ext;
			elseif (!empty($w)):
				$newPath = $cacheFolder . $filename . '_w' . $w . '.' . $ext;
			elseif (!empty($h)):
				$newPath = $cacheFolder . $filename . '_h' . $h . '.' . $ext;
			else:
				return false;
			endif;
		}
		$create = true;
		if (file_exists($newPath) == true):
			$create = false;
			$origFileTime = date("YmdHis", filemtime($imagePath));
			$newFileTime = date("YmdHis", filemtime($newPath));
			if ($newFileTime < $origFileTime): # Not using $opts['expire-time'] ??
			{
				$create = true;
			}
			endif;
		endif;
		if ($create == true):
			if (!empty($w) and !empty($h)):

				list($width, $height) = getimagesize($imagePath);
				$resize = $w;
				if ($width > $height):
					$resize = $w;
					if (true === $opts['crop']):
						$resize = "x" . $h;
					endif;
				else:
					$resize = "x" . $h;
					if (true === $opts['crop']):
						$resize = $w;
					endif;
				endif;
				if (true === $opts['scale']):
					$cmd = $path_to_convert . " " . escapeshellarg($imagePath) . " -resize " . escapeshellarg($resize) . " -quality "
					       . escapeshellarg($opts['quality']) . " " . escapeshellarg($newPath);
				else:
					$cmd = $path_to_convert . " " . escapeshellarg($imagePath) . " -resize " . escapeshellarg($resize) . " -size " . escapeshellarg($w
					                                                                                                                                . "x"
					                                                                                                                                . $h)
					       . " xc:" . escapeshellarg($opts['canvas-color']) . " +swap -gravity center -composite -quality "
					       . escapeshellarg($opts['quality']) . " " . escapeshellarg($newPath);
				endif;
			else:
				$cmd = $path_to_convert . " " . escapeshellarg($imagePath) . " -thumbnail " . (!empty($h) ? 'x' : '') . $w . ""
				       . (isset($opts['maxOnly']) && $opts['maxOnly'] == true ? "\>" : "") . " -quality " . escapeshellarg($opts['quality']) . " "
				       . escapeshellarg($newPath);
			endif;
			//echo $cmd;

			ilUtil::execConvert(str_ireplace($path_to_convert, '', $cmd));
			//			$c = exec($cmd, $output, $return_code);
			//			if ($return_code != 0) {
			//				echo("Tried to execute : $cmd, return code: $return_code, output: " . print_r($output, true));
			//
			//				return false;
			//			}
		endif;

		return $newPath;
	}


	public static function getAllOnlineObjects() {
		global $ilDB;
		/**
		 * @var $ilDB ilDB
		 */
	}
}

?>
