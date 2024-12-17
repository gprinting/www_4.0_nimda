/**
 * @brief 주문상태값으로 진행상태값 불러옴
 *
 * @param dvs = 구분값
 * @param val = 주문상태값
 */
var loadStatusProc = {
    "dvs"  : null,
    "exec" : function(dvs, val) {
        this.dvs = dvs;

        var url = "/ajax/business/order_common_mng/load_status_proc.php";
        var data = {
            "val" : val
        };
        var callback = function(result) {
            $("#status_proc_" + loadStatusProc.dvs).html(result);
        };
    
        ajaxCall(url, "html", data, callback);
    }
};

/**
* @brief 웹로그인
*/
var webLogin = function(seqno) {
    var url = "/ajax/business/order_hand_regi/load_admin_hash.php";
    var callback = function(result) {
        window.open("http://new.gprinting.co.kr/common/login.php?seqno=" + seqno + "&flag=" + result.trim()+ "&isadmin=Y", "_blank");
    };

    showMask();
    ajaxCall(url, "html", {}, callback);
}

/**
 * @brief 배송유형 변경시 거기에 속한 상세정보 검색
 *
 * @param val = 판매채널 일련번호
 */
var changeDlvrDvs = function(dlvrDvs) {
    var url = "/ajax/business/order_mng/load_dlvr_code_info.php";
    var data = {
        "dlvr_dvs" : dlvrDvs
    };
    var callback = function(result) {
        $("#dlvr_code").html(result);
    };

    ajaxCall(url, "html", data, callback);
};

/**
 * @brief 페이징 html 생성 관련 공통함수
 *
 * @param id       = 영역객체 아이디
 * @param funcName = 페이지 변경시 작동할 함수명
 * @param rowCnt   = 행 표시개수
 * @param totalCnt = 전체개수
 * @param blockCnt = 페이지 버튼 표시개수
 * @param dvs      = 초기화인지 전/후 버튼인지 구분값
 */
var pagingCommon = function(id, funcName, rowCnt, totalCnt, blockCnt, dvs) {
    rowCnt   = parseInt(rowCnt);
    totalCnt = parseInt(totalCnt);
    blockCnt = parseInt(blockCnt);

    /* 2017-08-22 이청산 주석처리 : 이부분으로 빠지면 pagingCommon의 init 기능이 작동하지 않음
    if (isNaN(totalCnt) || isNaN(blockCnt)
            || totalCnt === 0 || blockCnt === 0) {
        return false;
    } 
    */

    // 최대 페이지
    var maxPage = Math.ceil(totalCnt / rowCnt);
    // active 할 페이지
    var activePage = 1;

    var html = "<span><button type=\"button\" id=\"" + id + "_fwd\" ";
    html += "onclick=\"pagingCommon('";
    html += id + "','";
    html += funcName + "',";
    html += rowCnt + ',';
    html += totalCnt + ',';
    html += blockCnt + ',';
    html += "'fwd');\" class=\"btn_page_pre\"></button></span>";
    
    if (dvs === "init") {
        // 페이지 영역 초기화
        if (maxPage < blockCnt) {
            blockCnt = maxPage;
        }
        for (var i = 1; i <= blockCnt; i++) {
            html += "<span><a href=\"#none\" ";
            if (i === 1) {
                html += "class=\"" + id + " page_accent\" ";
            } else {
                html += "class=\"" + id + "\" ";
            }
            html += "id=\"" + id + '_' + i + "\" ";
            html += "onclick=\"" + funcName + "(" + i + ");\">";
            html += i;
            html += "</a></span>";
        }
    } else if (dvs === "fwd") {
        // << 버튼 클릭시
        var firstPage = parseInt($('#' + id + "_fwd").parent("span").next().find("a").text());
        activePage = firstPage - 1;
        var block = firstPage - blockCnt;
        if (block < 0) {
            return false;
        }
        for (var i = block; i < firstPage; i++) {
            html += "<span><a href=\"#none\" ";
            if (i === activePage) {
                html += "class=\"" + id + " page_accent\" ";
            } else {
                html += "class=\"" + id + "\" ";
            }
            html += "id=\"" + id + '_' + i + "\" ";
            html += "onclick=\"" + funcName + "(" + i + ");\">";
            html += i;
            html += "</a></span>";
        }
        window[funcName](activePage);
    } else if (dvs === "bwd") {
        // >> 버튼 클릭시
        var lastPage = parseInt($('#' + id + "_bwd").parent("span").prev().find("a").text());
        activePage = lastPage + 1;
        if (maxPage === lastPage) {
            return false;
        }
        var block = lastPage + blockCnt;
        for (var i = lastPage + 1; i <= block; i++) {
            if (maxPage < i) {
                break;
            }
            html += "<span><a href=\"#none\" ";
            if (i === activePage) {
                //html += "class=\"" + id + " active_page\" ";
                html += "class=\"" + id + " page_accent\" ";
            } else {
                html += "class=\"" + id + "\" ";
            }
            html += "id=\"" + id + '_' + i + "\" ";
            html += "onclick=\"" + funcName + "(" + i + ");\">";
            html += i;
            html += "</a></span>";
        }
        window[funcName](activePage);
    }

    html += "<span><button type=\"button\" id=\"" + id + "_bwd\" ";
    html += "onclick=\"pagingCommon('";
    html += id + "','";
    html += funcName + "',";
    html += rowCnt + ',';
    html += totalCnt + ',';
    html += blockCnt + ',';
    html += "'bwd');\" class=\"btn_page_nxt\"></button></span>";

    $('#' + id).html(html);
};

