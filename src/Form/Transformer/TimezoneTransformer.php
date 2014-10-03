<?php
namespace BZIon\Form\Transformer;

use BZIon\Form\Type\TimezoneType;
use Symfony\Component\Form\DataTransformerInterface;

class TimezoneTransformer implements DataTransformerInterface
{
    /**
     * Take a timezone and put its value on the time and timezone fields, so the
     * user can see it
     *
     * @param  string $timezone The timezone
     * @return array
     */
    public function transform($timezone)
    {
        if ($timezone === null) {
            return null;
        }

        $timezones = array_keys(TimezoneType::getTimezones());

        // The provided timezone is in the reduced list of timezones that we
        // show to the user, so we can use it immediately
        if (in_array($timezone, $timezones)) {
            return $timezone;
        }

        // The timezone isn't in the list, just find one with the same offset
        // and use that instead
        foreach ($timezones as $t) {
            if (\TimeDate::now()->setTimezone($t)->offset === \TimeDate::now()->setTimezone($timezone)->offset) {
                return $t;
            }
        }

        return 'Europe/London'; // Default if we can't find a timezone
    }

    /**
     * Return a timezone ready to use internally
     *
     * @param  array  $data Symfony's form data
     * @return string
     */
    public function reverseTransform($data)
    {
        return $data;
    }
}
