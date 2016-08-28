<?php
/**
 * This file contains an abstract bzion event class
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Event;

use BZIon\Debug\Debug;
use Symfony\Component\EventDispatcher\Event as SymfonyEvent;

/**
 * A general BZiON event
 *
 * Events can be serialized using PHP's `serialize()` and `unserialize()`
 * functions. Class properties present as parameters to the constructor are
 * serialized and unserialized - if you provide type hinting for a model, only
 * its ID (and type, if necessary) is stored. This means that you shouldn't
 * change the parameter names or non-abstract model type hints without writing
 * the appropriate database migrations first.
 */
abstract class Event extends SymfonyEvent implements \Serializable
{
    /**
     * Serialize an event so that it can be stored in the database
     */
    public function serialize()
    {
        $class = new \ReflectionObject($this);
        $params = $class->getConstructor()->getParameters();

        $data = array();

        // Iterate through all the parameters of the constructor
        foreach ($params as $param) {
            // Find the related class property
            $property = $class->getProperty($param->getName());
            $property->setAccessible(true);
            $value = $property->getValue($this);

            // We just need to store IDs and types for models
            if ($value !== null && $this->isModel($param)) {
                if (!$param->getClass()->isAbstract()) {
                    // The parameter is a non-abstract model, we can just
                    // store its ID since the type will be known when
                    // unserializing
                    $value = $value->getId();
                } else {
                    // The parameter is an abstract model class, we need to
                    // store the model's type as well
                    $value = array(
                        'id'   => $value->getId(),
                        'type' => get_class($value)
                    );
                }
            }

            $data[$property->getName()] = $value;
        }

        return serialize($data);
    }

    /**
     * Call the event's constructor using serialized data
     *
     * @param string $data
     */
    public function unserialize($data)
    {
        $data = unserialize($data);

        $class = new \ReflectionObject($this);
        $params = $class->getConstructor()->getParameters();

        $pass = array();

        // Iterate through all the parameters of the constructor and try to
        // locate the value for each one in the serialized data
        foreach ($params as $param) {
            if (isset($data[$param->getName()])) {
                $value = $data[$param->getName()];

                // If the serialized data contained a model's ID (and type),
                // pass a new instance of it
                if ($this->isModel($param)) {
                    if (!$param->getClass()->isAbstract()) {
                        $value = $param->getClass()
                            ->getMethod('get')
                            ->invoke(null, $value);
                    } else {
                        $id = $value['id'];
                        $type = $value['type'];
                        $value = call_user_func(array($type, 'get'), $id);
                    }
                }
            } else {
                $value = null;
            }

            $pass[$param->getName()] = $value;
        }

        $class->getConstructor()->invokeArgs($this, $pass);
    }

    /**
     * Find out if the specified parameter of the Event's constructor needs a
     * Model
     *
     * @param  \ReflectionParameter $param The constructor's parameter
     * @return bool
     */
    private static function isModel($param)
    {
        $class = $param->getClass();

        if ($class === null) {
            return false;
        }

        if ($class->isSubclassOf('\Model')) {
            return true;
        }

        if ($class->getName() === 'Model') {
            return true;
        }

        return false;
    }

    /**
     * Send a notification to the players affected by this event
     *
     * @param string $type The type of the event
     */
    public function notify($type)
    {
    }

    /**
     * Sends a notification to some players
     *
     * @param mixed    $players A single player/ID or a player/ID list
     * @param string   $type   The type of the event
     * @param null|\Player|int $except A player who should not receive a notification
     * @param \Player $except
     */
    protected function doNotify($players, $type, $except = null)
    {
        Debug::log("Notifying about $type", array(
            'players' => $players,
            'except'  => $except
        ));

        if ($except instanceof \Player) {
            $except = $except->getId();
        }

        if (!is_array($players)) {
            $players = array($players);
        }

        foreach ($players as $player) {
            if ($player instanceof \Player) {
                $player = $player->getId();
            }

            if ($player != $except) {
                $notification = \Notification::newNotification($player, $type, $this);

                \Service::getContainer()->get('event_dispatcher')->dispatch(
                    Events::NOTIFICATION_NEW,
                    new NewNotificationEvent($notification)
                );
            }
        }
    }
}
