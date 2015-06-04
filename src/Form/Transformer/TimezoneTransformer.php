<?php
namespace BZIon\Form\Transformer;

use BZIon\Form\Type\TimezoneType;
use Symfony\Component\Form\DataTransformerInterface;

class TimezoneTransformer implements DataTransformerInterface
{
    /**
     * The list of timezones where the transformed string can belong
     * @var array
     */
    private $timezones;

    /**
     * Create a new TimezoneTransformer
     * @param array $timezones An array of timezones, defaults to TimezoneType::getTimezones()
     */
    public function __construct(array $timezones = null) {
        $this->timezones = ($timezones === null) ? TimezoneType::getTimezones() : $timezones;
    }

    /**
     * Take a timezone and put its value on the time and timezone fields, so the
     * user can see it
     *
     * @param  string $timezone The timezone
     * @return null|string
     */
    public function transform($timezone)
    {
        if ($timezone === null) {
            return null;
        }

        $timezones = array_keys($this->timezones);

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

        return 'UTC'; // Default if we can't find a timezone
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
