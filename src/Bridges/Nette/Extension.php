<?php declare(strict_types=1);

namespace Calendar\Bridges\Nette;

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
        'week' => [],
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
    }
}
