<?php
namespace BZIon\Form;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class DatetimeWithTimezoneTransformer implements DataTransformerInterface
{
    /**
     * Take a TimeDate object (with a timezone) and put its value on the time
     * and timezone fields, so the user can see it
     *
     * @param  \TimeDate $time
     * @return array
     */
    public function transform($time)
    {
        if ($time === null) {
            return null;
        }

        $timezone  = $time->timezone->getName();
        $timezones = array_keys(TimezoneType::getTimezones());

        if (!in_array($timezone, $timezones)) {
            // The timezone isn't in the list, just find one with the same
            // offset and use it instead
            $timezone = 'Europe/London'; // Default if we can't find a timezone

            foreach ($timezones as $t) {
                if ($time->copy()->setTimezone($t)->offset === $time->offset) {
                    $timezone = $t;
                    break;
                }
            }
        }

        return array(
            'time' => $this->createTimeDate($time),
            'timezone' => $timezone
        );
    }

    /**
     * Take the timestamp from the time field and the timezone that the user
     * provided and combine them into a single timezoned TimeDate
     *
     * @param  array $data
     * @return \TimeDate
     */
    public function reverseTransform($data)
    {
        return $this->createTimeDate($data['time'], $data['timezone']);
    }

    /**
     * Create a TimeDate object from another one, ignoring its timezone
     *
     * @param  \TimeDate   $from     The original timestamp
     * @param  string|null $timezone The timezone to add to the object (defaults
     *                               to the PHP's default)
     * @return \TimeDate
     */
    private function createTimeDate($from, $timezone=null)
    {
        if ($from === null) {
            return null;
        }

        // Make sure it's a TimeDate instance
        $time = \TimeDate::from($from);

        return \TimeDate::create(
            $time->year,
            $time->month,
            $time->day,
            $time->hour,
            $time->minute,
            $time->second,
            $timezone
        );
    }
}
