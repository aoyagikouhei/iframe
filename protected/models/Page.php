<?php

class Page extends EMongoDocument
{
  public $page_id;
  public $name;
  public $email;
  public $company;
  public $phone;
  public $before_id;
  public $after_id;

  public $before_image;
  public $after_image;

  const ATTRS = "page_id,name,email,company,phone,before_id,after_id";
  
  public static function model($className=__CLASS__)
  {
    return parent::model($className);
  }

  public function getCollectionName()
  {
    return 'pages';
  }

  private function makeImage($key)
  {
    $file = $key . '_image';
    $imageId = $key . '_id';
    if (empty($this->$file)) {
      return;
    }
    $image = new Image;
    $image->filename = $this->$file->tempName;
    $image->save();
    if (!empty($this->$imageId)) {
      Image::model()->deleteByPk(new MongoID($this->$imageId));
    }
    $this->$imageId = $image->_id;
  }

  public function save($runValidation=true, $attributes=null) {
    if ("make" === $this->scenario) {
      $this->makeImage('before');
      $this->makeImage('after');
    }
    return parent::save($runValidation, explode(",", self::ATTRS));
  }
  
  public function rules()
  {
    return array(
      array('name, email', 'required', 'on' => 'regist'),
      array('email', 'email', 'on' => 'regist'),
      array('phone, company', 'safe', 'on' => 'regist'),
      array(
        'before_image, after_image'
        ,'file'
        ,'maxSize' => 1024*1024*0.5
        ,'on' => 'make'),
    );
  }
  
  public function attributeLabels()
  {
    return array(
      'name' => '名前',
      'company' => '会社名',
      'email' => 'メールアドレス',
      'phone' => '電話番号',
      'before_image' => 'いいね前の画像',
      'after_image' => 'いいね後の画像',
    );
  }
  
}
