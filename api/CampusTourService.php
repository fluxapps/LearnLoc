<?php


namespace LearnLocApi;

/**
 * Class CampusTourService
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class CampusTourService implements Service
{

    /**
     * @var int
     */
    protected $ref_id = 0;

    /**
     * @var Service
     */
    protected $locations_service;

    /**
     * @param int $ref_id Ref-ID of the campus tour node
     */
    public function __construct($ref_id = 0)
    {
        $this->ref_id = ($ref_id > 0) ? $ref_id : (int) \ilLearnLocConfigGUI::_getValue('campus_tour_node');
        $obj_id = \ilObject::_lookupObjId($this->ref_id);
        $this->locations_service = new LocationsService($obj_id);
    }

    /**
     * @return array|mixed
     */
    public function getResponse()
    {
        return $this->locations_service->getResponse();
    }
}