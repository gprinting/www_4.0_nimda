/*
 *
 * Copyright (c) 2015 Nexmotion, Inc.
 * All rights reserved.
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2015/10/28 임종건 생성
 * 2015/11/20 임종건 팝업 공통 추가
 *=============================================================================
 *
 */

$(document).ready(function() {

    //일자별 검색 datepicker 기본 셋팅
    $("#date_from").datepicker({
        autoclose:true,
        format: "yyyy-mm-dd",
        todayBtn: "linked",
        todayHighlight: true
    });

    $("#date_to").datepicker({
        autoclose:true,
        format: "yyyy-mm-dd",
        todayBtn: "linked",
        todayHighlight: true
    });

    $("#date").datepicker({
        autoclose:true,
        format: "yyyy-mm-dd",
        todayBtn: "linked",
        todayHighlight: true
    });
});

/*
//팝업 스크롤 내릴시 유지
$(function(){
    var lastScroll = 0;
    var height = $(window).height();

    $(window).scroll(function(event){
        var st = $(window).scrollTop();
        var objHeight = $('#regi_popup').outerHeight();
        var topVal = (height - objHeight)/2 + st;

        if (height < objHeight) {
            topVal = (height - objHeight)/3 + st;
        }

        $('#regi_popup').css({position:'absolute'}).css({
            top : topVal + "px",
        });
        $('#pop_popup').css({position:'absolute'}).css({
            top : topVal + "px",
        });

        lastScroll = st;
    });
});
*/

/* div 드래그 */
var img_L = 0;
var img_T = 0;
var targetObj;

function getLeft(o){
    return parseInt(o.style.left.replace('px', ''));
}
function getTop(o){
    return parseInt(o.style.top.replace('px', ''));
}

function moveDrag(e){
    var e_obj = window.event? window.event : e;
    var dmvx = parseInt(e_obj.clientX + img_L);
    var dmvy = parseInt(e_obj.clientY + img_T);
    targetObj.style.left = dmvx +"px";
    targetObj.style.top = dmvy +"px";
    return false;
}

function startDrag(e, obj){
    targetObj = obj;
    var e_obj = window.event? window.event : e;
    img_L = getLeft(obj) - e_obj.clientX;
    img_T = getTop(obj) - e_obj.clientY;

    document.onmousemove = moveDrag;
    document.onmouseup = stopDrag;
    if(e_obj.preventDefault)e_obj.preventDefault();
}

function stopDrag(){
    document.onmousemove = null;
    document.onmouseup = null;
}
/* div 드래그 */

