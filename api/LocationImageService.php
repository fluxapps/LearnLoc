<?php

namespace LearnLocApi;

/**
 * Class LocationImageService
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class LocationImageService implements Service
{

    /**
     * @var int
     */
    protected $id;

    /**
     * @var array
     */
    protected $options = array();

    /**
     * @var int
     */
    protected $ref_id;
    /**
     * @var bool
     */
    protected $data_only = false;


    /**
     * @param int $id Object-ID of Location object
     * @param $options
     */
    public function __construct($id, $options = array(), $data_only = false)
    {
        $this->id = $id;
        $this->ref_id = array_pop(\ilObject::_getAllReferences($this->id));
        $this->options = $options;
        $this->data_only = $data_only;
    }


    /**
     * @return string
     */
    public function getResponse()
    {
        $obj = new \ilObjLearnLoc($this->ref_id);
        $media = new \ilLearnLocMedia($obj->getInitMobId());
        $media->setOptions($this->options);

        return $media->resizeFirstImage();
    }
}