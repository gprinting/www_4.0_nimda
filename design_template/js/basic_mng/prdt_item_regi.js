/*
 *
 * Copyright (c) 2015-2017 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2015/11/30 임종건 생성
 * 2015/12/01 임종건 생성 종이 관련 함수 추가
 * 2015/12/02 임종건 생성 사이즈, 인쇄도수 관련 함수 추가
 * 2015/12/03 임종건 생성 후공정, 옵션 관련 함수 추가
 * 2016/09/02 엄준현 수정(페이지 이동시 스크롤 위로 가도록 수정)
 * 2016/09/13 엄준현 수정(인쇄도수에 계열, 용도구분 추가)
 * 2017/01/03 엄준현 수정(불필요한 로직 제거, 후공정 수정)
 * 2017/07/11 엄준현 추가(카테고리 종이 별칭수정 추가)
 *=============================================================================
 *
 */

//현재 탭
var selectTab = "paper";

//상품검색어
var prSearchTxt = "";
//상품현재 페이지
var prNowPage = "";
//상품리스트 갯수
var prShowPage  = "";
//상품선택한 대분류
var prSelectSort = "";
//상품선택한 이름
var prSelectName = "";

//상품구성아이템카테고리분류코드
var cateSortCode = "";
//상품구성아이템검색어
var caSearchTxt = "";
//상품구성아이템현재 페이지
var caNowPage = "";
//상품구성아이템리스트 갯수
var caShowPage  = "";
//상품구성아이템선택한 대분류
var caSelectSort = "";
//상품구성아이템선택한 이름
var caSelectName = "";

$(document).ready(function() {
    tabCtrl("paper");
});

//전체 선택
var allCheck = function(el) {

    //만약 전체선택 체크박스가 체크 된 상태일 경우
    if ($("#" + el + "_allCheck").prop("checked")) {
        $("#" + el +  "_list input[type=checkbox]").prop("checked", true);
    } else {
        $("#" + el +  "_list input[type=checkbox]").prop("checked", false);
    }
}

//체크박스 선택시 value값 가져오는 함수
var getselectedNo = function(el) {

    var selectedValue = ""; 
    
    $("#" + el + "_list input[name=" + el + "_chk]:checked").each(function() {
        selectedValue += ","+ $(this).val();		    
    });

    if (selectedValue != "") {
        selectedValue = selectedValue.substring(1);
    }

    return selectedValue;
}

var init = function() {
    
    $("select[name=list_set]").val("30");
    $(".check_box").prop("checked", false);
    sortInit();
}

//상품리스트 초기화
var prInit = function() {

    prSearchTxt = "";
    prNowPage = 1;
    prShowPage  = 30;
    prSelectSort = "";
    prSelectName = "";
}

//상품구성아이템리스트 초기화
var caInit = function() {

    caSearchTxt = "";
    caNowPage = 1;
    caShowPage  = 30;
    caSelectSort = "";
    caSelectName = "";
}

//탭 선택시 제어 함수
var tabCtrl = function(el) {

    selectTab = el;
    init();
    prInit();
    caInit();
 
    $("#" + el + "_search").val("");
    $("#cate_" + el + "_search").val("");

    listOptionAjaxCall(el, "", false);
    prdtItemAjaxCall(el, prSearchTxt, prShowPage, prNowPage, prSelectSort, prSelectName, "");
    listOptionAjaxCall("cate_" + el, "", false);
    prdtItemAjaxCall("cate_" + el, caSearchTxt, caShowPage, caNowPage, caSelectSort, caSelectName, "");

    if (selectTab === "after") {
        checkCateSize();
    }
}

