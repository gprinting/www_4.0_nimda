/*
 *
 * Copyright (c) 2015 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2015/11/20 임종건 생성
 * 2015/11/23 임종건 종이탭 관련 함수 및 내용 추가
 * 2015/11/25 임종건 사이즈탭 출력정보, 사이즈 관련 함수 및 내용 추가
 * 2015/11/26 임종건 인쇄탭 인쇄정보, 인쇄도수 관련 함수 및 내용 추가
 * 2015/11/27 임종건 후공정탭 후공정, 옵션탭 옵션 관련 함수 및 내용 추가
 *=============================================================================
 *
 */

//검색어
var searchTxt = "";
//현재 페이지
var nowPage = "";
//리스트 갯수
var showPage  = "";
//선택한 대분류
var selectSort = "";
//선택한 이름
var selectName = "";

$(document).ready(function() {
    tabCtrl("paper");
});

//검색조건 초기화
var init = function() {
    searchTxt = "";
    nowPage = 1;
    showPage  = 30;
    selectSort = "";
    selectName = "";
    $("select[name=list_set]").val("30");
    sortInit();
}

//탭 선택시 제어 함수
var tabCtrl = function(el) {

    if (el == "size") {
        init();
        $("#output_search").val("");
        $("#size_search").val("");
        listOptionAjaxCall("output", "");
        listOptionAjaxCall("size", "");
        prdtBasicAjaxCall("output", searchTxt, showPage, nowPage, selectSort, selectName, "");
        prdtBasicAjaxCall("size", searchTxt, showPage, nowPage, selectSort, selectName, "");
    } else if (el == "tmpt") {
        init();
        $("#print_search").val("");
        $("#tmpt_search").val("");
        listOptionAjaxCall("print", "");
        listOptionAjaxCall("tmpt", "");
        prdtBasicAjaxCall("print", searchTxt, showPage, nowPage, selectSort, selectName, "");
        prdtBasicAjaxCall("tmpt", searchTxt, showPage, nowPage, selectSort, selectName, "");
    } else {
        init();
        selectEl = el;
        $("#" + el + "_search").val("");
        listOptionAjaxCall(el, "");
        prdtBasicAjaxCall(el, searchTxt, showPage, nowPage, selectSort, selectName, "");
    }
}

//리스트 조건 선택박스 호출
var listOptionAjaxCall = function(el, sort) {

    showBgMask();
    showMask();
    var url = "/ajax/basic_mng/prdt_basic_regi/load_list_option.php";
    var data = {
    	"selectEl"   : el,
        "sort"       : sort
    };

    $.ajax({
        type: "POST",
        data: data,
        url: url,
        success: function(result) {
	
            hideMask();
            var tmp = result.split("♪");

            if (sort == "") {
                if (tmp[0]) {
                    $("#" + el + "_sort").html(tmp[1]);
                    $("#" + el + "_name").html(tmp[2]);
                } else {
                    $("#" + el + "_name").html(tmp[1]);
                }
            } else {
                $("#" + el + "_name").html(tmp[2]);
            }
        },
        error: getAjaxError 
    });
}

/*
 * 상품 기초등록 리스트 호출
 * el :선택 된 탭
 * txt : 검색한 단어
 * sPage : 페이지당 보여줄 리스트 줄수
 * page : 현재 페이지
 * sSort : 검색할 대분류
 * sName : 검색할 이름
 * sorting : 정렬
 */
var prdtBasicAjaxCall = function(el, txt, sPage, page, sSort, sName, sorting) {
 
    showMask();
    var tmp = sorting.split('/');
    for (var i in tmp) {
        tmp[i];
    }

    var emptyCheck = "";
    var data = {
    	"selectEl"      : el,
        "searchTxt"     : txt,
    	"showPage"      : sPage,
    	"page"          : page,
    	"select_sort"   : sSort,
    	"select_name"   : sName,
        "sorting"       : tmp[0],
        "sorting_type"  : tmp[1]
    };
    var url = "/ajax/basic_mng/prdt_basic_regi/prdt_basic_regi_list.php";

    var col = 9;

    //탭별 리스트 컬럼수
    if (el === "paper") {
        col = 9;
    } else if (el === "output") {
        col = 9;
    } else if (el === "size") {
        col = 9;
    } else if (el === "print") {
        col = 9;
    } else if (el === "tmpt") {
        col = 9;
    } else if (el === "after") {
        col = 9;
    } else if (el === "opt") {
        col = 9;
    }

    var blank = "<tr><td colspan=\"" + col + "\">검색 된 내용이 없습니다.</td></tr>";

    $.ajax({
        type: "POST",
        data: data,
        url: url,
        success: function(result) {

            hideMask();
            var rs = result.split("♪");

            if (rs[0] == "") {
                $("#" + el + "_list").html(blank);
            } else {
                $("#" + el + "_list").html(rs[0]);
            }

            $("#" + el + "_page").html(rs[1]);
        }   
    });
}

