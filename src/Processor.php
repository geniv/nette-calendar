<?php declare(strict_types=1);

namespace Calendar;

use DateTime;


/**
 * Class Processor
 *
 * @author  geniv
 * @package Calendar
 */
class Processor implements IProcessor
{

    /**
     * Process.
     *
     * @param WeekCalendar $weekCalendar
     * @return array
     */
    public function process(WeekCalendar $weekCalendar): array
    {
        $parameters = $weekCalendar->getParameters();

        $result = [];
        foreach (range($parameters['firstDay'], $parameters['lastDay']) as $indexDay) {
            $day = new DateTime;
            $day->modify('+' . $indexDay . ' day +' . $weekCalendar->getSeekDay() . ' day')->setTime($parameters['fromTime'], 0);

            $result[$indexDay] = [
                'day'     => $day,
                'current' => (new DateTime)->format('Y-m-d') == $day->format('Y-m-d'),
            ];

            $result[$indexDay]['hours'] = [];
            foreach (range(0, $parameters['countBlock']) as $indexHour) {
                $hour = clone $result[$indexDay]['day'];
                $result[$indexDay]['hours'][$indexHour] = [
                    'hour'   => $hour->getTimestamp(),  // return GTM timestamp
                    'select' => in_array($hour, $weekCalendar->getLoadData()),
                ];
                $result[$indexDay]['day']->modify($parameters['stepBlock']);
            }
        }
        return $result;
    }
}
