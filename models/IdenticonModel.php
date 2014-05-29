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
     * @param  string $idData    The data (name or id) that will be used to generate an identicon
     * @param  string $file_name The name of the file that will be created for the identicon
     *
     * @return string The URL to the generated identicon
     */
    public function getIdenticon($idData, $file_name)
    {
        $fileName = $this->getIdenticonPath($file_name);

        if (!$this->hasIdenticon()) {
            $identicon = new Identicon();
            $imageDataUri = $identicon->getImageDataUri($idData, 250);

            file_put_contents($fileName, file_get_contents($imageDataUri));
        }

        return Service::getRequest()->getBaseUrl() . static::IDENTICON_LOCATION . $file_name . ".png";
    }

    /**
     * Get the path to the identicon
     *
     * @param  string $file_name The file name of the identicon we're getting the path for
     *
     * @return string The path to the image
     */
    protected function getIdenticonPath($file_name)
    {
        return DOC_ROOT . static::IDENTICON_LOCATION . $file_name . ".png";
    }

    /**
     * Check if the team has an identicon already made
     *
     * @return bool True if the identicon already exists
     */
    protected function hasIdenticon()
    {
        return file_exists(static::IDENTICON_LOCATION . $this->getAlias());
    }
}