//대분류 선택시
var selectSortOption = function(el, val) {
    
    $("#" + el + "_search").val("");
    searchTxt = "";
    nowPage = 1;
    selectName = "";
    listOptionAjaxCall(el, val);
    selectSort = val;
    prdtBasicAjaxCall(el, searchTxt, showPage, nowPage, selectSort, selectName, "");
}

//이름 선택시
var selectNameOption = function(el, val) {

    $("#" + el + "_search").val("");
    searchTxt = "";
    nowPage = 1;
    selectName = val;
    prdtBasicAjaxCall(el, searchTxt, showPage, nowPage, selectSort, selectName, "");
}

//보여 주는 페이지 갯수 설정
var showPageSetting = function(el, val) {

    showPage = val;
    nowPage = 1;
    prdtBasicAjaxCall(el, searchTxt, showPage, nowPage, selectSort, selectName, "");
}

//페이지 이동
var movePage = function(val, el) {

    nowPage = val;
    prdtBasicAjaxCall(el, searchTxt, showPage, nowPage, selectSort, selectName, "");
}

//검색어 검색 엔터
var searchKey = function(event, val, el) {

    if (event.keyCode == 13) {
        nowPage = 1;
        searchTxt = val;
        prdtBasicAjaxCall(el, searchTxt, showPage, nowPage, selectSort, selectName, "");
    }
}

//검색어 검색 버튼
var searchText = function(el) {

    nowPage = 1;
    searchTxt = $("#" + el + "_search").val();
    prdtBasicAjaxCall(el, searchTxt, showPage, nowPage, selectSort, selectName, "");
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

    prdtBasicAjaxCall(selectEl, searchTxt, showPage, nowPage, selectSort, selectName, sorting);
}

//카테고리 대분류 선택시
var cateSelect = function(cateSortcode) {
 
    var url = "/ajax/common/load_cate_list.php";
 
    if (checkBlank(cateSortcode)) {
        var url = "/ajax/common/load_cate_mid.php";
    }

    var data = {
        "cate_sortcode" : cateSortcode,
        "cate_type"     : "mid"
    };
    var callback = function(result) {
        $("#pop_print_cate_mid").html(result);
        showBgMask();
    }
    //ajax call 함수
    ajaxCall(url, "html", data, callback);
}

//출력이름 변경 시
var changeOutputName = function(val) {

    var url = "/ajax/basic_mng/prdt_basic_regi/load_output_board.php";
    var data = {
        "output_name" : val
    };
    var callback = function(result) {
        $("#pop_size_output_board_dvs").html(result);
        showBgMask();
    }
    //ajax call 함수
    ajaxCall(url, "html", data, callback);
}

//인쇄명 변경 시
var changePrintName = function(val) {

    var url = "/ajax/basic_mng/prdt_basic_regi/load_purp_dvs.php";
    var data = {
        "print_name" : val
    };
    var callback = function(result) {
        $("#pop_tmpt_purp_dvs").html(result);
        showBgMask();
    }
    //ajax call 함수
    ajaxCall(url, "html", data, callback);
}

