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

require_once("./Modules/Folder/classes/class.ilObjFolder.php");
@include_once('./classes/class.ilLink.php');
@include_once('./Services/Link/classes/class.ilLink.php');

/**
 * Application class for LearnLoc Folder Object.
 *
 * @author         Fabian Schmid <fs@studer-raimann.ch>
 *
 * $Id$
 */
class ilLearnLocFolder extends ilObjFolder {

    const PERMISSION_VISIBLE = 2;

	/**
	 * @param ilObjLearnLoc $parent_obj
	 * @param int           $a_id
	 */
	function __construct(ilObjLearnLoc $parent_obj, $a_id = 0) {
		$this->parent_obj = $parent_obj;
		$this->parent_id = $parent_obj->getId();
		parent::__construct($a_id);
	}


	/**
	 * @return int
	 */
	public function doCreate() {
		global $tree, $rbacreview, $rbacadmin;
		$parent = $tree->getParentId($this->parent_obj->getRefId());
		$this->setTitle("Pool: " . $this->parent_obj->getTitle());
		$this->setDescription("<a href='goto.php?target=xlel_" . $this->parent_obj->getRefId() . "'>Switch to LearningLocation</a>");
		$this->setOwner(6);
		$this->create();
		$this->createReference();
		$this->putInTree($parent);
		$this->setPermissions($parent);

		$parentRoles = $rbacreview->getParentRoleIds($this->getRefId());


		foreach ($parentRoles as $i => $parRol) {
			$ops = $rbacreview->getOperationsOfRole($parRol["obj_id"], "fold", $parRol["parent"]); //getOperationsOfRole($parRol["obj_id"], "fold", $parRol["parent"]);
			$key = array_search(self::PERMISSION_VISIBLE, $ops);
			unset($ops[$key]);

			$rbacadmin->revokePermission($this->getRefId(), $parRol["obj_id"]);
			$rbacadmin->grantPermission($parRol["obj_id"], $ops, $this->getRefId());
		}

		return $this->getRefId();
	}


	public function update() {
		$this->setTitle("Pool: " . $this->parent_obj->getTitle());

		return parent::update();
	}
}