<?php
include 'db_info.php';
date_default_timezone_set('Asia/Seoul');

// 기본 값 설정
$varCallStep = isset($_REQUEST['CallStep']) ? $_REQUEST['CallStep'] : '0'; // CallStep: 0은 처음 로드, 1은 검색 버튼 클릭 시
$varStartDt = isset($_REQUEST['start_dt']) ? $_REQUEST['start_dt'] : date("Y-m-d", time() - 86400); // 기본값: 1일 전
$varEndDt = isset($_REQUEST['end_dt']) ? $_REQUEST['end_dt'] : date("Y-m-d", time()); // 기본값: 현재일
$endDtWithTime = $varEndDt . " 23:59:59";
$varSearch_order_id = isset($_REQUEST['search_order_id']) ? trim($_REQUEST['search_order_id']) : ''; // 주문번호/CTN 검색

// 노트 및 isrefunded 업데이트 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['note_update'])) {
    $note_id = $_POST['note_id'];
    $note_content = $_POST['note_content'];
    $isrefunded = isset($_POST['isrefunded']) ? 1 : 0;

    // DB 연결
    $conn = new mysqli($db_host, $db_user, $db_pwd, $db_category, $db_port);
    if ($conn->connect_error) {
        die("DB 연결 실패: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");

    // 노트와 isrefunded 업데이트 쿼리 실행
    $stmt = $conn->prepare("UPDATE t_esim_bulk_order_tb SET note=?, isrefunded=? WHERE id=?");
    $stmt->bind_param("sii", $note_content, $isrefunded, $note_id);

    if ($stmt->execute()) {
        // 업데이트 성공 시, 데이터를 다시 로드하도록 $varCallStep를 유지하고 넘어갑니다.
        $stmt->close();
        $conn->close();
        // 여기서 리다이렉트 대신 업데이트된 데이터로 진행합니다.
    } else {
        echo "노트 업데이트 실패: " . $stmt->error;
    }
}

// 정산 CSV 파일 다운로드 처리
if (isset($_POST['download_csv_calculate'])) { // 엑셀 다운로드 버튼 클릭 여부를 확인
    // DB 연결
    $conn = new mysqli($db_host, $db_user, $db_pwd, $db_category, $db_port);
    if ($conn->connect_error) {
        die("DB 연결 실패: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4"); // utf8mb4로 변경

    // SQL 쿼리
    $sql = "SELECT order_num, roming_phon_num, created_at, isrefunded, note, esimDays
            FROM t_esim_bulk_order_tb
            WHERE created_at BETWEEN '$varStartDt' AND '$endDtWithTime'";

    if ($varSearch_order_id != '') {
        $sql .= " AND (order_num LIKE '%$varSearch_order_id%' OR roming_phon_num LIKE '%$varSearch_order_id%')";
    }
    $sql .= " ORDER BY id DESC";

    $result = $conn->query($sql);

    // CSV 파일 생성
    $filename = 'eSIM_Bulk_Order_Cal_' . date('Ymd') . '.csv';

    // UTF-8 BOM 추가
    $bom = chr(0xEF) . chr(0xBB) . chr(0xBF);

    header('Content-Type: text/csv; charset=UTF-8');
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');

    // BOM 출력
    fwrite($output, $bom);

    // CSV 헤더 작성
    fputcsv($output, ['주문번호','상품명','옵션','비고','환불여부','CTN','API 요청시간']);

    // 데이터를 CSV 파일에 입력
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $isRefunded = $row['isrefunded'] == 0 ? 'X' : 'O';
            $product = '레드';
            fputcsv($output, [
                $row['order_num'],
                $product,
                $row['esimDays'],
                $row['note'],
                $isRefunded,
                $row['roming_phon_num'],
                $row['created_at']
            ]);
        }
    }

    fclose($output);
    $conn->close();
    exit; // 다운로드 후 더 이상의 처리가 없도록 종료
}

