/*
 *
 * Copyright (c) 2015 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2016/01/12 임종건 생성
 *=============================================================================
 *
 */

//선택된 탭
//var selectTab = "paper";
var selectTab = "output";
//선택 된 조판판형 일련번호
var selectTypsetSeqno = "";
//선택 된 카테고리 레벨
var selectLevel = "";
//리스트 갯수
var showPage = "30";
//검색단어
var searchTxt = "";
//현재 페이지
var nowPage = "1";

$(document).ready(function(){
    //cndSearch.exec("paper", 30, 1, searchTxt ,"");
    cndSearch.exec("output", 30, 1, searchTxt ,"");
});

//트리 초기화
var treeClickInit = function() {
    $(".one").removeClass("red_text01");   
    $(".two").removeClass("on");   
    $(".one").removeClass("fwb");   
    $(".two").removeClass("fwb");   
}

//첫번째 트리 클릭
var oneLevelTreeClick = function(el) {
   
    treeClickInit();
    $(el).addClass("red_text01");
    $(el).addClass("fwb");
}

//두번째 트리 클릭
var twoLevelTreeClick = function(el, seqno) {
    
    selectTypsetSeqno = seqno;
    getBasicProMngInfo();
    treeClickInit();
    $(el).addClass("on");
    $(el).addClass("fwb");
}

//초기화
var init = function(el) {

    //종이
    if (el === "paper") {
        $("#" + el + "_name").val("");
        $("#" + el + "_extnl").val("");
        $("#" + el + "_purp").val("");
        $("#" + el + "_storplace_dvs").val("");
        $("#" + el + "_storplace").val("");
        $("#" + el + "_grain").val("");
    //출력
    } else if (el === "output") {
        $("#" + el + "_name").val("");
        $("#" + el + "_extnl").val("");
        $("#" + el + "_board").val("");
        $("#" + el + "_wid_size").val("");
        $("#" + el + "_vert_size").val("");
        $("#" + el + "_storplace_dvs").val("");
        $("#" + el + "_storplace").val("");
    //인쇄
    } else if (el === "print") {
        $("#" + el + "_name").val("");
        $("#" + el + "_extnl").val("");
        $("#" + el + "_wid_size").val("");
        $("#" + el + "_vert_size").val("");
        $("#" + el + "_storplace_dvs").val("");
        $("#" + el + "_storplace").val("");
    //후공정
    } else if (el === "after") {
        $("#" + el + "_name").val("");
        $("#" + el + "_extnl").val("");
        $("#" + el + "_depth1").val("");
        $("#" + el + "_depth2").val("");
        $("#" + el + "_depth3").val("");
    //옵션
    } else if (el === "opt") {
        $("#" + el + "_name").val("");
        $("#" + el + "_depth1").val("");
        $("#" + el + "_depth2").val("");
        $("#" + el + "_depth3").val("");
    }
    
    $("#" + el + "_seqno").val("");
    $("#" + el + "_produce_yn").prop("checked", false);
}

//카테고리 소분류에 저장된 정보 가져옴
var getBasicProMngInfo = function() {

    if (checkBlank(selectTypsetSeqno)) {
        return false;
    }

    var el = selectTab;
 
    //입출고 배송 기획 없음
    if (el === "stor_release" || el === "dlvr") {
        return false;
    }

    var url = "/ajax/manufacture/typset_format/load_" + el + "_cont.php";
    var data = {
        "el"            : el,
        "seqno"         : selectTypsetSeqno
    };

    var callback = function(result) {
	
        var rs = result.split("♪");
 
	init(el);
	//종이
        if (el === "paper") {

	    if (!checkBlank(rs[0])) {
                $("#" + el + "_storplace").html(rs[0]);
	    }
	    if (!checkBlank(rs[1])) {
                $("#" + el + "_purp").val(rs[1]);
	    }
	    if (!checkBlank(rs[2])) {
                $("#" + el + "_seqno").val(rs[2]);
                selectRadio.exec(el, rs[2]); 
	    }
	    if (!checkBlank(rs[3])) {
                $("#" + el + "_storplace_dvs").val(rs[3]);
	    }
	    if (!checkBlank(rs[4])) {
                $("#" + el + "_storplace").val(rs[4]);
	    }
	    if (!checkBlank(rs[5])) {
                $("#" + el + "_grain").val(rs[5]);
	    }
	    if (!checkBlank(rs[6])) {
                if (rs[6] == "Y") {
                    $("#" + el + "_produce_yn").prop("checked", true);
		} else {
                    $("#" + el + "_produce_yn").prop("checked", false);
		}
	    }

        //후공정
        } else if (el === "after") {
            var blank = "<tr><td colspan=\"7\">내용이 없습니다.</td></tr>";
            if (rs[0].trim() == "") {
	        $("#after_regi_list").html(blank);
	    } else {
	        $("#after_regi_list").html(rs[0]);
	    }
	    if (!checkBlank(rs[1])) {
                if (rs[1] == "Y") {
                    $("#" + el + "_produce_yn").prop("checked", true);
		} else {
                    $("#" + el + "_produce_yn").prop("checked", false);
		}
	    }

	//옵션
        } else if (el === "opt") {
            var blank = "<tr><td colspan=\"6\">내용이 없습니다.</td></tr>";
            if (rs[0].trim() == "") {
	        $("#opt_regi_list").html(blank);
	    } else {
	        $("#opt_regi_list").html(rs[0]);
	    }
	    if (!checkBlank(rs[1])) {
                if (rs[1] == "Y") {
                    $("#" + el + "_produce_yn").prop("checked", true);
		} else {
                    $("#" + el + "_produce_yn").prop("checked", false);
		}
	    }

	//입출고
        } else if (el === "stor_release") {
	//배송
        } else if (el === "dlvr") {

	//출력 인쇄
	} else {
	    if (!checkBlank(rs[0])) {
                $("#" + el + "_storplace").html(rs[0]);
	    }
	    if (!checkBlank(rs[1])) {
                $("#" + el + "_seqno").val(rs[1]);
                selectRadio.exec(el, rs[1]); 
	    }
	    if (!checkBlank(rs[2])) {
                $("#" + el + "_storplace_dvs").val(rs[2]);
	    }
	    if (!checkBlank(rs[3])) {
                $("#" + el + "_storplace").val(rs[3]);
	    }
	    if (!checkBlank(rs[4])) {
                if (rs[4] == "Y") {
                    $("#" + el + "_produce_yn").prop("checked", true);
		} else {
                    $("#" + el + "_produce_yn").prop("checked", false);
		}
	    }
	}
    }

    showMask();

    ajaxCall(url, "html", data, callback);
}

