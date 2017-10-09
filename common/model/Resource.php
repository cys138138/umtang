<?php
namespace common\model;

use Yii;
use umeworld\lib\Query;
use yii\helpers\ArrayHelper;


class Resource extends \common\lib\DbOrmModel{
	const TYPE_BANK_PHOTO = 1; //银行照片相关
	const TYPE_PROFILE = 2; //头像类型
	const TYPE_GOODS_PHOTO = 3; //商品图片
	const TYPE_COMMENT_PHOTO = 4; //用户评价上传图片
	const TYPE_RECEPTION_SEND = 5; //接送类型
	const TYPE_TENANT_PHOTO = 6; //相册
	
	public static function tableName() {
		return Yii::$app->db->parseTable('_@resource');
	}
	
	public static function add($aData){
		(new Query())->createCommand()->insert(self::tableName(), $aData)->execute();
		return Yii::$app->db->getLastInsertID();
	}

	public function getUrl(){
		return $this->path;
	}
}

