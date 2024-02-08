<?php
session_start();

if (isset($_GET['button'])) {    //削除ボタン、読了ボタンが押された場合
    $button = $_GET['button'];
    if ($_GET['button'] === 'del') {
        $del_button = true;   //削除ボタンが押された場合
        $over_button = false;
    } else if ($_GET['button'] === 'over') {    //読了ボタンが押された場合
        $del_button = false;
        $over_button = true;
    }
} else {                //削除ボタン、読了ボタンが押されていない場合
    $button = "";
    $del_button = false;
    $over_button = false;
}

//タイトル一覧の次に、thread番号と、削除ボタンや読了ボタンを押したことを出力する
$name = filter_input(INPUT_GET, "name");    //投稿者のname
//起動直後
$name = isset($name) ? $name . "\n" : "";

$ipadress = filter_input(INPUT_GET, "ip");    //投稿者のip
$ipadress = isset($ipadress) ? $ipadress . "\n" : NULL;   //ip表示後、改行する。起動直後、ipが存在しないので、""を定義しておく。


//タイトル番号
$number = filter_input(INPUT_GET, "number");    //タイトル番号
if (isset($number)) {
    $_SESSION["number"] = $number;
} elseif (isset($_SESSION["number"])) {
    $number = $_SESSION["number"];
}
//threadの番号
$num = filter_input(INPUT_GET, "num");      //スレッド番号

// 国内サッカーのタイトル一覧から、サンフレッチェ広島を含むタイトルをリンクで表示する。
// $_GET['number'], $_SESSION['number], $htmlが返ってくる
require "sanf_select.php";


//0: last.txtにデータを新設, 1:データを追加, 2:データを入れ替え, -1:何もしない
$add = -1;  // -1:何もしない　初期値
$equal_sw = 0;  // 初期値
$last_nullcheck = (file_get_contents("./last.txt")) ? 0 : 1;
if (!$last_nullcheck) {         //last.txtにデータがある場合
    $num_row_array = file('./last.txt', FILE_IGNORE_NEW_LINES);

    foreach ($num_row_array as $num_row) {
        $num_column_array = explode(',', $num_row);
        if ($num_column_array[0] === $_SESSION["number"]) {
            $equal_sw = 1;      //データを追加
            if (!isset($num)) {     //$numが未設定の場合
                $num = $num_column_array[1];
                $add = -1;      //-1:何もしない
            } else {
                $add = 2;   //書き換え
            }
        }
    }

    if ($equal_sw === 0) {
        if (!isset($num)) {     //$numが未設定の場合
            $num = 2;   //先頭行
        }
        $add = 1;   //追加
    }
} else {  //last.txtにデータがない場合
    $num = 2;
    $add = 0;   //新設
}


if ($del_button) {    //削除ボタンを押した場合
    echo $num . "を削除しました";
    file_put_contents("./del.txt", $name, FILE_APPEND); //非表示にしたいnameを保存する
    isset($ipadress)? file_put_contents("./del.txt", $ipadress, FILE_APPEND): ""; //非表示にしたいipを保存する
} elseif ($over_button) { //読了ボタンを押した場合
    echo '<p>'.$num . 'まで読んだ</p>';
}
// last.txtに書き込む
$last = $number . "," . $num . "\n";
$filename = './last.txt';

if ($add === 0) {   //0: last.txtにデータを新設, 1:データを追加, 2:データを入れ替え, -1:何もしない
    file_put_contents($filename, $last);    //thread番号をlast.txtに保存する
} elseif ($add === 1) {
    file_put_contents($filename, $last, FILE_APPEND);    //thread番号をlast.txtに保存する
} elseif ($add === 2) {
    $num_row_array = file('./last.txt', FILE_IGNORE_NEW_LINES);
    file_put_contents($filename, "");   //空にする
    foreach ($num_row_array as $num_row) {
        $num_column_array = explode(',', $num_row);
        if ($num_column_array[0] === $_SESSION['number']) {
            $num_column_array[1] = $num;
        }
        file_put_contents($filename, $num_column_array[0] . "," . $num_column_array[1] . "\n", FILE_APPEND);
    }
}



$_SESSION['txt'] = $html;          //html文 text
// htmlを読み込む 
$html_line = explode("\n", $_SESSION['txt']);
$thread_line_num = 1384;    //$thread_line_numは、htmlの中で、threadが書き込まれている行。
$html_line[$thread_line_num - 1] = mb_convert_encoding($html_line[$thread_line_num - 1], "utf-8", "sjis"); // シフトJISからUTF-8に変換
// echo $html_line[$thread_line_num - 1];
//thread行の不要部分を取り除く
$thread_line = mb_strstr($html_line[$thread_line_num - 1], '});</script>', false);   // 指定文字より後の部分の文字列を抜き出す
$thread_line = mb_substr($thread_line, 12);       //指定文字数を先頭から取り除く
$thread_line = mb_strstr($thread_line, '<div class="navmenu">', true);   // 指定文字より前の部分の文字列を抜き出す
$thread_line = trim($thread_line);
//trueを指定した場合、指定した文字列より前の文字列を取得します。指定しない場合（false）、指定した文字列以降の文字列を取得します。





//thread行の前後不要部分を取り除く
$data = explode('</section></article>', $thread_line);    //threadが書き込まれているテキストを、各threadの配列にする。$data[0]は0002スレの情報

