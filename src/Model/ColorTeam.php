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

    public function getAvatar()
    {
        return "assets/imgs/team_" . $this->color . ".png";
    }
}
