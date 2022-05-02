<?php

class MineWeeper
{
    /**
     * @var int 棋盘行数
     */
    private $rowNum = 5;

    /**
     * @var int 棋盘列数
     */
    private $columnNum = 5;

    /**
     * @var int 炸弹数
     */
    private $mineNum = 3;

    /**
     * @var array 点位数据
     */
    private $board = [];

    /**
     * 操作说明
     */
    const OPERATION = [
        'o'   => '打开未知区域',
        'c'   => '插旗',
        'unc' => '取消插旗',
    ];

    public function __construct($rowNum, $columnNum, $mineNum)
    {
        $this->rowNum    = $rowNum;
        $this->columnNum = $columnNum;
        $this->mineNum   = $mineNum;

        $this->init();
        $this->setMine();
        $this->setNumber();
    }

    /**
     * 初始化棋盘
     */
    public function init()
    {
        for ($x = 0; $x < $this->rowNum; $x++) {
            for ($y = 0; $y < $this->columnNum; $y++) {
                $this->board[$x][$y] = new Point($x, $y);
            }
        }
    }

    /**
     * @desc 设置炸弹的位置
     * @throws Exception
     */
    public function setMine()
    {
        $mineIndexList = [];
        for ($i = 0; $i < $this->mineNum; $i++) {
            $index = random_int(0, $this->rowNum * $this->columnNum);
            if (in_array($index, $mineIndexList)) {
                $i--;
                continue;
            }
            $mineIndexList[] = $index;
        }

        for ($i = 0; $i < $this->mineNum; $i++) {
            $index = $mineIndexList[$i];
            $x     = floor($index / $this->rowNum);
            $x     = $x > 0 ? $x : 0;
            $y     = $index % $this->rowNum;

            $point         = $this->board[$x][$y];
            $point->isMine = true;
        }
    }

    /**
     * 计算点位应该保存的数字值
     */
    public function setNumber()
    {
        for ($x = 0; $x < $this->rowNum; $x++) {
            for ($y = 0; $y < $this->columnNum; $y++) {
                $curPoint = $this->board[$x][$y];
                if ($curPoint->isMine) {
                    continue;
                }
                $totalMine      = 0;
                $checkPointList = [
                    [$x - 1, $y - 1],
                    [$x - 1, $y],
                    [$x - 1, $y + 1],
                    [$x, $y - 1],
                    [$x, $y + 1],
                    [$x + 1, $y - 1],
                    [$x + 1, $y],
                    [$x + 1, $y + 1]
                ];
                foreach ($checkPointList as $item) {
                    $point = $this->board[$item[0]][$item[1]];
                    if (!$point) {
                        continue;
                    }
                    if ($point->isMine) {
                        $totalMine++;
                    }
                }
                $curPoint->number = $totalMine;
            }
        }
    }

    /**
     * @desc 展示棋盘
     * @param false $isOver 是否为游戏结束标识
     */
    public function display($isOver = false)
    {
        $str = '';
        for ($i = 0; $i < $this->columnNum; $i++) {
            $zeroCount = floor($i / 10);
            $zeroCount = $zeroCount == 1 ? 0 : 1;
            $zeroStr   = str_repeat(' ', $zeroCount);
            $str       .= $zeroStr . $i . ' ';
        }
        $str .= PHP_EOL;
        $str .= str_repeat(" \033[0;32m|\033[0m ", $this->columnNum);
        $str .= PHP_EOL;

        for ($i = 0; $i < $this->rowNum; $i++) {
            for ($j = 0; $j < $this->columnNum; $j++) {
                $point = $this->board[$i][$j];
                if ($isOver) {
                    $showElement = $this->getPointRawContent($point);
                } else {
                    $showElement = '◾️';
                    if ($point->isConfirmed) {
                        $showElement = '🚩';
                    } else {
                        if ($point->isShow) {
                            $showElement = $this->getPointRawContent($point);
                        }
                    }
                }


                $str .= $showElement . '|';
            }
            $str .= " \033[0;32m-\033[0m " . $i . PHP_EOL;
        }
        echo $str;
    }

