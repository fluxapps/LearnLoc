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

/**
 * JSON Interface for ILIAS LearnLoc
 *
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 *
 */
class ilLearnLocJsonService {

	const DEV = false;
	const LOG = false;
	//
	// Construct & Authenticate
	//
	/**
	 * @param $data
	 */
	public function __construct($data) {
		if (self::DEV OR $_GET['debug'] = 1) {
			error_reporting(E_ERROR);
			ini_set('display_errors', 'stdout');
		} else {
			error_reporting(0);
		}
		$this->setUsername(rawurldecode($data["username"]));
		$this->setPassword(rawurldecode($data['password']));
		$this->setPostdata($data);
		// Refactor
		$this->setUrl("https://" . $_SERVER['SERVER_NAME'] . str_ireplace("Customizing/global/plugins/Services/Repository/RepositoryObject/LearnLoc/"
				. basename($_SERVER['SCRIPT_FILENAME']), "", $_SERVER['REQUEST_URI'])); //."goto.php?";
		$this->setPath(getcwd());
		$this->setRoot(str_ireplace("Customizing/global/plugins/Services/Repository/RepositoryObject/LearnLoc/"
			. basename($_SERVER['SCRIPT_FILENAME']), "", $_SERVER['SCRIPT_FILENAME']));
		// END Refactor
		global $ilUser;
		$this->includes();
		$this->setLogging();
		$this->pl = new ilLearnLocPlugin();
		$this->xlelAuth();
		//		var_dump($ilUser); // FSX
		if (! $this->failed) {
			$this->log->write("Login ok for user " . $this->getUsername() . ", IP: " . $_SERVER['REMOTE_ADDR']);
			$service = $data["service"];
			global $ilDB;
			/**
			 * @var $ilDB ilDB
			 */
			$this->db = $ilDB;
			if ($service != "" && in_array($service, get_class_methods($this))) {
				if ($this->checkParams($service)) {
					try {
						$this->log->write("Start Service " . $service . " for User " . $this->getUsername() . ", IP: " . $_SERVER['REMOTE_ADDR']);
						$this->{$service}();
						$this->log->write("End Service " . $service . " for User " . $this->getUsername() . ", IP: " . $_SERVER['REMOTE_ADDR']);
					} catch (Exception $e) {
						$err = $e->getMessage();
						$this->log->write("Service " . $service . " failed for User " . $this->getUsername() . ", IP: " . $_SERVER['REMOTE_ADDR']
							. ", err: " . $err);
					}
				}
			} else {
				$protected = array(
					"__construct",
					"login",
					"setResponse",
					"getResponse",
					"xlelAuth",
					"getResizedImage",
					"getDF",
					"setImageResponse",
					"getLocationImages",
					"getCommentImages",
					"getBase64Image",
					"checkParams",
					"getMatCount",
					"includes",
					"setLogging",
					"setPassword",
					"getPassword",
					"setUsername",
					"getUsername",
					"setPostdata",
					"getPostdata",
					"setPath",
					"getPath",
					"setUrl",
					"getUrl",
					"setRoot",
					"getRoot"

				);
				foreach (get_class_methods($this) as $met) {
					if (! in_array($met, $protected)) {
						$services[] = $met;
					}
				}
				$this->setResponse(array(
					"error" => "Service $service not available",
					"resolve" => 'Specify a Service by passing the POST-Var service',
					"available-services" => $services
				));
				$this->log->write("Service " . $service . "  not available for User " . $this->getUsername() . ", IP: " . $_SERVER['REMOTE_ADDR']);
			}
		}
	}


	//
	// Authentication
	//
	public function xlelAuth() {
		require_once('Auth/Auth.php');
		require_once('./Services/AuthShibboleth/classes/class.ilShibboleth.php');
		require_once("./Services/Authentication/classes/class.ilAuthUtils.php");
		ilAuthUtils::_initAuth();

		global $ilAuth;
		if (! $ilAuth->getAuth()) {
			$res = array( "error" => array( "errorcode" => 1, "description" => "Wrong credentials" ) );
			$this->setResponse($res);
			$this->log->write("Login for User " . $this->getUsername() . " failed, IP: " . $_SERVER['REMOTE_ADDR']);
			$this->failed = true;
		}
	}


