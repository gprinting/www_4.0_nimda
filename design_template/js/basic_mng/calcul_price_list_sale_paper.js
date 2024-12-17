/**
 * @brief 수량별 종이 할인 카테고리 변경시 
 * 카테고리에 물려있는 종이랑 사이즈 정보 검색
 *
 * @param cateSortcode = 카테고리 분류코드
 */
var initDetailInfo = function(cateSortcode) {
    if (checkBlank(cateSortcode)) {
        $("#sale_paper_name").html("<option value=\"\">종이명</option>");
        $("#sale_size_typ").html("<option value=\"\">사이즈유형(전체)</option>");
	initSelect();

        return false;
    }

    var url = "/json/basic_mng/calcul_price_list/load_paper_stan.php";
    var data = {
        "sell_site"     : $("#sale_sell_site").val(),
        "cate_sortcode" : cateSortcode
    };
    var callback = function(result) {
        $("#sale_paper_name").html(result.paper);
        $("#sale_size_typ").html(result.size_typ);
	initSelect();

        $("#sale_min_amt").html("<option value=\"\">최소수량</option>");
        $("#sale_min_amt").append(result.amt);
        $("#sale_max_amt").html("<option value=\"\">최대수량</option>");
        $("#sale_max_amt").append(result.amt);
    };

    ajaxCall(url, "json", data, callback);
};

/**
 * @brief 셀렉트박스 초기화
 */
var initSelect = function() {
    $("#sale_output_size").html("<option value=\"\">전체</option>");
    $("#sale_paper_dvs").html("<option value=\"\">구분</option>");
    $("#sale_paper_color").html("<option value=\"\">색상</option>");
    $("#sale_paper_basisweight").html("<option value=\"\">평량</option>");
    $("#sale_min_amt").html("<option value=\"\">최소수량</option>");
    $("#sale_max_amt").html("<option value=\"\">최대수량</option>");
};
