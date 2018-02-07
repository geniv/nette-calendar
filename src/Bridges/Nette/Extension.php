<?php declare(strict_types=1);

namespace Calendar\Bridges\Nette;

use Calendar\Processor;
use Calendar\WeekCalendar;
use Nette\DI\CompilerExtension;


/**
 * Class Extension
 *
 * @author  geniv
 * @package Calendar\Bridges\Nette
 */
class Extension extends CompilerExtension
{
    /** @var array default values */
    private $defaults = [
        'autowired'  => null,
        'processor'  => Processor::class,
        'offsetDay'  => 7,
        'firstDay'   => 0,
        'lastDay'    => 6,
        'fromTime'   => 11,
        'countBlock' => 10,
        'stepBlock'  => '+1 hour +30 minute',
    ];


    /**
     * Load configuration.
     */
    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();
        $config = $this->validateConfig($this->defaults);

        // define week calendar
        $builder->addDefinition($this->prefix('week'))
            ->setFactory(WeekCalendar::class, [$config]);

        // define week calendar logic processor
        $builder->addDefinition($this->prefix('processor'))
            ->setFactory($config['processor']);

        // if define autowired then set value
        if (isset($config['autowired'])) {
            $builder->getDefinition($this->prefix('default'))
                ->setAutowired($config['autowired']);

            $builder->getDefinition($this->prefix('processor'))
                ->setAutowired($config['autowired']);
        }
    }
}
