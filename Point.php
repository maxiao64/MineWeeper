<?php

class Point
{
    /**
     * @var bool 是否是炸弹flag
     */
    public $isMine = false;

    /**
     * @var int 周围炸弹的数量
     */
    public $number = 0;

    /**
     * @var bool 展示flag
     */
    public $isShow = false;

    /**
     * @var bool 是否插旗flag
     */
    public $isConfirmed = false;

    /**
     * @var int x坐标
     */
    public $x = 0;

    /**
     * @var int y坐标
     */
    public $y = 0;

    public function __construct($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
    }
}
