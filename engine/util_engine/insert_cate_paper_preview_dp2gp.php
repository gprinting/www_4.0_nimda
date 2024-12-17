#! /usr/local/bin/php -f
<?php
/**
 * @file insert_cate_paper_preview_dp2gp.php
 *
 * @brief dprinting에서 gprinting으로 종이재질 사진/정보 이동
 */

include_once(dirname(__FILE__) . '/ConnectionPool.php');
include_once(dirname(__FILE__) . '/CommonDAO.php');
include_once("/home/sitemgr/inc/common_define/common_config.inc");

$dp = new ConnectionPool("mysqli", "172.16.33.225", "gprinting", "gpdb2021", "dprinting");
$gp = new ConnectionPool("mysqli", "172.16.33.225", "gprinting", "gpdb2021", "gprinting");

$dpConn = $dp->getPooledConnection();
$gpConn = $gp->getPooledConnection();

// 기준경로
$base_path = "/home/sitemgr/" . SITE_NET_DRIVE;

$query = "select * from paper_preview";

$dp_paper_photo_rs = $dpConn->Execute($query);

$gp_param_arr = [];

while ($dp_paper_photo_rs && !$dp_paper_photo_rs->EOF) {
    $fields = $dp_paper_photo_rs->fields;

    $name  = $fields["name"];
    $dvs   = $fields["dvs"];
    $color = $fields["color"];
    $file_path        = $fields["file_path"];
    $save_file_name   = $fields["save_file_name"];
    $origin_file_name = $fields["origin_file_name"];

    $gp_path = str_replace("/attach/", SITE_DEFAULT_ATTACH . '/', $file_path);

    if (!is_dir($base_path . $gp_path)) {
        mkdir($base_path . $gp_path, 0755, true);
    } else {
        if (!is_file($base_path . $gp_path . '/' . $save_file_name)) {
            copy($base_path . $file_path . '/' . $save_file_name,
                 $base_path . $gp_path . '/' . $save_file_name);
        }
    }

    $gp_param = [
         "file_path"        => $gp_path . '/'
        ,"save_file_name"   => $save_file_name
        ,"origin_file_name" => $origin_file_name
        ,"name"  => $name
        ,"dvs"   => $dvs
        ,"color" => $color
    ];

    $gp_param_arr[] = $gp_param;

    $dp_paper_photo_rs->MoveNext();
}

$query = "insert into paper_preview(file_path, save_file_name, origin_file_name, name, dvs, color) \n\tvalues ('%s', '%s', '%s', '%s', '%s', '%s');\n";

$gpConn->Debug = 1;
foreach ($gp_param_arr as $gp_param) {
    $q_str = sprintf($query, $gp_param["file_path"]
                           , $gp_param["save_file_name"]
                           , $gp_param["origin_file_name"]
                           , $gp_param["name"]
                           , $gp_param["dvs"]
                           , $gp_param["color"]);

    //echo $q_str;
    $gpConn->Execute($q_str);
}

$dpConn->Close();
$gpConn->Close();
