<?php
header('Content-Type: application/json; charset=UTF-8');
date_default_timezone_set("Asia/Seoul");

// 데이터베이스 연결
include 'db_info.php';

$conn = new mysqli( $db_host, $db_user, $db_pwd, $db_category, $db_port );
if ($conn->connect_error) {
    die(json_encode(["error" => "DB 연결 실패: " . $conn->connect_error]));
}

// *** 입력값 필터링 ***
if (!isset($_REQUEST['RENTAL_FEE_PROD_ID'])) {
    echo json_encode(["error" => "요금제를 입력해주세요"]);
    die;
}

$rental_fee_prod_id = $_REQUEST['RENTAL_FEE_PROD_ID'];
$total_cnt = isset($_REQUEST['TOTAL_CNT']) ? (int)$_REQUEST['TOTAL_CNT'] : 1;
$order_num = isset($_REQUEST['ORDER_NUM']) ? $_REQUEST['ORDER_NUM'] : null;





// eSIM 요금제 일 수 설정
$esimDays = 0;
switch ($rental_fee_prod_id) {
    case "NA00007679": $esimDays = 1; break;
    case "NA00007680": $esimDays = 3; break;
    case "NA00007681": $esimDays = 5; break;
    case "NA00008761": $esimDays = 7; break;
    case "NA00007682": $esimDays = 10; break;
    case "NA00008762": $esimDays = 15; break;
    case "NA00007683": $esimDays = 20; break;
    case "NA00007684": $esimDays = 30; break;
    case "NA00008763": $esimDays = 60; break;
    case "NA00008764": $esimDays = 90; break;
    default:
        $esimDays = 0;
        break;
}

// API 컬럼 고정값
$apiType = 'api7';
$company = '프리피아';
$date = date("Ymd");
$roming_typ_cd = '16';
$post_sale_org_id = 'V992470000';
$dom_cntc_num = '0000';
$email_addr = 'cs@prepia.co.kr';
$rsv_rcv_dtm = date("YmdHis");
$nation_cd = 'GHA';
$rcmndr_id = '1313033433';
$cust_nm = 'PrepiaBulk';
$passeport_num = 'KR' . $date;

// 기본 데이터를 설정합니다.
$data = array(
    "apiType" => $apiType,
    "company" => $company,
    "rental_schd_sta_dtm" => $date,
    "rental_schd_end_dtm" => $date,
    "rental_sale_org_id" => $post_sale_org_id,
    "dom_cntc_num" => $dom_cntc_num,
    "email_addr" => $email_addr,
    "rsv_rcv_dtm" => $rsv_rcv_dtm,
    "roming_passport_num" => $passeport_num,
    "cust_nm" => $cust_nm,
    "nation_cd" => $nation_cd,
    "rcmndr_id" => $rcmndr_id,
    "total_cnt" => $total_cnt
);

// total_cnt 값을 사용하여 IN1 배열을 동적으로 생성합니다.
$inArray = array();
for ($i = 1; $i <= $total_cnt; $i++) {
    $suffix = str_pad($i, 2, '0', STR_PAD_LEFT); // 01, 02, 03 등으로 변환
    $rsv_vou_num = $order_num . '-' . $suffix; // 접미사 추가
    $inArray[] = array(
        "roming_typ_cd" => $roming_typ_cd,
        "rsv_vou_num" => $rsv_vou_num, // 누적된 번호를 포함
        "rental_fee_prod_id" => $rental_fee_prod_id,
    );
}
$data['IN1'] = $inArray;

// JSON으로 변환
$jsonData = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
if ($jsonData === false) {
    echo "JSON 인코딩 오류: " . json_last_error_msg();
    die;
}

// API 전송
$url = "https://www.skroaming.com/api/swinghub";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

$res = curl_exec($ch);

if ($res === false) {
    $error_msg = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo "cURL Error: " . $error_msg . " (HTTP Code: " . $http_code . ")";
    die;
}

curl_close($ch);

// JSON 디코딩 및 오류 확인
$resData = json_decode($res, true);
if ($resData === null && json_last_error() !== JSON_ERROR_NONE) {
    echo "JSON 디코딩 오류: " . json_last_error_msg();
    echo "서버 응답: " . $res;
    die;
}

// 응답 데이터 확인
if (!isset($resData['OUT1'])) {
    echo "OUT1 데이터가 응답에 없습니다. 응답 내용: " . json_encode($resData, JSON_UNESCAPED_UNICODE);
    die;
}

foreach ($resData['OUT1'] as $index => $item) {
    $rsv_vou_num = $data['IN1'][$index]['rsv_vou_num'];
    $rental_mst_num = $item['RENTAL_MST_NUM'];
    $eqp_mdl_cd = $item['EQP_MDL_CD'];
    $esim_mapping_id = $item['ESIM_MAPPING_ID'];
    $eqp_ser_num = $item['EQP_SER_NUM'];
    $roming_phon_num = $item['ROMING_PHON_NUM'];
    $roming_num = $item['ROMING_NUM'];

    // ESIM_MAPPING_ID를 '$' 기준으로 나누기
    $parts = explode('$', $esim_mapping_id, 3); // 최대 3개의 부분으로 분리

    if (count($parts) === 3) {
        // 첫 번째 부분(LPA:1$ 포함)을 smdp_address로 저장
        $smdp_address = $parts[0] . '$' . $parts[1]; // 'LPA:1$' 포함
        $activation_code = $parts[2]; // 세 번째 요소: activation_code
    } else {
        // 값이 잘못된 경우에 대한 처리
        $smdp_address = null;
        $activation_code = null;
    }

    // DB 저장
    $stmt = $conn->prepare("INSERT INTO t_esim_bulk_order_tb (order_num, esimDays, rental_mst_num, eqp_mdl_cd, esim_mapping_id, eqp_ser_num, roming_phon_num, roming_num, smdp_address, activation_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        echo "쿼리 준비 실패: " . $conn->error;
        die;
    }

    // bind_param에서 smdp_address와 activation_code 추가
    $stmt->bind_param("ssssssssss", $rsv_vou_num, $esimDays, $rental_mst_num, $eqp_mdl_cd, $esim_mapping_id, $eqp_ser_num, $roming_phon_num, $roming_num, $smdp_address, $activation_code);

    if (!$stmt->execute()) {
        echo "DB 저장 오류: " . $stmt->error;
        die;
    }

    $stmt->close();
}

echo json_encode(["success" => true, "data" => $resData], JSON_UNESCAPED_UNICODE);
$conn->close();
?>