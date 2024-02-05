<!-- 国内サッカーのタイトル一覧から、サンフレッチェ広島を含むタイトルをリンクで表示する。 -->

<?php
$top = "https://kizuna.5ch.net/test/read.cgi/soccer/"; //    左のurlにタイトル番号を追加すると、スレッドが表示される。

$file = fopen("https://kizuna.5ch.net/soccer/subback.html", "r");   //タイトル一覧表示
$i = 0;
while (feof($file) === false) { //!feof($file)と同じ（falseの間続けますよ）の意味
    $line[$i] = trim(fgets($file));
    $line[$i] = mb_convert_encoding($line[$i], "utf-8", "sjis"); // シフトJISからUTF-8に変換
    $line[$i] = htmlspecialchars($line[$i], ENT_QUOTES, 'UTF-8');   // タイトル番号を抽出するため、html文字を分解する
    if (strstr($line[$i], "サンフレッチェ広島")) {  //サンフレッチェ広島という文字がある場合、
        $title[$i] = mb_substr($line[$i], 41);       //指定文字数以降を抜き出す
        $title[$i] = mb_strstr($title[$i], '&lt;/a&gt;', true);   // 指定文字より前の部分の文字列を抜き出す

        $number[$i] = mb_substr($line[$i], 17, 10);   //サンフレッチェ広島の文字があるhtml文字列から、タイトル番号のみ抽出。特殊文字に注意 < と　"
        echo '<a href="index.php?idno=' . $i . '" style="width:900px; size=80; font-family:メイリオ; font-size: 20px;"> ' . $title[$i] . '</a>';
        echo "<br>";
        $i++;
    }
}
fclose($file);
// 以前のセッションを消去する処理？？


if (isset($_GET['idno'])) {   //GET['idno']がtrueの場合
    $idno = $_SESSION['idno'] = $_GET['idno'];   //$_GET['idno']を与える
    $_SESSION['number'] = $number[$idno];     //タイトル番号 
} elseif (!isset($_GET['idno']) && isset($_SESSION['idno'])) {  //削除や読了ボタンが押され、GET['idno']がfalseになった場合
    foreach ($number as $key => $value) {
        if (isset($_SESSION['number'])) {
            if ($_SESSION['number'] === $value) {
                $idno = $key;
            }
        }
    }
    // $idno = $_SESSION['idno'];
} else {   //起動直後
    $idno = $_SESSION['idno'] = 0;      //とりあえず、0番目のスレッドを指定する
    $_SESSION['number'] = $number[$idno];     //タイトル番号 配列
}


// リンク先のHTMLを保存するためのコード
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $top . $number[$idno]);   //html文読み込み
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$html = curl_exec($ch);          //html文
curl_close($ch);
// file_put_contents($number[$i] . '.html', $html[$i]);
?>