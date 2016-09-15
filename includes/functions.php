<?php
// searches all characters between 2 specified tags in $s
// from $start
// to $end
// return array ($result, $sPos,$ePos) where $sPos - start tag end
// and $ePos - end tag beginning
// if not found, return false
function mcgfuidgen_GetStringBetweenTags($sTag, $eTag, $s, $start=0, $end=0) {
	$l = strlen($sTag);
	if ((strlen($s) <= 0) || (strlen($eTag) <= 0) || ($l <= 0))
		return false;
	$p0 = (int) $start;
	if ($p0 >= strlen($s))
		return false;
	$end = (int) $end;
	// search start tag
	$sPos = stripos($s, $sTag, $p0);
	// if not found or found outside $end - return false, not found
	if (($sPos === false) || (($end > 0) && ($sPos >= $end)))
		return false;
	$sPos+=$l;
	$ePos = stripos($s, $eTag, $sPos);
	// if not found or found outside $end - return false, not found
	if (($ePos === false) && ($end <= 0))
		$ePos = strlen($s);
	else
		if (($ePos === false) || (($end > 0) && ($ePos >= $end)))
			return false;
	$s = substr($s, $sPos, $ePos - $sPos);
	$res = array($s, $sPos, $ePos);
	return $res;
} // mcgfuidgen_GetStringBetweenTags
