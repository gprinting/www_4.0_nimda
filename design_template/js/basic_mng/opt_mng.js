/*
 *
 * Copyright (c) 2015 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *============================================================================
 * 2015/12/10 왕초롱 생성
 * 2016/05/18 임종건 버그 수정 및 추가 개발
 *============================================================================
 *
 */

var page = "1"; //페이지
var list_num = "30"; //리스트 갯수
var tab_id = "prdt"; //탭 id
var opt_seqno = "";
var opt_data = {
	"list_num"     : "",
	"page"         : "1",
	"name"         : "",
	"depth1"       : "",
	"depth2"       : "",
	"depth3"       : "",
	"crtr_unit"    : ""
}

$(document).ready(function() {
    selectSearch(1,1);
});

//옵션 depth 이름 가져오기
var loadDepthName = function(val, depth) {

    var url = "/ajax/basic_mng/common_mng/load_opt_depth_name.php";
    var data = { 
        "name" : val,
        "depth": depth
    };

    var callback = function(result) {
        if (depth == "depth1") {
            $("#opt_depth1").html(result);
	    $("#opt_depth2").html("<option value=''>depth2</option>");
	    $("#opt_depth3").html("<option value=''>depth3</option>");
        } else if (depth == "depth2") {
            $("#opt_depth2").html(result);
	    $("#opt_depth3").html("<option value=''>depth3</option>");
        } else {
            $("#opt_depth3").html(result);
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//선택 조건으로 검색
var selectSearch = function(page, type) {
    tabCtrl(tab_id, page, type);
}

//옵션 정보 팝업에 입력
var loadOptInfo = function(seq) {

    opt_seqno = seq;

    var url = "/ajax/basic_mng/opt_mng/load_opt_info.php";
    var data = { 
        "opt_seqno" : seq
    };

    var callback = function(result) {
        var tmp = result.split('♪♥♭');
        var opt_info = tmp[1].split('♪♡♭');

        hideMask();
        openRegiPopup(tmp[0], "800");

        $("#opt_name").val(opt_info[0]);
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
        $("#opt_list input[type=checkbox]").prop("checked", true);
    } else {
        $("#opt_list input[type=checkbox]").prop("checked", false);
    }
}

//옵션 품목 저장
var saveOpt = function(type) {

    //옵션명이 비었을때
    if($("#pop_opt_name").val() == "") {

        alert("옵션명을 입력해주세요.");
	    $("#pop_opt_name").focus();
        return false;
    }

    var formData = new FormData($("#opt_form")[0]);
        formData.append("opt_seqno", opt_seqno);

        $.ajax({
            type: "POST",
            data: formData,
	        processData : false,
	        contentType : false,
            url: "/proc/basic_mng/opt_mng/proc_opt.php",
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

//옵션 선택 삭제
var delOpt = function() {

    var select_opt = getselectedNo();

    if (select_opt == "") {

        alert("삭제할 목록을 선택해주세요");
        return false;
    }

    $.ajax({
            type: "POST",
            data: {
                "select_prdt" : select_opt
            },
            url: "/proc/basic_mng/opt_mng/del_opt_prdt.php",
            success: function(result) {
            if($.trim(result) == "1") {

                alert("삭제했습니다.");

            } else {

                alert("삭제에 실패했습니다.");

            }
            selectSearch("1", "1");
           }, 
           error: getAjaxError
    });
}

//옵션 개별 삭제
var delPopOpt = function() {

    $.ajax({
            type: "POST",
            data: {
                "select_prdt" : opt_seqno
            },
            url: "/proc/basic_mng/opt_mng/del_opt_prdt.php",
            success: function(result) {
            if($.trim(result) == "1") {

                alert("삭제했습니다.");
    		hideRegiPopup();
                selectSearch("1", "1");

            } else {

                alert("삭제에 실패했습니다.");

            }
           }, 
           error: getAjaxError
    });
}

//체크박스 선택시 value값 가져오는 함수
var getselectedNo = function(el) {

    var selectedValue = ""; 
    
    $("#opt_list input[name=opt_chk]:checked").each(function() {
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
        opt_data.name = $("#select_opt_name").val();
        opt_data.depth1 = $("#opt_depth1").val();
        opt_data.depth2 = $("#opt_depth2").val();
        opt_data.depth3 = $("#opt_depth3").val();
        opt_data.crtr_unit =  $("#opt_crtr_unit").val();

    }

    //페이지 list 갯수
    opt_data.list_num = list_num;

    //페이지
    opt_data.page = page;

    //탭 별 Ajax호출
    if (tab_id == "prdt") {

        optPrdcAjax(opt_data);

    } else {
        if (checkBlank($("#select_opt_name").val()) === true) {
            alert("옵션명을 선택해주세요.");
            hideMask();
            return false;
        }

        var url = "/ajax/basic_mng/opt_mng/load_opt_price.php";
        var callback = function(result) {
            $("#opt_price_list").show();
            $("#opt_price_list").html(result);
        };

        ajaxCall(url, "html", opt_data, callback);

        //준현씨 가격
        hideMask();

    }
}

//옵션 Ajax 호출
var optPrdcAjax = function(formData) {

    $.ajax({
        type: "POST",
        data: formData,
        url: "/ajax/basic_mng/opt_mng/load_opt_list.php",
        success: function(result) {

            var list = result.split('★');
	        if ($.trim(list[0]) == "") {

            	$("#opt_list").html("<tr><td colspan='8'>검색된 내용이 없습니다.</td></tr>"); 

	        } else {

            	$("#opt_list").html(list[0]);
            	$("#opt_page").html(list[1]); 
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

    var $name     = $("#opt_name_" + pos);
    var $depth1   = $("#opt_depth1_" + pos);
    var $depth2   = $("#opt_depth2_" + pos);
    var $depth3   = $("#opt_depth3_" + pos);
    var $crtrUnit = $("#crtr_unit_" + pos);

    var url = "/proc/basic_mng/opt_mng/update_opt_price.php";
    var data = {
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
        showModiPop("modi_each_price", point.x, point.y);
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

    var url = "/proc/basic_mng/opt_mng/update_opt_price.php";
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
    if (checkBlank($("#select_opt_name").val()) === true) {
        alert("옵션명을 입력해주세요.");
        return false;
    }

    var url = "/ajax/basic_mng/opt_mng/down_excel_opt_price.php";
    var data = null;
    var callback = function(result) {
        if (result === "FALSE") {
            alert("엑셀파일 생성에 실패했습니다.");
        } else if (result === "NOT_INFO") {
            alert("엑셀로 생성할 정보가 존재하지 않습니다.");
        } else {
            var downUrl  = "/common/excel_file_down.php?name=" + result;
            $("#file_ifr").attr("src", downUrl);
        }
    };

    opt_data.name = $("#select_opt_name").val();
    opt_data.depth1 = $("#opt_depth1").val();
    opt_data.depth2 = $("#opt_depth2").val();
    opt_data.depth3 = $("#opt_depth3").val();
    opt_data.crtr_unit =  $("#opt_crtr_unit").val();

    showMask();

    ajaxCall(url, "text", opt_data, callback);
};

/**
 * @brief 출력 가격 양식 다운로드시 사용되는 함수
 */
var downloadSampleFile = function () {
    var downUrl  = "/common/excel_sample_down.php?name=opt_pur_price_sample";
    $("#file_ifr").attr("src", downUrl);
}

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
