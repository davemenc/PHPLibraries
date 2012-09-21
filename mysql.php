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
 /* $Id: mysql.php,v 1.13 2007/12/03 01:10:04 dmenconi Exp $ */
//include_once("config.php");
//include_once("../library/miscfunc.php");

/***********************************************************************/
//######################################################################

/**
 * Tells you what the version of this file is
 * 
 * @return  string  a version string
 * @author Dave Menconi <whatchamall@menconi.com>
 */
function MYSQL_Func_Version(){
	return("$Id: mysql.php,v 1.13 2007/12/03 01:10:04 dmenconi Exp $");
}
// Global Mysql connection functions

function make_mysql_connect($dbhost,$dbuser,$dbpass,$dbname){
    $link = mysql_connect($dbhost, $dbuser, $dbpass,true) or die(mysql_error());
	//debug_string("made connection",$link);
    mysql_select_db($dbname,$link);
    return $link;
}
function break_mysql_connect(&$link){
    mysql_close($link);
	$link=NULL;
//debug_string("broke the connection",$link);
}


function db_connection(){
 	global $dbname, $tableprefix, $dbuser, $dbpass, $dbhost,$link;
	// Open the connection
	$link = mysql_connect($dbhost, $dbuser, $dbpass) or errd("MySQL Error!","Connection MySQL server : $dbhost failed!");
	// pick the database
	mysql_select_db($dbname,$link);
}
// Disconnect the DB
function db_disconnect() {
	//close the connect
	global $link;
	mysql_close($link);
	$link=NULL;
}

/**********************************************************
 * MYSQLSimpleSelect()
 * $link: a connection to the database
 * $tablename: the name of a table in that database
 * This routine will return an array of associative arrays
 * where each ass. array is a line from the database.
 * You can't limit the fields or do a where or order it; 
 * it's just simple. 
 * "Get it all and let PHP sort it out"
 *********************************************************/
function MYSQLSimpleSelect(&$link,$tablename){
    $sql = "select * from $tablename";
    //debug_string("Simple Select",$sql);
	mysql_select($link,$sql,$die=true);
    //$result = mysql_query($sql,$link) or die(mysql_error());
    $num_rows = mysql_num_rows($result);
	for($i=0;$i<$num_rows;$i++){
        $data[]=mysql_fetch_array($result,MYSQL_ASSOC);
	}
	return $data;
}
/********************************************************
 * MYSQLSimpleList()
 * $link: a connection to the database
 * $tablename: the name of a table in that database
 * $fieldname: a column in that table
 * This routine will return a simple array with all the values
 * of that column in that table.
 * This is useful to get a list of categories or games or whatever.
 *************************************************************/
function MYSQLSimpleList(&$link,$tablename,$fieldname){
    $sql = "select $fieldname from $tablename";
    //debug_string("Simple List",$sql);
	mysql_select($link,$sql,$die=true);
    //$result = mysql_query($sql,$link) or die(mysql_error());
    $num_rows = mysql_num_rows($result);
	for($i=0;$i<$num_rows;$i++){
        $onedatum=mysql_fetch_array($result,MYSQL_NUM);
		$data[]=$onedatum[0];
	}
	return $data;
}
/**
 * Gets a list of names and descriptions from the database
 * 
 * Various tables have a list of possible stuff that is created and maintained by the system. For example
 * category lists or store types. As much as possible we've tried to avoid defining things in advance.
 * 
 * @param  resource  $link  attached to an open database
 * @param  string  $tablename  the name of the table from which the list will come
 * @param  string  $fieldnames  a comma separated list of fields
 * @param  string  $clause  where clause
 * @param  bool  $debug  turns on or off the debug code
 * @return datatype  description
 * @author Dave Menconi <whatchamall@menconi.com>
 */
function MYSQLGetList(&$link,$tablename,$fieldnames,$clause="",$debug=0){
	if($debug){
		$oldflag=debug_on();
	}else{
		$oldflag = debug_off();
	}
	if (isset($clause)) $sql = "select $fieldnames from $tablename where $clause";
	else $sql = "select $fieldnames from $tablename";
    //debug_string("Get List select",$sql);
	mysql_select($link,$sql,$die=true);
    //$result = mysql_query($sql,$link) or die(mysql_error());
    $num_rows = mysql_num_rows($result);
    for($i=0;$i<$num_rows;$i++){
        $onedatum=mysql_fetch_array($result,MYSQL_NUM);
		for($j=0;$j<count($onedatum);$j++){
			$data[$j][]=$onedatum[$j];
		}
        //$data[0][]=$onedatum[0];
        //$data[1][]=$onedatum[1];
    }
	debug_set($oldflag);
    return $data;
}
	

