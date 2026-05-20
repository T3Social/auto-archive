<?php
/**
 * Auto Archive
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

use humhub\helpers\MailStyleHelper;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\notification\models\Notification;
use humhub\widgets\mails\MailButton;
use humhub\widgets\mails\MailButtonList;
use humhub\widgets\mails\MailContentEntry;

/* @var $this yii\web\View */
/* @var $viewable \humhub\modules\autoArchive\notifications\AutoArchiveAdminNotification // TODO update */
/* @var $url string */
/* @var $date string */
/* @var $isNew boolean */
/* @var $originator \humhub\modules\user\models\User */
/* @var $source yii\db\ActiveRecord */
/* @var $contentContainer ContentContainerActiveRecord */
/* @var $space humhub\modules\space\models\Space */
/* @var $record Notification */
/* @var $html string */
/* @var $text string */

/* @var $mailMessage string */
?>

<?php $this->beginContent('@notification/views/layouts/mail.php'); ?>

<?php $contentRecord = $viewable->source ?>

<table width="100%" border="0" cellspacing="0" cellpadding="0" align="left">
    <tr>
        <td style="padding:10px; border:1px solid <?= MailStyleHelper::getBackgroundColorSecondary() ?>; border-radius:4px;">
            <?= MailContentEntry::widget([
                'originator' => $originator,
                'content' => $mailMessage, // Or $contentRecord
                'date' => $date,
                'space' => $space,
            ]) ?>
        </td>
    </tr>
    <tr>
        <td height="10"></td>
    </tr>
    <tr>
        <td>
            <?= MailButtonList::widget(['buttons' => [
                MailButton::widget([
                    'url' => $url,
                    'text' => Yii::t('ContentModule.notifications_mails', 'View Online'),
                ]),
            ]]) ?>
        </td>
    </tr>
</table>

<?php $this->endContent(); ?>
