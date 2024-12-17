$(document).ready(function() {

    $(".date").datepicker({
        format         : "yyyy-mm-dd",
        autoclose      : true,
        todayBtn       : "linked",
        todayHighlight : true,
        language       : "kr"
    }).datepicker("setDate", '0');
    loadDvsDetailList();


    $("#deal_date").datepicker("setDate", new Date());

});
var page = "1";
var list_num = "30";
var adjust_seqno = "";
var member_seqno = "";

//입력 구분 상세 리스트
var loadDvsDetailList = function() {
 
    $.ajax({
            type: "POST",
            data: {
                "dvs" : $("#dvs").val(),
            },
            url: "/ajax/calcul_mng/adjust_regi/load_dvs_detail.php",
            success: function(result) {
                $("#dvs_detail").html(result);
            }   
    });
}

//조정 등록
var saveAdjust = function() {

    member_seqno = $("#member_seqno").val();
    //회원 일련번호가 없을때
    if (member_seqno == "") {

        alert("사내 닉네임을 검색 후 선택해주세요");
        $("#office_nick").focus();
        
        return false;
    }

    //거래일자가 없을때
    if ($("#deal_date").val() == "") {
        alert("거래일자를 입력해주세요.");
        return false;
    }

    //금액이 없을때
    if ($("#price").val() == "") {
        alert("금액을 입력해주세요.");
	    $("#price").focus();
        return false;
    }

    if ($("#dvs").val() == "차감") {
        price = $("#price").val() * -1;
        $("#price").val(price);
    }

    var formData = new FormData($("#adjust_form")[0]);
        formData.append("member_seqno", member_seqno);
        formData.append("sell_site", $("#sell_site").val());


    $.ajax({
        type: "POST",
        data: formData,
	    processData : false,
	    contentType : false,
        url: "/proc/calcul_mng/adjust_regi/regi_adjust.php",
        success: function(result) {
        console.log(result);
            if($.trim(result) == "1") {
                alert("저장했습니다.");
            	loadAdjustList(page);
            	resetRegiForm();
            } else if ($.trim(result) == "3"){
                alert("일 마감된 거래 날짜입니다.");
            } else if ($.trim(result) == "4") {
                alert("거래일자가 현재일자보다 클 수 없습니다.")
            } else {
                alert("실패했습니다.");
            }
        },
        error: getAjaxError
    });
}

//회원명 가져오기
var searchOfficeNick = function(event, val, dvs) {
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
        if (dvs !== "select") {
            searchPopShow(event, "loadOfficeNick", "loadOfficeNick");
        } else {
            showBgMask();
        }

        $("#search_list").html(result);
    };

    ajaxCall(url, "html", data, callback);

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
        if (dvs !== "select") {
            searchPopShow(event, "loadOfficeNick", "loadOfficeNick");
        } else {
            showBgMask();
        }

        $("#search_list").html(result);
    };

    ajaxCall(url, "html", data, callback);

}

//팝업 검색된 인쇄명 클릭시
var nameClick = function(seqno, name, sell_site) {
    $("#office_nick").val(name);
    $("#member_seqno").val(seqno);
    $("#sell_site").val(sell_site);
    hideRegiPopup();
};

//조정 리스트
var loadAdjustList = function(pg) {

    page = pg;

    //회원명 인풋창 비었을경우  회원일련번호 초기화
    if (checkBlank($("#office_nick").val())) {
        $("#member_seqno").val("");
    }

    if (checkBlank($("#member_seqno").val()) && !checkBlank($("#office_nick").val())) {
        alert("검색창 팝업을 이용하시고 검색해주세요.");
        $("#office_nick").focus();
        return false;
    }

    if (checkBlank($("#member_seqno").val()) || checkBlank($("#office_nick").val())) {
        member_seqno = "";
        alert("사내닉네임을 검색해주세요");
        $("#office_nick").focus();
        return false;

    }
    member_seqno = $("#member_seqno").val();

    var formData = new FormData($("#adjust_form")[0]);
        formData.append("page", pg);
        formData.append("list_num", list_num);
        formData.append("member_seqno", member_seqno);
        formData.append("sell_site", $("#sell_site").val());

    $.ajax({

        type: "POST",
        data: formData,
	    processData : false,
	    contentType : false,
        url: "/ajax/calcul_mng/adjust_regi/load_adjust_list.php",
        success: function(result) {
		var list = result.split('♪♭@');
            if($.trim(list[0]) == "") {

                $("#adjust_list").html("<tr><td colspan='5'>" +
                    "검색된 내용이 없습니다.</td></tr>"); 

	    } else {

                $("#adjust_list").html(list[0]);
                $("#adjust_page").html(list[1]); 
                $('select[name=list_set]').val(list_num);

	    }
        }, 
        error: getAjaxError
    });
}

//조정 리스트 상세보기
var loadAdjustDetail = function(cont
                               ,dvs
                               ,dvs_detail
                               ,deal_date
                               ,price
                               ,adjust_seqno) {

    $("#cont").val(cont);
    $("#dvs").val(dvs);
    $("#dvs_detail").html("<option>" + dvs_detail + "</option>");
    
    $("#deal_date").val(deal_date);
    price = price.replace(/,/g,"").replace("-","");
    $("#price").val(price);
    $("#adjust_seqno").val(adjust_seqno);

    $("#save_btn").text("수정");
    $("#del_btn").show();
    $("#cancle_btn").show();
}

var delAdjust = function() {

    adjust_seqno = $("#adjust_seqno").val();
    $.ajax({
        type: "POST",
        data: {
            "adjust_seqno" : adjust_seqno
        },
        url: "/ajax/calcul_mng/adjust_regi/delete_adjust_item.php",
        success: function(result) {

            if($.trim(result) == "1") {
                alert("삭제했습니다.");
                loadAdjustList(page);
                resetRegiForm();
            } else {
                alert("삭제에 실패했습니다.");
            }
        }
    });
}

//보기취소버튼 클릭시 등록으로 초기화
var resetRegiForm = function() {
    document.adjust_form.reset();

    $("#save_btn").text("저장");
    $("#cancle_btn").hide();
    $("#del_btn").hide();
    $("#deal_date").datepicker("setDate", new Date());
    $("#adjust_seqno").val("");
    loadDvsDetailList();
}

//보여 주는 페이지 갯수 설정
var showPageSetting = function(val) {

    list_num = val;
    loadAdjustList(1);
} 

//선택 조건으로 검색(페이징 클릭)
var searchResult = function(pg) {

    page = pg;
    loadAdjustList(page);
}


