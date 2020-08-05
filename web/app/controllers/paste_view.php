<?php

$paste_id = $_GET['rand_str_id'];

$paste = DB::selectFirst("select * from pastes where `index` = '".DB::escape($paste_id)."'");

$REQUIRE_LIB['shjs'] = "";
echoUOJPageHeader("Paste!");
echoPasteContent($paste);
echoUOJPageFooter();