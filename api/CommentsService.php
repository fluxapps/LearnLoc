<?php


namespace LearnLocApi;


class CommentsService implements Service
{

    /**
     * @var int
     */
    protected $location_id = 0;

    /**
     * @var int
     */
    protected $start = 0;

    /**
     * @var int
     */
    protected $count = 100;


    /**
     * @param $location_id
     * @param $start
     * @param $count
     */
    public function __construct($location_id, $start, $count)
    {
        $this->location_id = $location_id;
        $this->start = $start;
        $this->count = $count;
    }


    /**
     * @return mixed
     */
    public function getResponse()
    {
        global $ilUser;

        $return = array(
            'comments' => array(
                'count' => 0,
                'comment' => array(),
            )
        );

        $comments = \ilLearnLocComment::_getNumberOfCommentsForObjId($this->location_id, $this->start, $this->count);
        foreach ($comments as $comment) {
            $replies = array();
            foreach ((array) $comment->children as $child) {
                $replies[] = array(
                    'id' => $child->getId(),
                    'title' => $child->getTitle(),
                    'body' => $child->getBody(),
                    'username' => $ilUser->_lookupFullname($child->getUserId()),
                    'date' => strtotime($child->getCreationDate()),
                    'haspicture' => ($child->getMediaId() > 0) ? 1 : 0,
                );
            }
            $return['comments']['comment'][] = array(
                'id' => $comment->getId(),
                'title' => $comment->getTitle(),
                'body' => $comment->getBody(),
                'username' => $ilUser->_lookupFullname($comment->getUserId()),
                'date' => strtotime($comment->getCreationDate()),
                'haspicture' => ($comment->getMediaId() > 0) ? 1 : 0,
                'replies' => $replies
            );
        }
        $return['comments']['count'] = count($return['comments']['comment']);

        return $return;
    }
}