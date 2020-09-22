<?php

namespace MyApp;

class Calendar
{
    public $prev;
    public $next;
    public $yearMonth;
    private $_thisMonth;


    public function __construct()
    {
        try {
            if (!isset($_GET['t']) || !preg_match('/\A\d{4}-\d{2}\z/', $_GET['t'])) {
                throw new \Exception();
            }
            $this->_thisMonth = new \DateTime($_GET['t']);
        } catch (\Exception $e) {
            $this->_thisMonth = new \DateTime('first day of this month');
        }

        $this->prev = $this->_createPrevLink();
        $this->next = $this->_createNextLink();
        $this->yearMonth = $this->_thisMonth->format('F Y');
    }


    private function _createPrevLink()
    {
        $dt = clone $this->_thisMonth;
        return $dt->modify('-1 month')->format('Y-m');
    }


    private function _createNextLink()
    {
        $dt = clone $this->_thisMonth;
        $next = $dt->modify('+1 month')->format('Y-m');
    }

    //カレンダーの表示処理
    public function show()
    {
        $tail = $this->_getTail();
        $body = $this->_getBody();
        $head = $this->_getHead();
        $html = '<tr>' . $tail . $body . $head . '</tr>';
        echo $html;
    }

    //前月の終わりを取得
    private function _getTail()
    {
        $tail = '';
        $lastDayOfPrevMonth = new \DateTime('last day of ' . $this->yearMonth . ' -1 month'); //前月の最終日のみ取得する
        while ($lastDayOfPrevMonth->format('w') < 6) {
            $tail = sprintf('<td class="gray">%d</td>', $lastDayOfPrevMonth->format('d')) . $tail; //$lastDayOfPrevMonthには31が代入されている
            $lastDayOfPrevMonth->sub(new \DateInterval('P1D')); //sub()を使ってDateInterval('P1D')でDateTime()の31から1引いてループ
        }                                                       //ごとに30...29...28となる
        return $tail;
    }

    //当月の処理
    private function _getBody()
    {
        $body = '';
        $period = new \DatePeriod( //日付の期間を表すクラス
            new \DateTime('first day of ' . $this->yearMonth), //第一引数に始まり
            new \DateInterval('P1D'), //第二引数に間隔
            new \DateTime('first day of ' . $this->yearMonth . ' +1 month') //第三引数に終わり そのは含まないこの場合次月の1日は含まず末日まで
        );
        $today = new \DateTime('today');
        foreach ($period as $day) {
            if ($day->format('w') === '0') { //DateTimeobjectで2020/9/22現在の9/1(火)を取得しているのでformat('w')で最初に「2」が取得できる
                $body .= '</tr>
    <tr>';
            }
            $todayClass = ($day->format('Y-m-d') === $today->format('Y-m-d')) ? 'today' : ''; //trueなら$todayClassにtodayを代入
            $body .= sprintf(
                '<td class="youbi_%d %s">%d</td>',
                $day->format('w'), //format():DateTimeobjectを好きな書式で表示する
                $todayClass, //$todayClassはtdタグのクラス名:cssでtodayクラスはfont-weight: boldで指定している
                $day->format('d')
            );
        }
        return $body;
    }

    //次月の始まりを取得
    private function _getHead()
    {
        $head = '';
        $firstDayOfNextMonth = new \DateTime('first day of ' . $this->yearMonth . ' +1 month');
        while ($firstDayOfNextMonth->format('w') > 0) {
            $head .= sprintf('<td class="gray">%d</td>', $firstDayOfNextMonth->format('d'));
            $firstDayOfNextMonth->add(new \DateInterval('P1D'));
        }
        return $head;
    }
}
