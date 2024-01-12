<?php

namespace common\models;

use common\mosquitto\phpMQTT;
use Yii;

/**
 * This is the model class for table "order".
 *
 * @property int $id
 * @property string|null $date
 * @property string|null $status
 * @property float|null $total_price
 * @property int|null $nif
 * @property int $user_id
 * @property int $iva_id
 *
 * @property Iva $iva
 * @property OrderItem[] $orderItems
 * @property Transaction[] $transactions
 * @property User $user
 */
class Order extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'order';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date'], 'safe'],
            [['total_price'], 'number'],
            [['nif', 'user_id', 'iva_id'], 'integer'],
            [['user_id', 'iva_id'], 'required'],
            [['status'], 'string', 'max' => 45],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['iva_id'], 'exist', 'skipOnError' => true, 'targetClass' => Iva::class, 'targetAttribute' => ['iva_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date' => 'Date',
            'status' => 'Status',
            'total_price' => 'Total Price',
            'nif' => 'Nif',
            'user_id' => 'User ID',
            'iva_id' => 'Iva ID',
        ];
    }

    /**
     * Gets query for [[Iva]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIva()
    {
        return $this->hasOne(Iva::class, ['id' => 'iva_id']);
    }

    /**
     * Gets query for [[OrderItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrderItems()
    {
        return $this->hasMany(OrderItem::class, ['orders_id' => 'id']);
    }

    /**
     * Gets query for [[Transactions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTransactions()
    {
        return $this->hasMany(Transaction::class, ['orders_id' => 'id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        //Obter dados do registo em causa
        $id = $this->id;
        $date = $this->date;
        $status = $this->status;
        $total_price = $this->total_price;
        $nif = $this->nif;
        $user_id = $this->user_id;
        $iva_id = $this->iva_id;




        $myObj=new \stdClass();
        $myObj->id=$id;
        $myObj->date=$date;
        $myObj->status=$status;
        $myObj->total_price=$total_price;
        $myObj->nif=$nif;
        $myObj->user_id=$user_id;
        $myObj->iva_id=$iva_id;


        $myJSON = json_encode($myObj);
        if($insert)
            $this->FazPublishNoMosquitto("INSERT_ORDER",$myJSON);
        else
            $this->FazPublishNoMosquitto("UPDATE_ORDER",$myJSON);
    }

    public function afterDelete()
    {
        parent::afterDelete();
        $prod_id= $this->id;
        $myObj=new \stdClass();
        $myObj->id=$prod_id;
        $myJSON = json_encode($myObj);
        $this->FazPublishNoMosquitto("DELETE_ORDER",$myJSON);
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
