<?php declare(strict_types=1);

namespace Calendar;

use DateTime;
use GeneralForm\ITemplatePath;
use Nette\Application\UI\Control;
use Nette\Http\Session;
use Nette\Http\SessionSection;
use Nette\Localization\ITranslator;


/**
 * Class WeekCalendar
 *
 * @author  geniv
 * @package Calendar
 */
class WeekCalendar extends Control implements ITemplatePath
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
    /** @var array */
    private $variableTemplate = [];
    /** @var SessionSection */
    private $sessionSection;


    /**
     * WeekCalendar constructor.
     *
     * @param array            $parameters
     * @param ITranslator|null $translator
     * @param IProcessor       $processor
     * @param Session          $session
     */
    public function __construct(array $parameters, ITranslator $translator = null, IProcessor $processor, Session $session)
    {
        parent::__construct();

        $this->parameters = $parameters;
        $this->translator = $translator;
        $this->processor = $processor;
        $this->sessionSection = $session->getSection(__CLASS__);
        // remove session section after refresh browser (true after restart)
        if (isset($_SERVER['HTTP_CACHE_CONTROL']) && ($_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0' || $_SERVER['HTTP_CACHE_CONTROL'] == 'no-cache')) {
            $this->sessionSection->remove();
        }

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
     * @param string $path
     */
    public function setTemplatePath(string $path)
    {
        $this->templatePath = $path;
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
            $this->sessionSection['timestamp'] = $timestamp;    // save last select to session

            if ($this->presenter->isAjax()) {
                $this->redrawControl('calendar');
            }
            $this->onSelectDate((new DateTime())->setTimestamp($timestamp));
        }
    }


    /**
     * Handle inactive date.
     *
     * @param int $timestamp
     */
    public function handleInactiveDate(int $timestamp)
    {
        $this->onInactiveDate((new DateTime())->setTimestamp($timestamp));
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
        // calculate day offset
        $offsetDay = $this->parameters['offsetDay'];
        $seekDay = intval(floor($diff->days / $offsetDay) * $offsetDay);

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
     * Add variable template.
     *
     * @param string $name
     * @param        $values
     * @return WeekCalendar
     */
    public function addVariableTemplate(string $name, $values): self
    {
        $this->variableTemplate[$name] = $values;
        return $this;
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
            $template->selectDay = $this->selectDay ?: $this->sessionSection['timestamp'];  // load timestamp from variable or session

            // add user defined variable
            foreach ($this->variableTemplate as $name => $value) {
                $template->$name = $value;
            }

            $template->setTranslator($this->translator);
            $template->setFile($this->templatePath);
            $template->render();
        } else {
            echo 'Configure missing section week!';
        }
    }
}
