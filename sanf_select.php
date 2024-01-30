<!-- 国内サッカーのタイトル一覧から、サンフレッチェ広島を含むタイトルをリンクで表示する。 -->

    <?php

    $top = "https://kizuna.5ch.net/test/read.cgi/soccer/"; //    左のurlにタイトル番号を追加すると、スレッドが表示される。

    $file = fopen("https://kizuna.5ch.net/soccer/subback.html", "r");
    $i = 0;
    while (feof($file) === false) { //!feof($file)と同じ（falseの間続けますよ）の意味
        $line[$i] = trim(fgets($file));
        $line[$i] = mb_convert_encoding($line[$i], "utf-8", "sjis"); // シフトJISからUTF-8に変換
        if (strstr($line[$i], "サンフレッチェ広島")) {  //サンフレッチェ広島という文字がある場合、
            $title[$i] = mb_substr($line[$i], 25);       //指定文字数を先頭から取り除く
            $title[$i] = mb_strstr($title[$i], '</a>', true);   // 指定文字より前の部分の文字列を抜き出す
            // echo ($title[$i]);  echo "<br>";
            $line[$i] = htmlspecialchars($line[$i], ENT_QUOTES, 'UTF-8');   // タイトル番号を抽出するため、html文字を分解する
            // echo ($line[$i]);  echo "<br>";
            $num[$i] = mb_substr($line[$i], 17, 10);   //サンフレッチェ広島の文字があるhtml文字列から、タイトル番号のみ抽出。特殊文字に注意 < と　"
            // echo ($top.'---'.$num[$i]);  echo "<br>";

            // リンク先のHTMLを保存するためのコード
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $top . $num[$i]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $html[$i] = curl_exec($ch);
            curl_close($ch);
            // file_put_contents($num[$i] . '.html', $html[$i]);
            echo '<a href="index.php?id=' . $i . '" style="width:900px; size=80; font-family:メイリオ; font-size: 20px;"> ' . $title[$i] . '</a>';
            echo "<br>";
            $i++;
        }
    }
    fclose($file);
    ?>