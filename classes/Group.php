<?php

class Group extends Controller {

    /**
     * The subject of the group
     * @var string
     */
    private $subject;

    /**
     * An array of group members
     * @var string
     */
    private $members;

    /**
     * The status of the group
     * Can be 'active', 'disabled', 'deleted' or 'reported'
     * @var string
     */
    private $status;

    /**
     * The name of the database table used for queries
     */
    const TABLE = "groups";

    /**
     * Construct a new group
     * @param int $id The group's id
     */
    function __construct($id) {

        parent::__construct($id);
        $group = $this->result;

        $this->subject = $group['subject'];
        $this->members = unserialize($group['members']);
        $this->status = $group['status'];
    }

}
