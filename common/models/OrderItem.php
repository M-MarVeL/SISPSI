<?php

namespace common\models;

use common\mosquitto\phpMQTT;
use Yii;

/**
 * This is the model class for table "order_item".
 *
 * @property int $id
 * @property float|null $price
 * @property int $orders_id
 * @property int $courses_id
 * @property float|null $iva_price
 *
 * @property Course $courses
 * @property Order $orders
 */
class OrderItem extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'order_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['price', 'iva_price'], 'number'],
            [['orders_id', 'courses_id'], 'required'],
            [['orders_id', 'courses_id'], 'integer'],
            [['courses_id'], 'exist', 'skipOnError' => true, 'targetClass' => Course::class, 'targetAttribute' => ['courses_id' => 'id']],
            [['orders_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::class, 'targetAttribute' => ['orders_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'price' => 'Price',
            'orders_id' => 'Orders ID',
            'courses_id' => 'Courses ID',
            'iva_price' => 'Iva Price',
        ];
    }

    /**
     * Gets query for [[Courses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCourses()
    {
        return $this->hasOne(Course::class, ['id' => 'courses_id']);
    }

    /**
     * Gets query for [[Orders]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasOne(Order::class, ['id' => 'orders_id']);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        //Obter dados do registo em causa
        $id = $this->id;
        $price = $this->price;
        $orders_id = $this->orders_id;
        $courses_id = $this->courses_id;
        $iva_price = $this->iva_price;

        $myObj=new \stdClass();
        $myObj->id=$id;
        $myObj->price=$price;
        $myObj->orders_id=$orders_id;
        $myObj->courses_id=$courses_id;
        $myObj->iva_price=$iva_price;


        $myJSON = json_encode($myObj);
        if($insert)
            $this->FazPublishNoMosquitto("INSERT_ORDERITEM",$myJSON);
        else
            $this->FazPublishNoMosquitto("UPDATE_ORDERITEM",$myJSON);
    }

    public function afterDelete()
    {
        parent::afterDelete();
        $prod_id= $this->id;
        $myObj=new \stdClass();
        $myObj->id=$prod_id;
        $myJSON = json_encode($myObj);
        $this->FazPublishNoMosquitto("DELETE_ORDERITEM",$myJSON);
    }

    public function FazPublishNoMosquitto($canal,$msg)
    {
        $server = "127.0.0.1";
        $port = 1883;
        $username = ""; // set your username
        $password = "";
        $client_id = "phpMQTT-publisher"; // unique!
        $mqtt = new phpMQTT($server, $port, $client_id);
        if ($mqtt->connect(true, NULL, $username, $password))
        {
            $mqtt->publish($canal, $msg, 0);
            $mqtt->close();
        }
        else { file_put_contents('debug.output','Time out!'); }
    }
}
