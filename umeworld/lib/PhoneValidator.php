<?php
namespace umeworld\lib;

use Yii;
use yii\base\InvalidConfigException;
use yii\web\JsExpression;
use yii\helpers\Json;

/**
 * EmailValidator validates that the attribute value is a valid email address.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class PhoneValidator extends \yii\validators\Validator
{
    /**
     * @var boolean whether to check whether the email's domain exists and has either an A or MX record.
     * Be aware that this check can fail due to temporary DNS problems even if the email address is
     * valid and an email would be deliverable. Defaults to false.
     */
    public $checkDNS = false;
    /**
     * @var boolean whether validation process should take into account IDN (internationalized domain
     * names). Defaults to false meaning that validation of emails containing IDN will always fail.
     * Note that in order to use IDN validation you have to install and enable `intl` PHP extension,
     * otherwise an exception would be thrown.
     */
    public $enableIDN = false;


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->enableIDN && !function_exists('idn_to_ascii')) {
            throw new InvalidConfigException('In order to use IDN validation intl extension must be installed and enabled.');
        }
        if ($this->message === null) {
            $this->message = Yii::t('yii', '{attribute} is not a valid phone.');
        }
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
		$valid =  true;
		if(!preg_match('/^((\+86)|(86))?(1[3|4|5|7|8]{1}\d{9})$/', $value)) {
			$valid =  false;
		}

        return $valid ? null : [$this->message, []];
    }

}