//상품리스트, 상품구성아이템리스트 조건 선택박스 호출
var listOptionAjaxCall = function(el, sort, flag) {

    showBgMask();
    showMask();
    var url = "/ajax/basic_mng/prdt_item_regi/load_list_option.php";
    var data = {
    	"selectEl"   : el,
        "sort"       : sort,
        "cate_sortcode" : cateSortCode
    };
 
    var elSort = "";
    var elName = "";

    if (el === "cate_paper") {
        elSort = "종이대분류(전체)";
        elName = "종이명(전체)";
    } else if (el === "cate_size") {
        elSort = "사이즈대분류(전체)";
        elName = "사이즈명(전체)";
    } else if (el === "cate_tmpt") {
        elSort = "인쇄도수대분류(전체)";
        elName = "인쇄도수명(전체)";
    } else if (el === "cate_after") {
        elSort = "후공정분류(전체)";
        elName = "후공정명(전체)";
    } else if (el === "cate_opt") {
        elSort = "옵션분류(전체)";
        elName = "옵션명(전체)";
    }
 
    if (el === "cate_paper" ||
        el === "cate_size" ||
        el === "cate_tmpt" ||
        el === "cate_after" ||
        el === "cate_opt") {

        if (cateSortCode == "") {
            hideMask();
            $("#" + el + "_sort").html("<option>" + elSort + "</option>");
            $("#" + el + "_name").html("<option>" + elName + "</option>");
            $(".select_cate_name").html("[]");
            return false;
        }
    }

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

            if (flag == true) {
                if (el == "cate_after") {
	                $("#" + el + "_sort").val($(":input:radio[name='after_radio']:checked").val());
                } else if (el == "cate_opt") {
	                $("#" + el + "_sort").val($(":input:radio[name='opt_radio']:checked").val());
                } else {
	                $("#" + el + "_sort").val(prSelectSort);
                }
                $("#" + el + "_name").val(prSelectName);
	        }
        },
        error: getAjaxError 
    });
}

/*
 * 상품리스트, 상품구성아이템리스트 호출
 * el :선택 된 탭
 * txt : 검색한 단어
 * sPage : 페이지당 보여줄 리스트 줄수
 * page : 현재 페이지
 * sSort : 검색할 대분류
 * sName : 검색할 이름
 * sorting : 정렬
 */
