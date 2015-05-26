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

class ilObjLearnLocJson {

	protected $response;
	protected $username;
	protected $password;


	/**
	 * __construct
	 * $data = $_POST Request
	 */
	function __construct($data) {
		error_reporting(0);
		if ($_SERVER['REMOTE_ADDR'] == '212.41.220.231' AND $_GET['debug'] == 'fsx') {
			error_reporting(E_ALL);
			ini_set('display_errors', '1');
		}
		$this->username = $data["username"];
		$data['password'] = rawurldecode($data['password']);
		$_POST['password'] = rawurldecode($data['password']);
		$this->password = $data['password'];
		$this->postdata = $data;
		$this->url = "https://" . $_SERVER['SERVER_NAME']
			. str_ireplace("Customizing/global/plugins/Services/Repository/RepositoryObject/LearnLoc/json.php", "", $_SERVER['REQUEST_URI']); //."goto.php?";
		$this->path = getcwd();
		$this->root = str_ireplace("Customizing/global/plugins/Services/Repository/RepositoryObject/LearnLoc/json.php", "", $_SERVER['SCRIPT_FILENAME']);
		include_once($this->root . "Services/Logging/classes/class.ilLog.php");
		$this->log = new ilLog($this->path, "debug.log", "LEARNLOC:" . time(), true);
		$this->log->write("Login initiated for user " . $this->username);
		if ($this->username == "userfabian.schmid") {
			$this->log->write("Password for user " . $this->username . ": " . rawurldecode($this->password));


		}
		$dummy = $this->xlelAuth();
		if (! $this->failed) {
			$this->log->write("Login ok for user " . $this->username . ", IP: " . $_SERVER['REMOTE_ADDR']);
			ini_set('session.use_cookies', '0');
			unset($_COOKIE['PHPSESSID']);
			setcookie('PHPSESSID', '', time() - 1);
			unset($_COOKIE['authchallenge']);
			setcookie('authchallenge', '', time() - 1);
			require_once('./include/inc.header.php');
			require_once('./class.ilObjLearnLoc.php');
			$service = $data["service"];
			if ($service != "" && in_array($service, get_class_methods($this))) {
				if ($this->checkParams($service)) {
					try {
						$this->log->write("Start Service " . $service . " for User " . $this->username . ", IP: "
							. $_SERVER['REMOTE_ADDR']);
						$this->{$service}();
						$this->log->write("End Service " . $service . " for User " . $this->username . ", IP: "
							. $_SERVER['REMOTE_ADDR']);
					} catch (Exception $e) {
						$err = $e->getMessage();
						$this->log->write("Service " . $service . " failed for User " . $this->username . ", IP: "
							. $_SERVER['REMOTE_ADDR'] . ", err: " . $err);
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
					"getMatCount"
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
				$this->log->write("Service " . $service . "  not available for User " . $this->username . ", IP: "
					. $_SERVER['REMOTE_ADDR']);
			}
		}
	}


	//
	// Authentication
	//
	function xlelAuth() {
		chdir($this->root);
		require_once('class.ilLearnLocConfigGUI.php');
		require_once './Services/Init/classes/class.ilInitialisation.php';
		$ilInit = new ilInitialisation();
		$ilInit->returnBeforeAuth(true);
		$ilInit->initILIAS();
		if (! $this->postdata['username'] && ! $this->postdata['password']
			|| $this->postdata['username'] == ilLearnLocConfigGUI::_getValue('campus_tour_username')
		) {
			if (! $this->postdata['username']) {
				$this->emptylogin = true;
			}
			$this->postdata['username'] = ilLearnLocConfigGUI::_getValue('campus_tour_username');
			$_POST['username'] = ilLearnLocConfigGUI::_getValue('campus_tour_username');
			$this->postdata['password'] = ilLearnLocConfigGUI::_getValue('campus_tour_password');
			$_POST['password'] = ilLearnLocConfigGUI::_getValue('campus_tour_password');
			$this->nologin = true;
		}
		$_COOKIE = "";
		$_GET["baseClass"] = "ilStartUpGUI";
		require_once('./include/inc.header.php');
		require_once('./Services/Authentication/classes/class.ilAuthFactory.php');
		require_once('./Services/Authentication/classes/class.ilAuthContainerMultiple.php');
		$ilAuth = ilAuthFactory::factory(new ilAuthContainerMultiple());
		if (! $ilAuth->getAuth()) {
			$res = array( "error" => array( "errorcode" => 1, "description" => "Wrong credentials" ) );
			$this->setResponse($res);
			$this->log->write("Login for User " . $this->username . " failed, IP: " . $_SERVER['REMOTE_ADDR']);
			$this->failed = true;
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
		$this->count_locs += count($tree->getChildsByType($a_ref_id, 'xlel'));
		foreach ($tree->getChildsByType($a_ref_id, 'fold') as $ref) {
			$this->getLearnLocCountForRefId($ref['ref_id']);
		}

		return $this->count_locs;
	}


	public function getCourses() {
		global $ilAccess, $ilUser, $tree;
		$ref_ids = array();
		foreach (ilObject2::_getObjectsByType(ilLearnLocPlugin::_getType()) as $xlel) {
			foreach (ilObject2::_getAllReferences($xlel['obj_id']) as $xlel_ref) {
				if ($ilAccess->checkAccessOfUser($ilUser->getId(), "read", "", $xlel_ref, ilLearnLocPlugin::_getType())
					AND $ilAccess->checkAccessOfUser($ilUser->getId(), "read", "view", $tree->getParentId($xlel_ref))
				) {
					$obj = ilObjectFactory::getInstanceByRefId($tree->getParentId($xlel_ref));
					//					if($obj->getType() == 'crs') {
					//						if(!in_array($obj->getRefId(), $ref_ids)) {
					//							$courses[] = array(
					//								"title" => $obj->getTitle(),
					//								"id" => $obj->getRefId(),
					//								"write-permission" => ($ilAccess->checkAccessOfUser($ilUser->getId(), "create", "", $obj->getRefId(), "xlel") ? 1 : 0),
					//								"loc_count" => $this->getLearnLocCountForRefId($obj->getRefId())
					//							);
					//							$this->count_locs = 0;
					//						}
					//						$ref_ids[] = $obj->getRefId();
					//					}
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
	public function getLearnLocsForContObj(&$obj) {
		global $tree;
		foreach ($this->getTypeIdsForContObj($obj, ilLearnLocPlugin::_getType()) as $ref_id) {
			$xlelObj = ilObjectFactory::getInstanceByRefId($ref_id);
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
	public function getTypeIdsForContObj(&$obj, $type = 'xlel', $ref_id = 0) {
		global $ilAccess, $ilUser, $tree;
		$subitems = $obj->getSubItems();
		foreach ($subitems[$type] as $ref_id) {
			if ($type == ilLearnLocPlugin::_getType()
				OR
				count($this->getTypeIdsForContObj(ilObjectFactory::getInstanceByRefId($ref_id['ref_id']), ilLearnLocPlugin::_getType()))
					> 0
				OR ($ilAccess->checkAccessOfUser($ilUser->getId(), 'create', '', $ref_id['ref_id'], 'xlel')
					AND ! ilObjLearnLoc::_isPool($ref_id['ref_id']))
			) {
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


	/**
	 * addLocation
	 */
	public function addLocation() {
		$ob = new stdClass();
		if ($this->getDF("folder-id")) {
			$ob->crs = $this->getDF("folder-id");
		} else {
			$ob->crs = $this->getDF("course-id");
		}
		$ob->title = $this->getDF("name");
		$ob->description = $this->getDF("description");
		$ob->address = $this->getDF("address");
		$ob->longitude = $this->getDF("longitude");
		$ob->latitude = $this->getDF("latitude");
		$ob->elevation = $this->getDF("elevation");
		$ob->image = $this->getDF("image");
		// Mob erstellen
		if ($ob->image) {
			require_once './Services/MediaObjects/classes/class.ilObjMediaObject.php';
			require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/LearnLoc/classes/Folder/class.ilLearnLocFolder.php');
			$media_obj = new ilObjMediaObject();
			$media_obj->create();
			$media_obj->createDirectory();
			$mob_dir = ilObjMediaObject::_getDirectory($media_obj->getId());
			$media_item = new ilMediaItem();
			$media_obj->addMediaItem($media_item);
			$media_item->setPurpose("Standard");
			$name = "img_ws_" . time() . "_" . rand(1000, 9999) . ".jpg";
			$file_upload = $mob_dir . "/" . $name;
			file_put_contents($file_upload, base64_decode($ob->image));
			//file_put_contents($file_upload, $ob->image);
			$format = ilObjMediaObject::getMimeType($file_upload);
			$location = $name;
			$media_item->setFormat($format);
			$media_item->setLocation($location);
			$media_item->setLocationType("LocalFile");
			ilUtil::renameExecutables($mob_dir);
			$media_obj->update();
			$mob_id = $media_obj->getId();
		}
		// LearnLoc erstellen
		$xlelObj = new ilObjLearnLoc();
		$xlelObj->create();
		$xlelObj->setTitle($ob->title);
		$xlelObj->setDescription($ob->description);
		$xlelObj->update();
		$xlelObj->setOnline(1);
		$xlelObj->setLatitude($ob->latitude);
		$xlelObj->setLongitude($ob->longitude);
		$xlelObj->setElevation(6); //$ob->elevation);
		$xlelObj->setAddress($ob->address);
		$xlelObj->setInitMobId($mob_id);
		$xlelObj->doUpdate();
		$xlelObj->createReference();
		$xlelObj->setPermissions($ob->crs);
		$xlelObj->putInTree($ob->crs);
		$xlelObj->createFolder();
	}


	//
	// Comments
	//
	/**
	 * getComment
	 */
	public function getComment() {
		global $ilDB, $ilUser;
		$set = $ilDB->query("SELECT * FROM `rep_robj_xlel_comments` WHERE `parent_id` = "
			. $ilDB->quote($this->getDF("comment-id"), "integer") . ";");
		while ($rec = $ilDB->fetchObject($set)) {
			//$data['init'] = $rec;
			$replies[] = array(
				"id" => $rec->id,
				"title" => $rec->title,
				"body" => $rec->body,
				"username" => $ilUser->_lookupFullname($rec->user_id),
				"date" => strtotime($rec->creation_date),
				"haspicture" => ($rec->media_id > 0) ? 1 : 0,
			);
		}
		$set = $ilDB->query(
			"SELECT * FROM `rep_robj_xlel_comments` WHERE `id` = " . $ilDB->quote($this->getDF("comment-id"), "integer")
				. ";");
		while ($rec = $ilDB->fetchObject($set)) {
			$data['init'] = $rec;
			$comment = array(
				"id" => $rec->id,
				"title" => $rec->title,
				"body" => $rec->body,
				"username" => $ilUser->_lookupFullname($rec->user_id),
				"date" => strtotime($rec->creation_date),
				"haspicture" => ($rec->media_id > 0) ? 1 : 0,
				"replies" => $replies
			);
		}
		if ($comment != NULL) {
			$return = array(
				"comments" => array(
					"count" => 1,
					"comment" => $comment
				)
			);
		} else {
			$return = array(
				"error" => array(
					"errorcode" => 5,
					"description" => "No or wrong comment-id given"
				)
			);
		}
		$this->setResponse($return);
	}


	/**
	 * getComments
	 */
	public function getComments() {
		global $ilDB, $ilUser;
		$filter = "`ref_id` = " . $ilDB->quote($this->getDF("location-id"), "integer");
		$set = $ilDB->query(
			"SELECT * FROM `rep_robj_xlel_comments` WHERE " . $filter . " ORDER BY creation_date DESC LIMIT 0, 1000;");
		while ($rec = $ilDB->fetchObject($set)) {
			if ($rec->parent_id) {
				$data[$rec->parent_id]['replies'][] = $rec;
			} else {
				$data[$rec->id]['init'] = $rec;
			}
		}
		$start = ($this->getDF("start") == "") ? 1 : $this->getDF("start");
		$debug['start'] = $start;
		$count = ($this->getDF("count") == "") ? $start + 10 : $start + $this->getDF("count") - 1;
		$debug['count'] = $count;
		$i = 1;
		if (count($data) > 0) {
			foreach ($data as $comment) {
				$debug['durchgang_i'][$i] = $comment['init']->title;
				if ($i > $count || $i < $start) {
					$i ++;
					continue;
				}
				$debug['durchgang_inachtest'][$i] = $comment['init']->title;
				if (count($comment['replies']) > 0) {
					foreach ($comment['replies'] as $answer) {
						$replies[] = array(
							"id" => $answer->id,
							"title" => $answer->title,
							"body" => $answer->body,
							"username" => $ilUser->_lookupFullname($answer->user_id),
							"date" => strtotime($answer->creation_date),
							"haspicture" => ($answer->media_id > 0) ? 1 : 0,
						);
					}
				}
				$comments[] = array(
					"id" => $comment['init']->id,
					"title" => $comment['init']->title,
					"body" => $comment['init']->body,
					"username" => $ilUser->_lookupFullname($comment['init']->user_id),
					"date" => strtotime($comment['init']->creation_date),
					"haspicture" => ($comment['init']->media_id > 0) ? 1 : 0,
					"replies" => $replies
				);
				$replies = "";
				$i ++;
			}
		}
		if ($comments != NULL) {
			$return = array(
				"comments" => array(
					"count" => count($data),
					"comment" => $comments
				),
				//"debug" => $debug
			);
		} else {
			$return = array(
				"error" => array(
					"errorcode" => 4,
					"description" => "No or wrong location-id given or no comments"
				)
			);
		}
		if ($this->getDF("start") > count($comments)) {
			$return = array(
				"comments" => array(
					"count" => count($data),
					"comment" => ""
				),
			);
		}
		$this->setResponse($return);
	}


	/**
	 * addComment
	 */
	public function addComment() {
		global $ilDB, $ilUser;
		$ob = new stdClass();
		$ob->title = $this->getDF("title");
		$ob->body = $this->getDF("body");
		$ob->location_id = $this->getDF("location-id");
		$ob->reply_to_id = $this->getDF("reply-to-id");
		$ob->image = $this->getDF("image");
		// Mob erstellen
		if ($ob->image) {
			require_once './Services/MediaObjects/classes/class.ilObjMediaObject.php';
			$media_obj = new ilObjMediaObject();
			$media_obj->create();
			$media_obj->createDirectory();
			$mob_dir = ilObjMediaObject::_getDirectory($media_obj->getId());
			$media_item = new ilMediaItem();
			$media_obj->addMediaItem($media_item);
			$media_item->setPurpose("Standard");
			$name = "img_wscm_" . time() . "_" . rand(1000, 9999) . ".jpg";
			$file_upload = $mob_dir . "/" . $name;
			file_put_contents($file_upload, base64_decode($ob->image));
			$format = ilObjMediaObject::getMimeType($file_upload);
			$location = $name;
			$media_item->setFormat($format);
			$media_item->setLocation($location);
			$media_item->setLocationType("LocalFile");
			ilUtil::renameExecutables($mob_dir);
			$media_obj->update();
			$mob_id = $media_obj->getId();
		}
		$ilDB->insert("rep_robj_xlel_comments", array(
			"id" => array( "integer", $ilDB->nextID("rep_robj_xlel_comments") ),
			"ref_id" => array( "integer", $ob->location_id ),
			"parent_id" => array( "integer", $ob->reply_to_id ),
			"user_id" => array( "integer", $ilUser->getId() ),
			"title" => array( "text", $ob->title ),
			"body" => array( "clob", $ob->body ),
			"creation_date" => array( "timestamp", date("Y-m-d H:i:s", time()) ),
			"media_id" => array( "integer", $mob_id )
		));
	}


	//
	// Images
	//
	/*public function getBase64Image($c_id, $w, $h, $crop = false, $mx = false)
	{
		require_once './Services/MediaObjects/classes/class.ilObjMediaObject.php';
		if ($c_id != 0)
		{
			$media_obj = new ilObjMediaObject($c_id);
			$data['dir']  = ilObjMediaObject::_getDirectory($media_obj->getId());
			foreach ($media_obj->getMediaItems() as $i => $med)
			{
				$data[media][$i]->src = $med->location;
				$data[media][$i]->id = $med->id;
			}
			$file = $data['dir']."/".$data[media][0]->src;

		}
		else
		{
			$file = "./Customizing/global/plugins/Services/Repository/RepositoryObject/LearnLoc/templates/images/init.jpg";
		}

		$scale = (!$crop)? true: false;

		if ($mx)
		{
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
		//echo $path;
		return file_get_contents($path);
	}*/
	/*private function getCommentImages($c_id, $w, $h, $crop = false)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT media_id FROM `rep_robj_xlel_comments` WHERE `id` = '".$c_id."'");
		$rec = $ilDB->fetchObject($set);

		return $this->getBase64Image($rec->media_id, $w, $h, $crop);
	}*/
	private function getCommentImages($c_id, $w, $h, $crop = false, $scale = "", $mx = false) {
		require_once('class.ilLearnLocMedia.php');
		require_once('class.ilLearnLocComment.php');
		$obj = new ilLearnLocComment($this->getDF('comment-id'));
		$mediaObj = new ilLearnLocMedia($obj->getMediaId());
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


	/*
		private function getLocationImages($c_id, $w, $h, $crop = false, $scale = "")
		{
			global $ilDB;

			$set = $ilDB->query("SELECT init_mob_id FROM `rep_robj_xlel_data` WHERE `id` = '".$c_id."'");
			$rec = $ilDB->fetchObject($set);

			return $this->getBase64Image($rec->init_mob_id, $w, $h, $crop, $scale);
		}



		public function getLocationThumb()
		{
			$img = $this->getLocationImages($this->getDF("location-id"), 64, 64, true);
			$this->setImageResponse($img);
		}


		public function getLocationImage()
		{
			$img = $this->getLocationImages($this->getDF("location-id"), 960, 960, false);
			$this->setImageResponse($img);
		}
		*/
	private function getLocationImages($c_id, $w, $h, $crop = false, $scale = "", $mx = false) {
		require_once('class.ilLearnLocMedia.php');
		$obj = ilObjectFactory::getInstanceByObjId($this->getDF('location-id'));
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
	}


	public function getLocationThumb() {
		$img = $this->getLocationImages($this->getDF("location-id"), 64, 64, true);
		$this->setImageResponse($img);
	}


	public function getLocationImage() {
		$img = $this->getLocationImages($this->getDF("location-id"), 960, 960, false);
		$this->setImageResponse($img);
	}


	public function getLocationHeaderImage() {
		$img = $this->getLocationImages($this->getDF("location-id"), 720, 400, true, false);
		$this->setImageResponse($img);
	}


	/*
		public function getLocationHeaderImage2()
		{
			//$img = $this->getLocationImages($this->getDF("location-id"), 720, 400, true, true);

			global $ilDB;
			$set = $ilDB->query("SELECT init_mob_id FROM `rep_robj_xlel_data` WHERE `id` = '".$this->getDF("location-id")."'");

			$rec = $ilDB->fetchObject($set);

			require_once './Services/MediaObjects/classes/class.ilObjMediaObject.php';
			$media_obj = new ilObjMediaObject($rec->init_mob_id);

			if ($rec->init_mob_id != 0 && $rec->init_mob_id != '')
			{
				$data['dir']  = ilObjMediaObject::_getDirectory($media_obj->getId());
				foreach ($media_obj->getMediaItems() as $i => $med)
				{
					$data['media'][$i]->src = $med->location;
					$data['media'][$i]->id = $med->id;
				}

				$file = $data['dir']."/".$data[media][0]->src;
			}
			else
			{
				$file = "./Customizing/global/plugins/Services/Repository/RepositoryObject/LearnLoc/templates/images/init.jpg";
			}

			list($width, $height) = getimagesize($file);

			if ($width/$height > 1 && $width/$height < 2) // Querformat
				{
				$crop = false;
			}
			else
			{
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
	*/
	//
	// get Fields of PostVars
	//
	public function getDF($a_val) {
		return $this->postdata[$a_val];
	}


	public function checkParams($a_val) {
		$services = array(
			"getCourses" => array(),
			"getLocations" => array( "course-id" ),
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
						"description" =>
						"Service requires the following parameters: " . implode(", ", $services[$a_val])
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
	public function setResponse($res) {
		header('Content-type: application/json');
		$this->response = json_encode($res);
		$this->log->write(
			"JSON-Response for User " . $this->username . ", IP: " . $_SERVER['REMOTE_ADDR'] . ": " . $this->response);
	}


	public function setImageResponse($res) {
		header('Content-type: image/jpeg');
		$this->response = $res;
		$this->log->write("Image-Response for User " . $this->username . ", IP: " . $_SERVER['REMOTE_ADDR']);
	}


	public function getResponse() {
		return $this->response;
	}
}


?>
