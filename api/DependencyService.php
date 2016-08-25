<?php
namespace LearnLocApi;


/**
 * Class DependencyService
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
class DependencyService implements Service{

	protected $ref_id;

	public function __construct($id) {
		$this->ref_id = $id;
	}

	/**
	 * @return mixed
	 */
	public function getResponse() {
		return array("success" => true);
	}
}