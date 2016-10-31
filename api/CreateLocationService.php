<?php

namespace LearnLocApi;

/**
 * Class CreateLocationService
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class CreateLocationService implements Service {

	/**
	 * @var int
	 */
	protected $parent_id = 0;
	/**
	 * @var int
	 */
	protected $user_id = 0;
	/**
	 * @var array
	 */
	protected $data = array(
		'title'       => '',
		'description' => '',
		'image'       => '', // Base64 encoded
		'latitude'    => null,
		'longitude'   => null,
		'address'     => '',
	);


	/**
	 * @param $parent_id Ref-ID!! of a parent
	 * @param $data
	 * @param int $user_id
	 */
	function __construct($parent_id, $data, $user_id = 0) {
		global $ilUser;

		$this->parent_id = $parent_id;
		$this->user_id = $user_id ? $user_id : $ilUser->getId();
		$this->data = array_merge($this->data, $data);
	}


	/**
	 * @return mixed
	 */
	public function getResponse() {
		try {
			$id = $this->createLocation();

			$course = new LocationsService($this->user_id);
			
			return $course->getLocation($id);
		} catch (\Exception $e) {
			return array( 'error' => $e->getMessage() );
		}
	}


	/**
	 * @return int
	 */
	protected function createLocation() {
		$location = new \ilObjLearnLoc();
		$location->setTitle($this->get('title'));
		$location->setDescription($this->get('description'));
		$location->setOnline(true);
		$location->setLatitude($this->get('latitude'));
		$location->setLongitude($this->get('longitude'));
		$location->setElevation(16);
		$location->setAddress($this->get('address'));
		$location->create();
		$location->createReference();
		$location->setPermissions($this->parent_id);
		$location->putInTree($this->parent_id);
        $location->createFolder();


		if ($this->get('image')) {
			$mob = new \ilLearnLocMedia();
			$mob->setTitle('lelinitmob');
			$mob->create($location->getId());
			$name = '/img_ws_' . time() . '_' . rand(1000, 9999) . '.jpg';
			$file_upload = $mob->getPath() . $name;
			$img = str_replace('data:image/png;base64,', '', $this->get('image'));
			$img = str_replace(' ', '+', $img);
			$data = base64_decode($img);
			file_put_contents($file_upload, $data);
			$file['image']['tmp_name'] = $file_upload;
			$file['image']['name'] = $name;
			$mob->setFile($file);
			$mob->addImage();
			$location->setInitMobId($mob->getId());
			$location->update();
		}

		return $location->getRefId();
	}


	/**
	 * @param $key
	 * @return null
	 */
	protected function get($key) {
		return isset($this->data[$key]) ? $this->data[$key] : null;
	}
}