window.myClassList = [];
window.schoolId = 0;
function lookClass(oDom,id) {
	window.schoolId = id;
	ajax({
		url: '/grade/show-class-list',
		data: {id: id},
		success: function (aResult) {
			if (aResult.status == 1) {
				myClassList = aResult.data.aClassList;

				var schoolName = $(oDom).parent().siblings().eq(0).text();
				var renNum = $(oDom).parent().siblings().eq(3).text();
				var snv = schoolName + '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;人数：' + renNum;
				$(".modal-title").html(snv);
				//  列出年级选择框的值
				var tarl = aResult.data.aClassList.length;
				var grade = [];
				for (var i = 0; i < tarl; i++) {
					grade[i] = aResult.data.aClassList[i].grade;
				}
				// 去除重复的年级值
				function unique(arr) {
					var result = [], hash = {};
					for (var i = 0, elem; (elem = arr[i]) != null; i++) {
						if (!hash[elem]) {
							result.push(elem);
							hash[elem] = true;
						}
					}
					return result;
				}

				grade = unique(grade);

				var rel = grade.length;
				for (var i = 0; i < rel; i++) {
					$("#gradeSelect").append('<option value="' + grade[i] + '" onclick ="gradeList()" >' + grade[i] + '</option>');
				}

				// 让数据列表表单清空上次记录
				$("#classList").html('');

				// 先给初始化数据，给显示最小年级数据列表
				var startArr = [];
				for (var i = 0; i < tarl; i++) {
					if (aResult.data.aClassList[i].grade === grade[0]) {
						startArr.push(aResult.data.aClassList[i]);
					}
				}

				var stle = startArr.length;
				for (var i = 0; i < stle; i++) {
					$("#classList").append('<div class="col-md-10"><div class="col-md-2">' + startArr[i].grade + '</div><div class="col-md-4">' + startArr[i].class + '</div><div class="col-md-2">' + startArr[i].nums + '</div><div class="col-md-4" ><a href="javascript:void(0)" onclick="editGrade(this, '+startArr[i].grade+','+"'"+ startArr[i].class+"'"+')">编辑</a> </div> </div>');
				}

			} else {
				UBox.show(aResult.msg, aResult.status);
			}
		}
	});

}

function gradeList() {
	$("#classList").html('');
	//  根据筛选后的年级条件显示数据列表

	var gradeId = $("#gradeSelect").val();
	var startArr = [];
	var tarl = myClassList.length;
	for (var i = 0; i < tarl; i++) {
		if (myClassList[i].grade === gradeId) {
			startArr.push(myClassList[i]);
		}
	}

	var stle = startArr.length;
	for (var i = 0; i < stle; i++) {
		$("#classList").append('<div class="col-md-10"><div class="col-md-2">' + startArr[i].grade + '</div><div class="col-md-4">' + startArr[i].class + '</div><div class="col-md-2">' + startArr[i].nums + '</div></div>');
	}

}

function editGrade(oDom, grade, gradeName){
	var thisGrade = $(oDom).parent().siblings().eq(1);
	var gradeName = thisGrade.text();

	var newGrade = prompt("请修改班级名", gradeName);
	if(newGrade != null){
		ajax({
			url: '/grade/edit-class',
			data: {
				schoolId : window.schoolId,
				grade : grade,
				oldClass : gradeName,
				newClass: newGrade
			},
			success: function (aResult) {
				if (aResult.status == 1) {
					UBox.show(aResult.msg, aResult.status);
					thisGrade.text(newGrade);
				}else{
					UBox.show(aResult.msg, aResult.status);
				}
			}

		});
	}

}