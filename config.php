<?php

/**
 * Auto Archive
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

use humhub\commands\CronController;
use humhub\modules\autoArchive\Events;

return [
    'id' => 'auto-archive',
    'class' => humhub\modules\autoArchive\Module::class,
    'namespace' => 'humhub\modules\autoArchive',
    'events' => [
        [
            'class' => CronController::class,
            'event' => CronController::EVENT_ON_DAILY_RUN,
            'callback' => [Events::class, 'onCronDailyRun'],
        ],
    ],
];
