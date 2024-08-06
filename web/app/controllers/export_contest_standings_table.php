<?php

if (!validateUInt($_GET['id']) || !($contest = queryContest($_GET['id']))) {
	become404Page();
}
genMoreContestInfo($contest);

if (!hasContestPermission(Auth::user(), $contest)) {
	becomeMsgPage('您没有权限下载此比赛的排名。');
}
if ($contest['cur_progress'] < CONTEST_FINISHED) {
	becomeMsgPage('比赛尚未结束，无法下载排名。');
}

$contest_data = queryContestData($contest);
calcStandings($contest, $contest_data, $score, $standings);

function array2csv(array &$array) {
	if (count($array) == 0) {
		return null;
	}

	ob_start();

	$df = fopen("php://output", 'w');

	fputcsv($df, array_keys(reset($array)));

	foreach ($array as $row) {
		fputcsv($df, $row);
	}

	fclose($df);

	return ob_get_clean();
}

$export_data = [];
$csv_header = ['Rank', 'Username', 'Score', 'Penalty'];
$n_problems = count($contest_data['problems']);

for ($i = 0; $i < $n_problems; $i++) {
	$csv_header[] = chr(ord('A') + $i);
	$csv_header[] = chr(ord('A') + $i) . '_penalty';
	$csv_header[] = chr(ord('A') + $i) . '_submission_id';
}

$export_data[] = $csv_header;

// Convert data in $standings and $score to $export_data
foreach ($standings as $rank => $row) {
	// $row: rank, username, score, penalty
	$res = [$rank + 1, $row[2][0], $row[0], $row[1]];
	for ($i = 0; $i < $n_problems; $i++) {
		// $score[$row[2][0]][$i]: score, penalty, submission_id
		$res[] = isset($score[$row[2][0]][$i][0]) ? $score[$row[2][0]][$i][0] : "";
		$res[] = isset($score[$row[2][0]][$i][1]) ? $score[$row[2][0]][$i][1] : "";
		$res[] = isset($score[$row[2][0]][$i][2]) ? $score[$row[2][0]][$i][2] : "";
	}
	$export_data[] = $res;
}

$csv = array2csv($export_data);

header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
header("Last-Modified: {$now} GMT");
header("Content-Type: text/csv");
header("Content-Disposition: attachment;filename={$contest['id']}_standings.csv");

die($csv);
