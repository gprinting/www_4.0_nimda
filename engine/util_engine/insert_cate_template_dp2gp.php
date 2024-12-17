#! /usr/local/bin/php -f
<?php
/**
 * @file insert_cate_photo_dp2gp.php
 *
 * @brief dprinting에서 gprinting으로 카테고리 템플릿 파일/정보 이동
 */

include_once(dirname(__FILE__) . '/ConnectionPool.php');
include_once(dirname(__FILE__) . '/CommonDAO.php');
include_once("/home/sitemgr/inc/common_define/common_config.inc");

$dp = new ConnectionPool("mysqli", "211.110.168.85", "dpuser01", "gpdb2021", "dprinting");
$gp = new ConnectionPool("mysqli", "211.110.168.85", "dpuser01", "gpdb2021", "gprinting");

$dpConn = $dp->getPooledConnection();
$gpConn = $gp->getPooledConnection();

// 카테고리 
$CATE_MATCH_ARR = [
     "001001001" => "003001001"
    ,"001001002" => "003001003"
    ,"001001004" => "003001002"
    ,"001001005" => "003001004"
    ,"001002001" => "003002001"
    ,"001002002" => -1
    ,"001002003" => -1
    ,"001002004" => -1
    ,"001002005" => -1
    ,"001002006" => -1
    ,"001002007" => -1
    ,"001002008" => -1
    ,"001002009" => -1
    ,"001002010" => -1
    ,"001002011" => -1
    ,"001002012" => -1
    ,"001002013" => -1
    ,"001002014" => -1
    ,"001002015" => -1
    ,"001002016" => -1
    ,"001002017" => -1
    ,"001002018" => -1
    ,"001003001" => "003003001"
    ,"001003002" => -1
    ,"001003003" => -1
    ,"001003004" => -1
    ,"001003005" => -1
    ,"001003006" => -1
    ,"001003007" => -1
    ,"001003008" => -1
    ,"001003009" => -1
    ,"001003010" => -1
    ,"001003011" => -1
    ,"001003012" => -1
    ,"001003013" => -1
    ,"001003014" => -1
    ,"001003015" => -1
    ,"001003016" => -1
    ,"001003017" => -1
    ,"001003018" => -1
    ,"001003019" => -1
    ,"001003020" => -1
    ,"001003021" => -1
    ,"001003022" => -1
    ,"001004001" => -1
    ,"001004002" => -1
    ,"001004003" => -1
    ,"001004004" => -1
    ,"001004006" => -1
    ,"001005001" => -1
    ,"001005002" => "008001001"
    ,"001005003" => -1
    ,"001005004" => -1
    ,"001005005" => -1
    ,"001005006" => "008002001"

    ,"002001001" => "004001001"
    ,"002001002" => -1
    ,"002001003" => "004002001"
    ,"002001004" => -1
    ,"002001005" => -1
    ,"002001006" => "008002002"
    ,"002001007" => -1
    ,"002001008" => -1
    ,"002001009" => "008002003"
    ,"002001010" => -1
    ,"002002001" => "004003002"
    ,"002002002" => "004003004"
    ,"002002003" => "004003007"
    ,"002002004" => "004003003"
    ,"002002005" => "004003006"
    ,"002002006" => "004003005"
    ,"002002007" => "004003001"
    ,"002002008" => "004003008"
    ,"002002009" => -1
    ,"002002010" => "004003009"
    ,"002003001" => -1
    ,"002004009" => -1

    ,"003001001" => "005001001"
    ,"003001002" => -1
    ,"003001003" => -1
    ,"003003001" => "005002001"
    ,"003003002" => -1
    ,"003003003" => -1
    ,"003003004" => -1

    ,"004001001" => "001001001"
    ,"004001002" => -1
    ,"004001003" => -1
    ,"004001004" => -1
    ,"004002001" => -1
    ,"004002002" => -1
    ,"004002003" => -1
    ,"004002004" => -1

    ,"004003001" => "001003003"
    ,"004003002" => -1
    ,"004003003" => "001002002"
    ,"004003004" => -1
    ,"004003005" => "008001002"
    ,"004003006" => "001004004"
    ,"004003007" => "008001003"
    ,"004003008" => "008001005"
    ,"004003009" => "008001006"
    ,"004003010" => -1
    ,"004003011" => "008001004"

    ,"005001001" => "006001001"
    ,"005001002" => -1
    ,"005001003" => -1
    ,"005001004" => -1
    ,"005001005" => -1
    ,"005001006" => "006001002"
    ,"005003001" => ["006002001", "006002002"]
    ,"005003002" => "006002003"
    ,"005003003" => "006002004"
    ,"005003004" => "006002005"
    ,"005003005" => "006002006"
    ,"005003006" => "006002007"
    ,"005003007" => "006002008"
    ,"005003008" => "006002009"
    ,"005003009" => "006002010"

    ,"006001001" => "007001001"
    ,"006001002" => "007001002"
    ,"006001003" => "007001003"
    ,"006002001" => "007002001"

    ,"007001001" => "005003001"
    ,"007001002" => -1
    ,"007001003" => -1
    ,"007001004" => -1

    ,"008001001" => "009001001"
    ,"008001002" => "009001002"
];

