(function(win){
win.UBox = {
	skipNextMsgDialog : false,

	isComputer: !new RegExp('iPhone|Android').test(navigator.userAgent),
	/**
	 * 显示消息盒子
	 */
	show : function(message, status, finishCallBack, closeTimeOut, onDestoryCallBack){
		if(self.skipNextMsgDialog){
			self.skipNextMsgDialog = false;
			return;
		}

		if(!this.isComputer){
			//移动端
			alert(message);

			if(typeof(finishCallBack) == 'string' && finishCallBack.length){
				location.href = finishCallBack;
			}else if(typeof(finishCallBack) == 'function'){
				finishCallBack();
			}
			return;
		}

		if($.inArray(status, [-1, 1, 2]) == -1){
			status = 0;
		}

		if(!closeTimeOut){
			closeTimeOut = 2500;
		}else{
			closeTimeOut = Math.abs(closeTimeOut) * 1000;
		}

		self.$oLastBox && self.$oLastBox.remove();	//删除上一个消息盒子

		//判断采用哪算提示样式
		var statuClass = ['Notice', 'Error', 'Success'][status + 1];
		var $container = $('body > .container');
		var $oBox = $('<div class="wrapUBox">\n\
			<div class="uBoxshadow">\n\
				<div id="wrapUBoxLeft" class="wrapUBoxLeft uBox' + statuClass + 'Icon"></div>\n\
				<div id="wrapUBoxMiddle" class="wrapUBoxMiddle uBox' + statuClass + 'Message">' + message + '</div>\n\
				<div id="wrapUBoxRightSpace" class="wrapUBoxRightSpace uBox' + statuClass + 'RightSpace"></div>\n\
			</div>\n\
		</div>');
		if($container[0]){
			$oBox.appendTo($container)
		}else{
			$oBox.appendTo('body');
		}
		$oBox.css('left', ($(win).width() / 2 - $oBox.width() / 2) + 'px');
		
		//注册单击消失
		$oBox.click(function(){
			$(this).remove();
			if(typeof(onDestoryCallBack) == 'function'){
				onDestoryCallBack();
			}else if(typeof(finishCallBack) == 'string' && finishCallBack.length){
				if(finishCallBack == 'reload'){
					location.reload();
					return;
				}

				var regUrl = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
				var regUrl2 = /[0-9a-zA-Z\/?]*[-A-Z0-9+&@#\/%=~_|]/ig;
				if(regUrl.test(finishCallBack) || regUrl2.test(finishCallBack)){
					location.href = finishCallBack;
				}
			}else if(typeof(finishCallBack) == 'function'){
				finishCallBack();
			}

			self.$oLastBox = null;
		});

		self.$oLastBox = $oBox;

		//注册自动消失
		setTimeout(function(){
			$oBox.trigger('click');
		}, closeTimeOut);

		return $oBox;
	},

	/**
	 * 弹出确认消息框
	 * @param {type} message 确认消息
	 * @param {type} yesCallBack 确定按钮回调
	 * @param {type} noCallBack 否定按钮架设
	 * @param {type} aMoreOption 更多设置项
	 * @returns 确认框的jQuery的fn对象
	 */
	confirm : function(message, yesCallBack, noCallBack, aMoreOption){
		if(!this.isComputer){
			//移动端
			if(confirm(message)){
				yesCallBack && yesCallBack();
			}else{
				noCallBack && noCallBack();
			}
			return;
		}

		if(!yesCallBack){
			yesCallBack = $.noop;
		}

		if(!noCallBack){
			noCallBack = $.noop;
		}

		self.$oLastBox && self.$oLastBox.remove();	//删除上一个消息盒子

		var aOption = $.extend({
			hideYesBtn : false
			,hideNoBtn : false
			,btnYesText : '确定'
			,btnNoText : '取消'
		}, aMoreOption);

		var buttonsHtml = '';
		if(!aOption.hideYesBtn){
			buttonsHtml += '<button xid="uBoxButtonYes">' + aOption.btnYesText + '</button>';
		}
		if(!aOption.hideNoBtn){
			buttonsHtml += '<button xid="uBoxButtonNo">' + aOption.btnNoText + '</button>';
		}

		var $container = $('body > .container');
		var $oBox = $('<div class="wrapMask">\n\
			<div xid="uBoxWrapConfirm" class="uBoxWrapConfirm">\
				<div class="uBoxTitle">操作确认<a xid="uBoxClose" class="uBoxClose"></a></div>\
				<div class="wrapTip"><div class="confirmIcon"></div><div class="tip">' + message + '</div></div>\
				<div class="wrapControl">' + buttonsHtml + '</div>\
			</div>\
			<div xid="uBoxMask" class="uBoxMask"></div>\
		</div>');
		if($container[0]){
			$oBox.appendTo($container)
		}else{
			$oBox.appendTo('body');
		}

		$oBox.find('div[xid="uBoxMask"]').height($('body').height()).css({position: 'fixed', top: '0px'});
		var oConfirmBox = $oBox.find('div[xid="uBoxWrapConfirm"]');
		oConfirmBox.css({
			top : ($(win).height() / 2 - oConfirmBox.height() / 2) + 'px',
			left : ($(win).width() / 2 - oConfirmBox.width() / 2) + 'px'
		});

		$oBox.find('button[xid="uBoxButtonYes"]').click(function(){
			if(yesCallBack() != false){
				$oBox.remove();
			}
		});
		$oBox.find('button[xid="uBoxButtonYes"]').focus();
		$oBox.find('button[xid="uBoxButtonNo"],a[xid="uBoxClose"]').click(function(){
			var isClose = $(this).attr('xid') == 'uBoxClose';
			if(typeof(noCallBack) == 'function' && !isClose){
				noCallBack();
			}
			$oBox.remove();
		});

		return $oBox;
	},

	/**/
	alert:function(data){
		var message = typeof(data) == 'string' ? data : data.message,
		 status = data.status, 
		 closeTimeOut = data.closeTimeOut, 
		 onDestoryCallBack = data.onDestroyCallBack;
		if(!closeTimeOut){
			closeTimeOut = 2500;
		}else{
			closeTimeOut = Math.abs(closeTimeOut) * 1000;
		}
		self.$oLastBox && self.$oLastBox.remove();	//删除上一个消息盒子

		var statusHtml = '<div class="status">!</div>';
		var $container = $('body > .container');
		var $oBox = $('<div class="uBoxAlert">\n\
			<div class="uBoxAlertContent">\
				<div class="uBoxAlertshadow">\n\
					'+statusHtml+'\
					<div class="uBoxAlertMsg">'+message+'</div>\
				</div>\n\
			</div>\
		</div>');
		if($container[0]){
			$oBox.appendTo($container)
		}else{
			$oBox.appendTo('body');
		}
		//单击消失
		$oBox.click(function(){
			$(this).remove();
			if(typeof(onDestoryCallBack) == 'function'){
				onDestoryCallBack();
			}
			self.$oLastBox = null;
		});

		self.$oLastBox = $oBox;

		//注册自动消失
		setTimeout(function(){
			$oBox.trigger('click');
		}, closeTimeOut);

		return $oBox;

	},
	/**
	 * 控制台输出
	 */
	debug : function(){
		if(window && window.console && window.console.debug){
			window.console.debug.apply(window.console, arguments);
		}
	}

};

var self = win.UBox;
})(window);