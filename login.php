<?PHP
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
/* $Id: login.php,v 1.4 2007/12/03 01:10:04 dmenconi Exp $ */
include_once "../library/debug.php";
include_once "../library/mysql.php";
include_once "../library/miscfunc.php";
include_once "../library/htmlfuncs.php";
/***********************************
 ********* FUNCTIONS **************
 **********************************/
//------------------------------------

function get_userno($link,$cookiename="login"){
    //debug_string("get_userno");
    $username = get_username($cookiename);
	if (""==$username) return -1;
	$user = MYSQLComplexSelect($link,array("*"),array("user"),array("username='$username' limit 1"),array(),0);
    $num_rows = count($user);
    if (0==count($user)){
        return -1;
    }
	$userno = $user[0]['id'];
	return $userno;
}
//------------------------------------
//$link: live link to db
//$cookiename: the cookie we're looking for
// Gets the username out of the cookie 
// looks it up in the database
// returns type or -1 if there is no such user
function get_usertype($link,$cookiename="login"){//get the type of user
	//debug_string("get_usertype");
    $username = get_username($cookiename);
	if (""==$username) return -1;
	$user = MYSQLComplexSelect($link,array("*"),array("user"),array("username='$username' limit 1"),array(),0);
	if(count($user)<1) return -1;
	$type = $user[0]['type'];
    return $type;
}

//------------------------------------
function get_userlist($link){//get a list of users for a particular application
    //debug_string("get_userlist");
	$users = MYSQLComplexSelect($link,array("*"),array("user"),array(),array(),0);
    return $users;
}

// parameters
//------------------------------------
function displayactivation($title,$color,$mode){
}
//------------------------------------
function activate(){
}
//------------------------------------
// displaylogin
// $title: Title of login screen
// $color: Background color of login screen
// $mode: mode value to pass back through form to original application
// $message: display this to explain why we're here (eg "bad password")
// $loginapp: application that will process login info
// $reg: display registration section? 
// $PARAMS: values of last put or get

function displaylogin($title,$color,$mode,$message="",$loginapp="index.php",$reg=false,$PARAMS=""){
	global $version,$lastmodified;	
	if ($PARAMS!=""){
		if(isset($PARAMS['name'])) $name=$PARAMS['name'];
		if(isset($PARAMS['email'])) $email=$PARAMS['email'];
		if(isset($PARAMS['name'])) $username=$PARAMS['username'];
		if(isset($PARAMS['userpass'])) $userpass=$PARAMS['userpass'];
		if(isset($PARAMS['password2'])) $password2=$PARAMS['password2']; 
	}
	//debug_string("displaylogin()");
	Display_Generic_Header($title . " Login",$color);
	
	echo <<< EOF
	<center>|  <a href="index.php?mode=list">Return to $title </a> |</center>
	<br><br>
	<br><br>
<center>
<table >
<tr>
<td valign="top">

	<font color=red size=+1>$message</font>
	<br><br>
	<h2>Enter Username & Password</h2>
	<h3></h3>
	<form method="post" action="$loginapp">
	<table><tr><td><b>Username: </b></td> <td><input type="text" name="username"><br></td></tr>
	<tr><td><b>Password: </b></td><td>	<input type="password" name="password"><br></td></tr>
	</table>	<input type="submit"  value="Submit"><br>
	<input type="hidden" name="mode" value="$mode">
	</form>
</td>
<td align="center">
<b>|<br>|<br>|<br>|<br>|<br> &nbsp;&nbsp;&nbsp;&nbsp;- OR - &nbsp;&nbsp;&nbsp;&nbsp;<br>|<br>|<br>|<br>|<br>|<br></b>
</td>
<td>

<center><h2>Register As New User</h2>
<h3>It's Free!</h3>
<!-- ' -->
<b>All fields are required.</br>
<b>
<form method="post" action="index.php">
<table >
<tr><td><b>Your Real Name: </b></td><td>	<input type="text" name="name" value="$name"><br></td></tr>
<tr><td><b>Email: </b></td><td>	<input type="text" name="email" value="$email"><br></td></tr>
<tr><td><b>Username: </b></td> <td><input type="text" name="username" value="$username"><br></td></tr>
<tr><td><b>Password: </b></td><td>	<input type="password" name="userpass" ><br></td></tr>
<tr><td><b>Confirm Password: </b></td><td>	<input type="password" name="password2"><br></td></tr>
</table>	<input type="submit"  value="Submit"><br>
<input type="hidden" name="mode" value="newuser">

</form>
<b>An email will be sent to your email with an activation URL. <br>Your new account will work only <i>after</i> you activate. </b>


</td>
</tr>
</table>
EOF;
    Display_Generic_Footer($version,date("F d Y H:i:s", getlastmod()));
}
//------------------------------------
function parse_newuser($link,$PARAMS){
	$name = $PARAMS['name'];
	$email = $PARAMS['email'];
	$username = $PARAMS['username'];
	$userpass = $PARAMS['userpass'];
	$password2 = $PARAMS['password2'];
	// confirm that all the fields are there
	if ($username=="") return "You must enter a user name.";
	if ($userpass=="") return "You must enter a password";
	if($name=="") return "Your real name is required!";
	if ($email=="") return "Your email address is required.";

	// validate the fields
	if ($userpass!=$password2) return "Password and Confirm Password Do Not Match. Please try again.";
    $users = MYSQLComplexSelect($link,array("*"),array("user"),array("username='".$username."'"),array(),0);
	if (count($users)>0) return "Username already in use; please pick another.";

	// add user to db
	    
		
	$actword = GenerateRandomWord();
	$sql = "insert into user (username,userpass,email,name,temppw,type,actword) values ('".$username."','".$userpass."','".$email."','".$name."','','inactive','".$actword."')";
    //debug_string("sql",$sql);
	mysql_update($link,$sql,$die=true);
    //$result = mysql_query($sql,$link) or die(mysql_error());
		   

}

