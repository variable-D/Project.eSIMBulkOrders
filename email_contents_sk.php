<?php
$email_subject = "[Korea eSIM Red QR code from Korea SIM] Here is your eSIM!";
$email_headers = "From: Korea SIM Card <cs@koreaesim.com>\r\n";
$email_headers .= "Content-type: text/html; charset=utf-8\r\n";

$email_contents = "<div dir=\"ltr\"><br></div><div dir=\"ltr\">Hello.</div><span style=\"color:rgb(80,0,80)\"><div></div><div><font color=\"#222222\"></font></div><div><font color=\"#222222\">Thank you for choosing Korea eSIM Red.</font></div><div><font color=\"#222222\"><br></font></div><div><font color=\"#222222\"><br></font></div></span>";
$email_contents .= "<div><font color=\"#222222\" size=\"4\"><b>Order Number : $order_item_code </b></font></div><div><font color=\"#222222\"><b><br></b></font></div>";
$email_contents .= "<div><font color=\"#222222\" size=\"4\"><b>eSIM QR code : $esimDays Days</b></font></div><div><font color=\"#222222\"><b><br></b></font></div>";
$email_contents .= "<div style=\"color:rgb(34,34,34)\"><font color=\"#222222\"><img border=\"0\" src=\"$varQRCodeImg\"><br></font></div><div><font color=\"#222222\"><br></font></div>";
$email_contents .= "<div><font color=\"##ff0000\">⚠DO NOT DELETE the eSIM's PROFILE AFTER SCAN the QR CODE. IT CAN NOT BE RESCANNED！⚠</font></div>";
$email_contents .= "<b>QR Code Address : LPA:1\$RSP-0010.OBERTHUR.NET\$$varQRCodePath </b><div><font color=\"#222222\"><br></font></div>";
$email_contents .= "<b>SM-DP+ Address : RSP-0010.OBERTHUR.NET </b><div><font color=\"#222222\"><b><br></b></font></div>";
$email_contents .= "<b>Activation Code : $varQRCodePath </b><div><font color=\"#222222\"><b><br></b></font></div>";
$email_contents .= "<b>Confirmation Code : No need </b><div><font color=\"#222222\"><b><br></b></font></div>";
// 전화번호 비표시
//$email_contents .= "<div><font color=\"#222222\" size=\"4\"><b>Phone Number : $varCtn </b></font></div><div><font color=\"#222222\"><b><br></b></font></div>";
$email_contents .= "<div><b><font size=\"4\">How to Use&nbsp;</font></b></div><br>
    <div><font color=\"#222222\">Please scan above QR code image with the phone that you are about to use with eSIM according to following instruction.&nbsp;</font></div><br><div><font color=\"#222222\">iOS : [Setting] - [Mobile Data] - [Add Data Plan]</font></div><span style=\"color:rgb(80,0,80)\"><div>
    <font color=\"#222222\">Android : [Setting] - [Network and Internet] - [Mobile Network] - [Download a SIM instead]</font></span></div><br>
    <div style=\"color:rgb(80,0,80)\"><span style=\"color:rgb(255,0,0)\"><b style=\"\">
    ※ When you are abroad, it is normal to be displayed \"activating…\" after you download the eSIM's profile. It will be activated automatically when you arrive in Korea, and the usage period will begin.</b></span></div><br>";
$email_contents .=
    "<div><font color=\"#222222\" size=\"4\"><b>Notification</b></font></div><br>
    <div style=\"color:rgb(80,0,80)\"><span style=\"color:rgb(255,0,0)\"><b style=\"\">&nbsp;·&nbsp;&nbsp;&nbsp; Please Do Not Remove Data Plan after Scanning the QR Code Unless You Have Used It Up.&nbsp;</b></span></div>
    <div><font color=\"#222222\">&nbsp;·&nbsp;&nbsp;&nbsp; If deleted, it can not be rescaned or reused.</font></div>
    <div><font color=\"#222222\">&nbsp;·&nbsp;&nbsp;&nbsp; If you have questions about the service contents, please contact us through Korea SIM Chat Service (krsim.channel.io)</font></div>
    <div><font color=\"#222222\">&nbsp;·&nbsp;&nbsp;&nbsp; If you encounter technical troubles, please contact SKT customer center Tel : 02-6343-9000.</font></div><br>
    <div><font color=\"#222222\">Have a wonderful day.</font></div><br><div><font color=\"#222222\">(This email is only for sending eSIM QR code. Don’t reply.)</font></div><br>
    <div><font color=\"#222222\">====================</font></div><br>
    <div><h3><b>Exclusive Coupon Book Event</b></h3></div>
    <div>~ 2024.12.31</div><br>
    <div><ul>
    <li><b>Get the Coupon Book</b>: 👉 <a href=\"https://krsim.net/\">Click here</a> (or visit <a href=\"http://krsim.net\">krsim.net</a>) and find the 🎁 icon at the bottom right. Then, enter ‘<a href=\"http://krsim.net/\">krsim.net</a>’ as a password.</li>
    </ul></div><br>
    <hr><br>
    <div><b>How to Collect</b></div><br>
    <div><ul>
    <li>For eSIM Red with email delivery, please check the email sent after your purchase.</li>
    <li>For any other airport pick-up products, a physical coupon will be provided on pick-up.</li>
    </ul></div><br>
    <div><b>How to Use</b></div><br>
    <div><ul>
    <li>Present the coupon at the entrance or during payment.</li>
    </ul></div><br>
    <hr><br>
    <div><font color=\"#222222\">Korea SIM (&nbsp;<a href=\"https://www.krsim.net/\" target=\"_blank\">https://www.krsim.net/</a>&nbsp;)&nbsp;located in Seoul,&nbsp;South Korea.</font></div><br><br>";

?>