	public function includes() {
		chdir($this->getRoot());
		require_once('./include/inc.ilias_version.php');
		require_once('./Services/Component/classes/class.ilComponent.php');
		require_once('./Services/Context/classes/class.ilContext.php');
		ilContext::init(ilContext::CONTEXT_WEB_ACCESS_CHECK);
		require_once('./Services/Init/classes/class.ilInitialisation.php');
		ilInitialisation::initILIAS();
		require_once('class.ilLearnLocPlugin.php');
		require_once('class.ilObjLearnLoc.php');
		require_once('./Services/Logging/classes/class.ilLog.php');
		require_once('class.ilLearnLocConfigGUI.php');
		require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/LearnLoc/classes/class.ilObjLearnLocAccess.php');
	}


	public function setLogging() {
		if (self::LOG) {
			$this->log = new ilLog($this->getPath(), "debug.log", "LEARNLOC:" . time(), true);
			$this->log->write("Login initiated for user " . $this->getUsername());
		} else {
			$this->log = new dummyLog();
		}
	}


	public function getLoginType() {
		global $ilUser, $ilias;
		require_once('./Services/Authentication/classes/class.ilAuthUtils.php');
		require_once('class.ilLearnLocConfigGUI.php');
		if ($this->emptylogin || $this->failed) {
			$res = array( "login-type" => '0' );
		} else {
			switch ($ilUser->getAuthMode()) {
				case 'default':
					$all_modes = ilAuthUtils::_getAllAuthModes();
					$mode = $all_modes[$ilias->getSetting('auth_mode')];
					break;
				default:
					$mode = $ilUser->getAuthMode();
					break;
			}

			switch ($mode) {
				case 'local';
					$res = array( "login-type" => '2' );
					break;
				case 'ldap';
					$res = array( "login-type" => '1' );
					break;
			}
		}
		$this->setResponse($res);
	}


	//
	// Locations
	//
	public function getLearnLocCountForRefId($a_ref_id) {
		global $tree;
		$this->count_locs += count($tree->getChildsByType($a_ref_id, 'xlel')); // TODO Filter offline
		foreach ($tree->getChildsByType($a_ref_id, 'fold') as $ref) {
			$this->getLearnLocCountForRefId($ref['ref_id']);
		}

		return $this->count_locs;
	}


	public function getCourses() {
		global $ilAccess, $ilUser, $tree, $ilDB;
		$ref_ids = array();
		foreach (ilObject2::_getObjectsByType(ilLearnLocPlugin::_getType()) as $xlel) {
			foreach (ilObject2::_getAllReferences($xlel['obj_id']) as $xlel_ref) {
				$read_access = $ilAccess->checkAccessOfUser($ilUser->getId(), "read", "", $xlel_ref, ilLearnLocPlugin::_getType());
				$read_access_course = $ilAccess->checkAccessOfUser($ilUser->getId(), "read", "view", $tree->getParentId($xlel_ref));
				if ($read_access AND $read_access_course) {
					$obj = ilObjectFactory::getInstanceByRefId($tree->getParentId($xlel_ref));
					$x = 0;
					while ($obj->getType() != 'crs' && $x < 3) {
						$obj = ilObjectFactory::getInstanceByRefId($tree->getParentId($obj->getRefId()));
						$x ++;
					}
					if (! in_array($obj->getRefId(), $ref_ids) && $obj->getType() == 'crs') {
						$courses[] = array(
							"title" => $obj->getTitle(),
							"id" => $obj->getRefId(),
							"write-permission" => ($ilAccess->checkAccessOfUser($ilUser->getId(), "create", "", $obj->getRefId(), "xlel") ? 1 : 0),
							"loc_count" => $this->getLearnLocCountForRefId($obj->getRefId())
						);
						$this->count_locs = 0;
					}
					$ref_ids[] = $obj->getRefId();
				}
			}
		}
		$return = array( "user" => array( "id" => $ilUser->getId(), "courses" => array( "course" => $courses ) ) );
		$this->setResponse($return);
	}


