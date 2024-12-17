$(document).ready(function() {

    loadVirtBaList(1);
    loadBankName();

});

var page = 1;
var list_num = 30;
var edit_virt_seqno = "";
var edit_member_seqno = "";

//가상계좌리스트 불러오기
var loadVirtBaList = function(pg) {

    showMask();

    var formData = new FormData($("#ba_form")[0]);
    page = pg;
    formData.append("page", page);
    formData.append("list_num", list_num);

    showMask();

    $.ajax({
        type: "POST",
        processData : false,
        contentType : false,
        data: formData,
        url: "/ajax/calcul_mng/virt_ba_list/load_virt_ba_refund_list.php",
        success: function(result) {
            var list = result.split('♪♭@');
            if($.trim(list[0]) == "") {

                $("#ba_list").html("<tr><td colspan='5'>검색된 내용이 없습니다.</td></tr>");

            } else {

                $("#ba_list").html(list[0]);
                $("#ba_page").html(list[1]);
                $('select[name=list_set]').val(list_num);

            }
            hideMask();
        },
        error: getAjaxError
    });
}

//가상계좌 저장
var saveVirtBa = function() {
    if($("#after_bank_account").val() == "") {
        alert("가상계좌를 발급받으세요.");
        return;
    }
    showMask();

    $.ajax({
        type: "POST",
        data: {
            "virt_ba_change_history_seqno"  : $("#virt_ba_change_history_seqno").val(),
            "member_seqno"  : $("#member_seqno").val(),
            "after_bank_name" : $("#after_bank_name").val(),
            "after_bank_account" : $("#after_bank_account").val(),
            "depo_name" : $("#depo_name").val(),
        },
        url: "/ajax/calcul_mng/virt_ba_list/save_virt_ba_change.php",
        success: function(result) {
            if ($.trim(result == "1")) {
                alert("저장했습니다.");
                loadVirtBaList(1);
                hideRegiPopup();

            } else {
                alert("저장에 실패했습니다.");

            }
            hideMask();
        },
        error: getAjaxError
    });

}

//예금주 인풋창 지우기
var removeBaMember = function() {

    edit_member_seqno = "";
    $("#pop_member_name").val('');

}

//팝업창 검색 버튼 클릭 검색시
var clickSearchName = function(event) {

    loadSearchName(event, $("#search_pop").val(), "click");

}

//회원명 가져오기
var loadSearchName = function(event) {

    showMask();
    showPopPopMask();

    $.ajax({
        type: "POST",
        data: {
            "sell_site"  : $("#pop_sell_site").val(),
            "search_str" : $("#search_mem").val()
        },
        url: "/ajax/calcul_mng/virt_ba_list/load_search_cnd.php",
        success: function(result) {

            $("#search_list").html(result);
            hideMask();
            hidePopPopMask();
            showBgMask();
            showPopMask();

        }
    });
}

//검색창 팝업 show
var popSearchMember = function(event) {

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
    html += "\n      <label for=\"search_mem\" class=\"con_label\">";
    html += "\n        Search : ";
    html += "\n        <input id=\"search_mem\" type=\"text\" class=\"search_btn fix_width180\" onkeydown=\"loadSearchName(event);\">";
    html += "\n        <button type=\"button\" class=\"btn btn-sm btn-info fa fa-search\" onclick=\"clickSearchName(event);\">";
    html += "\n        </button>";
    html += "\n      </label>";
    html += "\n      <hr class=\"hr_bd3\">";
    html += "\n      <div class=\"list_scroll fix_height120\" id=\"search_list\">";
    html += "\n      </div>";
    html += "\n    </div>";
    html += "\n  </div>";
    html += "\n</div>";

    openPopPopup(html, 440);
    showPopMask();

}

//보여 주는 페이지 갯수 설정
var showPageSetting = function(val) {

    list_num = val;
    loadVirtBaList(1);
}

//선택 조건으로 검색(페이징 클릭)
var searchResult = function(pg) {

    page = pg;
    loadVirtBaList(page);

}

//수정 팝업 검색된 회원명 클릭시
var editMemberClick = function(seq, name) {

    edit_member_seqno = seq;
    $("#pop_member_name").val(name);
    hidePopPopup();

}

//수정 팝업의 판매채널 변경시
var changeSellSite = function() {

    $("#pop_member_name").val("");
    edit_member_seqno = "";
    loadBankName();

}

//은행이름 가져오기
var loadBankName = function() {

    $.ajax({
        type: "POST",
        data: {
            "sell_site"  : $("#sell_site").val()
        },
        url: "/ajax/calcul_mng/virt_ba_list/load_bank_name.php",
        success: function(result) {
            $("#bank_name").html(result);
        }
    });
}

//회원 가상계좌 상세
var loadVirtBaRefundDetail = function(seq) {

    $.ajax({
        type: "POST",
        data: {
            "seq"  : seq
        },
        url: "/ajax/calcul_mng/virt_ba_list/save_virt_ba_refund.php",
        success: function(result) {
            if ($.trim(result == "1")) {
                alert("변경성공.");
                loadVirtBaList(1);
            } else {

                alert("변경실패.");

            }
        }
    });

}

var getVirtNum = function() {
    $.ajax({
        type: "POST",
        data: {
            "bank_name"  : $("#after_bank_name").val(),
            "member_seqno"  : $("#member_seqno").val()
        },
        url: "/ajax/calcul_mng/virt_ba_list/request_virt_ba_num.php",
        success: function(result) {
            var arr = JSON.parse(result);
            $("#after_bank_account").val(arr.accountNo);
        }
    });
}

//회원 가상계좌 삭제
var removeMemberVirtBa = function(seq) {

    $.ajax({
        type: "POST",
        data: {
            "virt_ba_admin_seqno" : seq
        },
        url: "/proc/calcul_mng/virt_ba_list/proc_member_virt_ba.php",
        success: function(result) {
            if (result == "1") {

                alert("삭제하였습니다.");
                loadVirtBaList(page);

            } else {

                alert("삭제에 실패하였습니다.");


            }
        }
    });
}

