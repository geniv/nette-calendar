<?php declare(strict_types=1);

namespace Calendar;

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
    /** @var int */
    private $fromTime;
    /** @var int */
    private $countBlock;
    /** @var string */
    private $stepBlock;
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

        $this->fromTime = $this->parameters['fromTime'];
        $this->countBlock = $this->parameters['countBlock'];
        $this->stepBlock = $this->parameters['stepBlock'];

        $this->templatePath = __DIR__ . '/WeekCalendar.latte';  // set path

        $this->processCalendar();
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
     * Get select date.
     *
     * @return array
     */
    public function getSelectDate(): array
    {
        return $this->selectDate;
    }


    /**
     * Set select date.
     *
     * @param array $values
     * @return WeekCalendar
     */
    public function setSelectDate(array $values): self
    {
        $this->selectDate = $values;
        $this->processCalendar();   // re-process
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
        $this->fromTime = $fromTime;
        return $this;
    }


    /**
     * Get count block.
     *
     * @return int
     */
    public function getCountBlock(): int
    {
        return $this->countBlock;
    }


    /**
     * Set count block.
     *
     * @param int $countBlock
     * @return WeekCalendar
     */
    public function setCountBlock(int $countBlock): self
    {
        $this->countBlock = $countBlock;
        return $this;
    }


    /**
     * Get step block.
     *
     * @return string
     */
    public function getStepBlock(): string
    {
        return $this->stepBlock;
    }


    /**
     * Get from time.
     *
     * @return int
     */
    public function getFromTime(): int
    {
        return $this->fromTime;
    }


    /**
     * Set step block.
     *
     * @param string $stepBlock
     * @return WeekCalendar
     */
    public function setStepBlock(string $stepBlock): self
    {
        $this->stepBlock = $stepBlock;
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
     * Process calendar.
     */
    private function processCalendar()
    {
        $this->timeTable = $this->processor->process($this);
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
