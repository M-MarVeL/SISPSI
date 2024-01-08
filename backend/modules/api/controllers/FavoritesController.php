<?php

namespace backend\modules\api\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\web\Response;


class FavoritesController extends ActiveController
{
    public $modelClass = 'backend\modules\api\models\Favorites';

    public function actionFavorites(){
        
        $request = Yii::$app->request;
        $user_id = $request->post('user_id');
        $course_id = $request->post('course_id');
  
        $favorites = Yii::$app->db->createCommand("INSERT INTO favorites (user_id, course_id) VALUES ('$user_id', '$course_id')")->execute();
        return $favorites;
    }

    public function actionDeleteFavorites($id){
        
      $request = Yii::$app->request;
      
      $favorites = Yii::$app->db->createCommand("DELETE FROM favorites WHERE id = '$id'")->execute();
      return $favorites;

    }

?>
