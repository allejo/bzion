<?php
/**
 * This file contains functionality relating to database objects that have identicons such as teams and players
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

use \Identicon\Identicon;

/**
 * A Model that has a URL, an alias, and an identicon
 * @package    BZiON\Models
 */
abstract class IdenticonModel extends AliasModel
{
    /**
     * The location of identicons will stored in
     */
    const IDENTICON_LOCATION = "";

    /**
     * Get the identicon for a player. This function will create one if it does not already exist
     *
     * @return string The URL to the generated identicon
     */
    public function getIdenticon()
    {
        $fileName = $this->getIdenticonPath();

        if (!$this->hasIdenticon()) {
            $identicon = new Identicon();
            $imageDataUri = $identicon->getImageDataUri($this->getName(), 250);

            file_put_contents($fileName, file_get_contents($imageDataUri));
        }

        return Service::getRequest()->getBaseUrl() . static::IDENTICON_LOCATION . $this->getIdenticonName();
    }

    /**
     * Get the path to the identicon
     *
     * @return string The path to the image
     */
    protected function getIdenticonPath()
    {
        return DOC_ROOT . static::IDENTICON_LOCATION . $this->getIdenticonName();
    }

    /**
     * Check if the team has an identicon already made
     *
     * @return bool True if the identicon already exists
     */
    protected  function hasIdenticon()
    {
        return file_exists(static::IDENTICON_LOCATION . $this->getAlias());
    }

    /**
     * Get the file name of the identicon
     *
     * @return string The file name of the saved identicon
     */
    private function getIdenticonName()
    {
        return static::getAlias() . ".png";
    }
}