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
#   autowired: false    # default null, false => disable autowiring (in case multiple linked extension) | self
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
onInactiveDate(int $timestamp)
onSelectDate(int $timestamp)
```

load date:
----------
```php
$weekCalendar->setLoadData(array $dates);
```

select date:
------------
```php
$weekCalendar->selectDate($timestamp);
```

usage:
```php
protected function createComponentWeekCalendar(WeekCalendar $weekCalendar): WeekCalendar
{
    $dates = $this->reservationModel->getList()->where(['active' => true])->fetchPairs('id', 'date');
    $weekCalendar->setLoadData($dates);

    $weekCalendar->onInactiveDate[] = function ($timestamp) {
        // callback inactive row
    };
    
    $weekCalendar->onSelectDate[] = function ($timestamp) {
        $this->template->datum = $timestamp;

        $this['reservationForm']->setDefaults([
            'date' => date('Y-m-d H:i:s', $timestamp),
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