    /**
     * 处理点击⌚事件
     * @param $x integer x坐标
     * @param $y integer y坐标
     * @param $type string 点击类型 o: 打开点位 / c:插旗 / unc: 取消插旗
     */
    public function click($x, $y, $type)
    {
        $point = $this->board[$x][$y];
        switch ($type) {
            case 'o':
                $this->clickForOpen($point);
                break;
            case 'c':
                $this->clickForConfirm($point);
                break;
            case 'unc':
                $this->clickForUnConfirm($point);
                break;
        }
        $this->display();
    }

    public function clickForConfirm($point)
    {
        if ($point->isShow) {
            return;
        }
        $point->isConfirmed = true;
        $this->checkSuccessProcess();
    }

    public function clickForUnConfirm($point)
    {
        $point->isConfirmed = false;
    }

    public function clickForOpen($point)
    {
        $x                  = $point->x;
        $y                  = $point->y;
        $point->isShow      = true;
        $point->isConfirmed = false;
        if ($point->isMine) {
            echo 'you die!💣💣💣💣💣💣💣💣💣💣💣💣💣💣💣💣💣' . PHP_EOL;
            $this->display(true);
            exit();
        }

        // 如果数字为0，则将其上下左右的点位不是炸弹的点打开
        if ($point->number === 0) {
            $checkPoint = [
                [$x - 1, $y],
                [$x + 1, $y],
                [$x, $y - 1],
                [$x, $y + 1]
            ];
            while ($pointPosition = array_shift($checkPoint)) {
                $x     = $pointPosition[0];
                $y     = $pointPosition[1];
                $point = $this->board[$x][$y];
                if (!$point) {
                    continue;
                }

                if ($point->isMine || $point->number !== 0 || $point->isShow) {
                    $point->isShow = true;
                    continue;
                }
                $point->isShow = true;

                array_push($checkPoint, [$x - 1, $y], [$x + 1, $y], [$x, $y - 1], [$x, $y + 1]);
            }
        }
    }

    /**
     * 检查是否成功
     */
    public function checkSuccessProcess()
    {
        $success = $this->checkSuccess();
        if ($success) {
            echo '恭喜你游戏成功🎆🎆🎆🎆🎆🎆🎆🎆🎆🎆🎆🎆🎆🎆🎆' . PHP_EOL;
            $this->display(true);
            exit();
        }
    }

    public function checkSuccess(): bool
    {
        for ($x = 0; $x < $this->rowNum; $x++) {
            for ($y = 0; $y < $this->columnNum; $y++) {
                $point = $this->board[$x][$y];
                // 如果有不是炸弹，但是插旗的，则不算赢
                if ($point->isConfirmed && !$point->isMine) {
                    return false;
                }
                // 如果有是炸弹，但是没插旗的，则不算赢
                if (!$point->isConfirmed && $point->isMine) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * 数字添加颜色
     * @param $number
     * @return mixed|string
     */
    public function numberFormat($number)
    {
        if ($number <= 1) {
            return $number;
        }
        $colorCode = 47;
        switch ($number) {
            case $number <= 2:
                $colorCode = 46;
                break;
            case $number <= 4:
                $colorCode = 45;
                break;
            case $number <= 6:
                $colorCode = 44;
                break;
            case $number <= 8:
                $colorCode = 41;
                break;
        }
        return "\033[0;{$colorCode}m{$number}\033[0m";
    }

    /**
     * 操作说明
     */
    public function getOperationInstructions()
    {
        $operation = '';
        foreach (self::OPERATION as $key => $desc) {
            $operation .= "【操作符】:{$key} -> {$desc} \n";
        }
        $operation .= "输入坐标加【操作符】\n比如:\n2,3,c,意思是打开2,3坐标下的内容。\n2,3,o是在2,3坐标下插旗\n2,3,uno是取消插旗";
        return $operation;
    }

    /**
     * 获取点位的内容
     * @param $point
     * @return string
     */
    public function getPointRawContent($point): string
    {
        if ($point->isMine) {
            return '💣';
        } else {
            return ' ' . $this->numberFormat($point->number);
        }
    }
}
