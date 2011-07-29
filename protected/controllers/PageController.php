<?php
require_once('facebook/facebook.php');
class PageController extends Controller
{
  const NOIMAGE = '/../../images/noimage.jpeg';
  private $facebook;

  public function init()
  {
    parent::init();
    $this->facebook = new Facebook(array(
      'appId'  => Yii::app()->params['fb_appid']
      ,'secret' => Yii::app()->params['fb_secret']
      ,'cookie' => true,
    ));
    $this->layout = 'fb';
  }

  private function getSignedRequest() 
  {
    $sr = $this->facebook->getSignedRequest();
    $session = Yii::app()->getSession();
    if (empty($sr)) {
      $sr = unserialize($session["signed_request"]);
    } else {
      $session["signed_request"] = serialize($sr);
    }
    return $sr;
  }
  
  private function checkSr($sr) {
    if (empty($sr)) {
      $this->renderPartial('error');
      return true;
    } else {
      return false;
    }
  }

  public function actionRegist()
  {
    $sr = $this->getSignedRequest();
    if ($this->checkSr($sr)) {
      return;
    }
    $model = new Page('regist');
    $form = new CForm('application.views.page.registForm', $model);
    if ($form->submitted('regist') && $form->validate()) {
      $model->page_id = $sr['page']['id'];
      $model->save();
      $this->redirect(array("page/index"));
    } else {
      $this->render('regist', array('form' => $form));
    }
  }

  private function applyImage($model, $type) {
    $key = $type . "_image";
    $file = CUploadedFile::getInstance($model, $key);
    if (empty($file)) {
      return;
    }
    $model->$key = $file;
  }

  public function actionMake()
  {
    $sr = $this->getSignedRequest();
    if ($this->checkSr($sr)) {
      return;
    }
    $model = Page::model()->findByAttributes(array('page_id' => $sr["page"]["id"]));
    if (empty($model)) {
      $this->renderPartial('error');
      return;
    }
    $model->scenario = "make";
    $form = new CForm('application.views.page.makeForm', $model);
    if ($form->submitted('cancel')) {
      $this->redirect(array('page/index'));
    }
    if ($form->submitted('regist')) {
      $this->applyImage($model, "before");
      $this->applyImage($model, "after");
      if ($model->validate()) {
        $model->save();
        $this->redirect(array('page/index'));
      }
    }
    $this->render('make', array('form' => $form));
  }

  public function actionIndex()
  {
    $sr = $this->getSignedRequest();
    if ($this->checkSr($sr)) {
      return;
    }
    Yii::log($sr["user_id"], "info", "iframe");
    $model = Page::model()->findByAttributes(array('page_id' => $sr['page']['id']));
    if (empty($model)) {
      if ($sr['page']['admin']) {
        $this->redirect(array("page/regist"));
      } else {
        $this->render('preregist');
      }
      return;
    }

    $this->render('index', array('sr' => $sr, 'model' => $model));
  }

  public function actionImage($id)
  {
    $model = Image::model()->findByPk(new MongoId($id));
    if (is_null($model)) {
      header('Content-Type: image/jpeg');
      echo file_get_contents(dirname(__FILE__) . self::NOIMAGE);
      return;
    }
    header('Content-Type: image/' . strtolower($model->format));
    echo $model->getBytes();
  }
}
