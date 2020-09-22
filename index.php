<?php

function h($s)
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

try {
    if (!isset($_GET['t']) || !preg_match('/\A\d{4}-\d{2}\z/', $_GET['t'])) {
        throw new Exception();
    }
    $thisMonth = new DateTime($_GET['t']);
} catch (Exception $e) {
    $thisMonth = new DateTime('first day of this month');
}

$dt = clone $thisMonth;
$prev = $dt->modify('-1 month')->format('Y-m');
$dt = clone $thisMonth;
$next = $dt->modify('+1 month')->format('Y-m');

$yearMonth = $thisMonth->format('F Y');

//前月の終わりを取得
$tail = '';
$lastDayOfPrevMonth = new DateTime('last day of ' . $yearMonth . ' -1 month'); //前月の最終日のみ取得する
while ($lastDayOfPrevMonth->format('w') < 6) {
    $tail = sprintf('<td class="gray">%d</td>', $lastDayOfPrevMonth->format('d')) . $tail; //$lastDayOfPrevMonthには31が代入されている
    $lastDayOfPrevMonth->sub(new DateInterval('P1D')); //sub()を使ってDateInterval('P1D')でDateTime()の31から1引いてループごとに
}                                                      //30...29...28となる

$body = '';
$period = new DatePeriod(                     //日付の期間を表すクラス
    new DateTime('first day of ' . $yearMonth),  //第一引数に始まり
    new DateInterval('P1D'),                  //第二引数に間隔
    new DateTime('first day of ' . $yearMonth . ' +1 month')   //第三引数に終わり そのは含まないこの場合次月の1日は含まず末日まで
);
$today = new DateTime('today');
foreach ($period as $day) {
    if ($day->format('w') % 7 === 0) {  //DateTimeobjectで2020/9/22現在の9/1(火)を取得しているのでformat('w')で最初に「2」が取得できる
        $body .= '</tr><tr>';
    }
    $todayClass = ($day->format('Y-m-d') === $today->format('Y-m-d')) ? 'today' : ''; //trueなら$todayClassにtodayを代入
    $body .= sprintf(
        '<td class="youbi_%d %s">%d</td>',
        $day->format('w'),      //format():DateTimeobjectを好きな書式で表示する
        $todayClass,            //$todayClassはtdタグのクラス名:cssでtodayクラスはfont-weight: boldで指定している
        $day->format('d')
    );
}

//次月の始まりを取得
$head = '';
$firstDayOfNextMonth = new DateTime('first day of ' . $yearMonth . ' +1 month');
while ($firstDayOfNextMonth->format('w') > 0) {
    $head .= sprintf('<td class="gray">%d</td>', $firstDayOfNextMonth->format('d'));
    $firstDayOfNextMonth->add(new DateInterval('P1D'));
}

$html = '<tr>' . $tail . $body . $head . '</tr>';

?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <table>
        <thead>
            <tr>
                <th><a href="./?t=<?php echo h($prev); ?>">&laquo;</a></th>
                <th colspan="5"><?php echo h($yearMonth); ?></th>
                <th><a href="./?t=<?php echo h($next); ?>">&raquo;</a></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Sun</td>
                <td>Mon</td>
                <td>Tue</td>
                <td>Wed</td>
                <td>Thu</td>
                <td>Fri</td>
                <td>Sat</td>
            </tr>
            <?php echo $html; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="7"><a href="./">Today</a></th>
            </tr>
        </tfoot>
    </table>
</body>

</html>
