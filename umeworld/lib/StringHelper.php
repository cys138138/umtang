<?php
namespace umeworld\lib;
use Yii;

class StringHelper extends \yii\helpers\BaseStringHelper{
    const MODE_NUMBER  = 4;
    const MODE_LETTER = 16;
    const MODE_POINT = 32;
    const MODE_CHINESE = 64;

    /**
     * 生成随机字符串
     * @param  int $min  最小长度
     * @param  int $max  最大长度
     * @param  int $mode  要生成的字符串类型,4表示纯数字，16表示纯字母,32表示纯英文字符,64表示混合组合
     * @return string 字符类型
     * @author zhangliping
     * @test \tests\codeception\common\unit\framework\StringHelperTest::testBuildRandomString
     */
	public static function buildRandomString($min, $max, $mode){
		if($min <= 0 || $max <= 0){
			throw Yii::$app->buildError('输入的长度有错误');
		}
		if($max < $min){
			$max = $min;
		}
		if($mode == self::MODE_NUMBER || $mode == self::MODE_LETTER || $mode == self::MODE_POINT){
			$result = StringHelper::getRandNumber($min, $max, $mode);
		}else if($mode == self::MODE_CHINESE){
			$result = StringHelper::getRandChinese($min, $max);
		}else if($mode == self::MODE_NUMBER + self::MODE_LETTER || $mode == self::MODE_NUMBER+self::MODE_CHINESE || $mode == self::MODE_NUMBER+self::MODE_POINT || $mode == self::MODE_LETTER + self::MODE_POINT || $mode == self::		MODE_LETTER + self::MODE_CHINESE || $mode == self::MODE_POINT + self::MODE_CHINESE){
			$result = StringHelper::getRandMix($min, $max, $mode);
		}else{
			throw Yii::$app->buildError('类型参数不正确');
		}
		return $result;
	}
	/**
	 * 截取字符串函数
	 * @param  type $string 要截取的字符串
	 * @param  int $min    最少截取数
	 * @param  int $max    最大截取数
	 * @param  int $offset 开始截取的位置
	 * @return string       字符类型
	 * @author zhangliping
	 * @test \tests\codeception\common\unit\framework\StringHelperTest::testDeleteString
	*/
	public static function deleteString($string, $min = 0, $max = 0, $offset = 0){
		$detalString = $string;
		$offset = $offset;
		$resultStr = "";
		$len = mb_strlen($detalString, 'utf8');
		if($len < $min && $len < $max){
			throw Yii::$app->buildError('字符串长度不够');
		}
		if($max <= $min){
			$max = $min;
		}
		if($min == 0 && $max == 0){
			$size = intval(floor($len/2))+1;
		}else{
			$size = rand($min, $max);
		}if($offset>=1){
			$resultStrPriv = mb_substr($detalString, 0, $offset, 'utf-8');
		}else{
       	   $resultStrPriv = "";
		}
		$countAfter = $len - ($offset + $size);
		$startAfter = $offset + $size;
		if($countAfter >= 1){
		   $resultStrAfter = mb_substr($detalString, $startAfter, $countAfter, 'utf-8');
		}else{
		   $resultStrAfter = "";
		}
		$resultStr = $resultStrPriv.$resultStrAfter;
	    return  $resultStr;
	}