// CSV 파일 다운로드 처리
if (isset($_POST['download_csv_order'])) { // 엑셀 다운로드 버튼 클릭 여부를 확인
    // DB 연결
    $conn = new mysqli($db_host, $db_user, $db_pwd, $db_category, $db_port);
    if ($conn->connect_error) {
        die("DB 연결 실패: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4"); // utf8mb4로 변경

    // SQL 쿼리
    $sql = "SELECT order_num, roming_phon_num, esimDays, smdp_address, activation_code, esim_mapping_id, isrefunded, note
            FROM t_esim_bulk_order_tb
            WHERE created_at BETWEEN '$varStartDt' AND '$endDtWithTime' AND csv_downloaded = 0";

    if ($varSearch_order_id != '') {
        $sql .= " AND (order_num LIKE '%$varSearch_order_id%' OR roming_phon_num LIKE '%$varSearch_order_id%')";
    }
    $sql .= " ORDER BY id DESC";

    $result = $conn->query($sql);

    // CSV 파일 생성
    $filename = 'eSIM_Bulk_Order_' . date('Ymd') . '.csv';

    // UTF-8 BOM 추가
    $bom = chr(0xEF) . chr(0xBB) . chr(0xBF);

    header('Content-Type: text/csv; charset=UTF-8');
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');

    // BOM 출력
    fwrite($output, $bom);

    // CSV 헤더 작성
    fputcsv($output, ['주문번호','일자','CTN','SMDP Address','Activation Code','LPA']);

    // 데이터를 CSV 파일에 입력
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if($row['csv_downloaded'] !== 0 && $row['isrefunded'] !== 0 && $row['note'] !== null) {
                return;
            }
            fputcsv($output, [
                $row['order_num'],
                $row['esimDays'],
                $row['roming_phon_num'],
                $row['smdp_address'],
                $row['activation_code'],
                $row['esim_mapping_id']
            ]);
            $sql1 = "UPDATE t_esim_bulk_order_tb SET csv_downloaded = 1 WHERE order_num = '".$row['order_num']."'";
            $conn->query($sql1);
        }
    }

    fclose($output);
    $conn->close();
    exit; // 다운로드 후 더 이상의 처리가 없도록 종료
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>Manager Site</title>
    <link rel="stylesheet" href="./stylesheet.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
</head>
<body>

<h1>SKT-RED eSIM Bulk Order</h1>

<!-- Bulk Order Form -->
<form id="esim_bulk_order">
    <ul class="esim_list_sch2_ul es-col7">
        <li>
            <p class="esim_list_sch2-subj">수량</p>
            <input type="text" name="TOTAL_CNT" class="input_data" placeholder="20개 이하 작성" id="TOTAL_CNT" required>
        </li>
    </ul>
    <ul class="esim_list_sch2_ul es-col7 mt">
        <li>
            <p class="esim_list_sch2-subj">요금제</p>
            <select name="RENTAL_FEE_PROD_ID" id="RENTAL_FEE_PROD_ID" class="input_data" required>
                <option value="">선택</option>
                <option value="NA00007679">레드 eSIM 1일(수신불가)</option>
                <option value="NA00008249">레드 eSIM 3일(수신불가)</option>
                <option value="NA00008250">레드 eSIM 5일(수신불가)</option>
                <option value="NA00008777">레드 eSIM 7일(수신불가)</option>
                <option value="NA00008251">레드 eSIM 10일(수신불가)</option>
                <option value="NA00008778">레드 eSIM 15일(수신불가)</option>
                <option value="NA00008252">레드 eSIM 20일(수신불가)</option>
                <option value="NA00008253">레드 eSIM 30일(수신불가)</option>
                <option value="NA00008779">레드 eSIM 60일(수신불가)</option>
                <option value="NA00008780">레드 eSIM 90일(수신불가)</option>
            </select>
        </li>
        <li class="esim_list_sch2_submit">
            <input type="button" value="Create" onclick="esim_bulk_order()" class="sch2_submit-btn w100">
        </li>
    </ul>
</form>
<br/>
<!-- Search Form -->


<!-- 검색 결과 출력 --><div class="esim_list_sch1">
    <form name="input_form" action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
        <input type="hidden" name="CallStep" value="1">
        <ul class="esim_list_sch1_ul">
            <li>
                <input type="text" name="start_dt" id="start_dt" value="<?= htmlspecialchars($varStartDt) ?>"/> ~
                <input type="text" name="end_dt" id="end_dt" value="<?= htmlspecialchars($varEndDt) ?>"/>
            </li>
            <li>
                <input type="text" name="search_order_id" value="<?= htmlspecialchars($varSearch_order_id) ?>" placeholder="주문번호/CTN"/>
            </li>
            <li>
                <input class="sch1_submit-btn" type="submit" value="Search">
            </li>
            <li>
                <button class="sch1_submit-btn" type="submit" name="download_csv_order">주문-CSV</button>
            </li>
            <li>
                <button class="sch1_submit-btn" type="submit" name="download_csv_calculate">정산-CSV</button>
            </li>
        </ul>
    </form>
