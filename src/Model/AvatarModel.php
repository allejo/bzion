<?php
/**
 * This file contains functionality relating to database objects that have avatars and identicons such as teams and players
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

use \Identicon\Identicon;
use Symfony\Component\HttpFoundation\File\File;

/**
 * A Model that has a URL, an alias, and an avatar
 * @package    BZiON\Models
 */
abstract class AvatarModel extends AliasModel implements NamedModel
{
    /**
     * The url of the object's profile avatar
     * @var string
     */
    protected $avatar;

    /**
     * The location where avatars will be stored
     */
    const AVATAR_LOCATION = "";

    /**
     * Get the identicon for a model. This function will overwrite the previous avatar
     *
     * @param string $idData    The data (name or id) that will be used to generate an identicon
     *
     * @return string The path to the generated identicon
     */
    protected function getIdenticon($idData)
    {
        $fileName = $this->getAvatarFileName();

        Service::getContainer()->get('logger')
            ->info('Generating new identicon for "' . $this->getName() . '" in ' . static::AVATAR_LOCATION . $fileName);

        $identicon = new Identicon();
        $imageDataUri = $identicon->getImageDataUri($idData, 250);

        file_put_contents(DOC_ROOT . static::AVATAR_LOCATION . $fileName, file_get_contents($imageDataUri));

        return static::AVATAR_LOCATION . $fileName;
    }

    /**
     * Set the avatar of the object to be a specific file
     *
     * @param  File|null $file The avatar file
     * @return self
     */
    public function setAvatarFile($file)
    {
        if ($file) {
            $filename = $this->getAvatarFileName();

            $file->move(DOC_ROOT . static::AVATAR_LOCATION, $filename);
            $this->setAvatar(static::AVATAR_LOCATION . $filename);
        }

        return $this;
    }

    /**
     * Get the URL for the image used as the team avatar
     *
     * @return string The URL for the avatar
     */
    public function getAvatar()
    {
        if (empty($this->avatar)) {
            $this->setAvatar($this->getIdenticon($this->getName()));
        }

        return Service::getRequest()->getBaseUrl() . $this->avatar;
    }

    /**
     * Change the avatar of the object
     *
     * @param  string $avatar The URL to the team's avatar
     * @return self
     */
    public function setAvatar($avatar)
    {
        return $this->updateProperty($this->avatar, 'avatar', $avatar, 's');
    }

    /**
     * Get the filename for the avatar
     *
     * @return string The file name of the avatar
     */
    protected function getAvatarFileName()
    {
        return $this->id . ".png";
    }

}
