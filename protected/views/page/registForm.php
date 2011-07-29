<?php
return array(
    'title'=>'利用前に登録を行ってください。',
    'elements'=>array(
    '<div id="required_div"><span class="required">*</span>は必須項目です。</div>',
        'name'=>array(
            'type'=>'text',
            'maxlength'=>150,
            'hint'=>'山田太郎',
        ),
        'company'=>array(
            'type'=>'text',
            'maxlength'=>32,
            'hint'=>'凸凹商事',
        ),
        'email'=>array(
            'type'=>'text',
            'maxlength'=>32,
            'hint'=>'tarou_yamada@example.com'
        ),
        'phone'=>array(
            'type'=>'text',
            'maxlength'=>32,
            'hint' => '03-1234-5678'
        ),
    ),
    'buttons'=>array(
        'regist'=>array(
            'type'=>'submit',
            'label'=>'登録',
        ),
    ),
);
