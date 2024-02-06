<!-- 国内サッカーのタイトル一覧から、サンフレッチェ広島を含むタイトルをリンクで表示する。 -->

<?php
$top = "https://kizuna.5ch.net/test/read.cgi/soccer/"; //    左のurlにタイトル番号を追加すると、スレッドが表示される。

$file = fopen("https://kizuna.5ch.net/soccer/subback.html", "r");   //タイトル一覧表示
$i = 1;
while (feof($file) === false) { //!feof($file)と同じ（falseの間続けますよ）の意味
    $line[$i] = trim(fgets($file));
    $line[$i] = mb_convert_encoding($line[$i], "utf-8", "sjis"); // シフトJISからUTF-8に変換
    $line[$i] = htmlspecialchars($line[$i], ENT_QUOTES, 'UTF-8');   // タイトル番号を抽出するため、html文字を分解する
    if (strstr($line[$i], "サンフレッチェ広島")) {  //サンフレッチェ広島という文字がある場合、
        $title[$i] = mb_substr($line[$i], 41);       //指定文字数以降を抜き出す
        $title[$i] = mb_strstr($title[$i], '&lt;/a&gt;', true);   // 指定文字より前の部分の文字列を抜き出す
        $title_number[$i] = mb_substr($line[$i], 17, 10);   //タイトル番号のみ、サンフレッチェ広島の文字があるhtml文字列から抽出。特殊文字に注意 < と　"

        echo '<br>';
        //タイトル番号
        echo '<a href="index.php?number=' . $title_number[$i] . '" style="width:900px; size=80; font-family:メイリオ; font-size: 20px;"> ' . $title[$i] . '</a>';
        echo "<br>";
        $i++;
    }
}
fclose($file);

// $_SESSION['number'][$i]:   タイトル番号
// $_SESSION['num']:   スレッド番号
// $num: スレッド番号
if (isset($_SESSION['number'])) {
    if (($_SESSION['number']) === 0) {
        $_SESSION['number'] = $title_number[1];
    }
}

// リンク先のHTMLを保存するためのコード
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $top . $_SESSION['number']);   //html文読み込み
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$html = curl_exec($ch);          //html文
curl_close($ch);
// file_put_contents($title_number[$i] . '.html', $html[$i]);
?>