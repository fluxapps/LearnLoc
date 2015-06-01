<?php

namespace LearnLocApi;

require_once(dirname(dirname(__FILE__)) . '/classes/class.ilLearnLocPlugin.php');
require_once(dirname(dirname(__FILE__)) . '/classes/class.ilObjLearnLoc.php');

interface Service
{

    /**
     * @return array
     */
    public function getResponse();

}