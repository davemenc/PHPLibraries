<?php
 /*
  	Copyright (c) 2007 Dave Menconi

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
/* $Id: list.php,v 1.20 2007/12/04 06:16:50 dmenconi Exp $ */
/**
 * ListData
 * PARAMETERS
 * $link: active link to a database
 * $fieldinfo array of field data
    Each field has 2 variables, "title" and "field". 
		"field" is the field name in the database (and, also, in the form) 
		"title" is the name it's to be displayed by
 * $dbinfo This has several pieces of information in it. 
        -- collectively the filter information is so that the user can select one field in the data to filter on
		- filter = is the name of the field that the user can filter on (a field in the filtertable table)
		- filtertable = is the table that has the possible values in this field
        - filterfield = is the field in the table that contains the names we're going to filter on
        - filtertableidfield = is the name of the field in the table that contains the unique unary index
		- index = is the unary index that we're going to use for editing a specific record -- it must be unique!
        -- collectively = the next few things are for doing a mysql select; necessary because the logic of the list will need to change the select
        - fieldnames: = the fields we want to extract from the database
        - tablenames = the tables that we want to get
        - where = the pieces of the where clause; these will all be put together by ands
        - order = the order you want in the select by default -- this will be overridden by the sort so I'm not sure it's useful
 * $displayinfo
	- mastertitle = the title that the page should have SHOULD NOT contain "list" 
	- actionkeyword = the name of the parameter that causes this page to be displayed
	- listname = the value of the keyword that causes this to be displayed
	- addname = the value of the action url variable that will result in an add
	- editname = the keyword to use to get something to be editd
	- editfield  = one of the columns in the displayed table will have links to edit the field; this is the name of the field we want to put the edit link on
    - editindexkeyword = the name of the url keyword that we will use for the index (how about "indexno") 
	- editindexfield = the name of the field whose value is the index
	- menu = a menu string (in html) that we'll display at the top of the page to allow people to find other parts of the system
	- css = the url of the css file
thus <a href=$self?$actionkeyword=$editname&$editindexkeyword=$database[$editindexfield]> {editname}</a>
	- color: the background color of the page
 * $PARAMS the get and post parameters
 * $PageLengths  a list of the possible page lengths that you want; it has good defaults
 */
