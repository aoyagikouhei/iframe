<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/screen.css" media="screen, projection" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/print.css" media="print" />
	<!--[if lt IE 8]>
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/ie.css" media="screen, projection" />
	<![endif]-->
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/form.css" />
	<title></title>
</head>
<body>

<div id="content">
<?php echo $content; ?>
</div>

<div id="fb-root"></div>
<script>
  window.fbAsyncInit = function() {
    FB.init({
      appId: '<?php echo Yii::app()->getParams()->fb_appid; ?>', 
      status: true, 
      cookie: true,
      xfbml: true});
    fb_init();
  };
</script>
<?php Yii::app()->clientScript->registerCoreScript('jquery'); ?>
<script>
jQuery(function(){
	init();
	var e = document.createElement('script'); e.async = true;
    e.src = document.location.protocol +
      '//connect.facebook.net/en_US/all.js';
    document.getElementById('fb-root').appendChild(e);
});
</script>
</body>
</html>
