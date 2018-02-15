Nette calendar
==============

Installation
------------
```sh
$ composer require geniv/nette-calendar
```
or
```json
"geniv/nette-calendar": ">=1.0.0"
```

require:
```json
"php": ">=7.0.0",
"nette/nette": ">=2.4.0"
```

Include in application
----------------------
neon configure:
```neon
# calendar
calendar:
#   autowired: false
#   processor: Calendar\Processor
    offsetDay: 7    # offset next day
    firstDay: 0     # number first day
    lastDay: 6      # number last day
    fromTime: 11    # number from hour (modify)
    countBlock: 7   # count block hour (modify)
    stepBlock: "+1 hour +30 minute" # offset hour (modify)
```

Logic processor:
----------------
must implemented interface: `Calendar\IProcessor` with process method for self logic calendar

neon configure extension:
```neon
extensions:
    calendar: Calendar\Bridges\Nette\Extension
```

callback:
---------
```php
onInactiveDate(DateTime $date)
onSelectDate(DateTime $date)
```

load date:
----------
```php
$weekCalendar->setLoadData(array $dates);
```

set variable to calendar latte:
-------------------------------
```php
$weekCalendar->addVariableTemplate('game', $game);
```

select date:
------------
```php
$weekCalendar->selectDate($date);
```

usage:
```php
protected function createComponentWeekCalendar(WeekCalendar $weekCalendar): WeekCalendar
{
    $dates = $this->reservationModel->getList()->where(['active' => true])->fetchPairs('id', 'date');
    $weekCalendar->setLoadData($dates);

    // setting calendar
    $weekCalendar->setFromTime(11);
    $weekCalendar->setCountBlock(10);
    $weekCalendar->setStepBlock('+2 hour');
    
    // $weekCalendar->setTemplatePath(__DIR__ . '/templates/WeekCalendar.latte');
    $weekCalendar->onInactiveDate[] = function (DateTime $date) {
        // callback inactive row
    };
    
    $weekCalendar->onSelectDate[] = function (DateTime $date) {
        $this->template->datum = $date;

        $this['reservationForm']->setDefaults([
            'date' => $date,
        ]);

        if ($this->isAjax()) {
            $this->redrawControl('reservationSnippet');
        }
    };
    return $weekCalendar;
}
```

usage:
```latte
{control weekCalendar}
...
{snippet reservationSnippet}
    {ifset $datum}
        {$datum|date:'d.m.Y H:i'}
        {control reservationForm}
    {/ifset}
{/snippet}
```