var prdtItemAjaxCall = function(el, txt, sPage, page, sSort, sName, sorting) {
 
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
        "sorting_type"  : tmp[1],
        "cate_sortcode" : cateSortCode
    };
    var url = "/ajax/basic_mng/prdt_item_regi/prdt_item_regi_list.php";

    var col = 9;

    //탭별 리스트 컬럼수
    if (el === "paper" || el === "cate_paper") {
        col = 9;
    } else if (el === "size" || el === "cate_size") {
        col = 10;
    } else if (el === "tmpt" || el === "cate_tmpt") {
        col = 9;
    } else if (el === "after" || el === "cate_after") {
        col = 10;
    } else if (el === "opt" || el === "cate_opt") {
        col = 9;
    }

    var blank = "<tr><td colspan=\"" + col + "\">검색 된 내용이 없습니다.</td></tr>";

    if (el === "cate_paper" ||
        el === "cate_size" ||
        el === "cate_tmpt" ||
        el === "cate_after" ||
        el === "cate_opt") {

        if (cateSortCode == "") {
            hideMask();
            $("#" + el + "_list").html(blank);
            $("#" + el + "_page").html("");
            $(".select_cate_name").html("[]");
            return false;
        }
    }

    $.ajax({
        type: "POST",
        data: data,
        url: url,
        success: function(result) {
            // 상품 정보부분 페이지 이동시 스크롤이
	    // 줄어들어서 햇갈리는 것 때문에 추가
            if (el === "after") {
                $("#page-content").scrollTop(0)
            }

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
var selectSortOption = function(el, val, flag) {
    
    listOptionAjaxCall(el, val, false);
    $("#" + el + "_search").val("");

    //상품리스트
    if (flag === 1) {
        prSearchTxt = "";
        prNowPage = 1;
        prSelectName = "";
        prSelectSort = val;
        prdtItemAjaxCall(el, prSearchTxt, prShowPage, prNowPage, prSelectSort, prSelectName, "");
    //상품구성아이템리스트
    } else {
        caSearchTxt = "";
        caNowPage = 1;
        caSelectName = "";
        caSelectSort = val;
        prdtItemAjaxCall(el, caSearchTxt, caShowPage, caNowPage, caSelectSort, caSelectName, "");
    }
}

//이름 선택시
var selectNameOption = function(el, val, flag) {

    $("#" + el + "_search").val("");
 
    //상품리스트
    if (flag === 1) {
        prSearchTxt = "";
        prNowPage = 1;
        prSelectName = val;
        prdtItemAjaxCall(el, prSearchTxt, prShowPage, prNowPage, prSelectSort, prSelectName, "");
    //상품구성아이템리스트
    } else {
        caSearchTxt = "";
        caNowPage = 1;
        caSelectName = val;
        prdtItemAjaxCall(el, caSearchTxt, caShowPage, caNowPage, caSelectSort, caSelectName, "");
    }
}

//보여 주는 페이지 갯수 설정
var showPageSetting = function(el, val, flag) {

    //상품리스트
    if (flag === 1) {
        prShowPage = val;
        prNowPage = 1;
        prdtItemAjaxCall(el, prSearchTxt, prShowPage, prNowPage, prSelectSort, prSelectName, "");
    //상품구성아이템리스트
    } else {
        caShowPage = val;
        caNowPage = 1;
        prdtItemAjaxCall(el, caSearchTxt, caShowPage, caNowPage, caSelectSort, caSelectName, "");
    }
}

//상품리스트 페이지 이동
var movePage = function(val, el) {

    prNowPage = val;
    prdtItemAjaxCall(el, prSearchTxt, prShowPage, prNowPage, prSelectSort, prSelectName, "");
}

//상품구성아이템리스트 페이지 이동
var cateMovePage = function(val, el) {

    caNowPage = val;
    prdtItemAjaxCall(el, caSearchTxt, caShowPage, caNowPage, caSelectSort, caSelectName, "");
}

//검색어 검색 엔터
var searchKey = function(event, val, el, flag) {

    if (event.keyCode == 13) {

        //상품리스트
        if (flag === 1) {
            prSearchTxt = val;
            prdtItemAjaxCall(el, prSearchTxt, prShowPage, prNowPage, prSelectSort, prSelectName, "");
        //상품구성아이템리스트
        } else {
            caSearchTxt = val;
            prdtItemAjaxCall(el, caSearchTxt, caShowPage, caNowPage, caSelectSort, caSelectName, "");
        }
    }
}

//검색어 검색 버튼
var searchText = function(el, flag) {

    //상품리스트
    if (flag === 1) {
        prSearchTxt = $("#" + el + "_search").val();
        prdtItemAjaxCall(el, prSearchTxt, prShowPage, prNowPage, prSelectSort, prSelectName, "");
    //상품구성아이템리스트
    } else {
        caSearchTxt = $("#" + el + "_search").val();
        prdtItemAjaxCall(el, caSearchTxt, caShowPage, caNowPage, caSelectSort, caSelectName, "");
    }
}

//컬럼별 sorting
var sortList = function(val, el, selectEl, elFlag) {

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
 
    //상품리스트
    if (elFlag === 1) {
        prdtItemAjaxCall(selectEl, prSearchTxt, prShowPage, prNowPage, prSelectSort, prSelectName, sorting);
    //상품구성아이템리스트
    } else {
        prdtItemAjaxCall(selectEl, caSearchTxt, caShowPage, caNowPage, caSelectSort, caSelectName, sorting);
    }
}

//카테고리 소분류 선택시
var cateBotSelect = function(val) {

    $(".check_box").prop("checked", false);
    cateSortCode = val;
    listOptionAjaxCall("cate_" + selectTab, "", false);
    prdtItemAjaxCall("cate_" + selectTab, caSearchTxt, caShowPage, caNowPage, caSelectSort, caSelectName, "");

    if (selectTab === "after") {
        checkCateSize();
    }

    if (checkBlank(val)) {
        $(".select_cate_name").html("[]");
    }

    $(".select_cate_name").html("[" + $("#cate_bot > option:selected").text() + "]");
}

/**
 * @brief 카테고리 후공정 사이즈명 체크박스 체크
 */
var checkCateSize = function() {
    var cateSortcode = $("#cate_bot").val();
    if (checkBlank(cateSortcode)) {
        return false;
    }

    var url = "/ajax/basic_mng/prdt_item_regi/load_cate_size.php";
    var data = {
        "cate_sortcode" : cateSortcode
    };
    var callback = function(result) {
        $(".aft_size").each(function() {
            if (!checkBlank(result[$(this).val()])) {
                $(this).prop("checked", true);
            } else {
                $(this).prop("checked", false);
            }
        });
    };

    ajaxCall(url, "json", data, callback);
};

//카테고리 별 상품 등록
var regiCatePrdt = function(el) {

    if (cateSortCode == "") {
        alert("카테고리 소분류를 선택해주세요.");
        return false;
    }

    var val = "";
    val = getselectedNo(el);
    
    if (val == "") {
        alert("등록 하실 항목을 선택해주세요.");
        return false;
    }

    var data = {
        "cate_sortcode" : cateSortCode,
        "seqno"         : val,
        "selectEl"      : el,
    };

    if (el == "after" || el == "opt" || el == "ao_after" || el == "ao_opt") {
        data.basic_yn = $("input[name='" + el + "_radio']:checked").val();
    }
    if (el == "after" || el == "ao_after") {
        var aftSize     = '';

        $("input[name='aft_size[]']:checked").each(function() {
            aftSize += $(this).val() + '!';
        });

        data.size = aftSize;
        data.crtr_unit = $("#aft_crtr_unit").val();
    }

    showMask();
    var url = "/proc/basic_mng/prdt_item_regi/regi_prdt_item_list.php";

    $.ajax({
        type: "POST",
        data: data,
        url: url,
        success: function(result) {
	
            hideMask();

            if (result == true) {
                init();
                caInit();
                listOptionAjaxCall("cate_" + el, "", true);
                caSelectSort = prSelectSort;
                caSelectName = prSelectName;

                prdtItemAjaxCall("cate_" + el, caSearchTxt, caShowPage, caNowPage, caSelectSort, caSelectName, "");
                $(".check_box").prop("checked", false);
                alert("등록 되었습니다. 중복된 값은 등록 되지 않습니다.");
            } else {
                alert("등록을 실패하였습니다.");
            }
        },
        error: getAjaxError 
    });
}

//카테고리 별 상품 삭제
var delCatePrdt = function(el) {

    if (cateSortCode == "") {
        alert("카테고리 소분류를 선택해주세요.");
        return false;
    }

    var val = "";
    val = getselectedNo(el);
 
    if (val == "") {
        alert("삭제 하실 항목을 선택해주세요.");
        return false;
    }

    var confirmMgs = "이 상품과 관련 된 데이터(가격 정보)가 전부 삭제 됩니다.\n삭제 하시겠습니까?";

    if (confirm(confirmMgs) == false) {
        return false;
    }

    showMask();
    var url = "/proc/basic_mng/prdt_item_regi/del_prdt_item_list.php";
    var data = {
        "seqno"         : val,
        "table"         : el
    };
 
    $.ajax({
        type: "POST",
        data: data,
        url: url,
        success: function(result) {
	
            hideMask();

            if (result == 1) {
                init();
                caInit();
                listOptionAjaxCall(el, "", false);
                prdtItemAjaxCall(el, caSearchTxt, caShowPage, caNowPage, caSelectSort, caSelectName, "");
                $(".check_box").prop("checked", false);
                alert("삭제 되었습니다.");
            } else {
                alert("삭제를 실패하였습니다.");
            }
        },
        error: getAjaxError 
    });
};

/**
 * @brief 카테고리 종이 별칭 수정
 *
 * @param seqno = 카테고리 종이 일련번호
 */
var modiCatePaperNick = function(seqno) {
    var url = "/proc/basic_mng/prdt_item_regi/modi_cate_paper_nick.php";
    var data = {
        "seqno" : seqno,
        "nick"  : $("#cate_paper_nick_" + seqno).val()
    };
    var callback = function(result) {
        if (!checkBlank(result)) {
            return alertReturnFalse(result);
        }

        alert("별칭 등록에 성공했습니다.");
    };

    ajaxCall(url, "text", data, callback);
};

/**
 * @brief 카테고리 구성리스트에서 기본값 설정
 *
 * @param dvs   = 아이템 구분값
 * @param seqno = 아이템 일련번호
 * @param yn    = 기본여부
 */
var selectBasic = function(dvs, seqno, yn) {
    var url = "/proc/basic_mng/prdt_item_regi/modi_item_basic_yn.php";
    var data = {
        "dvs"           : dvs,
        "seqno"         : seqno,
        "yn"            : yn,
        "cate_sortcode" : cateSortCode
    };

    dvs = (dvs === "stan") ? "size" : dvs;
    dvs = (dvs === "print") ? "tmpt" : dvs;

    var callback = function(result) {
        if (!checkBlank(result)) {
            return alertReturnFalse(result);
        }

        prdtItemAjaxCall("cate_" + dvs,
                         caSearchTxt,
                         caShowPage,
                         caNowPage,
                         caSelectSort,
                         caSelectName,
                         "");
    };

    ajaxCall(url, "text", data, callback);
};