/********************************************************************
 * MYSQLComplexSelect()
 * Does a complicated select from the mysql database and returns an array 
 * with the data. 
 * $link: a link to the database
 * $fieldnames: An array of field names
 * $tablenames: An array of table names
 * $where: An array of where clauses (NOT including the word "where")
 * $Order: An array of order by clauses
 *
 * The basic idea is that you can create these arrays based on complicated code. For example, you might have two or three different flags 
 * to check to constuct the where clause (include hidden items or not, show active items, show all or just one particular set, etc)
 * and you can check these one by one, adding clauses to the $where array in any order. 
 * 
 * The routine doesn't deal with "OR" at the moment; all the elements in the $where array are separated by "AND"
 * The routine doesn't handle subselects, groupby or limit clauses either. 
 * These shortcomings can, in some cases, be overcome by adding he clauses you want to the appropriate array. 
 * For example $where[]="group by foo"; if done last might get you the grouping you want or $order[]="limit=1"
 * 
 * The routine returns an array of arrays of results. The actual data for each record in the database is a associative 
 * array with the field names from the database.
 ********************************************************************/
function MYSQLComplexSelect(&$link,$fieldnames=array("*"),$tablenames=array(),$where=array(),$order=array(),$debug=0){
	if($debug){
		$oldflag=debug_on();
	}else{
		$oldflag = debug_off();
	}
	//$oldflag=debug_on();
	//$oldflag=log_off();
	if ($debug==1){
		debug_string("MYSQLComplexSelect()");
		debug_array("fieldnames",$fieldnames);
		debug_array("tablenames",$tablenames);
		debug_array("where",$where);
		debug_array("order",$order);
	}

	//calculate field list
	if(count($fieldnames)>0){
		$fieldlist=$fieldnames[0];
		for ($i=1;$i<count($fieldnames);$i++){
			$fieldlist .= "," . $fieldnames[$i];
		}
	}else{
		$fieldlist="*";
	}
	//debug_string("<hr> <b>FIELDLIST</b>");
	//debug_array("fieldnames",$fieldnames);
	//debug_string("fieldlist",$fieldlist);

	// calculate table list
	$tablelist=$tablenames[0];
	for($i=1;$i<count($tablenames);$i++){
		$tablelist .= "," . $tablenames[$i];	
	}
	//debug_string("<hr> <b>TABLELIST</b>");
	//debug_array("tablenames",$tablenames);
	//debug_string("tablelist",$tablelist);

	// calculate where list
	if (count($where)>0){
		$wherelist="where ".$where[0];
		for($i=1;$i<count($where);$i++){
			$wherelist .= " and " . $where[$i];
		}
	}else{
		$wherelist="";
	}
	//debug_string("<hr> <b>WHERELIST</b>");
	//debug_array("where",$where);
	//debug_string("wherelist",$wherelist);

	// calculate order list
	//debug_string("order count",count($order));
	if (count($order)>0){
		$orderlist="order by ".$order[0];
		for($i=1;$i<count($order);$i++){
			$orderlist .= " , " . $order[$i];
		}
	}else{
		$orderlist="";
	}
	//debug_string("<hr> <b>ORDERLIST</b>");
	//debug_array("order",$order);
	//debug_string("orderlist",$orderlist);
    $sql = "select $fieldlist  from $tablelist $wherelist $orderlist";
    if ($debug==2)debug_string("Complex Select",$sql);
	$result = mysql_select ($link,$sql,true);
    //$result = mysql_query($sql,$link) or die(mysql_error());
    $num_rows = mysql_num_rows($result);
	$data=array();
	for($i=0;$i<$num_rows;$i++){
        $data[]=mysql_fetch_array($result,MYSQL_ASSOC);
	}
	debug_set($oldflag);
	//log_set($oldflag);
	return $data;
}
/*************************************
****************************************/
function MYSQLGetData(&$link,$sqlcommand){
	//debug_string("MYSQLGetData($sqlcommand)");
	$result = mysql_select ($link,$sqlcommand,true);
//if ($result)debug_string("good");
//else debug_string("bad");
    $num_rows = mysql_num_rows($result);
//debug_string("mysql rows",$num_rows);
	$data=array();
	for($i=0;$i<$num_rows;$i++){
        $data[]=mysql_fetch_array($result,MYSQL_ASSOC);
	}
	return $data;
}

