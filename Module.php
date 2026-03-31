<?php

/**
 * Auto Archive
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\autoArchive;

use humhub\modules\autoArchive\models\Configuration;
use humhub\modules\autoArchive\notifications\AutoArchiveAdminNotification;
use Yii;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;

/**
 *
 * @property-read mixed $configUrl
 * @property-read Configuration $configuration
 * @property-read string[] $notifications
 */
class Module extends \humhub\components\Module
{
    public const ID = 'auto-archive';
    /**
     * @var string defines the icon
     */
    public $icon = 'archive';

    private ?Configuration $_configuration = null;

    /**
     * @throws NotFoundHttpException
     */
    public static function getInstance(): static
    {
        /** @var ?static $module */
        $module = Yii::$app->getModule(self::ID);
        if (!$module?->isEnabled) {
            throw new NotFoundHttpException('Auto Archive module not enabled');
        }
        return $module;
    }

    public function getConfiguration(): Configuration
    {
        if ($this->_configuration === null) {
            $this->_configuration = new Configuration(['settingsManager' => $this->settings]);
            $this->_configuration->loadBySettings();
        }
        return $this->_configuration;
    }

    /**
     * @inheritdoc
     */
    public function getConfigUrl()
    {
        return Url::to(['/auto-archive/config']);
    }

    /**
     * @inerhitdoc
     */
    public function getName()
    {
        return Yii::t('AutoArchiveModule.base', 'Auto Archive');
    }

    /**
     * @inerhitdoc
     */
    public function getDescription()
    {
        return Yii::t('AutoArchiveModule.base', 'Automatically archive inactive HumHub Spaces to keep your network tidy.');
    }

    /**
     * @inheridoc
     */
    public function getNotifications()
    {
        return $this->getConfiguration()->notifySpaceAdmin
            ? [AutoArchiveAdminNotification::class]
            : [];
    }
}