	/**
	 * 生成随机的数字,字母,或者英文符号
     * @param  int $min  最小长度
     * @param  int $max  最大长度
     * @param  int $mode  要生成的字符串类型,4表示纯数字，16表示纯字母,32表示纯英文字符,64表示混合组合
     * @return string 字符类型
     * @author zhangliping
     */
    private static function  getRandNumber($min, $max, $mode) {
		if($mode == self::MODE_NUMBER){
          $str = "0123456789";
     	}if($mode == self::MODE_LETTER){
      	   $str = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
     	}if($mode == self::MODE_POINT){
           $str = "+=-*&^%$#@!()|\.,;:";
      	}
        $randString = '';
        $len = strlen($str)-1;
        $sum = rand($min, $max);
        for($i = 0; $i < $sum; $i ++){
            $num = mt_rand(0, $len);
            $randString .= $str[$num];
		}
        return $randString ;
    }
    /**
     * 生成混合的随机数,支持中文+英文,数字+英文,等组合
     * @param  int $min  最小长度
     * @param  int $max  最大长度
     * @return string 字符类型
     * @author zhangliping
     */
    protected static  function  getRandMix($min, $max, $mode){
		$strChinese = StringHelper::getRandChinese(10, 15);
		$strNumberLetter = "0a1b2cd3e4f5ghi6jk7lmn8op9qrs10t1u2vwx3yzABCD4EF5GHI6JK7LM8OPQRS9TUVWXYZ";
		$strNumberPoint = "+=-*&^%$1234567890#@!()|\.,;:";
		$strLetterPoint = "+=-*&^%$#@!()|\.,;:abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ+=-*&^%$#@!()|\.,;:";
		$strNumberChinese = "1234567890".$strChinese;
		$strLetterChinese = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ".$strChinese;
		$strPointChinese = "#@!()|\.,;:".$strChinese;
		$sum = rand($min, $max);
		$randString = '';
        $str = "";
     	if($mode == self::MODE_NUMBER + self::MODE_LETTER){
			$str = $strNumberLetter;
		}if($mode == self::MODE_NUMBER + self::MODE_POINT){
			$str = $strNumberPoint;
		}if($mode == self::MODE_NUMBER + self::MODE_CHINESE){
            $str = $strNumberChinese;
		}if($mode == self::MODE_LETTER + self::MODE_POINT){
			$str = $strLetterPoint;
		}if($mode == self::MODE_LETTER + self::MODE_CHINESE){
            $str = $strLetterChinese;
		}if($mode == self::MODE_POINT + self::MODE_CHINESE){
            $str = $strPointChinese;
		}
		for($i = 0; $i < $sum; $i++){
			$Xi = mt_rand(0,mb_strlen($str, 'utf8'));
			$randString .= mb_substr($str, $Xi-1, 1, 'UTF-8');
		}
         return $randString;
    }

