<?php
use umeworld\lib\Url;

$protocol =  (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';


$this->setTitle('优满堂-联系方式'); 
?>
<div id="wrapPage" class="umt-about-page">
	<div class="item">
		<div class="title">联系我们</div>
        <div class="content map">
             <div id="map" class="map-pic"></div>
             <div class="map-msg"><span>公司电话：</span>020-89237947</div>
             <div class="map-msg"><span>公司地址：</span>广州市海珠区新港东路2433号启盛园区906-907</div>
             <div class="map-msg"><span>商户入驻：</span>陈经理，18666023764，elman@umfun.com</div>
             <div class="map-msg"><span>产品/课程/其他合作：</span><a>cooperate@umfun.com</a></div>
        </div>
    </div>
</div>
<script>
    //地图设置
    function initMap(){
		var center = new qq.maps.LatLng(23.095918,113.399870);
		var map = new qq.maps.Map(
		    document.getElementById("map"),
		    {
		        center: center,
		        zoom: 60
		    }
		);
		var marker = new qq.maps.Marker({
		    position: center,
		    map: map
		});
    }

	function loadScript() {
		try{
			var script = document.createElement("script");
			script.type = "text/javascript";
			script.src = "<?php echo $protocol;?>map.qq.com/api/js?v=2.exp&callback=initMap";
			document.body.appendChild(script);
		}catch(e){
			
		}
	}

	loadScript();
</script>