//등록창 오픈
var showRegiPopup = function(el, seqno) {
 
    showMask();
    var url = "/ajax/basic_mng/prdt_basic_regi/load_regi_popup.php";
    var data = {
    	"selectEl"   : el,
        "seqno"      : seqno
    };

    var el_width = "";

    if (el == "paper") {
        el_width = 560;
    } else if (el == "output") {
        el_width = 560;
    } else if (el == "size") {
        el_width = 560;
    } else if (el == "print") {
        el_width = 650;
    } else if (el == "tmpt") {
        el_width = 600;
    } else if (el == "after") {
        el_width = 750;
    } else if (el == "opt") {
        el_width = 750;
    }

    $.ajax({
        type: "POST",
        data: data,
        url: url,
        success: function(result) {
            var tmp = result.split("♪");

            hideMask();
            openRegiPopup(tmp[0], el_width);
  
            //일련번호가 없을 경우
            if (seqno != "") {
                //종이
                if (el == "paper") {
                    $("#pop_paper_sort").val(tmp[1]);
                    $("#pop_paper_basisweight_unit").val(tmp[2]);
                    $("#pop_paper_affil").val(tmp[3]);
                    $("#pop_paper_crtr_unit").val(tmp[4]);
                //출력정보
                } else if (el == "output") {
                    $("#pop_output_board_dvs").val(tmp[1]);
                    $("#pop_output_affil").val(tmp[2]);
                //사이즈
                } else if (el == "size") {
                    $("#pop_size_sort").val(tmp[1]);
                    $("#pop_size_output_name").val(tmp[2]);
                    $("#pop_size_output_board_dvs").val(tmp[3]);
                    $("#pop_size_affil").val(tmp[4]);
                //인쇄정보
                } else if (el == "print") {
                    $("#pop_print_cate_top").val(tmp[1]);
                    $("#pop_print_cate_mid").val(tmp[2]);
                    $("#pop_print_affil").val(tmp[3]);
                    $("#pop_print_crtr_unit").val(tmp[4]);
                //인쇄도수
                } else if (el == "tmpt") {
                    $("#pop_tmpt_sort").val(tmp[1]);
                    $("#pop_tmpt_print_name").val(tmp[2]);
                    $("#pop_tmpt_purp_dvs").val(tmp[3]);
                    $("#pop_tmpt_side_dvs").val(tmp[4]);
                //후공정
                } else if (el == "after") {
                    $("#pop_after_name").val(tmp[1]);
                    $("#pop_after_crtr_unit").val(tmp[2]);
                //옵션
                } else if (el == "opt") {
                     $("#pop_opt_name").val(tmp[1]);
                }
            }
        },
        error: getAjaxError 
    });
}

/**
 * @brief 상품기초데이터 추가
 *
 * @param el = 항목
 * @param seqno = 일련번호
 * @param flag = 항목추가에만 사용, 팝업 유지용
 */
