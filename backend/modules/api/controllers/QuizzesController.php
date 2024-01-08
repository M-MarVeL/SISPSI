<?php

namespace backend\modules\api\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\web\Response;


class QuizzesController extends ActiveController
{
    public $modelClass = 'backend\modules\api\models\Quizzes';

    public function actionSubmitQuizz(){

        $request = Yii::$app->request;

    }

?>
