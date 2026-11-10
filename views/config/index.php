<?php
/**
 * Auto Archive
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

use humhub\components\View;
use humhub\modules\admin\permissions\ManageSettings;
use humhub\modules\admin\widgets\IncompleteSetupWarning;
use humhub\modules\autoArchive\models\Configuration;
use humhub\modules\autoArchive\Module;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\form\ActiveForm;


/**
 * @var $this View
 * @var $model Configuration
 */

$module = Module::getInstance();
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <?= Yii::$app->user->can(ManageSettings::class) ? Button::light(Yii::t('AutoArchiveModule.config', 'Administration'))
            ->link(['/auto-archive/admin/index'])
            ->style('margin-left: 6px;')
            ->right()
            ->sm() : '' ?>

        <strong><?= $module->getName() ?></strong>

        <div class="text-body-secondary">
            <?= $module->getDescription() ?>
        </div>
    </div>

    <div class="panel-body">
        <div class="alert alert-info cuzy-free-module-info" role="alert">
            This module was created and is maintained by
            <a href="https://www.cuzy.app/"
               target="_blank">CUZY.app (view other modules)</a>.
            <br>
            It's free, but it's the result of a lot of design and maintenance work over time.
            <br>
            If it's useful to you, please consider
            <a href="https://www.cuzy.app/checkout/donate/"
               target="_blank">making a donation</a>
            or
            <a href="https://github.com/cuzy-app/clean-theme"
               target="_blank">participating in the code</a>.
            Thanks!
        </div>

        <?= IncompleteSetupWarning::widget() ?>

        <?php $form = ActiveForm::begin(['acknowledge' => true]); ?>
        <?= $form->field($model, 'daysBeforeArchiving')->textInput(['type' => 'number', 'step' => 1, 'min' => 1]) ?>
        <?= $form->field($model, 'notifySpaceAdmin')->checkbox() ?>
        <?= Button::save()->submit() ?>
        <?php ActiveForm::end(); ?>

    </div>
</div>
