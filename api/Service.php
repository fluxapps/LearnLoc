<?php

namespace LearnLocApi;

require_once(dirname(dirname(__FILE__)) . '/classes/class.ilLearnLocPlugin.php');
require_once(dirname(dirname(__FILE__)) . '/classes/class.ilObjLearnLoc.php');
require_once(dirname(dirname(__FILE__)) . '/classes/class.ilLearnLocConfigGUI.php');
require_once(dirname(dirname(__FILE__)) . '/classes/class.ilObjLearnLoc.php');
require_once(dirname(dirname(__FILE__)) . '/classes/class.ilLearnLocMedia.php');
require_once(dirname(dirname(__FILE__)) . '/classes/class.ilLearnLocComment.php');

/**
 * Interface Service
 *
 * @package LearnLocApi
 */
interface Service
{

    /**
     * @return mixed
     */
    public function getResponse();

}