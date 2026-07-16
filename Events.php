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

        /** @var Space $space */
        foreach (Space::find()->where(['status' => Space::STATUS_ENABLED])->each() as $space) {

            // Date of the most recent Content created in this space
            $lastContentDate = (new Query())
                ->select(['MAX(created_at)'])
                ->from('content')
                ->where(['contentcontainer_id' => $space->contentcontainer_id])
                ->scalar();

            // Date of the most recent Comment on any Content belonging to this space
            $lastCommentDate = (new Query())
                ->select(['MAX(comment.created_at)'])
                ->from('comment')
                ->innerJoin('content', 'content.id = comment.content_id')
                ->where(['content.contentcontainer_id' => $space->contentcontainer_id])
                ->scalar();

            $contentInactive = ($lastContentDate === null || $lastContentDate < $threshold);
            $commentInactive = ($lastCommentDate === null || $lastCommentDate < $threshold);

            if ($contentInactive && $commentInactive) {
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
}
