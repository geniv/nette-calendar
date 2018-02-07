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
            $day->modify('+' . $indexDay . ' day +' . $weekCalendar->getSeekDay() . ' day')->setTime($weekCalendar->getFromTime(), 0);

            $timeTable[$indexDay] = [
                'day'     => $day,
                'current' => (new DateTime)->format('Y-m-d') == $day->format('Y-m-d'),
            ];

            $result[$indexDay]['hours'] = [];
            foreach (range(0, $weekCalendar->getCountBlock()) as $indexHour) {
                $hour = clone $timeTable[$indexDay]['day'];
                $timeTable[$indexDay]['hours'][$indexHour] = [
                    'hour'   => $hour->getTimestamp(),  // return GTM timestamp
                    'select' => in_array($hour, $weekCalendar->getSelectDate()),
                ];
                $timeTable[$indexDay]['day']->modify($weekCalendar->getStepBlock());
            }
        }

        return $result;
    }
}