    /**
      * 生成随机中文字符
      * @param  int $min  最小长度
      * @param  int $max  最大长度
      * @return string 字符类型
      * @author zhangliping
      */
	private static function getRandChinese($min, $max){
		$code = "";
		$str = "的一是在了不和有大这主中人上为们地个用工时要动国产以我到他会作来分生对于学下级就年阶义发成部民可出能方进同行面说种过命度革而多子后自社加小机也经力线本电高量长党得实家定深法表着水理化争现所二起政三好十战无农使性前等反体合斗路图把结第里正新开论之物从当两些还天资事队批如应形想制心样干都向变关点育重其思与间内去因件日利相由压员气业代全组数果期导平各基或月毛然问比展那它最及外没看治提五解系林者米群头意只明四道马认次文通但条较克又公孔领军流入接席位情运器并飞原油放立题质指建区验活众很教决特此常石强极土少已根共直团统式转别造切九你取西持总料连任志观调七么山程百报更见必真保热委手改管处己将修支识病象几先老光专什六型具示复安带每东增则完风回南广劳轮科北打积车计给节做务被整联步类集号列温装即毫知轴研单色坚据速防史拉世设达尔场织历花受求传口断况采精金界品判参层止边清至万确究书术状厂须离再目海交权且儿青才证低越际八试规斯近注办布门铁需走议县兵固除般引齿千胜细影济白格效置推空配刀叶率述今选养德话查差半敌始片施响收华觉备名红续均药标记难存测士身紧液派准斤角降维板许破述技消底床田势端感往神便贺村构照容非搞亚磨族火段算适讲按值美态黄易彪服早班麦削信排台声该击素张密害侯草何树肥继右属市严径螺检左页抗苏显苦英快称坏移约巴材省黑武培著河帝仅针怎植京助升王眼她抓含苗副杂普谈围食射源例致酸旧却充足短划剂宣环落首尺波承粉践府鱼随考刻靠够满夫失包住促枝局菌杆周护岩师举曲春元超负砂封换太模贫减阳扬江析亩木言球朝医校古呢稻宋听唯输滑站另卫字鼓刚写刘微略范供阿块某功套友限项余倒卷创律雨让骨远帮初皮播优占死毒圈伟季训控激找叫云互跟裂粮粒母练塞钢顶策双留误础吸阻故寸盾晚丝女散焊功株亲院冷彻弹错散商视艺灭版烈零室轻血倍缺厘泵察绝富城冲喷壤简否柱李望盘磁雄似困巩益洲脱投送奴侧润盖挥距触星松送获兴独官混纪依未突架宽冬章湿偏纹吃执阀矿寨责熟稳夺硬价努翻奇甲预职评读背协损棉侵灰虽矛厚罗泥辟告卵箱掌氧恩爱停曾溶营终纲孟钱待尽俄缩沙退陈讨奋械载胞幼哪剥迫旋征槽倒握担仍呀鲜吧卡粗介钻逐弱脚怕盐末阴丰编印蜂急拿扩伤飞露核缘游振操央伍域甚迅辉异序免纸夜乡久隶缸夹念兰映沟乙吗儒杀汽磷艰晶插埃燃欢铁补咱芽永瓦倾阵碳演威附牙芽永瓦斜灌欧献顺猪洋腐请透司危括脉宜笑若尾束壮暴企菜穗楚汉愈绿拖牛份染既秋遍锻玉夏疗尖殖井费州访吹荣铜沿替滚客召旱悟刺脑措贯藏敢令隙炉壳硫煤迎铸粘探临薄旬善福纵择礼愿伏残雷延烟句纯渐耕跑泽慢栽鲁赤繁境潮横掉锥希池败船假亮谓托伙哲怀割摆贡呈劲财仪沉炼麻罪祖息车穿货销齐鼠抽画饲龙库守筑房歌寒喜哥洗蚀废纳腹乎录镜妇恶脂庄擦险赞钟摇典柄辩竹谷卖乱虚桥奥伯赶垂途额壁网截野遗静谋弄挂课镇妄盛耐援扎虑键归符庆聚绕摩忙舞遇索顾胶羊湖钉仁音迹碎伸灯避泛亡答勇频皇柳哈揭甘诺概宪浓岛袭谁洪谢炮浇斑讯懂灵蛋闭孩释乳巨徒私银伊景坦累匀霉杜乐勒隔弯绩招绍胡呼痛峰零柴簧午跳居尚丁秦稍追梁折耗碱殊岗挖氏刃剧堆赫荷胸衡勤膜篇登驻案刊秧缓凸役剪川雪链渔啦脸户洛孢勃盟买杨宗焦赛旗滤硅炭股坐蒸凝竟陷枪黎救冒暗洞犯筒您宋弧爆谬涂味津臂障褐陆啊健尊豆拔莫抵桑坡缝警挑污冰柬嘴啥饭塑寄赵喊垫康遵牧遭幅园腔订香肉弟屋敏恢忘衣孙龄岭骗休借丹渡耳刨虎笔稀昆浪萨茶滴浅拥穴覆伦娘吨浸袖珠雌妈紫戏塔锤震岁貌洁剖牢锋疑霸闪埔猛诉刷狠忽灾闹乔唐漏闻沈熔氯荒茎男凡抢像浆旁玻亦忠唱蒙予纷捕锁尤乘乌智淡允叛畜俘摸锈扫毕璃宝芯爷鉴秘净蒋钙肩腾枯抛轨堂拌爸循诱祝励肯酒绳穷塘燥泡袋朗喂铝软渠颗惯贸粪综墙趋彼届墨碍启逆卸航雾冠丙街莱贝辐肠付吉渗瑞惊顿挤秒悬姆烂森糖圣凹陶词迟蚕亿矩";
		$sum = rand($min, $max);
		for($i=0; $i < $sum; $i++){
			$Xi = mt_rand(0,mb_strlen($str, 'utf8'));
            $code .= mb_substr($str, $Xi-1, 1, 'UTF-8');
		}
		return  $code;
	}

    /**
     * 判断传入的字符串是否包含emoji 表情符号，及其他非字符型的图片、表情数据 （字符型也就是 只接受 交流语言文字，文字符号，数字，字母）
     * @param  string $string  要判断的字符串数据
     * @return bool 有则返回true,无返回false
     * @author 钟长青
     */
    public static function hasEmojiCode($string){
        $utfToGbkString = mb_convert_encoding($string, 'GBK', 'utf-8');
        $gbkToUtfString = mb_convert_encoding($utfToGbkString, 'utf-8', 'GBK');
        if($string != $gbkToUtfString){
            return true;
        }else {
            return false;
        }
    }
	
	/**
	 * 获取字符串的长度
	 *
	 * 计算时, 汉字或全角字符占1个长度, 英文字符占0.5个长度
	 *
	 * @param string  $str
	 * @param boolean $filter 是否过滤html标签
	 * @return int 字符串的长度
	 */
	public static function getStringLength($str, $filter = false){
		if($filter) {
			$str = html_entity_decode($str, ENT_QUOTES, 'UTF-8');
			$str = strip_tags($str);
		}
		return (strlen($str) + mb_strlen($str, 'UTF8')) / 4;
	}
 }