//------------------------------------
function create_auth_cookie($username, $appexpiry, $appword,$cookiename="login"){
    // create a cookie from the information we have
	//debug_string("create_auth_cookie()");
	if (!isset($username) || ""==$username) return;
    $hashtime = time();
	$cookexp = calc_cookie_expiration($appexpiry,$hashtime);
    $hash = md5($appword.$username.$hashtime.$cookexp);
//debug_string("hash",$hash);
	save_cook_data($username,$hashtime,$hash,$cookexp,$cookiename);
}//create_auth_cookie

// $appword: code word to make hash unique
// $cookexp: 
// $cookiename: the name of the cookie of interest
// $username: username we're looking for; if omitted we'll accept out of the cookie
// returns 
//------------------------------------
function check_auth_cookie($appword,$cookexp=0,$cookiename="login",$username=""){
//debug_string("check_auth_cookie()");
//debug_string("appword",$appword);
//debug_string("cookexp",$cookexp);
//debug_string("cookiename",$cookiename);
//debug_string("username",$username);

	$c_data = get_cook_data($cookiename);// get the cookie data
//debug_array("c_data",$c_data);
    if (!isset($c_data)){// if no data return false
        return false;
    }
//parse out the data in the cookie
	$c_username = $c_data['name'];// get the username
//debug_string("c_username",$c_username);
//debug_string("mark1");
    if (""==$username){$username=$c_username;}//use cookie username if none given
//debug_string("mark2");
	$c_time = $c_data['time'];
	$c_hash = $c_data['hash'];
	$c_expire = $c_data ['expire'];
//check expiration time; delete cookie and return false if expired
//debug_string("c_time",$c_time);
//debug_string("c_hash",$c_hash);
//debug_string("c_expire",$c_expire);
//debug_string("mark3");
	if (0!=$c_expire && $c_expire<time()){
//debug_string("mark4");
		delete_cookie($cookiename);
		return false;
	}
//calc hash
//debug_string("mark5");
    $hash = md5($appword.$username.$c_time.$cookexp);// calculate hash 
//debug_string("hash",$hash);
//debug_string("c_hash",$c_hash);
//debug_string("c_username",$c_username);
//debug_string("username",$username);
// check hash
//debug_string("mark6");
    if(strcmp($c_username,$username)==0 && strcmp($hash,$c_hash)==0 ){
//debug_string("mark7 true");

        return true;
    }else{
//debug_string("mark8 false");
        return false;
    }
//debug_string("mark9");
}

