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
		if (\ilComponent::isVersionGreaterString(ILIAS_VERSION_NUMERIC, '5.1.999')) {
			require_once './Services/Context/classes/class.ilContext.php';
			\ilContext::init(\ilContext::CONTEXT_CRON);
			require_once './Services/Init/classes/class.ilInitialisation.php';
			\ilInitialisation::initILIAS();
		} elseif (\ilComponent::isVersionGreaterString(ILIAS_VERSION_NUMERIC, '4.2.999')) {
			require_once './Services/Context/classes/class.ilContext.php';
			\ilContext::init(\ilContext::CONTEXT_CRON);
			require_once './Services/Init/classes/class.ilInitialisation.php';
			\ilInitialisation::initILIAS();
		}
		else {
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
			if (\ilComponent::isVersionGreaterString(ILIAS_VERSION_NUMERIC, '5.1.999')) {
				global $ilAuthSession;
				/** @var \ilAuthSession $ilAuthSession */
				$ilAuthSession = $ilAuthSession;
//				$valid = $ilAuthSession->isAuthenticated();
				if(!$valid && $_POST['username'] && $_POST['password']) {
					if($valid = $this->authenticate($_POST['username'], $_POST['password'])) {
						global $ilUser;
						$id = \ilObjUser::_lookupId($_POST['username']);
						$ilUser = new \ilObjUser($id);
					}
				}
			} else {
				global $ilAuth, $ilUser;
				$valid = (bool)$ilAuth->getAuth();
			}


		}
		if ($valid) {
			$this->next->call();
		} else {
			$this->app->halt(500, 'Invalid credentials');
		}
	}

	private function authenticate($username, $password) {
		global $ilAuth;
		include_once './Services/Authentication/classes/Frontend/class.ilAuthFrontendCredentials.php';
		$credentials = new \ilAuthFrontendCredentials();
		$credentials->setUsername($username);
		$credentials->setPassword($password);

		include_once './Services/Authentication/classes/Provider/class.ilAuthProviderFactory.php';
		$provider_factory = new \ilAuthProviderFactory();
		$providers = $provider_factory->getProviders($credentials);

		include_once './Services/Authentication/classes/class.ilAuthStatus.php';
		$status = \ilAuthStatus::getInstance();

		include_once './Services/Authentication/classes/Frontend/class.ilAuthFrontendFactory.php';
		$frontend_factory = new \ilAuthFrontendFactory();
		$frontend_factory->setContext(\ilAuthFrontendFactory::CONTEXT_STANDARD_FORM);
		$frontend = $frontend_factory->getFrontend(
			$GLOBALS['DIC']['ilAuthSession'],
			$status,
			$credentials,
			$providers
		);

		$frontend->authenticate();

		switch($status->getStatus())
		{
			case \ilAuthStatus::STATUS_AUTHENTICATED:
				return true;

			case \ilAuthStatus::STATUS_ACCOUNT_MIGRATION_REQUIRED:
				return false;

			case \ilAuthStatus::STATUS_AUTHENTICATION_FAILED:
				return false;
		}
	}
}