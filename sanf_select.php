<!-- 国内サッカーのタイトル一覧から、サンフレッチェ広島を含むタイトルを表示する。
そのタイトルの中で、読みたいスレッドを選択する。 -->
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sanfrecce</title>
</head>

<body>
    <?php
//     $top = "https://kizuna.5ch.net/test/read.cgi/soccer/"; //    左のurlにスレ番号,ファイル名を追加すると、スレッドが表示される。
//     $title = filter_input(INPUT_POST, "list");
//     if ($title !== null){        // ""をhtmlspecialcharsに使用することは、非推奨のため(deprecated)
//     $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
//     // var_dump ($title);
//     // echo "<br>";
//     }
//     if ($title !== null) {
//         $num = mb_substr($title, 17, 11);   //サンフレッチェ広島の文字がある行から、スレ番号のみ抽出
//         $start = mb_strstr($title, " (", true);   // (　より前の部分の文字列を抜き出す
//         // $name = mb_substr($start, mb_strrpos($start, ": ") + 1, mb_strlen($start));  // : より後の部分の文字列を抜き出し、ファイル名(拡張子なし)　↑↑↑サンフレッチェ広島Part2014↑↑↑
//         // $name = trim($name);    //スペース部分を消す
//         $name = "index";
//         $title = $top . $num . $name . ".html";   //スレッドのurl
//         $title = trim($title);
//         $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
//     // $title="https://weathernews.jp/";
//     echo '<a href="'.$title.'">リンク</a>';
//         echo "<br>";
// // $ch = curl_init();
// // curl_setopt($ch, CURLOPT_URL, $title);
// // curl_setopt($ch, CURLOPT_HEADER, false);
// // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
// // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
// // curl_setopt($ch, CURLOPT_TIMEOUT, 30);
// // $file = curl_exec($ch);

// // var_dump($file);
// // curl_close($ch);
//     }

    $top = "https://kizuna.5ch.net/test/read.cgi/soccer/"; //    左のurlにスレ番号,ファイル名を追加すると、スレッドが表示される。

    $file = fopen("https://kizuna.5ch.net/soccer/subback.html", "r");
    while (feof($file) === false) { //!feof($file)と同じ（falseの間続けますよ）の意味
        $line = trim(fgets($file));
        $line = mb_convert_encoding($line, "utf-8", "sjis"); // シフトJISからUTF-8に変換
        $line = htmlspecialchars($line, ENT_QUOTES, 'UTF-8');
        if (strstr($line, "サンフレッチェ広島")) {  //サンフレッチェ広島という文字がある場合、

        $num = mb_substr($line, 17, 11);   //サンフレッチェ広島の文字がある行から、スレ番号のみ抽出
// var_dump($top.$num);  echo "<br>";
            // echo '<form method ="post">';
            echo '<p><a href="'.$top.$num.'" style="width:900px; size=80; font-family:メイリオ; font-size: 20px;"> ' . $line . '</a></p>';

            // echo '<input type="text" name="list" style="width:900px; size=80; font-family:メイリオ; font-size: 20px;" 
            // value="' . $line . '" >';
            // echo '<button type="submit" style="width:100px; background:#CCF; border:solid gray 0.5px; padding:10px;" 
            // onMouseOut="this.style.background=\'#CCF\';" onMouseOver="this.style.background=\'#EEF\';">select</button>';
            // echo '</form>';
        }
    }
    fclose($file);


    ?>
</body>

</html>