//------------------------------------
// $cookiename: the name of the cookie
// gets the user name from the cookie 
// returns username or "" if there is no cookie
//------------------------------------
function get_username($cookiename="login"){
	//debug_string("get_username()");
	
	$cdata = get_cook_data($cookiename);
    if(isset($cdata['name'])){
        return $cdata['name'];
    }else{
        return "";
    }
}
//------------------------------------
function calc_cookie_expiration($expiry,$time){
	//debug_string('calc_cookie_expiration()');
	if(0==$expiry)$cookexp=0;
	else $cookexp=$time+$expiry;
//debug_string("expiry",$expiry);
//debug_string("cookexp",$cookexp);
//debug_string("time",$time);
	return($cookexp);
}
//------------------------------------
/******************************************
$username: the username the user entered
$pass: the password the user entered
$link: a live link to the database
$magic_word: 
$cookiename: the name we should give the cookie
$admin: does this login require an admin
$title: 
$color: Color of the background of the login screen
$mode: mode value to be passed back to the login application
$loginapp: name of login application
$reg: should login include registration? 
*******************************************/
function GetAuthenticated($username,$pass,$link,$magic_word,$cookiename="login",$admin=false,$expiry=0,$title="Application",$color='#F0F0F0',$mode="login",$loginapp="index.php", $reg=false){
    //check Cookie
    //debug_string("---- GetAuthenticated ----");
//debug_string("username",$username);
//debug_string("pass",$pass);

	$c_data = get_cook_data($cookiename);
//debug_array("cookie data",$c_data);
	$message = "";
//debug_string("----------------------------------------------");
//debug_string("type0",$type);
// check for a valid cookie
	if (check_auth_cookie($magic_word,calc_cookie_expiration($expiry,$c_data['time']),$cookiename)){
		$type = get_usertype($link,$cookiename);
//debug_string("type1",$type);
		$username = get_username($cookiename);
		if('inactive'==$type){//shouldn't have a cookie
//debug_string("type2",$type);
			delete_cookie($cookiename);
			displaylogin($title,$color, $mode,$message="User in cookie is listed as inactive.",$loginapp,$reg);
			exit();
		} else if('admin'==$type){ // the user is the admin so it's good enough no matter what
//debug_string("type3",$type);
			create_auth_cookie($username,$expiry,$magic_word,$cookiename);//reset cookie so it doesn't expire// how can there be a valid $type at this point? 
	} else if((!$admin && 'active'==$type)|| 'admin'==$type){//we don't need an admin so any good cookie is good enough
//debug_string("type4",$type);
			create_auth_cookie($username,$expiry,$magic_word,$cookiename);//reset cookie so it doesn't expire
            return true;
// no valid cookie, no valid type, 
        } else { //it will get here if we've dropped the username out of the cookie or anyother case where get_username fails
//debug_string("type5",$type);
			delete_cookie($cookiename);
			displaylogin($title,$color, $mode,$message="User in cookie has unknown type (cookie removed).",$loginapp,$reg);
			exit();
        }
//debug_string("type6",$type);
		return true;
    } else {
//debug_string("type7",$type);
		//debug_string("check_auth_cookie failed!");
		delete_cookie($cookiename);
	}
    //at this point there isn't a valid cookie
//debug_string("type8",$type);
		//debug_string("check_auth_cookie failed!");

    // check username/password
//debug_string("username",$username);
//debug_string("pass",$pass);
    if(isset($username) && isset($pass)){//we have no cookie but we have a username and password
//debug_string("type9",$type);
    	$users = MYSQLComplexSelect($link,array("*"),array("user"),array("username='".$username."'"),array(),0);
		if (count($users)==0){//no such user
//debug_string("typeA",$type);
			displaylogin($title,$color,$mode,$message="Bad user/password combination.",$loginapp,$reg);
			exit();
		}
//debug_string("typeb",$type);
		$user = $users[0];
		$id =  $user['id'];        
		$userpass =  $user['userpass'];  
		$email =  $user['email'];     
		$name =  $user['name'];      
		$temppw =  $user['temppw'];    
		$tpwexpiry =  $user['tpwexpiry']; 
		$type =  $user['type'];      
		$actword =  $user['actword'];   
		$ts =  $user['ts'];    
		$today = date("Y-m-d");
		if (($pass==$userpass) or ($temppw==$userpass && $tpwexpiry>$today)) { // valid password 
//debug_string("typeC",$type);
		
		/*   
TRUTH TABLE OF USER ACCESS CASES
           $admin
$TYPE   |True|False|
--------------------
admin   | T  |  T  |
--------------------
active  | F  |  T  |
--------------------
inactive| F  |  F  |
--------------------
*/
			if ('inactive'==$type) {//user inactive
//debug_string("typeE",$type);
				displaylogin($title,$color,$mode,$message="User is inactive. Activate user or login as different user.",$loginapp,$reg);
				exit();
			}
			else if ($admin && 'active'==$type){  // action requires admin privs
//debug_string("typeF",$type);
				displaylogin($title,$color,$mode,$message="Action requires user to be admin.",$loginapp,$reg);
				exit();
			} else {// good to go!
//debug_string("typeG",$type);
				create_auth_cookie($username,$expiry,$magic_word,$cookiename);//reset cookie so it doesn't expire
				return true;
			}
		}else{// bad password
//debug_string("typeH",$type);
			displaylogin($title,$color,$mode,$message="Bad user/password combination.",$loginapp,$reg);
			exit();
		}// password
			

    }//we have username and password
    //display password routine
//debug_string("typeI",$type);
	displaylogin($title,$color,$mode,$message="",$loginapp,$reg);
    exit();
}
//------------------------------------
function delete_cookie($cookiename="login"){
	setcookie($cookiename,"",time()-11000);
}

