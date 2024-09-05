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
    echo "요금제를 입력해주세요";
    die;
}

$rental_fee_prod_id = $_REQUEST['RENTAL_FEE_PROD_ID'];
$total_cnt = isset($_REQUEST['TOTAL_CNT']) ? $_REQUEST['TOTAL_CNT'] : 1;
$order_num = isset($_REQUEST['ORDER_NUM']) ? $_REQUEST['ORDER_NUM'] : null;

// eSIM 요금제 일 수 설정
$esimDays = 0;
switch ($rental_fee_prod_id) {
    case "NA00007679":
        $esimDays = 1;
        break;
    case "NA00007680":
        $esimDays = 3;
        break;
    case "NA00007681":
        $esimDays = 5;
        break;
    case "NA00008761":
        $esimDays = 7;
        break;
    case "NA00007682":
        $esimDays = 10;
        break;
    case "NA00008762":
        $esimDays = 15;
        break;
    case "NA00007683":
        $esimDays = 20;
        break;
    case "NA00007684":
        $esimDays = 30;
        break;
    case "NA00008763":
        $esimDays = 60;
        break;
    case "NA00008764":
        $esimDays = 90;
        break;
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
$passeport_num = 'KR'.$date;

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
$totalCnt = (int)$data['total_cnt']; // total_cnt 값을 정수로 변환

for ($i = 1; $i <= $totalCnt; $i++) {
    $suffix = str_pad($i, 2, '0', STR_PAD_LEFT); // 01, 02, 03 등으로 변환
    $rsv_vou_num = $order_num . '-' . $suffix; // 접미사 추가
    $inArray[] = array(
        "roming_typ_cd" => $roming_typ_cd,
        "rsv_vou_num" => $rsv_vou_num, // 누적된 번호를 포함
        "rental_fee_prod_id" => $rental_fee_prod_id,
    );
}
// 생성된 배열을 $data 배열에 추가합니다.
$data['IN1'] = $inArray;

// JSON으로 변환
$jsonData = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
if ($jsonData === false) {
    echo "JSON 인코딩 오류: " . json_last_error_msg();
    die;
}

// API 전송
$url = "https://www.skroaming.com/api/swinghub";  // 라이브

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
    curl_close($ch);
    echo "cURL Error: " . $error_msg;
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

// API 리턴값 오류 확인
if (isset($resData['OUT1']) && is_array($resData['OUT1'])) {
    foreach ($resData['OUT1'] as $index => $item) {
        // IN1 배열의 rsv_vou_num 값을 사용
        $rsv_vou_num = $data['IN1'][$index]['rsv_vou_num'];

        // 각 $item을 처리하는 코드
        $stmt = $conn->prepare("INSERT INTO esim_bulk_order_tb (order_num, final_json_data, esimDays) VALUES (?, ?, ?)");
        $json_item_data = json_encode($item, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $stmt->bind_param("sss", $rsv_vou_num, $json_item_data, $esimDays);

        if (!$stmt->execute()) {
            echo "DB 저장 오류: " . $stmt->error;
            die;
        }

        $stmt->close();
    }
} else {
    error_log("OUT1 데이터가 응답에 없습니다. 응답 내용: " . json_encode($resData));
    die("API 응답에 OUT1 데이터가 없습니다.");
}

// 정상적으로 처리된 경우
echo json_encode(["success" => true, "data" => $resData], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

$conn->close();

?>