/*
 *
 * Copyright (c) 2015 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2016/06/02 왕초롱 생성
 *=============================================================================
 *
 */
$(document).ready(function() {
    tabCtrl("대기");
});

var list_page = "1";
var list_num = "30";
var public_state = "";
var selectTab = "";
var public_seqno = "";
var tab_name = "대기";
var complete_data = {};
var standby_data = {};
var cashreceipt_data = {};
var except_data = {};

var publicLayer = function(dvs,
			   public_seq,
			   public_date,
			   sell_site,
			   req_date,
			   pay_price,
			   card_price,
			   cash_price,
			   etc_price,
			   oa,
			   before_oa,
			   object_price,
			   name,
			   repre,
			   crn,
			   bc,
			   tob,
			   addr,
			   zipcode,
			   req_mon,
			   day,
			   unit_price,
			   supply_price,
			   vat,
			   public_dvs){

    public_seqno = public_seq;
    $("#new_member").hide();
    $("#new_save").hide();
    $("#except_save").show();
    $("#except_del").show();

    if (dvs == "basic") {
    	openRegiPopup($("#basic_popup").html(), 800);
	$("#" + dvs + "_pay_price").html(pay_price);
	$("#" + dvs + "_card_price").html(card_price);
	$("#" + dvs + "_cash_price").html(cash_price);
	$("#" + dvs + "_etc_price").html(etc_price);
	$("#" + dvs + "_object_price").html(object_price);
	$("#" + dvs + "_corp_name").html(name);
	$("#" + dvs + "_repre_name").html(repre);
	$("#" + dvs + "_crn").html(crn);
	$("#" + dvs + "_bc").html(bc);
	$("#" + dvs + "_tob").html(tob);
	$("#" + dvs + "_addr").html(addr);
	$("#" + dvs + "_zipcode").html(zipcode);
    } else {
    	openRegiPopup($("#edit_popup").html(), 800);
	$("#" + dvs + "_pay_price").val(pay_price);
	$("#" + dvs + "_card_price").val(card_price);
	$("#" + dvs + "_cash_price").val(cash_price);
	$("#" + dvs + "_etc_price").val(etc_price);
	$("#" + dvs + "_object_price").val(object_price);
	$("#" + dvs + "_corp_name").val(name);
	$("#" + dvs + "_repre_name").val(repre);
	$("#" + dvs + "_crn").val(crn);
	$("#" + dvs + "_bc").val(bc);
	$("#" + dvs + "_tob").val(tob);
	$("#" + dvs + "_addr").val(addr);
	$("#" + dvs + "_zipcode").val(zipcode);
	$("input[name=" + dvs + "_unitprice]").attr('value', unit_price);
	$("input[name=" + dvs + "_supply_price]").attr('value', unit_price);
	$("input[name=" + dvs + "_vat]").attr('value', vat);
	$("input[name=" + dvs + "_tot_price]").attr('value', object_price);

	$("input:radio[name='edit_public_dvs']:radio[value='" 
			+ public_dvs + "']").attr("checked",true);
    }

    $("#" + dvs + "_sell_site").val(sell_site);
    $("#" + dvs + "_req_date").html(req_date);
    $("#" + dvs + "_oa").val(oa);
    $("#" + dvs + "_before_oa").val(before_oa);
    $("#" + dvs + "_mon").html(req_mon);
    $("#" + dvs + "_day").html(day);
    $("#" + dvs + "_unitprice").html(unit_price);
    $("#" + dvs + "_supply_price").html(supply_price);
    $("#" + dvs + "_vat").html(vat);
    $("#" + dvs + "_sum_price").html(cash_price);
    $("#" + dvs + "_tot_price").html(cash_price);

    //datepicker 기본 셋팅
    $("#" + dvs + "_public_date").datepicker({
        autoclose:true,
        format: "yyyy-mm-dd",
        todayBtn: "linked",
        todayHighlight: true,
    });

    if (public_date) {
    	$("#" + dvs + "_public_date").val(public_date);
    } else {
    	$("#" + dvs + "_public_date").datepicker("setDate", new Date());
    }
}

//회원명 가져오기
var loadOfficeNick = function(event, val, dvs) {
    if (event.keyCode != 13) {
        return false;
    }

    if (val.length < 2) {
        alert("두글자 이상 입력하세요.");
	    return false;
    }

    var url = "/ajax/calcul_mng/sales_tab/load_office_nick.php";
    var data = {
        "sell_site"  : $("#sell_site").val(),
        "search_val" : val
    };
    var callback = function(result) {
        searchPopShow(event, "loadOfficeNick", "loadOfficeNick");
        $("#search_list").html(result);
    };

    ajaxCall(url, "html", data, callback);

}

//클릭시에 회원 발행 가능 금액 가져오기
var loadIssueClick = function(seqno, name) {

    var url = "/ajax/calcul_mng/sales_tab/load_member_issue_price.php";
    var data = {
        "member_seqno" : seqno,
       	"year"         : $("#year").val(),
    	"mon"          : $("#mon").val(),

    };
    var callback = function(result) {
        $("#office_nick").val(name);
        $("#able_price").val(result.trim());
        hideRegiPopup();
    };

    ajaxCall(url, "html", data, callback);

}

//매출계산서 대기 리스트가져오기
var loadPublicStandByList = {
    "exec"       : function(listSize, page) {
	if(listSize){
	    list_num = listSize;
	} 
	if(page) {
	   list_page = page;
	}

        var url = "/ajax/calcul_mng/sales_tab/load_public_standby_list.php";
        var blank = "<tr><td colspan=\"14\">검색 된 내용이 없습니다.</td></tr>";
        standby_data = {
       	    	"year"         : $("#year").val(),
    	    	"mon"          : $("#mon").val(),
    	    	"member_dvs"   : $("#member_dvs").val(),
                "member_seqno"   : $("#member_seqno").val(),
    	    	"sell_site"    : $("#sell_site option:selected").text(),
            "member_seqno"    : $("#member_seqno").val(),
            "dlvr_way"    : $("#dlvr_way option:selected").val(),
	    };

        var callback = function(result) {
            var rs = result.split("♪♭@");

            if (rs[0].trim() == "") {
                $("#standby_list").html(blank);
                $("#standby_member_cnt").html(rs[2]);
                //$("#standby_pay_price").html(rs[3] + "원");
                //$("#standby_pay_price").html("<button>보기</button>");
            	//$("#standby_total_price").html(rs[3] + "원");
                //$("#standby_card_price").html(rs[4] + "원");

                return false;
            }

            $("#standby_list").html(rs[0]);
            $("#standby_page").html(rs[1]);
            $("#standby_member_cnt").html(rs[2]);
            $("#standby_pay_price").html("<button class='btn_float_right btn_Turquoise01' onclick='loadStandbySum();'>불러오기</button>");
            $("#standby_total_price").html("");
            $("#standby_card_price").html("");
            $("#standby_issue_price").html(rs[6] + "원");
        };

        standby_data.corp_name   = $("#standby_corp_name").val();
        standby_data.listSize      = list_num;
        standby_data.page          = list_page;

        showMask();
        ajaxCall(url, "html", standby_data, callback);
    }
}