/* 
********* BUGS TODO ********
no alpha menu (?)
//TODO gamesname and typename are sometimes undefined so they need to be isset checked
//TODO at one point  $pagelenidx had a value of 25 which it should never have
//TODO alphamenu is used so it needs to be defined or removed
*/
function ListData($link,$fieldinfo,$dbinfo,$displayinfo,$PARAMS,$PageLengths=""){
debug_string("==== ListData() ====");
    //================= Define Some Constant Tables ======================
    // define table headings and field names
    if ($PageLengths=="")$PageLengths = array(25,50,75,100,200,300);
    $listkeywords = array("first","page","sort","filtervalue");
    $listdefaults = array("first"=>"0","page"=>"25", "sort"=>"0", "filtervalue"=>"0");

    //================= Get the display parameters ========================
debug_string("================= Get the display parameters ========================");
	$mastertitle = $displayinfo['mastertitle'];
	$color = $displayinfo['color'];
	$listname = $displayinfo['listname'];	
	$actionkeyword = $displayinfo['actionkeyword'];	
	$addname = $displayinfo['addname'];	
    $self=$_SERVER['PHP_SELF'];
	$url = $self."?".$actionkeyword."=".$listname;
	$editname = $displayinfo['editname'];	
	$editfield = $displayinfo['editfield'];	
	$editindexkeyword = $displayinfo['editindexkeyword'];	
	$editindexfield = $displayinfo['editindexfield'];	
    $menu =  '<b><center>'. "<a href=\"$self?$actionkeyword=$addname\">Add Record</a> ".  '</center></b>';
//debug_string("actionkeyword",$actionkeyword);
//debug_string("listname",$listname);
	if (isset($displayinfo['menu']))	$menu = $displayinfo['menu'];
	if (isset($displayinfo['css']))		$css  = $displayinfo['css'];

    //================= get the list parameters =======================
    //debug_string("//================= get the list parameters =======================");
	$cookiename = $dbinfo['tablenames'][0]."ListParams";
	//debug_string("cookiename",$cookiename);	
//debug_array("listkeywords",$listkeywords);
	foreach ($listkeywords as $i => $keyword){
        if(isset($PARAMS[$keyword])) {
            $listparams[$keyword]=$PARAMS[$keyword];
//debug_string("$keyword",$listparams[$keyword]);
        }
        else if(isset($_COOKIE[$cookiename])){
//debug_string("cookie set ");
            $listparams[$keyword]=$_COOKIE[$cookiename][$i];
//debug_string("$keyword",$listparams[$keyword]);
 		}else{ 
//debug_string("default ");
			$listparams[$keyword]=$listdefaults[$keyword];
//debug_string("$keyword",$listparams[$keyword]);
 		} 
		$thiscookie = $cookiename."[".$i."]";
//debug_string("thiscookie",$thiscookie);

		setcookie($thiscookie,$listparams[$keyword],0);
 	} 
//debug_array("listparams",$listparams);
//debug_string("end of list parameters");

    // get the list of filternames
    //================= Get Data From filter Table ====================
	$filterflag = true;
	if (!isset($dbinfo['filtertable'])) $filterflag=false;
	if ($filterflag){
		$filtersql = "select * from ". $dbinfo['filtertable'];
//		debug_string("filtersql",$filtersql);
		$data = MYSQLGetData($link,$filtersql);
		$idfield = $dbinfo['filtertableidfield'];
		$filterfield = $dbinfo['filterfield'];
		foreach($data as $value){
//			debug_array("value",$value);
			$filterdata[$value[$idfield]] =	$value[$filterfield];
		}
	}
//	debug_array("data",$data);
//	debug_string("idfield",$idfield);
//	debug_string("filterfield",$filterfield);
//	debug_array("filterdata",$filterdata);
    //================= Get Data From Database =======================

	$order = $dbinfo['order'];
    $order[] = $fieldinfo[$listparams['sort']]['field'];

	$where = $dbinfo['where'];
	$filtervalue = $listparams['filtervalue'];
	if (isset($filterdata[$filtervalue])){//if we're looking for a value that exists...
		$where[] = $dbinfo['filter'].'='.$listparams['filtervalue'];
	} 
    $records = MYSQLComplexSelect($link, $dbinfo['fieldnames'],$dbinfo['tablenames'],$where,$order,0);
    $recordcount = count($records);
	if ($recordcount<1){
		foreach ($listkeywords as $i => $keyword){
			$thiscookie = $cookiename."[".$i."]";
			setcookie($thiscookie,"",time()-60);
		}
    	Display_Generic_Header("$mastertitle List",$color,$css);
		print "	<div class=\"listpage\"><center>\n<h1>$mastertitle Display Page</h1></center>\n <center><b>No Records Found</b></center>\n<center><a href=\"$self?$actionkeyword=$listname\">Return to List</a></div>\n";
		return;
	}
//TODO alphamenu is used so it needs to be defined or removed
    //$alphamenu = alphalist($records);

    //================= Calculate paging information ======================
    // $end -- we're at the end of the file, there is no next
    // $start -- we're at the start of the file, there is no start
    // $pagelen -- how many line show on a page
    // $firstline -- the first lines to show on the page
    // $lastline -- the last line to show on the page
    // $prevline -- if the user clicks on "previous", what's the first line going to be?
    // $nextline -- if the user clicks on "next" what's the first line going to be?
    // $pagelist -- array of line starts and ends for page menu
    // $pagemenu -- actual page menu in HTML
    // $startline -- first line of the firt page
    // $endline -- first line of thelast page

    $firstline = $listparams['first'];
	$pagelenidx = $listparams['page']; 
//TODO at one point  $pagelenidx had a value of 25 which is should never have
	$pagelen = $PageLengths[$pagelenidx];
    $end = false;
    $start = false;
    if ($pagelen<=25) $pagelen=25;
    if ($firstline<0) $firstline=0;
    if ($pagelen>$recordcount) $pagelen=$recordcount;
    $lastline = $firstline+$pagelen-1;
    if ($lastline>$recordcount) $lastline=$recordcount;
    if ($lastline >= $recordcount-$pagelen)$end=true;
    if ($firstline == 0) $start = true;


    $prevline = $firstline-$pagelen;
    if ($prevline<0) $prevline=0;
    $nextline = $firstline+$pagelen;
    if ($nextline>$recordcount)$nextline=$recordcount;
    $endline = $recordcount-$pagelen+1;
    $startline = 0;

    $previousparams = "first=$prevline";
    $firstparams = "first=$startline";
    if (!$start){
        $prevhtml = "<a href=\"$url&".$previousparams."\">Previous</a>";
        $firsthtml = "<a href=\"$url&".$firstparams."\">First</a>";
    } else {
        $prevhtml = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        $firsthtml = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    }
    $nextparams = "first=$nextline";
    $lastparams = "first=$endline";
    if (!$end) {
        $nexthtml = "<a href=\"$url&".$nextparams."\">Next</a>";
        $lasthtml = "<a href=\"$url&".$lastparams."\">Last</a><br>";
    } else {
        $nexthtml = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        $lasthtml = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>";
    }

    // page menu calculations
    $totalpages = intval($recordcount/$pagelen)+1;
    $p=1;
    //$pagemenu ="Pages: ";

    $pagemenu = "";
    for($l=0;$l<$recordcount;$l+=$pagelen){
        $pagelist[$p]['start'] = $l;
        $pagelist[$p]['end']= $l+$pagelen-1;
        if($firstline>=$l && $firstline<=$l+$pagelen-1){
            $currentpage=$p;
            $pagemenu .= " $p ";
        }else{
            $pagemenu .= " <a href=\"$url&first=$l\">$p</a> ";
        }
        $p++;
    }

    //======================== Draw Screen ===========================
   	Display_Generic_Header("$mastertitle List",$color,$css);
   	print " \n<div class=\"listpage\"> \n";
    //WriteBody($firstline,$lastline);
    	//debug_string("listparams[filtervalue]",$listparams['filtervalue']);
	if ($filterflag){
		//debug_string("filterflag set");
		//create filter pulldown menu
	    $filteroption = "&nbsp;&nbsp;<b>Filter by:</b> <select name=\"filtervalue\"><option value=\"0\">All";
		foreach($filterdata as $id=>$name){
			//debug_string("id $id",$name);
	        $optionname = $name;
	        $optionvalue = $id;
	        $filteroption .= "<option value=\"".$optionvalue."\"";
	        if($listparams['filtervalue']==$id)$filteroption .= " selected ";
	        $filteroption .= ">".$optionname;
	    }
	    $filteroption .= '</select>';
	 $filteroption .= '<input type="hidden" name="first" value="0">';
	}
//create sort pulldown menu

    $sortoption = "&nbsp;&nbsp;<b>Sort by:</b> <select name=\"sort\">";
    for ($i=0;$i<count($fieldinfo);$i++){
        $sortoption .= "<option value=\"".$i."\"";
		if($listparams['sort']==$i)$sortoption .= " selected ";
        $sortoption .=">" . $fieldinfo[$i]['title'];
    }
    $sortoption .= '</select>';
// create heading
    $headings = "";
    for ($i=0;$i<count($fieldinfo);$i++){
        $headings .= "<td><center><b><a href=\"$url&sort=" . $i . "\">" . $fieldinfo[$i]['title'] . "</b></font></a></td>";
    }

//create page length pulldown menu
    $pageoption =  "&nbsp;&nbsp;<b>Page Size:</b> <select name=\"page\">";
	foreach ($PageLengths as $index => $lines){
        $pageoption .= "<option value=\"".$index."\"";
		if($listparams['page']==$index)$pageoption .= " selected ";
        $pageoption .= ">" . $lines;
	}

    $pageoption .= '</select>';
//TODO gamesname and typename are sometimes undefined so they need to be isset checked
    $usermayedit =  true;
    // create record list html
    $datalist = "";
    for($i=$firstline;$i<=$lastline;$i++){
        if (fmod($i,2)==0)$class=" class=\"odd\" ";
       	else $class=" class=\"even\" ";
        // modify the record information before we try to output it (hey, that's cheating!)
        //$records[$i]['rawname'] = $records[$i]['gamesname'];
		if($records[$i][$editfield]=="")$records[$i][$editfield]="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        $records[$i][$editfield] = "</center><a href=\"$self?$actionkeyword=$editname&$editindexkeyword=" . $records[$i][$editindexfield] . "\">" . $records[$i][$editfield]. "</a>";
        if ($usermayedit)$records[$i]['gamesname'] .= "<font size=-2><i>Edit</font></i></a>";
        //$records[$i]['gamesname'] .= "</i></a>";
        /*if (isset($records[$i]['description'])){
            $records[$i]['description'] = substr($records[$i]['description'],0,60);
        } else {
            $records[$i]['description'] = "&nbsp";
        }
		*/
        // now step through the fields and output information
        $datalist .= "<tr $class>\n";
        for($j=0;$j<count($fieldinfo);$j++){
            if(!isset( $records[$i][$fieldinfo[$j]['field']])){
                $records[$i][$fieldinfo[$j]['field']]="&nbsp;";
            }
            $datalist .= "\t<td>" . $records[$i][$fieldinfo[$j]['field']] . "&nbsp;</td>\n";
        }
        if($records[$i]['typename']=="Raw"){
            $datalist .= "\t<td><a href=\"http://clusty.com/search?tb=firefox-1.0.3&query=".$records[$i]['rawname']."\"><font size=-2><i>Web Search</i></font></a></td>";
        }
        $datalist .= "</tr>\n\n";
    } //for($i)
//debug_string("actionkeyword",$actionkeyword);
//debug_string("listname",$listname);
echo <<< EOF
<center><h1>$mastertitle Display Page</h1></center>
<div class="listmenu">
$menu
</div>

<center><h2>Display Settings</h2></center>

 <div class="listpagecontrols">
<center> <FORM action="$self" method="get">
 <input type="hidden" name="$actionkeyword" value="$listname">
$sortoption
$filteroption
$pageoption
 <input type="submit" value="Display">
 </form>
 </div>
</center>
<center>
<div class="listsubtitle">
	<center><font size=+2><b>$mastertitle List </b></font></center>
	<center><font size=+1> <i>($recordcount lines)</i></font></h2></center>
</div>
</center>
<center>
<div class="listpagemenu">
$alphamenu<br>
<center>$firsthtml &nbsp; &nbsp; &nbsp; &nbsp; $prevhtml &nbsp; &nbsp; $pagemenu &nbsp; &nbsp; $nexthtml&nbsp; &nbsp; &nbsp; &nbsp; $lasthtml</center>
</div>
</center>
<center><table border=1>
    <tr>
        $headings
    </tr>
    $datalist

</table>

</center>
<center>$firsthtml &nbsp; &nbsp; &nbsp; &nbsp; $prevhtml &nbsp; &nbsp; &nbsp; &nbsp; $nexthtml&nbsp; &nbsp; &nbsp; &nbsp; $lasthtml</center>
<br><br>
EOF;
}
function alphalist($companies){
    $alpha=array();
    for ($i=count($companies)-1;$i>=0;$i--){
        $name=ucfirst($companies[$i]['name']);

        $initial = substr($name,0,1);
        $alpha[$initial] = $i;
    }
    asort($alpha);

    $alphamenu = "";
    foreach ($alpha as $initial=>$count){
        $alphamenu .= "<a href=\"$url&first=$count\">$initial</a> ";
    }
    return($alphamenu);
}

