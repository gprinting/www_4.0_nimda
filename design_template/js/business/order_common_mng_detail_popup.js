//현재 탭위치
var selectEl = "typset";
//낱장형-amt/책자형-page
var seqno = "";
//조판번호
var typsetNum = "";

//탭컨트롤
var tabCtrl = function(el) {
    selectEl = el;
    showTyp(seqno);
}

//주문상세
var OrderDetailInfo = function() {

    var url = "/ajax/business/order_common_mng_detail_popup/load_" + selectEl + "_info.php";
    var data = { 
        "typset_num" : typsetNum,
	    "flattyp_yn" : $("#flattyp_yn").val()
    };

    var callback = function(result) {
        $("#" + selectEl).html(result);
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//후공정 정보
var getAfterInfo = function(after_op_seqno, cate_name) {

    var url = "/ajax/business/order_common_mng_detail_popup/load_after_info.php";
    var data = { 
        "after_op_seqno" : after_op_seqno,
        "cate_name"      : cate_name,
        "flattyp_yn" : $("#flattyp_yn").val()
    };

    var callback = function(result) {
        $("#after_info").html(result);
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//낱장 - amt_order_detail_sheet_seqno
//책자 - page_order_detail_brochure_seqno
var showTyp = function (seq) {
    //후공정은 리스트를 먼저 보여줌
    seqno = seq;
    var url = "";
    if (selectEl === "after") {
        url = "/ajax/business/order_common_mng_detail_popup/load_after_list.php";
        var blank = "<tr><td colspan=\"12\">검색된 내용이 없습니다.</td></tr>";
    } else {
        url = "/ajax/business/order_common_mng_detail_popup/load_" + selectEl + "_info.php";
    }
    var data = { 
        "seqno" : seqno,
        "flattyp_yn" : $("#flattyp_yn").val()
    };

    var callback = function(result) {
        if (selectEl === "after") {
            if (checkBlank(result)) {
                $("#after_list").html(blank);
            } else {
                $("#after_list").html(result);
            }
        } else {
            $("#" + selectEl).html(result);
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}