// 기준경로
$base_path = "/home/sitemgr/" . SITE_NET_DRIVE;

$query = "select * from cate_template";

$dp_cate_template_rs = $dpConn->Execute($query);

$ext_arr = ["ai", "eps", "cdr", "sit"];

$gp_param_arr = [];

while ($dp_cate_template_rs && !$dp_cate_template_rs->EOF) {
    $fields = $dp_cate_template_rs->fields;

    $cate_sortcode  = $fields["cate_sortcode"];
    $uniq_num       = $fields["uniq_num"];
    $stan_name      = $fields["stan_name"];
    $cut_wid_size   = $fields["cut_wid_size"];
    $cut_vert_size  = $fields["cut_vert_size"];
    $work_wid_size  = $fields["work_wid_size"];
    $work_vert_size = $fields["work_vert_size"];

    $match_sortcode = $CATE_MATCH_ARR[$cate_sortcode];

    if ($match_sortcode < 0) {
        $dp_cate_template_rs->MoveNext();
        continue;
    }

    $gp_param = [];
    $gp_param["cate_sortcode"]  = $cate_sortcode;
    $gp_param["uniq_num"]       = $uniq_num;
    $gp_param["stan_name"]      = $stan_name;
    $gp_param["cut_wid_size"]   = $cut_wid_size;
    $gp_param["cut_vert_size"]  = $cut_vert_size;
    $gp_param["work_wid_size"]  = $work_wid_size;
    $gp_param["work_vert_size"] = $work_vert_size;

    foreach ($ext_arr as $ext) {
        $file_path        = $fields[$ext . "_file_path"];
        $save_file_name   = $fields[$ext . "_save_file_name"];
        $origin_file_name = $fields[$ext . "_origin_file_name"];

        if (!empty($file_path)) {
            $gp_path = str_replace("/attach/", SITE_DEFAULT_ATTACH . '/', $file_path);

            if (!is_dir($base_path . $gp_path)) {
                mkdir($base_path . $gp_path, 0755, true);
            } else {
                if (!is_file($base_path . $gp_path . '/' . $save_file_name)) {
                    copy($base_path . $file_path . '/' . $save_file_name,
                         $base_path . $gp_path . '/' . $save_file_name);
                }
            }

            if (is_array($match_sortcode)) {
                foreach ($match_sortcode as $sortcode) {
                    $gp_param[$ext . "_file_path"]        = $gp_path . '/';
                    $gp_param[$ext . "_save_file_name"]   = $save_file_name;
                    $gp_param[$ext . "_origin_file_name"] = $origin_file_name;
                    $gp_param["cate_sortcode"] = $sortcode;
                }
            } else {
                $gp_param[$ext . "_file_path"]        = $gp_path . '/';
                $gp_param[$ext . "_save_file_name"]   = $save_file_name;
                $gp_param[$ext . "_origin_file_name"] = $origin_file_name;
                $gp_param["cate_sortcode"] = $match_sortcode;
            }
        }
    }

    if (!empty($gp_param)) {
        $gp_param_arr[] = $gp_param;
    }

    $dp_cate_template_rs->MoveNext();
}

$query = "insert into cate_template(cate_sortcode, uniq_num, stan_name, cut_wid_size, cut_vert_size, work_wid_size, work_vert_size, ai_file_path, ai_save_file_name, ai_origin_file_name, eps_file_path, eps_save_file_name, eps_origin_file_name, cdr_file_path, cdr_save_file_name, cdr_origin_file_name, sit_file_path, sit_save_file_name, sit_origin_file_name) \n\tvalues ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s');\n";

$gpConn->Debug = 1;
foreach ($gp_param_arr as $gp_param) {
    $q_str = sprintf($query, $gp_param["cate_sortcode"]
                           , $gp_param["uniq_num"]
                           , $gp_param["stan_name"]
                           , $gp_param["cut_wid_size"]
                           , $gp_param["cut_vert_size"]
                           , $gp_param["work_wid_size"]
                           , $gp_param["work_vert_size"]
                           , $gp_param["ai_file_path"]
                           , $gp_param["ai_save_file_name"]
                           , $gp_param["ai_origin_file_name"]
                           , $gp_param["eps_file_path"]
                           , $gp_param["eps_save_file_name"]
                           , $gp_param["eps_origin_file_name"]
                           , $gp_param["cdr_file_path"]
                           , $gp_param["cdr_save_file_name"]
                           , $gp_param["cdr_origin_file_name"]
                           , $gp_param["sit_file_path"]
                           , $gp_param["sit_save_file_name"]
                           , $gp_param["sit_origin_file_name"]);

    $gpConn->Execute($q_str);
}

$dpConn->Close();
$gpConn->Close();
