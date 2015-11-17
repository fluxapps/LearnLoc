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
 * Class ilLearnLocComment
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilLearnLocComment {

	/**
	 * @var int
	 */
	protected $id;
	/**
	 * @var int
	 */
	protected $ref_id;
	/**
	 * @var int
	 */
	protected $parent_id;
	/**
	 * @var int
	 */
	protected $user_id;
	/**
	 * @var string
	 */
	protected $title;
	/**
	 * @var string
	 */
	protected $description;
	/**
	 * @var string
	 */
	protected $body;
	/**
	 * @var datetime
	 */
	protected $creation_date;
	/**
	 * @var int
	 */
	protected $media_id;
	/**
	 * @var ilLearnLocComment[]
	 */
	public $children;
	/**
	 * @var bool
	 */
	protected $children_loaded = false;


	/**
	 * @param int $a_id
	 */
	function __construct($a_id = 0) {
		if ($a_id != 0) {
			$this->setId($a_id);
		}
		$this->read();
	}


	public function read() {
		global $ilDB;

		$ilDB->setLimit(1, 0);
		$result = $ilDB->queryF('SELECT * FROM rep_robj_xlel_comments WHERE id = %s', array( 'integer' ), array( $this->getId() ));
		while ($record = $ilDB->fetchObject($result)) {
			$this->setRefId($record->ref_id);
			$this->setParentId($record->parent_id);
			$this->setUserId($record->user_id);
			$this->setTitle($record->title);
			$this->setDescription($record->description);
			$this->setBody($record->body);
			$this->setCreationDate($record->creation_date);
			$this->setMediaId($record->media_id);
		}

		return true;
	}


	public function create() {
		global $ilDB;

		$ilDB->insert('rep_robj_xlel_comments', array(
			'id' => array(
				'integer',
				$ilDB->nextID('rep_robj_xlel_comments')
			),
			'ref_id' => array(
				'integer',
				$this->getRefId()
			),
			'parent_id' => array(
				'integer',
				$this->getParentId()
			),
			'user_id' => array(
				'integer',
				$this->getUserId()
			),
			'title' => array(
				'text',
				$this->getTitle()
			),
			'description' => array(
				'text',
				$this->getDescription()
			),
			'body' => array(
				'text',
				$this->getBody()
			),
			'creation_date' => array(
				'timestamp',
				date('Y-m-d H:i:s', $this->getCreationDate())
			),
			'media_id' => array(
				'integer',
				$this->getMediaId()
			),
		));

		return true;
	}


	/**
	 * @return bool
	 */
	public function delete() {
		global $ilDB;

		if (! $this->getId()) {
			return false;
		}
		$result = $ilDB->queryF('DELETE FROM rep_robj_xlel_comments WHERE id = %s', array( 'integer' ), array( $this->getId() ));

		$this->loadChildren();
		if (is_array($this->children)) {
			foreach ($this->children as $child) {
				$child->delete();
			}
		}

		return true;
	}


	/**
	 * @return bool
	 */
	public function update() {
		return true;
	}


	/**
	 * @return bool
	 */
	public function hasParent() {
		return ($this->getParentId()) ? true : false;
	}


	/**
	 * @return bool
	 */
	public function loadChildren() {
		if ($this->isChildrenLoaded()) {
			return false;
		}
		global $ilDB;
		$sel = 'SELECT * FROM rep_robj_xlel_comments WHERE parent_id = ' . $ilDB->quote($this->getId(), 'integer')
			. ' ORDER BY creation_date DESC LIMIT 0,1000;';
		$result = $ilDB->query($sel);
		$objs = array();
		while ($row = $ilDB->fetchObject($result)) {
			$objs[] = new ilLearnLocComment($row->id);
		}
		$this->setChildrenLoaded(true);
		$this->setChildren($objs);

		return true;
	}


	/**
	 * @return bool
	 */
	public function hasChildren() {
		$this->loadChildren();

		return count($this->getChildren()) > 0;
	}


	/**
	 * @return bool
	 */
	public function isDeletable() {
		global $ilUser, $ilAccess;

		if ($ilUser->getId() == $this->getUserId() OR $ilAccess->checkAccess('write', '', $_GET['ref_id'])) {
			return true;
		} else {
			return false;
		}
	}

	//
	// Static
	//

	/**
	 * @param int $a_ref_id
	 *
	 * @return array|bool
	 */
	public static function _getAllForRefId($a_ref_id) {
		global $ilDB;
		if ($a_ref_id == 0) {
			return false;
		}
		$objs = array();
		$sel = 'SELECT * FROM rep_robj_xlel_comments WHERE ref_id = ' . $ilDB->quote($a_ref_id, 'integer')
			. ' ORDER BY creation_date DESC LIMIT 0,1000;';
		$result = $ilDB->query($sel);
		while ($row = $ilDB->fetchObject($result)) {
			$newCommentObj = new ilLearnLocComment($row->id);
			$newCommentObj->loadChildren();
			if (! $newCommentObj->hasParent()) {
				$objs[] = $newCommentObj;
			}
		}

		return $objs;
	}


	/**
	 * @param $a_ref_id
	 * @param $from
	 * @param $count
	 *
	 * @return array
	 */
	public static function _getNumberOfCommentsForObjId($a_ref_id, $from, $count) {
		global $ilDB;
		if ($from == 1) {
			$from = 0;
		}
		$objs = array();
		$sel = 'SELECT * FROM rep_robj_xlel_comments WHERE ref_id = ' . $ilDB->quote($a_ref_id, 'integer')
			. ' ORDER BY creation_date ASC LIMIT 0,1000;';
		$result = $ilDB->query($sel);
		$end = $from + $count;
		$x = 0;
		while ($row = $ilDB->fetchObject($result)) {
			if ($x < $from) {
				$x ++;
				continue;
			}
			$newCommentObj = new ilLearnLocComment($row->id);
			$newCommentObj->loadChildren();
			if (! $newCommentObj->hasParent()) {
				$x ++;
				$objs[] = $newCommentObj;
			}
			if ($x == $end) {
				break;
			}
		}

		return $objs;
	}


	/**
	 * @param $a_ref_id
	 *
	 * @return array
	 */
	public static function _getAllMediaForRefId($a_ref_id) {
		global $ilDB;

		$mod_ids = array();
		$sel = 'SELECT media_id FROM rep_robj_xlel_comments WHERE ref_id = ' . $ilDB->quote($a_ref_id, 'integer')
			. ' ORDER BY creation_date DESC LIMIT 0,1000;';
		$result = $ilDB->query($sel);
		while ($row = $ilDB->fetchObject($result)) {
			if ($row->media_id) {
				$mod_ids[] = $row->media_id;
			}
		}

		return $mod_ids;
	}


	/**
	 * @param $a_media_id
	 *
	 * @return ilLearnLocComment
	 */
	public static function _getInstanceByMediaId($a_media_id) {
		global $ilDB;

		$result = $ilDB->query('SELECT id FROM rep_robj_xlel_comments WHERE media_id = ' . $ilDB->quote($a_media_id, 'integer') . ';');
		while ($row = $ilDB->fetchObject($result)) {
			$id = $row->id;
		}

		return new ilLearnLocComment($id);
	}


	/**
	 * @param string $body
	 */
	public function setBody($body) {
		$this->body = $body;
	}


	/**
	 * @return string
	 */
	public function getBody() {
		return $this->body;
	}


	/**
	 * @param \datetime $creation_date
	 */
	public function setCreationDate($creation_date) {
		$this->creation_date = $creation_date;
	}


	/**
	 * @return \datetime
	 */
	public function getCreationDate() {
		return $this->creation_date;
	}


	/**
	 * @param string $descreption
	 */
	public function setDescription($descreption) {
		$this->description = $descreption;
	}


	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
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
	public function getId() {
		return $this->id;
	}


	/**
	 * @param int $media_id
	 */
	public function setMediaId($media_id) {
		$this->media_id = $media_id;
	}


	/**
	 * @return int
	 */
	public function getMediaId() {
		return $this->media_id;
	}


	/**
	 * @param int $parent_id
	 */
	public function setParentId($parent_id) {
		$this->parent_id = $parent_id;
	}


	/**
	 * @return int
	 */
	public function getParentId() {
		return $this->parent_id;
	}


	/**
	 * @param int $ref_id
	 */
	public function setRefId($ref_id) {
		$this->ref_id = $ref_id;
	}


	/**
	 * @return int
	 */
	public function getRefId() {
		return $this->ref_id;
	}


	/**
	 * @param string $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}


	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
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
	public function getUserId() {
		return $this->user_id;
	}


	/**
	 * @return ilLearnLocComment[]
	 */
	public function getChildren() {
		return $this->children;
	}


	/**
	 * @param ilLearnLocComment[] $children
	 */
	public function setChildren($children) {
		$this->children = $children;
	}


	/**
	 * @return boolean
	 */
	public function isChildrenLoaded() {
		return $this->children_loaded;
	}


	/**
	 * @param boolean $children_loaded
	 */
	public function setChildrenLoaded($children_loaded) {
		$this->children_loaded = $children_loaded;
	}
}