//탭 컨트롤
var tabCtrl = function(el) {
    searchTxt = "";
    nowPage = 1;
    showPage  = 30;
    $("select[name=list_set]").val("30");
    sortInit();

    selectTab = el;
    getBasicProMngInfo();
    cndSearch.exec(el, 30, 1, searchTxt, "");
}

/**
 * @brief 선택조건으로 검색 클릭시
 */
var cndSearch = {
    "exec"       : function(el, listSize, page, txt, sorting) {
        
        var url = "/ajax/manufacture/typset_format/load_typset_format_list.php";
        var blank = "<tr><td colspan=\"10\">검색 된 내용이 없습니다.</td></tr>";
        var tmp = sorting.split('/');
        for (var i in tmp) {
            tmp[i];
        }

        var data = {
    	    "el"                  : el,
    	    "listSize"            : listSize,
    	    "page"                : page,
            "sorting"             : tmp[0],
            "sorting_type"        : tmp[1],
	    "search_txt"          : txt,
	    "typset_format_seqno" : selectTypsetSeqno
	};

        var callback = function(result) {
            var rs = result.split("♪");
            if (rs[0].trim() == "") {
                $("#" + el + "_list").html(blank);
                return false;
            }
            $("#" + el + "_list").html(rs[0]);
            $("#" + el + "_page").html(rs[1]);
        };

        showMask();
        ajaxCall(url, "html", data, callback);
    }
};

/**
* @brief 보여줄 페이지 수 설정
*/
var showPageSetting = function(val, el) {
    showPage = val;
    cndSearch.exec(el, val, 1, searchTxt, "");
}

/**
* @brief 페이지 이동
*/
var movePage = function(val, el) {
    cndSearch.exec(el, showPage, val, searchTxt, "");
}

//컬럼별 sorting
var sortList = function(val, el, selectEl) {

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

    var sorting = val + "/" + flag;
 
    cndSearch.exec(selectEl, showPage, 1, searchTxt, sorting);
}

//리스트 라디오 선택
var selectRadio = {
    "exec"       : function(el, seqno) {
        
        var url = "/ajax/manufacture/typset_format/load_" + el + "_info.php";
        var data = {
	    "el"     : el,
            "seqno"  : seqno
	};

        var callback = function(result) {
            var rs = result.split("♪");
     
	    //종이
	    if (el === "paper") {
                $("#" + el + "_name").val(rs[0]);
                $("#" + el + "_extnl").val(rs[1]);
                $("#" + el + "_seqno").val(seqno);

	    //출력
	    } else if (el === "output") {
	        $("#" + el + "_name").val(rs[0]);
                $("#" + el + "_extnl").val(rs[1]);
                $("#" + el + "_board").val(rs[2]);
                $("#" + el + "_wid_size").val(rs[3]);
                $("#" + el + "_vert_size").val(rs[4]);
                $("#" + el + "_seqno").val(seqno);

            //인쇄
	    } else if (el === "print") {
	        $("#" + el + "_name").val(rs[0]);
                $("#" + el + "_extnl").val(rs[1]);
                $("#" + el + "_wid_size").val(rs[2]);
                $("#" + el + "_vert_size").val(rs[3]);
                $("#" + el + "_seqno").val(seqno);

            //후공정
	    } else if (el === "after") {
	        $("#" + el + "_name").val(rs[0]);
                $("#" + el + "_extnl").val(rs[1]);
                $("#" + el + "_depth1").val(rs[2]);
                $("#" + el + "_depth2").val(rs[3]);
                $("#" + el + "_depth3").val(rs[4]);
                $("#" + el + "_seqno").val(seqno);
	    }
        };

        showMask();
        ajaxCall(url, "html", data, callback);
    }
};

