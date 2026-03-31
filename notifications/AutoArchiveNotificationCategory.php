<?php

/**
 * Auto Archive
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\autoArchive\notifications;

use humhub\modules\notification\components\NotificationCategory;
use humhub\modules\notification\targets\WebTarget;
use Yii;
use yii\base\InvalidConfigException;

/**
 * Will be automatically added in the notifications settings form
 * Must be mentioned in the BaseNotification extended classes ->category() method
 */
class AutoArchiveNotificationCategory extends NotificationCategory
{
    /**
     * @inheritdoc
     */
    public $id = 'auto-archive';

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return Yii::t('AutoArchiveModule.notification', 'Auto Archive');
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return Yii::t('AutoArchiveModule.notification', 'If you are admin of a Space, receive a notification if it is automatically archived.');
    }

    /**
     * Prevent disabling web notifications
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function getFixedSettings()
    {
        $webTarget = Yii::createObject(WebTarget::class);
        return [
            $webTarget->id,
        ];
    }
}