var insertInfo = function(el, seqno, flag) {
 
    showMask();
    var url = "/proc/basic_mng/prdt_basic_regi/regi_prdt_basic_regi_list.php";
    var data = {
    	"selectEl"   : el,
        "seqno"      : seqno
    };

    //종이
    if (el == "paper") {

        var paper = new Array();
        paper[0] = "sort";
        paper[1] = "name";
        paper[2] = "dvs";
        paper[3] = "color";
        paper[4] = "basisweight";
        paper[5] = "basisweight_unit";
        paper[6] = "affil";
        paper[7] = "wid_size";
        paper[8] = "vert_size";
        paper[9] = "crtr_unit";

        var paper_alert = new Array();
        paper_alert[0] = "종이 대분류가 선택되지 않았습니다.";
        paper_alert[1] = "종이명이 입력되지 않았습니다.";
        paper_alert[2] = "구분이 입력되지 않았습니다.";
        paper_alert[3] = "색상이 입력되지 않았습니다.";
        paper_alert[4] = "평량이 입력되지 않았습니다.";
        paper_alert[5] = "평량 단위가 선택되지 않았습니다.";
        paper_alert[6] = "계열이 선택되지 않았습니다.";
        paper_alert[7] = "가로사이즈가 입력되지 않았습니다.";
        paper_alert[8] = "세로사이즈가 입력되지 않았습니다.";
        paper_alert[8] = "기준단위가 선택되지 않았습니다.";

	//validation
        for (var i = 0; i < paper.length; i++) {
            if ($.trim($("#pop_paper_" + paper[i]).val()) == "") {
                $("#pop_paper_" + paper[i]).focus();
                hideMask();
                showBgMask();
                alert(paper_alert[i]);
                return false;
            }
        }

        data.sort = $.trim($("#pop_paper_sort").val());
        data.name = $.trim($("#pop_paper_name").val());
        data.dvs = $.trim($("#pop_paper_dvs").val());
        data.color = $.trim($("#pop_paper_color").val());
        data.basisweight = $.trim($("#pop_paper_basisweight").val());
        data.basisweight_unit = $.trim($("#pop_paper_basisweight_unit").val());
        data.affil = $.trim($("#pop_paper_affil").val());
        data.wid_size = $.trim($("#pop_paper_wid_size").val());
        data.vert_size = $.trim($("#pop_paper_vert_size").val());
        data.crtr_unit = $.trim($("#pop_paper_crtr_unit").val());

    //출력정보
    } else if (el == "output") {

        var output = new Array();
        output[0] = "name";
        output[1] = "board_dvs";
        output[2] = "affil";
        output[3] = "wid_size";
        output[4] = "vert_size";

        var output_alert = new Array();
        output_alert[0] = "출력명이 입력되지 않았습니다.";
        output_alert[1] = "출력판이 선택되지 않았습니다.";
        output_alert[2] = "계열이 선택되지 않았습니다.";
        output_alert[3] = "가로사이즈가 입력되지 않았습니다.";
        output_alert[4] = "세로사이즈가 입력되지 않았습니다.";

	//validation
        for (var i = 0; i < output.length; i++) {
            if ($.trim($("#pop_output_" + output[i]).val()) == "") {
                $("#pop_output_" + output[i]).focus();
                hideMask();
                showBgMask();
                alert(output_alert[i]);
                return false;
            }
        }

        data.output_name = $.trim($("#pop_output_name").val());
        data.output_board_dvs = $.trim($("#pop_output_board_dvs").val());
        data.affil = $.trim($("#pop_output_affil").val());
        data.wid_size = $.trim($("#pop_output_wid_size").val());
        data.vert_size = $.trim($("#pop_output_vert_size").val());

    //사이즈
    } else if (el == "size") {
 
        var size = new Array();
        size[0] = "sort";
        size[1] = "name";
        size[2] = "typ";
        //size[3] = "affil";
        size[4] = "output_name";
        size[5] = "output_board_dvs";
        size[6] = "work_wid_size";
        size[7] = "work_vert_size";
        size[8] = "cut_wid_size";
        size[9] = "cut_vert_size";
        size[10] = "design_wid_size";
        size[11] = "design_vert_size";
        size[12] = "tomson_wid_size";
        size[13] = "tomson_vert_size";

        var size_alert = new Array();
        size_alert[0] = "사이즈 대분류가 선택되지 않았습니다.";
        size_alert[1] = "사이즈명이 입력되지 않았습니다.";
        size_alert[2] = "사이즈유형이 입력되지 않았습니다.";
        //size_alert[3] = "계열이 선택되지 않았습니다.";
        size_alert[4] = "출력명이 선택되지 않았습니다.";
        size_alert[5] = "출력판구분이 선택되지 않았습니다.";
        size_alert[6] = "작업가로사이즈가  입력되지 않았습니다.";
        size_alert[7] = "작업세로사이즈가 입력되지 않았습니다.";
        size_alert[8] = "재단가로사이즈가 입력되지 않았습니다.";
        size_alert[9] = "재단세로사이즈가 입력되지 않았습니다.";
        size_alert[10] = "디자인가로사이즈가 입력되지 않았습니다.";
        size_alert[11] = "디자인세로사이즈가 입력되지 않았습니다.";
        size_alert[12] = "도무송가로사이즈가 입력되지 않았습니다.";
        size_alert[13] = "도무송세로사이즈가 입력되지 않았습니다.";

	//validation
        for (var i = 0; i < size.length; i++) {
            if (checkBlank(size[i])) {
                continue;
            }

            if ($.trim($("#pop_size_" + size[i]).val()) == "") {
                $("#pop_size_" + size[i]).focus();
                hideMask();
                showBgMask();
                alert(size_alert[i]);
                return false;
            }
        }

        data.sort = $.trim($("#pop_size_sort").val());
        data.name = $.trim($("#pop_size_name").val());
        data.typ = $.trim($("#pop_size_typ").val());
        data.affil = $.trim($("#pop_size_affil").val());
        data.output_name = $.trim($("#pop_size_output_name").val());
        data.output_board_dvs = $.trim($("#pop_size_output_board_dvs").val());
        data.work_wid_size = $.trim($("#pop_size_work_wid_size").val());
        data.work_vert_size = $.trim($("#pop_size_work_vert_size").val());
        data.cut_wid_size = $.trim($("#pop_size_cut_wid_size").val());
        data.cut_vert_size = $.trim($("#pop_size_cut_vert_size").val());
        data.design_wid_size = $.trim($("#pop_size_design_wid_size").val());
        data.design_vert_size = $.trim($("#pop_size_design_vert_size").val());
        data.tomson_wid_size = $.trim($("#pop_size_tomson_wid_size").val());
        data.tomson_vert_size = $.trim($("#pop_size_tomson_vert_size").val());

    //인쇄정보
    } else if (el == "print") {

        var print = new Array();
        print[0] = "name";
        print[1] = "purp_dvs";
        print[2] = "crtr_unit";
        print[3] = "cate_top";

        var print_alert = new Array();
        print_alert[0] = "인쇄명이 입력되지 않았습니다.";
        print_alert[1] = "용도구분이 입력되지 않았습니다.";
        print_alert[2] = "기준단위가 선택되지 않았습니다.";
        print_alert[3] = "카테고리대분류가 선택되지 않았습니다.";

	//validation
        for (var i = 0; i < print.length; i++) {
            if ($.trim($("#pop_print_" + print[i]).val()) == "") {
                $("#pop_print_" + print[i]).focus();
                hideMask();
                showBgMask();
                alert(print_alert[i]);
                return false;
            }
        }

        var cate_sortcode = "";

        if ($.trim($("#pop_print_cate_mid").val()) == "") {
            data.cate_sortcode = $.trim($("#pop_print_cate_top").val());
        } else {
            data.cate_sortcode = $.trim($("#pop_print_cate_mid").val());
        }

        data.print_name = $.trim($("#pop_print_name").val());
        data.purp_dvs = $.trim($("#pop_print_purp_dvs").val());
        data.affil = $.trim($("#pop_print_affil").val());
        data.crtr_unit = $.trim($("#pop_print_crtr_unit").val());

    //인쇄도수
    } else if (el == "tmpt") {
 
        var tmpt = new Array();
        tmpt[0] = "sort";
        tmpt[1] = "name";
        tmpt[2] = "print_name";
        tmpt[3] = "purp_dvs";
        tmpt[4] = "side_dvs";
        tmpt[5] = "beforeside_tmpt";
        tmpt[6] = "aftside_tmpt";
        tmpt[7] = "add_tmpt";
        tmpt[8] = "tot_tmpt";
        tmpt[9] = "output_board_amt";

        var tmpt_alert = new Array();
        tmpt_alert[0] = "인쇄도수대분류가 선택되지 않았습니다.";
        tmpt_alert[1] = "인쇄도수명이 입력되지 않았습니다.";
        tmpt_alert[2] = "인쇄명이 선택되지 않았습니다.";
        tmpt_alert[3] = "용도구분이 선택되지 않았습니다.";
        tmpt_alert[4] = "면구분이 선택되지 않았습니다.";
        tmpt_alert[5] = "앞면인쇄도수가 입력되지 않았습니다.";
        tmpt_alert[6] = "뒷면인쇄도수가 입력되지 않았습니다.";
        tmpt_alert[7] = "추가인쇄도수가 입력되지 않았습니다.";
        tmpt_alert[8] = "총인쇄도수가 입력되지 않았습니다.";
        tmpt_alert[9] = "총판수가 입력되지 않았습니다.";

	    //validation
        for (var i = 0; i < tmpt.length; i++) {
            if ($.trim($("#pop_tmpt_" + tmpt[i]).val()) == "") {
                $("#pop_tmpt_" + tmpt[i]).focus();
                hideMask();
                showBgMask();
                alert(tmpt_alert[i]);
                return false;
            }
        }

	var beforeside_tmpt = $.trim($("#pop_tmpt_beforeside_tmpt").val());
	var aftside_tmpt = $.trim($("#pop_tmpt_aftside_tmpt").val());
	var add_tmpt = $.trim($("#pop_tmpt_add_tmpt").val());
	var new_tot_tmpt = Number(beforeside_tmpt) + Number(aftside_tmpt) + Number(add_tmpt);
	var tot_tmpt = $.trim($("#pop_tmpt_tot_tmpt").val());

	//총도수 유효성검사
	if (new_tot_tmpt != tot_tmpt) {
            hideMask();
            showBgMask();
	    $("#pop_tmpt_tot_tmpt").focus();
            alert("총도수값이 올바르지 않습니다.");
            return false;
	}

        data.sort = $.trim($("#pop_tmpt_sort").val());
        data.name = $.trim($("#pop_tmpt_name").val());
        data.print_name = $.trim($("#pop_tmpt_print_name").val());
        data.purp_dvs = $.trim($("#pop_tmpt_purp_dvs").val());
        data.side_dvs = $.trim($("#pop_tmpt_side_dvs").val());
        data.beforeside_tmpt = beforeside_tmpt;
        data.aftside_tmpt = aftside_tmpt;
        data.add_tmpt = add_tmpt;
        data.tot_tmpt = tot_tmpt;
        data.output_board_amt = $.trim($("#pop_tmpt_output_board_amt").val());

    } else if (el == "after") {
 
        var after = new Array();
        after[0] = "name";
        after[1] = "depth1";
        after[2] = "depth2";
        after[3] = "depth3";

        var after_alert = new Array();
        after_alert[0] = "후공정명이 선택되지 않았습니다.";
        after_alert[1] = "Depth1이 입력되지 않았습니다.";
        after_alert[2] = "Depth2이 입력되지 않았습니다.";
        after_alert[3] = "Depth3이 입력되지 않았습니다.";

	//validation
        for (var i = 0; i < after.length; i++) {
            if ($.trim($("#pop_after_" + after[i]).val()) == "") {
                $("#pop_after_" + after[i]).focus();
                hideMask();
                showBgMask();
                alert(after_alert[i]);
                return false;
            }
        }

        data.after_name = $.trim($("#pop_after_name").val());
        data.depth1 = $.trim($("#pop_after_depth1").val());
        data.depth2 = $.trim($("#pop_after_depth2").val());
        data.depth3 = $.trim($("#pop_after_depth3").val());

    } else if (el == "opt") {
 
        var opt = new Array();
        opt[0] = "name";
        opt[1] = "depth1";
        opt[2] = "depth2";
        opt[3] = "depth3";

        var opt_alert = new Array();
        opt_alert[0] = "옵션명이 선택되지 않았습니다.";
        opt_alert[1] = "Depth1이 입력되지 않았습니다.";
        opt_alert[2] = "Depth2이 입력되지 않았습니다.";
        opt_alert[3] = "Depth3이 입력되지 않았습니다.";

	//validation
        for (var i = 0; i < opt.length; i++) {
            if ($.trim($("#pop_opt_" + opt[i]).val()) == "") {
                $("#pop_opt_" + opt[i]).focus();
                hideMask();
                showBgMask();
                alert(opt_alert[i]);
                return false;
            }
        }

        data.opt_name = $.trim($("#pop_opt_name").val());
        data.depth1 = $.trim($("#pop_opt_depth1").val());
        data.depth2 = $.trim($("#pop_opt_depth2").val());
        data.depth3 = $.trim($("#pop_opt_depth3").val());

    } else if (el == "ao_after") {
 
        var after = new Array();
        after[0] = "name";
        after[1] = "depth1";
        after[2] = "depth2";
        after[3] = "depth3";
        after[4] = "unitprice";

        var after_alert = new Array();
        after_alert[0] = "후공정명이 입력되지 않았습니다.";
        after_alert[1] = "Depth1이 입력되지 않았습니다.";
        after_alert[2] = "Depth2이 입력되지 않았습니다.";
        after_alert[3] = "Depth3이 입력되지 않았습니다.";
        after_alert[4] = "단가가 입력되지 않았습니다.";

	//validation
        for (var i = 0; i < after.length; i++) {
            if ($.trim($("#pop_ao_after_" + after[i]).val()) == "") {
                $("#pop_ao_after_" + after[i]).focus();
                hideMask();
                showBgMask();
                alert(after_alert[i]);
                return false;
            }
        }

        data.after_name = $.trim($("#pop_ao_after_name").val());
        data.depth1 = $.trim($("#pop_ao_after_depth1").val());
        data.depth2 = $.trim($("#pop_ao_after_depth2").val());
        data.depth3 = $.trim($("#pop_ao_after_depth3").val());
        data.unitprice = $.trim($("#pop_ao_after_unitprice").val());

    } else if (el == "ao_opt") {
 
        var opt = new Array();
        opt[0] = "name";
        opt[1] = "depth1";
        opt[2] = "depth2";
        opt[3] = "depth3";
        opt[3] = "unitprice";

        var opt_alert = new Array();
        opt_alert[0] = "옵션명이 입력되지 않았습니다.";
        opt_alert[1] = "Depth1이 입력되지 않았습니다.";
        opt_alert[2] = "Depth2이 입력되지 않았습니다.";
        opt_alert[3] = "Depth3이 입력되지 않았습니다.";
        opt_alert[3] = "단가가 입력되지 않았습니다.";

	//validation
        for (var i = 0; i < opt.length; i++) {
            if ($.trim($("#pop_ao_opt_" + opt[i]).val()) == "") {
                $("#pop_ao_opt_" + opt[i]).focus();
                hideMask();
                showBgMask();
                alert(opt_alert[i]);
                return false;
            }
        }

        data.opt_name = $.trim($("#pop_ao_opt_name").val());
        data.depth1 = $.trim($("#pop_ao_opt_depth1").val());
        data.depth2 = $.trim($("#pop_ao_opt_depth2").val());
        data.depth3 = $.trim($("#pop_ao_opt_depth3").val());
        data.unitprice = $.trim($("#pop_ao_opt_unitprice").val());
    }

    $.ajax({
        type: "POST",
        data: data,
        url: url,
        success: function(result) {
            if (checkBlank(flag)) {
                hideMask();
            }

            if (result == true) {
                init();
                listOptionAjaxCall(el, "");
                $("#" + el + "_search").val("");
                prdtBasicAjaxCall(el, searchTxt, showPage, nowPage, selectSort, selectName, "");
                if (checkBlank(flag)) {
                    hideRegiPopup();
	        }

                if (seqno == "") {
                    alert("등록 되었습니다.");
                } else {
                    alert("수정 되었습니다.");
                }

            } else if(result == "over") {
                showBgMask();

                if (seqno == "") {
                    alert("중복 된 값이 있습니다.\n등록을 취소하시려면 닫기를 버튼을 눌러주세요.");
                } else {
                    if (el == "output") {
                        alert("이미 같은 출력명에 출력판이 중복 된 값이 있습니다.\n수정을 취소하시려면 닫기를 버튼을 눌러주세요.");
                    } else if(el == "print") {
                        alert("이미 같은 인쇄명에 용도구분이 중복 된 값이 있습니다.\n수정을 취소하시려면 닫기를 버튼을 눌러주세요.");
                    } else {
                        alert("중복 된 값이 있거나 수정 된 값이 없습니다.\n수정을 취소하시려면 닫기를 눌러주세요.");
                    }
                }
            } else if (result == false) {
                showBgMask();
                if (seqno == "") {
                    alert("등록을 실패하였습니다.");
                } else {
                    alert("수정을 실패하였습니다.");
                }
            }

            hideMask(); 
        },
        error: getAjaxError 
    });
}

