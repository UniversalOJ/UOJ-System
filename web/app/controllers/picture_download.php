<?php
if(!isset($_GET['p'])) exit('Filename is empty');
ob_clean();
$imageBed = new ImageBed();
$filename = $_GET['p'];
$imageBed->download($filename);