//------------------------------------
function get_cook_data($cookiename="login"){
	if(!isset($_COOKIE[$cookiename])){
		return array();
	}
	//debug_string("get_cook_data()");
	$cookstr = $_COOKIE[$cookiename];
	$datalist = explode ( "+", $cookstr);
	$result['name'] = $datalist[0];
	$result['time'] = $datalist[1];
	$result['hash'] = $datalist[2];
	$result['expire'] = $datalist[3];
	return $result;
}
//------------------------------------
function save_cook_data($name,$hashtime,$hash,$cookexp=0,$cookiename="login"){
	$data = $name."+".$hashtime."+".$hash."+".$cookexp;
	//debug_string("save_cook_data()");
	setcookie($cookiename,$data,$cookexp);
}
//------------------------------------
function Generate_Random_Word($minlen=10,$maxlen=25){
    $charset = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $charsetlen = strlen($charset);
    // generate a length value that various widely but tends to be around 20 characters
	$maxlen = $maxlen-12;
	if ($minlen<1) $minlen=1;
	if ($maxlen<6) $maxlen=6;
    $len=mt_rand($minlen,$maxlen)+mt_rand(0,4)+mt_rand(0,4)+mt_rand(0,4);
    for ($i = 1; $i <= $len; $i++){
        $n = mt_rand(0,$charsetlen-1);
        $word = $word.$charset[$n];
    }
    //debug_string("word",$word);
    return $word;
}

//------------------------------------
function login_util_version(){
    return '$Id: login.php,v 1.4 2007/12/03 01:10:04 dmenconi Exp $';
}
/*  
$Log: login.php,v $
Revision 1.4  2007/12/03 01:10:04  dmenconi
checkpoint

Revision 1.3  2007/05/11 15:52:12  dmenconi
added license information

Revision 1.2  2007/03/10 16:30:53  dmenconi
added code to log logins

Revision 1.2  2007/02/24 23:06:53  dave
changed mysql_query to the new insert and update routines

Revision 1.1  2007/02/24 21:14:12  dave
Initial revision

Revision 1.1  2007/02/06 06:05:05  dmenconi
Initial revision

*/
?>
