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

    public function actionAddCart(){

      $request = Yii::$app->request;
      $cart_id = $request->post('cart_id');
      $course_id = $request->post('course_id');

      $cart = Yii::$app->db->createCommand("INSERT INTO cart_items (cart_id, course_id) VALUES ('$cart_id', '$course_id')")->execute();
      return $cart;
    }

    public function actionDeleteCart($id){

      $cart = Yii::$app->db->createCommand("DELETE FROM cart_items WHERE id = '$id'")->execute();
      return $cart;
    }

    public function actionGetCart($id){

      $cart = Yii::$app->db->createCommand("SELECT * FROM cart_items WHERE cart_id = '$id'")->queryAll();
      return $cart;
    }
}









?>
