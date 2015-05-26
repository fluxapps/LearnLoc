<?php

/** 
*  JSON Interface
* 
* @copyright studer + raimann ag
* @author Fabian Schmid
* @version $Id$
**/

require_once("./classes/class.ilLearnLocJsonService.php");
$json = new ilLearnLocJsonService($_POST);

echo $json->getResponse();
exit;
?>
