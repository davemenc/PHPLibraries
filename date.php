<?php
 /*
  	Copyright 2007 Dave Menconi

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
*/
/* $Id: date.php,v 1.1 2007/05/11 15:50:49 dmenconi Exp $ */
/**
 * date.php is a collection of routines that do date conversions
 * 
 * @author Dave Menconi 
 */

function tomonthword($monthno,$len=0){
	$monthno--;
	$monthwords = array("January",
"February", "March", "April","May","June", "July","August", "September","October", "November","December");
	if ($len!=0){
		return substr($monthwords[$monthno],0,$len);	
	}
	return $monthwords[$monthno];
}	
/**
 * parses a date into it's components 
 * 
 * Takes a timestamp format and converts it into an array. Intended for 
 * dates from a mysql call. 
 * @param string $datestr
 * @return  datatype  array of string components: year, mon, mday, hours, minutes, seconds
 * @author Dave Menconi 
 */
function parse_tsdate($datestr){
//20070317120012
	$datearray['year']=substr($datestr,0,4);
	$datearray['month']=substr($datestr,4,2);
	$datearray['mday']=substr($datestr,6,2);
	$datearray['hours']=substr($datestr,8,2);
	$datearray['minutes']=substr($datestr,10,2);
	$datearray['seconds']=substr($datestr,12,2);
	//debug_array("datearray",$datearray);
	return $datearray;
}
function parse_dbdate($datestr){
//2007-03-17
	$datearray['year']=substr($datestr,0,4);
	$datearray['month']=substr($datestr,5,2);
	$datearray['mday']=substr($datestr,8,2);
	return $datearray;
}
function parse_usdate($datestr){
//03/17/2007
	//debug_string("parse_usdate($datestr)");
	$datearray['month']=substr($datestr,0,2);
	$datearray['mday']=substr($datestr,3,2);
	$datearray['year']=substr($datestr,6,4);
	return $datearray;
}

function smart_parse_date($datestr){
//	debug_string(" smart_parse_date($datestr)");
	if (strpos($datestr,"-")>0){
		$datearray = parse_dbdate($datestr);
	}else if (strpos($datestr,"\\")>0){
		$datearray = parse_tsdate($datestr); 
	}else{
		 $datearray = parse_tsdate($datestr); 
	}
	return $datearray;
}
function datearray_to_epoch($datearray){
    $result = mktime(12,00,00,$datearray[month],$datearray[mday],$datearray[year]);
	return $result;
}
/**
 * Converts date array format to a time stamp
 * 
 * used with parse_date to get things back into the date format
 * see parse_date for the format of the array
 * @param array $datearray
 * @return  string  in mktime format
 * @author Dave Menconi 
 */
function datearray_to_tstamp($datearray){
	$tstamp = mktime($datearray['hours'],$datearray['minutes'],$datearray['seconds'],$datearray['month'],$datearray['mday'],$datearray['year']);
	return $tstamp;
}
/**
 *  converts a datestring to a standard US date
 * 
 * this takes a date string and turns it into M/D/Y format
 * @param string $datestr
 * @return  string  m/d/y date format
 * @author Dave Menconi 
 */
function std_date($datestr){
	if (strpos($datestr,"-")===false) $datearray = parse_tsdate($datestr);
	else $datearray = parse_dbdate($datestr);
	//debug_array("datearray",$datearray);
	$tstamp = datearray_to_tstamp($datearray);
	//debug_string("tstamp",$tstamp);
	$std_date = date("n/j/Y",$tstamp);
	//debug_string("std_date",$std_date);
	return($std_date);
}
/**
 * Creates an array of epoch-formatted dates between two dates
 * 
 * Given two dates in m/d/y format, returns a list of dates (in epoch format) that are between them
 */
function CreateDateInterval($startdate,$enddate){
	$sepoch = StrToEpoch($startdate);
	$eepoch = StrToEpoch($enddate);
	for ($t=$sepoch; $t<=$eepoch;$t+=86400){
		$result[]=$t;
	}
	return $result;
}
/**
 * Converts from a string to an epoch
 * 
 * This is actually kind of hard. For some cases (to wit, m/d/y and d/m/y style dates) this will convert from a string
 * to an epoch. You can set the delim (meaning you can do m-d-y and d-m-y styles) and you can set whether it's U.S.
 * style (m/d/y) or european style (d/m/y). 
 */
function StrToEpoch($date,$delim="/",$usorder=true){
//	debug_string("StrToEpoch($date,$delim)");
//if($usorder)debug_string ("usorder");
//else debug_string ("euro order");
	//$daypos=$strpos($date,$delimI/
	$elements = explode ( $delim, $date );
	//debug_string("date",$date);
	//debug_array("elements",$elements);
	if ($usorder)$day = $elements[1];
	else $day = $elements[0];
	if ($usorder)$month = $elements[0];
	else $month = $elements[1];
	$year = $elements[2];
	if (strlen($year)<4){// 2 digit year
		if($year<70)$year+=2000;
		else $year+=1900;
	}	
	$result = mktime(12,00,00,$month,$day,$year);
	//debug_string("result",$result);
	//debug_string("result date",strftime('%c',$result));
	return $result;
}
/* 
 * $Log: date.php,v $
 * Revision 1.1  2007/05/11 15:50:49  dmenconi
 * Initial revision
 *
 *
 */
