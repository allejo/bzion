<?php
/**
 * This file contains functionality relating to a BZFS world file
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * A BZFlag server map
 * @package    BZiON\Models
 */
class Map extends AvatarModel implements NamedModel
{
    /**
     * The name of the map
     * @var string
     */
    protected $name;

    /**
     * A description of the map
     * @var string
     */
    protected $description;

    /**
     * The status of the map
     * @var string
     */
    protected $status;

    /**
     * The name of the database table used for queries
     */
    const TABLE = "maps";

    /**
     * The location where avatars will be stored
     */
    const AVATAR_LOCATION = "/web/assets/imgs/avatars/maps/";

    const CREATE_PERMISSION = Permission::ADD_MAP;
    const EDIT_PERMISSION = Permission::EDIT_MAP;
    const SOFT_DELETE_PERMISSION = Permission::SOFT_DELETE_MAP;
    const HARD_DELETE_PERMISSION = Permission::HARD_DELETE_MAP;

    /**
     * {@inheritdoc}
     */
    protected function assignResult($map)
    {
        $this->name = $map['name'];
        $this->description = $map['description'];
        $this->alias = $map['alias'];
        $this->avatar = $map['avatar'];
        $this->status = $map['status'];
    }

    /**
     * Add a new map
     *
     * @param string      $name        The name of the map
     * @param string|null $alias       The custom API-friendly alias of the map
     * @param string      $description The description of the map
     * @param string|null $avatar      An image of the map
     * @param string      $status      The status of the map (active, hidden, disabled or deleted)
     *
     * @return static
     */
    public static function addMap($name, $alias = null, $description = '', $avatar = null, $status = 'active')
    {
        return self::create(array(
            'name'        => $name,
            'alias'       => $alias,
            'description' => $description,
            'avatar'      => $avatar,
            'status'      => $status
        ));
    }

    /**
     * Get the name of the map
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the description of the map
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the name of the map
     *
     * @param string $name The new name
     * @return self
     */
    public function setName($name)
    {
        return $this->updateProperty($this->name, 'name', $name);
    }

    /**
     * Set the description of the map
     *
     * @param string $description The new description
     * @return self
     */
    public function setDescription($description)
    {
        return $this->updateProperty($this->description, 'description', $description);
    }

    /**
     * {@inheritdoc}
     */
    public static function getActiveStatuses()
    {
        return array('active');
    }

    /**
     * Get a query builder for news
     * @return QueryBuilder
     */
    public static function getQueryBuilder()
    {
        return new QueryBuilder('Map', array(
            'columns' => array(
                'name'   => 'name',
                'status' => 'status'
            ),
            'name' => 'name'
        ));
    }
}