//Ajax error 공통 함수
var getAjaxError = function(request,status,error) {
    alert("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
    console.log("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
    hideBgMask();
    hideMask();
}

/*
 * Ajax Call 공통 함수
 * 사용 예제 ajaxCall('호출주소', 'html', {data:data}, callback);
 *
 */
var ajaxCall  = function(url, dataType, data, sucCallback) {
    if (checkBlank(url)) {
        return false;
    }

    $.ajax({
        type     : "POST",
        url      : url,
        dataType : dataType,
        data     : data,
        success  : function(result) {
            hideMask();
            return sucCallback(result);
        },
        error: getAjaxError
    });
};

var ajaxCallMultipart = function(url, dataType, data, sucCallback) {
    if (checkBlank(url)) {
        return false;
    }

    $.ajax({
        type     : "POST",
        url      : url,
        dataType : dataType,
        data     : data,
        processData : false,
        contentType : false,
        success  : function(result) {
            hideMask();
            return sucCallback(result);
        },
        error: getAjaxError
    });
}

//래프트 메뉴
var midCheckLeft = function(el) {

    $("#mainnav-menu .mid").removeClass("active");
    $(el).addClass("active");
}

//어떤 값이 공백값이거나 undefined 값이면 false 반환
var checkBlank = function(val) {
   if (val === ""
           || val === ''
           || val === null
           || val === "null"
           || typeof val === "undefined") {
       return true;
   } else {
       return false;
   }
};

//해당 함수가 존재하는지 확인하고 존재할경우 true 반환
var isFunc = function(funcName) {
   if (typeof(window[funcName]) === "function") {
       return true;
   } else {
       return false;
   }
};

//해당 변수가 존재하는지 확인하고 존재할경우 true 반환
var isVar = function(varName) {
   if (typeof(window[varName]) === "undefined") {
       return false;
   } else {
       return true;
   }
};

//정열 초기화
var sortInit = function() {

    $(".sorting").children().removeClass("fa-sort-desc");
    $(".sorting").children().removeClass("fa-sort-asc");
    $(".sorting").children().addClass("fa-sort");
}

//경고메시지 width440 size show
var showMsg440 = function(msg) {

    showBgMask();

    var html = "";
    html += "\n     <dl> ";
    html += "\n         <dt class=\"tit\"> ";
    html += "\n               <h4>경고창</h4> ";
    html += "\n         </dt> ";
    html += "\n         <dt class=\"cls\"> ";
    html += "\n               <button type=\"button\" id=\"showMsg440\" class=\"btn btn-sm btn-danger fa fa-times\" onClick=\"hideMsg440();\"></button> ";
    html += "\n           </dt> ";
    html += "\n      </dl>   ";
    html += "\n      <div class=\"pop-base\"> ";
    html += "\n          <div class=\"pop-content tac\"> ";
    html += "\n              " + msg;
    html += "\n          </div> ";
    html += "\n      </div> ";

    $('#alert_msg_440').css({position:'absolute'}).css({
        left: ($(window).width() - $('#alert_msg_440').outerWidth())/2 + $(window).scrollLeft() + "px",
        top : ($(window).height() - $('#alert_msg_440').outerHeight())/2 + $(window).scrollTop() + "px"
    });

    $("#alert_msg_440").html(html);
    $("#showMsg440").focus();
    $("#alert_msg_440").show();
}

//경고메세지 width440 size hide
var hideMsg440 = function() {

    $("#alert_msg_440").hide();
    hideBgMask();
}

//로딩 중 이미지 보이기
var showMask = function() {

    showBgMask();

    $obj = $("#loading_img");
    $obj.css("position","absolute");
    $obj.css("top", Math.max(0, (($(window).height() - $obj.height()) / 2) + $(window).scrollTop()) + "px");
    $obj.css("left", Math.max(0, (($(window).width() - $obj.width()) / 2) + $(window).scrollLeft()) + "px");
    $("#loading_img").show();
}

//로딩 중 이미지 숨기기
var hideMask = function() {

    $("#loading_img").hide()
    hideBgMask();
}

//Background 마스크 show
var showBgMask = function() {

    var maskHeight = $(document).height();
    var maskWidth = $(window).width();

    //마스크의 높이와 너비를 화면 것으로 만들어 전체 화면을 채운다.
    $("#black_mask").css({'width':maskWidth,'height':maskHeight});
    $("#black_mask").show();
}

//Background 마스크 hide
var hideBgMask = function() {

    $("#black_mask").hide();
}

//Background 마스크 show
var showPopMask = function() {

    var maskHeight = $(document).height();
    var maskWidth = $(window).width();

    //마스크의 높이와 너비를 화면 것으로 만들어 전체 화면을 채운다.
    $("#pop_mask").css({'width':maskWidth,'height':maskHeight});
    $("#pop_mask").show();
}

//Background 마스크 hide
var hidePopMask = function() {

    $("#pop_mask").hide();
}

//Background 마스크 show
var showPopPopMask = function() {

    var maskHeight = $(document).height();
    var maskWidth = $(window).width();

    //마스크의 높이와 너비를 화면 것으로 만들어 전체 화면을 채운다.
    $("#pop_pop_mask").css({'width':maskWidth,'height':maskHeight});
    $("#pop_pop_mask").show();
}

//Background 마스크 hide - 팝업의 팝업에서 검색 했을 경우 마스크
var hidePopPopMask = function() {

    $("#pop_pop_mask").hide();
}

/**
 * @brief 팀 변경시 담당자 변경
 *
 * @param deparCode = 부서코드
 * @param prefix    = 영역별 접두사
 */
var changeDepar = function(deparCode, prefix) {
    if (checkBlank(deparCode)) {
        var html = "<option value=''>전체</option>";
        $('#' + prefix + "empl").html(html);
        return false;
    }

    var url = "/ajax/business/order_mng/load_empl_info.php";
    var data = {
        "depar_code" : deparCode
    };
    var callback = function(result) {
        $('#' + prefix + "empl").html(result);
    };

    ajaxCall(url, "html", data, callback);
};



//레이어 팝업 show
var popShow = function($obj) {

    $obj.css("position","absolute");
          $obj.css("top", Math.max(0, (($(window).height() - $obj.height()) / 2) + $(window).scrollTop()) + "px");
                $obj.css("left", Math.max(0, (($(window).width() - $obj.width()) / 2) + $(window).scrollLeft()) + "px");
    $obj.fadeIn();
}

//레이어 팝업 hide
var popHide = function($obj) {
    $obj.fadeOut();
}

//검색창 팝업 show
var searchPopShow = function(event, fn1, fn2) {

    var html = "";
    html += "\n  <dl>";
    html += "\n    <dt class=\"tit\">";
    html += "\n      <h4>검색창 팝업</h4>";
    html += "\n    </dt>";
    html += "\n    <dt class=\"cls\">";
    html += "\n      <button type=\"button\" onclick=\"hideRegiPopup();\" class=\"btn btn-sm btn-danger fa fa-times\">";
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
                     //<ul style="ofh">
                     //  <li onclick=\"selectResult('%s');\" style=\"cursor: pointer;\"></li>
                     //</ul>
    html += "\n      </div>";
    html += "\n    </div>";
    html += "\n  </div>";
    html += "\n</div>";

    openRegiPopup(html, 440);
    $("#search_pop").focus();
}

//검색창 팝업 show
var searchPopShow2 = function(event, fn1, fn2) {

    var html = "";
    html += "\n  <dl>";
    html += "\n    <dt class=\"tit\">";
    html += "\n      <h4>검색창 팝업</h4>";
    html += "\n    </dt>";
    html += "\n    <dt class=\"cls\">";
    html += "\n      <button type=\"button\" onclick=\"hideRegiPopup();\" class=\"btn btn-sm btn-danger fa fa-times\">";
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
                     //<ul style="ofh">
                     //  <li onclick=\"selectResult('%s');\" style=\"cursor: pointer;\"></li>
                     //</ul>
    html += "\n      </div>";
    html += "\n    </div>";
    html += "\n  </div>";
    html += "\n</div>";

    openRegiPopup(html, 440);
    $("#search_pop").focus();
}

//마우스로 클릭한 위치에 인풋박스 레이어 팝업 출력
var showInputPop = function(event, $obj) {
    var ua = window.navigator.userAgent;
    var _x = null;
    var _y = null;

    //마우스로 선택한곳의 x축(화면에서 좌측으로부터의 거리)를 얻는다.
    //브라우저가 IE일 경우
    if (ua.indexOf("Chrome") === -1) {
        _x = event.clientX + document.documentElement.scrollLeft;

    //브라우저가 크롬일 경우
    } else {
        _x = event.clientX + document.body.scrollLeft;
    }

    //마우스로 선택한곳의 y축(화면에서 상단으로부터의 거리)를 얻는다.
    //브라우저가 IE일 경우
    if (ua.indexOf("Chrome") === -1) {
        _y = event.clientY + document.documentElement.scrollTop;
    //브라우저가 크롬일 경우
    } else {
        _y = event.clientY + document.body.scrollTop;
    }

    //마우스로 선택한 위치의 값이 -값이면 0으로 초기화. (화면은 0,0으로 시작한다.)
    if(_x < 0) _x = 0;
    //마우스로 선택한 위치의 값이 -값이면 0으로 초기화. (화면은 0,0으로 시작한다.)
    if(_y < 0) _y = 0;

    //레이어팝업의 좌측으로부터의 거리값을 마우스로 클릭한곳의 위치값으로 변경.
    $obj.css("left", _x+"px");
    //레이어팝업의 상단으로부터의 거리값을 마우스로 클릭한곳의 위치값으로 변경.
    $obj.css("top", _y+"px");

    $obj.css("display", "block");
};

//인풋박스 레이어 닫기
var hideInputPop = function($obj) {
    $obj.css("display", "none");
};

//업로드 할 파일의 확장자 체크
var checkExt = function($obj) {
    var val = $obj.val();
        val = val.split('.');
    var ext = val[val.length - 1];

    if (ext !== "xlsx" && ext !== "xls") {
        alert("엑셀파일만 업로드 할 수 있습니다.");
        return false;
    }

    return true;
};

//옵션 html 생성
var makeOption =  function(str) {
    var html = "<option value=\"\" selected>" + str + "</option>";
    return html;
}

//검색 날짜 범위 설정
/*
var dateSet = function(num) {

    var day = new Date();
    var time = day.getHours();
    var d_day = new Date(day - (num * 1000 * 60 * 60 * 24));
    var last = new Date(day - (365 * 1000 * 60 * 60 * 24));

    //전체 범위 검색시 날짜 범위 초기화
    if (num == "last") {
        $("#date_from").datepicker("setDate", last);
        $("#time_from").val("0");
        $("#date_to").datepicker("setDate", last);
        $("#time_to").val(time);
    } else if (num == "all"){
        $("#date_from").val("");
        $("#time_from").val("0");
        $("#date_to").val("");
        $("#time_to").val("0");
    } else {
        $("#date_from").datepicker("setDate", d_day);
        $("#time_from").val("0");
        $("#date_to").datepicker("setDate", '0');
        $("#time_to").val(time);
    }
};
*/

var getPrefix = function(dvs) {
    if (checkBlank(dvs) === true) {
        return '#';
    } else {
        return '#' + dvs + '_';
    }
}

var padStr = function(pad, len, str) {
    str += '';
    var tmp = pad;
    for (var i = 1; i < len; i++) {
        pad += tmp;
    }

    return pad.substring(0, pad.length - str.length) + str;
}

/**
 * @function 검색 날짜 범위 설정
 * @modified 180130 이청산
 * @param num     시작 날짜
 *       ,numExt  끝 날짜
 *       ,flag    전주, 전월 플래그
 */
var dateSet = function(prefix, num, numExt, flag) {
    var day = new Date();
    var time = day.getHours();
    prefix = getPrefix(prefix);

    //전체 범위 검색시 날짜 범위 초기화
    if (num == "last") {
        var last = new Date(day - (365 * 1000 * 60 * 60 * 24));

        $(prefix + "from").datepicker("setDate", last);
        $(prefix + "to").datepicker("setDate", last);
    } else if (num == "all") {
        $(prefix + "from").val("");
        $(prefix + "to").val("");
    } else if (flag == "lw") {
        var d_day = new Date(day - (num * 1000 * 60 * 60 * 24));
        var e_day = new Date(day - (numExt * 1000 * 60 * 60 * 24));

        $(prefix + "from").datepicker("setDate", d_day);
        $(prefix + "to").datepicker("setDate", e_day);
    } else if (flag == "lm") {
        // 전달
        var year = day.getFullYear();
        var mon = day.getMonth();
        mon = (mon === 0) ? 12 : mon;
        mon = padStr('0', 2, mon);
        var date = "01";

        var e_day = new Date(year + '-' + mon + '-' + date); 
        e_day.setMonth(e_day.getMonth() + 1);
        e_day.setDate(e_day.getDate() - 1);


        var from = year + '-' + mon + '-' + date;
        var to = e_day.getFullYear() + '-'
                 + padStr('0', 2, e_day.getMonth() + 1) + '-'
                 + padStr('0', 2, e_day.getDate());
        $(prefix + "from").val(year + '-' + mon + '-' + date);
        $(prefix + "to").val(to);
    } else if (flag == "cm") {
        // 당월
        var year = day.getFullYear();
        var mon = day.getMonth() + 1;
        mon = padStr('0', 2, mon);
        var day = "01";

        $(prefix + "from").val(year + '-' + mon + '-' + day);
        $(prefix + "to").datepicker("setDate", '0');
    } else {
        var d_day = new Date(day - (num * 1000 * 60 * 60 * 24));

        $(prefix + "from").datepicker("setDate", d_day);
        $(prefix + "to").datepicker("setDate", '0');
    }
};

/**
 * @brief 카테고리 선택시 하위 카테고리 정보 검색
 *
 * @param cateType     = 카테고리 선택 구분(top, mid)
 * @param cateSortcode = 카테고리 분류코드
 * @param prefix       = 객체 id prefix값
 */
var cateSelect = {
    "type" : null,
    "exec" : function(cateType, cateSortcode, prefix, callback2 = () => {}) {
        var midId = "cate_mid";
        var botId = "cate_bot";
        if (checkBlank(prefix)) {
            midId = '#' +  midId;
            botId = '#' +  botId;
        } else {
            midId = '#' + prefix + '_' + midId;
            botId = '#' + prefix + '_' + botId;
        }

        //카테고리 분류코드가 빈값인 경우
        if (checkBlank(cateSortcode)) {
            var html = "";

            //카테고리 대분류
            if (cateType === "top") {
                html = makeOption("중분류(전체)");
                $(midId).html(html);
                html = makeOption("소분류(전체)");
                $(botId).html(html);
            } else if (cateType === "mid") {
                html = makeOption("소분류(전체)");
                $(botId).html(html);
            }

            // 상세 검색정보 초기화 하는 함수 있을경우 실행
            if (isFunc('resetDetailInfo') === true) {
                resetDetailInfo();
            }
            return false;
        }

        this.type = cateType;

        // 카테고리 대 변경시 카테고리 소 초기화
        if (cateType === "top") {
            html = makeOption("소분류(전체)");
            $(botId).html(html);
        }

        var url = "/ajax/common/load_cate_list.php";
        var data = {
            "cate_sortcode" : cateSortcode,
            "cate_type"     : cateType
        };
        var callback = function(result) {
            var html = "";
            if (cateSelect.type === "top") {
                $(midId).html(result);
            } else if (cateSelect.type === "mid") {
                $(botId).html(result);
            }

            // 상세 검색정보 초기화 하는 함수 있을경우 실행
            if (isFunc('resetDetailInfo') === true) {
                resetDetailInfo();
            }

            callback2();
        }

        //ajax call 함수
        ajaxCall(url, "html", data, callback);
    },
    "pop" : function(cateType, cateSortcode) {
        //카테고리 분류코드가 빈값인 경우
        if (checkBlank(cateSortcode)) {
            var html = "";

            //카테고리 대분류
            if (cateType === "top") {
                html = makeOption("중분류(전체)");
                $("#cate_mid").html(html);
                html = makeOption("소분류(전체)");
                $("#cate_bot").html(html);
            } else if (cateType === "mid") {
                html = makeOption("소분류(전체)");
                $("#cate_bot").html(html);
            }

            // 상세 검색정보 초기화 하는 함수 있을경우 실행
            if (isFunc('resetDetailInfo') === true) {
                resetDetailInfo();
            }
            return false;
        }

        this.type = cateType;

        // 카테고리 대 변경시 카테고리 소 초기화
        if (cateType === "top") {
            html = makeOption("소분류(전체)");
            $("#cate_bot").html(html);
        }

        var url = "/ajax/common/load_cate_list.php";
        var data = {
            "cate_sortcode" : cateSortcode,
            "cate_type"     : cateType
        };
        var callback = function(result) {
            var html = "";
            if (cateSelect.type === "top") {
                $("#cate_mid").html(result);
            } else if (cateSelect.type === "mid") {
                $("#cate_bot").html(result);
            }
            showBgMask();
        }

        //ajax call 함수
        ajaxCall(url, "html", data, callback);
    }
};

/**
 * @brief 카테고리 소분류 선택했는지 검사
 */
var isSelectCateBot = function() {
    if (checkBlank($("#cate_bot").val())) {
        alert("카테고리를 소분류까지 선택해주세요.");
        return false;
    }

    return true;
};

/**
 * @brief 요율, 적용금액 입력시 숫자만 입력받도록 하는 함수
 *
 * @param 인풋박스 입력값
 */
var inputOnlyNumber = function(val) {
    // 숫자를 제외한 나머지만 반환하는 정규식
    // 12.34214asd -> asd만 반환
    var pattern1 = /[^-?\d+\.?\d+]+/gi;
    // 소수점 세 자리 까지만 유지하는 정규식
    // 123124.3214 -> 123124.321만 반환
    var pattern2 = /^[-]?\d*[\.]?\d{0,3}/gi;

    // 문자열 제거
    var temp1 = val.replace(pattern1, "");
    // 제거된 문자열에서 정수부분이 9자리 미만이 되도록 조정
    var temp2 = temp1.split('.');

    if (temp2[0].length > 9) {
        var i = temp2[0].substr(0, 9);
        temp1 = i + temp2[1];
    }

    return pattern2.exec(temp1);
};

//인풋박스 숫자만 가능
var onlyNumber = function(event, el) {

    var num_pattern = /^[0-9]*$/;
    var str_pattern = /[^-?\d+\.?\d+]+/gi;
    var spc_pattern = /[^(가-힣ㄱ-ㅎㅏ-ㅣa-zA-Z0-9)]/gi;

    if (!num_pattern.test(el.value)) {
        alert("숫자만 입력 가능합니다.");
        el.value = el.value.replace(str_pattern, "");
        el.value = el.value.replace(spc_pattern, "");
        el.focus();
        return false;
    }
}

//인풋박스 숫자만 가능
var onlyNumber2 = function(event) {

    event = event || window.event;

    var keyID = (event.which) ? event.which : event.keyCode;
    if (keyID == 8 || keyID == 46 || keyID == 37 || keyID == 39) {
        return;
    } else {
        event.target.value = event.target.value.replace(/[^0-9]/g, "");
    }
}

//인풋박스 숫자(가격)만 가능
var onlyNumberPrice = function(event) {

    event = event || window.event;

    var keyID = (event.which) ? event.which : event.keyCode;
    if (keyID == 46 || keyID == 37 || keyID == 39) {
        return;
    } else {
        event.target.value = event.target.value.replace(/[^0-9]/g, "").format();
    }
}

/**
 *
 * @brief 등록 팝업
 * param html : 팝업열 html
 * param el_width : 팝업 html 가로사이즈
 *
*/
var openRegiPopup = function(html, el_width, el_height) {

    $("#regi_popup").html(html);
    $("#regi_popup").css("width", el_width + "px");

    var height = $(window).height();
    if (el_height) {
        height = el_height;
    }
    var width  = $(window).width();

    var st = $(window).scrollTop();
    var sl = $(window).scrollLeft();

    var objWidth  = $('#regi_popup').outerWidth();
    var objHeight = $('#regi_popup').outerHeight();

    var topVal = (height - objHeight)/2 + st;

    if (height < objHeight) {
        topVal = (height - objHeight)/3 + st;
    } else if ($(window).height() < 1000) {
        topVal = (height - objHeight)/4.5 + st;
    }

    showBgMask();
    if (objHeight > 550) {
        $("#regi_popup").css({position:'absolute'}).css({
            left: (width - objWidth)/2 + sl + "px",
            top : (height - 600)/2 + st + "px",
            width : el_width + "px"
        });

        $(".pop_add_box .pop-base").css({
            height : "600px"
        });

    } else {
        $("#regi_popup").css({position:'absolute'}).css({
            left: (width - objWidth)/2 + sl + "px",
            top : topVal + "px",
            width : el_width + "px"
        });
    }

    $("#regi_popup").focus();
    $("#regi_popup").show();
}

//등록창 닫기
var hideRegiPopup = function() {

    hideBgMask();
    $("#regi_popup").html("");
    $("#regi_popup").hide();
}

/**
 *
 * @brief 팝업 위 팝업
 * param html : 팝업열 html
 * param el_width : 팝업 html 가로사이즈
 *
*/
var openPopPopup = function(html, el_width) {

    $("#pop_popup").html(html);
    $("#pop_popup").css("width", el_width + "px");

    var height = $(window).height();
    var width  = $(window).width();

    var st = $(window).scrollTop();
    var sl = $(window).scrollLeft();

    var objWidth  = $('#pop_popup').outerWidth();
    var objHeight = $('#pop_popup').outerHeight();

    var topVal = (height - objHeight)/2 + st;

    if (height < objHeight) {
        topVal = (height - objHeight)/3 + st;
    }

    showBgMask();
    showPopMask();
    if (objHeight > 550) {
        $("#pop_popup").css({position:'absolute'}).css({
            left: (width - objWidth)/2 + sl + "px",
            top : (height - 600)/2 + st + "px",
            width : el_width + "px"
        });

        $("#pop_popup .pop_add_box .pop-base").css({
            height : "550px"
        });

    } else {
        $("#pop_popup").css({position:'absolute'}).css({
            left: (width - objWidth)/2 + sl + "px",
            top : topVal + "px",
            width : el_width + "px"
        });
    }

    $("#pop_popup").focus();
    $("#pop_popup").show();
}

//팝업 위 팝업 닫기
var hidePopPopup = function() {

    showBgMask();
    hidePopMask();
    $("#pop_popup").html("");
    $("#pop_popup").hide();
}

/**
 *
 * @brief 숫자, backspace, delete, -, . , ←, →  만 입력 받기
 *
*/
var checkNumber = function(event){

    event = event || window.event;

    var keyID = (event.which) ? event.which : event.keyCode;

    if ( (keyID >= 48 && keyID <= 57) || (keyID >= 96 && keyID <= 105)
            || keyID == 8 || keyID == 46 || keyID == 37 || keyID == 39
            || keyID == 109 || keyID == 173 || keyID == 190)
        return;
    else
        return false;
}

/**
 *
 * @brief 숫자, backspace, delete, ←, → 만 입력 받기
 *
*/
var checkOnlyNumber = function(event){

    event = event || window.event;

    var keyID = (event.which) ? event.which : event.keyCode;

    if ( (keyID >= 48 && keyID <= 57) || (keyID >= 96 && keyID <= 105)
            || keyID == 8 || keyID == 46 || keyID == 37 || keyID == 39 || keyID == 189 || keyID == 109)
        return;
    else
        return false;
}

/**
 *
 * @brief 숫자, backspace, delete, -, . 외 문자 삭제
 *
*/
var removeChar = function(event) {

    event = event || window.event;

    var keyID = (event.which) ? event.which : event.keyCode;
    console.log(keyID);
    if ( keyID == 8 || keyID == 46 || keyID == 37 || keyID == 39  || keyID == 109 )

        return;
    else
        event.target.value = event.target.value.replace(/[^0-9,.,-]/g, "");
}



/*
 *  생년월일 형식체크
 */
var checkBirth = function(str, obj) {

    if (str == "")  return false;

    var num = str.replace(/[^0-9]/g, '');
    var len = num.length;

    if (len == 8) {
        obj.value = num.replace(/([0-9]{4})([0-9]{2})([0-9]{2})/,"$1-$2-$3");
    } else {
        alert("생년월일 형식이 옳지 않습니다.");
        obj.value = "";
        obj.focus();
        return false;
    }
}

/*
 *  전화번호 형식체크
 */
var checkTel = function(str, obj) {

    if (str == "")  return false;

    var num = str.replace(/[^0-9]/g, '');
    var len = num.length;

    if (len == 8) { // ex) 1588-1234
        if (num.substring(0,2) == "02")
            error_num(obj);
        else
            obj.value = phone_format(1, num);
    } else if (len == 9) { // ex) 02-123-1234
        if (num.substring(0,2) == "02")
            obj.value = phone_format(2, num);
        else
            error_num(obj);
    } else if (len == 10) { // ex) 02-1234-1234 / 031-123-1234
        if (num.substring(0,2) == "02")
            obj.value = phone_format(2, num);
        else
            obj.value = phone_format(3, num);
    } else if (len == 11) { // ex)
        if (num.substring(0,2) == "02")
            error_num(obj);
        else
            obj.value = phone_format(3, num);
    } else {
        error_num(obj);
    }

}

function phone_format(type, num){

    if (type == 1) {
        return num.replace(/([0-9]{4})([0-9]{4})/,"$1-$2");
    } else if (type == 2) {
        return num.replace(/([0-9]{2})([0-9]+)([0-9]{4})/,"$1-$2-$3");
    } else {
        return num.replace(/(^01.{1}|[0-9]{3})([0-9]+)([0-9]{4})/,"$1-$2-$3");
    }

}

function error_num(obj){

    alert("정상적인 번호가 아닙니다.");
    obj.value = "";
    obj.focus();
    return;

}

/*
 * 이메일 형식 체크
 */
var checkEmail = function(str, obj) {

    if (str == "")  return false;
    var reg = /[0-9a-zA-Z][_0-9a-zA-Z-]*@[_0-9a-zA-Z-]+(\.[_0-9a-zA-Z-]+){1,2}$/;

    //이메일 형식에 맞지않으면
    if (!str.match(reg)){
        alert("이메일 형식이 옳지 않습니다.");
        obj.value = "";
        obj.focus();
        return false;
    }

    return true;
}

//이메일 형식 체크
var emailCheck = function(str) {

    var reg = /[0-9a-zA-Z][_0-9a-zA-Z-]*@[_0-9a-zA-Z-]+(\.[_0-9a-zA-Z-]+){1,2}$/;

    //이메일 형식에 맞지않으면
    if (!str.match(reg)){
        return false;
    }

    return true;
}

/**
 * @brief 회원명 검색할 때 사용하는 함수
 *
 * @param event = 키 이벤트
 * @param val   = 입력값
 * @param dvs   = 팝업 출력인지 재검색인지 구분값
 */
var searchOfficeNick = function(event, val, dvs) {

    if (event.keyCode != 13) {
        return false;
    }

    if (val.length < 2) {
        alert("두글자 이상 입력하세요.");
        return false;
    }

    var url = "/ajax/common/load_office_nick.php";
    var data = {
        "sell_site"  : $("#sell_site").val(),
        "search_val" : val
    };
    var callback = function(result) {
        if (dvs !== "select") {
            searchPopShow(event, "searchOfficeNick", "searchOfficeNick");
        } else {
            showBgMask();
        }

        $("#search_list").html(result);
    };

    ajaxCall(url, "html", data, callback);
};

/**
 * @brief 회원명 검색할 때 사용하는 함수
 *
 * @param event = 키 이벤트
 * @param val   = 입력값
 * @param dvs   = 팝업 출력인지 재검색인지 구분값
 */
var searchOfficeNick2 = function(event, val, dvs) {

    if (event.keyCode != 13) {
        return false;
    }

    if (val.length < 2) {
        alert("두글자 이상 입력하세요.");
        return false;
    }

    var url = "/ajax/common/load_office_mb_id.php";
    var data = {
        "sell_site"  : $("#sell_site").val(),
        "search_val" : val
    };
    var callback = function(result) {
        if (dvs !== "select") {
            searchPopShow2(event, "searchOfficeNick2", "searchOfficeNick2");
        } else {
            showBgMask();
        }

        $("#search_list").html(result);
    };

    ajaxCall(url, "html", data, callback);
};

/**
 * @brief 회원명 검색창에서 결과값 클릭했을 경우 인풋에 값 입력
 *
 * @param val = 선택값
 */
var nameClick = function(seqno, name, sell_site) {
    $("#office_nick").val(name);
    $("#member_seqno").val(seqno);
    $("#sell_site").val(sell_site);
    hideRegiPopup();
};

/**
 * @brief 회원명 검색창에서 결과값 클릭했을 경우 인풋에 값 입력
 *
 * @param val = 선택값
 */
var nameClick2 = function(seqno, name, sell_site) {
    $("#mb_id_point").val(name);
    $("#member_seqno").val(seqno);
    $("#member_seqno2").val(seqno);
	$("#sell_site").val(sell_site);
    hideRegiPopup();
};

/**
 * @brief 조건(인쇄물제목, 주문번호, 사내닉네임, 회원명, 회원아이디) 검색할 때 사용하는 함수
 *
 * @param event = 키 이벤트
 * @param val   = 입력값
 * @param dvs   = 팝업 출력인지 재검색인지 구분값
 */
var searchCnd = function(event, val, dvs) {

    if (event.keyCode != 13){
        return false;
    }

    if (val.length < 2) {
        alert("두글자 이상 입력하세요.");
        return false;
    }

    //인쇄물 제목일 경우
    if ($("#cnd_val").val() == "title") {
        return false;
    }

    var url = "/ajax/common/load_cnd_search.php";

    if (dvs == "select") {
        var data = {
            "search_cnd"  : $("#cnd_val").val(),
            "search_txt"  : $("#search_pop").val(),
            "sell_site"   : $("#sell_site").val(),
            "order_state" : $("#state").val()
        };
    } else {
        var data = {
            "search_cnd"  : $("#cnd_val").val(),
            "search_txt"  : $("#search_val").val(),
            "sell_site"   : $("#sell_site").val(),
            "order_state" : $("#state").val()
        };
    }
    var callback = function(result) {
        if (dvs !== "select") {
            searchPopShow(event, "searchCnd", "searchCndKey");
        } else {
            showBgMask();
        }
        $("#search_cnd").val($("#cnd_val").val());
        $("#search_list").html(result);
    };

    ajaxCall(url, "html", data, callback);
}

/**
 * @brief 조건(인쇄물제목, 주문번호, 사내닉네임, 회원명, 회원아이디) 검색할 때 사용하는 함수
 *
 * @param event = 키 이벤트
 * @param val   = 입력값
 * @param dvs   = 팝업 출력인지 재검색인지 구분값
 */
var searchCndKey = function(event, val, dvs) {

    var url = "/ajax/common/load_cnd_search.php";
    var data = {
        "search_cnd"  : $("#cnd_val").val(),
        "search_txt"  : $("#search_pop").val(),
        "sell_site"   : $("#sell_site").val()
    };
    var callback = function(result) {
        if (dvs !== "select") {
            searchPopShow(event, "searchCnd", "searchCndKey");
        } else {
            showBgMask();
        }
        $("#search_cnd").val($("#cnd_val").val());
        $("#search_list").html(result);
    };

    ajaxCall(url, "html", data, callback);
}

/**
 * @brief 조건 검색창에서 결과값 클릭했을 경우 인풋에 값 입력
 *
 * @param val = 선택값
 */
var cndClick = function(seqno, val) {
    $("#search_val").val(val);
    $("#search_txt").val(seqno);
    hideRegiPopup();
};

// 숫자 타입에서 쓸 수 있도록 format() 함수 추가
Number.prototype.format = function(){
    if(this==0) return 0;

    var reg = /(^[+-]?\d+)(\d{3})/;
    var n = (this + '');

    while (reg.test(n)) n = n.replace(reg, '$1' + ',' + '$2');

    return n;
};

// 문자열 타입에서 쓸 수 있도록 format() 함수 추가
String.prototype.format = function(){
    var num = parseFloat(this);
    if( isNaN(num) ) return "0";

    return num.format();
};

/**
 * @brief 판매채널에 해당하는 영업팀 검색
 */
var loadDeparInfo = function() {

    showMask();

    var url = "/ajax/common/load_depar_list.php";
    var data = {
        "sell_site" : $("#sell_site").val()
    };
    var callback = function(result) {
        $("#depar_code").html(result);
    };

    ajaxCall(url, "html", data, callback);
};

/**
 * @brief 로그인
 */
var login = function() {

    showMask();

    var url = "/common/login.php";

    if ($(':radio[name="login_dvs"]:checked').val() == "out") {
        url = "/common/extnl_mng_login.php";
    }

    var data = {
        "id"  : $("#id").val(),
        "pw"  : $("#pw").val()
    };
    var callback = function(result) {
        var err_msg = "아이디 또는 비밀번호를 다시 확인하세요.</br>등록되지 않은 아이디이거나,</br>아이디 또는 비밀번호를 잘못 입력하셨습니다.";
        var rs = result.split("♪");
        if (rs[0] == "true") {
            if ($(':radio[name="login_dvs"]:checked').val() == "dp") {
                loadAuthPage(rs[1]);
                //location.href = "/business/order_mng.html";
            } else {
                location.href = rs[1];
            }
        } else {
            $("#login_err_msg").html(err_msg);
        }
    };

    if (checkBlank($("#id").val())) {
        alert("아이디를 입력 해주세요.");
        $("#id").focus();
        return false;
    }

    if (checkBlank($("#pw").val())) {
        alert("암호를 입력 해주세요.");
        $("#pw").focus();
        return false;
    }

    ajaxCall(url, "html", data, callback);
};


/**
 * @brief 로그인
 *
 *
var loadAuthPage = function(section) {
    var url = "/ajax/common/load_auth_page.php";
    var data = {
        "section" : section
    };
    var callback = function(result) {
        if (result.trim() == false) {
            alert("권한이 없습니다. \n페이지권한을 설정해주세요.");
        } else {
            location.href = result.trim();
        }
    };

    ajaxCall(url, "html", data, callback);
}*/

/**
 * @brief 로그인
 */
var loadAuthPage = function(section) {

    var desti = section;
    var url = '/' + desti;

    switch(desti) {
        case "business":
            location.href = url + "/order_mng.html";
            break;
        case "order":
            location.href = url + "/order_common_mng.html";
            break;
        case "manufacture":
            location.href = url + "/label_list.html";
            break;
        case "member":
            location.href = url + "/member_common_list.html";
            break;
        case "mkt":
            location.href = url + "/point_mng.html";
            //location.href = url + "/grade_mng.html";
            break;
        case "calcul_mng":
            location.href = url + "/cashbook_regi.html";
            break;
        case "basic_mng":
            location.href = url + "/prdt_price_list.html";
            break;
        case "dataproc_mng":
            location.href = url + "/prdt_info_mng.html";
            break;
        case "statistics":
            location.href = url + "/sales_order.html";
            break;
        case "01":
            location.href = "/business/order_mng.html";
            break;
        case "02":
            location.href = "/order/order_common_mng.html";
            break;
        case "03":
            location.href = "/manufacture/label_list.html";
            break;
        case "04":
            location.href = "/manufacture/stor_list.html";
            break;
        default:
            location.href = "/order/order_common_mng.html";
            break;

    }
}

/**
 * @brief 로그인
 */
var loginKey = function(event) {

    if (event.keyCode == 13) {
        login();
    }
}

/**
 * @brief 암호 입력란으로 이동
 */
var idkey = function(event) {
    if (event.keyCode == 13) {
        $("#pw").focus();
    }
}

/**
 * @brief 종이 정보 리셋
 */
var resetPaperInfo = function() {
    var paper = "paper";
    $("#" + paper + "_dvs").html("<option value=\"\">구분</option>");
    $("#" + paper + "_color").html("<option value=\"\">색상</option>");
    $("#" + paper + "_basisweight").html("<option value=\"\">평량</option>");
};

/**
 * @brief 카테고리 선택시 하위 카테고리 정보 검색
 *
 * @param paperType = 종이 선택 구분(NAME, DVS, COLOR)
 * @param val       = 선택한 값
 * @param prefix    = id 중복일 때 접두사
 */
var paperSelect = {
    "type" : null,
    "exec" : function(paperType, val, prefix) {
        if (paperType === "name" && checkBlank(val) === true) {
            resetPaperInfo();
            return false;
        }

        if (checkBlank(prefix)) {
            prefix = '#';
        } else {
            prefix = '#' + prefix + '_';
        }

        this.type = paperType;

        var url = "/json/basic_mng/prdt_price_list/load_paper_info.php";
        var data = {
            "cate_sortcode" : $(prefix + "cate_bot").val(),
            "type"          : paperType,
            "paper_name"    : $(prefix + "paper_name").val(),
            "paper_dvs"     : $(prefix + "paper_dvs").val(),
            "paper_color"   : $(prefix + "paper_color").val()
        };

        var callback = function(result) {
            if (paperSelect.type === "NAME") {
                $(prefix + "paper_dvs").html(result.dvs);
                $(prefix + "paper_color").html(result.color);
                $(prefix + "paper_basisweight").html(result.basisweight);
            } else if (paperSelect.type === "DVS") {
                $(prefix + "paper_color").html(result.color);
                $(prefix + "paper_basisweight").html(result.basisweight);
            } else if (paperSelect.type === "COLOR") {
                $(prefix + "paper_basisweight").html(result.basisweight);
            }
        };

        ajaxCall(url, "json", data, callback);
    }
};

var windowClose = function() {
    window.close();
}

/**
 * @brief 주문 내용 조회팝업 출력
 *
 * @param seq = 주문일련번호
 */
var showOrderContent = function(seq) {
    var url = "/ajax/common/load_order_info.php";
    var data = {
        "seqno" : seq
    };
    var callback = function(result) {
        openRegiPopup(result, "950");
    };

    ajaxCall(url, "html", data, callback);
};

/**
 * @brief value를 number format 후 해당 객체에 적용
 */
var getNumberFormat = function(val, el) {
    var newVal = val.replace(/,/gi, "");
    $("#" + el).val(newVal.format());
};

/**
 * @brief value를 number format 후 해당 객체에 적용
 * ??????????????????????????????????????????????????????????
 */
var getDetail = {
    "memo" : function(empl_seqno) {

        $("#page-content").html("memo");
	/*
        var url = "";
        var data = {
            "empl_seqno"   : empl_seqno
        };

        var callback = function(result) {
        };

        ajaxCall(url, "json", data, callback);
	*/
    },
    "order" : function(empl_seqno) {

        $("#page-content").html("order");
	/*
        var url = "";
        var data = {
            "empl_seqno"   : empl_seqno
        };

        var callback = function(result) {
        };

        ajaxCall(url, "json", data, callback);
	*/
    },
    "member" : function(empl_seqno) {

        $("#page-content").html("member");
	/*
        var url = "";
        var data = {
            "empl_seqno"   : empl_seqno
        };

        var callback = function(result) {
        };

        ajaxCall(url, "json", data, callback);
	*/
    },
    "team" : function(empl_seqno) {

        $("#page-content").html("team");
	/*
        var url = "";
        var data = {
            "empl_seqno"   : empl_seqno
        };

        var callback = function(result) {
        };

        ajaxCall(url, "json", data, callback);
	*/
    }
};

/**
 * @brief 전달받은 메세지 alert으로 띄우고 false값 반환
 *
 * @param str = 메세지
 *
 * @return false
 */
var alertReturnFalse = function(str) {
    alert(str);
    return false;
};

/**
 * @brief 쿠키 생성
 *
 * @param param = 쿠키생성할 정보
 * @detail param.data = [k1|v1, k3|v3, k2|v2, ...] // 쿠키데이터
 * @detail param.expire = 365 // 쿠키 만료일
 *
 * @return false
 */
var setCookie = function(param) {
    var expire = parseInt(param.expire);
    var dataArr = param.data;
    var arrLength = dataArr.length;

    var d = new Date();
    d.setTime(d.getTime() + (expire * 86400000));

    var str = '';
    for (var i = 0; i < arrLength; i++) {
        var data = dataArr[i].split('|');

        var str = data[0] + '=' + data[1] + ';';
        str += "expires="+ d.toUTCString() + ";path=/;";
        document.cookie = str;
    }
};

/**
 * @brief 쿠키값 반환
 *
 * @param cname = 쿠키 키값
 *
 * @return false
 */
var getCookie = function(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
};

/**
 * @테이블 가로폭이 넘칠시에 마우스로 스크롤
 */
	
	
var dragFlag = false;
var x, y, pre_x, pre_y;


$(function () {
    $('.overflow_auto').mousedown(
        function (e) {
            dragFlag = true;
            var obj = $(this);
            x = obj.scrollLeft();
            y = obj.scrollTop();

            pre_x = e.screenX;
            pre_y = e.screenY;					

            $(this).css("cursor", "pointer");

            //$('#result').text("x:" + x + "," + "y:" + y + "," + "pre_x:" + pre_x + "," + "pre_y:" + pre_y);
            $('#result').text(dragFlag);

        }
    );

    $('.overflow_auto').mousemove(
        function (e) {
            if (dragFlag) {
                var obj = $(this);
                obj.scrollLeft(x - e.screenX + pre_x);
                obj.scrollTop(y - e.screenY + pre_y);

                //$('#result').text((x - e.screenX + pre_x) + "," + (y - e.screenY + pre_y));
                return false;
            }

        }
    );

    $('.overflow_auto').mouseup(
        function () {
            dragFlag = false;
            //$('#result').text("x:" + x + "," + "y:" + y + "," + "pre_x:" + pre_x + "," + "pre_y:" + pre_y);
            $('#result').text(dragFlag);
            $(this).css("cursor", "default");
        }


    );

    $('body').mouseup(
        function () {
            dragFlag = false;					
            $('#result').text(dragFlag);
            $(this).css("cursor", "default");
        }


    );


});

/**
 * @brief 십원단위 반올림
 *
 * @param val = 반올림할 값
 *
 * @return 계산된 값
 */
var ceilVal = function(val) {
    var isMinus = (parseFloat(val) < 0) ? true : false;

    val = Math.abs(val);
    val = Math.floor(val);
    val = Math.round(val * 0.01) * 100;

    return isMinus ? val * -1 : val;
};
