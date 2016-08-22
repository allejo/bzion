<?php
/**
 * This file contains functionality relating to the fun match teams
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * A team identified by its color in BZFlag
 * @package    BZiON\Models
 */
class ColorTeam implements TeamInterface
{
    /**
     * The color of the team
     *
     * @var string
     */
    protected $color;

    /**
     * Define a new ColorTeam
     * @param string $color The color of the team
     */
    public function __construct($color)
    {
        $this->color = strtolower($color);
    }

    /**
     * Get a unique identifier for the team
     *
     * @return string
     */
    public function getId()
    {
        return $this->color;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return ucwords($this->color) . " Team";
    }

    /**
     * {@inheritdoc}
     */
    public function getAvatar()
    {
        return "assets/imgs/team_" . $this->color . ".png";
    }

    /**
     * Return whether a team color is valid
     *
     * @param string $color The color to check
     */
    public static function isValidTeamColor($color)
    {
        return in_array($color, array('red', 'green', 'blue', 'purple'));
    }

    /**
     * Find out if a team is the same as another team
     *
     * @param mixed $team The team to compare
     * @param bool
     */
    public function isSameAs($team)
    {
        $sameType = $this instanceof $team || $team instanceof $this;

        return $sameType && $this->color === $team->color;
    }
}
