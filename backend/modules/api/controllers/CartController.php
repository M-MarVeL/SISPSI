<?php

namespace backend\modules\api\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\web\Response;


class CartController extends ActiveController
{
    public $modelClass = 'backend\modules\api\models\Cart';

    public function actionPostCart($userId, $courseId)
    {
      $cart = Yii::$app->db->createCommand("SELECT * FROM cart")->queryAll();
      return $cart;
    }













?>
