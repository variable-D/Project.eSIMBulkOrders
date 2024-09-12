<?php
include 'db_info.php'; // DB 연결 정보

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 데이터베이스 연결
    $conn = new mysqli($db_host, $db_user, $db_pwd, $db_category, $db_port);

    if ($conn->connect_error) {
        die("DB 연결 실패: " . $conn->connect_error);
    }

    // POST로 전송된 데이터 받기
    $note = trim($_POST['note']);
    $id = intval($_POST['id']);

    // SQL UPDATE 쿼리 실행
    $sql = "UPDATE t_esim_bulk_order_tb SET note = ? WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $note, $id);

    if ($stmt->execute()) {
        // 수정 성공 시 다시 목록 페이지로 리디렉션
        header("Location: {$_SERVER['HTTP_REFERER']}");
    } else {
        echo "수정 중 오류 발생: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
