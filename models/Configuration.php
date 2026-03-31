<?php

/**
 * Auto Archive
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\autoArchive\models;

use humhub\components\SettingsManager;
use Yii;
use yii\base\Model;

class Configuration extends Model
{
    public SettingsManager $settingsManager;

    public int|string|null $daysBeforeArchiving = 90;
    public bool|string $notifySpaceAdmin = true;


    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['daysBeforeArchiving'], 'integer', 'min' => 1],
            [['notifySpaceAdmin'], 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'daysBeforeArchiving' => Yii::t('AutoArchiveModule.config', 'Number of days after the last content or comment to automatically archive spaces'),
            'notifySpaceAdmin' => Yii::t('AutoArchiveModule.config', 'Notify the space admin when the space is archived'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints(): array
    {
        return [
            'daysBeforeArchiving' => Yii::t('AutoArchiveModule.config', 'Leave empty to disable auto-archiving.'),
        ];
    }

    public function loadBySettings(): void
    {
        $this->daysBeforeArchiving = (int)$this->settingsManager->get('daysBeforeArchiving', $this->daysBeforeArchiving) ?: null;
        $this->notifySpaceAdmin = (bool)$this->settingsManager->get('notifySpaceAdmin', $this->notifySpaceAdmin);
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $this->settingsManager->set('daysBeforeArchiving', $this->daysBeforeArchiving);
        $this->settingsManager->set('notifySpaceAdmin', $this->notifySpaceAdmin);

        return true;
    }
}