var loadStandbySum = function() {
    var url = "/ajax/calcul_mng/sales_tab/load_public_standby_all_price.php";
    var data = {
        "year"         : $("#year").val(),
        "mon"          : $("#mon").val(),
        "member_dvs"   : $("#member_dvs").val(),
        "member_seqno"   : $("#member_seqno").val(),
        "sell_site"    : $("#sell_site option:selected").text(),
        "member_seqno"    : $("#member_seqno").val(),
        "dlvr_way"    : $("#dlvr_way option:selected").val()
    };
    var callback = function(result) {
        var rs = result.split("♪♭@");
        $("#standby_pay_price").html(rs[0]);
        $("#standby_card_price").html(rs[1]);
        $("#standby_total_price").html(rs[2]);
        hideRegiPopup();
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

var loadCompleteSum = function() {
    var url = "/ajax/calcul_mng/sales_tab/load_public_complete_all_price.php";
    var data = {
        "year"         : $("#year").val(),
        "mon"          : $("#mon").val(),
        "member_dvs"   : $("#member_dvs").val(),
        "member_seqno"   : $("#member_seqno").val(),
        "sell_site"    : $("#sell_site option:selected").text(),
        "member_seqno"    : $("#member_seqno").val(),
        "dlvr_way"    : $("#dlvr_way option:selected").val()
    };
    var callback = function(result) {
        var rs = result.split("♪♭@");
        $("#complete_pay_total").html(rs[0]);
        $("#complete_card_total").html(rs[1]);
        $("#complete_total_price").html(rs[2]);
        hideRegiPopup();
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

/**
 * @brief 엑셀다운로드 함수
 *
 * @param dvs = 다운로드할 엑셀파일 구분 > 쓸지 말지 고민좀 해보자
 */
var downloadFile = function() {

    var url = "/ajax/calcul_mng/sales_tab/";
    var data = null;
    // 데이터는 cndSearch의 데이터를 사용
    //data = cndSearch.data;
    // 추가로 다운로드 url을 붙임
    url += "down_excel_salestab.php";

    $.ajax({
        type     : "POST",
        url      : url,
        dataType : "text",
        data     : {
            "year"         : $("#year").val(),
            "mon"          : $("#mon").val(),
            "member_dvs"   : $("#member_dvs").val(),
            "member_seqno"    : $("#member_seqno").val(),
            "sell_site"    : $("#sell_site").val(),
            "tab_public"   : $("#tab_public").val(),
            "dlvr_way"    : $("#dlvr_way option:selected").val(),
        },
        success  : function(result) {
            hideMask();
            if (result === "FALSE") {
                alert("엑셀파일 생성에 실패했습니다.");
            } else if (result === "NOT_INFO") {
                alert("엑셀로 생성할 정보가 존재하지 않습니다.");
            } else {
                var nameArr  = result.split('!');
                var downUrl  = "/common/excel_file_down.php?name=" + nameArr[1];
                downUrl += "&from=" + $("#from").val();
                downUrl += "&to=" + $("#to").val();
                downUrl += "&file_dvs=" + nameArr[0];

                $("#file_ifr").attr("src", downUrl);

            }
        },
        error    : function(request,status,error) {
            hideMask();
            alert("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
            console.log("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
        }
    });

    showMask();
};


var downloadFileStandby = function() {

    var url = "/ajax/calcul_mng/sales_tab/";
    var data = null;
    // 데이터는 cndSearch의 데이터를 사용
    //data = cndSearch.data;
    // 추가로 다운로드 url을 붙임
    url += "down_excel_standby.php";

    $.ajax({
        type     : "POST",
        url      : url,
        dataType : "text",
        data     : {
            "year"         : $("#year").val(),
            "mon"          : $("#mon").val(),
            "member_dvs"   : $("#member_dvs").val(),
            "member_seqno" : $("#member_seqno").val(),
            "sell_site"    : $("#sell_site option:selected").text(), //$("#sell_site").val()//
        },
        success  : function(result) {
            hideMask();
            if (result === "FALSE") {
                alert("엑셀파일 생성에 실패했습니다.");
            } else if (result === "NOT_INFO") {
                alert("엑셀로 생성할 정보가 존재하지 않습니다.");
            } else {
                var nameArr  = result.split('!');
                var downUrl  = "/common/excel_file_down.php?name=" + nameArr[1];
                downUrl += "&from=" + $("#from").val();
                downUrl += "&to=" + $("#to").val();
                downUrl += "&file_dvs=" + nameArr[0];

                $("#file_ifr").attr("src", downUrl);

            }
        },
        error    : function(request,status,error) {
            hideMask();
            alert("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
            console.log("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
        }
    });

    showMask();
};

var downloadFileCard = function() {

    var url = "/ajax/calcul_mng/sales_tab/";
    var data = null;
    // 데이터는 cndSearch의 데이터를 사용
    //data = cndSearch.data;
    // 추가로 다운로드 url을 붙임
    url += "down_excel_card.php";

    $.ajax({
        type     : "POST",
        url      : url,
        dataType : "text",
        data     : {
            "year"         : $("#year").val(),
            "mon"          : $("#mon").val(),
            "member_dvs"   : $("#member_dvs").val(),
            "member_seqno"   : $("#member_seqno").val(),
            "sell_site"    : $("#sell_site").val()
        },
        success  : function(result) {
            hideMask();
            if (result === "FALSE") {
                alert("엑셀파일 생성에 실패했습니다.");
            } else if (result === "NOT_INFO") {
                alert("엑셀로 생성할 정보가 존재하지 않습니다.");
            } else {
                var nameArr  = result.split('!');
                var downUrl  = "/common/excel_file_down.php?name=" + nameArr[1];
                downUrl += "&from=" + $("#from").val();
                downUrl += "&to=" + $("#to").val();
                downUrl += "&file_dvs=" + nameArr[0];

                $("#file_ifr").attr("src", downUrl);

            }
        },
        error    : function(request,status,error) {
            hideMask();
            alert("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
            console.log("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
        }
    });

    showMask();
};

var downloadFileComplete = function() {

    var url = "/ajax/calcul_mng/sales_tab/";
    var data = null;
    // 데이터는 cndSearch의 데이터를 사용
    //data = cndSearch.data;
    // 추가로 다운로드 url을 붙임
    url += "down_excel_complete.php";

    $.ajax({
        type     : "POST",
        url      : url,
        dataType : "text",
        data     : {
            "year"         : $("#year").val(),
            "mon"          : $("#mon").val(),
            "member_dvs"   : $("#member_dvs").val(),
            "member_seqno"   : $("#member_seqno").val(),
            "sell_site"    : $("#sell_site").val()
        },
        success  : function(result) {
            hideMask();
            if (result === "FALSE") {
                alert("엑셀파일 생성에 실패했습니다.");
            } else if (result === "NOT_INFO") {
                alert("엑셀로 생성할 정보가 존재하지 않습니다.");
            } else {
                var nameArr  = result.split('!');
                var downUrl  = "/common/excel_file_down.php?name=" + nameArr[1];
                downUrl += "&from=" + $("#from").val();
                downUrl += "&to=" + $("#to").val();
                downUrl += "&file_dvs=" + nameArr[0];

                $("#file_ifr").attr("src", downUrl);

            }
        },
        error    : function(request,status,error) {
            hideMask();
            alert("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
            console.log("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
        }
    });

    showMask();
};

var downloadFileTaxform = function() {

    var url = "/ajax/calcul_mng/sales_tab/";
    var data = null;
    // 데이터는 cndSearch의 데이터를 사용
    //data = cndSearch.data;
    // 추가로 다운로드 url을 붙임
    url += "down_excel_salestab_taxform.php";

    $.ajax({
        type     : "POST",
        url      : url,
        dataType : "text",
        data     : {
            "year"         : $("#year").val(),
            "mon"          : $("#mon").val(),
            "member_dvs"   : $("#member_dvs").val(),
            "sell_site"    : $("#sell_site option:selected").text(), // $("#sell_site").val()
        },
        success  : function(result) {
            hideMask();
            if (result === "FALSE") {
                alert("엑셀파일 생성에 실패했습니다.");
            } else if (result === "NOT_INFO") {
                alert("엑셀로 생성할 정보가 존재하지 않습니다.");
            } else {
                var nameArr  = result.split('!');
                var downUrl  = "/common/excel_file_down.php?name=" + nameArr[1];
                downUrl += "&from=" + $("#from").val();
                downUrl += "&to=" + $("#to").val();
                downUrl += "&file_dvs=" + nameArr[0];

                $("#file_ifr").attr("src", downUrl);

            }
        },
        error    : function(request,status,error) {
            hideMask();
            alert("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
            console.log("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
        }
    });

    showMask();
};

//매출계산서 현금영수증 리스트가져오기
var loadCashreceiptList = {
    "exec"       : function(listSize, page, state) {
	if(listSize){
	    list_num = listSize;
	} 
	if(page) {
	   list_page = page;
	}

        var url = "/ajax/calcul_mng/sales_tab/load_cashreceipt_list.php";
        var blank = "<tr><td colspan=\"12\">검색 된 내용이 없습니다.</td></tr>";
        cashreceipt_data = {
       	    	"year"         : $("#year").val(),
    	    	"mon"          : $("#mon").val(),
    	    	"member_dvs"   : $("#member_dvs").val(),
    	    	"sell_site"    : $("#sell_site option:selected").text(),
            "member_seqno"    : $("#member_seqno").val(),
                "dvs"    : $("#sell_site").val(),
            "dlvr_way"    : $("#dlvr_way option:selected").val(),
                "state" : state
        };

        var callback = function(result) {
            var rs = result.split("♪♭@");

            if (rs[0].trim() == "") {
                if(state == "대기") {
                    $("#cashreceipt_list").html(blank);
                } else {
                    $("#cashreceipt_complete_list").html(blank);
                }
                $("#cashreceipt_member_cnt").html(rs[2]);
                $("#cashreceipt_pay_total").html(rs[3] + "원");
            	$("#cashreceipt_total_price").html(rs[3] + "원");
            	$("#cashreceipt_card_total").html(rs[4] + "원");
                $("#cashreceipt_issue_total").html(rs[3] + "원");
                return false;
            }

            if(state == "대기") {
                $("#cashreceipt_list").html(rs[0]);
                $("#page").html(rs[1]);
                $("#cashreceipt_member_cnt").html(rs[2]);
                $("#cashreceipt_pay_total").html(rs[3] + "원");
                $("#cashreceipt_total_price").html(rs[3] + "원");
                $("#cashreceipt_card_total").html(rs[4] + "원");
                $("#cashreceipt_issue_total").html(rs[3] + "원");
            } else {
                $("#cashreceipt_complete_list").html(rs[0]);
                $("#page").html(rs[1]);
                $("#cashreceipt_complete_member_cnt").html(rs[2]);
                $("#cashreceipt_complete_pay_total").html(rs[3] + "원");
                $("#cashreceipt_complete_total_price").html(rs[3] + "원");
                $("#cashreceipt_complete_card_total").html(rs[4] + "원");
                $("#cashreceipt_complete_issue_total").html(rs[3] + "원");
            }
        };

        cashreceipt_data.corp_name   = $("#cashreceipt_corp_name").val();
        cashreceipt_data.listSize      = list_num;
        cashreceipt_data.page          = list_page;

        showMask();
        ajaxCall(url, "html", cashreceipt_data, callback);
    }
}

//매출계산서 미발행(현금영수증) 리스트가져오기
var loadUnissuedList = {
    "exec"       : function(listSize, page, state) {
	if(listSize){
	    list_num = listSize;
	} 
	if(page) {
	   list_page = page;
	}

        var url = "/ajax/calcul_mng/sales_tab/load_unissued_list.php";
        var blank = "<tr><td colspan=\"11\">검색 된 내용이 없습니다.</td></tr>";
        var data = {
       	    	"year"         : $("#year").val(),
    	    	"mon"          : $("#mon").val(),
    	    	"member_dvs"   : $("#member_dvs").val(),
    	    	"sell_site"    : $("#sell_site").val(),
                "member_seqno"    : $("#member_seqno").val(),
            "dlvr_way"    : $("#dlvr_way option:selected").val(),
	    };

        var callback = function(result) {
            var rs = result.split("♪♭@");

            if (rs[0].trim() == "") {
                $("#unissued_list").html(blank);
                $("#unissued_member_cnt").html(rs[3]);
                $("#unissued_pay_total").html(rs[4] + "원");
                return false;
            }

            $("#unissued_list").html(rs[0]);
            $("#page").html(rs[1]);
            $("#unissued_member_cnt").html(rs[3]);
            $("#unissued_pay_total").html(rs[4] + "원");
            $("#unissued_cash_price").html(rs[5] + "원");
            $("#unissued_pay_total").html(rs[4] + "원");
            $("#unissued_complete_enuri_total").html(rs[5] + "원");
            $("#unissued_complete_issue_total").html(rs[6] + "원");
            $("#unissued_complete_total_price").html(rs[6] + "원");
        };

        data.search        = $("#unissued_search").val();
        data.search_dvs    = $("#unissued_search_dvs").val();
        data.listSize      = list_num;
        data.page          = list_page;

        showMask();
        ajaxCall(url, "html", data, callback);
    }
}

//매출계산서 완료 리스트가져오기
var loadPublicCompleteList = {
    "exec"       : function(listSize, page, dvs) {
	if(listSize){
	    list_num = listSize;
	} 
	if(page) {
	   list_page = page;
	}

        var url = "/ajax/calcul_mng/sales_tab/load_public_complete_list.php";
        var blank = "<tr><td colspan=\"14\">검색 된 내용이 없습니다.</td></tr>";
        complete_data = {
       	        "year"         : $("#year").val(),
    	        "mon"          : $("#mon").val(),
    	        "member_dvs"   : $("#member_dvs").val(),
    	        "sell_site"    : $("#sell_site").val(),
    	        "tab_public"   : $("#tab_public").val(),
                "member_seqno"    : $("#member_seqno").val(),
            "dlvr_way"    : $("#dlvr_way option:selected").val(),
	    };

        /*
        if (rs[0].trim() == "") {
                $("#standby_list").html(blank);
                $("#standby_member_cnt").html(rs[2]);
                //$("#standby_pay_price").html(rs[3] + "원");
                //$("#standby_pay_price").html("<button>보기</button>");
            	//$("#standby_total_price").html(rs[3] + "원");
                //$("#standby_card_price").html(rs[4] + "원");

                return false;
            }

            $("#standby_list").html(rs[0]);
            $("#standby_page").html(rs[1]);
            $("#standby_member_cnt").html(rs[2]);
            $("#standby_pay_price").html("<button class='btn_float_right btn_Turquoise01' onclick='loadStandbySum();'>불러오기</button>");
            $("#standby_total_price").html("");
            $("#standby_card_price").html("");
            $("#standby_issue_price").html(rs[6] + "원");
         */

        var callback = function(result) {
            var rs = result.split("♪♭@");

            if (rs[0].trim() == "") {
                $("#complete_list").html(blank);
                $("#complete_member_cnt").html(rs[2]);
                //$("#complete_pay_total").html(rs[3] + "원");
                //$("#complete_total_price").html(rs[3] + "원");
                //$("#complete_issue_price").html(rs[4] + "원");
                return false;
            }

            $("#complete_list").html(rs[0]);
            $("#complete_page").html(rs[1]);
            $("#complete_member_cnt").html(rs[2]);
            $("#complete_pay_total").html("<button class='btn_float_right btn_Turquoise01' onclick='loadCompleteSum();'>불러오기</button>");
            $("#complete_total_price").html("");
            $("#complete_card_total").html("");
            $("#complete_issue_price").html(rs[6] + "원");
        };

        complete_data.search   = $("#complete_search").val();
        complete_data.search_dvs   = $("#complete_search_dvs").val();
        complete_data.listSize      = list_num;
        complete_data.page          = list_page;

        showMask();
        ajaxCall(url, "html", complete_data, callback);
    }
}

var loadCardpayList = {
    "exec"       : function(listSize, page, dvs) {
        if(listSize){
            list_num = listSize;
        }
        if(page) {
            list_page = page;
        }

        var url = "/ajax/calcul_mng/sales_tab/load_cardpay_list.php";
        var blank = "<tr><td colspan=\"14\">검색 된 내용이 없습니다.</td></tr>";
        complete_data = {
            "year"         : $("#year").val(),
            "mon"          : $("#mon").val(),
            "member_dvs"   : $("#member_dvs").val(),
            "sell_site"    : $("#sell_site").val(),
            "tab_public"   : $("#tab_public").val(),
            "member_seqno"    : $("#member_seqno").val(),
            "dlvr_way"    : $("#dlvr_way option:selected").val(),
        };

        var callback = function(result) {
            var rs = result.split("♪♭@");

            if (rs[0].trim() == "") {
                $("#cardpay_list").html(blank);
                $("#cardpay_member_cnt").html(rs[2]);
                $("#cardpay_pay_total").html(rs[3] + "원");
                $("#cardpay_card_total").html(rs[4] + "원");
                $("#cardpay_total_price").html(rs[5] + "원");
                $("#cardpay_issue_price").html(rs[6] + "원");
                return false;
            }

            $("#cardpay_list").html(rs[0]);
            $("#cardpay_page").html(rs[1]);
            $("#cardpay_member_cnt").html(rs[2]);
            $("#cardpay_pay_total").html(rs[3] + "원");
            $("#cardpay_card_total").html(rs[4] + "원");
            $("#cardpay_total_price").html(rs[5] + "원");
            $("#cardpay_issue_price").html(rs[6] + "원");
        };

        complete_data.search   = $("#cardpay_search").val();
        complete_data.search_dvs   = $("#cardpay_search_dvs").val();
        complete_data.listSize      = list_num;
        complete_data.page          = list_page;

        showMask();
        ajaxCall(url, "html", complete_data, callback);
    }
}

var loadPublicAllList = {
    "exec"       : function(listSize, page, dvs) {
        if(listSize){
            list_num = listSize;
        }
        if(page) {
            list_page = page;
        }

        var url = "/ajax/calcul_mng/sales_tab/load_public_all_list.php";
        var blank = "<tr><td colspan=\"14\">검색 된 내용이 없습니다.</td></tr>";
        complete_data = {
            "year"         : $("#year").val(),
            "mon"          : $("#mon").val(),
            "member_dvs"   : $("#member_dvs").val(),
            "sell_site"    : $("#sell_site").val(),
            "tab_public"   : $("#tab_public").val(),
            "member_seqno"    : $("#member_seqno").val(),
            "dlvr_way"    : $("#dlvr_way option:selected").val(),
        };

        var callback = function(result) {
            var rs = result.split("♪♭@");

            if (rs[0].trim() == "") {
                $("#all_list").html(blank);
                $("#all_member_cnt").html(rs[2]);
                $("#all_pay_total").html(rs[3] + "원");
                $("#all_total_price").html(rs[3] + "원");
                $("#all_card_total").html(rs[4] + "원");
                return false;
            }
            $("#all_list").html(rs[0]);
            $("#all_page").html(rs[1]);
            $("#all_member_cnt").html(rs[2]);
            $("#all_pay_price").html(rs[4] + "원");
            $("#all_total_price").html(rs[3] + "원");
            $("#all_card_total").html(rs[5] + "원");
            $("#all_enuri").html(rs[6] + "원");
        };

        complete_data.search   = $("#all_search").val();
        complete_data.search_dvs   = $("#all_search_dvs").val();
        complete_data.listSize      = list_num;
        complete_data.page          = list_page;

        showMask();
        ajaxCall(url, "html", complete_data, callback);
    }
}


//예외처리  리스트가져오기
var loadPublicExceptList = {
    "exec"       : function(listSize, page) {
	if(listSize){
	    list_num = listSize;
	} 
	if(page) {
	   list_page = page;
	}

        var url = "/ajax/calcul_mng/sales_tab/load_public_except_list.php";
        var blank = "<tr><td colspan=\"14\">검색 된 내용이 없습니다.</td></tr>";
        except_data = {
       	    	"year"         : $("#year").val(),
    	    	"mon"          : $("#mon").val(),
    	    	"member_dvs"   : $("#member_dvs").val(),
    	    	"sell_site"    : $("#sell_site option:selected").text(),
                "member_seqno"    : $("#member_seqno").val(),
            "dlvr_way"    : $("#dlvr_way option:selected").val(),
	    };

        var callback = function(result) {
            var rs = result.split("♪♭@");

            if (rs[0].trim() == "") {
                $("#except_list").html(blank);
                $("#except_member_cnt").html(rs[2]);
                $("#except_pay_price").html(rs[3] + "원");
            	$("#except_card_total").html(rs[4] + "원");
            	$("#except_cash_price").html(rs[5] + "원");
                return false;
            }

            $("#except_list").html(rs[0]);
            $("#page").html(rs[1]);
            $("#except_member_cnt").html(rs[2]);
            $("#except_pay_price").html(rs[3] + "원");
            $("#except_card_total").html(rs[4] + "원");
            $("#except_cash_price").html(rs[5] + "원");
        };

        except_data.corp_name   = $("#except_corp_name").val();
        except_data.listSize      = list_num;
        except_data.page          = list_page;

        showMask();
        ajaxCall(url, "html", except_data, callback);
    }
}



//보여줄 페이지 수 설정
var showPageSetting = function(val, state) {
 
    if (state == "대기") {
        loadPublicStandByList.exec(val, 1);
    } else if (state == "현금영수증(대기)") {
        loadCashreceiptList.exec(val, 1,'대기');
    } else if (state == "현금영수증(완료)") {
        loadCashreceiptList.exec(val, 1,'완료');
    } else if (state == "미발행") {
        loadUnissuedList.exec(val, 1);
    } else if (state == "완료") {
        loadPublicCompleteList.exec(val, 1);
    } else if (state == "카드건별") {
        loadCardpayList.exec(val, 1);
    } else {
        loadPublicExceptList.exec(val, 1);
    }
}

//세금계산서(대기) 페이지 이동
var moveStandbyPage = function(val) {
    loadPublicStandByList.exec(list_num, val);
}

//현금영수증 페이지 이동
var moveCashreceiptPage = function(val) {
    loadCashreceiptList.exec(list_num, val);
}

//미발행(현금순매출) 페이지 이동
var moveUnissuedPage = function(val) {
    loadUnissuedList.exec(list_num, val);
}

//세금계산서(발급완료) 페이지 이동
var moveCompletePage = function(val) {
    loadPublicCompleteList.exec(list_num, val);
}

//예외처리 페이지 이동
var moveExceptPage = function(val) {
    loadPublicExceptList.exec(list_num, val);
}

var moveCardpayPage = function(val) {
    loadCardpayList.exec(list_num, val);
}

/**
 * @brief 밸행 구분 탭
 */
var tabCtrl = function(state) {

    if (state) {
    	tab_name = state;
    }
    resetSearchInput();
    
    if (tab_name == "대기") {
	loadPublicStandByList.exec('30', '1');
    } else if (tab_name == "현금영수증(대기)") {
	loadCashreceiptList.exec('30', '1','대기');
    } else if (tab_name == "현금영수증(완료)") {
        loadCashreceiptList.exec('30', '1','완료');
    } else if (tab_name == "미발행") {
	loadUnissuedList.exec('30', '1');
    } else if (tab_name == "카드건별") {
        loadCardpayList.exec('30', '1');
    } else if (tab_name == "완료") {
	loadPublicCompleteList.exec('30', '1');
    } else if (tab_name == "전체") {
        loadPublicAllList.exec('30', '1');
    } else {
	loadPublicExceptList.exec('30', '1');
    }
}

var resetSearchInput = function() {
    $("#standby_corp_name").val('');
    $("#cashreceipt_corp_name").val('');
    $("#unissued_search").val('');
    $("#complete_search_name").val('');
    $("#except_corp_name").val('');
}

var savePublicSeq = function(seqno) {
    public_seqno = seqno;
    savePublic("예외");
}

var saveStateSeq = function(member_seqno, state, kind) {
    savePublicState(state, member_seqno, kind);
}

var savePublicState = function(state,seqno) {

    var url = "/proc/calcul_mng/sales_tab/proc_public_only_state.php";
    var data = {
	    "public_state" : state,
        "member_seqno" : seqno,
        "year"         : $("#year").val(),
        "mon"          : $("#mon").val(),
    };

    var callback = function(result) {
        if (result == 1) {
	    if (state == "예외") {
		    alert("예외처리로 이동하였습니다.");
	    } else {
            if (tab_name == "대기") {
                alert("발행완료");
            } else if (tab_name == "완료") {
                alert("발행취소");
            }
	    }
	    hideRegiPopup();

	    if (tab_name == "대기") {
            	loadPublicStandByList.exec();
	    } else if (tab_name == "완료") {
            loadPublicCompleteList.exec();
        } else {
            	loadPublicExceptList.exec();
	    }

        } else {
            alert("실패하였습니다.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);

}

var savePublicDetail = function() {

    var url = "/proc/calcul_mng/sales_tab/proc_public_detail.php";
    var data = {
    	"seqno"        : public_seqno,
	"oa"           : $("#basic_oa").val(),
	"before_oa"    : $("#basic_before_oa").val(),
	"public_date"  : $("#basic_public_date").val(),
    };

    var callback = function(result) {
        if (result == 1) {
	    hideRegiPopup();
	    if (tab_name == "대기") {
	        alert("저장에 성공하였습니다.  발급완료로 이동하였습니다.");
                loadPublicStandByList.exec();
	    } else {
		alert("저장에 성공하였습니다.");
                loadPublicCompleteList.exec();
	    }
        } else {
            alert("실패하였습니다.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//세금계산서 상태 변경
var savePublic = function(state) {

    var url = "/proc/calcul_mng/sales_tab/proc_public_state.php";
    var data = {
	"state" : state,
    	"seqno" : public_seqno,
	    "tab_name" : tab_name
    };

    var callback = function(result) {
        if (result == 1) {
	    if (state == "완료") {
            	alert("발급완료로 이동했습니다.");
	    } else {
		if (tab_name == "완료") {
		    alert("미발행(현금순매출)으로 이동하였습니다.");

		} else {
		    alert("예외처리로 이동하였습니다.");
		}
	    }
	    hideRegiPopup();

	    if (tab_name == "대기") {
            	loadPublicStandByList.exec();
	    } else {
            	loadPublicCompleteList.exec();
	    }

        } else {
            alert("실패하였습니다.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);

}

//현금영수증 미발행 현금순매출 변경
var saveCashreceipt = function(member_seqno, year, month, detail_dvs) {

    var url = "/proc/calcul_mng/sales_tab/proc_cashreceipt_state.php";
    var data = {
        "member_seqno" : member_seqno,
        "year" : year,
        "month" : month,
        "detail_dvs" : detail_dvs,
        "kind" : "현금영수증",
        "new_state" : "완료"
    };

    var callback = function(result) {
        if (result == 1) {
	    alert("발행완료.");
            loadCashreceiptList.exec(30,1,"대기");
        } else {
            alert("실패하였습니다.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//현금영수증 미발행 현금순매출 변경
var cancelCashreceipt = function(member_seqno, year, month, detail_dvs) {

    var url = "/proc/calcul_mng/sales_tab/proc_cashreceipt_state.php";
    var data = {
        "member_seqno" : member_seqno,
        "year" : year,
        "month" : month,
        "detail_dvs" : detail_dvs,
        "now_state" : "현금영수증",
        "new_state" : "대기"
    };

    var callback = function(result) {
        if (result == 1) {
            alert("취소완료");
            loadCashreceiptList.exec(30,1,'완료');
        } else {
            alert("실패하였습니다.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

var showReceiptReadyCasePopup = function(member_seqno, year, month, state, dvs_detail) {
    var url = "/ajax/calcul_mng/income_data/load_receipt_popup.php";
    var data = {
        "member_seqno" : member_seqno,
        "year" : year,
        "month" : month,
        "state" : state,
        "dvs_detail" : dvs_detail,
        "kind" : "현금영수증"
    };

    var callback = function(result) {
        openRegiPopup(result, "1210");
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}
var ChangePrice = function (seq) {
    
    var url = "/proc/calcul_mng/sales_tab/proc_public_price.php";

    standby_data = {
        "year"         : $("#year").val(),
        "mon"          : $("#mon").val(),
        "member_seqno"   : seq,
        "new_price"   : $('#cp_'+seq).val(),
    };

    var data = standby_data;

    var callback = function(result) {
        if (result == 1) {
	    alert("입력되었습니다.");
            hideRegiPopup();
            loadPublicStandByList.exec();

        } else {
            alert("실패했습니다.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback); 
}

var showTaxReadyCasePopup = function(member_seqno, year, month, state) {
    var url = "/ajax/calcul_mng/income_data/load_receipt_popup.php";
    var data = {
        "member_seqno" : member_seqno,
        "year" : year,
        "month" : month,
        "state" : state,
        "kind" : "세금계산서"
    };

    var callback = function(result) {
        openRegiPopup(result, "1210");
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

var showUnissueCasePopup = function(member_seqno, year, month, state) {
    var url = "/ajax/calcul_mng/income_data/load_receipt_popup.php";
    var data = {
        "member_seqno" : member_seqno,
        "year" : year,
        "month" : month,
        "state" : state,
        "kind" : "미발행"
    };

    var callback = function(result) {
        openRegiPopup(result, "1210");
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

var receipt_issue = function(order_num,change_state,kind) {
    var url = "/proc/calcul_mng/sales_tab/proc_cashreceipt_state.php";
    var data = {
        "kind" : kind,
        "new_state" : change_state,
        "order_num" : order_num
    };

    old_state = "완료";
    if(change_state == "완료")
        old_state = "대기";
    var callback = function(result) {
        if (result == 1) {
            alert("발행완료.");
            loadCashreceiptList.exec(30,1,old_state);
        } else {
            alert("실패하였습니다.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

var showCardpayListPopup = function(member_seqno, year, month) {
    var url = "/ajax/calcul_mng/income_data/load_cardpay_popup.php";
    var data = {
        "member_seqno" : member_seqno,
        "year" : year,
        "month" : month,
        "kind" : "카드"
    };

    var callback = function(result) {
        openRegiPopup(result, "1210");
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//현금영수증 미발행 현금순매출 변경
var changeDvs = function(seqno, state) {

    var url = "/proc/calcul_mng/sales_tab/proc_public_state.php";
    var data = {
        "seqno" : seqno,
        "state" : state
    };

    var callback = function(result) {
        if (result == 1) {
            alert("이동하였습니다.");
            loadCashreceiptList.exec();
        } else {
            alert("실패하였습니다.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

var changeDvsPeriod = function(member_seqno, public_dvs, dvs_detail, year, month, cnt) {
    var url = "/proc/calcul_mng/sales_tab/proc_public_state_period.php";
    var data = {
        "member_seqno" : member_seqno,
        "year" : year,
        "month" : month,
        "now_state" : "현금영수증",
        "public_dvs" : public_dvs,
        "dvs_detail" : dvs_detail,
        "new_state" : $("#dvs_detail_" + cnt).val()
    };

    var callback = function(result) {
        if (result == 1) {
            alert("이동하였습니다.");
            loadCashreceiptList.exec();
        } else {
            alert("실패하였습니다.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

var ChangeDvsPeriodByCase = function(order_num) {
    var url = "/proc/calcul_mng/sales_tab/proc_public_state_case.php";
    var data = {
        "order_num" : order_num,
        "new_state" : $("#dvs_detail_" + order_num).val()
    };

    var callback = function(result) {
        if (result == 1) {
            alert("이동하였습니다.");
            loadCashreceiptList.exec();
        } else {
            alert("실패하였습니다.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}


var StandbyChangeDvsPeriod = function(member_seqno, public_dvs, dvs_detail, year, month, cnt) {
    var url = "/proc/calcul_mng/sales_tab/proc_public_state_period.php";
    var data = {
        "member_seqno" : member_seqno,
        "year" : year,
        "month" : month,
        "now_state" : "세금계산서",
        "public_dvs" : public_dvs,
        "dvs_detail" : dvs_detail,
        "new_state" : $("#dvs_detail_" + cnt).val()
    };

    var callback = function(result) {
        if (result == 1) {
            alert("이동하였습니다.");
            loadPublicStandByList.exec();
        } else {
            alert("실패하였습니다.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

var UnissuedChangeDvsPeriod = function(member_seqno, public_dvs, dvs_detail, year, month, cnt) {
    var url = "/proc/calcul_mng/sales_tab/proc_public_state_period.php";
    var data = {
        "member_seqno" : member_seqno,
        "year" : year,
        "month" : month,
        "now_state" : "미발행",
        "public_dvs" : public_dvs,
        "dvs_detail" : dvs_detail,
        "new_state" : $("#unissue_dvs_detail_" + cnt).val()
    };

    var callback = function(result) {
        if (result == 1) {
            alert("이동하였습니다.");
            loadUnissuedList.exec();
        } else {
            alert("실패하였습니다.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}


//현금영수증 미발행 현금순매출 삭제
var removeCashreceipt = function(seqno) {
    if (confirm("삭제하시겠습니까?") == false){
	return;
    }

    var url = "/proc/calcul_mng/sales_tab/del_cashreceipt.php";
    var data = {
    	"seqno" : seqno
    };

    var callback = function(result) {
        if (result == 1) {
	    alert("삭제하였습니다.");
            loadUnissuedList.exec();
        } else {
            alert("실패하였습니다.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//체크박스 선택시 value값 가져오는 함수
var getselectedNo = function(el) {

    var selectedValue = ""; 
    
    $("#" + el + "_list input[name=" + el + "_chk]:checked").each(function() {
        selectedValue += ","+ $(this).val();		    
    });

    if (selectedValue != "" && el != "flatt_y") {
        selectedValue = selectedValue.substring(1);
    }

    return selectedValue;
}

//전체 선택
var allCheck = function(type) {
    //만약 전체선택 체크박스가 체크 된 상태일 경우
    if ($("#allCheck_" + type).prop("checked")) {
        $("#" + type + "_list input[type=checkbox]").prop("checked", true);
    } else {
        $("#" + type + "_list input[type=checkbox]").prop("checked", false);
    }
}

/**
* @brief 대기리스트 발행상태 변경
*/
var saveStandbyChkPublic = function(state) {

    var seqno = getselectedNo('standby');
    if (seqno.length == 0) {
	alert("체크박스를 선택해주세요");
	return false;
    }

    var url = "/proc/calcul_mng/sales_tab/proc_tab_public.php";
    var data = {
	"state" : state,
        "seqno" : seqno,
        "year"         : $("#year").val(),
        "mon"          : $("#mon").val(),
    };

    var callback = function(result) {
        if (result == 1) {
            loadPublicStandByList.exec();
            $(".check_box").prop("checked", false);
	    if (state == "완료") {
            alert("발급을 완료하였습니다.");
	    } else {
		    alert("보류하였습니다.");
	    }
        } else {
            alert("실패하였습니다.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

/**
* @brief 발행상태 변경
*/
var saveCashreceiptPublic = function() {
    cashreceipt_data.seqno = '';

    var url = "/proc/calcul_mng/sales_tab/proc_public_cashreceipt.php";
    var callback = function(result) {
        if (result == 0) {
            return alertReturnFalse("실패하였습니다.");
        }

        var downUrl  = "/common/excel_file_down.php?file_dvs=" + result + "&name=" + result;
        $("#file_ifr").attr("src", downUrl);

        alert("발행하였습니다.");
        loadCashreceiptList.exec();
    };

    showMask();
    ajaxCall(url, "text", cashreceipt_data, callback);

}

/**
* @brief 대기리스트 발행상태 변경
*/
var saveStandbyPublic = function() {

    var url = "/proc/calcul_mng/sales_tab/proc_public_standby.php";
    var data = standby_data;

    var callback = function(result) {
        if (result == 1) {
	    alert("발급을 완료하였습니다.");
            loadPublicStandByList.exec();

        } else {
            alert("실패하였습니다.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);

}

/**
* @brief 완료리스트 발행상태 변경
*/
var saveCompletePublic = function() {
    complete_data.seqno = '';

    var url = "/proc/calcul_mng/sales_tab/proc_public_complete.php";
    var callback = function(result) {
        if (result == 0) {
            return alertReturnFalse("실패하였습니다.");
        }

        var downUrl  = "/common/excel_file_down.php?file_dvs=" + result + "&name=" + result;
        $("#file_ifr").attr("src", downUrl);

        loadPublicCompleteList.exec();
    };

    showMask();
    ajaxCall(url, "text", complete_data, callback);
}

/**
* @brief 예외리스트 일괄 발행상태 변경
*/
var saveExceptBatchPublic = function() {

    //TODO 성공시에  엑셀다운로드 만들어야함
    var url = "/proc/calcul_mng/sales_tab/proc_public_except.php";
    var data = except_data;

    var callback = function(result) {

        if (result == 1) {
	    alert("수정하였습니다.");
            loadPublicExceptList.exec();

        } else {
            alert("실패하였습니다.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);

}

/**
* @brief 발행여부 및 보류
*/
var procStandby = function(state, dvs) {

    var seqno = getselectedNo(dvs);
    if (seqno.length == 0) {
	    alert("체크박스를 선택해주세요");
	    return false;
    }

    var url = "/proc/calcul_mng/sales_tab/proc_tab_public.php";
    var data = {
	"state" : state,
	"origin": dvs,
    	"seqno" : seqno
    };

    var callback = function(result) {
        if (result == 1) {
            if (dvs == "unissued") {
                loadPublicUnissuedList.exec();
            } else {
                loadPublicCompleteList.exec();
            }
            $(".check_box").prop("checked", false);
	    alert("발행을 대기하였습니다.");
        } else {
            alert("실패하였습니다.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

/**
* @brief 예외처리  발행 수정
*/
var saveExceptPublic = function() {
    var formData = $("#editForm").serialize() + "&seqno=" + public_seqno;

    var url = "/proc/calcul_mng/sales_tab/proc_except_public.php";
    var data = formData;
    var callback = function(result) {
        if (result == 1) {
            hideRegiPopup();
            alert("성공하였습니다.");
            loadPublicExceptList.exec();
        } else {
            alert("실패하였습니다.");
        }
    }

    showMask();
    ajaxCall(url, "html", data, callback);
}

var enterCheck = function(event, dvs) {
    if(event.keyCode == 13) {
	if (dvs == "standby") {
	    loadPublicStandByList.exec();
	} else if (dvs == "cashreceipt") {
	    loadCashreceiptList.exec();
	} else if (dvs == "unissued") {
	    loadUnissuedList.exec();
	} else if (dvs == "complete") {
	    loadPublicCompleteList.exec();
	} else {
	    loadPublicExceptList.exec();
	}
    }
}

//매출, 대상금액, 단가, 공급가액, 세액 구하기
var applySumPrice = function(event) {

    removeChar(event);

    //매출 금액 구하기
    var sales = Number($("#edit_card_price").val()) + Number($("#edit_cash_price").val()) + Number($("#edit_etc_price").val());
    $("#edit_pay_price").val(sales);

    //대상금액 구하기
    var object_sum = Number($("#edit_pay_price").val()) - Number($("#edit_card_price").val());
    $("#edit_object_price").val(object_sum);
    $("input[name=edit_tot_price]").attr('value', object_sum);

    //단가, 공급가액 구하기
    var unitprice = Math.ceil(Number($("#edit_object_price").val()) / 1.1);
    $("#edit_unitprice").html(unitprice); 
    $("#edit_supply_price").html(unitprice); 
    $("input[name=edit_unitprice]").attr('value', unitprice);
    $("input[name=edit_supply_price]").attr('value', unitprice);

    //세액 구하기
    var vat = Number($("#edit_object_price").val()) - unitprice;
    $("#edit_vat").html(vat); 
    $("input[name=edit_vat]").attr('value', vat);

    //합계 구하기
    $("#edit_tot_price").html(object_sum);

}

/**
* @brief 세금계산서 발행
*/
var saveNewPublic = function() {
    if (!$("#regi_member_seqno").val()) {
	alert("회원명검색 후 클릭한 다음 저장해주세요.");
	return false;
    }

    var year = $("#edit_req_date").html().split("년");
    var mon = year[1].trim().split("월");

    var formData = $("#editForm").serialize() + "&year=" + year[0] + "&mon=" + mon[0];

    var url = "/proc/calcul_mng/sales_tab/proc_new_public.php";

    var data = formData;
    var callback = function(result) {
        if (result == 1) {
            hideRegiPopup();
            alert("성공하였습니다.");
	    tabCtrl();
        } else {
            alert("실패하였습니다.");
        }
    }

    showMask();
    ajaxCall(url, "html", data, callback);
}

//세금계산서 새로 만들기
var insertNewPublic = function() {
    public_seqno = "";
    openRegiPopup($("#edit_popup").html(), 800);
    $("#new_member").show();
    $("#new_save").show();
    $("#except_save").hide();
    $("#except_del").hide();

    //datepicker 기본 셋팅
    $("#edit_public_date").datepicker({
        autoclose:true,
        format: "yyyy-mm-dd",
        todayBtn: "linked",
        todayHighlight: true,
    });

    $("#edit_public_date").datepicker("setDate", new Date());
    $("#edit_req_date").html($("#year").val() + "년 " + $("#mon").val() + "월");

}

/**
* @brief 새로생성한 발행저장
*/
var saveNewPublic = function() {
    if (!$("#regi_member_seqno").val()) {
	alert("회원명검색 후 클릭한 다음 저장해주세요.");
	return false;
    }

    var year = $("#edit_req_date").html().split("년");
    var mon = year[1].trim().split("월");

    var formData = $("#editForm").serialize() + "&year=" + year[0] + "&mon=" + mon[0];

    var url = "/proc/calcul_mng/sales_tab/proc_new_public.php";

    var data = formData;
    var callback = function(result) {
        if (result == 1) {
            hideRegiPopup();
            alert("성공하였습니다.");
	    tabCtrl();
        } else {
            alert("실패하였습니다.");
        }
    }

    showMask();
    ajaxCall(url, "html", data, callback);
}

/**
* @brief 새로생성할 회원 일련번호 가져옴
*/
var loadMemberSeqClick = function(seqno, name) {

    var url = "/ajax/calcul_mng/sales_tab/load_member_info.php";
    var data = {
        "member_seqno" : seqno
    };
    var callback = function(result) {
        var rs = result.split("♪♭♬");
	
	$("#edit_corp_name").val(rs[1]);
	$("#edit_repre_name").val(rs[2]);
	$("#edit_crn").val(rs[3]);
	$("#edit_bc").val(rs[4]);
	$("#edit_tob").val(rs[5]);
	$("#edit_addr").val(rs[6]);
	$("#edit_zipcode").val(rs[7]);

    	$("#regi_office_nick").val(name);
    	$("#regi_member_seqno").val(seqno);

    	hidePopPopup();
    };

    ajaxCall(url, "html", data, callback);
}

//새로 발급할 회원 조회
var loadRegiOfficeNick = function(event, val, dvs) {
    if (event.keyCode != 13) {
        return false;
    }

    if (val.length < 2) {
        alert("두글자 이상 입력하세요.");
	    return false;
    }

    var url = "/ajax/calcul_mng/sales_tab/load_public_office_nick.php";
    var data = {
        "sell_site"  : $("#sell_site").val(),
        "search_val" : val
    };
    var callback = function(result) {
        if (dvs !== "select") {
            searchPopPopShow(event, "loadRegiOfficeNick", "loadRegiOfficeNick");
        } else {
            showBgMask();
        }

        $("#search_list").html(result);
    };

    ajaxCall(url, "html", data, callback);
}

//검색창 팝업 show
var searchPopPopShow = function(event, fn1, fn2) {

    var html = "";
    html += "\n  <dl>";
    html += "\n    <dt class=\"tit\">";
    html += "\n      <h4>검색창 팝업</h4>";
    html += "\n    </dt>";
    html += "\n    <dt class=\"cls\">";
    html += "\n      <button type=\"button\" onclick=\"hidePopPopup();\" class=\"btn btn-sm btn-danger fa fa-times\">";
    html += "\n      </button>";
    html += "\n    </dt>";
    html += "\n  </dl>";
    html += "\n  <div class=\"pop-base\">";
    html += "\n    <div class=\"pop-content\">";
    html += "\n      <label for=\"search_pop\" class=\"con_label\">";
    html += "\n        Search : ";
    html += "\n        <input id=\"search_pop\" type=\"text\" class=\"search_btn fix_width180\" onkeydown=\"" + fn1 + "(event, this.value, 'select');\">";
    html += "\n        <button type=\"button\" class=\"btn btn-sm btn-info fa fa-search\" onclick=\"" + fn2 + "(event, this.value, 'select');\">";
    html += "\n        </button>";
    html += "\n      </label>";
    html += "\n      <hr class=\"hr_bd3\">";
    html += "\n      <div class=\"list_scroll fix_height120\" id=\"search_list\">";
    html += "\n      </div>";
    html += "\n    </div>";
    html += "\n  </div>";
    html += "\n</div>";

    openPopPopup(html, 440);
    $("#search_pop").focus();
}

//엑셀 다운로드
var excelDownload = function(dvs) {
    var seqno = '';
    var data  = null;
    var callback = null;

    $("input[name='" + dvs + "_chk']:checked").each(function() {
        seqno += $(this).val();
        seqno += ',';
    });
    seqno = seqno.substr(0, seqno.length - 1);

    var url = "/proc/calcul_mng/sales_tab/";
    if (dvs === "cashreceipt") {
        url += "proc_public_cashreceipt.php";

        cashreceipt_data.seqno = seqno;
        data = cashreceipt_data;

        callback = function(result) {
            var downUrl  = "/common/excel_file_down.php?file_dvs=" + result + "&name=" + result;
            $("#file_ifr").attr("src", downUrl);
            loadCashreceiptList.exec();
        };
    } else if (dvs === "complete") {
        url += "proc_public_complete.php";

        complete_data.seqno = seqno;
        data = complete_data;

        callback = function(result) {
            var downUrl  = "/common/excel_file_down.php?file_dvs=" + result + "&name=" + result;
            $("#file_ifr").attr("src", downUrl);
            loadPublicCompleteList.exec();
        };
    } else {
        url += "proc_public_.php";

        except_data.seqno = seqno;
        data = cashreceipt_data;
    }

    console.log(data);

    ajaxCall(url, "text", data, callback);
}

//발행구분 변경
var changePublicDvs = function(val) {
    //세금계산서일때
    if (val == "세금계산서") {
	$("#tax_invoice").show();
	$("#cashreceipt").hide();
    //현금영수증일때
    } else if (val == "현금영수증") {
	$("#tax_invoice").hide();
	$("#cashreceipt").show();
    //미발행일때
    } else {
	$("#tax_invoice").hide();
	$("#cashreceipt").hide();
    }
}