	public function getCampusTour() {
		global $ilUser, $ilAccess;
		require_once('class.ilLearnLocConfigGUI.php');
		if (ilLearnLocConfigGUI::_getValue('campus_tour_node')) {
			$crs = ilLearnLocConfigGUI::_getValue('campus_tour_node');
		} else {
			$crs = 126717;
		}
		$obj = ilObjectFactory::getInstanceByRefId($crs);
		$courses[] = array(
			"title" => $obj->getTitle(),
			"id" => $obj->getRefId(),
			"write-permission" => ($ilAccess->checkAccessOfUser($ilUser->getId(), "create", "", $obj->getRefId(), "xlel") ? 1 : 0),
			"loc_count" => $this->getLearnLocCountForRefId($obj->getRefId())
		);
		$this->count_locs = 0;
		$return = array( "user" => array( "id" => $ilUser->getId(), "courses" => array( "course" => $courses ) ) );
		$this->setResponse($return);
	}


	// Alte Version
	public function getLocations() {
		global $tree;
		$cont = ilObjectFactory::getInstanceByRefId($this->getDF('course-id'));
		$subitems = $cont->getSubItems();
		foreach ($subitems[ilLearnLocPlugin::_getType()] as $ref_id) {
			$xlelObj = ilObjectFactory::getInstanceByRefId($ref_id['ref_id']);
			$locations[] = array(
				"id" => $xlelObj->getId(),
				"title" => $xlelObj->getTitle(),
				"latitude" => $xlelObj->getLatitude(),
				"longitude" => $xlelObj->getLongitude(),
				"elevation" => 0,
				"link" => $this->url . "login.php?target=fold_" . $xlelObj->getContainerId() . "&full=1",
				"description" => str_ireplace("\r\n", '<br/>', $xlelObj->getLongDescription()),
				"show_if_near" => 0,
				"mat_count" => count($tree->getChilds($xlelObj->getContainerId())),
				"allow-comments" => ($this->nologin ? 0 : 1)
			);
		}
		$return = array(
			"course" => array(
				"id" => $cont->getRefId(),
				"description" => str_ireplace("\r\n", '<br/>', $cont->getLongDescription()),
				"locations" => array( "location" => $locations )
			)
		);
		$this->setResponse($return);
	}


	// new
	public function getLocationsAndFolders() {
		/**
		 * @var $cont ilObjCourse
		 */
		$cont = ilObjectFactory::getInstanceByRefId($this->getDF('course-id'));
		$locations = $this->getLearnLocsForContObj($cont);
		$folders = $this->getFoldersForContObj($cont);
		$return = array(
			"course" => array(
				"id" => $cont->getRefId(),
				"description" => str_ireplace("\r\n", '<br/>', $cont->getLongDescription()),
				"locations" => array( "location" => $locations ),
				"folder" => array( "folders" => $folders )
			)
		);
		$this->setResponse($return);
	}


	/**
	 * @param $obj
	 *
	 * @return array
	 */
	public function getFoldersForContObj(&$obj) {
		foreach ($this->getTypeIdsForContObj($obj, 'fold') as $ref_id) {
			$fold = ilObjectFactory::getInstanceByRefId($ref_id);
			$folders[] = array(
				'id' => $fold->getRefId(),
				'title' => $fold->getTitle(),
				'location' => $this->getLearnLocsForContObj($fold),
				'folder' => $this->getFoldersForContObj($fold)
			);
		}

		return $folders;
	}


	/**
	 * @param $obj
	 *
	 * @return array
	 */
	private function getLearnLocsForContObj($obj) {
		global $tree;
		foreach ($this->getTypeIdsForContObj($obj, ilLearnLocPlugin::_getType()) as $ref_id) {
			$xlelObj = ilObjLearnLoc::getInstance($ref_id);
			$return[] = array(
				'id' => $xlelObj->getId(),
				'title' => $xlelObj->getTitle(),
				'latitude' => $xlelObj->getLatitude(),
				'longitude' => $xlelObj->getLongitude(),
				'elevation' => 0,
				'link' => $this->url . 'login.php?target=fold_' . $xlelObj->getContainerId() . '&full=1',
				'description' => str_ireplace("\r\n", '<br/>', $xlelObj->getLongDescription()),
				'show_if_near' => 0,
				'mat_count' => count($tree->getChilds($xlelObj->getContainerId())),
				'allow-comments' => ($this->nologin ? 0 : 1)
			);
		}

		return $return;
	}