/**
 * @brief 전역 object값 false로 초기화
 *
 * @param id = 전역 object 이름
 */
var initObjValAllFalse = function(id) {
    if (typeof window[id] !== "object") {
        return false;
    }

    $.each(window[id], function(idx, val) {
        window[id][idx] = false;
    }); 
};

/**
 * @brief id 접두사 생성
 *
 * @return 구분값
 */
var getPrefix = function(dvs) {
    if (checkBlank(dvs) === true) {
        return '#';
    } else {
        return '#' + dvs + '_';
    }
}

/**
 * @brief 각 검색정보 구역 slide show/hide
 *
 * @param dvs = 영역 구분값
 * @param btnDvs = 현재 버튼 구분값
 */
var toggleSearchInfo = function(dvs, btnDvs) {
    var prefix = getPrefix(dvs);

    $(prefix + btnDvs).hide();

    switch(btnDvs) {
        case "plus" :
            $(prefix + "search").slideDown();
            $(prefix + "minus").show();
            break
        case "minus" :
            $(prefix + "search").slideUp();
            $(prefix + "plus").show();
            break
    }
};

/**
 * @brief 날짜 범위 설정 - 공통
 *
 * @param prefix   = 데이트피커 객체구분값
 * @param dvs      = 연산구분값
 * @param fromCalc = 시작일 계산할 일자
 * @param toCalc   = 종료일 계산할 일자
 * @param txt      = 버튼 텍스트값
 * @param toFix    = 종료일 고정여부
 */
var setDateVal = function(prefix, dvs, fromCalc, toCalc, txt, toFix) {
    if (dvs === 'a') {
        $('#' + prefix + "_from").val('');
        return false;
    } else if (dvs === 't') {
        $('#' + prefix + "_from").datepicker("setDate", '0');
        $('#' + prefix + "_to").datepicker("setDate", '0');
        return false;
    }

    var from = $('#' + prefix + "_from").val();
    var to   = $('#' + prefix + "_to").val().split('-');

    if (checkBlank(from) || toFix) {
        from = to;
    } else {
        from = from.split('-');
    }

    var calc = calcDate.exec({
        "dvs"      : dvs,
        "fromCalc" : fromCalc,
        "toCalc"   : toCalc,
        "from" : {
            'y' : from[0],
            'm' : from[1],
            'd' : from[2]
        },
        "to" : {
            'y' : to[0],
            'm' : to[1],
            'd' : to[2]
        }
    });

    $('#' + prefix + "_from").datepicker("setDate", calc.from);
    $('#' + prefix + "_to").datepicker("setDate", calc.to);
};

/**
 * @brief 날짜 계산
 *
 * @param param = 계산용 파라미터
 * {
 *     dvs      = 계산기준
 *     fromCalc = 시작일 계산할 값
 *     toCalc   = 종료일 계산할 값
 *     from{}   = 시작 일자
 *     to{}     = 끝 일자
 *     from/to.y = 계산될 연
 *     from/to.m = 계산될 월
 *     from/to.d = 계산될 일
 * }

* @return 계산된 from/to Date객체
 */