</div>
<?php if ($varCallStep == "1"): ?>
    <?php
    // DB 연결
    $conn = new mysqli($db_host, $db_user, $db_pwd, $db_category, $db_port);
    if ($conn->connect_error) {
        die("DB 연결 실패: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");

    // SQL 쿼리 생성
    if ($varSearch_order_id != '') {
        // 주문번호나 CTN으로 검색 시 날짜 조건을 무시합니다.
        $sql = "SELECT id, order_num, esimDays, rental_mst_num, created_at, roming_phon_num, esim_mapping_id, note, isrefunded, csv_downloaded, rental_mgmt_num
                FROM t_esim_bulk_order_tb
                WHERE (order_num LIKE '%$varSearch_order_id%' OR roming_phon_num LIKE '%$varSearch_order_id%')";
    } else {
        // 날짜로 검색
        $sql = "SELECT id, order_num, esimDays, rental_mst_num, created_at, roming_phon_num, esim_mapping_id, note, isrefunded, csv_downloaded, rental_mgmt_num
                FROM t_esim_bulk_order_tb
                WHERE created_at BETWEEN '$varStartDt' AND '$endDtWithTime'";
    }
    $sql .= " ORDER BY id DESC";

    $result = $conn->query($sql);

    if ($result->num_rows > 0): ?>
        <table border="0" cellpadding="1" cellspacing="1" class="list-tb">
            <caption>주문 조회</caption>
            <thead>
            <tr height="24" bgcolor="#FFFFFF">
                <th width="100">순번</th>
                <th width="200">주문번호</th>
                <th width="140">상품옵션(일자)</th>
                <th width="200">비고</th>
                <th width="120">환불</th>
                <th width="150">수정</th>
                <th width="120">csvDownloaded</th>
                <th width="150">API 요청시간</th>
                <th width="120">CTN</th>
                <th width="120">Rental_Mgmt_Num</th>
                <th width="300">QR Code Data</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $row_number = 1;
            while ($row = $result->fetch_assoc()):
                // 필요한 정보를 변수에 저장
                $order_num = htmlspecialchars($row["order_num"]);
                $esimDays = htmlspecialchars($row["esimDays"]);
                $roming_phon_num = htmlspecialchars($row["roming_phon_num"]);

                ?>
                <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <input type="hidden" name="CallStep" value="1">
                    <input type="hidden" name="note_update" value="1">
                    <input type="hidden" name="note_id" value="<?php echo $row['id']; ?>">
                    <!-- 기존 검색 조건을 유지하기 위해 hidden input에 값들을 저장 -->
                    <input type="hidden" name="start_dt" value="<?php echo htmlspecialchars($varStartDt); ?>">
                    <input type="hidden" name="end_dt" value="<?php echo htmlspecialchars($varEndDt); ?>">
                    <input type="hidden" name="search_order_id" value="<?php echo htmlspecialchars($varSearch_order_id); ?>">
                    <tr height="24" bgcolor="#FFFFFF">
                        <td align="center"><?php echo $row_number; ?></td>
                        <td align="center">
                            <!-- 주문번호를 클릭 가능한 링크로 변경하고 클래스와 데이터 속성 추가 -->
                            <a href="#" class="copyOrderInfo"
                               data-order="<?php echo $order_num; ?>"
                               data-product="<?php echo $esimDays; ?>"
                               data-ctn="<?php echo $roming_phon_num; ?>">
                                <?php echo $order_num; ?>
                            </a>
                        </td>
                        <td align="center">레드 eSIM <?php echo $esimDays; ?>일(수신불가)</td>
                        <td align="center">
                            <!-- 각 note 필드를 개별적으로 수정 가능 -->
                            <textarea name="note_content" rows="3" cols="30"><?php echo htmlspecialchars($row["note"]); ?></textarea>
                        </td>
                        <!-- 환불 (isrefunded) 체크박스 -->
                        <td align="center">
                            <input type="checkbox" name="isrefunded" value="1" <?php if ($row['isrefunded'] == 1) echo 'checked'; ?>>
                        </td>
                        <td align="center">
                            <div class="list-mng-btn_wrap">
                                <input type="submit" value="Modify" class="list-mng-btn btn-tp2 mt">
                            </div>
                        </td>
                        <!-- csvDownloaded 체크박스 -->
                        <td align="center" <?php if ($row['csv_downloaded'] == 1) echo 'style="background-color: orange;"'; ?>>
                            <input type="checkbox" disabled <?php if ($row['csv_downloaded'] == 1) echo 'checked'; ?>>
                        </td>
                        <td align="center"><?php echo htmlspecialchars($row["created_at"]); ?></td>
                        <td align="center"><?php echo $roming_phon_num; ?></td>
                        <td align="center"><?php echo htmlspecialchars($row["rental_mgmt_num"]); ?></td>
                        <td align="center"><?php echo htmlspecialchars($row["esim_mapping_id"]); ?></td>
                    </tr>
                </form>
                <?php $row_number++; ?>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p align="center" style="font-size: 100px">결과가 없습니다.</p>
    <?php endif;

    $conn->close();
    ?>
<?php endif; ?>

<script>
    // 주문번호 클릭 시 정보 복사 기능 추가
    $(document).ready(function() {
        $('.copyOrderInfo').click(function(e) {
            e.preventDefault();

            var orderNumber = $(this).data('order');
            var productDays = $(this).data('product');
            var ctn = $(this).data('ctn');

            var copyText = '주문번호: ' + orderNumber + '\n' +
                '상품(일자): 레드 eSIM ' + productDays + '일(수신불가)\n' +
                'CTN: ' + ctn;

            // 클립보드에 복사
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(copyText).then(function() {
                    alert('다음 정보가 복사되었습니다:\n\n' + copyText);
                }, function(err) {
                    console.error('복사 실패:', err);
                    alert('복사 실패: ' + err);
                });
            } else {
                // 비동기 클립보드 API를 사용할 수 없는 경우
                var tempElem = $('<textarea>');
                $('body').append(tempElem);
                tempElem.val(copyText).select();
                document.execCommand('copy');
                tempElem.remove();
                alert('다음 정보가 복사되었습니다:\n\n' + copyText);
            }
        });
    });

    document.getElementById('TOTAL_CNT').addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');

        let value = parseInt(this.value, 10);
        if (!isNaN(value) && value > 20) {
            alert('수량은 20개 이하로 작성해야 합니다.');
            this.value = '';  // 입력값을 초기화
        }
    });


    const esim_bulk_order = () => {
        const today = new Date();
        const yyyymmdd = today.toISOString().slice(0, 10).replace(/-/g, '');
        const uniqueId = 'BK';


        const random1Padded = Math.floor(Math.random() * 10000).toString().padStart(4, '0');
        const yyyymmddSum = [...yyyymmdd].reduce((sum, digit) => sum + parseInt(digit, 10), 0);
        const random2 = Math.floor(Math.random() * 10000);
        const checksum = (yyyymmddSum + random2) % 10;

        const orderNum = `${yyyymmdd}${uniqueId}${random1Padded}${checksum}`;

        if (!window.confirm(
            `eSIM Bulk Order API 요청을 하시겠습니까?\n\n- 수량: ${document.getElementById('TOTAL_CNT').value}\n- 요금제: ${document.getElementById('RENTAL_FEE_PROD_ID').options[document.getElementById('RENTAL_FEE_PROD_ID').selectedIndex].text}\n- 주문 번호: ${orderNum}`
        )) {
            return;
        }

        const formData = new FormData(document.getElementById('esim_bulk_order'));
        formData.append('ORDER_NUM', orderNum);


        fetch('/mobile_app/mgr/esim_bulk_order.php', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Server returned an error: ${response.status} - ${response.statusText}`);
                }
                return response.text();
            })
            .then(data => {
                try {
                    const jsonData = JSON.parse(data);
                    alert('API 요청 성공: ' + JSON.stringify(jsonData));
                } catch (error) {
                    alert('API 요청 성공: ' + data);
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