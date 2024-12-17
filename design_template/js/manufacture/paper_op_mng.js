$(document).ready(function() {
    //dateSet('0');
    $(".datepicker_input").datepicker({
        format         : "yyyy-mm-dd",
        autoclose      : true,
        todayBtn       : "linked",
        todayHighlight : true,
        language       : "kr"
    }).datepicker("setDate", '0').attr('readonly', 'readonly');

    $("#cancel_modify").css('display','none');
    cndSearch.exec(30, 1);
    cndSearch.info("");                    
});

//보여줄 페이지 수
var showPage = 30;

/**
 * @brief 선택조건으로 검색 클릭시
 */
var cndSearch = {
    //종이 발주 리스트
    "exec"       : function(showPage, page) {
        var url = "/ajax/manufacture/paper_op_mng/load_paper_op_mng_list.php";
        var blank = "<tr><td colspan=\"13\">검색 된 내용이 없습니다.</td></tr>";
        var data = {
    	    "date_cnd"    : $("#date_cnd").val(),
    	    "date_from"   : $("#basic_from").val(),
    	    "date_to"     : $("#basic_to").val(),
    	    "time_from"   : $("#time_from").val(),
    	    "time_to"     : $("#time_to").val(),
    	    "state"       : $("#op_state").val(),
    	    "cnd_val"     : $("#cnd_val").val(),
    	    "search_val"  : $("#search_val").val()
        };

        var callback = function(result) {

            var rs = result.split("♪");
            if (rs[0].trim() == "") {
                $("#list").html(blank);
                return false;
            }
            $("#list").html(rs[0]);
            $("#page").html(rs[1]);
	        $("#allCheck").prop("checked", false);
        };

        data.showPage      = showPage;
        data.page          = page;

        showMask();
        ajaxCall(url, "html", data, callback);
    },
    //등록업체 리스트
    "info"       : function(sorting) {
        var tmp = sorting.split('/');
        var url = "/ajax/manufacture/paper_op_mng/load_paper_info_list.php";
        var blank = "<tr><td colspan=\"6\">검색 된 내용이 없습니다.</td></tr>";
        var data = {
            "sorting"           : tmp[0],
            "sorting_type"      : tmp[1],
            "extnl_etprs_seqno" : $("#paper_extnl_etprs_seqno").val(),
    	    "name"              : $("#name").val(),
    	    "dvs"               : $("#dvs").val(),
    	    "color"             : $("#color").val(),
    	    "basisweight"       : $("#basisweight").val()
        };
        var callback = function(result) {
            
            if (result.trim() == "") {
                $("#paper_info_list").html(blank);
                return false;
            }

            $("#paper_info_list").html(result);
        };

        showMask();
        ajaxCall(url, "html", data, callback);
    },
    //발주서
    "ord"       : function() {
        var url = "/ajax/manufacture/paper_op_mng/load_paper_ord_list.php";
        var blank = "<tr><td colspan=\"7\">검색 된 내용이 없습니다.</td></tr>";
        var data = {
    	    "date_from"   : $("#date_from").val(),
    	    "date_to"     : $("#date_to").val(),
    	    "time_from"   : $("#time_from").val(),
    	    "time_to"     : $("#time_to").val(),
    	    "state"       : $("#op_state").val(),
	};
        var callback = function(result) {
            $("paper_op_print").html(result);   
        };

        showMask();
        ajaxCall(url, "html", data, callback);
    }
};

//검색어 검색 엔터
var searchKey = function(event, val) {

    if (event.keyCode == 13) {
        cndSearch.info("");
    }
}

//보여줄 페이지 수 설정
var showPageSetting = function(val, dvs) {
    showPage = val;
    cndSearch.exec(val, 1);
}

//페이지 이동
var movePage = function(val) {
    cndSearch.exec(showPage, val);
}

/**
* @brief 검색 조건 변경
*/
var changeSearchCnd = function(val) {

    $("#search_cnd").val(val);
    $("#search_val").val("");
    $("#search_txt").val("");
        
    $("#search_val").attr("placeholder", "검색창 팝업 사용");
}

/**
 * @brief 조건(발주번호, 수주처, 입고처) 검색할 때 버튼 클릭하여 검색
 *
 * @param event = 키 이벤트
 * @param val   = 입력값
 * @param dvs   = 팝업 출력인지 재검색인지 구분값
 */
