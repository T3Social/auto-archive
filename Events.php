<?php

/**
 * Auto Archive
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\autoArchive;

use humhub\components\console\Application;
use humhub\modules\autoArchive\commands\AutoArchiveController;
use humhub\modules\autoArchive\notifications\AutoArchiveAdminNotification;
use humhub\modules\space\models\Space;
use Yii;
use yii\db\Query;
use yii\helpers\Console;

class Events
{
    /**
     * @param $event
     * @return void
     */
    public static function onConsoleApplicationInit($event)
    {
        /** @var Application $application */
        $application = $event->sender;
        $application->controllerMap['auto-archive'] = AutoArchiveController::class;
    }

    /**
     * Daily cron job: archive spaces inactive for the configured number of days.
     *
     * A space is archived when BOTH its last Content AND its last Comment
     * are older than the configured threshold (or do not exist at all).
     *
     * @param $event
     * @return void
     */
    public static function onCronDailyRun($event): void
    {
        /** @var ?Module $module */
        $module = Yii::$app->getModule('auto-archive');
        if (!$module || !$module->isEnabled) {
            return;
        }

        $configuration = $module->getConfiguration();
        $daysBeforeArchiving = $configuration->daysBeforeArchiving;

        if (!$daysBeforeArchiving) {
            return;
        }

        $controller = $event->sender;
        $controller->stdout('Auto Archive: scanning spaces for inactivity...' . PHP_EOL, Console::FG_YELLOW);

        $threshold = date('Y-m-d H:i:s', strtotime("-{$daysBeforeArchiving} days"));
        $archived = 0;

        // The spaces whose latest content, or latest comment, is recent enough: two grouped queries for
        // all the enabled spaces, instead of two aggregate queries per space
        $activeContainerIds = static::findActiveContainerIds($threshold);

        /** @var Space $space */
        foreach (Space::find()->where(['status' => Space::STATUS_ENABLED])->each() as $space) {
            if (!isset($activeContainerIds[$space->contentcontainer_id])) {
                Yii::warning('Auto Archiving Space "' . $space->displayName . '" for inactivity.', Module::ID);
                $space->archive();
                $archived++;

                if ($configuration->notifySpaceAdmin) {
                    // Notify all admins and the owner of the space
                    AutoArchiveAdminNotification::instance()
                        ->about($space)
                        ->sendBulk($space->getAdminsQuery());
                }
            }
        }

        $controller->stdout('Auto Archive: archived ' . $archived . ' space(s).' . PHP_EOL, Console::FG_GREEN);
    }

    /**
     * Content containers of the enabled spaces that had a Content created, or a Comment posted on one of
     * their Contents, at or after the given date (a space is inactive when it is NOT in this list:
     * no content and no comment, or only ones older than the threshold)
     *
     * @return array<int, true> indexed by contentcontainer_id
     */
    public static function findActiveContainerIds(string $threshold): array
    {
        $enabledSpaceContainers = (new Query())
            ->select('contentcontainer_id')
            ->from('space')
            ->where(['status' => Space::STATUS_ENABLED]);

        // Spaces whose most recent Content is recent enough
        $contentActiveIds = (new Query())
            ->select('contentcontainer_id')
            ->from('content')
            ->where(['contentcontainer_id' => $enabledSpaceContainers])
            ->groupBy('contentcontainer_id')
            ->having(['>=', 'MAX(created_at)', $threshold])
            ->column();

        // Spaces whose most recent Comment (on any of their Contents) is recent enough
        $commentActiveIds = (new Query())
            ->select('content.contentcontainer_id')
            ->from('comment')
            ->innerJoin('content', 'content.id = comment.content_id')
            ->where(['content.contentcontainer_id' => $enabledSpaceContainers])
            ->groupBy('content.contentcontainer_id')
            ->having(['>=', 'MAX(comment.created_at)', $threshold])
            ->column();

        return array_fill_keys(array_map('intval', array_merge($contentActiveIds, $commentActiveIds)), true);
    }
}
