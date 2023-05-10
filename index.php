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
$total_lines= 10;    //$total_linesは、htmlの中で、threadが書き込まれている行。
while ($i <= $total_lines) { //!feof($file)と同じ（falseの間続けますよ）の意味
    // while (feof($file) === false) { //!feof($file)と同じ（falseの間続けますよ）の意味
    $line = trim(fgets($file));
    //var_dump($line);
//thread行の不要部分を取り除く
    if ($i === $total_lines) {
        $line = mb_convert_encoding($line, "utf-8", "sjis"); // シフトJISからUTF-8に変換
        // こちらはHTMLのまま
        $line = mb_strstr($line, '★ULA版★', false);   // 後の部分の文字列を抜き出す
        // var_dump($line);
        $line = mb_strstr($line, '/ul>', false);   // 後の部分の文字列を抜き出す
        $line = mb_strstr($line, 'lass="thread">', false);   // 後の部分の文字列を抜き出す
        $line = mb_substr($line, 14);
        $line = mb_strstr($line, '前100</a>', true);   // 前の部分の文字列を抜き出す
        // var_dump (htmlspecialchars($line, ENT_QUOTES, 'utf-8'));
        //trueを指定した場合、指定した文字列より前の文字列を取得します。指定しない場合（false）、指定した文字列以降の文字列を取得します。
    }
    $i++;
}
fclose($file);


//thread行の前後不要部分を取り除く
// $data = explode("name", htmlspecialchars($line, ENT_QUOTES, 'utf-8'));
$data = explode('class="name">', $line);    //threadが書き込まれているテキストを、各threadの配列にする。

// 配列に、thread, id, 表示可否情報を収める
$j = 0;
$num_jump = 1;  //ボタンを押した後のジャンプ先、初期値設定
$thread[0] = "";
$id[0] = "";
foreach ($data as $value) {
    // echo $j; echo '<br>';
    $del_sw[$j] = 0;    // 0: NG IDではない 1:NG IDである
    if ($j > 0 && $j < 1001) {

        // var_dump($value); echo '<br>'; echo '<br>';
        // echo '<br>';
        $id[$j] = mb_strstr($value, "uid", false);  // 後の部分の文字列を抜き出す
        $id[$j] = mb_strstr($id[$j], "span", true);     // 前の部分の文字列を抜き出す
        $id[$j] = mb_substr($id[$j], 8, 9);     //idを抽出する。これが最終id

        $thread[$j] = mb_strstr($value, "escaped", false);  // 後の部分の文字列を抜き出す
        $thread[$j] = mb_substr($thread[$j], 9);    //9文字以降のthreadを抽出する
        $thread[$j] = mb_strstr($thread[$j], "/span", true);    // 前の部分の文字列を抜き出す
        $len = mb_strlen($thread[$j]);
        $thread[$j] = mb_substr($thread[$j], 0, $len - 1);      //最後の1文字を取り除く。これが最終thread

//threadをNG idを除き表示する
        $ng_name = file("./del.txt");  //NG IDを読み込む
        foreach ($ng_name as $row) {   //このIDがNGかどうか確認する。まずNG IDを配列にする
            $row = trim($row);
            if ($id[$j] === $row) {     //NG IDと一致するか
                $del_sw[$j] = 1;        //NG IDと一致した場合、$del_swを1にし、表示不可とする。
            }
        }
        if ($del_sw[$j] !== 1 && $j <= $num) {    //表示可能かつボタンを押したthread番号以下の場合
            $num_jump = $j;   //ジャンプ先のthread番号
        }
    }
        $jmax=$j;    //読み込みthread数
    $j++;
}



//$numが1より大きい場合、ジャンプする先をリンクで表示する
if ($num > 1) {
    echo '<a href="#' . ($num_jump) . '">ID = #' . ($num_jump) . 'へジャンプ</a>';
    echo "<br> \n";
}


//NGと重複を除いたthreadを表示する
$thread[0] = "";
for ($j=1; $j<=$jmax; $j++){

            if ($del_sw[$j] !== 1) {     //NG IDと一致しない場合
                if ($thread[$j] !== $thread[$j-1]) {    //重複threadではない場合
                    $pos = strpos($thread[$j], "../test/read.cgi/soccer/"); //引用リンク(>6など)が、スレ内の何文字目に存在するかを$posに代入
                    if ($pos !== false) {   //引用リンクが存在する場合、
                        $pick = substr($thread[$j], $pos, 35);  //リンク先のフォルダを修正  $thread[$j]内の$pos文字目の35文字を$pickとする
                        $thread[$j] = str_replace($pick, "index.php#", $thread[$j]);   // $thread[$j]内の$pickをindex.php#に置き換える
                    }
//実際に表示するthread                
                echo '<a id="' . $j . '">' . $j . '</a>';
                echo '<form action="index.php#" method="get">';
                echo '<input type="text" name="name" value="' . $id[$j] . '" style="border:none;">';
                echo '<input type="hidden" name="num" value="' . $j . '">';
                echo '<button type="submit" name="button" value="del" style="background-color:white; border:solid gray 1px;border-radius:50%;">削除</button>';
                echo '<button type="submit" name="button" value="over" style="background-color:white; border:solid gray 1px;border-radius:50%;">読了</button>';
                echo '</form>';

                echo '<p style=" font-family:メイリオ; font-size: 20px; background: azure";>' . $thread[$j] . '</p>';
                echo "\n";
                }
            }
        
    

}
// require "sanf_select.php";