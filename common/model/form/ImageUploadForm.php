<?php
namespace common\model\form;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * 通用图片上传表单
 */
class ImageUploadForm extends \yii\base\Model{
	public $aRules = [];

	/**
	 * @var yii\web\UploadFile 上传封面的实例
	 */
	public $oImage = null;

	/**
	 * @var bool 是否通过百度编辑器上传
	 */
	public $savePath = '';

	/**
	 * @var callable 自定义验证函数
	 */
	public $fCustomValidator = null;

	/**
	 * @var string 上传后的图片路径,基于@p.resource的相对路径
	 */
	public $savedFile = '';

	/**
	 * @var callable 命名函数
	 */
	public $fBuildFilename = null;
	
	public $tfn = '';
	
	/**
	 * @var int 生成图宽
	 */
	public $toWidth = null;
	/**
	 * @var int 生成图高
	 */
	public $toHeight = null;

	/**
	 * @inheritedoc
	 */
	public function rules(){
		return ArrayHelper::merge([
			['oImage', 'required'],
			'image' => ['oImage', 'image'],
			'base' => ['oImage', 'file', 'maxSize' => 2048000],
			'custom' => ['oImage', 'customValidate'],
		], $this->aRules);
	}

	/**
	 * 验证普通图片尺寸
	 * @param mixed $param
	 * @param string $attrName
	 * @return boolean
	 */
	public function customValidate($param, $attrName) {
		if(is_callable($this->fCustomValidator)){
			$function = $this->fCustomValidator;
			return $function($this);
		}else{
			return true;
		}
	}

	/**
	 * 上传图片
	 * @return boolean
	 */
	public function upload($savePath = null){
		if(!$this->validate()){
			return false;
		}

		$filePath = '';
		$resourcePath = Yii::getAlias('@p.resource');

		//获取保存的文件名
		$saveFileName = '';
		if(is_callable($this->fBuildFilename)){
			$fBuildFilename = $this->fBuildFilename;
			$saveFileName = $fBuildFilename($this->oImage);
		}else{
			$saveFileName = $this->_buildFileName($this->oImage);
		}

		if($savePath && is_dir(Yii::getAlias("@p.resource/$savePath"))){
			$filePath = $savePath . '/' . $saveFileName;
			if(strpos($filePath, $resourcePath) === 0){
				throw Yii::$app->buildError('自定义的的保存路径只要是@p.resource的相对路径即可,不需要包含@p.resource');
			}
		}else{
			mkdir(Yii::getAlias("@p.resource/$savePath"));
			//$filePath = Yii::getAlias('@p.temp_upload') . '/' . $saveFileName;
			$filePath = $savePath . '/' . $saveFileName;
		}

		$result = $this->oImage->saveAs($resourcePath . '/' . $filePath);
		if(!$result){
			$this->addError('oImage', '保存图片失败');
			return false;
		}else{
			$this->savedFile = $filePath;
			if($this->toWidth && $this->toHeight){
				return static::cutThumbnail($resourcePath . '/' . $filePath, '', $this->toWidth, $this->toHeight);
			}
			return true;
		}
	}

	/**
	 * 构建上传后的文件名
	 * @param \yii\web\UploadFile $oImage
	 * @return string
	 */
	private function _buildFileName($oImage){
		$ext = $oImage->getExtension();
		if($ext == ''){
			$ext = preg_replace('/\w+\//', '', $oImage->type); 
		}
		$this->tfn = md5(microtime());
		return $this->tfn . '.' . $ext;
	}
	
