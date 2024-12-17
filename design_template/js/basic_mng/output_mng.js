/*
 *
 * Copyright (c) 2015 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2015/12/09 왕초롱 생성
 * 2016/11/04 엄준현 수정(삭제부분 로직 수정)
 *=============================================================================
 *
 */

var page = "1"; //페이지
var list_num = "30"; //리스트 갯수
var tab_id = "prdt"; //탭 id
var output_seqno = "";
var output_data = {
	"list_num"     : "",
	"page"         : "1",
	"name"         : "",
	"manu_seqno"   : "",
	"brand_seqno"  : "",
	"affil_fs"     : "",
	"affil_guk"    : "",
	"affil_spc"    : "",
	"crtr_unit"    : "",
	"sort"         : "",
	"sort_type"    : ""
}

$(document).ready(function() {

    selectSearch(1,1);

});

//출력명 가져오기
var loadOutputName = function(event, search_str, dvs) {
    
    if (event.keyCode != 13) {
        return false;
    }

    showMask();

    $.ajax({
            type: "POST",
            data: {
                "manu_seqno"  : $("#manu_name").val(),
                "brand_seqno" : $("#brand_name").val(),
                "search_str"  : search_str
            },
            url: "/ajax/basic_mng/output_mng/load_output_name.php",
            success: function(result) {
                if (dvs != "select") {

                    searchPopShow(event, 'loadOutputName', 'loadOutputName');

                } else {

                    showBgMask();

                }
                hideMask();
                $("#search_list").html(result);
            },
            error: getAjaxError
    });
}

//출력 제조사 가져오기
var loadOutputManu = function(event, search_str, dvs) {
    
    if (event.keyCode != 13) {
        return false;
    }

    showMask();

    $.ajax({
            type: "POST",
            data: {
	    	"pur_prdt" : "출력",
                "search_str" : search_str
            },
            url: "/ajax/basic_mng/common_mng/load_manu_list.php",
            success: function(result) {
                if (dvs != "select") {

                    searchPopShow(event, 'loadOutputManu', 'loadOutputManu');

                } else {

                    showBgMask();

                }
                hideMask();
                $("#search_list").html(result);
            },
            error: getAjaxError
    });
}

//브랜드명 가져오기
var loadOutputBrand = function(event, search_str, dvs, type) {

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
        $("#select_output_name").html("<option value=''>출력명 선택</option>");

		return false;
	}

    showMask();

    $.ajax({
            type: "POST",
            data: {
                "manu_seqno" : $("#manu_name").val(),
                "search_str" : search_str,
		"type"       : type
            },
            url: "/ajax/basic_mng/common_mng/load_brand_list.php",
            success: function(result) {
	    	//제조사 셀렉트 부분 변경시
	        if (type == "Y") {

                    $("#brand_name").html(result);
                    loadOutputNameSelect();

		    //검색 팝업으로 검색시
	    	} else {
                	if (dvs != "select") {

                    		searchPopShow(event, 'loadOutputBrand', 'loadOutputBrand');

                	} else {

                    		showBgMask();

                	}
                	$("#search_list").html(result);
	   	}
            hideMask();
           },
           error: getAjaxError
    });
}

//출력명 가져오기
var loadOutputNameSelect = function() {
    var url = "/ajax/basic_mng/output_mng/load_output_name_select.php";
    var data = {
        "manu_seqno"  : $("#manu_name").val(),
        "brand_seqno" : $("#brand_name").val()
    };
    var callback = function(result) {
        $("#select_output_name").html(result);
    };

    ajaxCall(url, "html", data, callback);
}

//팝업 검색된 출력명 클릭시
var nameClick = function(val) {

    hideRegiPopup();
    $("#select_output_name").val(val);

}

//팝업 검색된 제조사 클릭시
var manuClick = function(val) {

    hideRegiPopup();
    $("#manu_name").val(val);
    loadOutputBrand('', '', '','Y');

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

//출력 정보 팝업에 입력
var loadOutputInfo = function(seq) {

    output_seqno = seq;
    showMask();

    $.ajax({

        type: "POST",
        data: {
                "output_seqno" : seq
        },
        url: "/ajax/basic_mng/output_mng/load_output_info.php",
        success: function(result) {

            var tmp = result.split('♪♥♭');
            var output_info = tmp[1].split('♪♡♭');

            hideMask();
            openRegiPopup(tmp[0], "800");

           $("#output_top").val(output_info[0]);
           $("#affil").val(output_info[1]);

        }, 
        error: getAjaxError
    });
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
        $("#output_list input[type=checkbox]").prop("checked", true);
    } else {
        $("#output_list input[type=checkbox]").prop("checked", false);
    }
}

