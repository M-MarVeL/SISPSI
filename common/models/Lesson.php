<?php

namespace common\models;

use common\mosquitto\phpMQTT;
use Yii;

/**
 * This is the model class for table "lesson".
 *
 * @property int $id
 * @property string|null $title
 * @property string|null $context
 * @property int $sections_id
 * @property int $quizzes_id
 * @property int $file_id
 * @property int $lesson_type_id
 *
 * @property CompletedLesson[] $completedLessons
 * @property File $file
 * @property LessonType $lessonType
 * @property Note[] $notes
 * @property Quiz $quizzes
 * @property Section $sections
 */
class Lesson extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'lesson';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sections_id', 'quizzes_id', 'file_id', 'lesson_type_id'], 'required'],
            [['sections_id', 'quizzes_id', 'file_id', 'lesson_type_id'], 'integer'],
            [['title'], 'string', 'max' => 70],
            [['context'], 'string', 'max' => 100],
            [['title','context'],'required'],
            [['quizzes_id'], 'exist', 'skipOnError' => true, 'targetClass' => Quiz::class, 'targetAttribute' => ['quizzes_id' => 'id']],
            [['sections_id'], 'exist', 'skipOnError' => true, 'targetClass' => Section::class, 'targetAttribute' => ['sections_id' => 'id']],
            [['file_id'], 'exist', 'skipOnError' => true, 'targetClass' => File::class, 'targetAttribute' => ['file_id' => 'id']],
            [['lesson_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => LessonType::class, 'targetAttribute' => ['lesson_type_id' => 'id']],
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
            'context' => 'Context',
            'sections_id' => 'Sections ID',
            'quizzes_id' => 'Quizzes ID',
            'file_id' => 'File ID',
            'lesson_type_id' => 'Lesson Type ID',
        ];
    }

    /**
     * Gets query for [[CompletedLessons]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompletedLessons()
    {
        return $this->hasMany(CompletedLesson::class, ['lessons_id' => 'id', 'sections_id' => 'sections_id', 'quizzes_id' => 'quizzes_id']);
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
     * Gets query for [[LessonType]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLessonType()
    {
        return $this->hasOne(LessonType::class, ['id' => 'lesson_type_id']);
    }

    /**
     * Gets query for [[Notes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNotes()
    {
        return $this->hasMany(Note::class, ['lessons_id' => 'id']);
    }

    /**
     * Gets query for [[Quizzes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQuizzes()
    {
        return $this->hasOne(Quiz::class, ['id' => 'quizzes_id']);
    }

    /**
     * Gets query for [[Sections]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSections()
    {
        return $this->hasOne(Section::class, ['id' => 'sections_id']);
    }
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        //Obter dados do registo em causa
        $id = $this->id;
        $title = $this->title;
        $context = $this->context;
        $sections_id = $this->sections_id;
        $quizzes_id = $this->quizzes_id;
        $file_id = $this->file_id;
        $lesson_type_id = $this->lesson_type_id;



        $myObj=new \stdClass();
        $myObj->id=$id;
        $myObj->title=$title;
        $myObj->context=$context;
        $myObj->sections_id=$sections_id;
        $myObj->quizzes_id=$quizzes_id;
        $myObj->file_id=$file_id;
        $myObj->lesson_type_id=$lesson_type_id;


        $myJSON = json_encode($myObj);
        if($insert)
            $this->FazPublishNoMosquitto("INSERT_LESSON",$myJSON);
        else
            $this->FazPublishNoMosquitto("UPDATE_LESSON",$myJSON);
    }

    public function afterDelete()
    {
        parent::afterDelete();
        $prod_id= $this->id;
        $myObj=new \stdClass();
        $myObj->id=$prod_id;
        $myJSON = json_encode($myObj);
        $this->FazPublishNoMosquitto("DELETE_LESSON",$myJSON);
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
