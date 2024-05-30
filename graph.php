<?php
$location = $_GET['location'];
$del = $_GET['del'];
$rawJsonTxt = file_get_contents('MB_chapters.json');
$MBlist = json_decode(preg_replace('/\xc2\xa0/', ' ', $rawJsonTxt));
$bigD = '';
if($location == 'Whole Book of Mormon') {
	wholeBookOfMormon($location, $del, $MBlist);
} else {
	//check if theres a number at the end
	if(is_numeric(substr($location, -1))) {
		oneChapter($location, $del, $MBlist);
	} else {
		oneBook($location, $del, $MBlist);
	}
}


function wholeBookOfMormon($location, $del, $MBlist) {
	//echo("searching the whole Book of Mormon");
	$bLinks = json_decode(file_get_contents('MB_chapter_links.json'));

	$Xaxis = array();
	$Yaxis = array();
	$links = array();
	
	foreach($MBlist as $b => $cNams) {
		$w = '';
		foreach($cNams as $cNam) {
			$c = json_decode(file_get_contents('MB/' . $cNam[1] . '.json'));
			$w .= combineVerses($c);
			//echo $cNam[1].'<br>';
			//array_push($Xaxis, $cNam[1]);
		}
		array_push($Xaxis, $b);
		array_push($Yaxis, searchString($w, $del));
		array_push($links, $bLinks->$b);
	}
	$GLOBALS['bigD'] = '['.json_encode($Xaxis).','.json_encode($Yaxis).','.json_encode($links).']';
    //echo($bigD);
}
function oneBook($location, $del, $MBlist) {
	//echo("searching 1 book");
	$chapters = $MBlist->$location;

	$Xaxis = array();
	$Yaxis = array();
	$links = array();
	
	foreach($chapters as $cNam) {
		//fetch that chapter
		$c = json_decode(file_get_contents('MB/' . $cNam[1] . '.json'));
		$w = combineVerses($c);
		//add total word count
		array_push($links, $cNam[0]);
		array_push($Xaxis, $cNam[1]);
		array_push($Yaxis, searchString($w, $del));
	}
	$GLOBALS['bigD'] = '['.json_encode($Xaxis).','.json_encode($Yaxis).','.json_encode($links).']';
    //echo($bigD);
}
function oneChapter($location, $del, $MBlist) {
	//get chapter link
	$bNam = bookNamFromChap($location);
	$chapLink = '';
	foreach($MBlist->$bNam as $chap) {
		if($chap[1] == $location) {
			$chapLink = $chap[0];
			break;
		}
	}
	
	//echo("searching 1 chapter");
	$thisChapter = json_decode(file_get_contents('MB/' . $location . '.json'));

	$Xaxis = array();
	$Yaxis = array();
	$links = array();
	
	foreach($thisChapter as $v) {
		array_push($Xaxis, $v[0]);
		array_push($Yaxis, searchString($v[1], $del));
		array_push($links, $chapLink . vLink($v[0]));
	}
	$GLOBALS['bigD'] = '['.json_encode($Xaxis).','.json_encode($Yaxis).','.json_encode($links).']';
    //echo($bigD);
}
//

// - - - - - - - - - - - - - - - - - -
//The real serching starts here *smirk*

function searchString($body, $del) {
	if(isset($_GET['ctrlfmatch'])) { // this will count everything, even inside other words (like ctrl+f does)
		return preg_match_all('/'.strtolower(preg_quote($del)).'/', strtolower($body), $matches);
	} else {
		$body = preg_replace( '/[\W]/', ' ', $body);
		return preg_match_all('/\\b'.strtolower(preg_quote($del)).'\\b(?>\\s|$)/i', strtolower($body), $matches);
	}
}

function combineVerses($c) {
	$o = '';
	foreach($c as $v) {
		$o .= $v[1] . ' ';
	}
	return $o;
}

function vLink($vNum) {
	return '&id=p' . $vNum . '#p' . $vNum;
}
function bookNamFromChap($location) {
	$a = explode(' ', $location);
	array_pop($a);
	return join(' ', $a);
}
?>
<head>
	<script src='https://cdn.plot.ly/plotly-2.16.1.min.js'></script>
  <style>
body, html {
  width: 100%;
  height: 100%;
  overflow: hidden;
}
#myDiv {
  width: 100%;
  height: 100%;
}
  </style>


<body>
	<div id='myDiv'></div>
  <script>
let bigD = <?php echo($GLOBALS['bigD']); ?>;

var trace1 = {
  x: bigD[0],
  y: bigD[1],
  type: 'bar',
  marker: {
        color: '#b3ddff',
        line: {
            width: 2
        }
    }
};
var data = [trace1];
Plotly.newPlot('myDiv', data);
  </script>
