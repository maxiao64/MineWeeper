<?php
include_once "MineWeeper.php";
include_once "Point.php";
include_once "Reader.php";

$board = new MineWeeper(10, 5, 10);
echo $board->getOperationInstructions() . PHP_EOL;
$board->display();
$reader = new Reader();
while ($res = $reader->read()) {
    $position = explode(',', $res);
    $x = trim($position[0]);
    $y = trim($position[1]);
    $type = trim($position[2]);
    if (!(isset($x) && isset($y) && $type && in_array($type,array_keys(MineWeeper::OPERATION)))) {
        echo "按规则玩游戏" . PHP_EOL;
        continue;
    }
    $board->click($x, $y, $type);
}