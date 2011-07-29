<?php

class Image extends EMongoGridFS
{
	public $width;
	public $height;
	public $format;

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function getCollectionName()
	{
		return 'images';
	}

	public function save($runValidation=true, $attributes=null)
	{
		// サイズの取得
		if (is_file($this->filename)) {
			$gm = new Gmagick();
			$gm->readImage($this->filename);
			$this->width = $gm->getImageWidth();
			$this->height = $gm->getImageHeight();
			$this->format = $gm->getImageFormat();
		}
		return parent::save($runValidation, $attributes);
	}
}
