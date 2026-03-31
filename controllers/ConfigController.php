<?php

/**
 * Auto Archive
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\autoArchive\controllers;

use humhub\modules\admin\components\Controller;
use humhub\modules\autoArchive\Module;
use Yii;

class ConfigController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        $model = Module::getInstance()->getConfiguration();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->view->saved();
        }

        return $this->render('index', [
            'model' => $model,
        ]);
    }
}
