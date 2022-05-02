<?php

class Reader
{
    public function read()
    {
        echo "请输入操作：";
        $handle = fopen ("php://stdin","r");
        $line = fgets($handle);
        fclose($handle);
        return $line;
    }
}
