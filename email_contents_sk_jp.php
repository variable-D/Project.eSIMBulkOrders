<?php
$email_subject = "[Korea SIM Card]eSIM RedのQRコードが届きました。";
$email_headers = "From: Korea SIM Card <cs@koreaesim.com>\r\n";
$email_headers .= "Content-type: text/html; charset=utf-8\r\n";

$email_contents = "<div dir=\"ltr\"><br></div><div dir=\"ltr\">こんにちは。</div><span style=\"color:rgb(80,0,80)\"><div></div><div><font color=\"#222222\"></font></div><div><font color=\"#222222\">
Korea eSIM Redをご利用いただき、誠にありがとうございます。</font></div><div><font color=\"#222222\"><br></font></div><div><font color=\"#222222\"><br></font></div></span>";
$email_contents .= "<div><font color=\"#222222\" size=\"4\"><b>注文番号 : $order_item_code </b></font></div><div><font color=\"#222222\"><b><br></b></font></div>";
$email_contents .= "<div><font color=\"#222222\" size=\"4\"><b>eSIMのQRコード : $esimDays Days</b></font></div><div><font color=\"#222222\"><b><br></b></font></div>";
$email_contents .= "<div style=\"color:rgb(34,34,34)\"><font color=\"#222222\"><img border=\"0\" src=\"$varQRCodeImg\"><br></font></div><div><font color=\"#222222\"><br></font></div>";
$email_contents .= "<div><font color=\"#ff0000\">⚠QRコードをスキャンした後、eSIMプロファイルを削除しないでください！一度スキャンしたQRコードは無効になります！⚠</font></div>";
$email_contents .= "<b>QRコードアドレス : LPA:1\$RSP-0010.OBERTHUR.NET\$$varQRCodePath </b><div><font color=\"#222222\"><br></font></div>";
$email_contents .= "<b>SM-DP+アドレス : RSP-0010.OBERTHUR.NET </b><div><font color=\"#222222\"><b><br></b></font></div>";
$email_contents .= "<b>アクティベーションコード : $varQRCodePath </b><div><font color=\"#222222\"><b><br></b></font></div>";
$email_contents .= "<b>確認コード : 入力不要 </b><div><font color=\"#222222\"><b><br></b></font></div>";
// 電話番号は表示しません
//$email_contents .= "<div><font color=\"#222222\" size=\"4\"><b>eSIMの電話番号 : $varCtn </b></font></div><div><font color=\"#222222\"><b><br></b></font></div>";
$email_contents .= "<div><b><font size=\"4\">使用方法&nbsp;</font></b></div><br>
    <div><font color=\"#222222\">QRコードを下記の説明に従い、スキャンしてください。&nbsp;</font></div><br><div><font color=\"#222222\">iOS: [設定] - [モバイル通信] - [モバイル通信プランを追加]</font></div><span style=\"color:rgb(80,0,80)\"><div>
    <font color=\"#222222\">Android: [設定] - [ネットワークとインターネット] - [モバイルネットワーク] - [SIMをダウンロード]</font></span></div><br>
    <div style=\"color:rgb(80,0,80)\"><span style=\"color:rgb(255,0,0)\"><b style=\"\">
    ※ 海外にいらっしゃるうちは「アクティベート中…」と表示され、SIMが利用できないのが正常でございます。韓国に到着されると自動でアクティベートされ、利用開始となります。</b></span></div><br>";
$email_contents .=
	"<div><font color=\"#222222\" size=\"4\"><b>注意事項</b></font></div><br>
    <div style=\"color:rgb(80,0,80)\"><span style=\"color:rgb(255,0,0)\"><b style=\"\">&nbsp;·&nbsp;&nbsp;&nbsp; QRコードをスキャンし、eSIMプロファイルを追加したら、eSIMプロファイルを削除してはなりません。&nbsp;</b></span></div>
    <div><font color=\"#222222\">&nbsp;·&nbsp;&nbsp;&nbsp; eSIMプロファイルは削除したら再度スキャンや使用することができません。</font></div>
    <div><font color=\"#222222\">&nbsp;·&nbsp;&nbsp;&nbsp; サービスに関するお問い合わせはチャットサービス(krsim.channel.io)までお願いします。</font></div>
    <div><font color=\"#222222\">&nbsp;·&nbsp;&nbsp;&nbsp; 技術的トラブルが発生した場合はSKTカスタマーセンターまでご連絡ください。☎02-6343-9000</font></div><br>
    <div><font color=\"#222222\">韓国での旅がより快適で素敵になりますように。</font></div><br><div><font color=\"#222222\">(このメールは発信専用なので、返信には対応いたしかねます。)</font></div><br>
    <div><img src=\"https://www.koreaesim.com/mobile_app/img/esim_red_event.jpg\"></div><br>
    <div><font color=\"#222222\">====================</font></div><br>
    <div><h3><b>Exclusive Coupon Book Event</b></h3></div><br>
    <div>~ 2024.12.31</div><br>
    <div><ul>
    <li><b>クーポンブックを取得方法</b>: 👉 <a href=\"https://esimkorea.com\">こちらをクリック</a>（または <a href=\"http://esimkorea.com\">esimkorea.com</a>にアクセス）、右下の🎁アイコンをクリックして、「esimkorea.com」をパスワード欄に入力してください。</li>
    </ul></div><br>
    <hr><br>
    <div><b>受け取り方法</b></div><br>
    <div><ul>
    <li>eSIM Red（メール配信)の場合、購入後に送信されたメールをご確認ください。</li>
    <li>その他の空港受け取り商品の場合、受け取り時に紙のクーポンが提供されます。</li>
    </ul></div><br>
    <div><b>使用方法</b></div><br>
    <div><ul>
    <li>入場時または支払い時にクーポンを提示してください。</li>
    </ul></div><br>
    <hr><br>
    <div><font color=\"#222222\">Korea SIM (&nbsp;<a href=\"https://www.krsim.net/\" target=\"_blank\">https://www.krsim.net/</a>&nbsp;)&nbsp;ソウルに位置する、韓国の企業です。</font></div><br><br>";
?>