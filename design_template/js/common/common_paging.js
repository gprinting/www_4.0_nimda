/**
 * @brief 대용량 데이터용 페이징 처리함수
 * 좌우측 화살표 클릭시 호출됨
 *
 * @param page     = 현재 페이지
 */
var paging = {
    "blockSize"  : 5,
    "blockRight" : 1,
    "blockLeft"  : 0,
    "exec"       : function(page) {
        var html = "";
        
        page = parseInt(page);

	    if (page === 1) {
	        this.blockRight = 1;
	        this.blockLeft  = 0;
	    }

        // 뒤로 한 블럭 가는지 가는지 확인
        // 뒤로 갈 경우 페이지 시작단위가 틀려지므로 처리함
	    // 또한 좌우측 화살표 페이지 값도 처리
        // ex) 11에서 뒤로 한 블럭 간 경우 6-7-8-9-[10] 이지만
        //     15에서 앞으로 한 블럭 간 경우는 [16]-17-18-29-20
	    var showPage = page;
        if (page < parseInt(cndSearch.page)) {
            // 1page의 경우에는 조건에 해당이 안되므로 else로 빠짐

            showPage -= (this.blockSize - 1);

	        this.blockRight -= this.blockSize;
	        this.blockLeft  = this.blockRight - this.blockSize - 1;
        } else {
	        this.blockRight += this.blockSize;
	        this.blockLeft  = this.blockRight - this.blockSize - 1;
	    }

        // 좌측 화살표 disabled 처리
        if (page <= this.blockSize) {
            html = "<li class=\"disabled\"><i class=\"fa fa-angle-left\"></i></li>";
        } else {
            html = "<li><a onclick=\"paging.exec(" + (this.blockLeft) + ");\" class=\"fa fa-angle-left\"></a></li>";
        }

        for (var i = 0; i < this.blockSize; i++) {
            var pageVal = showPage + i;

            if (pageVal === page) {
                html += "<li><a id=\"page_" + pageVal + "\" onclick=\"cndSearch.exec('p', '" + pageVal + "');\" class=\"active\" style=\"cursor:pointer;\">" + pageVal + "</a></li>";
            } else {
                html += "<li><a id=\"page_" + pageVal + "\" onclick=\"cndSearch.exec('p', '" + pageVal + "');\" style=\"cursor:pointer;\">" + pageVal + "</a></li>";
            }
        }

        html += "<li><a onclick=\"paging.exec(" + (this.blockRight) + ");\" class=\"fa fa-angle-right\" style=\"cursor:pointer;\"></a></li>";

        $("#paging").html(html);

        if (cndSearch.searchFlag === true) {
            cndSearch.exec('p', page);
        }
    }
};
