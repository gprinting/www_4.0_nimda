/**
 * @brief 판매채널 변경시 팀/담당자 변경
 *
 * @param val = 판매채널 일련번호
 */
var changeCpnAdmin = function(seqno) {
    var url = "/json/business/order_mng/load_depar_info.php";
    var data = {
        "seqno" : seqno
    };
    var callback = function(result) {
    $("#depar").html(result.depar);
        $("#empl").html(result.empl);
        $("#crm_info_depar").html(result.depar);
        $("#crm_info_empl").html(result.empl);
    };

    ajaxCall(url, "json", data, callback);
};

/**
 * @brief 부분 마스크 show
 * @comment 현재 전체 마스크 show로 변경됨(17.08.29)
 * @param param  = 설정값 파라미터
 * @detail 설정값 상세
 * {
 *   id     = 부분 마스크 div id, *필수
 *   width  = 부분 마스크 div width, *필수
 *   height = 부분 마스크 table height
 *   top    = 부분 마스크 div top
 * }
 *
var showLoadingMask = function(param) {
    var id     = param["id"];
    var width  = param["width"];
    var height = param["height"];
    var top    = param["top"];

    if (!checkBlank(width)) {
        $('#' + id).show().css("width", width + "px");
    }
    if (!checkBlank(height)) {
        $('#' + id + ">table").attr("height", height);
    }
    if (!checkBlank(top)) {
        $('#' + id).css("top", top + "px");
    }
};*/


/**
 * @brief 비동기 여러번 실행시 로딩마스크 닫는 공통 함수
 *
 * @param stack = 비동기 콜백 스택 수
 * @param callback = 마스크 닫고 실행할 함수
 */
var hideMaskByAjaxStack = function(stack, callback) {
    if (stack > 0) {
        return false;
    }

    hideMask();

    if (!checkBlank(callback)) {
        callback();
    }
};