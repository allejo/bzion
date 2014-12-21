<?php
namespace BZIon\Form\Transformer;

use Model;
use Symfony\Component\Form\DataTransformerInterface;

class PlayerTransformer implements DataTransformerInterface
{
    /**
     * Transforms a value assigned to the form using setData() into something we
     * can read
     *
     * @param  mixed $players The value
     * @return array
     */
    public function transform($players)
    {
        if (!$players) {
            return '';
        }

        if ($players instanceof Model) {
            return array($players);
        }

        return $players;
    }

    /**
     * Transforms the user's input into something we can read
     *
     * Even though a ModelTransformer should handle this, we use form events
     * in the PlayerType class so that errors are shown to the user
     *
     * @param  mixed $value The value
     * @return mixed
     */
    public function reverseTransform($value)
    {
        return $value;
    }
}
