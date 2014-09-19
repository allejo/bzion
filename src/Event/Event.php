<?php
/**
 * This file contains an abstract bzion event class
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Event;

use Symfony\Component\EventDispatcher\Event as SymfonyEvent;

/**
 * A general BZiON event
 *
 * Events can be serialized using PHP's `serialize()` and `unserialize()`
 * functions. Class properties present as parameters to the constructor are
 * serialized and unserialized - if you provide type hinting for a model, only
 * its ID and type are stored.
 */
abstract class Event extends SymfonyEvent implements \Serializable {
    /**
     * Serialize an event so that it can be stored in the database
     */
    public function serialize() {
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
                $value = array(
                    'id' => $value->getId(),
                    'type' => get_class($value)
                );
            }

            $data[$property->getName()] = $value;
        }

        return serialize($data);
    }

    /**
     * Call the event's constructor using serialized data
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

                // If the serialized data contained a model's ID and type, pass
                // a new instance of it
                if ($this->isModel($param)) {
                    $id = $value['id'];
                    $type = $value['type'];
                    $value = new $type($id);
                }
            } else {
                $value = null;
            }

            $pass[$param->getName()] = $value;
        }

        $class->getConstructor()->invokeArgs($this, $pass);
    }

    /**
     * Find out if the specified parameter of the Event's constructor needs a Model
     */
    private function isModel($param)
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
}