//출력 품목 저장
var saveOutput = function(type) {

    //출력명이 비었을때
    if($("#pop_output_name").val() == "") {

        alert("출력명을 입력해주세요.");
	$("#pop_output_name").focus();
        return false;
    }

    var formData = new FormData($("#output_form")[0]);
        formData.append("output_seqno", output_seqno);

    $.ajax({
            type: "POST",
            data: formData,
	        processData : false,
	        contentType : false,
            url: "/proc/basic_mng/output_mng/proc_output.php",
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

//출력 선택 삭제
var delOutput = function() {

    var select_output = getselectedNo();

    if (select_output == "") {

        alert("삭제할 목록을 선택해주세요");
        return false;
    }

    delPopOutput(select_output);
}

//출력 개별 삭제
var delPopOutput = function(seqno) {
    var url = "/proc/basic_mng/output_mng/del_output_prdt.php";
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
        data.select_prdt = output_seqno;
    } else {
        data.select_prdt = seqno;
    }

    ajaxCall(url, "text", data, callback);
}

//체크박스 선택시 value값 가져오는 함수
var getselectedNo = function(el) {

    var selectedValue = ""; 
    
    $("#output_list input[name=output_chk]:checked").each(function() {
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
        output_data.name = $("#select_output_name").val();
        output_data.manu_seqno =  $("#manu_name").val();
        output_data.brand_seqno =  $("#brand_name").val();
        output_data.affil_fs =  $('input[name=affil_fs]:checked').val();
        output_data.affil_guk =  $('input[name=affil_guk]:checked').val();
        output_data.affil_spc =  $('input[name=affil_spc]:checked').val();
        output_data.crtr_unit =  $("#output_crtr_unit").val();

    } else if (type == "2") {
	    
	//결과내에서 검색
	 
    } else {

        //sort 정보
        var sort_info = type.split('/');
        for (var i in sort_info) {
            sort_info[i];
        }

        //sort할 컬럼이름
        output_data.sort = sort_info[0];
        //sort 종류(ex:DESC, ASC)
        output_data.sort_type = sort_info[1];

    }

    //페이지 list 갯수
    output_data.list_num = list_num;

    //페이지
    output_data.page = page;

    //부가세
    output_data.tax_yn = $("input[name='tax_yn']:checked").val();

    //탭 별 Ajax호출
    if (tab_id == "prdt") {

        outputPrdcAjax(output_data);

    } else {
        if (checkBlank($("#select_output_name").val()) === true) {
            alert("출력명을 선택해주세요.");
            hideMask();
            return false;
        }

        var url = "/ajax/basic_mng/output_mng/load_output_price.php";
        var callback = function(result) {
            $("#output_price_list").show();
            $("#output_price_list").html(result);
        };

        ajaxCall(url, "html", output_data, callback);

        //준현씨 가격
        hideMask();

    }
}

//출력 Ajax 호출
var outputPrdcAjax = function(formData) {

    $.ajax({
            type: "POST",
            data: formData,
            url: "/ajax/basic_mng/output_mng/load_output_list.php",
            success: function(result) {

                var list = result.split('★');
	            if ($.trim(list[0]) == "") {

                	$("#output_list").html("<tr><td colspan='11'>검색된 내용이 없습니다.</td></tr>"); 

	            } else {

                	$("#output_list").html(list[0]);
                	$("#output_page").html(list[1]); 
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

    var $manu     = $("#output_manu_" + pos);
    var $brand    = $("#output_brand_" + pos);
    var $top      = $("#output_top_" + pos);
    var $name     = $("#output_name_" + pos);
    var $board    = $("#output_board_" + pos);
    var $affil    = $("#output_affil_" + pos);
    var $size     = $("#output_size_" + pos);
    var $crtrUnit = $("#crtr_unit_" + pos);

    var url = "/proc/basic_mng/output_mng/update_output_price.php";
    var data = {
        "manu"      : $manu.attr("val"),
        "brand"     : $brand.attr("val"),
        "top"       : $top.attr("val"),
        "name"      : $name.attr("val"),
        "board"     : $board.attr("val"),
        "affil"     : $affil.attr("val"),
        "size"      : $size.attr("val"),
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

    var url = "/proc/basic_mng/output_mng/update_output_price.php";
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

    var url = "/ajax/basic_mng/output_mng/down_excel_output_price.php";
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

    output_data.name = $("#select_output_name").val();
    output_data.manu_seqno =  $("#manu_name").val();
    output_data.brand_seqno =  $("#brand_name").val();
    output_data.affil_fs =  $('input[name=affil_fs]:checked').val();
    output_data.affil_guk =  $('input[name=affil_guk]:checked').val();
    output_data.affil_spc =  $('input[name=affil_spc]:checked').val();
    output_data.crtr_unit =  $("#output_crtr_unit").val();

    showMask();

    ajaxCall(url, "text", output_data, callback);
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

