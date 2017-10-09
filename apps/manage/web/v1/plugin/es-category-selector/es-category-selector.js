/**
 * 题目分类选择插件
 * @author 黄文非
 */
(function(container, $){
/*
 * @param {json} aOptions 对象配置，格式:
 * {
 *		source : '返回分类列表的URL' ,
 *		defaultId  : 66	//弹出时默认选中的分类ID ,
 *		isShowAddButton : false	//是否显示添加按钮
 *	}
 */
container.EsCategorySelector = function(aOptions) {
	var self = this;
	var _aCategoryList = [];//目录树列表
	var _oDirTree = null;
	var _aOptions = _parseOption(aOptions);//EsCategorySelector 配置
	var _$dialog = null;//选择界面，是一个jquery对象
	self.selectedId = 0;//当前选中的分类ID
	self.selectedName = '';//当前选中的分类名称

	var _globalVarName = '__umfun_oDirTree' + (++_instanceCount);	//需要一个全局变量名称

	$.extend(self, new Component());

	/**
	 * 弹出一个窗口提供选择
	 */
	self.show = function(){

		if(_$dialog === null){
			//构建主界面
			_$dialog = $(_buildDialogHtml());
			_oDirTree = new dTree(_globalVarName);
			window[_globalVarName] = _oDirTree;
			_oDirTree.add(0, -1, '目录选择器');
			//拼接tree数据
			ajax({
				url : _aOptions.source,
				success : function(aResult){
					if(aResult.status === 1){
						_aCategoryList = aResult.data;
						_buildTreeDir();
						_selectDefaultNode();
					}else {
						$.error(aResult.msg);
					}
				}
			});

			_$dialog.on('click', '.dTreeNode', function(){
				var selectedId = _oDirTree.getSelected();
				if(!selectedId){
					return;
				}

				self.selectedId = selectedId;
				var oNode = _oDirTree.aNodes[_oDirTree.selectedNode];
				if(!oNode){
					return;
				}
				self.selectedName = oNode.name;
				self.triggerEvent(container.EsCategorySelector.EVENT_ON_SELECT_CATEGORY);
			});

			_$dialog.on('click', '.J-btnConfirm', function(){
				var oEvent = new UmFunEvent();
				oEvent.cancel = false;
				self.triggerEvent(container.EsCategorySelector.EVENT_ON_CONFIRM, oEvent);
				if(oEvent.cancel){
					return;
				}

				_$dialog.modal('hide');
			});

			_$dialog.on('click', '.J-btnAdd', function(){
				self.triggerEvent(container.EsCategorySelector.EVENT_ON_ADD);
			});

			_$dialog.on('hidden.bs.modal', function(){
				self.triggerEvent(container.EsCategorySelector.EVENT_ON_CLOSE);
			});
		}

		//显示选择窗口
		$('body').append(_$dialog);
		_$dialog.modal('show');
	};

	/**
	 * 关闭弹出窗口
	 */
	self.close = function(){
		_$dialog.detach();
	};

	/*
	 * 获取当前选中的分类路径,比如叫"小学一年级上册 - 第一单元 - 静夜思"
	 */
	self.getSelectedFullPath = function(delimiter){
		if(delimiter == undefined){
			delimiter = ' - ';
		}
		var aCategoryNames = [];
		var dirTreeNodeId = _oDirTree.selectedNode,
			oNode = _oDirTree.aNodes[dirTreeNodeId],
			$nodeToken = $('#s' + _globalVarName + dirTreeNodeId),
			$nodeItem = $nodeToken.closest('.dTreeNode');
		aCategoryNames.push(oNode.name);

		var $treeNode = $nodeItem.closest('.dTreeNode');
		var $parentWrap = $treeNode.closest('.clip');
		var $node = null;

		while($parentWrap.length){
			if($parentWrap.length){
				$treeNode = $parentWrap.prev('.dTreeNode');
				if($treeNode.length){
					$node = $treeNode.find('.node');
					if($node.length){
						aCategoryNames.unshift($node.attr('title'));
					}
				}
			}else{
				break;
			}

			$parentWrap = $treeNode.closest('.clip');
		};

		return aCategoryNames.join(delimiter);
	};

	/*
	 * 判断当前选中的分类是否存在子目录
	 * @return {boolean} 有子分类返回true，否则返回false
	 */
	self.selectedCategoryHasChild = function(){
		if(!self.selectedId){
			$.error('未选中任何分类');
		}

		return _oDirTree.hasChild(self.selectedId);
	};

	/**
	 * 配置解析
	 * @param {json} aOption ，格式为 {source : '返回分类列表的URL', defaultId : 弹出时默认选中的分类ID}
	 */
	function _parseOption(aOption){
		if(aOption.source === null){
			$.error('配置缺少source,输出目录列表的地址');
		}
		if(aOption.defaultId === null){
			aOption.defaultId = 0;
		}
		return aOption;
	}


	/*
	 * 拼接弹出主界面
	 */
	function _buildDialogHtml(){
		var btnHtmls = '';
		if(_aOptions.isShowAddButton){
			btnHtmls = '<button type="button" class="btn btn-primary J-btnAdd">添加</button>\
						<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>';
		}else{
			btnHtmls = '<button type="button" class="btn btn-primary J-btnConfirm">确定</button>';
		}

		return '<div class="modal fade J-category" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">\
			<div class="modal-dialog">\
				<div class="modal-content">\
					<div class="modal-header">\
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">\
							<span aria-hidden="true">&times;</span>\
						</button>\
						<h4 class="modal-title">选择目录</h4>\
					</div>\
					<div class="modal-body ztree J-dirTree" style="max-height:600px; overflow-y:scroll;"></div>\
					<div class="modal-footer">' + btnHtmls + '</div>\
				</div>\
			</div>\
		</div>';
	}

	/*
	 * 拼接目录树
	 */
	function _buildTreeDir(){
		$(_aCategoryList).each(function(){
			_oDirTree.add(this.id, this.parent_id, this.name, 'javascript:;', this.name);
		});
		_$dialog.find('.J-dirTree').html(_oDirTree.toString());
	}

	/*
	 * 根据配置的 defaultId ，选择对应的分类
	 */
	function _selectDefaultNode(){
		if(!_aOptions.defaultId){
			return;
		}

		try{
			_oDirTree.openTo(_aOptions.defaultId, true);
		}catch(e){
			$.error('无效的默认分类ID:' + _aOptions.defaultId + ',请确认是这个科目的哦');
		}
	}
};

/*
 * 点击目录分类时候触发
 */
container.EsCategorySelector.EVENT_ON_SELECT_CATEGORY = 'on_select_category';
/**
 * 点击添加的时候
 */
container.EsCategorySelector.EVENT_ON_ADD = 'on_add';
/**
 * 点击确定的时候
 */
container.EsCategorySelector.EVENT_ON_CONFIRM = 'on_confirm';
/**
 * 插件对话框消失的时候
 */
container.EsCategorySelector.EVENT_ON_CLOSE = 'on_close';

var _instanceCount = 0;	//插件实例化的个数
})(window, jQuery);

