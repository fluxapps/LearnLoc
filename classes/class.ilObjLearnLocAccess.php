<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
require_once('./Services/Repository/classes/class.ilObjectPluginAccess.php');

/**
 * Access/Condition checking for LearnLoc object
 *
 * Please do not create instances of large application classes (like ilObjLearnLoc)
 * Write small methods within this class to determin the status.
 *
 * @author        Fabian Schmid <fs@studer-raimann.ch>
 * @version       $Id$
 */
class ilObjLearnLocAccess extends ilObjectPluginAccess {

	/**
	 * @param string $a_cmd
	 * @param string $a_permission
	 * @param int $a_ref_id
	 * @param int $a_obj_id
	 * @param string $a_user_id
	 *
	 * @return bool
	 */
	function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = '') {
		global $ilUser, $ilAccess;
		if ($a_user_id == '') {
			$a_user_id = $ilUser->getId();
		}
		switch ($a_permission) {
			case 'read':
				if (!ilObjLearnLocAccess::checkOnline($a_obj_id)
					&& !$ilAccess->checkAccessOfUser($a_user_id, 'write', '', $a_ref_id)
				) {
					return false;
				}
				break;
		}

		return true;
	}


	/**
	 * @var array
	 */
	protected static $online_cache = array();


	/**
	 * @param $a_id
	 *
	 * @return bool
	 */
	static function checkOnline($a_id) {
		if (isset(self::$online_cache[$a_id])) {
			return self::$online_cache[$a_id];
		}
		global $ilDB;
		$set = $ilDB->query('SELECT is_online FROM rep_robj_xlel_data ' . ' WHERE id = ' . $ilDB->quote($a_id, 'integer'));
		$rec = $ilDB->fetchAssoc($set);

		$is_online = (boolean)$rec['is_online'];
		self::$online_cache[$a_id] = $is_online;

		return $is_online;
	}


	/**
	 * @param $a_id
	 *
	 * @return bool
	 */
	static function checkOnlineForRefId($a_id) {
		global $ilDB;
		$q = "SELECT * FROM rep_robj_xlel_data AS dat
				JOIN object_reference AS ref ON ref.obj_id = dat.id
				WHERE ref.ref_id = " . $ilDB->quote($a_id, 'integer');

		$set = $ilDB->query($q);
		$rec = $ilDB->fetchAssoc($set);

		return (boolean)$rec['is_online'];
	}


	/**
	 * @param $a_permission
	 *
	 * @return mixed
	 */
	public static function _checkPermission($a_permission) {
		global $ilUser, $ilAccess;

		return $ilAccess->checkAccessOfUser($ilUser->getId(), $a_permission, '', $_GET['ref_id']);
	}
}

?>
