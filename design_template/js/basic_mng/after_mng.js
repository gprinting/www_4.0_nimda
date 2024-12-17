/*
 *
 * Copyright (c) 2015 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *============================================================================
 * 2015/12/10 왕초롱 생성
 * 2016/05/18 임종건 버그 수정 및 추가 개발
 * 2016/11/04 엄준현 수정(삭제부분 로직 수정)
 *============================================================================
 *
 */

var page = "1"; //페이지
var list_num = "30"; //리스트 갯수
var tab_id = "prdt"; //탭 id
var after_seqno = "";
var after_data = {
	"list_num"     : "",
	"page"         : "1",
	"name"         : "",
	"manu_seqno"   : "",
	"brand_seqno"  : "",
	"depth1"       : "",
	"depth2"       : "",
	"depth3"       : "",
	"crtr_unit"    : ""
}

$(document).ready(function() {
    selectSearch(1,1);
});

//후공정 depth 이름 가져오기
var loadDepthName = function(val, depth) {

    var url = "/ajax/basic_mng/common_mng/load_after_depth_name.php";
    var data = { 
        "name" : val,
        "depth": depth
    };

    var callback = function(result) {
        if (depth == "depth1") {
            $("#after_depth1").html(result);
	        $("#after_depth2").html("<option value=''>depth2</option>");
	        $("#after_depth3").html("<option value=''>depth3</option>");
        } else if (depth == "depth2") {
            $("#after_depth2").html(result);
	        $("#after_depth3").html("<option value=''>depth3</option>");
        } else {
            $("#after_depth3").html(result);
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//후공정 제조사 가져오기
var loadAfterManu = function(event, search_str, dvs) {
    
    if (event.keyCode != 13) {
        return false;
    }

    var url = "/ajax/basic_mng/common_mng/load_manu_list.php";
    var data = { 
        "pur_prdt" : "후공정",
        "search_str" : search_str
    };

    var callback = function(result) {
        if (dvs != "select") {
            searchPopShow(event, 'loadAfterManu', 'loadAfterManu');
        } else {
            showBgMask();
        }
        $("#search_list").html(result);
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//브랜드명 가져오기
var loadAfterBrand = function(event, search_str, dvs, type) {

	if (type != "Y") {
		if (event.keyCode != 13) {
			return false;
		}
	}

	if($("#manu_name").val() == "") {

		if (type != "Y") {
			alert("제조사를 선택하셔야합니다.");
		}
	    $("#brand_name").html("<option value=''>제조사 선택</option>");
        $("#select_after_name").html("<option value=''>후공정명(전체)</option>");
        $("#after_depth1").html("<option value=''>depth1(전체)</option>");
        $("#after_depth2").html("<option value=''>depth2(전체)</option>");
        $("#after_depth3").html("<option value=''>depth3(전체)</option>");
		return false;
	}
 
    var url = "/ajax/basic_mng/common_mng/load_brand_list.php";
    var data = { 
        "manu_seqno" : $("#manu_name").val(),
        "search_str" : search_str,
		"type"       : type
    };

    var callback = function(result) {
	    //제조사 셀렉트 부분 변경시
	    if (type == "Y") {
            $("#brand_name").html(result);
            loadAfterName();

		//검색 팝업으로 검색시
	    } else {
            if (dvs != "select") {
                searchPopShow(event, 'loadAfterBrand', 'loadAfterBrand');
            } else {
                showBgMask();
            }
            $("#search_list").html(result);
		}
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//후공정명 가져오기
var loadAfterName = function() {
    var url = "/ajax/basic_mng/after_mng/load_after_name.php";
    var data = {
        "manu_seqno"  : $("#manu_name").val(),
        "brand_seqno" : $("#brand_name").val()
    };
    var callback = function(result) {
        $("#select_after_name").html(result);
    };

    ajaxCall(url, "html", data, callback);
}

//팝업 검색된 제조사 클릭시
var manuClick = function(val) {

    hideRegiPopup();
    $("#manu_name").val(val);
    loadAfterBrand('', '', '','Y');
}

//팝업 검색된 브랜드 클릭시
var brandClick = function(val) {

    hideRegiPopup();
    $("#brand_name").val(val);
}

//선택 조건으로 검색
var selectSearch = function(page, type) {

    tabCtrl(tab_id, page, type);
}

//후공정 정보 팝업에 입력
var loadAfterInfo = function(seq) {

    after_seqno = seq;

    var url = "/ajax/basic_mng/after_mng/load_after_info.php";
    var data = { 
        "after_seqno" : seq
    };

    var callback = function(result) {
        var tmp = result.split('♪♥♭');
        var after_info = tmp[1].split('♪♡♭');

        hideMask();
        openRegiPopup(tmp[0], "800");

        $("#after_name").val(after_info[0]);
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//보여 주는 페이지 갯수 설정
var showPageSetting = function(val) {

    list_num = val;
    tabCtrl(tab_id, page, '2');
} 

//선택 조건으로 검색(페이징 클릭)
var searchResult = function(page) {
    selectSearch(page, "2");
}

//전체 선택
var allCheck = function() {

    //만약 전체선택 체크박스가 체크 된 상태일 경우
    if ($("#all_check").prop("checked")) {
        $("#after_list input[type=checkbox]").prop("checked", true);
    } else {
        $("#after_list input[type=checkbox]").prop("checked", false);
    }
}

//후공정 품목 저장
var saveAfter = function(type) {

    //후공정명이 비었을때
    if($("#pop_after_name").val() == "") {

        alert("후공정명을 입력해주세요.");
	    $("#pop_after_name").focus();
        return false;
    }

    var formData = new FormData($("#after_form")[0]);
        formData.append("after_seqno", after_seqno);

    $.ajax({
            type: "POST",
            data: formData,
	        processData : false,
	        contentType : false,
            url: "/proc/basic_mng/after_mng/proc_after.php",
            success: function(result) {
            	if($.trim(result) == "1") {

	    	        alert("수정했습니다.");
		        hideRegiPopup();
    	    	    	selectSearch("1", "1");

	        } else {

	    	    	alert("실패했습니다.");
	       }
           }, 
           error: getAjaxError
    });
}

//후공정 선택 삭제
var delAfter = function() {

    var select_after = getselectedNo();

    if (select_after == "") {

        alert("삭제할 목록을 선택해주세요");
        return false;
    }

    delPopAfter(select_after);
}

//후공정 개별 삭제
var delPopAfter = function(seqno) {
    var url = "/proc/basic_mng/after_mng/del_after_prdt.php";
    var data = {};
    var callback = function(result) {
        if($.trim(result) == "1") {
            alert("삭제했습니다.");
            hideRegiPopup();
            selectSearch("1", "1");
        } else if ($.trim(result) == "3") {
            alert("카테고리관리-기본생산업체에 지정된\n항목은 제외하고 삭제했습니다.");
            hideRegiPopup();
            selectSearch("1", "1");
        } else {
            alert("삭제에 실패했습니다.");
        }
    };

    if (checkBlank(seqno)) {
        data.select_prdt = after_seqno;
    } else {
        data.select_prdt = seqno;
    }

    ajaxCall(url, "text", data, callback);
}

//체크박스 선택시 value값 가져오는 함수
var getselectedNo = function(el) {

    var selectedValue = ""; 
    
    $("#after_list input[name=after_chk]:checked").each(function() {
        selectedValue += ","+ $(this).val();		    
    });

    if (selectedValue != "") {
        selectedValue = selectedValue.substring(1);
    }

    return selectedValue;
}

//컬럼별 sort
var sortList = function(val, el) {

    var flag = "";

    if ($(el).children().hasClass("fa-sort-desc")) {
        sortInit();
        $(el).children().addClass("fa-sort-asc");
        $(el).children().removeClass("fa-sort");
        flag = "ASC";
    } else {
        sortInit();
        $(el).children().addClass("fa-sort-desc");
        $(el).children().removeClass("fa-sort");
        flag = "DESC";
    }

    var sort = val + "/" + flag;

    tabCtrl(tab_id, '1', sort);
}

// 탭 클릭시
var tabCtrl = function(el, page, type) { 

    //팝업 감추기
    showMask();

    tab_id = el; //탭 선택 
    var search_str = ""; //검색어 

    //탭클릭이나 상단의 검색바로 검색시 value 값 설정
    if (type == "1") {

        //리스트 카운트 초기화
        list_num = "30";
        $('select[name=list_set]').val('30');
        after_data.name = $("#select_after_name").val();
        after_data.depth1 = $("#after_depth1").val();
        after_data.depth2 = $("#after_depth2").val();
        after_data.depth3 = $("#after_depth3").val();
        after_data.manu_seqno  = $("#manu_name").val();
        after_data.brand_seqno = $("#brand_name").val();
        after_data.affil       = $("#affil").val();
        after_data.subpaper    = $("#subpaper").val();
        after_data.crtr_unit   = $("#after_crtr_unit").val();

    }

    //페이지 list 갯수
    after_data.list_num = list_num;

    //페이지
    after_data.page = page;

    //부가세
    after_data.tax_yn = $("input[name='tax_yn']:checked").val();

    //탭 별 Ajax호출
    if (tab_id == "prdt") {

        afterPrdcAjax(after_data);

    } else {
        if (checkBlank($("#select_after_name").val()) === true) {
            alert("후공정명을 선택해주세요.");
            hideMask();
            return false;
        }

        var url = "/ajax/basic_mng/after_mng/load_after_price.php";
        var callback = function(result) {
            $("#after_price_list").show();
            $("#after_price_list").html(result);
        };

        ajaxCall(url, "html", after_data, callback);

        //준현씨 가격
        hideMask();
    }
}

//후공정 Ajax 호출
var afterPrdcAjax = function(formData) {

    $.ajax({
            type: "POST",
            data: formData,
            url: "/ajax/basic_mng/after_mng/load_after_list.php",
            success: function(result) {

                var list = result.split('★');
	            if ($.trim(list[0]) == "") {

                	$("#after_list").html("<tr><td colspan='12'>검색된 내용이 없습니다.</td></tr>"); 

	            } else {

                	$("#after_list").html(list[0]);
                	$("#after_page").html(list[1]); 
			        $('select[name=list_set]').val(list_num);

	            }
	   	    hideMask();
           },
           error: getAjaxError
    });
}

/**
 * @brief 제목 탭에서 요율, 적용금액을 클릭했을 경우 전체수정팝업 출력
 *
 * @param event = 좌표값을 얻기위한 이벤트 객체
 * @param dvs   = 어떤 항목을 클릭했는지 구분값
 * @param pos   = 몇 번째 가격항목인지 위치
 */
var modiPriceInfo = {
    "dvs"  : null,
    "pos"  : null,
    "exec" : function(event, dvs, pos) {
        var point = getPopupPoint(event);

        selectedModiAllPriceDvs(dvs);

        this.dvs = dvs;
        this.pos = pos;

        hideModiPop("modi_each_price");
        showModiPop("modi_all_price", point.x, point.y);
    }
};

/**
 * @brief 일괄수정 적용버튼 클릭시
 */
var aplyPriceInfo = function() {
    if (checkBlank($("#modi_all_price_val").val()) === true) {
        if (modiPriceInfo.dvs === "R") {
            alert("요율을 입력해주세요.");
            return false;
        } else {
            alert("적용금액을 입력해주세요.");
            return false;
        }
    }

    var pos = modiPriceInfo.pos;

    var $manu     = $("#after_manu_" + pos);
    var $brand    = $("#after_brand_" + pos);
    var $name     = $("#after_name_" + pos);
    var $depth1   = $("#after_depth1_" + pos);
    var $depth2   = $("#after_depth2_" + pos);
    var $depth3   = $("#after_depth3_" + pos);
    var $crtrUnit = $("#crtr_unit_" + pos);

    var url = "/proc/basic_mng/after_mng/update_after_price.php";
    var data = {
        "manu"      : $manu.attr("val"),
        "brand"     : $brand.attr("val"),
        "name"      : $name.attr("val"),
        "depth1"    : $depth1.attr("val"),
        "depth2"    : $depth2.attr("val"),
        "depth3"    : $depth3.attr("val"),
        "crtr_unit" : $crtrUnit.attr("val"),
        "val"       : $("#modi_all_price_val").val(),
        "dvs"       : modiPriceInfo.dvs
    };
    var callback = function(result) {
        if (result === "T") {
            tabCtrl("price", 1, 1);
        } else {
            alert("가격 수정에 실패했습니다.");
        }

        hideModiPop();
    };

    ajaxCall(url, "text", data, callback);
}

/**
 * @brief 내용에서 요율, 적용금액을 클릭했을 경우 개별수정팝업 출력
 *
 * @param event = 좌표값을 얻기위한 이벤트 객체
 * @param dvs   = 어떤 항목을 클릭했는지 구분값
 * @param seqno = 가격항목 seqno
 */
var modiPriceInfoEach = {
    "dvs"    : null,
    "seqno"  : null,
    "exec"   : function(event, dvs, seqno) {
        var point = getPopupPoint(event);

        this.dvs     = dvs;
        this.seqno   = seqno;

        hideModiPop("modi_all_price");
        showModiPop("modi_each_price", point.x + 100, point.y);
    }
};

/**
 * @brief 개별수정 적용버튼 클릭시
 */
var aplyPriceInfoEach = function() {
    if (checkBlank($("#modi_each_price_val").val()) === true) {
        if (modiPriceInfoEach.dvs === "R") {
            alert("요율을 입력해주세요.");
            return false;
        } else {
            alert("적용금액을 입력해주세요.");
            return false;
        }
    }

    var url = "/proc/basic_mng/after_mng/update_after_price.php";
    var data = {
        "val"         : $("#modi_each_price_val").val(),
        "dvs"         : modiPriceInfoEach.dvs,
        "price_seqno" : modiPriceInfoEach.seqno,
    };
    var callback = function(result) {
        if (result === "T") {
            tabCtrl("price", 1, 1);
        } else {
            alert("가격 수정에 실패했습니다.");
        }

        hideModiPop();
    };

    ajaxCall(url, "text", data, callback);
};

/**
 * @brief 엑셀 다운로드시 사용되는 함수
 */
var downloadFile =  function() {
    if (checkBlank($("#manu_name").val()) === true) {
        alert("제조사를 선택해주세요.");
        return false;
    }

    var url = "/ajax/basic_mng/after_mng/down_excel_after_price.php";
    var data = null;
    var callback = function(result) {
        if (result === "FALSE") {
            alert("엑셀파일 생성에 실패했습니다.");
        } else if (result === "NOT_INFO") {
            alert("엑셀로 생성할 정보가 존재하지 않습니다.");
        } else {
            var nameArr = result.split('!');

            var downUrl  = "/common/excel_file_down.php?name=" + nameArr[1];
                downUrl += "&file_dvs=" + nameArr[0];
            $("#file_ifr").attr("src", downUrl);
        }
    };

    after_data.name = $("#select_after_name").val();
    after_data.depth1 = $("#after_depth1").val();
    after_data.depth2 = $("#after_depth2").val();
    after_data.depth3 = $("#after_depth3").val();
    after_data.manu_seqno  = $("#manu_name").val();
    after_data.brand_seqno = $("#brand_name").val();
    after_data.affil       = $("#affil").val();
    after_data.subpaper    = $("#subpaper").val();
    after_data.crtr_unit   = $("#after_crtr_unit").val();

    showMask();

    ajaxCall(url, "text", after_data, callback);
};

/**
 * @brief 엑셀 업로드 할 때 사용하는 함수
 *
 * @param dvs = 어떤 엑셀 파일을 업로드 하는지 구분
 */
var uploadFile = function(dvs) {

    var formData = new FormData();

    formData.append("dvs", dvs);

    if(checkExt($("#price_excel_path")) === false) {
        return false;
    }

    formData.append("file", $("#price_excel")[0].files[0]);

    showMask();

    excelUploadAjax(formData);
};
