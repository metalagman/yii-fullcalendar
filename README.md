# FullCalendar component for Yii 1.x

By Alexey Samoylov (<alexey.samoylov@gmail.com>).

## Requirements

- **PHP 5.4**
- **Yii 1.x**

### Examples

Global component configuration example:

```php
'components' => [
    'fullcalendar' => [
        'class' => 'ext.yii-fullcalendar.FullCalendar',
            'options' => [
                'buttonText' => [
                    'today' => 'Сегодня',
                    'week' => 'Неделя',
                    'day' => 'День',
                ],
                'allDayDefault' => false,
            ],
        ],
    ],
```

Usage example:

View:

```php
<?php $this->widget('ext.yii-fullcalendar.FullCalendarWidget', [ 'options' => [
    'defaultView' => 'agendaWeek',
    'header' => [
        'left' => 'prev,next today',
        'center' => 'title',
        'right' => 'agendaWeek, agendaDay',
    ],
    'allDaySlot' => false,
    'editable' => false,
    'buttonText' => [
        'today' => 'Сегодня',
        'week' => 'Неделя',
        'day' => 'День',
    ],
    'slotMinutes' => 15,
    'height' =>  5000,
    'minTime' => '08:00',
    'maxTime' => '20:00',
    'events' => [],
    'eventSources' => [
        [
            'url' => Yii::app()->request->url,
            'type' => 'POST',
            'data' => [
                'ajax' => true,
            ],
            'error' => "js:function() {
                alert('Ошибка загрузки данных!');
            }",
            'color' => 'green',
            'textColor' => 'black',
        ]
    ],
]]);
?>
```

Controller:

```php
public function actionCalendar()
{
    if (Yii::app()->request->isAjaxRequest) {
        $this->generateCalendarEvents();
        Yii::app()->end();
    }

    $this->render('calendar');
}

public function generateCalendarEvents()
{
    $events = [
        [ 'title' => 'sample event 1', 'start' => time() ],
    ];

    echo CJSON::encode($events);
}
```
### Links
* <https://github.com/russianlagman/yii-fullcalendar>
* <http://arshaw.com/fullcalendar/>
* <https://github.com/arshaw/fullcalendar/releases>
