<?php

namespace LearnLocApi;

/**
 * Class PingService
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class PingService implements Service {

	/**
	 * @var int
	 */
	protected $user_id = 0;


	/**
	 * @param int $user_id
	 */
	public function __construct($user_id = 0) {
		if ($user_id > 0) {
			$this->user_id = $user_id;
		} else {
			global $ilUser;
			$this->user_id = $ilUser->getId();
		}
	}


	/**
	 * @return array
	 */
	public function getResponse() {
		return array( 'status' => true );
	}
}