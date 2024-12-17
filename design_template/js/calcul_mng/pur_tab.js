/*
 *
 * Copyright (c) 2015 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2016/06/15 왕초롱 생성
 *=============================================================================
 *
 */
$(document).ready(function() {
    loadPurTabList.exec();
    activeDate();

});

var list_page = "1";
var list_num = "30";

//달력 활성화
var activeDate = function() {

    $('#write_date').datepicker({
        autoclose:true,
        format: "yyyy-mm-dd",
        todayBtn: "linked",
        todayHighlight: true
    });
}

//매입계산서
var loadPurTabList = {
    "exec"       : function(listSize, page) {
        if(listSize){
            list_num = listSize;
        } 
        if(page) {
            list_page = page;
        }

        if($("#start_year").val() > $("#end_year").val()) {
            alert("Error : 기간설정");
            return;
        }

        if(($("#start_year").val() == $("#end_year").val()) && ($("#start_mon").val() > $("#end_mon").val())) {
            alert("Error : 기간설정");
            return;
        }

        var url = "/ajax/calcul_mng/pur_tab/load_pur_tab_list.php";
        var blank = "<tr><td colspan=\"7\">검색 된 내용이 없습니다.</td></tr>";
        var data = {
            "start_year"         : $("#start_year").val(),
            "start_mon"          : $("#start_mon").val(),
            "end_year"         : $("#end_year").val(),
            "end_mon"          : $("#end_mon").val(),
            "sell_site"    : $("#sell_site").val()
        };

        var callback = function(result) {
            var rs = result.split("♪♭@");
            if (rs[0].trim() == "") {
                $("#pur_list").html(blank);
                $("#etprs_cnt").html(rs[2]);
                $("#sales_total").html(rs[3]);
                $("#supply_total").html(rs[4]);
                $("#vat_total").html(rs[5]);
                return false;
            }

            $("#pur_list").html(rs[0]);
            $("#page").html(rs[1]);
            $("#etprs_cnt").html(rs[2]);
            $("#sales_total").html(rs[3]);
            $("#supply_total").html(rs[4]);
            $("#vat_total").html(rs[5]);
        };

        data.search   = $("#search").val();
        data.listSize      = list_num;
        data.page          = list_page;

        showMask();
        ajaxCall(url, "html", data, callback);

        resetPurTab();
    }
}

/**
 * @brief 매입업체 클릭
 */
var nameClick = function(seqno, name, crn, repre, addr, bc, tob) {

    $("#manu_name").val(name);
    $("#crn").val(crn);
    $("#repre_name").val(repre);
    $("#addr").val(addr);
    $("#bc").val(bc);
    $("#tob").val(tob);
    $("#extnl_etprs_seqno").val(seqno);

    hideRegiPopup();

}

/**
 * @brief  매입업체 검색할 때 사용하는 함수
 */
var searchEtprsName = function(event, val, dvs) {
    if (event.keyCode != 13) {
        return false;
    }

    var url = "/ajax/calcul_mng/pur_tab/load_etprs_name.php";
    var data = {
        "sell_site"  : $("#sell_site").val(),
        "search_val" : val
    };
    var callback = function(result) {
        if (dvs !== "select") {
            searchPopShow(event, "searchEtprsName", "searchEtprsName");
        } else {
            showBgMask();
        }

        $("#search_list").html(result);
    };

    ajaxCall(url, "html", data, callback);
};

/**
 * @brief  매입계산서 등록
 */
var savePurTab = function() {

    if ($("#write_date").val() == "") {

        alert("작성년월을 입력해주세요");
        return false;

    }

    if ($("#supply_price").val() == "") {

        alert("공급가액을 입력해주세요");
        return false;

    }

    supply_price = ($("#supply_price").val()).replace(/,/g, "");
    $("#supply_price").val(supply_price);

    vat = ($("#vat").val()).replace(/,/g, "");
    $("#vat").val(vat);

    $("#purForm").find(":input:disabled").removeAttr('disabled');
    var formData = $("#purForm").serialize();
    var url = "/proc/calcul_mng/pur_tab/regi_pur_tab.php";
    var data = formData;
    var callback = function(result) {
        console.log(result);
        if (result == 1) {
            alert("등록하였습니다.");
            loadPurTabList.exec();
            $("form")[0].reset();
        } else {
            alert("등록에 실패하였습니다.");
            $("form")[0].reset();
        }

    };

    ajaxCall(url, "html", data, callback);
};

//보여줄 페이지 수 설정
var showPageSetting = function(val) {
    loadPurTabList.exec(val, 1);
}

var editPurTab = function (val) {
    var url = "/ajax/calcul_mng/pur_tab/load_pub_tab_item.php";
    var data = {
        "pur_tab_seqno" : val
    };
    var callback = function(result) {
        var rs = result.split("♪♭@");

        $("#write_date").val(rs[0]);
        $("#item").val(rs[1]);
        $("#supply_price").val(rs[2]);
        $("#vat").val(rs[3]);
        $("#manu_name").val(rs[4]);
        $("#crn").val(rs[5]);
        $("#repre_name").val(rs[6]);
        $("#addr").val(rs[7]);
        $("#bc").val(rs[8]);
        $("#extnl_etprs_seqno").val(rs[9]);
        $("#tob").val(rs[10]);
        $("#pur_tab_seqno").val(rs[11]);

        $("#save_btn").text("저장");
        $("#del_btn").show();
        $("#edit_btn").show();
    };
    ajaxCall(url, "html", data, callback);
}


var delPurTab = function () {
    var val = $("#pur_tab_seqno").val();

    var url = "/ajax/calcul_mng/pur_tab/delete_purtab.php";
    var data = {
        "pur_tab_seqno" : val
    };
    var callback = function(result) {
        if($.trim(result) == "1") {
            alert("삭제했습니다.");
            loadPurTabList.exec();
        } else {
            alert("삭제에 실패했습니다.");
        }
    };
    ajaxCall(url, "html", data, callback);
}



//등록 폼 초기화
var resetPurTab = function() {

    document.purForm.reset();
    //edit_yn = "N";
    $("#save_btn").text("신규등록");
    $("#del_btn").hide();
    $("#edit_btn").hide();
    $("#pur_tab_seqno").val("");
    $("#extnl_etprs_seqno").val("");
}

var calculate_tax = function(val) {
    vat = $("#supply_price").val();
    vat = vat.replace(/,/g, "");
    vat /= 10;
    $("#vat").val(vat.format());

    supply_price = ($("#supply_price").val()).replace(/,/g, "");
    $("#supply_price").val(supply_price.format());


}

//매입페이지 이동
var movePage = function(val) {
    loadPurTabList.exec(list_num, val);
}

var enterCheck = function(event) {

    if(event.keyCode == 13) {
        loadPurTabList.exec();
    }

}