//검색어 검색 엔터
var searchKey = function(event, val, el) {

    if (event.keyCode == 13) {
        cndSearch.exec(el, showPage, 1, val, "");
    }
}

//검색어 검색 버튼
var searchText = function(el) {
    cndSearch.exec(el, showPage, 1, $("#" + el + "_search"), "");
}

//저장
var regiData = function(el) {

    if (checkBlank(selectTypsetSeqno)) {
        alert("제조사가 선택되지 않았습니다.");
        return false;
    }

    var url = "/proc/manufacture/typset_format/regi_basic_pro_mng.php";

    if (el === "after") {
        url = "/proc/manufacture/typset_format/regi_basic_pro_after_mng.php";
    }

    var data = {
        "el"            : el,
        "seqno"         : selectTypsetSeqno
    };

    var produce_yn = "";

    if (!$("#" + el + "_produce_yn").prop("checked")) {
        if (confirm("공정 추가가 체크 안되어 있습니다.\n 체크를 하고 진행을 하시겠습니까?\n(체크가 되지 않을시 내용이 삭제 됩니다.)") == false) {
            produce_yn = "N";
	    init(el);
	    return false;

	} else {
            $("#" + el + "_produce_yn").prop("checked", true);
            produce_yn = "Y";
	}
    }

    var callback = function(result) {
        if (result == 1) {
	/* 초기화 하지말자고 하셨던 요청사항
            $("#" + selectTab + "_produce_yn").prop("checked", false);
            cndSearch.exec(selectTab, 30, 1, searchTxt, "");
	*/
            getBasicProMngInfo();
	    $("input:radio[name='" + el + "']").removeAttr("checked");
            alert("카테고리 별 기본생산업체관리를 등록하였습니다.");
        } else {
            alert("카테고리 별 기본생산업체관리를 등록을 실패하였습니다.");
        }
    }

    //종이
    if (el === "paper") {
        data.purp              = $("#" + el + "_purp").val();
        data.grain             = $("#" + el + "_grain").val();
        data.extnl_etprs_seqno = $("#" + el + "_storplace").val();
        data.paper_seqno       = $("#" + el + "_seqno").val();
        data.paper_produce_yn  = produce_yn;
    //출력
    } else if (el === "output") {
        data.extnl_etprs_seqno = $("#" + el + "_storplace").val();
        data.output_seqno       = $("#" + el + "_seqno").val();
        data.output_produce_yn  = produce_yn;

    //인쇄
    } else if (el === "print") {
        data.extnl_etprs_seqno = $("#" + el + "_storplace").val();
        data.print_seqno       = $("#" + el + "_seqno").val();
        data.print_produce_yn  = produce_yn;
    //후공정 
    } else if (el === "after") {
        data.extnl_etprs_seqno = $("#" + el + "_storplace").val();
        data.after_seqno       = $("#" + el + "_seqno").val();
        data.after_produce_yn  = produce_yn;
    //옵션
    } else if (el === "opt") {
        data.opt_seqno       = $("#" + el + "_seqno").val();
        data.opt_produce_yn  = produce_yn;
    }

    showMask();
    ajaxCall(url, "html", data, callback);
}

//후공정 옵션 삭제
var delRegiList = function(seqno, el) {

    var url = "/proc/manufacture/typset_format/del_basic_pro_mng.php";
    var data = {
        "el"            : el,
        "seqno"         : seqno
    };

    var str = "";
    if (el === "after") {
        str = "후공정";
    } else if (el === "opt") {
        str = "옵션";
    }

    var callback = function(result) {
        if (result == 1) {
            getBasicProMngInfo();
            alert("등록 된 " + str + "을 삭제하였습니다.");
        } else {
            alert("삭제를 실패하였습니다.");
        }
    }

    showMask();
    ajaxCall(url, "html", data, callback);
}

//매입품에 해당하는 매입업체 가져오기
var loadExtnlEtprs = function(val, el) {

    $.ajax({
        type: "POST",
        data: {"val" : val},
        url: "/ajax/basic_mng/pur_etprs_list/load_extnl_etprs.php",
        success: function(result) {
            $("#" + el).html(result);
        }, 
        error: getAjaxError
    });
};
