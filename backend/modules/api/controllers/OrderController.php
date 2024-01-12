<?php

namespace backend\modules\api\controllers;

use backend\modules\api\components\CustomAuth;
use common\models\Course;
use common\models\Section;
use common\models\User;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\QueryParamAuth;
use yii\rest\ActiveController;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;

/**
 * Default controller for the `api` module
 */
class OrderController extends ActiveController
{

    public $modelClass = 'common\models\Order';

    public $user = null;
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => CustomAuth::className(),
            //'auth' => [$this, 'authCustom'],
        ];
        return $behaviors;
    }

    /*public function auth($username, $password)
    {
        $user = User::findByUsername($username);
        if ($user && $user->validatePassword($password)) {
            $this->user = $user;
            return $user;
        }
        throw new ForbiddenHttpException(('No Authentication'));
    }*/
    public function authCustom($token)
    {
        $user_ = User::findIdentityByAccessToken($token);
        if($user_) {
            $this->user=$user_; //Guardar user autenticado
            return $user_;
        }
        throw new \yii\web\ForbiddenHttpException('No authentication'); //403
    }

    public function checkAccess($action, $model = null, $params = [])
    {
        if($this->user)
        {
            if($this->user->id == 1)
            {
                if($action==="delete")
                {
                    throw new ForbiddenHttpException('Proibido');
                }
            }
        }
    }
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionItems($id)
    {
        $order = $this->modelClass::find()->where(['user_id' => $id])->one();

        if ($order === null) {
            // Handle the case where the cart is not found
            return [];
        }

        $items = \common\models\OrderItem::find()->where(['orders_id' => $order->id])->all();

        if (empty($items)) {
            return [];
        }

        $fullorder = [];

        foreach ($items as $item) {
            $fullOrder[] = [
                'id' => $item->id,
                'price' => $item->price,
                'iva_price'=> $item->iva_price,
                // Add other order item attributes as needed
            ];
        }
        return $fullorder;
    }




}