	/**
	 * @param        $obj
	 * @param string $type
	 * @param int    $ref_id
	 *
	 * @return array
	 */
	public function getTypeIdsForContObj($obj, $type = 'xlel', $ref_id = 0) {
		global $ilAccess, $ilUser, $tree;

		/**
		 * @var $obj ilObjCourse
		 */
		$subitems = $obj->getSubItems();
		$ref_ids = array();
		$is_xlel = $type == ilLearnLocPlugin::_getType();
		foreach ($subitems[$type] as $ref_id) {
			$online = ilObjLearnLocAccess::checkOnlineForRefId($ref_id['ref_id']);
			$has_xlels = false;
			if(!$is_xlel) {
				$has_xlels = count($this->getTypeIdsForContObj(ilObjectFactory::getInstanceByRefId($ref_id['ref_id']), ilLearnLocPlugin::_getType())) > 0;
			}
			$can_create_xlel = $ilAccess->checkAccessOfUser($ilUser->getId(), 'create', '', $ref_id['ref_id'], 'xlel');
			$is_pool = ilObjLearnLoc::_isPool($ref_id['ref_id']);
			if (($is_xlel AND $online) OR $has_xlels OR (!$is_xlel AND $can_create_xlel AND ! $is_pool)) {
				$ref_ids[] = $ref_id['ref_id'];
			}
		}

		return $ref_ids;
	}


	// end new
	/**
	 * getMatCount
	 *
	 * @param int $a_fold
	 *
	 * @return int
	 */
	public function getMatCount($a_fold) {
		if ($a_fold != 0 && $a_fold != NULL) {
			require_once('./Modules/Folder/classes/class.ilObjFolder.php');
			$foldObj = new ilObjFolder($a_fold);
			$sub_items = $foldObj->getSubItems();

			return count($sub_items['_all']);
		} else {
			return 0;
		}
	}


	public function addLocation() {


		$xlelObj = new ilObjLearnLoc();
		$xlelObj->create();
		$xlelObj->setTitle($this->getDF("name"));
		$xlelObj->setDescription($this->getDF("description"));
		$xlelObj->update();
		$xlelObj->setOnline(1);
		$xlelObj->setLatitude($this->getDF("latitude"));
		$xlelObj->setLongitude($this->getDF("longitude"));
		$xlelObj->setElevation(6); //$ob->elevation);
		$xlelObj->setAddress($this->getDF("address"));
		$xlelObj->createReference();
		$xlelObj->setPermissions($this->getDF("course-id"));
		$xlelObj->putInTree($this->getDF("course-id"));

		if ($this->getDF("image")) {
			require_once('class.ilLearnLocMedia.php');
			$mob = new ilLearnLocMedia();
			$mob->setTitle('lelinitmob');
			$mob->create($xlelObj->getRefId(), true);
			$name = '/img_ws_' . time() . '_' . rand(1000, 9999) . '.jpg';
			$file_upload = $mob->getPath() . $name;
			$img = str_replace('data:image/png;base64,', '', $this->getDF("image"));
			$img = str_replace(' ', '+', $img);
			$data = base64_decode($img);
			file_put_contents($file_upload, $data);
			$file['image']['tmp_name'] = $file_upload;
			$file['image']['name'] = $name;
			$mob->setFile($file);
			$mob->addImage();
			$mob_id = $mob->getId();
			$xlelObj->setInitMobId($mob_id);
			$xlelObj->update();
		}
	}


	//
	// Comments
	//
	public function getComment() {
		global $ilUser;
		require_once('class.ilLearnLocComment.php');
		$comObj = new ilLearnLocComment($this->getDF("comment-id"));
		foreach ($comObj->children as $child) {
			$replies[] = array(
				'id' => $child->getId(),
				'title' => $child->getTitle(),
				'body' => $child->getBody(),
				'username' => $ilUser->_lookupFullname($child->getUserId()),
				'date' => strtotime($child->getCreationDate()),
				'haspicture' => ($child->getMediaId() > 0) ? 1 : 0,
			);
		}
		$comment = array(
			'id' => $comObj->getId(),
			'title' => $comObj->getTitle(),
			'body' => $comObj->getBody(),
			'username' => $ilUser->_lookupFullname($comObj->getUserId()),
			'date' => strtotime($comObj->getCreationDate()),
			'haspicture' => ($comObj->getMediaId() > 0) ? 1 : 0,
			'replies' => $replies
		);
		if ($comment != NULL) {
			$return = array( "comments" => array( "count" => 1, "comment" => $comment ) );
		} else {
			$return = array( "error" => array( "errorcode" => 5, "description" => "No or wrong comment-id given" ) );
		}
		$this->setResponse($return);
	}


