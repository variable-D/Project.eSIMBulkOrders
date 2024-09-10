<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Manager Site</title>
    <link rel="stylesheet" type="text/css" href="./stylesheet.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
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
<form method="post" action="" style="">
    <ul class="esim_list_sch2_ul es-col7 mt">
        <li>
            <p class="esim_list_sch2-subj">eSIM Select</p>
            <input type="text" class="input_data"><br/>
        </li>
        <li class="esim_list_sch2_submit">
            <input type="submit" value="SELECT" class="sch2_submit-btn w100">
        </li>
    </ul>
</form>
<table border=0 cellpadding=1 cellspacing=1 class="list-tb">
    <caption>주문 조회</caption>
    <thead>
    <tr height="24" bgcolor="#FFFFFF" >
        <td width=100 >순번</td>
        <td width=200 >주문번호</td>
        <td width=140 >납품처</td>
        <td width=200 >API 요청시간</td>
        <td width=100 >상품옵션(일자)</td>
        <td width=200 >비고</td>
        <td width=120 >CTN</td>
        <td width=300 >QR Code Data</td>
    </tr>
    </thead>
    <tbody>
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
                    }
                })
                .catch(error => {
                    console.error('API 요청 실패:', error);
                    alert('API 요청 실패: ' + error.message);
                });
        }
    </script>
</body>
</html>