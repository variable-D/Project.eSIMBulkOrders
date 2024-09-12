<?php
include 'db_info.php';

$varStartDt = isset($_REQUEST['start_dt'])? $_REQUEST['start_dt']:0; // 시작일
if( $varStartDt == 0 )
    $varStartDt = date("Y-m-d", time() - 86400 * 1); // 시작일 기본값은 1일 전

$varEndDt = isset($_REQUEST['end_dt'])? $_REQUEST['end_dt']:0;
if( $varEndDt == 0 )
    $varEndDt = date("Y-m-d", time() );		// 종료일 기본값은 현재일

$varSearch_order_id = isset($_REQUEST['search_order_id'])? trim($_REQUEST['search_order_id']):'';	// 주문번호/CTN/구입자명 검색
$varSearch_shop_no = isset($_REQUEST['search_shop_no'])? trim($_REQUEST['search_shop_no']):''; // 쇼핑몰 검색
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Manager Site</title>
    <link rel="stylesheet" type="text/css" href="./stylesheet.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

</head>

<body>
<h1>SKT-RED eSIM Bulk Order</h1>
<form id="esim_bulk_order">
    <ul class="esim_list_sch2_ul es-col7">
        <li>
            <p class="esim_list_sch2-subj">납품처</p>
            <select name="SHOP_NO" id="SHOP_NO" class="input_data">
                <option value="">선택</option>
                <option value="MD">명동사</option>
            </select>
        </li>
    </ul>
    <ul class="esim_list_sch2_ul es-col7">
        <li>
            <p class="esim_list_sch2-subj">수량</p>
            <input type="text" name="TOTAL_CNT" class="input_data" placeholder="20개 이하 작성." id="TOTAL_CNT">
        </li>
    </ul>

    <ul class="esim_list_sch2_ul es-col7 mt">
        <li>
            <p class="esim_list_sch2-subj">요금제</p>
            <select name="RENTAL_FEE_PROD_ID" id="RENTAL_FEE_PROD_ID" class="input_data">
                <option value="">선택</option>
                <option value="NA00007679">레드 eSIM 1일(수신불가)</option>
                <option value="NA00007680">레드 eSIM 3일(수신불가)</option>
                <option value="NA00007681">레드 eSIM 5일(수신불가)</option>
                <option value="NA00008761">레드 eSIM 7일(수신불가)</option>
                <option value="NA00007682">레드 eSIM 10일(수신불가)</option>
                <option value="NA00008762">레드 eSIM 15일(수신불가)</option>
                <option value="NA00007683">레드 eSIM 20일(수신불가)</option>
                <option value="NA00007684">레드 eSIM 30일(수신불가)</option>
                <option value="NA00008763">레드 eSIM 60일(수신불가)</option>
                <option value="NA00008764">레드 eSIM 90일(수신불가)</option>
            </select>
        </li>
        <li class="esim_list_sch2_submit">
            <input type="button" value="Create" onclick="esim_bulk_order()" class="sch2_submit-btn w100">
        </li>
    </ul>