	public function getComments() {
		global $ilUser;
		require_once('class.ilLearnLocComment.php');
		foreach (ilLearnLocComment::_getNumberOfCommentsForObjId($this->getDF("location-id"), $this->getDF("start"), $this->getDF("count")) as $comObj) {
			$replies = array();
			foreach ($comObj->children as $child) {
				$replies[] = array(
					'id' => $child->getId(),
					'title' => $child->getTitle(),
					'body' => $child->getBody(),
					'username' => $ilUser->_lookupFullname($child->getUserId()),
					'date' => strtotime($child->getCreationDate()),
					'haspicture' => ($child->getMediaId() > 0) ? 1 : 0,
				);
			}
			$comments[] = array(
				'id' => $comObj->getId(),
				'title' => $comObj->getTitle(),
				'body' => $comObj->getBody(),
				'username' => $ilUser->_lookupFullname($comObj->getUserId()),
				'date' => strtotime($comObj->getCreationDate()),
				'haspicture' => ($comObj->getMediaId() > 0) ? 1 : 0,
				'replies' => $replies
			);
		}
		if ($comments != NULL) {
			$return = array(
				"comments" => array(
					"count" => count(ilLearnLocComment::_getAllForRefId($this->getDF("location-id"))),
					"comment" => $comments
				)
			);
		} else {
			$return = array(
				"error" => array(
					"errorcode" => 4,
					"description" => "No or wrong location-id given or no comments"
				)
			);
		}
		$this->setResponse($return);
	}


	/**
	 * addComment
	 */
	public function addComment() {
		global $ilUser;
		require_once('class.ilLearnLocComment.php');
		$comObj = new ilLearnLocComment();
		$comObj->setRefId($this->getDF("location-id"));
		$comObj->setTitle($this->getDF("title"));
		$comObj->setBody($this->getDF("body"));
		$comObj->setParentId($this->getDF("reply-to-id"));
		$comObj->setUserId($ilUser->getId());
		if ($this->getDF("image")) {
			require_once('class.ilLearnLocMedia.php');
			$mob = new ilLearnLocMedia();
			$mob->setTitle('lelcommentmob');
			$mob->create($this->getDF("location-id"), true);
			$name = '/img_ws_' . time() . '_' . rand(1000, 9999) . '.jpg';
			$file_upload = $mob->getPath() . $name;
			file_put_contents($file_upload, base64_decode($this->getDF("image")));
			$file['image']['tmp_name'] = $file_upload;
			$file['image']['name'] = $name;
			$mob->setFile($file);
			$mob->addImage();
			$mob_id = $mob->getId();
		}
		$comObj->setMediaId($mob_id);
		$comObj->setCreationDate(time());
		$comObj->create();
	}


	//
	// Images
	//
	public function getBase64Image($c_id, $w, $h, $crop = false, $mx = false) {
		require_once './Services/MediaObjects/classes/class.ilObjMediaObject.php';
		if ($c_id != 0) {
			$media_obj = new ilObjMediaObject($c_id);
			$data['dir'] = ilObjMediaObject::_getDirectory($media_obj->getId());
			foreach ($media_obj->getMediaItems() as $i => $med) {
				$data[media][$i]->src = $med->location;
				$data[media][$i]->id = $med->id;
			}
			$file = $data['dir'] . "/" . $data[media][0]->src;
		} else {
			$file = "./Customizing/global/plugins/Services/Repository/RepositoryObject/LearnLoc/templates/images/init.jpg";
		}
		$scale = (! $crop) ? true : false;
		if ($mx) {
			$crop = true;
			$scale = false;
		}
		$path = ilObjLearnLoc::resize($file, array(
			'w' => $w,
			'h' => $h,
			'crop' => $crop,
			'scale' => $scale,
			'canvas-color' => '#ffffff',
			'quality' => 100,
			'maxOnly' => $mx
		));

		return file_get_contents($path);
	}


