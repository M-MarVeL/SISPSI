<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'modules' => [
        'api' => [
        'class' => 'backend\modules\api\ModuleAPI',
                ],
        ],
    'components' => [
        'view' => [
            'theme' => [
                'pathMap' => [
                    '@app/views' => '@vendor/hail812/yii2-adminlte3/src/views'
                ],
            ],
        ],
        'request' => [
            'csrfParam' => '_csrf-backend',
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-backend', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the backend
            'name' => 'advanced-backend',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
          'errorAction' => 'site/error',
        ],

        'urlManager' => [
          'enablePrettyUrl' => true,
          'showScriptName' => false,
          'rules' => [ 
                [
              'class' => 'yii\rest\UrlRule',
              'controller' => ['api/course'],
              'tokens' => [ 
                '{id}' => '<id:\\w+>',
                '{course_name}' => '<course_name:\\w+>',
                '{course_category}' => '<course_category:\\w+>',
                '{course_difficulty}' => '<course_difficulty:\\w+>',
                '{course_price}' => '<course_price:\\w+>'
              ],
              'extraPatterns' => [
                'GET courses' => 'courses',
                'GET {id}/title' => 'title',
                'GET courses/search/{course_name}/{course_category}/{course_difficulty}/{course_price}' => 'searchcourse',
                'POST courses/purchase' => 'purchasecourse',
              ],
                    ],
              [
                'class' => 'yii\rest\UrlRule',
                'controller' => 'api/cart',
                'tokens' => [
                  '{id}' => '<id:\\w+>'
                ],
                'extraPatterns' => [

                ],
              ],
            ],

          ],

    ],
    'params' => $params,
];
