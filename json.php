<?php

/**
 *  JSON Interface
 *
 * @copyright studer + raimann ag
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @version   $Id$
 **/

require_once("./classes/class.ilLearnLocJsonService.php");
$json = new ilLearnLocJsonService($_POST);
echo $json->getResponse();