/** 
 * 
 */
function ParseAddRecord($link,$tablename,$params,$defaults=array()){
	// set up empty lists, start as first
	$valuelist = "";
	$fieldlist = "";
	$first = true;
	// step through defaulst and add them to params as necessary
	foreach($defaults as $name=>$value){
		if(!isset($params[$name])) $params[$name]=$value;
	}

	// step through params and build sql field list and valuelist for insert
	foreach($params as $name=>$value){ 
		if($name=="mode")continue;//skip mode param
		// after first time we need commas for the SQL statement 
		if (!$first){
			$valuelist.=",";
			$fieldlist.=",";
		} else {
			$first =false;
		}
		//add the name/value pair to the value list and field list for SQL statement
		$valuelist .= "'".$value."'";
		$fieldlist .= $name;
	}//foreach

	// create insert statement
	$sql = "insert $tablename ($fieldlist) values ($valuelist)";
	//	do it. 
	mysql_insert($link,$sql,true);
}//function Parse...

/** 
 * 
 */
function AddRecord($link,$tablename,$datanames,$mode){
	$fieldnames = array_keys($datanames);
	$result .= "<center><h2>Add Record</h2></center>\n";
	$result .= " <FORM action=\"index.php\" method=\"post\">\n";
	$result .= " <input type=\"hidden\" name=\"mode\" value=\"$mode\">\n";
	$result .= " <input type=\"submit\" value=\"Add\">\n";
	$result .= " <table border=0>\n";

	foreach($datanames as $name=>$title){ 
			$result .= " <tr><td><b>$title</b></td><td> <input type=\"text\" name=\"$name\" size=\"50\"></td></tr>\n";
	}// foreach
	$result .= " </table>\n";
	$result .= " <input type=\"submit\" value=\"Add\">\n";
	$result .= " </form>\n";
	return $result;
}


