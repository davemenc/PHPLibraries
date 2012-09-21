<?php
/* $Id: debug.php,v 1.7 2007/05/11 15:52:12 dmenconi Exp $ */

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
	$debug_flag = 0;
	$log_flag = 0;
	function debug_on(){
		global $debug_flag;
		$oldflag=$debug_flag;
		$debug_flag = 1;
		return $oldflag;
	}
	function debug_off(){
		global $debug_flag;
		$oldflag=$debug_flag;
		$debug_flag = 0;
		return $oldflag;
	}
	function debug_set($value){
		global $debug_flag;
		$oldflag=$debug_flag;
		$debug_flag = $value;
		return $oldflag;
	}
	function log_on(){
		global $log_flag;
		$oldflag=$log_flag;
		$log_flag=1;
		return $oldflag;
	}
	function log_off(){
		global $log_flag;
		$oldflag=$log_flag;
		$log_flag=0;
		return $oldflag;
	}
	function log_set($value){
		global $log_flag;
		$oldflag=$log_flag;
		$log_flag = $value;
		return $oldflag;
	}
	function clear_log(){
		global $log_flag;
		if(1==$log_flag){
			$fout=fopen("log.txt","wt");
			fwrite($fout,"___");
			fwrite($fout,date("Y-m-d H:i"));
			fwrite($fout,"___\n");
			fflush($fout);
			fclose($fout);
		}
	}
    function debug_string($name,$string=NULL){
        global $debug_flag, $log_flag;
        if(1==$debug_flag){
            if(!isset($string)){
                print "*".$name."<br>\n";
            }else {
                print "*".$name.": ";
				print $string."<br>\n";
            }
        }
		if (1==$log_flag){
			$fout=fopen("log.txt","at");
			fwrite($fout,"===");
			fwrite($fout,date("Y-m-d H:i"));
			fwrite($fout," ===\n");
			if(!isset($string)){
				fwrite($fout,"*");
				fwrite($fout,$name);
				fwrite($fout,":\n");
			}else{
				fwrite($fout,"*");
				fwrite($fout,$name);
				fwrite($fout,": ");
				fwrite($fout,$string);
				fwrite($fout,"\n");
			}
			fflush($fout);
			fclose($fout);
		}
    }
function debug_params($params){
	$fout=fopen("params.txt","at");
	foreach($params as $key=>$value){
		fwrite($fout,$key);
		fwrite($fout,"\n");
	}
	fflush($fout);
	fclose($fout);
}
	
    function debug_array($name,$array){
        global $debug_flag, $log_flag;
        if (1==$debug_flag){
            print "<br>*".$name.":<br>";
            print_r($array);
            print "<br><br>";
        }
		if(1==$log_flag){
			$fout=fopen("log.txt","at");
			fwrite($fout,"---");
			fwrite($fout,date("Y-m-d H:i"));
			fwrite($fout," ---\n");
			fwrite($fout,"*");
			fwrite($fout,$name);
			fwrite($fout,":\nArray (");
			fflush($fout);
			if(isset($array)){
				foreach($array as $key=>$value){
					fwrite($fout,"[");
					fwrite($fout,$key);
					fwrite($fout,"]=>");
					fwrite($fout,$value);
				}
			}
			fwrite($fout,")\n");
			fflush($fout);
			fclose($fout);
		}
    }
	function debug_SERVER(){
		debug_array("Global Server Array",$_SERVER);
	}
/* 
 * $Log: debug.php,v $
 * Revision 1.7  2007/05/11 15:52:12  dmenconi
 * added license information
 *
 * Revision 1.6  2005/06/20 14:45:11  dave
 * remerge from what
 * added function debug_params() which writes the keys of an array out to standard out
 *
 * Revision 1.7  2005/03/26 07:45:44  dave
 * fixed a minor bug
 *
 * Revision 1.6  2005/03/12 05:58:10  dave
 * fixed itemno (was itemnum in some places)
 * fixed spaceno (was SpaceID in some places)
 * removed all debug stuff
 *
 * Revision 1.5  2005/03/02 01:32:05  dave
 * finished upload code
 *
 * Revision 1.4  2005/02/18 22:16:41  dave
 * made some minor tweaks to the debug_array routine
 *
 * Revision 1.3  2005/02/16 03:50:17  dave
 * added $LOG$ and $ID$ to file
 *
 */
?>
