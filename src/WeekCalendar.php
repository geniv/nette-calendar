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
    /** @var int */
    private $seekDay = 0;
    /** @var int */
    private $selectDay = 0;
    /** @var callable */
    public $onSelectDate;
    /** @var callable */
    public $onInactiveDate;
    /** @var array */
    private $loadData = [];
    /** @var IProcessor */
    private $processor;


    /**
     * WeekCalendar constructor.
     *
     * @param array            $parameters
     * @param ITranslator|null $translator
     * @param IProcessor       $processor
     */
    public function __construct(array $parameters, ITranslator $translator = null, IProcessor $processor)
    {
        parent::__construct();

        $this->parameters = $parameters;
        $this->translator = $translator;
        $this->processor = $processor;

        $this->templatePath = __DIR__ . '/WeekCalendar.latte';  // set path
    }


    /**
     * Get parameters.
     *
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
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
     * Get select date.
     *
     * @return array
     */
    public function getLoadData(): array
    {
        return $this->loadData;
    }


    /**
     * Set select date.
     *
     * @param array $data
     * @return WeekCalendar
     */
    public function setLoadData(array $data): self
    {
        $this->loadData = $data;
        return $this;
    }


    /**
     * Select date.
     *
     * @param string $date
     * @return WeekCalendar
     */
    public function selectDate(string $date): self
    {
        $selectDate = new DateTime($date);
        $diff = $selectDate->diff(new DateTime());
        // calculate offset day
        $offsetDay = $this->parameters['offsetDay'];
        $seekDay = intval(round($diff->days / $offsetDay) * $offsetDay);

        $this->handleSelectDate($seekDay, $selectDate->getTimestamp());
        return $this;
    }


    /**
     * Set from time.
     *
     * @param int $fromTime
     * @return WeekCalendar
     */
    public function setFromTime(int $fromTime): self
    {
        $this->parameters['fromTime'] = $fromTime;
        return $this;
    }


    /**
     * Set count block.
     *
     * @param int $countBlock
     * @return WeekCalendar
     */
    public function setCountBlock(int $countBlock): self
    {
        $this->parameters['countBlock'] = $countBlock;
        return $this;
    }


    /**
     * Set step block.
     *
     * @param string $stepBlock
     * @return WeekCalendar
     */
    public function setStepBlock(string $stepBlock): self
    {
        $this->parameters['stepBlock'] = $stepBlock;
        return $this;
    }


    /**
     * Get seek day.
     *
     * @return int
     */
    public function getSeekDay(): int
    {
        return $this->seekDay;
    }


    /**
     * Render.
     */
    public function render()
    {
        if ($this->parameters) {
            $template = $this->getTemplate();

            $template->timeTable = $this->processor->process($this);
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
