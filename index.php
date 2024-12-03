<?php
session_start();
$top = "https://ikura.2ch.sc/test/read.cgi/soccer/"; //    左のurlにタイトル番号を追加すると、スレッドが表示される。
$title_number = "1733041337";
$url = $top . $title_number;

$thread_line_num = 24;    //$thread_line_numは、htmlの中で、threadが書き込まれている行。
echo "<p><a href='https://itest.5ch.net/subback/soccer'>5.国内サッカー板</a></p>";
echo "<p><a href='https://ikura.2ch.sc/soccer/subback.html'>2.国内サッカー板</a></p>";
echo "<p><a href='https://hayabusa5.2ch.sc/livefoot/subback.html'>実況サッカーch</a></p>";

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
$num = filter_input(INPUT_GET, "num");      //ボタンを押したスレッド番号

// 国内サッカーのタイトル一覧から、サンフレッチェ広島を含むタイトルをリンクで表示する。
// $_GET['number'], $_SESSION['number], $htmlが返ってくる
$html = file_get_contents($url);

if ($html === false) {
    echo "Failed to retrieve content from the URL.";
}


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
            $num = 1;   //先頭行
        }
        $add = 1;   //追加
    }
} else {  //last.txtにデータがない場合
    $num = 1;
    $add = 0;   //新設
}


if ($del_button) {    //削除ボタンを押した場合
    echo '<p>' . $num . 'を削除しました</p>';
    file_put_contents("./del.txt", $name, FILE_APPEND); //非表示にしたいnameを保存する
    // isset($ipadress) ? file_put_contents("./del.txt", $ipadress, FILE_APPEND) : ""; //非表示にしたいipを保存する
} elseif ($over_button) { //読了ボタンを押した場合
    echo '<p>' . $num . 'まで読んだ</p>';
}

// last.txtに読了番号を書き込む
$last = $number . "," . $num . "\n";    //タイトル番号,ボタンを押したスレッド番号
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
            $num_column_array[1] = $num;    //タイトル番号と一致する場合、スレッド番号を変更する
        }
        file_put_contents($filename, $num_column_array[0] . "," . $num_column_array[1] . "\n", FILE_APPEND);    //空にしたファイルに書き直す
    }
}


$html = mb_convert_encoding($html, "utf-8", "sjis"); // シフトJISからUTF-8に変換
$html = mb_strstr($html,  '<dl class="thread" style="word-break:break-all;">', false);   // 指定文字より後の部分の文字列を抜き出す
$html = mb_strstr($html, '</dl>', true);
$html = trim($html);
// echo $html;
$_SESSION['txt'] = $html;          //html文 text



//thread行の前後不要部分を取り除く
$data = explode('<br><br>', $html);    //threadが書き込まれているテキストを、各threadの配列にする。
$jmax = count($data) - 1;
// var_dump($data);

// 配列に、thread, name, 表示可否情報を収める
$j = 1;
$num_jump = 1;  //ボタンを押した後のジャンプ先、初期値設定

