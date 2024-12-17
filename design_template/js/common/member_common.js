//검색날짜 범위 설정
var detailDateSet = function(num, dvs) {
    var day = new Date();
    var time = day.getHours();
    var d_day = new Date(day - (num * 1000 * 60 * 60 * 24));
    var last = new Date(day - (365 * 1000 * 60 * 60 * 24));

    if (num === "all") {
        $("#date_" + dvs + "_from").val("");
        $("#time_" + dvs + "_from").val("0");
        $("#date_" + dvs + "_to").val("");
        $("#time_" + dvs + "_to").val("0");

        return false;
    } else if (num === "last") {
        $("#date_" + dvs + "_from").datepicker("setDate", last);
        $("#time_" + dvs + "_from").val("0");
        $("#date_" + dvs + "_to").datepicker("setDate", last);
        $("#time_" + dvs + "_to").val(time);
        return false;
    }

    $("#date_" + dvs + "_from").datepicker("setDate", d_day);
    $("#time_" + dvs + "_from").val("0");
    $("#date_" + dvs + "_to").datepicker("setDate", '0');
    $("#time_" + dvs + "_to").val(time);
}

/**
* @brief 웹로그인
*/
var webLogin = function(seqno) {
    var url = "/ajax/business/order_hand_regi/load_admin_hash.php";
    var callback = function(result) {
        window.open("http://new.gprinting.co.kr/common/login.php?seqno=" + seqno + "&flag=" + result.trim()+ "&isadmin=Y", "_blank");
        //window.open("http://devfront.goodprinting.co.kr/common/login.php?seqno=" + seqno + "&flag=" + result.trim()+ "&isadmin=Y", "_blank");
    };

    showMask();
    ajaxCall(url, "html", {}, callback);
}

/**
 * @brief 웹로그인
 */
var webLoginOrder = function(seqno) {
    var url = "/ajax/business/order_hand_regi/load_admin_hash.php";
    var callback = function(result) {
        window.open("http://new.gprinting.co.kr/common/login.php?seqno=" + seqno + "&flag=" + result.trim()+ "&isadmin=Y&mode=order", "_blank");
        //window.open("http://devfront.goodprinting.co.kr/common/login.php?seqno=" + seqno + "&flag=" + result.trim()+ "&isadmin=Y", "_blank");
    };

    showMask();
    ajaxCall(url, "html", {}, callback);
}

//회원 상세보기
var showDetail = function(seqno) {
    $("#seqno").val(seqno);	
    var f = document.frm;
    window.open("", "POP")
    f.action = "/member/popup/pop_member_detail.html";
    //f.action = "/member/member_common_popup.html";
    f.target = "POP";
    f.method = "POST";
    f.submit();
    return false; 
}
