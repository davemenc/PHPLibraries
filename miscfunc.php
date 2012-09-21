<?php
 /*
  	Copyright (c)  2007 Dave Menconi

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
/* $Id: miscfunc.php,v 1.11 2007/05/11 15:52:12 dmenconi Exp $ */
/**
 * miscfunc.php is a collection of routines 
 * 
 * These routines fall into a variety of categories
 * from redirecting control to another html page to get stuff out
 * of the whatchamall database. 
 * In some cases we might expect that these routines will be moved into
 * more specific files later but generally, they are here to stay. 
 * @author Dave Menconi 
 */
include_once "../library/loc_login.php";
include_once "../library/mysql.php";

/**
 *  JumpTo redirects to the specified URL and then stops
 * 
 * Jumpto uses either the PHP header command or the javascript jump command 
 * this is the only place in all of whatchamall that we use javascript and, pratically,
 * it will only be invoked if we've already written the headers. This probably means
 * (absent a bug) that we have some debugging code in there. 
 * $myurl is the place it's going to jump. if it isn't set, we'll jump to index.php
 * @param string $myurl
 * @author Dave Menconi 
 */
function JumpTo($myurl="index.php"){
	header("Location $myurl") or jscriptJumpTo($myurl);
	exit();
}
/**
 * this function uses java script to redirect if the header("Location: xx") fails.
 * 
 * This is the java script way to jump and we don't really want to use it. However
 * if we've already sent the headers this is the only way to redirect. 
 * 
 * This should never be called directly! Always call JumpTO and let it call this one if it needs. 
 * @param string $myurl
 * @author Dave Menconi 
 */
//this function uses java script to redirect if the header("Location: xx") fails.
function jscriptJumpTo($myurl="index.php") {
//	print jscriptJumpTo($myurl);
echo <<<EOF
<HTML><HEAD><TITLE>Redirecting..</TITLE></HEAD><BODY>
<script>
<!--
document.location="$myurl";
//-->
</script>
</BODY></HTML>
EOF;
exit();
}
/**
 * Filters the standard RCS version string and removes various things from it so it's suitable for display 
 * 
 * @param string $$rcsversion string from RCS
 * @return  datatype  string with stuff we don't want removed
 * @author Dave Menconi <dave@menconi.com>
 */
function FilterVersion($rcsversion,$version=""){
	$rcsversion = str_replace("$","",$rcsversion);
    $rcsversion = substr($rcsversion,strpos($rcsversion,",v")+2);
    $localversion = $version . "<br>RCSversion: " . $rcsversion;
	return $localversion;		 
}
/**
 * Removes all tags and other undesirable characters
 * 
 * 
 * This routine removes all tags and special characters and
 * trims all th strings
 * @param array $ar an array of strings
 * @return array the string it was passed but cleaned up
 * @author Dave Menconi 
 */
function BrickWall($ar){
	if (!isset($ar)|| count($ar)<1)return $ar;
	foreach($ar as $key=>$value){
		$ar[$key]=strip_tags($value);
		$ar[$key]=trim($value);
	}
	$remove = array(
		"<script>",
		"</script>",
		chr(0)."..".chr(31),
		chr(127)."..".chr(255)
	);
	$ar = str_replace ( $remove, "",$ar);
	return $ar;
}
/**
 * Logs statistics about system usage
 * 
 * Actually pretty simple; it just takes a bunch of data from $_SERVER and dumps it to a database
 * needs the $PARAM array so that it can get the paramters and the $link so it can access the database.
 * @@param  array  $PARAMS we need this to include the parameters from get or post
 * @@param resource $link this is the resource that connects to the database
 * @@author Dave Menconi 
 */
function LogStats($link,$event,$eventtype="Entry"){
	if(is_array($event)){
		$newevent="";
		foreach($event as $key=>$value){
			$newevent .= "&".  $key.  "=".  $value;
		}
		$event=$newevent;
	}
	$user=loc_get_username();

	$insert="insert into guesslog (referer , remote_addr , http_user_agent ,username ,request_uri ,eventtype,event) values ('".$_SERVER['HTTP_REFERER']."','".$_SERVER['REMOTE_ADDR']."','".$_SERVER['HTTP_USER_AGENT']."','".$user."','".$_SERVER['REQUEST_URI']."','".$eventtype."','".$event."')";
	$result = mysql_query($insert,$link);
}
/* 
 * $Log: miscfunc.php,v $
 * Revision 1.11  2007/05/11 15:52:12  dmenconi
 * added license information
 *
 * Revision 1.9  2007/04/13 07:02:32  dmenconi
 * added addrecord, parseaddrecord, editrecord, parseeditrecord
 *
 * Revision 1.8  2007/03/19 08:24:26  dmenconi
 * added a bunch of date routines
 *
 * Revision 1.7  2007/03/10 16:31:39  dmenconi
 * added new function to log data
 *
 * Revision 1.2  2007/02/24 21:03:58  dave
 * added a log function and spiffed it up a bit, tested it, etc.
 *
 * Revision 1.1  2007/02/24 18:32:22  dave
 * Initial revision
 *
 * Revision 1.6  2007/02/06 06:06:46  dmenconi
 * safety
 *
 */
