<?php declare(strict_types=1);

namespace Calendar;

use DateTime;
use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;


/**
 * Class WeekCalendar
 *
 * @author  geniv
 * @package Calendar
 */
class WeekCalendar extends Control
{
    /** @var array */
    private $parameters;
    /** @var string template path */
    private $templatePath;
    /** @var ITranslator */
    private $translator = null;
    /** @var array */
    private $timeTable = [];
    /** @var int */
    private $seekDay = 0;
    /** @var int */
    private $selectDay = 0;
    /** @var callable */
    public $onSelectDate;
    /** @var callable */
    public $onInactiveDate;
    /** @var array */
    private $selectDate = [];


    /**
     * WeekCalendar constructor.
     *
     * @param array            $parameters
     * @param ITranslator|null $translator
     */
    public function __construct(array $parameters, ITranslator $translator = null)
    {
        parent::__construct();

        $this->parameters = (isset($parameters['week']) ? $parameters['week'] : []);
        $this->translator = $translator;

        $this->templatePath = __DIR__ . '/WeekCalendar.latte';  // implicit path

        $this->processCalendar();
    }


    /**
     * Set template path.
     *
     * @param $path
     * @return WeekCalendar
     */
    public function setTemplatePath($path): self
    {
        $this->templatePath = $path;
        return $this;
    }


    /**
     * Handle prev week.
     *
     * @param int $seekDay
     */
    public function handlePrevWeek(int $seekDay)
    {
        $this->seekDay = $seekDay - $this->parameters['offsetDay'];

        $this->processCalendar();   // re-process
        if ($this->presenter->isAjax()) {
            $this->redrawControl('calendar');
        }
    }


    /**
     * Handle next week.
     *
     * @param int $seekDay
     */
    public function handleNextWeek(int $seekDay)
    {
        $this->seekDay = $seekDay + $this->parameters['offsetDay'];

        $this->processCalendar();   // re-process
        if ($this->presenter->isAjax()) {
            $this->redrawControl('calendar');
        }
    }


    /**
     * Handle select date.
     *
     * @param int $seekDay
     * @param int $timestamp
     */
    public function handleSelectDate(int $seekDay, int $timestamp)
    {
        if ($timestamp) {
            $this->seekDay = $seekDay;
            $this->selectDay = $timestamp;

            $this->processCalendar();   // re-process
            if ($this->presenter->isAjax()) {
                $this->redrawControl('calendar');
            }

            $this->onSelectDate($timestamp);
        }
    }


    /**
     * Handle inactive date.
     *
     * @param int $timestamp
     */
    public function handleInactiveDate(int $timestamp)
    {
        $this->onInactiveDate($timestamp);
    }


    /**
     * Set select date.
     *
     * @param $values
     * @return WeekCalendar
     */
    public function setSelectDate($values): self
    {
        $this->selectDate = $values;
        $this->processCalendar();   // re-process
        return $this;
    }


    /**
     * Process calendar.
     */
    private function processCalendar()
    {
        foreach (range($this->parameters['firstDay'], $this->parameters['lastDay']) as $indexDay) {
            $day = new DateTime;
            $day->modify('+' . $indexDay . ' day +' . $this->seekDay . ' day')->setTime($this->parameters['fromTime'], 0);

            $this->timeTable[$indexDay] = [
                'day'     => $day,
                'current' => (new DateTime)->format('Y-m-d') == $day->format('Y-m-d'),
            ];

            $this->timeTable[$indexDay]['hours'] = [];
            foreach (range($this->parameters['firstHour'], $this->parameters['lastHour']) as $indexHour) {
                $hour = clone $this->timeTable[$indexDay]['day'];
                $this->timeTable[$indexDay]['hours'][$indexHour] = [
                    'hour'   => $hour->getTimestamp(),  // return GTM timestamp
                    'select' => in_array($hour, $this->selectDate),
                ];
                $this->timeTable[$indexDay]['day']->modify($this->parameters['hourModify']);
            }
        }
    }


    /**
     * Render.
     */
    public function render()
    {
        if ($this->parameters) {
            $template = $this->getTemplate();

            $template->timeTable = $this->timeTable;
            $template->seekDay = $this->seekDay;
            $template->selectDay = $this->selectDay;

            $template->setTranslator($this->translator);
            $template->setFile($this->templatePath);
            $template->render();
        } else {
            echo 'Configure missing section week!';
        }
    }
}
