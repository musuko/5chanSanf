<?php
//起動直後、削除ボタンを押した場合、または読了ボタンを押した場合、この行から下に進む。

// 国内サッカーのタイトル一覧から、サンフレッチェ広島を含むタイトルをリンクで表示する。
require "sanf_select.php";

//タイトル一覧の次に、thread番号と、削除ボタンや読了ボタンを押したことを出力する
$name = filter_input(INPUT_GET, "name");    //id
$name = isset($name) ? $name . "\n" : "";   //id表示後、改行する。起動直後、nameが存在しないので、""を定義しておく。
$num = filter_input(INPUT_GET, "num");  //threadの番号(前回の読了番号、今回の読了番号、削除番号)
$num = isset($num) ? $num : file_get_contents('last.txt');  //thread番号が存在しない場合、前回「読了」としたthread番号を読み込む
$button = filter_input(INPUT_GET, "button");    //削除ボタン、読了ボタンのいずれか

if (isset($num)) {
    if ($button === "del") {    //削除ボタンを押した場合
        echo $num . "を削除しました";
        file_put_contents("./del.txt", $name, FILE_APPEND); //非表示にしたいidを保存する
    } elseif ($button === "over") { //読了ボタンを押した場合
        file_put_contents("last.txt", $num);    //thread番号をlast.txtに保存する
        echo $num . 'まで読んだ';
        //echo '<br>';
        $name = "";
    } 
}
echo "<br> \n";

// htmlを読み込む
$file = fopen("./sanf.html", "r");
$i = 1;
$total_lines= 1452;    //$total_linesは、htmlの中で、threadが書き込まれている行。
while ($i <= $total_lines) { //!feof($file)と同じ（falseの間続けますよ）の意味
    // while (feof($file) === false) { //!feof($file)と同じ（falseの間続けますよ）の意味
    $line = trim(fgets($file));
    $line = mb_convert_encoding($line, "utf-8", "sjis"); // シフトJISからUTF-8に変換
    //thread行の不要部分を取り除く
    if ($i === $total_lines) {
        $line = mb_strstr($line, '});</script>', false);   // 指定文字より後の部分の文字列を抜き出す
        $line = mb_substr($line, 12);       //指定文字数を先頭から取り除く
        $line = mb_strstr($line, '<div class="navmenu">', true);   // 指定文字より前の部分の文字列を抜き出す
        //trueを指定した場合、指定した文字列より前の文字列を取得します。指定しない場合（false）、指定した文字列以降の文字列を取得します。
    }
    $i++;
}
// echo $i."piyo".($line)."pao";echo '<br>';
fclose($file);


//thread行の前後不要部分を取り除く
// $data = explode("name", htmlspecialchars($line, ENT_QUOTES, 'utf-8'));

$data = explode('</section></article>', $line);    //threadが書き込まれているテキストを、各threadの配列にする。$data[0]は0002スレの情報

// 配列に、thread, id, 表示可否情報を収める
$j = 2;
$num_jump = 1;  //ボタンを押した後のジャンプ先、初期値設定
$thread[1] = "";
$id[1] = "";
foreach ($data as $value) {
    // echo $j. '<br>'; echo $value. '<br>';

    $del_sw[$j] = 0;    // 0: NG IDではない 1:NG IDである
    if ($j < 1001) {
        $id[$j] = mb_strstr($value, '" data-id="', true);     // 指定文字前の部分の文字列を抜き出す
        $id[$j] = mb_strstr($id[$j], '"ID:', false);  // 指定文字後の部分の文字列を抜き出す
        $id[$j] = mb_substr($id[$j], 4);     //idを抽出する。これが最終id
        // echo htmlspecialchars($id[$j]); echo '<br>'; echo '<br>';

        $thread[$j] = mb_strstr($value, 'post-content">', false);  // 指定文字後の部分の文字列を抜き出す
        $thread[$j] = mb_substr($thread[$j], 15);    //指定文字数以降のthreadを抽出する
        // echo htmlspecialchars($thread[$j]); echo '<br>'; echo '<br>';
        // $len = mb_strlen($thread[$j]);
        // $thread[$j] = mb_substr($thread[$j], 0, $len - 1);      //最後の1文字を取り除く。これが最終thread
        
        // echo $j, $id[$j], $thread[$j]; echo '<br>';

//threadをNG idを除き表示する
        $ng_name = file("./del.txt");  //NG IDを読み込む
        foreach ($ng_name as $row) {   //このIDがNGかどうか確認する。まずNG IDを配列にする
            $row = trim($row);
            if ($id[$j] === $row) {     //NG IDと一致するか
                $del_sw[$j] = 1;        //NG IDと一致した場合、$del_swを1にし、表示不可とする。
            }
        }
        if ($del_sw[$j] !== 1 && $j <= $num) {    //表示可能かつ削除ボタンを押したthread番号以下の場合
            $num_jump = $j;   //ジャンプ先のthread番号
        }
        $jmax=$j;    //読み込みthread数
    }
    $j++;
}



//$numが1より大きい場合、ジャンプする先をリンクで表示する
if ($num > 1) {
    echo '<a href="#' . ($num_jump) . '">ID = #' . ($num_jump) . 'へジャンプ</a>';
    echo "<br> \n";
}


//NGと重複を除いたthreadを表示する
// $thread[0] = "";
for ($j=2; $j<=$jmax; ++$j){

            if ($del_sw[$j] !== 1) {     //NG IDと一致しない場合
                if ($thread[$j] !== $thread[$j-1]) {    //重複threadではない場合
                    // echo $j, $thread[$j];
                    $pos = strpos($thread[$j], "../test/read.cgi/soccer/"); //引用リンク(>6など)が、スレ内の何文字目に存在するかを$posに代入
                    if ($pos !== false) {   //引用リンクが存在する場合、
                        $pick = substr($thread[$j], $pos, 35);  //リンク先のフォルダを修正  $thread[$j]内の$pos文字目の35文字を$pickとする
                        $thread[$j] = str_replace($pick, "index.php#", $thread[$j]);   // $thread[$j]内の$pickをindex.php#に置き換える
                    }
//実際に表示するthread                
                // echo  $j;
                echo '<a id="' . $j . '">' . $j . '</a>';   //誤記に見えて意味がある。この番号のスレにジャンプするために使用。
                echo '<form action="index.php#" method="get">';     //ボタンを押したら、index.php#にジャンプする。
                echo '<input type="text" name="name" value="' . $id[$j] . '" style="border:none;">';    //スレッド記入者id
                echo '<input type="hidden" name="num" value="' . $j . '">';                             //スレッド番号
                echo '<button type="submit" name="button" value="del" style="background-color:white; border:solid gray 1px;border-radius:50%;">削除</button>';  //削除ボタン
                echo '<button type="submit" name="button" value="over" style="background-color:white; border:solid gray 1px;border-radius:50%;">読了</button>';    //読了ボタン
                echo '</form>';

                echo '<p style=" font-family:メイリオ; font-size: 20px; background: azure";>' . $thread[$j] . '</p>';
                echo "\n";
                }
            }
        
    

}