</form>
<div class="esim_list">
    <div class="esim_list_wrap">
        <div class="esim_list_top">
            <?php If ( $varCallStep == "1" ) { ?>
                <form name="csv_down_form" action="<?= $selfFile ?>" method="post">
                    <input type="hidden" name="CallStep" value="1">
                    <input type="hidden" name="start_dt" value="<?= $varStartDt ?>">
                    <input type="hidden" name="end_dt" value="<?= $varEndDt ?>">
                    <input type="hidden" name="search_shop_no" value="<?= $varSearch_shop_no?>">
                    <input type="hidden" name="action_mode" value="csv_down">
                    <a href="#" onClick="check_csv_down_form();return false;" class="csv_download-btn">CSV Download<img src="./img/down-icon.png" alt=""></a>
                </form>
            <?php } ?>

            <div class="esim_list_sch1">
                <form name="input_form" action="<?= $selfFile ?>" method="post">
                    <input type="hidden" name="CallStep" value="1">

                    <ul class="esim_list_sch1_ul">
                        <li>
                            <input type="text" value="<?= $varStartDt ?>" size="12" name="start_dt" id="start_dt"/>
                            ~
                            <input type="text" value="<?= $varEndDt ?>" size="12" name="end_dt" id="end_dt"/>
                        </li>

                        <li>
                            <select name="search_shop_no" class="input_data">
                                <option value="">선택(쇼핑몰)</option>
                                <option value="1" <?= $varSearch_shop_no==1 ? 'selected' : '' ?>>명동사</option>
                            </select>
                            <input type="text" value="<?= $varSearch_order_id ?>" name="search_order_id" id="search_order_id" placeholder="주문번호/CNT"/>
                        </li>
                        <li class="esim_list_sch1_submit">
                            <input type="submit" id="search" value="Search" class="sch1_submit-btn">
                        </li>
                    </ul>
                </form>
            </div>
            <?php
            $conn = new mysqli($db_host, $db_user, $db_pwd, $db_category, $db_port);
            if ($conn->connect_error) {
                die("DB 연결 실패: " . $conn->connect_error);
            }
            // 데이터베이스 연결 후, 문자셋 설정
            $conn->set_charset("utf8mb4");
            // SQL 쿼리 실행
            $sql = "SELECT id, order_num, esimDays, rental_mst_num, created_at, roming_phon_num, esim_mapping_id, note, shop FROM t_esim_bulk_order_tb order by id desc limit 50";
            $result = $conn->query($sql);

            if ($result->num_rows > 0): ?>
                <form method="post" action="update_note.php"> <!-- note 수정 작업을 위한 form -->
                    <table border=0 cellpadding=1 cellspacing=1 class="list-tb">
                        <caption>주문 조회</caption>
                        <thead>
                        <tr height="24" bgcolor="#FFFFFF">
                            <td width=100>순번</td>
                            <td width=200>주문번호</td>
                            <td width=140>상품옵션(일자)</td>
                            <td width="120">거래처</td>
                            <td width=200>비고</td>
                            <td width=150>수정</td>
                            <td width=150>API 요청시간</td>
                            <td width=120>CTN</td>
                            <td width=300>QR Code Data</td>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $row_number = 1;
                        while ($row = $result->fetch_assoc()): ?>
                            <tr height="24" bgcolor="#FFFFFF">
                                <td align="center"><?= $row_number ?></td>
                                <td align="center"><?= htmlspecialchars($row["order_num"]) ?></td>
                                <td align="center">레드 eSIM <?= htmlspecialchars($row["esimDays"]) ?>일(수신불가)</td>
                                <td align="center"> <?php
                                    switch ($row["shop"]) {
                                        case'1':
                                            $shop_name = "명동사";
                                            break;
                                        default:
                                            $shop_name = "알 수 없음";
                                            break;
                                    }
                                    echo htmlspecialchars($shop_name); // 값 출력
                                    ?></td>
                                <td align="center">
                                    <textarea name="note[<?= $row['id'] ?>]" rows="3" cols="30"><?= htmlspecialchars($row["note"]) ?></textarea> <!-- 각 행의 note 필드 -->
                                </td>
                                <td align="center">
                                    <div class="list-mng-btn_wrap">
                                        <input type="submit" value="Modify" class="list-mng-btn btn-tp2 mt">
                                    </div>
                                </td>
                                <td align="center"><?= htmlspecialchars($row["created_at"]) ?></td>
                                <td align="center"><?= htmlspecialchars($row["roming_phon_num"]) ?></td>
                                <td align="center"><?= htmlspecialchars($row["esim_mapping_id"]) ?></td>
                            </tr>
                            <?php $row_number++; ?>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </form>
            <?php else: ?>
                <p>결과가 없습니다.</p>
            <?php endif; ?>

            <?php $conn->close(); ?>
            <script>
                document.getElementById('TOTAL_CNT').addEventListener('input', function() {
                    // 현재 값에서 숫자만 남기고 나머지를 제거
                    this.value = this.value.replace(/[^0-9]/g, '');

                    let value = parseInt(this.value, 10);
                    if (!isNaN(value) && value > 20) {
                        alert('수량은 20개 이하로 작성해야 합니다.');
                        this.value = '';  // 입력값을 초기화
                    }
                });

                const esim_bulk_order = () => {
                    // 1. yyyymmdd 형식의 날짜 가져오기
                    const today = new Date();
                    const yyyymmdd = today.toISOString().slice(0, 10).replace(/-/g, '');

                    // 2. shop_no 값 가져오기 (예: "MD")
                    const shopNo = document.getElementById('SHOP_NO').value;

                    // 값이 비어있으면 리다이렉션
                    if (!shopNo) {
                        alert('쇼핑몰을 선택해주세요.');
                        return window.location.reload();
                    }

                    // 3. 0 ~ 9999 범위의 난수 생성 및 패딩
                    const random1Padded = Math.floor(Math.random() * 10000).toString().padStart(4, '0');

                    // 4. yyyymmdd 숫자 합 + 0 ~ 9999 난수
                    const yyyymmddSum = [...yyyymmdd].reduce((sum, digit) => sum + parseInt(digit, 10), 0);
                    const random2 = Math.floor(Math.random() * 10000);
                    const checksum = (yyyymmddSum + random2) % 10;

                    // 5. 최종 주문 번호 생성 (yyyymmdd + shop_no + 난수 + 체크섬)
                    const orderNum = `${yyyymmdd}${shopNo}${random1Padded}${checksum}`;

                    // 6. window.confirm 메시지에 orderNum 포함
                    if (!window.confirm(
                        `eSIM Bulk Order API 요청을 하시겠습니까?\n\n- 수량: ${document.getElementById('TOTAL_CNT').value}\n- 요금제: ${document.getElementById('RENTAL_FEE_PROD_ID').options[document.getElementById('RENTAL_FEE_PROD_ID').selectedIndex].text}\n- 주문 번호: ${orderNum}`
                    )) {
                        return window.location.reload();
                    }

                    // 7. FormData에 orderNum 추가 후 fetch 요청
                    const formData = new FormData(document.getElementById('esim_bulk_order'));
                    formData.append('ORDER_NUM', orderNum);
                    formData.append('SHOP_NO', document.getElementById('SHOP_NO').value);

                    fetch('/mobile_app/mgr/esim_bulk_order.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`Server returned an error: ${response.status} - ${response.statusText}`);
                            }
                            return response.text(); // 응답을 텍스트로 처리
                        })
                        .then(data => {
                            console.log('응답 데이터:', data);
                            try {
                                const jsonData = JSON.parse(data);
                                alert('API 요청 성공: ' + JSON.stringify(jsonData));
                            } catch (error) {
                                alert('API 요청 성공: ' + data);  // JSON이 아닐 경우 텍스트로 처리
                            }finally {
                                window.location.reload();
                            }
                        })
                        .catch(error => {
                            console.error('API 요청 실패:', error);
                            alert('API 요청 실패: ' + error.message);
                        });
                }

                $( "#start_dt" ).datepicker({ dateFormat: 'yy-mm-dd'});
                $( "#end_dt" ).datepicker({ dateFormat: 'yy-mm-dd'});
            </script>
</body>
</html>