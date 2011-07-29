<?php
return array(
  'enctype' => 'multipart/form-data',
  'title'=>'画像登録を行ってください。',
  'elements'=>array(
    '<div id="required_div"><span class="required">*</span>は必須項目です。</div>',
    'before_image'=>array(
      'type'=>'file',
    ),
    'after_image'=>array(
      'type'=>'file',
    ),
  ),
  'buttons'=>array(
    'regist'=>array(
       'type'=>'submit',
       'label'=>'登録',
    ),
    'cancel'=>array(
      'type'=>'submit',
      'label'=>'キャンセル',
    ),
  ),
);
