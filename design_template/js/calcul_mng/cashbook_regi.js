
$(document).ready(function() {

    $(".date").datepicker({
        format         : "yyyy-mm-dd",
        autoclose      : true,
        todayBtn       : "linked",
        todayHighlight : true,
        language       : "kr"
    }).datepicker("setDate", '0');
    activeDate();
    loadCashbookList(1);

    if(event.keyCode == 9) {
        alert('aa');
        if($("#price").is(':focus')) {
            $("#sumup").focus();
        }
    }

});
var etprs_seqno = "";
var search_etprs_seqno = "";
var member_seqno = "";
var search_member_seqno = "";
var page = 1; //페이지
var list_num = 30; //리스트 갯수
var edit_yn = "Y";
var cashbook_seqno = "";

//금전출납 리스트 불러오기
var loadCashbookList = function(pg) {

    var formData = new FormData($("#search_form")[0]);
    formData.append("page", pg);
    formData.append("acc_subject", $("#search_acc_subject option:selected").text());
    formData.append("acc_subject_detail", $("#search_acc_subject_detail option:selected").text());
        formData.append("list_num", list_num);
/*
    if ($("#search_etprs_name").val() != "") {

        if (!search_etprs_seqno) {

            alert("제조사명을 엔터 후 선택해주세요");
        }

        formData.append("etprs_seqno", search_etprs_seqno);

    } else {

        search_etprs_seqno = "";
    }



    if ($("#search_office_nick").val() != "") {

        if (!search_member_seqno) {

            alert("회원명을 엔터 후 선택해주세요");
        }

        formData.append("member_seqno", search_member_seqno);

    } else {

        search_member_seqno = "";

    }
*/
    $.ajax({

        type: "POST",
        data: formData,
	    processData : false,
	    contentType : false,
        url: "/ajax/calcul_mng/cashbook_regi/load_cashbook_list.php",
        success: function(result) {
		var list = result.split('♪♭@');
            if($.trim(list[0]) == "") {

                $("#cashbook_list").html("<tr><td colspan='12'>검색된 내용이 없습니다.</td></tr>"); 
		        $("#income_sum").html("0원");
		        $("#expen_sum").html("0원");
		        $("#trsf_income_sum").html("0원");
		        $("#trsf_expen_sum").html("0원");
	        } else {
                $("#cashbook_list").html(list[0]);
                $("#cashbook_page").html(list[1]); 
                $('select[name=list_set]').val(list_num);
		        $("#income_sum").html(list[2]);
		        $("#expen_sum").html(list[3]);
		        $("#trsf_income_sum").html(list[4]);
		        $("#trsf_expen_sum").html(list[5]);
	        }
        }, 
        error: getAjaxError
    });
}

//금전출납부 수정 데이터 셋팅
var editCashbook = function(cashbook_seq
			               ,sell_site
			               ,dvs
                           ,price
			               ,sumup
			               ,acc_subject_seq
			               ,acc_detail_seq
			               ,regi_date
			               ,evid_date
			               ,depo_path
			               ,depo_path_detail
			               ,member_seq
			               ,member_name
			               ,manu_seq
			               ,manu_name
                           ,card_cpn
                           ,card_num
                           ,mip_mon
                           ,aprvl_num
                           ,aprvl_date) {

    $("#save_btn").text("저장");
    $("#del_btn").show();
    $("#edit_btn").show();

    edit_yn = "Y";
    cashbook_seqno = cashbook_seq;

    $("#sell_site").val(sell_site);

    $("input:radio[name='dvs']:radio[value='" + dvs + "']").prop("checked",true);
    $("#acc_subject").val(acc_subject_seq);

    //계정상세 데이터가 있을때
    if (acc_detail_seq != "") {
    	loadAccDetail('#acc_subject', '1', acc_detail_seq);
    } else {
	    $('#acc_subject_detail').html("<option value=\"\">선택</option>");
    }

    $("#regi_date").val(regi_date);
    $("#evid_date").val(evid_date);
    $("#price").val(price);
    $("#sumup").val(sumup);
    $("#depo_path").val(depo_path);

    //입출금경로 데이터가 있을때
    if (depo_path_detail !="") {
    	loadPathDetail(depo_path_detail);
    } else {
	    $('#depo_path_detail').html("<option value=\"\">선택</option>");
    }

    member_seqno = member_seq;
    $("#office_nick").val(member_name);
    etprs_seqno = manu_seq;
    $("#etprs_name").val(manu_name);
    $("#card_cpn").val(card_cpn);
    $("#card_num").val(card_num);
    $("#mip_mon").val(mip_mon);
    $("#aprvl_num").val(aprvl_num);
    $("#aprvl_date").val(aprvl_date);

}

