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
     * The world size of the map
     * @var int|null
     */
    protected $world_size;

    /**
     * Whether or not the map is randomly generated each match
     * @var bool
     */
    protected $randomly_generated;

    /**
     * The number of a shots this map has
     * @var int
     */
    protected $shot_count;

    /**
     * Whether or not this map has ricochet
     * @var bool
     */
    protected $ricochet;

    /**
     * Whether or not this map has jumping
     * @var bool
     */
    protected $jumping;

    /**
     * The game mode this map supports
     *
     * @see Map::GAME_MODE_CTF
     * @see Map::GAME_MODE_AHOD
     *
     * @var int
     */
    protected $game_mode;

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

    const GAME_MODE_CTF = 1;
    const GAME_MODE_AHOD = 2;

    /**
     * {@inheritdoc}
     */
    protected function assignResult($map)
    {
        $this->name = $map['name'];
        $this->description = $map['description'];
        $this->world_size = $map['world_size'];
        $this->randomly_generated = $map['randomly_generated'];
        $this->alias = $map['alias'];
        $this->avatar = $map['avatar'];
        $this->shot_count = $map['shot_count'];
        $this->ricochet = $map['ricochet'];
        $this->jumping = $map['jumping'];
        $this->game_mode = $map['game_mode'];
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
     * Get the world size of the map
     *
     * @return int|null
     */
    public function getWorldSize()
    {
        return $this->world_size;
    }

    /**
     * Get the number of shots this map has
     *
     * @return int
     */
    public function getShotCount()
    {
        return $this->shot_count;
    }

    /**
     * Get the game mode supported by this map
     *
     * @see Map::GAME_MODE_CTF
     * @see Map::GAME_MODE_AHOD
     *
     * @return int
     */
    public function getGameMode()
    {
        return (int)$this->game_mode;
    }

    /**
     * Get whether or not the map is randomly generated each match
     *
     * @return bool
     */
    public function isRandomlyGenerated()
    {
        return (bool)$this->randomly_generated;
    }

    /**
     * Get whether or not ricochet is enabled
     *
     * @return bool
     */
    public function isRicochetEnabled()
    {
        return (bool)$this->ricochet;
    }

    /**
     * Get whether or not jumping is enabled
     *
     * @return bool
     */
    public function isJumpingEnabled()
    {
        return (bool)$this->jumping;
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
     * Set the world size of this map
     *
     * @param int $world_size
     *
     * @return Map
     */
    public function setWorldSize($world_size)
    {
        return $this->updateProperty($this->world_size, 'world_size', $world_size);
    }

    /**
     * Set the number of shots this map has
     *
     * @param int $shot_count
     *
     * @return self
     */
    public function setShotCount($shot_count)
    {
        return $this->updateProperty($this->shot_count, 'shot_count', $shot_count);
    }

    /**
     * Set whether or not this map is randomly generated
     *
     * @param bool $randomly_generated
     *
     * @return Map
     */
    public function setRandomlyGenerated($randomly_generated)
    {
        return $this->updateProperty($this->randomly_generated, 'randomly_generated', $randomly_generated);
    }

    /**
     * Set whether or not this map supports ricochet
     *
     * @param bool $ricochet
     *
     * @return self
     */
    public function setRicochetEnabled($ricochet)
    {
        return $this->updateProperty($this->ricochet, 'ricochet', $ricochet);
    }

    /**
     * Set whether or not this map supports jumping
     *
     * @param bool $jumping
     *
     * @return self
     */
    public function setJumpingEnabled($jumping)
    {
        return $this->updateProperty($this->jumping, 'jumping', $jumping);
    }

    /**
     * Set the game mode for this map
     *
     * @param int $game_mode
     *
     * @see Map::GAME_MODE_CTF
     * @see Map::GAME_MODE_AHOD
     *
     * @return Map
     */
    public function setGameMode($game_mode)
    {
        return $this->updateProperty($this->game_mode, 'game_mode', $game_mode);
    }

    /**
     * Get the number of matches played on this map
     *
     * @return int
     */
    public function countMatches()
    {
        return Match::getQueryBuilder()
            ->active()
            ->where('map')->is($this)
            ->count();
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