//상품삭제
var deleteInfo = function(el, seqno) {

    var confirmMgs = "이 상품과 관련 된 데이터(가격, 구성아이템)가 전부 삭제 됩니다.\n삭제 하시겠습니까?";

    if (confirm(confirmMgs) == false) {
        return false;
    }
 
    showMask();
    var url = "/proc/basic_mng/prdt_basic_regi/del_prdt_basic_regi_list.php";
    var data = {
    	"selectEl"   : el,
        "seqno"      : seqno
    };

    $.ajax({
        type: "POST",
        data: data,
        url: url,
        success: function(result) {
	
            hideMask();
            if (result == 1) {
                init();
                $("#" + el + "_search").val("");
                prdtBasicAjaxCall(el, searchTxt, showPage, nowPage, selectSort, selectName, "");
                listOptionAjaxCall(el, "");

		if (el == "output") {
	            $("#size_search").val("");
                    prdtBasicAjaxCall("size", searchTxt, showPage, nowPage, selectSort, selectName, "");
                    listOptionAjaxCall("size", "");
		}

		if (el == "print") {
	            $("#tmpt_search").val("");
                    prdtBasicAjaxCall("tmpt", searchTxt, showPage, nowPage, selectSort, selectName, "");
                    listOptionAjaxCall("tmpt", "");
		}

                hideRegiPopup();
                alert("삭제 되었습니다.");
            } else {
                showBgMask();
                alert("삭제를 실패하였습니다.");
            }
        },
        error: getAjaxError 
    });
}
