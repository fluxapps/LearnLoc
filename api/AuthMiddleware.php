<?php

namespace LearnLocApi;

use Slim\Middleware;

/**
 * Class AuthMiddleware
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class AuthMiddleware extends Middleware {

	protected function initILIAS() {
		error_reporting(E_WARNING | E_ERROR);
		$base_dir = realpath(dirname(__FILE__) . '/../../../../../../../..');
		chdir($base_dir);
		require_once('include/inc.ilias_version.php');
		require_once('Services/Component/classes/class.ilComponent.php');
		if (\ilComponent::isVersionGreaterString(ILIAS_VERSION_NUMERIC, '4.2.999')) {
			require_once './Services/Context/classes/class.ilContext.php';
			\ilContext::init(\ilContext::CONTEXT_CRON);
			require_once './Services/Init/classes/class.ilInitialisation.php';
			\ilInitialisation::initILIAS();
		} else {
			$_GET['baseClass'] = 'ilStartUpGUI';
			require_once('./include/inc.get_pear.php');
			require_once('./include/inc.header.php');
		}
	}


	/**
	 * Call
	 *
	 * Perform actions specific to this middleware and optionally
	 * call the next downstream middleware.
	 */
	public function call() {
		// Skip auth middleware if we are on the route -> this will display infos about the service
		if ($this->app->request()->getPathInfo() == '/') {
			$this->next->call();
			return;
		}
		$valid = false;
		if (isset($_REQUEST['username']) && isset($_REQUEST['password'])) {
			$_POST['username'] = $_REQUEST['username'];
			$_POST['password'] = $_REQUEST['password'];

			$this->initILIAS();
			// NOTE: If the credentials are invalid, ILIAS calls die("Authentication failed.");
			// This means (sadly) that the code below won't get executed, but this would be the correct
			global $ilAuth, $ilUser;
			$valid = (bool)$ilAuth->getAuth();
		}
		if ($valid) {
			$this->next->call();
		} else {
			$this->app->halt(500, 'Invalid credentials');
		}
	}
}