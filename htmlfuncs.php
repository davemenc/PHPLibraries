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
function HTML_Version(){
	return "$Id: htmlfuncs.php,v 1.11 2008/01/28 18:17:18 dmenconi Exp $";
}
function Display_Generic_Header($title,$color="#FFFFFF",$css="",$ico=""){

$year = Date("Y");
if ($css!="")$cssline = "<link rel=\"stylesheet\" type=\"text/css\" href=\"$css\" />";
else $cssline = "";

if ($ico!="")$icoline ="<link rel=\"SHORTCUT ICON\" href=\"$ico\"/>";
else $icoline="";

echo <<<EOF
<html>
<head>
   <meta http-equiv="Content-Type" content="text/html;
charset=iso-8859-1">
   <meta name="description" content="$title">
   <meta name="">
   <meta name="DISTRIBUTION" content="IU">
   <meta name="ROBOTS" content="noindex,nofollow">
   <meta name="revisit-after" content="30 days">
   <meta name="copyright" content="Copyright &copy; $year Dave Menconi, All
Rights Reserved">

   <meta name="author" content="Dave Menconi">

   <meta name="rating" content="PG-13">
   $cssline
   $icoline
    <Title>$title</title>
</head>
<body bgcolor="$color" >
EOF;
}
/***********************************************************
 Display_Template()
$template: path to template file
$delim: delimiter character
$values: an array of values to be substitued into template
         the template may contain the delim character followed by
         a string followed by a space; in this case the string is looked
         up in the array and the value in the array is substituted
         if there is no value in the array, the string is replaced with null
***********************************************************/
function Display_Template($template,$delim="%",$values=""){
	//debug_string("Display_Template()");
	// read the template file ($template)
	$template = getfile($template);
	//debug_string("template length",strlen($template));
	//debug_array("values",$values);
    // this will loop through, replacing special characters
    if ($values!=""){
		while(1){//infinite loop
			//location of insertion file character
			$i = strpos($template,$delim);
			//debug_string("delim location",$i);

			// check for no more insertions
			if ($i===FALSE){
				//debug_string("no delim location ");
				break;
			}

			//find key (defined as after delim but before space)
			$i++;// skip delim character
			$j = strpos($template," ",$i);//find next space
			//debug_string("keyword end location",$j);
			$len = $j-$i;//calc length
			//debug_string("key len",$len);
			$key = substr($template,$i,$len);//get key
			//debug_string("key",$key);

			// get value for key
			$value="";
			if(isset($values[$key]))	$value = $values[$key];
			//debug_string("value",$value);

			//replace string in template with actual contents of array
			$search = $delim.$key;
			//debug_string("search",$search);
			$template = str_replace($search,$value,$template);
		}
	}
    //debug_string("done with subst");

    print($template);
}
/***********************************************************
 * Display_Generic_Footer()
***********************************************************/
function Display_Generic_Footer($version,$lastmodified){
$year = Date("Y");
echo<<<EOF
		<div class="footer">
	     <p><font size=-2>Version: $version <br>Last changed on $lastmodified.</font>
	     <p><font size=-2>This page (including all images) Copyright &copy; $year  Dave Menconi.</font>
	     </div>
	     </body>
</html>
EOF;
}
/***********************************************************
 *  GetScriptName()
***********************************************************/
function GetScriptName(){
	$thisapp = $_SERVER['SCRIPT_NAME'];
	$pos = strpos($thisapp,"/",1)+1;
	//debug_string("pos",$pos);
	$thisapp = substr($thisapp,$pos);
	return $thisapp;
}
/*
getfile

$fname: File to open

Open file $fname, read it into a string and return the contents
*/
function getfile($fname){
    //debug_string("getfile($fname)");
    $fh = @fopen($fname,'r');
    if (false===$fh) return false;
//debug_string("open worked");
    $data = fread($fh,filesize($fname));
    if (false === $data) return false;
//debug_string("read worked");
    fclose($fh);
    return $data;
}

/***********************************************************
* Create_Column_Heads
* Input: an associative array
* Return: A list of bolded titles based on the key of that array
* Side Effect: none
***********************************************************/
function Create_Column_Heads($data){
	$temp  = array_keys($data);
	for ($i=0;$i<count($temp);$i++){
		$temp[$i] = "<b>".ucwords($temp[$i])."</b>";;
	}
	return $temp;
}
/*
 * $Log: htmlfuncs.php,v $
 * Revision 1.11  2008/01/28 18:17:18  dmenconi
 * added divs to footers
 * removed webmaster link
 *
 * Revision 1.10  2007/12/03 01:09:06  dmenconi
 * added css capability
 *
 * Revision 1.9  2007/07/09 06:45:57  dmenconi
 * checkpoint
 *
 * Revision 1.8  2007/05/11 15:52:12  dmenconi
 * added license information
 *
 * Revision 1.7  2007/03/10 16:27:45  dmenconi
 * back from meissen.org
 *
 * Revision 1.1  2007/02/24 21:14:12  dave
 * Initial revision
 *
 * Revision 1.6  2007/02/08 09:29:16  dmenconi
 * fixed a tiny bug (litterally 1 character) that prevented display_template from working
 *
 * Revision 1.5  2007/02/06 06:05:05  dmenconi
 * comments and spacing
 * display template routine
 *
 * Revision 1.4  2005/06/20 14:36:56  dave
 * *** empty log message ***
 *
 * Revision 1.3  2005/03/01 17:38:15  dave
 * blank last line can cause problems (?)
 *
 * Revision 1.2  2005/02/07 18:39:31  dave
 * added $Log: htmlfuncs.php,v $
 * added Revision 1.11  2008/01/28 18:17:18  dmenconi
 * added added divs to footers
 * added removed webmaster link
 * added
 * added Revision 1.10  2007/12/03 01:09:06  dmenconi
 * added added css capability
 * added
 * added Revision 1.9  2007/07/09 06:45:57  dmenconi
 * added checkpoint
 * added
 * added Revision 1.8  2007/05/11 15:52:12  dmenconi
 * added added license information
 * added
 * added Revision 1.7  2007/03/10 16:27:45  dmenconi
 * added back from meissen.org
 * added
 * added Revision 1.1  2007/02/24 21:14:12  dave
 * added Initial revision
 * added
 * added Revision 1.6  2007/02/08 09:29:16  dmenconi
 * added fixed a tiny bug (litterally 1 character) that prevented display_template from working
 * added
 * added Revision 1.5  2007/02/06 06:05:05  dmenconi
 * added comments and spacing
 * added display template routine
 * added
 * added Revision 1.4  2005/06/20 14:36:56  dave
 * added *** empty log message ***
 * added
 * added Revision 1.3  2005/03/01 17:38:15  dave
 * added blank last line can cause problems (?)
 * added at end of file
 * added $Id: htmlfuncs.php,v 1.11 2008/01/28 18:17:18 dmenconi Exp $ in version function
 *
 */
?>