/*************************************
These routines execute a command but do not get any data. 
They return the result of the command. 
THESE ARE INTERNAL ROUTINES, NOT TO BE USED OUTSIDE THIS FILE
****************************************/
function do_mysql($link,$sql,$die=false){
	if($die){	
    	$result = mysql_query($sql,$link) or die(mysql_error());
	} else {
    	$result = mysql_query($sql,$link);
	}
	return $result;
}
function mysql_delete($link,$sql,$die=false){
		//debug_string("mysql_delete($sql)");
	$result = do_mysql($link,$sql,$die);
	if ($result)$success=" (worked)";
	else $success=" (failed)";
	LogStats($link,$sql.$success,"Delete");
	return $result;
}
function mysql_insert($link,$sql,$die=false){
		//debug_string("mysql_insert($sql)");
	$result = do_mysql($link,$sql,$die);
	if ($result)$success=" (worked)";
	else $success=" (failed)";
	LogStats($link,$sql.$success,"Insert");
	return $result;
}
function mysql_select($link,$sql,$die=false){
		//debug_string("mysql_select($sql)");
	$result = do_mysql($link,$sql,$die);
	if ($result)$success=" (worked)";
	else $success=" (failed)";
	//LogStats($link,$sql.$success,"Select");
	return $result;
}
function mysql_update($link,$sql,$die=false){
		//debug_string("mysql_update($sql)");
	$result = do_mysql($link,$sql,$die);
	if ($result)$success=" (worked)";
	else $success=" (failed)";
	LogStats($link,$sql.$success,"Update");
	return $result;
}
/*************************************
****************************************/
/* 
 * $Log: mysql.php,v $
 * Revision 1.13  2007/12/03 01:10:04  dmenconi
 * checkpoint
 *
 * Revision 1.12  2007/06/15 23:12:44  dmenconi
 * added a delete function
 *
 * Revision 1.11  2007/05/11 15:52:12  dmenconi
 * added license information
 *
 * Revision 1.10  2007/03/31 21:52:24  dmenconi
 * fixed failing debug=2 case
 *
 * Revision 1.9  2007/03/29 15:06:47  dmenconi
 * fixed possible bug in MYSQLGetData where false was mispelled
 *
 * Revision 1.8  2007/03/10 16:31:39  dmenconi
 * added functionality to log actions to database (like inserts and updates)
 *
 * Revision 1.2  2007/02/24 23:06:53  dave
 * changed mysql_query to the new insert and update routines
 *
 * Revision 1.1  2007/02/24 21:12:15  dave
 * Initial revision
 *
 * Revision 1.7  2007/02/06 15:29:14  dmenconi
 * removed some debugs
 *
 * Revision 1.6  2007/02/06 06:08:48  dmenconi
 * change to connection routines
 *
 * Revision 1.5  2005/06/20 14:45:11  dave
 * remerge from what
 * added description comments to all the functions
 * whole file format prevents diff
 *
 * Revision 1.12  2005/06/08 22:03:26  dave
 * very minor changes
 *
 * Revision 1.11  2005/05/01 01:49:56  dave
 * changed the complex select debug to have three values 2 is just the select statement, 1 is everything and 0 if off
 *
 * Revision 1.10  2005/04/16 20:59:23  dave
 * added more comments
 * change GetList to get as many columns as there are...
 *
 * Revision 1.9  2005/03/30 21:51:20  dave
 * fixed bug in mysqlgetlist where it would try to pass mysql an empty where clause
 *
 * Revision 1.8  2005/03/26 08:29:16  dave
 * added debug flag to MYSQLGETLIST
 *
 * Revision 1.7  2005/03/10 04:26:32  dave
 * added a flg to the complex select to allow for easier debugging
 *
 * Revision 1.6  2005/03/03 04:11:41  dave
 * added category summary page
 *
 * Revision 1.5  2005/03/02 01:32:05  dave
 * finished upload code
 *
 * Revision 1.4  2005/02/18 22:16:41  dave
 * mostly a ton of debug stuff
 *
 * Revision 1.3  2005/02/16 20:40:33  dave
 * removed debug statements
 *
 * Revision 1.2  2005/02/16 03:44:07  dave
 * added several new routines to handle getting certain kinds of data
 * complex select
 * simple select
 * getsimple list
 * get list
 *
 */
?>