var searchCndP = function(event, val, dvs) {

    var url = "/ajax/common/load_cnd_search_paper.php";

    var data = {
   	    "search_cnd"  : $("#cnd_val").val(),
   	    "search_txt"  : val,
    };

    if (val.length < 2) {
        alert("두글자 이상 입력하세요.");
	    return false;
    }

    var callback = function(result) {

        if (dvs !== "select") {
            searchPopShow(event, "searchCndPEnt", "searchCndP");
        } else {
            showBgMask();
        }
        $("#search_cnd").val($("#cnd_val").val());
        $("#search_list").html(result);
    };

    ajaxCall(url, "html", data, callback);
}

/**
 * @brief 조건(발주번호, 수주처, 입고처) 검색할 때 Enter를 사용하여 검색
 *
 * @param event = 키 이벤트
 * @param val   = 입력값
 * @param dvs   = 팝업 출력인지 재검색인지 구분값
 */
var searchCndPEnt = function(event, val, dvs) {
 
    if (event.keyCode != 13){
        return false;
    }
    
    if (val.length < 2) {
        alert("두글자 이상 입력하세요.");
	    return false;
    }

    this.searchCndP(event, val, dvs);
}

//선택조건으로 검색
var searchOpList = function() {
    cndSearch.exec(showPage, 1);
}

//제조사 변경시 등록업체 리스트 재호출
var changeManuListCall = function() {
    cndSearch.info("");
}

/**
 * @brief 등록업체리스트 정렬
 */
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
    var tabVal = "";

    cndSearch.info(sorting);
}

/**
 * @brief 발주유형 변경 
 */
var selectOpTyp = function(val, el) {

    if (val == "자동발주") {
        $("#" + el + "_typ_detail").val("자동생성");
        $("#" + el + "_typ_detail").attr("disabled", true);
    } else  {
        $("#" + el + "_typ_detail").val("");
        $("#" + el + "_typ_detail").attr("disabled", false);
    }
}

/**
 * @brief 종이 form 초기화 
 */
var getPaperOpViewInit = function() {
    $("#paper_op_wid_size").val("");
    $("#paper_op_vert_size").val("");
    $("#paper_stor_wid_size").val("");
    $("#paper_stor_vert_size").val("");
    $("#paper_name").val("");
    $("#paper_dvs").val("");
    $("#paper_color").val("");
    $("#paper_basisweight").val("");
    $("#paper_affil").val("");
    $("#paper_subpaper").val("");
    $("input:radio[name=paper_grain]").eq(0).prop("checked", true);
    $("#paper_amt").val("");
    $("#paper_amt_unit option").eq(0).prop("selected", true);
    $("#paper_memo").val("");
    $("#paper_typ option").eq(0).prop("selected", true);
    $("#paper_typ_detail").val("");
    $("#paper_brand_seqno").val("");
    $("#paper_op_seqno").val("");
    $("#paper_manu_name").val("");
}

