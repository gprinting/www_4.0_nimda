$(document).ready(function() {
    dateSet('0'); 
    searchProcess(30, 1);
});

//보여줄 페이지 수
var showPage = 30;

//공정확인리스트 
var searchProcess = function(showPage, page) {

    var url = "/ajax/business/order_process_view/load_order_process_view.php";
    var blank = "<tr><td colspan=\"12\">검색 된 내용이 없습니다.</td></tr>";
    var data = { 
        "state"       : $("#state").val(),
        "cate_top"    : $("#cate_top").val(),
        "cate_mid"    : $("#cate_mid").val(),
        "cate_bot"    : $("#cate_bot").val(),
        "sell_site"   : $("#sell_site").val(),
        "state"       : $("#state").val(),
        "search_cnd"  : $("#search_cnd").val(),
        "search_txt"  : $("#search_txt").val(),
        "date_cnd"    : $("#date_cnd").val(),
        "date_from"   : $("#date_from").val(),
        "date_to"     : $("#date_to").val(),
        "time_from"   : $("#time_from").val(),
        "time_to"     : $("#time_to").val()
    };

    var callback = function(result) {
        var rs = result.split("♪");
        if (rs[0].trim() == "") {
            $("#list").html(blank);
            return false;
        }
        $("#list").html(rs[0]);
        $("#page").html(rs[1]);
    };

    data.showPage      = showPage;
    data.page          = page;

    showMask();
    ajaxCall(url, "html", data, callback);
}

//상품리스트 페이지 이동
var movePage = function(val) {
    searchProcess(showPage, val);
}
