<?php
function makeImage($id, $view)
{
  if (!empty($id)) {
    echo CHtml::image($view->createUrl("/page/image", array("id" => $id)));
  }
}
if ($sr["page"]["admin"]) {
  echo CHtml::link(
	    "編集"
	    ,$this->createUrl("/page/make"));
	echo "<br/><br/>";
}
?>
<?php
if ($sr["page"]["liked"]) {
  makeImage($model->after_id, $this);
} else {
  makeImage($model->before_id, $this);
}
?>
<script>
function init() {
}
function fb_init() {
	FB.Canvas.setSize();
	FB.Canvas.scrollTo(0,0);
}
</script>