var calcDate = {
    "year" : function(param) {
        var year = param.year;
        var calc = param.calc;

        return year + calc;
    },
    "month" : function(param) {
        var y = param.y;
        var m = param.m;
        var d = param.d;
        var calc = param.calc;

        var ret = m + calc;
    
        if (ret <= 0) {
            // 1월에서 전월일 경우 1년 감소
            y--;
            m = 12;
        } else {
            m = ret;
        }

        // 현재 월의 마지막 일이 계산된 월의 마지막 일보다 클 경우
        var lastDay = new Date(y, (m).toString(), "00").getDate();
        if (d > lastDay) {
            d = lastDay;
        }

        return {
            "y" : y,
            "m" : m,
            "d" : d
        };
    },
    "day" : function(param) {
        var y = param.y;
        var m = param.m;
        var d = param.d;
        var calc  = param.calc;

        var lastDay = new Date(y, (m).toString(), "00").getDate();

        // 해당 월의 마지막 일 검색
        var temp = d + calc;

        if (temp <= 0) {
            // 현재 일에서 계산된 일이 현재 연/월을 벗어나는 경우(이전)
            // ex) 2017-01-03의 1주일 전 = 2016-12-27
            var calc = this.month({
                'y' : y,
                'm' : m,
                'd' : d,
                "calc" : -1
            });

            y = calc.y;
            m = calc.m;
            // 바뀐 월의 마지막 날에서 일자 감소
            var lastDay = new Date(calc.y, (m).toString(), "00").getDate();
            d = lastDay + temp;

        } else if (lastDay < temp) {
            var calc = this.month({
                'y' : y,
                'm' : m,
                'd' : d,
                "calc" : -1
            });

            y = calc.y;
            m = calc.m;
            // 바뀐 월의 마지막 날에서 일자 감소
            d = temp - lastDay;
        } else {
            d = temp;
        }

        return {
            'y' : y,
            'm' : m,
            'd' : d
        };
    },
    "exec" : function(param) {
        var dvs      = param.dvs;
        var fromCalc = parseInt(param.fromCalc);
        var toCalc   = parseInt(param.toCalc);
        var from = param.from;
        var to   = param.to;
         
        var fromY = parseInt(from.y);
        var fromM = parseInt(from.m);
        var fromD = parseInt(from.d);
    
        var toY = parseInt(to.y);
        var toM = parseInt(to.m);
        var toD = parseInt(to.d);

        if (dvs === 'y') {
            // 연 계산
            fromY = this.year({
                "year" : fromY,
                "calc" : fromCalc
            });
            toY = this.year({
                "year" : toY,
                "calc" : toCalc
            });
        } else {
            var calcFrom = null;
            var calcTo   = null;

            if (dvs === 'm') {
                // 월 계산
                calcFrom = this.month({
                    'y' : fromY,
                    'm' : fromM,
                    'd' : fromD,
                    'calc' : fromCalc
                });
                calcTo = this.month({
                    'y' : toY,
                    'm' : toM,
                    'd' : toD,
                    'calc' : toCalc
                });
            } else if (dvs === 'd') { 
                // 일 계산
                calcFrom = this.day({
                    'y' : fromY,
                    'm' : fromM,
                    'd' : fromD,
                    'calc' : fromCalc
                });
                calcTo = this.day({
                    'y' : toY,
                    'm' : toM,
                    'd' : toD,
                    'calc' : toCalc
                });
            }

            fromY = calcFrom.y;
            fromM = calcFrom.m;
            fromD = calcFrom.d;
            toY = calcTo.y;
            toM = calcTo.m;
            toD = calcTo.d;
        } 

        fromM = ("0" + fromM).substr(-2,2);
        toM   = ("0" + toM).substr(-2,2);
        fromD = ("0" + fromD).substr(-2,2);
        toD   = ("0" + toD).substr(-2,2);
    
        var fromStr = fromY + '-' + fromM + '-' + fromD;
        var toStr = toY + '-' + toM + '-' + toD;

        return {
            "from" : new Date(fromStr),
            "to"   : new Date(toStr)
        };
    }
};

/**
 * @brief 전체 마스크 show
 *
 */
var showLoadingMask = function() {
    $('#common_mask').show().css("width:100%");
    $('#common_mask').attr("height", 100);
};

/**
 * @brief 부분 마스크 hide
 *
 * @param id = 부분 마스크 div id
 */
var hideLoadingMask = function() {
    $('#common_mask').hide();
};

/**
 * @brief 페이징 html 생성 관련 공통함수 upgrade ver.
 *
 * @param id       = 영역객체 id
 * @param funcName = 페이지 변경시 실행할 함수명
 * @param rowCnt   = 행 표시개수
 * @param totalCnt = 전체개수
 * @param blockCnt = 페이지 버튼 표시개수
 * @param dvs      = 초기화, 맨앞/맨뒤, 전/후버튼 구분값
 */
