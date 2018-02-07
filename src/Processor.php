<?php declare(strict_types=1);

namespace Calendar;

/**
 * Class Processor
 *
 * @author  geniv
 * @package Calendar
 */
class Processor extends WeekCalendar implements IProcessor
{

    /**
     * Process.
     *
     * @param WeekCalendar $weekCalendar
     * @return array
     */
    public function process(WeekCalendar $weekCalendar): array
    {
//        $parameters = $weekCalendar;

        $result = [];
        foreach (range($this->parameters['firstDay'], $this->parameters['lastDay']) as $indexDay) {
            $day = new DateTime;
            $day->modify('+' . $indexDay . ' day +' . $this->seekDay . ' day')->setTime($weekCalendar->getFromTime(), 0);

            $this->timeTable[$indexDay] = [
                'day'     => $day,
                'current' => (new DateTime)->format('Y-m-d') == $day->format('Y-m-d'),
            ];

            $this->timeTable[$indexDay]['hours'] = [];
            foreach (range(0, $weekCalendar->getCountBlock()) as $indexHour) {
                $hour = clone $this->timeTable[$indexDay]['day'];
                $this->timeTable[$indexDay]['hours'][$indexHour] = [
                    'hour'   => $hour->getTimestamp(),  // return GTM timestamp
                    'select' => in_array($hour, $this->selectDate),
                ];
                $this->timeTable[$indexDay]['day']->modify($weekCalendar->getStepBlock());
            }
        }
    }
}
