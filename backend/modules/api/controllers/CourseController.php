<?php

namespace backend\modules\api\controllers;

use yii\filters\auth\HttpBasicAuth;
use yii\rest\ActiveController;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\backend\modules\api\components\CustomAuth;
use common\models\Cart;

/**
 * Default controller for the `api` module
 */
class CourseController extends ActiveController
{

    public $modelClass = 'common\models\Course';

    public $user = null;
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBasicAuth::class,
            'auth' => [$this, 'auth'],
        ];
        return $behaviors;
    }

    public function auth($username, $password)
    {
        $user = User::findByUsername($username);
        if ($user && $user->validatePassword($password)) {
            $this->user = $user;
            return $user;
        }
        throw new ForbiddenHttpException(('No Authentication'));
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


    public function actionGetCourses()
    {
        $courses = new $this->modelClass;
        $recs = $courses::find()->all();
        return $recs;

    }

    public function actionGetCourse($id)
    {
        $course = Course::findOne($id);
        return $course;
    }

    public function actionCreateCourse()
    {
        $request = Yii::$app->request;
        $course = new Course();
        $course->title = $request->post('title');
        $course->description = $request->post('description');
        $course->save();
        return $course;
    }

    public function actionPutCourse($id)
    {
        $request = Yii::$app->request;
        $course = Course::findOne($id);
        $course->title = $request->post('title');
        $course->description = $request->post('description');
        $course->save();
        return $course;
    }

    public function actionDeleteCourse($id)
    {
        $course = Course::findOne($id);
        $course->delete();
        return $course;
    }

    public function actionSearchCourse($course_name, $course_category, $course_difficulty, $course_price)
    {
        $courses = Course::find()
        ->where(['like', 'title', $course_name])
        ->andWhere(['like', 'category', $course_category])
        ->andWhere(['like', 'difficulty', $course_difficulty])
        ->andWhere(['like', 'price', $course_price])
        ->andWhere(['like', 'rating', $course_rating])
        ->all();
        return $courses;
    }
    public function actionPurchaseCourse()
    {
        $request = Yii::$app->request;
        if(!$request->isPost) return "Only POST method is allowed";
         
        $user_id = $request->post('user_id');
        $course_id = $request->post('course_id');
        
    }
}