//종이발주 상세보기
var paperOpView = function(seqno) {

    var url = "/ajax/manufacture/paper_op_mng/load_paper_op_detail.php";
    var data = { 
        "paper_op_seqno" : seqno 
    };

    var callback = function(result) {
        var rs = result.split("♪");
        $("#paper_name").val(rs[0]);
        $("#paper_dvs").val(rs[1]);
        $("#paper_color").val(rs[2]);
        $("#paper_basisweight").val(rs[3] + rs[4]);
        $("#paper_manu_name").val(rs[5]);
        $("#paper_affil").val(rs[6]);
        $("#paper_op_wid_size").val(rs[7]);
        $("#paper_op_vert_size").val(rs[8]);
        $("#stor_place").val(rs[9]);
        $("#paper_subpaper").val(rs[10]);
        $("#paper_stor_wid_size").val(rs[11]);
        $("#paper_stor_vert_size").val(rs[12]);
        $("#grain").val(rs[13]);
        $("#paper_amt").val(rs[14]);
        $("#paper_amt_unit").val(rs[15]);
        $("#paper_memo").val(rs[16]);
        $("#paper_brand_seqno").val(rs[17]);
        $("#paper_op_seqno").val(rs[18]);
        $("#typset_num").val(rs[19]);
        tabView("add");
	    $(".li1").removeClass("active");
	    $(".li2").addClass("active");

        $("#cancel_modify").css('display','');
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

var cancelPaperOp = function() {
    $("#paper_op_seqno").val("");
    $("#paper_name").val("");
    $("#paper_dvs").val("");
    $("#paper_color").val("")
    $("#paper_basisweight").val("")
    $("#paper_affil").val("")
    $("#paper_op_wid_size").val("");
    $("#paper_op_vert_size").val("");
    $("#storplace").val(""),
    $("#paper_subpaper").val(""),
    $("#paper_stor_wid_size").val("");
    $("#paper_stor_vert_size").val("");
    $("#paper_amt").val("");
    $("#paper_memo").val("");
    $("#brand_seqno").val("");
    $("#typset_num").val("");


    $("#cancel_modify").css('display','none');
}

/**
 * @brief 종이발주 취소 
 *
 * @param paper_op_seqno = 취소할 id값
 */
var paperOpCancel = function(paper_op_seqno) {
    
    var url = "/proc/manufacture/paper_op_mng/modi_paper_op_cancel.php";
    var data = { 
        "paper_op_seqno" : paper_op_seqno 
    };

    var callback = function(result) {
        if (result == 1) {
            alert("종이발주를 취소 하였습니다..");
            cndSearch.exec(showPage, 1);
        } else {
            alert("종이발주취소를 실패 하였습니다. \n 관리자에게 문의 바람니다.");
        }
    };

    ajaxCall(url, "html", data, callback);
}

/**
 * @brief 종이발주 
 */
var paperOp = function() {
   
    if (checkBlank(getselectedNo())) {
        alert("선택한 항목이 없습니다.");
        return false;
    }

    var url = "/proc/manufacture/paper_op_mng/regi_paper_op.php";
    var data = { 
        "paper_op_seqno" : getselectedNo()
    };

    var callback = function(result) {
        if (result == 1) {
            alert("종이를 발주 하였습니다. \n완료 되거나, 취소 된 발주는 발주가 되지 않습니다.");
            cndSearch.exec(showPage, 1);
        } else {
            alert("종이발주를 실패 하였습니다. \n관리자에게 문의 해주세요.");
        }
    };

    ajaxCall(url, "html", data, callback);
}

var tabInit = function() {

    $("#paper_op_list").hide();
    $("#paper_op_add").hide();
}

var tabView = function(dvs) {

    tabInit();
    $("#paper_op_" + dvs).show();
}

// 종이명 선택시 셀렉트박스 설정
var selectPaperName = function() {

    if ($("#name").val() == "") {
        return false;
    }

    var url = "/ajax/manufacture/paper_stock_mng/load_paper_name.php";
    var data = { 
        "name" : $("#name").val() 
    };
    var callback = function(result) {
        var arr = result.split("♪");
        $("#dvs").html(arr[0]);
        $("#color").html(arr[1]);
        $("#basisweigth").html(arr[2]);
        
        $("#dvs").removeAttr("disabled");
        $("#color").removeAttr("disabled");
        $("#basisweight").removeAttr("disabled");
        cndSearch.info("");                    
    };

    ajaxCall(url, "html", data, callback);
}

// 종이구분 선택시 구분 셀렉트박스 설정
var selectPaperDvs = function() {

    if ($("#dvs").val() == "") {
        return false;
    }

    var url = "/ajax/manufacture/paper_stock_mng/load_paper_dvs.php";
    var data = { 
          "name" : $("#name").val(),
          "dvs"  : $("#dvs").val() 
    };

    var callback = function(result) {
        var arr = result.split("♪");
        $("#color").html(arr[0]);
        $("#basisweigth").html(arr[1]);
        cndSearch.info("");                    
    };

    ajaxCall(url, "html", data, callback);
}

// 종이색상 선택시 구분 셀렉트박스 설정
var selectPaperColor = function() {

    if ($("#name").val() == "") {
        return false;
    }

    var url = "/ajax/manufacture/paper_stock_mng/load_paper_color.php";
    var data = { 
        "name"   : $("#name").val(), 
        "dvs"    : $("#dvs").val(), 
        "color"  : $("#color").val() 
    };

    var callback = function(result) {
        $("#basisweight").html(result);
        cndSearch.info("");                    
    };

    ajaxCall(url, "html", data, callback);
}