var downloadFileCashbook = function() {

    var formData = new FormData($("#search_form")[0]);
    formData.append("acc_subject", $("#search_acc_subject option:selected").text());
    formData.append("acc_subject_detail", $("#search_acc_subject_detail option:selected").text());

    var url = "/ajax/calcul_mng/cashbook_regi/";
    var data = null;
    // 데이터는 cndSearch의 데이터를 사용
    //data = cndSearch.data;
    // 추가로 다운로드 url을 붙임
    url += "down_excel_cashbook.php";

    $.ajax({
        type     : "POST",
        url      : url,
        processData : false,
        contentType : false,
        data     : formData,
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


//보여 주는 페이지 갯수 설정
var showPageSetting = function(val) {
    list_num = val;
    loadCashbookList(1);
} 

//선택 조건으로 검색(페이징 클릭)
var searchResult = function(pg) {
    page = pg;
    loadCashbookList(page);
}

//달력 활성화
var activeDate = function() {
    //일자별 검색 datepicker 기본 셋팅
    $('#evid_date').datepicker({
        autoclose:true,
        format: "yyyy-mm-dd",
        todayBtn: "linked",
        todayHighlight: true
    });   

    //일자별 검색 datepicker 기본 셋팅
    $('#aprvl_date').datepicker({
        autoclose:true,
        format: "yyyy-mm-dd",
        todayBtn: "linked",
        todayHighlight: true
    });   
}

//계정과목 상세 불러오기
var loadAccDetail = function(el, dvs, detail) {
    //계정과목 미선택시
    if ($(el).val() == "") {
	    $(el + "_detail").html("<option value=\"\">선택</option>");
	    return false;
    }

    $.ajax({
        type: "POST",
        data: {
            "name" : $(el + " option:selected").text(),
	        "acc_subject" : $(el).val(),
            	"dvs"         : dvs
        },
        url: "/ajax/calcul_mng/cashbook_regi/load_acc_detail.php",
        success: function(result) {
	    
	    	$(el + "_detail").html(result);
		    //계정과목 상세정보가 있을때
		    if (detail != "N") {

			    $("#acc_subject_detail").val(detail);

		    }
        }, 
        error: getAjaxError
    });

}

//입출금경로 상세 불러오기
var loadPathDetail = function(detail) {

    //입출금경로 미선택시
    if ($("#depo_path").val() == "") {

        $("#depo_path_detail").html("<option value=\"\">선택</option>");
        return false;

    }

    $.ajax({

        type: "POST",
        data: {
		    "depo_path" : $("#depo_path").val()
        },
        url: "/ajax/calcul_mng/cashbook_regi/load_path_detail.php",
        success: function(result) {
	    
	    	$("#depo_path_detail").html(result);
		    //입출금경로 상세정보가 있을때
		    if (detail != "N") {

			    $("#depo_path_detail").val(detail);

		    }
        }, 
        error: getAjaxError
    });
}

var loadPathDetail2 = function(detail) {

    //입출금경로 미선택시
    if ($("#search_depo_path").val() == "") {

        $("#search_depo_path_detail").html("<option value=\"\">선택</option>");
        return false;

    }

    $.ajax({

        type: "POST",
        data: {
            "depo_path" : $("#search_depo_path").val()
        },
        url: "/ajax/calcul_mng/cashbook_regi/load_path_detail.php",
        success: function(result) {

            $("#search_depo_path_detail").html(result);
            //입출금경로 상세정보가 있을때
            if (detail != "N") {

                $("#search_depo_path_detail").val(detail);

            }
        },
        error: getAjaxError
    });
}


//팝업창 검색 버튼 클릭 검색시
var clickSearchNick = function(event, search_str, dvs) {

    loadSearchNick(event, $("#search_pop").val(), "click");

}

//회원 사내 닉네임 가져오기
var loadSearchNick = function(event, search_str, dvs) {
    
    if (dvs != "click") {
        if (event.keyCode != 13) {
            return false;
        }
    }

    showMask();
 
    $.ajax({
            type: "POST",
            data: {
                "search_str" : search_str,
                "sell_site"  : $("#sell_site").val(),
		        "func"       : "searchNick"
            },
            url: "/ajax/calcul_mng/cashbook_regi/load_office_nick.php",
            success: function(result) {
                if (dvs == "") {

                    hideMask();
                    searchPopShow(event, 'loadSearchNick', 'clickSearchNick');

                } else {

                    hideMask();
                    showBgMask();

                }
                $("#search_list").html(result);
            }   
    });
}

//팝업창 검색 버튼 클릭 검색시
var clickSearchEtprs = function(event, search_str, dvs) {

    loadSearchEtprs(event, $("#search_pop").val(), "click");

}

//제조사 이름 가져오기
var loadSearchEtprs = function(event, search_str, dvs, func) {
    
    if (dvs != "click") {
        if (event.keyCode != 13) {
            return false;
        }
    }

    showMask();
 
    $.ajax({
            type: "POST",
            data: {
                "search_str" : search_str,
		        "func"       : "searchEtprs"
            },
            url: "/ajax/calcul_mng/cashbook_regi/load_etprs_name.php",
            success: function(result) {

                if (dvs != "select") {

                    hideMask();
                    searchPopShow(event, 'loadSearchEtprs', 'clickSearchEtprs');

                } else {

                    hideMask();
                    showBgMask();

                }
                $("#search_list").html(result);
            }   
    });
}

//팝업창 검색 버튼 클릭 검색시
var clickRegiNick = function(event, search_str, dvs) {

    loadRegiNick(event, $("#search_pop").val(), "click");

}

//회원 사내 닉네임 가져오기
var loadRegiNick = function(event, search_str, dvs) {
    
    if (dvs == "" || dvs == "select") {
        if (event.keyCode != 13) {
            return false;
        }
    }

    if (dvs == "new") {

        searchPopShow(event, 'loadRegiNick', 'clickRegiNick');
        return false;
        
    }

    showMask();
 
    $.ajax({
            type: "POST",
            data: {
                "search_str" : search_str,
                "sell_site"  : $("#sell_site").val(),
		        "func"       : "nick"
            },
            url: "/ajax/calcul_mng/cashbook_regi/load_office_nick.php",
            success: function(result) {
                if (dvs == "") {

                    hideMask();
                    searchPopShow(event, 'loadRegiNick', 'clickRegiNick');

                } else {

                    hideMask();
                    showBgMask();

                }
                $("#search_list").html(result);
            }   
    });
}

//팝업창 검색 버튼 클릭 검색시
var clickRegiEtprs = function(event, search_str, dvs) {

    loadRegiEtprs(event, $("#search_pop").val(), "click");

}

//제조사 이름 가져오기
var loadRegiEtprs = function(event, search_str, dvs) {
    
    if (dvs == "" || dvs == "select") {
        if (event.keyCode != 13) {
            return false;
        }
    }

    if (dvs == "new") {

        searchPopShow(event, 'loadRegiEtprs', 'clickRegiEtprs');
        return false;
        
    }

    showMask();
 
    $.ajax({
            type: "POST",
            data: {
                "search_str" : search_str,
		        "func"       : "etprs"
            },
            url: "/ajax/calcul_mng/cashbook_regi/load_etprs_name.php",
            success: function(result) {

                if (dvs != "select") {

                    hideMask();
                    searchPopShow(event, 'loadRegiEtprs', 'clickRegiEtprs');

                } else {

                    hideMask();
                    showBgMask();

                }
                $("#search_list").html(result);
            }   
    });
}

//팝업 검색된 종이명 클릭시
var etprsClick = function(seq, name) {

    etprs_seqno = seq;
    $("#etprs_name").val(name);
    hideRegiPopup();

}

//팝업 검색된 종이명 클릭시
var nickClick = function(seq, name) {

    member_seqno = seq;
    $("#office_nick").val(name);
    hideRegiPopup();

}

//팝업 검색된 종이명 클릭시
var searchEtprsClick = function(seq, name) {

    search_etprs_seqno = seq;
    $("#search_etprs_name").val(name);
    hideRegiPopup();

}

//팝업 검색된 종이명 클릭시
var searchNickClick = function(seq, name) {

    search_member_seqno = seq;
    $("#search_office_nick").val(name);
    hideRegiPopup();

}

//금전출납등록
var saveCashbook = function() {

    //증빙일자가 없을때
    if ($("#evid_date").val() == "") {

        alert("증빙일자를 입력해주세요.");
        return false;
    }

    //금액이 없을때
    if ($("#price").val() == "") {

        alert("금액을 입력해주세요.");
	$("#price").focus();
        return false;
    }

    var formData = new FormData($("#regi_form")[0]);
/*
    if ($("#etprs_name").val() != "") {

        if (!etprs_seqno) {

            alert("제조사명을 엔터 후 선택해주세요");
        }

        formData.append("etprs_seqno", etprs_seqno);

    } else {

	etprs_seqno = "";

    }

    if ($("#office_nick").val() != "") {

        if (!office_nick) {

            alert("회원명을 엔터 후 선택해주세요");
        }

        formData.append("member_seqno", member_seqno);

    } else {

	member_seqno = "";

    }
 */
    if (edit_yn == "Y") {

        formData.append("cashbook_seqno", cashbook_seqno);

    } else {

	cashbook_seqno = "";

    }

    $.ajax({
        type: "POST",
        data: formData,
	processData : false,
	contentType : false,
        url: "/proc/calcul_mng/cashbook_regi/proc_cashbook.php",
        success: function(result) {
	console.log("aaaaaaaa=", result);
            if($.trim(result) == "1") {

                alert("저장했습니다.");
            	loadCashbookList(page);
            	resetRegiForm();

            } else if ($.trim(result) == "3"){

                alert("일 마감된 증빙 날짜입니다.");

            } else if ($.trim(result) == "4"){

                alert("잔액 데이터가 없습니다.");

            } else if ($.trim(result) == "5"){

                alert("즉시수금 선택시 회원을 필수로 등록하셔야 합니다.");

            } else {

                alert("실패했습니다.");

            }
        },
        //error: getAjaxError
    });
}

//금전출납부 삭제
var delCashbook = function() {
    
    $.ajax({
            type: "POST",
            data: {
                "cashbook_seqno" : cashbook_seqno,
            },
            url: "/proc/calcul_mng/cashbook_regi/delete_cashbook.php",
            success: function(result) {

                if($.trim(result) == "1") {
                    alert("삭제했습니다.");
                    loadCashbookList(page);
                    resetRegiForm();
                } else {
                    alert("삭제에 실패했습니다.");
                }
            }   
    });
}

//등록 폼 초기화
var resetRegiForm = function() {

    document.regi_form.reset();
    $("#acc_subject_detail").html("<option value=\"\">선택</option>");
    $("#depo_path_detail").html("<option value=\"\">선택</option>");
    edit_yn = "N";
    $("#save_btn").text("신규등록");
    $("#del_btn").hide();
    $("#edit_btn").hide();

}

//사내 닉네임 인풋창에서 삭제
var removeOfficeNick = function() {

    $("#office_nick").val('');

}

//제조사명 삭제
var removeEtprsName = function() {

    $("#etprs_name").val('');

}
