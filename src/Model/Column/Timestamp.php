<?php
/**
 * This file allows storing timestamps in models
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Model\Column;

/**
 * A timestamp column
 *
 * @todo Is there something we can do so that we don't have to assign the
 *       timestamp column for every model in Model::assignResult()?
 */
trait Timestamp
{
    /**
     * A timestamp
     *
     * @var \TimeDate
     */
    protected $timestamp;

    /**
     * Get a copy of the timestamp of the Model
     *
     * @return \TimeDate
     */
    public function getTimestamp()
    {
        return $this->timestamp->copy();
    }

    /**
     * Get a reference to the timestamp of the model
     *
     * Any changes in this timestamp will be reflected in the model's timestamp
     * and will be accessible to anyone
     *
     * @return \TimeDate
     */
    public function getOriginalTimestamp()
    {
        return $this->timestamp;
    }
}
