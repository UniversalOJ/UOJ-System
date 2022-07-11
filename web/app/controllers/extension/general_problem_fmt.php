<?php
class GeneralHeader {
    public $title = "default_title";
    public $name = "tmp";
    public $content = "";
    public $tag = array();
    public $type = 0;
    public $oj = "UOJ";
}

class DataSegment {
    public $n_subtask = -1;
    public $n_testcase = 0;
    public $score = array();
    public $time_limit = array();
    public $memmory_limit = array();
}

class OJSpecificSegment {
    public $content = "";
}
?>