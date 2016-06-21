<?php

namespace LearnLocApi;

/**
 * Class LocationsService
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class LocationsService implements Service {

	/**
	 * @var int
	 */
	protected $id = 0;


	/**
	 * @param int $id Container-ID e.g. from a Course
	 */
	public function __construct($id = 0) {
		$this->id = $id;
	}


	/**
	 * @return array
	 */
	public function getResponse() {
		$container = \ilObjectFactory::getInstanceByRefId($this->id);
		$locations = $this->getLocations($container);
		$folders = $this->getFolders($container);

		return array(
			"course" => array(
				"id"          => $container->getRefId(),
				"description" => str_ireplace("\r\n", '<br/>', $container->getLongDescription()),
				"locations"   => array( "location" => $locations ),
				"folder"      => array( "folders" => $folders ),
			),
		);
	}


	/**
	 * @param $container
	 * @return array
	 */
	public function getFolders($container) {
		$folders = array();
		foreach ($this->getTypeIdsForContObj($container, 'fold') as $ref_id) {
			$folder = \ilObjectFactory::getInstanceByRefId($ref_id);
			$folders[] = array(
				'id'       => $folder->getRefId(),
				'title'    => $folder->getTitle(),
				'location' => $this->getLocations($folder),
				'folder'   => $this->getFolders($folder),
			);
		}

		return $folders;
	}


	/**
	 * @param $container
	 * @return array
	 */
	protected function getLocations($container) {
		global $tree, $ilAccess, $ilUser;
		/**
		 * @var $ilAccess \ilAccessHandler
		 */

		foreach ($this->getTypeIdsForContObj($container, \ilLearnLocPlugin::TYPE) as $ref_id) {
			$location = \ilObjLearnLoc::getInstance($ref_id);
			if (!$location->getOnline()) {
				if (!$ilAccess->checkAccessOfUser($ilUser->getId(), "write", "", $ref_id) ? 1 : 0) {
					continue;
				}
			}
			$return[] = $this->getLocation($ref_id);
		}

		return $return;
	}


	/**
	 * @param $ref_id
	 * @return array|null
	 */
	public function getLocation($ref_id) {
		global $tree, $ilAccess, $ilUser;
		/**
		 * @var $ilAccess \ilAccessHandler
		 */
		$location = \ilObjLearnLoc::getInstance($ref_id);
		if (!$location->getOnline()) {
			if (!$ilAccess->checkAccessOfUser($ilUser->getId(), "write", "", $ref_id) ? 1 : 0) {
				return null;
			}
		}

		return array(
			'id'             => $location->getId(),
			'title'          => $location->getTitle(),
			'offline'        => $location->getOnline() ? 0 : 1,
			'latitude'       => $location->getLatitude(),
			'longitude'      => $location->getLongitude(),
			'elevation'      => 0,
			'link'           => $this->url . 'login.php?target=fold_' . $location->getContainerId() . '&full=1',
			'description'    => str_ireplace("\r\n", '<br/>', $location->getLongDescription()),
			'show_if_near'   => 0,
			'mat_count'      => count($tree->getChilds($location->getContainerId())),
			'allow-comments' => ($this->nologin ? 0 : 1),
			'images'         => $location->getImagesDataAsArray(),
		);
	}


	/**
	 * @param $container
	 * @param string $type
	 * @return array
	 */
	protected function getTypeIdsForContObj($container, $type = 'xlel') {
		global $ilAccess, $ilUser;
		$ref_ids = array();
		$subitems = $container->getSubItems();
		if (isset($subitems[$type])) {
			foreach ($subitems[$type] as $ref_id) {
				if ($type == \ilLearnLocPlugin::TYPE
				    OR count($this->getTypeIdsForContObj(\ilObjectFactory::getInstanceByRefId($ref_id['ref_id']), \ilLearnLocPlugin::TYPE)) > 0
				    OR ($ilAccess->checkAccessOfUser($ilUser->getId(), 'create', '', $ref_id['ref_id'], 'xlel')
				        AND !\ilObjLearnLoc::_isPool($ref_id['ref_id']))
				) {
					$ref_ids[] = $ref_id['ref_id'];
				}
			}
		}

		return $ref_ids;
	}
}