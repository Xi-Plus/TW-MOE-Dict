<?php
require(__DIR__."/dict.php");

$dict = new TWMOEDict();

$res = $dict->search("^測試$");
var_dump($res);

$res = $dict->search("巴哈", true);
var_dump($res);