var pagingCommonUpgv = function(id, funcName, rowCnt, totalCnt, blockCnt, dvs) {
    rowCnt   = parseInt(rowCnt); 
    totalCnt = parseInt(totalCnt); 
    blockCnt = parseInt(blockCnt); 

    // 최대 페이지
    var maxPage = Math.ceil(totalCnt / rowCnt);
    // active 할 페이지
    var activePage = 1;

    var html = "<span class=\"pagination\"><a href=\"#none\" id=\"" + id + "_first\" ";
    html += "onclick=\"pagingCommonUpgv('";
    html += id + "','";
    html += funcName + "',";
    html += rowCnt + ',';
    html += totalCnt + ',';
    html += blockCnt + ',';
    html += "'first');\" class=\"fa fa-angle-double-left\"></a></span>";
    html += "<span class=\"pagination\"><a href=\"#none\" id=\"" + id + "_fwd\" ";
    html += "onclick=\"pagingCommonUpgv('";
    html += id + "','";
    html += funcName + "',";
    html += rowCnt + ',';
    html += totalCnt + ',';
    html += blockCnt + ',';
    html += "'fwd');\" class=\"fa fa-angle-left\"></a></span>";

    // 페이지 영역 초기화
    if (maxPage < blockCnt) {
        blockCnt = maxPage;
    }

     if (dvs === "init") {
        for (var i = 1; i <= blockCnt; i++) {
            html += "<span><a href=\"#none\" ";
            if (i === 1) {
                html += "class=\"" + id + " page_accent\" ";
            } else {
                html += "class=\"" + id + "\" ";
            }
            html += "id=\"" + id + '_' + i + "\" ";
            html += "onclick=\"" + funcName + "(" + i + ");\">";
            html += i;
            html += "</a></span>";
        }
        
    } else if (dvs === "first") {
        // << 버튼 클릭시, 가장 첫 페이지(1페이지)로 이동
        var firstPage = 1;      
        for (var i = firstPage; i < blockCnt + 1; i++) {
            html += "<span><a href=\"#none\" ";
            if (i === firstPage) {
                html += "class=\"" + id + " page_accent\" ";
            } else {
                html += "class=\"" + id + "\" ";
            }
            html += "id=\"" + id + '_' + i + "\" ";
            html += "onclick=\"" + funcName + "(" + i + ");\">";
            html += i;
            html += "</a></span>";
        }
        window[funcName](firstPage);
    } else if (dvs === "last") {
        // >> 버튼 클릭시, 가장 마지막 페이지로 이동
        var lastPage = maxPage;      
        var lastBlock = Math.ceil(maxPage % 5);
        var lastStartPage = maxPage - lastBlock + 1;
        for (var i = lastStartPage; i < lastStartPage + lastBlock; i++) {
            html += "<span><a href=\"#none\" ";
            if (i === lastPage) {
                html += "class=\"" + id + " page_accent\" ";
            } else {
                html += "class=\"" + id + "\" ";
            }
            html += "id=\"" + id + '_' + i + "\" ";
            html += "onclick=\"" + funcName + "(" + i + ");\">";
            html += i;
            html += "</a></span>";
        }

        window[funcName](lastPage);
    } else if (dvs === "fwd") {
        // < 버튼 클릭시
        var firstPage = parseInt($('#' + id + "_fwd").parent("span").next().find("a").text());
        activePage = firstPage - 1;
        var block = firstPage - blockCnt;
    
        if (block <= 0) {
            return false;
        }
        for (var i = block; i < firstPage; i++) {
            html += "<span><a href=\"#none\" ";
            if (i === activePage) {
                html += "class=\"" + id + " page_accent\" ";
            } else {
                html += "class=\"" + id + "\" ";
            }
            html += "id=\"" + id + '_' + i + "\" ";
            html += "onclick=\"" + funcName + "(" + i + ");\">";
            html += i;
            html += "</a></span>";
        }
        window[funcName](activePage);
    } else if (dvs === "bwd") {
        // > 버튼 클릭시
        var lastPage = parseInt($('#' + id + "_bwd").parent("span").prev().find("a").text());
        activePage = lastPage + 1;
        if (maxPage === lastPage) {
            return false;
        }
        var block = lastPage + blockCnt;
        for (var i = lastPage + 1; i <= block; i++) {
            if (maxPage < i) {
                break;
            }
            html += "<span><a href=\"#none\" ";
            if (i === activePage) {
                //html += "class=\"" + id + " active_page\" ";
                html += "class=\"" + id + " page_accent\" ";
            } else {
                html += "class=\"" + id + "\" ";
            }
            html += "id=\"" + id + '_' + i + "\" ";
            html += "onclick=\"" + funcName + "(" + i + ");\">";
            html += i;
            html += "</a></span>";
        }
        window[funcName](activePage);
    }

    html += "<span class=\"pagination\"><a href=\"#none\" id=\"" + id + "_bwd\" ";
    html += "onclick=\"pagingCommonUpgv('";
    html += id + "','";
    html += funcName + "',";
    html += rowCnt + ',';
    html += totalCnt + ',';
    html += blockCnt + ',';
    html += "'bwd');\" class=\"fa fa-angle-right\"></a></span>";
    html += "<span class=\"pagination\"><a href=\"#none\" id=\"" + id + "_last\" ";
    html += "onclick=\"pagingCommonUpgv('";
    html += id + "','";
    html += funcName + "',";
    html += rowCnt + ',';
    html += totalCnt + ',';
    html += blockCnt + ',';
    html += "'last');\" class=\"fa fa-angle-double-right\"></a></span>";

    $('#' + id).html(html);
    
};
