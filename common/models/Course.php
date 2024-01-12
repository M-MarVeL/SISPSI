<?php

namespace common\models;

use common\mosquitto\phpMQTT;
use Yii;

/**
 * This is the model class for table "course".
 *
 * @property int $id
 * @property string|null $title
 * @property string|null $description
 * @property float|null $price
 * @property int|null $skill_level
 * @property int $user_id
 * @property int $category_id
 * @property int $file_id
 *
 * @property CartItem[] $cartItems
 * @property Category $category
 * @property Enrollment[] $enrollments
 * @property Favorite[] $favorites
 * @property File $file
 * @property OrderItem[] $orderItems
 * @property Rating[] $ratings
 * @property Section[] $sections
 * @property User $user
 */
class Course extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'course';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['price'], 'number'],
            [['skill_level', 'user_id', 'category_id', 'file_id'], 'integer'],

            [['user_id', 'category_id', 'file_id'], 'required'],
            [['title', 'description'], 'string', 'max' => 150],
            [['title', 'description','price','skill_level','category_id'], 'required'],


            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
            [['file_id'], 'exist', 'skipOnError' => true, 'targetClass' => File::class, 'targetAttribute' => ['file_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'description' => 'Description',
            'price' => 'Price',
            'skill_level' => 'Skill Level',
            'user_id' => 'User ID',
            'category_id' => 'Category ID',
            'file_id' => 'File ID',
        ];
    }

    /**
     * Gets query for [[CartItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCartItems()
    {
        return $this->hasMany(CartItem::class, ['courses_id' => 'id']);
    }

    /**
     * Gets query for [[Category]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    /**
     * Gets query for [[Enrollments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEnrollments()
    {
        return $this->hasMany(Enrollment::class, ['courses_id' => 'id']);
    }

    /**
     * Gets query for [[Favorites]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFavorites()
    {
        return $this->hasMany(Favorite::class, ['courses_id' => 'id']);
    }

    /**
     * Gets query for [[File]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFile()
    {
        return $this->hasOne(File::class, ['id' => 'file_id']);
    }

    /**
     * Gets query for [[OrderItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrderItems()
    {
        return $this->hasMany(OrderItem::class, ['courses_id' => 'id']);
    }

    /**
     * Gets query for [[Ratings]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRatings()
    {
        return $this->hasMany(Rating::class, ['courses_id' => 'id']);
    }

    /**
     * Gets query for [[Sections]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSections()
    {
        return $this->hasMany(Section::class, ['courses_id' => 'id', 'user_id' => 'user_id', 'category_id' => 'category_id']);
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
        $title = $this->title;
        $description = $this->description;
        $price = $this->price;
        $skill_level = $this->skill_level;
        $user_id = $this->user_id;
        $category_id = $this->category_id;
        $file_id = $this->file_id;


        $myObj=new \stdClass();
        $myObj->id=$id;
        $myObj->title=$title;
        $myObj->description=$description;
        $myObj->price=$price;
        $myObj->skill_level=$skill_level;
        $myObj->user_id=$user_id;
        $myObj->category_id=$category_id;
        $myObj->file_id=$file_id;

        $myJSON = json_encode($myObj);
        if($insert)
            $this->FazPublishNoMosquitto("INSERT_COURSE",$myJSON);
        else
            $this->FazPublishNoMosquitto("UPDATE_COURSE",$myJSON);
    }

    public function afterDelete()
    {
        parent::afterDelete();
        $prod_id= $this->id;
        $myObj=new \stdClass();
        $myObj->id=$prod_id;
        $myJSON = json_encode($myObj);
        $this->FazPublishNoMosquitto("DELETE_COURSE",$myJSON);
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
