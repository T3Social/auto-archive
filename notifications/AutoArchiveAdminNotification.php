<?php

/**
 * Auto Archive
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\autoArchive\notifications;

use humhub\modules\notification\components\BaseNotification;
use Yii;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class AutoArchiveAdminNotification extends BaseNotification
{
    /**
     * @inheritdoc
     */
    public $moduleId = 'auto-archive';

    public $requireOriginator = false;

    /**
     * @inheritdoc
     */
    public $viewName = 'autoArchiveAdminNotification';

    /**
     * @inheritdoc
     */
    public function category()
    {
        return new AutoArchiveNotificationCategory();
    }


    /**
     * For the web notification. Dynamically called (each time the notification is viewed after notification sending)
     * @inheritdoc
     * @throws Exception
     */
    public function html()
    {
        return $this->getWebAndMailSubject(true);
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function getMailSubject()
    {
        return $this->getWebAndMailSubject();
    }

    /**
     * @param bool $html
     * @return string
     * @throws Exception
     */
    public function getWebAndMailSubject(bool $html = false)
    {
        $space = $this->getSpace();
        if ($space === null) {
            return '';
        }
        return Yii::t('AutoArchiveModule.notification', 'The space {spaceName} has been automatically archived due to inactivity.', [
            'spaceName' => $html
                ? Html::tag('strong', Html::encode($space->displayName))
                : $space->displayNameSub,
        ]);
    }

    /**
     * @return string
     */
    public function getMailMessage()
    {
        return Yii::t('AutoArchiveModule.notification', 'This space has been automatically archived because no content or comment was posted for a long period of time.');
    }

    /**
     * Add params for view file
     * You can also avoid rewriting this method by using the property values directly in the view using $viewable->buttonText and $viewable->buttonUrl
     * Added attributes such as $this->buttonText are available here
     * @inerhitdoc
     */
    public function getViewParams($params = [])
    {
        // Do not use already used keys such as 'headline' or 'content'
        $result = [
            'mailMessage' => $this->getMailMessage(),
        ];

        return ArrayHelper::merge(parent::getViewParams($result), $params);
    }
}