// 配列に、thread, name, 表示可否情報を収める
$j = 2;
$num_jump = 2;  //ボタンを押した後のジャンプ先、初期値設定
$thread[1] = "";
$nameid[1] = "";
$ip[1] = "";
foreach ($data as $value) { //$data[2] thread nameが2。$dataのexplodeで一行多くなる。
    // $value = htmlentities($value);
    $del_sw[$j] = 0;    // 0: NG nameではない 1:NG name, NG IPである。または重複。
    if ($j < 1002) {
        $nameid[$j] = mb_strstr($value, 'data-userid="ID:', false);     // 指定文字前の部分の文字列を抜き出す
        $nameid[$j] = mb_strstr($nameid[$j], '" data-id', true);  // 指定文字後の部分の文字列を抜き出す
        $nameid[$j] = mb_substr($nameid[$j], 16);     //16文字以降を抽出する。これが最終name

        $ip[$j] = mb_strstr($value, ']', true);     // 指定文字前の部分の文字列を抜き出す
        $ip[$j] = mb_strstr($ip[$j], '[', false);  // 指定文字後の部分の文字列を抜き出す
        $ip[$j] = mb_substr($ip[$j], 1);     //1文字以降を抽出する。これが最終ip

        $datetime[$j] = mb_strstr($value, 'class="date">', false);  // 指定文字後の部分の文字列を抜き出す
        $datetime[$j] = mb_strstr($datetime[$j], '</span>', true);     // 指定文字前の部分の文字列を抜き出す
        $datetime[$j] = mb_substr($datetime[$j], 13);         //13文字以降を抽出する
        $datetime[$j] = mb_substr($datetime[$j], 0, 19);         //19文字までを抽出する

        $thread[$j] = mb_strstr($value, 'post-content">', false);  // 指定文字後の部分の文字列を抜き出す
        $thread[$j] = mb_substr($thread[$j], 15);    //指定文字数以降のthreadを抽出する

        //threadをNG name,重複threadを除き表示する
        $ng_name = file("./del.txt");  //NG name, NG IPを読み込む
        foreach ($ng_name as $row) {   //このname,IPがNGかどうか確認する。まずNG name, NG IPを配列にする
            $row = trim($row);
            if ($nameid[$j] === $row || $ip[$j] === $row) {     //NG name, NG IPと一致するか
                $del_sw[$j] = 1;        //NG name, NG IPと一致した場合、$del_swを1にし、表示不可とする。
            }
        }
        if ($thread[$j] === $thread[$j - 1]) {     //重複threadの場合
            $del_sw[$j] = 1;        //$del_swを1にし、表示不可とする。
        }
        if (mb_strpos($thread[$j], "たろわ") !== false) {     //"たろわ"を含む場合。含んでいても0を返すことがあるので、!==とする。
            $del_sw[$j] = 1;        //$del_swを1にし、表示不可とする。
        }
        if ($del_sw[$j] !== 1 && $j <= $num) {    //表示可能かつ削除ボタンを押したthread番号以下の場合
            $num_jump = $j;   //ジャンプ先のthread番号
        }
        $jmax = $j - 1;    //読み込みthread数。
    }
    $j++;
}



//ジャンプする先をリンクで表示する。ただし、$numが1より大きい場合。
if ($num > 1) {
    echo '<a class="top" href="#' . ($num_jump) . '">ID = #' . ($num_jump) . 'へジャンプ</a>';
    echo "<br> \n";
}
//削除ボタンを押した場合、直前の表示スレに自動でジャンプする。
if ($del_button) {
    header("location: index.php#" . $num_jump);
}

echo '<style> .top {font-family:メイリオ; position: relative; left: 10%;} </style>';
echo '<style> .thread {font-family:メイリオ; font-size: 18px; background: azure; position: relative; left: 15%; width: 800px;} </style>';

//NGと重複を除いたthreadを表示する
for ($j = 2; $j <= $jmax; ++$j) {

    if ($del_sw[$j] !== 1) {     //NG nameと一致せず、重複スレではない場合
        $pos = strpos($thread[$j], "../test/read.cgi/soccer/"); //引用リンク(>6など)が、スレ内の何文字目に存在するかを$posに代入
        if ($pos !== false) {   //引用リンクが存在する場合、
            $pick = substr($thread[$j], $pos, 35);  //リンク先のフォルダを修正  $thread[$j]内の$pos文字目の35文字を$pickとする
            $thread[$j] = str_replace($pick, "index.php#", $thread[$j]);   // $thread[$j]内の$pickをindex.php#に置き換える
        }



        //実際に表示するthread                
        // echo  $j;
        echo '<a class="top" id="' . $j . '">' . $j . '</a>';   //誤記に見えて意味がある。この番号のスレにジャンプするために使用。
        echo '<form class="top" action="index.php#" method="get">';     //ボタンを押したら、index.php#にジャンプする。
        echo '<input type="text" name="name" value="' . $nameid[$j] . '" style="border:none;">';    //スレッド記入者name
        echo '<input type="text" name="ip" value="' . $ip[$j] . '" style="border:none;">';    //スレッド記入者ip
        echo '<input type="text" name="datetime" value="' . $datetime[$j] . '" style="border:none;">';    //日時
        echo '<input type="hidden" name="num" value="' . $j . '">';                             //スレッド番号
        echo '<button type="submit" name="button" value="del" style="background-color:white; border:solid gray 1px;border-radius:50%;">削除</button>';  //削除ボタン
        echo '<button type="submit" name="button" value="over" style="background-color:white; border:solid gray 1px;border-radius:50%;">読了</button>';    //読了ボタン
        echo '</form>';
        echo '<p class="thread">' . $thread[$j] . '</p>';
        echo "\n";
    }
}