	/**
	 * @param      $c_id
	 * @param      $w
	 * @param      $h
	 * @param bool $crop
	 *
	 * @return string
	 */
	private function getCommentImages($c_id, $w, $h, $crop = false) {
		global $ilDB;
		$set = $ilDB->query("SELECT media_id FROM `rep_robj_xlel_comments` WHERE `id` = '" . $c_id . "'");
		$rec = $ilDB->fetchObject($set);

		return $this->getBase64Image($rec->media_id, $w, $h, $crop);
	}


	/**
	 * getCommentThumb
	 */
	public function getCommentThumb() {
		$img = $this->getCommentImages($this->getDF("comment-id"), 64, 64, true);
		$this->setImageResponse($img);
	}


	/**
	 * getCommentImage
	 */
	public function getCommentImage() {
		$img = $this->getCommentImages($this->getDF("comment-id"), 960, 960, false);
		$this->setImageResponse($img);
	}


	/**
	 * getLocationImages
	 */
	private function getLocationImages($c_id, $w, $h, $crop = false, $scale = "", $mx = false) {
		$obj = ilObjectFactory::getInstanceByObjId($this->getDF('location-id')); //new ilObjLearnLoc($this->getDF('location-id'));
		require_once('class.ilLearnLocMedia.php');
		$mediaObj = new ilLearnLocMedia($obj->getInitMobId());
		$scale = (! $crop) ? true : false;
		if ($mx) {
			$crop = true;
			$scale = false;
		}
		$mediaObj->setOptions(array(
			'w' => $w,
			'h' => $h,
			'crop' => $crop,
			'scale' => $scale,
			'canvas-color' => '#ffffff',
			'quality' => 100,
			'maxOnly' => $mx,
			'fullpath' => true
		));

		return file_get_contents($mediaObj->resizeFirstImage());
		/*
				$path = ilObjLearnLoc::resize($file, array('w' => $w, 'h' => $h, 'crop' => $crop, 'scale' => $scale, 'canvas-color' => '#ffffff', 'quality' => 100, 'maxOnly' => $mx));
				global $ilDB;

				$set = $ilDB->query("SELECT init_mob_id FROM `rep_robj_xlel_data` WHERE `id` = '" . $c_id . "'");
				$rec = $ilDB->fetchObject($set);

				return $this->getBase64Image($rec->init_mob_id, $w, $h, $crop, $scale);*/
	}


	/**
	 * getCommentThumb
	 */
	public function getLocationThumb() {
		$img = $this->getLocationImages($this->getDF("location-id"), 64, 64, true);
		$this->setImageResponse($img);
	}


	/**
	 * getCommentImage
	 */
	public function getLocationImage() {
		$img = $this->getLocationImages($this->getDF("location-id"), 960, 960, false);
		$this->setImageResponse($img);
	}


	/**
	 * getLocationHeaderImage
	 */
	public function getLocationHeaderImage() {
		$set = $this->db->query('SELECT init_mob_id FROM rep_robj_xlel_data WHERE id = ' . $this->db->quote($this->getDF('location-id'), 'integer'));
		$rec = $this->db->fetchObject($set);
		require_once './Services/MediaObjects/classes/class.ilObjMediaObject.php';
		$media_obj = new ilObjMediaObject($rec->init_mob_id);
		if ($rec->init_mob_id != 0 && $rec->init_mob_id != '') {
			$data['dir'] = ilObjMediaObject::_getDirectory($media_obj->getId());
			foreach ($media_obj->getMediaItems() as $i => $med) {
				$data['media'][$i]->src = $med->location;
				$data['media'][$i]->id = $med->id;
			}
			$file = $data['dir'] . "/" . $data[media][0]->src;
		} else {
			$file = "./Customizing/global/plugins/Services/Repository/RepositoryObject/LearnLoc/templates/images/init.jpg";
		}
		list($width, $height) = getimagesize($file);
		if ($width / $height > 1 && $width / $height < 2) // Querformat
		{
			$crop = false;
		} else {
			$crop = true;
		}
		//crop false und scale false bei querformat
		//crop true und scale false bei hochformat
		$path = ilObjLearnLoc::resize($file, array(
			'w' => 720,
			'h' => 400,
			'crop' => $crop,
			'scale' => false,
			'maxOnly' => false,
			'canvas-color' => '#ffffff',
			'quality' => 100
		));
		$img = file_get_contents($path);
		$this->setImageResponse($img);
	}