	/**
     * 生成保持原图纵横比的缩略图，支持.png .jpg .gif
     * 缩略图类型统一为.png格式
     * $srcFile     原图像文件名称
     * $toFile      缩略图文件名称，为空覆盖原图像文件
     * $toW         缩略图宽
     * $toH         缩略图高
     * @return bool
     */
    public static function createThumbnail($srcFile, $toFile = "", $toW = 100, $toH = 100)
    {
        if ($toFile == "") $toFile = $srcFile;

        $data = getimagesize($srcFile);//返回含有4个单元的数组，0-宽，1-高，2-图像类型，3-宽高的文本描述。
        if (!$data) return false;
        //将文件载入到资源变量im中
        switch ($data[2]) //1-GIF，2-JPG，3-PNG
        {
            case 1:
                if(!function_exists("imagecreatefromgif")) return false;
                $im = imagecreatefromgif($srcFile);
                break;
            case 2:
                if(!function_exists("imagecreatefromjpeg")) return false;
                $im = imagecreatefromjpeg($srcFile);
                break;
            case 3:
                if(!function_exists("imagecreatefrompng")) return false;
                $im = imagecreatefrompng($srcFile);
                break;
        }
        //计算缩略图的宽高
        $srcW = imagesx($im);
        $srcH = imagesy($im);
        $toWH = $toW / $toH;
        $srcWH = $srcW / $srcH;
        if ($toWH <= $srcWH) {
            $ftoW = $toW;
            $ftoH = (int)($ftoW * ($srcH / $srcW));
        } else {
            $ftoH = $toH;
            $ftoW = (int)($ftoH * ($srcW / $srcH));
        }

        if (function_exists("imagecreatetruecolor")) {
            $ni = imagecreatetruecolor($ftoW, $ftoH); //新建一个真彩色图像
            if ($ni) {
                //重采样拷贝部分图像并调整大小 可保持较好的清晰度
                imagecopyresampled($ni, $im, 0, 0, 0, 0, $ftoW, $ftoH, $srcW, $srcH);
            } else {
                //拷贝部分图像并调整大小
                $ni = imagecreate($ftoW, $ftoH);
                imagecopyresized($ni, $im, 0, 0, 0, 0, $ftoW, $ftoH, $srcW, $srcH);
            }
        } else {
            $ni = imagecreate($ftoW, $ftoH);
            imagecopyresized($ni, $im, 0, 0, 0, 0, $ftoW, $ftoH, $srcW, $srcH);
        }

        switch ($data[2]) //1-GIF，2-JPG，3-PNG
        {
            case 1:
                imagegif($ni, $toFile);
                break;
            case 2:
                imagejpeg($ni, $toFile);
                break;
            case 3:
                imagepng($ni, $toFile);
                break;
        }
        ImageDestroy($ni);
        ImageDestroy($im);
        return $toFile;
    }
	
	/**
     * 从原图中心剪切图片，支持.png .jpg .gif
     * 图片类型统一为.png格式
     * $srcFile     原图像文件名称
     * $toFile      生成图文件名称，为空覆盖原图像文件
     * $toW         剪切图宽
     * $toH         剪切图高
     * @return bool
     */
    public static function cutThumbnail($srcFile, $toFile = "", $toW = 100, $toH = 100){
        if($toFile == ''){
			$toFile = $srcFile;
		}
        $data = getimagesize($srcFile);	//返回含有4个单元的数组，0-宽，1-高，2-图像类型，3-宽高的文本描述。
        if(!$data){
			return false;
		}
		$isPng = false;
        //将文件载入到资源变量im中
        switch($data[2]){
            case 1:
                if(!function_exists("imagecreatefromgif")){
					return false;
				}
                $im = imagecreatefromgif($srcFile);
                break;
            case 2:
                if(!function_exists("imagecreatefromjpeg")){
					return false;
				}
                $im = imagecreatefromjpeg($srcFile);
                break;
            case 3:
                if(!function_exists("imagecreatefrompng")){
					return false;
				}
                $im = imagecreatefrompng($srcFile);
				imagesavealpha($im,true);
				$isPng = true;
                break;
        }

        if(function_exists("imagecreatetruecolor")){
            $ni = imagecreatetruecolor($toW, $toH); //新建一个真彩色图像
            if($isPng){
				imagealphablending($ni,false);
				imagesavealpha($ni,true);
			}
            if($ni){
                //剪切部分图像
				imagecopy($ni, $im, 0, 0, ($data[0] - $toW) / 2, ($data[1] - $toH) / 2, $toW, $toH);
            }else{
                return false;
			}
			switch($data[2]){
				case 1:
					imagegif($ni, $toFile);
					break;
				case 2:
					imagejpeg($ni, $toFile);
					break;
				case 3:
					imagepng($ni, $toFile);
					break;
			}
			ImageDestroy($ni);
			ImageDestroy($im);
        }else{
            return false;
        }
        return $toFile;
    }
}