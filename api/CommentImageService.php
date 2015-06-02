<?php

namespace LearnLocApi;

/**
 * Class CommentImageService
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class CommentImageService implements Service
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
     * @param int $id ID of Comment
     * @param $options
     */
    public function __construct($id, $options = array())
    {
        $this->id = $id;
        $this->options = $options;
    }


    /**
     * @return string
     */
    public function getResponse()
    {
        $comment = new \ilLearnLocComment($this->id);
        $media = new \ilLearnLocMedia($comment->getMediaId());
        $media->setOptions($this->options);

        return $media->resizeFirstImage();
    }
}