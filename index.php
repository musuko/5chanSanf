<?php
require "sanf_select.php";
// 読み込んだスレッドの一覧を表示する。
// 不快なスレッドのIDを非表示にする。
$name = filter_input(INPUT_GET, "name");
$num = filter_input(INPUT_GET, "num");
$num = isset($num) ? $num : file_get_contents('last.txt');
$button = filter_input(INPUT_GET, "button");
$name = isset($name) ? $name . "\n" : "";

if (isset($num)) {
    if ($button === "del") {
        echo $num . "を削除しました";
        $num -= 1;
    } elseif ($button === "over") {
        file_put_contents("last.txt", $num);
        echo $num . 'まで読んだ';
        echo '<br>';
        $name = "";
    }
}
echo "<br> \n";
//削除した行、読了の行($num)のひとつ前にジャンプ。削除した行にジャンプできないのでひとつ前にする。
//$numが1より大きい場合。先頭行に表示。
if ($num > 1) {
    echo '<a href="#' . ($num) . '">ID = #' . ($num) . 'へジャンプ</a>';
    $num_mem = $num;
}

echo "<br> \n";
file_put_contents("./del.txt", $name, FILE_APPEND);

$file = fopen("./sanf.html", "r");
$i = 1;
while ($i <= 7) { //!feof($file)と同じ（falseの間続けますよ）の意味
    // while (feof($file) === false) { //!feof($file)と同じ（falseの間続けますよ）の意味
    $line = trim(fgets($file));
    //var_dump($line);

    if ($i === 7) {
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
// $data = explode("name", htmlspecialchars($line, ENT_QUOTES, 'utf-8'));
$data = explode('class="name">', $line);
// var_dump($data);
$j = 0;
$thread_pre = "";
foreach ($data as $value) {
    // echo $j; echo '<br>';
    $del_sw = 0;    // 0: NG IDではない 1:NG IDである
    if ($j > 0 && $j < 1001) {
        $name = file("./del.txt");  //NG IDを読み込む
        // var_dump($value); echo '<br>'; echo '<br>';
        // echo '<br>';
        $id = mb_strstr($value, "uid", false);
        $id = mb_strstr($id, "span", true);
        $id = mb_substr($id, 8, 9);
        $thread = mb_strstr($value, "escaped", false);
        $thread = mb_substr($thread, 9);
        // echo $j.$id; echo '<br>';
        // echo $thread; echo '<br><br>';
        $thread = mb_strstr($thread, "/span", true);
        $len = mb_strlen($thread);
        $thread = mb_substr($thread, 0, $len - 1);


        if ($thread !== $thread_pre) {  //ひとつ前のスレとダブりがなければ、
            foreach ($name as $row) {   //このIDがNGかどうか確認する。まずNG IDを配列にする
                $row = trim($row);
                if ($id === $row) {     //NG IDと一致するか
                    $del_sw = 1;
                }
            }

            if ($del_sw !== 1) {     //NG IDと一致しない場合
                $pos = strpos($thread, "../test/read.cgi/soccer/"); //引用リンク(>6など)が、スレ内の何文字目に存在するかを$posに代入
                if ($pos !== false) {   //引用リンクが存在する場合、
                    $pick = substr($thread, $pos, 35);  //リンク先のフォルダを修正  $thread内の$pos文字目の35文字を$pickとする
                    $thread = str_replace($pick, "index.php#", $thread);   // $thread内の$pickをindex.php#に置き換える
                }
                echo '<a id="' . $j . '">' . $j . '</a>';
                echo '<form action="index.php#" method="get">';
                echo '<input type="text" name="name" value="' . $id . '" style="border:none;">';
                echo '<input type="hidden" name="num" value="' . $j . '">';
                echo '<button type="submit" name="button" value="del" style="background-color:white; border:solid gray 1px;border-radius:50%;">削除</button>';
                echo '<button type="submit" name="button" value="over" style="background-color:white; border:solid gray 1px;border-radius:50%;">読了</button>';
                echo '</form>';

                echo '<p style=" font-family:メイリオ; font-size: 20px; background: azure";>' . $thread . '</p>';
                echo "\n";
                $thread_pre = $thread;
            }
        }
    }

    $j++;
}

fclose($file);
// require "sanf_select.php";