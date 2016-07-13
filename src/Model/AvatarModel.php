<?php
/**
 * This file contains functionality relating to database objects that have avatars and identicons such as teams and players
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

use Identicon\Identicon;
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
     * @param string $idData The data (name or id) that will be used to generate an identicon
     *
     * @return string The path to the generated identicon
     */
    protected function getIdenticon($idData)
    {
        Service::getContainer()->get('logger')
            ->info('Generating new identicon for "' . $this->getName() . '" in ' . $this->getAvatarPath());

        $identicon = new Identicon();
        $imageData = $identicon->getImageData($idData, 250);

        $path = $this->getAvatarPath($imageData);

        file_put_contents(DOC_ROOT . $path, $imageData);

        return $path;
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
            // We don't use File's fread() because it's unavailable in less
            // recent PHP versions
            $path = $file->getPath() . '/' . $file->getFilename();
            $content = file_get_contents($path);

            $path = $this->getAvatarPath(null, false, false);
            $filename = $this->getAvatarFileName($content);

            $file->move(DOC_ROOT . $path, $filename);

            $this->setAvatar($path . $filename);
        }

        return $this;
    }

    /**
     * Get the path for the image used as the object's avatar
     *
     * @param  bool $url Whether to return an absolute URL
     * @return string  The path for the avatar
     */
    public function getAvatar($url = false)
    {
        if (empty($this->avatar) && $this->avatar !== null) {
            $this->resetAvatar();
        }

        if ($url) {
            return Service::getRequest()->getBaseUrl() . $this->avatar;
        }

        return $this->avatar;
    }

    /**
     * Change the avatar of the object
     *
     * @param  string $avatar The file name of the avatar
     * @return self
     */
    public function setAvatar($avatar)
    {
        if (!empty($this->avatar) && $avatar != $this->avatar) {
            // Remove the old avatar
            unlink(DOC_ROOT . $this->avatar);
        }

        // Clear the thumbnail cache
        $imagine = Service::getContainer()->get('liip_imagine.cache.manager');
        $imagine->remove($this->avatar);

        return $this->updateProperty($this->avatar, 'avatar', $avatar);
    }

    /**
     * Reset the object's avatar to an identicon
     *
     * @return self
     */
    public function resetAvatar()
    {
        $path = $this->getIdenticon($this->getName());

        return $this->setAvatar($path);
    }

    /**
     * Get the path to the avatar, creating its directory if it doesn't exist
     *
     * @param string|null $content The avatar data
     * @param bool $full Whether to return the full absolute path
     * @param bool $file Whether to include the name of the file
     * @return string The path to the avatar
     */
    private function getAvatarPath($content = null, $full = false, $file = true)
    {
        $path = static::AVATAR_LOCATION . $this->id . '/';

        if (!@file_exists(DOC_ROOT . $path)) {
            mkdir(DOC_ROOT . $path);
        }

        if ($full) {
            $path = DOC_ROOT . $path;
        }

        if ($file) {
            $path .= $this->getAvatarFileName($content);
        }

        return $path;
    }

    /**
     * Get the filename for the avatar
     *
     * @param  string|null $content The avatar data
     * @return string The file name of the avatar
     */
    private function getAvatarFileName(&$content = null)
    {
        // Calculate the avatar contents' hash, which is used to force the
        // browser to reload an image stored in the cache whenever it changes
        // (cache busting)
        //
        // MD5 is used because it's fast and we don't require a
        // cryptographically secure hashing function
        if ($content !== null) {
            $hash = substr(md5($content), 0, 7);
        } else {
            $hash = 'avatar';
        }

        return "$hash.png";
    }
}