	//
	// get Fields of PostVars
	//
	/**
	 * @param $a_val
	 *
	 * @return mixed
	 */
	public function getDF($a_val) {
		return $this->postdata[$a_val];
	}


	/**
	 * @param $a_val
	 *
	 * @return bool
	 */
	public function checkParams($a_val) {
		$services = array(
			"getCourses" => array(),
			"getLocations" => array( "course-id" ),
			"getLocationsAndFolders" => array( "course-id" ),
			"addLocation" => array( "name", "description", "longitude", "latitude", "elevation", "course-id" ),
			"getComment" => array( "comment-id" ),
			"getComments" => array( "location-id", "start", "count" ),
			"addComment" => array( "location-id", "body" ),
			"getCommentThumb" => array( "comment-id" ),
			"getCommentImage" => array( "comment-id" ),
			"getLocationThumb" => array( "location-id" ),
			"getLocationImage" => array( "location-id" ),
			"getLocationHeaderImage" => array( "location-id" ),
		);
		array_push($services[$a_val], "username", "password");
		$return = true;
		$log = "";
		foreach ($services[$a_val] as $v) {
			if ($v != "password") {
				$log .= $v . ":" . $this->postdata[$v] . ", ";
			}
			if ($this->postdata[$v] == "") {
				$return = false;
				$res = array(
					"error" => array(
						"errorcode" => 3,
						"description" => "Service requires the following parameters: " . implode(", ", $services[$a_val])
					)
				);
				$this->setResponse($res);
			}
		}
		$this->log->write("Sent Data for user " . $this->username . ": " . $log);

		return $return;
	}


	//
	// Resonse
	//
	/**
	 * @param $res
	 */
	public function setResponse($res) {
		header('Content-type: application/json');
		$this->response = json_encode($res);
		$this->log->write("JSON-Response for User " . $this->username . ", IP: " . $_SERVER['REMOTE_ADDR'] . ": " . $this->response);
	}


	/**
	 * @param $res
	 */
	public function setImageResponse($res) {
		header('Content-type: image/jpeg');
		$this->response = $res;
		$this->log->write("Image-Response for User " . $this->username . ", IP: " . $_SERVER['REMOTE_ADDR']);
	}


	/**
	 * @return string
	 */
	public function getResponse() {
		return $this->response;
	}


	/**
	 * @var string
	 */
	protected $response;
	/**
	 * @var string
	 */
	protected $username;
	/**
	 * @var string
	 */
	protected $password;
	/**
	 * @var array
	 */
	protected $postdata;
	/**
	 * @var string
	 */
	protected $url;
	/**
	 * @var string
	 */
	protected $path;
	/**
	 * @var mixed
	 */
	protected $root;
	//
	// Setter/Getter
	//
	/**
	 * @param string $password
	 */
	public function setPassword($password) {
		$this->password = $password;
	}


	/**
	 * @return string
	 */
	public function getPassword() {
		return $this->password;
	}


	/**
	 * @param string $username
	 */
	public function setUsername($username) {
		$this->username = $username;
	}


	/**
	 * @return string
	 */
	public function getUsername() {
		return $this->username;
	}


	/**
	 * @param array $postdata
	 */
	public function setPostdata($postdata) {
		$this->postdata = $postdata;
	}


	/**
	 * @return array
	 */
	public function getPostdata() {
		return $this->postdata;
	}


	/**
	 * @param string $path
	 */
	public function setPath($path) {
		$this->path = $path;
	}


	/**
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}


	/**
	 * @param string $url
	 */
	public function setUrl($url) {
		$this->url = $url;
	}


	/**
	 * @return string
	 */
	public function getUrl() {
		return $this->url;
	}


	/**
	 * @param mixed $root
	 */
	public function setRoot($root) {
		$this->root = $root;
	}


	/**
	 * @return mixed
	 */
	public function getRoot() {
		return $this->root;
	}
}

class dummyLog {

	public function write($text) {
		return true;
	}
}

?>