/** 
 * 
 * $link - a live link to a mysql database
 * $tablename - the name of the table we're editing
 * $idname - field name of the record id
 * $params - get/put params from the web field
 */
function ParseEditRecord($link,$tablename,$idname,$params,$defaults=array()){
	//debug_string("ParseEditRecord($tablename,$idname)");
	$valuelist = "";
	$first = true;
	// step through defaulst and add them to params as necessary
	foreach($defaults as $name=>$value){
		if(!isset($params[$name])) $params[$name]=$value;
	}
	foreach($params as $name=>$value){ 
		if($name=="mode")continue;//skip mode param // TODO: change this to a variable
		if($name==$idname){
			$recordid=$value;
			continue;
		}//if
		if (!$first) $valuelist.=",";
		else $first =false;
		$valuelist .= $name."='".$value."'";
	}//foreach
	$sql = "update $tablename set $valuelist where $idname='$recordid'";
	mysql_update($link,$sql,true);
}//function Parse...

/** 
 * EditRecord - returns a form to edit a record
 * This routine creates a string that contains a form that will allow the user to edit the data in the record 
 * and then pass the data back to the calling program via post;
 * that program must look for mode=$mode in the post data 
 * and then pass that data on to the ParseEditRecord() routine so the data can be updated. 
 * $link - connection to the mysql database 
 * $tablename - name of the table we're getting the record from. 
 * $idname - field name of the unique identifiyer of this records 
 * $recordid - id value of the record we're to edit
 * $datanames - associative array: the keys are the names of fields in the database, the data are the titles you want shown in the form
 * $mode - what to set the mode variable to get to the code to parse the results
 */