foreach ($data as $value) { //$data[2] thread nameが2。$dataのexplodeで一行多くなる。
    // $value = htmlentities($value);
    $del_sw[$j] = 0;    // 0: NG nameではない 1:NG nameである。または重複。
    if ($j < 1002) {
        $nameid[$j] = mb_strstr($value, 'ID:', false);     // 指定文字より後の部分の文字列を抜き出す
        $nameid[$j] = mb_strstr($nameid[$j], '.net', true);  // 指定文字より前の部分の文字列を抜き出す
        $nameid[$j] = mb_substr($nameid[$j], 3);     //16文字以降を抽出する。これが最終name
        // echo $nameid[$j]; echo "<br>";


        $datetime[$j] = mb_strstr($value, '</b></a>：', false);  // 指定文字後の部分の文字列を抜き出す
        $datetime[$j] = mb_strstr($datetime[$j], ' ID:', true);     // 指定文字前の部分の文字列を抜き出す
        $datetime[$j] = mb_substr($datetime[$j], 9);         //9文字以降を抽出する
        $datetime[$j] = mb_substr($datetime[$j], 0, 19);         //19文字までを抽出する 秒、ミリ秒を削除
        // echo $datetime[$j]; echo "<br>";

        $thread[$j] = mb_strstr($value, '<dd>', false);  // 指定文字後の部分の文字列を抜き出す
        $thread[$j] = mb_substr($thread[$j], 5);    //指定文字数以降のthreadを抽出する
        // echo $thread[$j]; echo "<br>";

        //threadをNGワード,NG name,重複threadを除き表示する
        $ng_name = file("./del.txt");  //NG nameを読み込む
        foreach ($ng_name as $row) {   //このnameがNGかどうか確認する。まずNG nameを配列にする
            $row = trim($row);
            if ($nameid[$j] === $row) {     //NG nameと一致するか
                $del_sw[$j] = 1;        //NG nameと一致した場合、$del_swを1にし、表示不可とする。
            }
        }
        if (mb_strpos($thread[$j], "たろわ") !== false) {     //"たろわ"を含む場合。===trueでは、含んでいても0を返すことがあるので、!==とする。
            $del_sw[$j] = 1;        //$del_swを1にし、表示不可とする。
        }
        if (mb_strpos($thread[$j], "ばかばっかり") !== false) {     //"ばかばっかり"を含む場合。===trueでは、含んでいても0を返すことがあるので、!==とする。
            $del_sw[$j] = 1;        //$del_swを1にし、表示不可とする。
        }
        if (mb_strpos($thread[$j], "赤メット") !== false) {     //"赤メット"を含む場合。===trueでは、含んでいても0を返すことがあるので、!==とする。
            $del_sw[$j] = 1;        //$del_swを1にし、表示不可とする。
        }
        if (mb_strpos($thread[$j], "ばあか") !== false) {     //"ばあか"を含む場合。===trueでは、含んでいても0を返すことがあるので、!==とする。
            $del_sw[$j] = 1;        //$del_swを1にし、表示不可とする。
        }
        if (mb_strpos($thread[$j], "ここですか？") !== false) {     //"ばあか"を含む場合。===trueでは、含んでいても0を返すことがあるので、!==とする。
            $del_sw[$j] = 1;        //$del_swを1にし、表示不可とする。
        }

        if ($j >= 2) {
            if ($thread[$j] === $thread[$j - 1]) {     //重複threadの場合
                $del_sw[$j] = 1;        //$del_swを1にし、表示不可とする。
            }
        }
        if ($del_sw[$j] !== 1 && $j <= $num) {    //表示可能かつ削除ボタンを押したthread番号以下の場合
            $num_jump = $j;   //削除ボタンを押したときの、ジャンプ先のthread番号を予め準備しておく
        }
        // $jmax = $j;    //読み込みthread数。
        $j++;
    }
    // echo $jmax; echo "<br>";
}



//ジャンプする先をリンクで表示する。ただし、$numが0より大きい場合。
if ($num > 0) {
    echo '<a class="top" href="#' . ($num_jump) . '">ID = #' . ($num_jump) . 'へジャンプ</a>';
    echo "<br> \n";
}
//削除ボタンを押した場合、直前の表示スレに自動でジャンプする。
if ($del_button) {
    header("location: index.php#" . $num_jump); //Warning: Cannot modify header information - headers already sent by 
}

echo '<style> .top {font-family:メイリオ; margin:0 0 0 5%; display: inline-block; border:none;} </style>';
echo '<style> .thread {font-family:メイリオ; font-size: 18px; background: azure; margin:0 0 1% 15%; width: 800px; border:none;} </style>';

//NGと重複を除いたthreadを表示する
for ($j = 1; $j <= $jmax; $j++) {

    // if ($del_sw[$j] !== 1) {     //NG nameと一致せず、重複スレではない場合
    $pos = strpos($thread[$j], "../test/read.cgi/soccer/"); //スレ$thread[$j]内で、引用リンク"http://localhost:3000/test/read.cgi/soccer/"(>>6など)が始まる文字数を、$posに代入
    if ($pos !== false) {   //引用リンクが存在する場合、
        $pick = substr($thread[$j], $pos, 35);  //  $thread[$j]内の$pos文字目から35文字を$pickに代入する。リンク先のフォルダを修正
        if ($j > 0) {
            $thread[$j] = str_replace($pick, "index.php#", $thread[$j]);   // $thread[$j]内の$pickを"index.php#"に置き換える
        }
        // echo $thread[$j]; echo "<br>";
    }
    if ($del_sw[$j] === 1) {
        $thread[$j] = "非表示";
    }


    //実際に表示するthread                
    echo '<a id="' . $j . '" style="font-family:メイリオ; margin: 0 0 0 5%;">' . $j . '</a>';   //誤記に見えて意味がある。この番号のスレにジャンプするために使用。
    echo '<form class="top" action="index.php#" method="get">';     //ボタンを押したら、index.php#にジャンプする。
    echo '<input type="text" name="name" value="' . $nameid[$j] . '" style="border:none; margin: 0;">';    //スレッド記入者name
    echo '<input type="text" name="datetime" value="' . $datetime[$j] . '" style="border:none; margin: 0;">';    //日時
    echo '<input type="hidden" name="num" value="' . $j . '">';                             //スレッド番号
    echo '<button type="submit" name="button" value="del" style="background-color:white; border:solid gray 1px;border-radius:50%; margin: 0;">削除</button>';  //削除ボタン
    echo '<button type="submit" name="button" value="over" style="background-color:white; border:solid gray 1px;border-radius:50%; margin: 0;">読了</button>';    //読了ボタン
    echo '</form>';
    echo '<div class="thread">' . $thread[$j] . '</div>';
    // echo "\n";
    // }
}
