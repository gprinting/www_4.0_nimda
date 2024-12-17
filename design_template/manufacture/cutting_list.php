<!doctype html>
<html lang="ko">
<head>
    <title>[TPH_Vmain_title]</title>
    <meta charset="utf-8">
    [TPH_I/common/common.html]
</head>
<body>

<div id="container">

    <!-- TOP NAVIGATION -->
    [TPH_I/common/header.html]

    <!-- LEFT NAVIGATION -->
    [TPH_Vleft]

    <!-- CONTENT -->
    <section id="page-content" class="">
        <!-- 본문 컨텐츠  시작-->
        <div id="content-container" class="content_container">
            <!--메인상단 왼쪽 레이아웃-->
            <div id="left_content" class="left_content">
                <!--타이틀 시작-->
                <div class="title">
                    [TPH_I/common/history.html]
                </div>
                <!--타이틀 끝-->
                <!--기본 검색정보 시작-->
                <section class="content_section">
                    <div class="content_wrapper">
                        <table class="table_search_layout">
                            <tr>
                                <th class="th_text manu th_manu_print_list_01">
                                    프리셋 카테고리
                                </th>
                                <td colspan="3">
                                    <select class="select_manu_print_list_02" id="preset_cate">
                                        [TPH_Vcate_html]
                                    </select>
                                    <button type="button" class="btn_float_right btn_Turquoise01" onclick="searchProcess(30, 1);">검색</button>
                                </td>
                            </tr>
                            <tr>
                                <th class="th_text manu th_manu_print_list_01">
                                    날짜
                                </th>
                                <td colspan="3">
                                    <select id="date_cnd" class="select_member_common_cnd">
                                        <option value="regi_date">발주일</option>
                                    </select>
                                    <input type="text" class="datepicker_input input_manu_print_list_02" readonly="readonly" id="basic_from">-
                                    <input type="text" class="datepicker_input input_manu_print_list_02" readonly="readonly" id="basic_to">
                                    <span class="container_btn_align_02">
                                        <button type="button" class="btn_gray01" onclick="setDateVal('basic', 'd', -1,  0, $(this).text(), true);">어제</button>
                                        <button type="button" class="btn_gray01" onclick="setDateVal('basic', 't',  0,  0, $(this).text(), false);">오늘</button>
                                        <button type="button" class="btn_gray01" onclick="setDateVal('basic', 'd', -7,  0, $(this).text(), false);">일주일</button>
                                        <button type="button" class="btn_gray01" onclick="setDateVal('basic', 'm', -1,  0, $(this).text(), false);">한달</button>
                                        <button type="button" class="btn_gray01" onclick="setDateVal('basic', 'y', -1, -1, $(this).text(), true);">작년동기</button>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="th_text manu th_manu_print_list_01">
                                    인쇄업체 구분
                                </th>
                                <td>
                                    <select class="select_manu_print_list_03" id="extnl_etprs_seqno">
                                        [TPH_Vmanu_html]
                                    </select>
                                </td>
                                <th class="th_text manu th_manu_print_list_02">
                                    상태
                                </th>
                                <td style="padding-right:116px;">
                                    <select class="select_manu_print_list_01" id="state">
                                        [TPH_Vstate_html]
                                    </select>
                                </td>
                            </tr>
                        </table>
                        <form name="frm" id="frm">
                            <input type="hidden" id="seqno" name="seqno">
                        </form>
                    </div>
                </section>
                <!--기본검색정보 끝-->
                <!--검색정보 테이블 시작-->
                <section class="content_section">
                    <div class="content_wrapper">
                        <!-- 탭 박스 -->
                        <!-- 탭 박스 출력 -->
                        <ul class="table_top">
                            <li class="sel">
                            </li>
                            <li class="sel tar">
                                <button type="button" class="btn_yellow h_26" style="margin-right:4px;" onclick="multiFinish();">선택항목 완료처리</button>
                                <button type="button" class="btn_yellow h_26" onclick="goBarcode(); return false;">바코드 처리 페이지</button>
                            </li>
                        </ul>
                        <table class="table_basic_layout">
                            <thead>
                            <tr>
                                <th class=""><input type="checkbox" id="allCheck" onclick="allCheck();"></th>
                                <th class="">조판번호</th>
                                <th class="">종이</th>
                                <th class="">사이즈</th>
                                <th class="">인쇄도수</th>
                                <th class="">수량</th>
                                <th class="">특기사항</th>
                                <th class="">상태</th>
                                <th class="">관리</th>
                            </tr>
                            </thead>
                            <tbody id="list">
                            <tr><td colspan="9">검색 된 내용이 없습니다.</td></tr>
                            </tbody>
                        </table>
                        <p class="p_num_b fs12">
                            <select name="list_set" class="fix_width55" onchange="showPageSetting(this.value);">
                                <option>5</option>
                                <option>10</option>
                                <option>20</option>
                                <option selected="selected">30</option>
                            </select>
                            개씩 보기
                        </p>
                        <div class="tac clear" id="page"></div>
                        <!-- 탭 박스 -->
                    </div>
                </section>
                <!--검색정보 테이블 끝-->
            </div>
            <!--메인상단 왼쪽 레이아웃 끝-->
        </div>
        <!-- 본문 컨텐츠 끝 -->
    </section>
</div>
</body>
</html>