function EditRecord($link,$tablename,$idname,$recordid,$datanames,$mode){
    $self=$_SERVER['PHP_SELF'];
	//debug_string("Editrecord()");
	$fieldnames = array_keys($datanames);
	$record = MYSQLComplexSelect($link,$fieldnames,array($tablename),array($idname."='".$recordid."' "),array(),0);
	$record = $record[0];
	$result .= "<center><h2>Edit Record</h2></center>\n";
	$result .= " <FORM action=\"$self\" method=\"post\">\n";
	$result .= " <input type=\"hidden\" name=\"mode\" value=\"$mode\">\n";
	$result .= " <input type=\"hidden\" name=\"$idname\" value=\"$recordid\">\n";
	$result .= " <input type=\"submit\" value=\"Update\">\n";
	$result .= " <table border=0>\n";

	foreach($record as $name=>$default){ 
		$title = $datanames[$name];
		$size = strlen($default)*2;
		if ($size<10)$size = 10;
		if ($size<50){
			$result .= " <tr><td><b>$title</b></td><td> <input type=\"text\" name=\"$name\" value=\"$default\" size=\"$size\"></td></tr>\n";
		} else {
			$result .= " <tr><td valign=top><b>$title&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td><td> <textarea name=\"$name\" rows=\"8\" cols=\"80\">$default</textarea></td></tr>\n";
		}//else
	}// foreach
	$result .= " </table>\n";
	$result .= " <input type=\"submit\" value=\"Update\">\n";
	$result .= " </form>\n";
	return $result;
}
/**
 * createmenu
 * This function creates a form menu based the values in a database table. 
 * There are two fields in the table that are relevant: the index field and the name field
 * $link - a valid connection to a database
 * $table - the name of the table 
 * $namefld - the field that has the names that will appear in the menu -- this willb e the name
 * $indexfld - the unique unary index to the table; this will be the value (well, x+this number is the value)
 * $selected - optional param to identify which entry is shown by default; this number must match the value in the indexfld
*/
function createmenu($link,$table,$namefld,$indexfld,$selected=-1){
    //debug_string("createmenu(link,$table,$namefld,$indexfld)");
    $sql = "select $namefld,$indexfld from $table order by $namefld";
    $data = MYSQLGetData($link,$sql);
    $menu = "<select name=\"$table\">";
    foreach ($data as $rec){
        $index=$rec[$indexfld];
        $name = $rec[$namefld];
        $menu .= '<option ';
		if($selected == $index)$menu .=" selected ";
        $menu .='value="x'.$index.'">'.$name.'</option>';
    }
    $menu.="</select>";
    return $menu;
}


