<?php declare(strict_types=1);

namespace Calendar;

/**
 * Interface IProcessor
 *
 * @author  geniv
 * @package Calendar
 */
interface IProcessor
{

    /**
     * Process.
     *
     * @param WeekCalendar $weekCalendar
     * @return array
     */
    public function process(WeekCalendar $weekCalendar): array;
}
