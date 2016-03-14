<?php
/**
 * This file contains functionality relating to permissions that roles have
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * A permission that is assigned to a role
 * @package    BZiON\Models
 */
class Permission extends Model
{
    const ADD_BAN            = "add_ban";
    const ADD_MAP            = "add_map";
    const ADD_SERVER         = "add_server";
    const CREATE_NEWS        = "add_news";
    const CREATE_PAGE        = "add_page";
    const CREATE_ROLE        = "add_role";
    const CREATE_TEAM        = "add_team";
    const CREATE_USER        = "add_user";
    const EDIT_BAN           = "edit_ban";
    const EDIT_MAP           = "edit_map";
    const EDIT_MATCH         = "edit_match";
    const EDIT_NEWS          = "edit_news";
    const EDIT_PAGE          = "edit_page";
    const EDIT_ROLE          = "edit_role";
    const EDIT_SERVER        = "edit_server";
    const EDIT_TEAM          = "edit_team";
    const EDIT_USER          = "edit_user";
    const ENTER_MATCH        = "add_match";
    const HARD_DELETE_BAN    = "wipe_ban";
    const HARD_DELETE_MAP    = "wipe_map";
    const HARD_DELETE_MATCH  = "wipe_match";
    const HARD_DELETE_NEWS   = "wipe_news";
    const HARD_DELETE_PAGE   = "wipe_page";
    const HARD_DELETE_ROLE   = "wipe_role";
    const HARD_DELETE_SERVER = "wipe_server";
    const HARD_DELETE_TEAM   = "wipe_team";
    const HARD_DELETE_USER   = "wipe_user";
    const PUBLISH_NEWS       = "publish_news";
    const PUBLISH_PAGE       = "publish_page";
    const SEND_PRIVATE_MSG   = "send_pm";
    const SOFT_DELETE_BAN    = "del_ban";
    const SOFT_DELETE_MATCH  = "del_match";
    const SOFT_DELETE_MAP    = "del_map";
    const SOFT_DELETE_NEWS   = "del_news";
    const SOFT_DELETE_PAGE   = "del_page";
    const SOFT_DELETE_ROLE   = "del_role";
    const SOFT_DELETE_SERVER = "del_server";
    const SOFT_DELETE_TEAM   = "del_team";
    const SOFT_DELETE_USER   = "del_user";
    const VIEW_SERVER_LIST   = "view_server_list";
    const VIEW_VISITOR_LOG   = "view_visitor_log";
    const BYPASS_MAINTENANCE = "bypass_maintenance";

    /**
     * The name of the permission
     * @var string
     */
    protected $name;

    /**
     * The description of the permission
     * @var string
     */
    protected $description;

    /**
     * The name of the database table used for queries
     */
    const TABLE = "permissions";

    /**
     * {@inheritdoc}
     */
    protected function assignResult($permission)
    {
        $this->name        = $permission['name'];
        $this->description = $permission['description'];
    }

    /**
     * Get the description of the permission
     * @return string The description of the permission
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get the name of the permission
     * @return string The name of the permission
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get all of the existing permissions in the database
     * @return Permission[] An array of permissions
     */
    public static function getPerms()
    {
        return parent::arrayIdToModel(
            parent::fetchIds()
        );
    }

    /**
     * @param string|Permission $perm_name
     * @return Permission
     */
    public static function getPermissionFromName($perm_name)
    {
        if ($perm_name instanceof Permission) {
            return $perm_name;
        }

        return self::get(
            parent::fetchIdFrom($perm_name, "name")
        );
    }

    public static function getQueryBuilder()
    {
        return new QueryBuilder("Permission", array(
            'columns' => array(
                'name' => 'name'
            ),
            'name' => 'name'
        ));
    }
}
