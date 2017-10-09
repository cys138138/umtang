<?php
namespace umeworld\lib;

/**
 * 基础UI类
 */
class BaseUi extends \yii\helpers\BaseHtml{
	/**
	 * getter方法,由于BaseHtml没有继承yii\base\Object所以复制了那个方法过来这边
	 * @param string $name 属性名称
	 * @return mixed 不可预料的类型值
	 * @throws yii\base\InvalidCallException
	 * @throws yii\base\UnknownPropertyException
	 * @author 黄文非
	 */
    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter();
        } elseif (method_exists($this, 'set' . $name)) {
            throw new \yii\base\InvalidCallException('Getting write-only property: ' . get_class($this) . '::' . $name);
        } else {
            throw new \yii\base\UnknownPropertyException('Getting unknown property: ' . get_class($this) . '::' . $name);
        }
    }

	/**
	 * @var array 提示集合,可以分多层,以键名做标识符
	 */
	public $aTips = [];

	/**
	 * 获取文案提示内容
	 *
	 * 例如:
	 *
	 * ~~~
	 * Yii::$app->ui->getTips('element.expr');
	 * ~~~
	 * @param string $path 文案的数组路径
	 * @return string
	 * @throws \yii\base\InvalidParamException
	 * @see $aTips
	 * @author 黄文非
	 */
	public function getTips($path){
		if(!is_string($path)){
			throw new \yii\base\InvalidParamException('错误的文案路径参数');
		}

		$aPath = explode('.', $path);
		$aCurrentItem = $this->aTips;
		foreach($aPath as $item){
			if(isset($aCurrentItem[$item])){
				$aCurrentItem = $aCurrentItem[$item];
			}else{
				return '';
			}
		}
		return $aCurrentItem;
	}
}