/* 
 * $Log: list.php,v $
 * Revision 1.20  2007/12/04 06:16:50  dmenconi
 * moved menu below title
 *
 * Revision 1.19  2007/12/04 05:54:57  dmenconi
 * more css class tags added
 *
 * Revision 1.18  2007/12/03 20:09:35  dmenconi
 * fixed a bug with the paging menu going wayward
 *
 * Revision 1.17  2007/12/03 19:18:24  dmenconi
 * add odd and even classes to the list
 *
 * Revision 1.16  2007/12/03 01:05:41  dmenconi
 * now with CSS
 *
 * Revision 1.15  2007/12/02 04:44:16  dmenconi
 * added a description of a crucial field for filtering (the filteridfield)
 *
 * Revision 1.14  2007/10/08 15:44:01  dmenconi
 * added code to parseedit and parseadd to handle defaults
 *
 * Revision 1.13  2007/07/10 15:51:11  dmenconi
 * made display pulldown menus persistent & removed debug stuff
 *
 * Revision 1.12  2007/07/09 06:45:33  dmenconi
 * added to createmenu the ability to have a default
 *
 * Revision 1.11  2007/06/24 00:49:14  dmenconi
 * added a link to the no record page; fixed a naming problem
 *
 * Revision 1.10  2007/06/24 00:00:18  dmenconi
 * check point
 *
 * Revision 1.9  2007/06/23 23:39:24  dmenconi
 * fixed zero record problem, added menus
 *
 * Revision 1.8  2007/06/23 23:10:32  dmenconi
 * checkpoint
 *
 * Revision 1.7  2007/06/12 21:44:28  dmenconi
 * fixed numbering problem (the count was defined as count($records)-1 -- go figure!)
 *
 * Revision 1.6  2007/06/12 19:58:04  dmenconi
 * removed another debug statement
 *
 * Revision 1.5  2007/06/12 18:28:43  dmenconi
 * fixed case where filed we put the edit link has no characters in it.
 *
 * Revision 1.4  2007/06/12 00:42:20  dmenconi
 * removed all comments
 *
 * Revision 1.3  2007/06/12 00:36:05  dmenconi
 * removed $type from list.php
 *
 * Revision 1.2  2007/06/10 16:00:17  dmenconi
 * removed all debug_ calls
 *
 * Revision 1.1  2007/05/11 15:51:31  dmenconi
 * Initial revision
 *
 */
?>
