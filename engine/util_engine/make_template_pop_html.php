#! /usr/local/bin/php -f
<?php
/**
 * @file make_template_pop_html.php
 *
 * @brief 카테고리 템플릿 팝업 html 재생성
 */
include_once(dirname(__FILE__) . '/ConnectionPool.php');
include_once("/home/sitemgr/inc/com/nexmotion/html/nimda/dataproc_mng/set/TemplateMngHTML.inc");

$connectionPool = new ConnectionPool("mysqli", "211.110.168.85", "dpuser01", "gpdb2021", "gprinting");
$conn = $connectionPool->getPooledConnection();

$query  = "\n SELECT  sortcode, cate_name";
$query .= "\n   FROM  cate";
$query .= "\n  WHERE  cate_level = 3";
$query .= "\n  ORDER BY sortcode";

$rs = $conn->Execute($query);

while ($rs && !$rs->EOF) {
    $query  = "\n SELECT  cate_template_seqno";
    $query .= "\n        ,uniq_num";
    $query .= "\n        ,stan_name";
    $query .= "\n        ,CONCAT(cut_wid_size, '*', cut_vert_size) AS cut_size";
    $query .= "\n        ,CONCAT(work_wid_size, '*', work_vert_size) AS work_size";
    $query .= "\n        ,ai_origin_file_name";
    $query .= "\n        ,eps_origin_file_name";
    $query .= "\n        ,cdr_origin_file_name";
    $query .= "\n        ,sit_origin_file_name";
    $query .= "\n   FROM  cate_template";
    $query .= "\n  WHERE  1 = 1";
    $query .= "\n    AND  cate_sortcode = '" . $rs->fields["sortcode"] . "'";

    $rs2 = $conn->Execute($query);

    //$html = makeTemplatePopHtml($rs2, ["cate_name" => $rs->fields["cate_name"]]);

    //$dest = "/home/sitemgr/front/ajax/product/template_pop/" . $rs->fields["sortcode"] . ".html";

    //$fd = @fopen($dest, 'w');
    //@fwrite($fd, $html);
    //@fclose($fd);

    echo $rs->fields["sortcode"] . ' / ' . $rs->fields["cate_name"] . "\n";

    $rs->MoveNext();
}

