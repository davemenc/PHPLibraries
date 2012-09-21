<?PHP
/* $Id: loc_login.php,v 1.12 2008/01/23 03:19:45 dmenconi Exp $ */

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
include_once "../library/debug.php";
include_once "../library/mysql.php";
include_once "../library/miscfunc.php";
include_once "../library/htmlfuncs.php";
//include_once "login.php";
/***********************************
 ********* FUNCTIONS **************
 **********************************/
//------------------------------------

function loc_get_userno($link,$cookiename="login"){
	global $usertable;
    //debug_string("loc_get_userno()");
    $username = loc_get_username($cookiename);
	if (""==$username) return -1;
	$user = MYSQLComplexSelect($link,array("*"),array($usertable),array("username='$username' limit 1"),array(),0);
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
function loc_get_usertype($link,$cookiename="login"){//get the type of user
	global $usertable;
	//debug_string("loc_get_usertype()");
    $username = loc_get_username($cookiename);
	if (""==$username) return -1;
	$user = MYSQLComplexSelect($link,array("*"),array($usertable),array("username='$username' limit 1"),array(),0);
	if(count($user)<1) return -1;
	$type = $user[0]['type'];
    return $type;
}

//------------------------------------
function loc_get_userlist($link){//get a list of users for a particular application
	global $usertable;
    //debug_string("loc_get_userlist()");
	$users = MYSQLComplexSelect($link,array("*"),array($usertable),array(),array(),0);
    return $users;
}

//------------------------------------
function loc_display_activation_failed($title,$color){
	global $version,$lastmodified;	
	//debug_string("loc_display_activation_failed($title,$color)");
	Display_Generic_Header($title . " Activation Failed",$color);
	print "<h1>$title Activation Failed</h2>\n";

	print "For some reason your activation failed. Please try the registration process again.<br> \n";
	print "<center>  <a href=\"index.php\">Register on $title again.</a> </center>\n";
    Display_Generic_Footer($version,date("F d Y H:i:s", getlastmod()));
	exit();
}
//------------------------------------
function loc_display_activation($title,$color){
	global $version,$lastmodified;	
	//debug_string("loc_display_activation($title,$color)");
	Display_Generic_Header($title . " Activation",$color);
	print "<h1>$title Activation</h2>\n";

	print "You have successfully activated you $title account.<br> \n";
	print "<center>  <a href=\"index.php\">Log in to $title </a> </center>\n";
    Display_Generic_Footer($version,date("F d Y H:i:s", getlastmod()));
	exit();
}
//------------------------------------
function loc_activate($link,$PARAMS,$title,$color){
	global $usertable;
	//debug_string("loc_activate()");
	$username = $PARAMS['username'];
	$actword = $PARAMS['act'];	
	
	$user = MYSQLComplexSelect($link,array("*"),array($usertable),array("actword='$actword'","username='$username'"),array(),0);
    if (0==count($user)){
		// activation failed
		loc_display_activation_failed($title,$color);
		exit();
	} 
	$sql = "update $usertable set type='active' where username='$username'";
    //$result = mysql_query($sql,$link) or die(mysql_error());
	mysql_update($link,$sql,$die=true);
	loc_display_activation($title,$color);
	exit();
}
//------------------------------------
// loc_display_login
// $title: Title of login screen
// $color: Background color of login screen
// $mode: mode value to pass back through form to original application
// $message: display this to explain why we're here (eg "bad password")
// $loginapp: application that will process login info
// $reg: display registration section? 
// $PARAMS: values of last put or get

function loc_display_login($title,$color,$mode,$message="",$loginapp="index.php",$reg=false,$PARAMS=""){
	global $version,$lastmodified;	
	//debug_string("loc_display_login()");
	if ($PARAMS!=""){
		if(isset($PARAMS['name'])) $name=$PARAMS['name'];
		if(isset($PARAMS['email'])) $email=$PARAMS['email'];
		if(isset($PARAMS['username'])) $username=$PARAMS['username'];
		if(isset($PARAMS['password'])) $password=$PARAMS['password'];
		if(isset($PARAMS['password2'])) $password2=$PARAMS['password2']; 
	}
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
	<table><tr><td><b>Username: </b></td> <td><input type="text" name="username" value="$username"><br></td></tr>
	<tr><td><b>Password: </b></td><td>	<input type="password" name="password" ><br></td></tr>
	</table>	<input type="submit"  value="Submit"><br>
	<input type="hidden" name="mode" value="$mode">
	</form>
</td>
<table>
EOF;
	if (!$reg){
	} else {
	echo <<< EOF2
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
<tr><td><b>Username: </b></td> <td><input type="text" name="uname" value="$username"><br></td></tr>
<tr><td><b>Password: </b></td><td>	<input type="password" name="userpass" ><br></td></tr>
<tr><td><b>Confirm Password: </b></td><td>	<input type="password" name="password2"><br></td></tr>
</table>	<input type="submit"  value="Submit"><br>
<input type="hidden" name="mode" value="newuser">

</form>
<b>A message will be sent to your email with an activation URL. <br>Your new account will work only <i>after</i> you activate. </b>


</td>
</tr>
</table>
EOF2;
	}
    Display_Generic_Footer($version,date("F d Y H:i:s", getlastmod()));
}
//------------------------------------
function loc_parse_newuser($link,$PARAMS){
	global $usertable;
	//debug_string("loc_parse_newuser()");
	$name = $PARAMS['name'];
	$email = $PARAMS['email'];
	$username = $PARAMS['uname'];
	$userpass = $PARAMS['userpass'];
	$password2 = $PARAMS['password2'];
	// confirm that all the fields are there
	if ($username=="") return "You must enter a user name.";
	if ($userpass=="") return "You must enter a password";
	if($name=="") return "Your real name is required!";
	if ($email=="") return "Your email address is required.";

	// validate the fields
	if ($userpass!=$password2) return "Password and Confirm Password Do Not Match. Please try again.";
    $users = MYSQLComplexSelect($link,array("*"),array($usertable),array("username='".$username."'"),array(),0);
	if (count($users)>0) return "Username already in use; please pick another.";

	// add user to db
	$actword = loc_GenerateRandomWord();
	//$sql = "insert into $usertable (username,userpass,email,name,temppw,type,actword) values ('".$username."','".$userpass."','".$email."','".$name."','','inactive','".$actword."')";
	$sql = "insert into $usertable (username,userpass,email,name,temppw,type,actword) values ('".$username."','".$userpass."','".$email."','".$name."','','active','".$actword."')";
	mysql_insert($link,$sql,$die=true);
    //$result = mysql_query($sql,$link) or die(mysql_error());

// send email to user
	$subject = "Activation Email";
	$URL = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."?act=$actword&username=$username&mode=act";
//debug_string("http_host",$_SERVER['HTTP_HOST']);
//debug_string("URI",$_SERVER['REQUEST_URI']);
	$message = "To activate your account for $username, please go to the following URL: $URL";
//debug_string("subject",$subject);
//debug_string("Url",$URL);
//debug_string("message",$message);
//debug_string("email",$email);
	$success = mail($email, $subject, $message,"FROM:dave@menconi.com" );
// send email to admin
	if (!$success) $status="failed";
	else $status = "succeeded";
//debug_string("status",$status);
//mail("dave@menconi.com","test mail from rabbit","This is the message, I hope it isn't too long","from: dave@menconi.com");
//mail("davemenc@gmail.com","test mail from rabbit","This is the message, I hope it isn't too long");
	mail("dave@menconi.com,davemenc@gmail.com","user registration","User $name ($username) tried to register. We tried to send an email to $email and it $status. $URL","FROM: dave@menconi.com");

	loc_display_newuseradded();
	exit();
}
function loc_display_newuseradded(){
	print "<h1>CONGRATULATIONS</h1>\n";
	print "You have successfully registered. <br>\n";
	print "Please look in your email for an activation message. Go to the URL in that message and you will automatically be activated. <br>\n";
	print "Thank you for registering! <br>\n";
	exit();
}
//------------------------------------
function loc_create_auth_cookie($username, $appexpiry, $appword,$cookiename="login"){
    // create a cookie from the information we have
	//debug_string("loc_create_auth_cookie()");
	if (!isset($username) || ""==$username) return;
    $hashtime = time();
	$cookexp = loc_calc_cookie_expiry($appexpiry,$hashtime);
    $hash = md5($appword.$username.$hashtime.$cookexp);
	loc_save_cookie_data($username,$hashtime,$hash,$cookexp,$cookiename);
}//create_auth_cookie

// $appword: code word to make hash unique
// $cookexp: 
// $cookiename: the name of the cookie of interest
// $username: username we're looking for; if omitted we'll accept out of the cookie
// returns 
//------------------------------------
function loc_check_auth_cookie($appword,$cookexp=0,$cookiename="login",$username=""){
//debug_string("loc_check_auth_cookie()");

	$c_data = loc_get_cookie_data($cookiename);// get the cookie data
    if (!isset($c_data)){// if no data return false
        return false;
    }
//parse out the data in the cookie
	$c_username = $c_data['name'];// get the username
    if (""==$username){$username=$c_username;}//use cookie username if none given
	$c_time = $c_data['time'];
	$c_hash = $c_data['hash'];
	$c_expire = $c_data ['expire'];
//check expiration time; delete cookie and return false if expired
	if (0!=$c_expire && $c_expire<time()){
		loc_delete_cookie($cookiename);
		return false;
	}
//calc hash
    $hash = md5($appword.$username.$c_time.$cookexp);// calculate hash 
// check hash
    if(strcmp($c_username,$username)==0 && strcmp($hash,$c_hash)==0 ){

        return true;
    }else{
        return false;
    }
}
//------------------------------------
// loc_get_username
// $cookiename: the name of the cookie
// gets the user name from the cookie 
// returns username or "" if there is no cookie
//------------------------------------
function loc_get_username($cookiename="login"){
	//debug_string("loc_get_username()");
	
	$cdata = loc_get_cookie_data($cookiename);
    if(isset($cdata['name'])){
        return $cdata['name'];
    }else{
        return "";
    }
}
//------------------------------------
//loc_calc_cookie_expiry($expiry,$time){
//------------------------------------
function loc_calc_cookie_expiry($expiry,$time){
//debug_string('loc_calc_cookie_expiry()');
	if(0==$expiry)$cookexp=0;
	else $cookexp=$time+$expiry;
	return($cookexp);
}
//------------------------------------
/******************************************
loc_GetAuthenticated()
$PARAMS params from _GET and _PUT 
$link: a live link to the database
$magic_word: 
$cookiename: the name we should give the cookie
$admin: does this login require an admin
$title: 
$color: Color of the background of the login screen
$loginapp: name of login application
$reg: should login include registration? 
PARAMS: Mode=login or newuser

*******************************************/
function loc_GetAuthenticated($PARAMS,$link,$magic_word,$cookiename="login",$admin=false,$expiry=0,$title="Application",$color='#F0F0F0',$loginapp="index.php", $reg=false){
	global $usertable;
//debug_string(usertable,$usertable );
	if (!isset($usertable))$usertable ="user";
//debug_string(usertable,$usertable );
    //check Cookie
    //debug_string("---- loc_GetAuthenticated ----");
//debug_array("loc_GetAut PARAM",$PARAMS);
	$username = $PARAMS['username'];
	$pass = $PARAMS['password'];
	$mode = $PARAMS['mode'];
	if($mode=="")$mode="login";
	if($mode=="newuser"){	
		$message = loc_parse_newuser($link,$PARAMS);
		loc_display_login($title,$color, $mode,$message,$loginapp,$reg);
		exit();
	}
	if($mode=="act"){
		loc_activate($link,$PARAMS,$title,$color);
	}	

	$c_data = loc_get_cookie_data($cookiename);
		//debug_string("get cookie");
	$message = "";
	// check for a valid cookie
	if (loc_check_auth_cookie($magic_word,loc_calc_cookie_expiry($expiry,$c_data['time']),$cookiename)){
		//debug_string("good cookie");
		$type = loc_get_usertype($link,$cookiename);
		$username = loc_get_username($cookiename);
		if('inactive'==$type){//shouldn't have a cookie
			loc_delete_cookie($cookiename);
			loc_display_login($title,$color, $mode,$message="User in cookie is listed as inactive.",$loginapp,$reg);
			exit();
		} else if('admin'==$type){ // the user is the admin so it's good enough no matter what
			loc_create_auth_cookie($username,$expiry,$magic_word,$cookiename);//reset cookie so it doesn't expire// how can there be a valid $type at this point? 
		} else if((!$admin && 'active'==$type)|| 'admin'==$type){//we don't need an admin so any good cookie is good enough
			loc_create_auth_cookie($username,$expiry,$magic_word,$cookiename);//reset cookie so it doesn't expire
            return true;
// no valid cookie, no valid type, 
        } else { //it will get here if we've dropped the username out of the cookie or anyother case where get_username fails
			loc_delete_cookie($cookiename);
			loc_display_login($title,$color, $mode,$message="User in cookie has unknown type (cookie removed).",$loginapp,$reg);
			exit();
        }
		return true;
    } else {
		loc_delete_cookie($cookiename);
	}//if loc_check_auth
    //at this point there isn't a valid cookie
//debug_string("no valid cookie");
    // check username/password
    if(isset($username) && isset($pass)){//we have no cookie but we have a username and password
//debug_string("have username and password",$username.":".$pass);
//debug_string("doing mysql think");
    	$users = MYSQLComplexSelect($link,array("*"),array($usertable),array("username='".$username."'"),array(),0);
//debug_string ("done with mysql thing");
		if (count($users)==0){//no such user
			loc_display_login($title,$color,$mode,$message="Bad user/password combination.",$loginapp,$reg);
			exit();
		}
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
		
		/*   
TRUTH TABLE OF USER ACCESS CASES
           $admin (need admin privs to succeed)
$TYPE   |True|False|
--------------------
admin   | T  |  T  |
--------------------
active  | F  |  T  |
--------------------
inactive| F  |  F  |
--------------------
T means the login succeeds
F means the login fails
*/
			if ('inactive'==$type) {//user inactive
				loc_display_login($title,$color,$mode,$message="User is inactive. Activate user or login as different user.",$loginapp,$reg);
				exit();
			}
			else if ($admin && 'active'==$type){  // action requires admin privs
				loc_display_login($title,$color,$mode,$message="Action requires user to be admin.",$loginapp,$reg);
				exit();
			} else {// good to go!
				loc_create_auth_cookie($username,$expiry,$magic_word,$cookiename);//reset cookie so it doesn't expire
				return true;
			}
		}else{// bad password
			loc_display_login($title,$color,$mode,$message="Bad user/password combination.",$loginapp,$reg);
			exit();
		}// password
			

    }//we have username and password
    //display password routine
	loc_display_login($title,$color,$mode,$message="",$loginapp,$reg);
    exit();
}
//------------------------------------
function loc_delete_cookie($cookiename="login"){
	setcookie($cookiename,"",time()-11000);
}

//------------------------------------
function loc_get_cookie_data($cookiename="login"){
	if(!isset($_COOKIE[$cookiename])){
		return array();
	}
	//debug_string("loc_get_cookie_data()");
	$cookstr = $_COOKIE[$cookiename];
	$datalist = explode ( "+", $cookstr);
	$result['name'] = $datalist[0];
	$result['time'] = $datalist[1];
	$result['hash'] = $datalist[2];
	$result['expire'] = $datalist[3];
	return $result;
}
//------------------------------------
function loc_save_cookie_data($name,$hashtime,$hash,$cookexp=0,$cookiename="login"){
	$data = $name."+".$hashtime."+".$hash."+".$cookexp;
	//debug_string("loc_save_cookie_data()");
	setcookie($cookiename,$data,$cookexp);

}
//------------------------------------
/***********************************
 * loc_GenerateRandomWord()
 * creates a random word of alpha numerics of variable length
 * $minlen: the minimum allowable length of the word
 * $maxlen: the maximum allowable length of the word
 * Return: a string of random characters
 * side effects: none (well, it uses some random numbers which probably changes the seed)
 *******************************************/
function loc_GenerateRandomWord($minlen=10,$maxlen=25){
//debug_string("loc_GenerateRandomWord($minlen,$maxlen)");
    $charset = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";// character set
    $charsetlen = strlen($charset);//lenth of character set

    // generate a length value that various widely but tends to be around 75% of maxlen characters
	$varlen = ($maxlen-$minlen)/2;
	$maxlen = $maxlen-$varlen;
	if ($minlen<1) $minlen=1;
	if ($maxlen<6) $maxlen=6;
    $len=mt_rand($minlen,$maxlen)+mt_rand(0,$varlen/3)+mt_rand(0,$varlen/3)+mt_rand(0,$varlen/3);
	
// generate a random string of characters using charset of len $len
    for ($i = 1; $i <= $len; $i++){
        $n = mt_rand(0,$charsetlen-1);
        $word = $word.$charset[$n];
    }
    return $word;
}

//------------------------------------
function loc_login_util_version(){
    return '$Id: loc_login.php,v 1.12 2008/01/23 03:19:45 dmenconi Exp $';
}
/*
 * $Log: loc_login.php,v $
 * Revision 1.12  2008/01/23 03:19:45  dmenconi
 * added <table> to login page to fix display bug
 *
 * Revision 1.11  2007/12/03 01:10:04  dmenconi
 * checkpoint
 *
 * Revision 1.10  2007/05/11 15:52:12  dmenconi
 * added license information
 *
 * Revision 1.9  2007/03/10 16:29:39  dmenconi
 * added code to update log database and to mail from: somewher
 *
 * Revision 1.2  2007/02/24 23:06:53  dave
 * changed mysql_query to the new insert and update routines
 *
 * Revision 1.1  2007/02/24 21:14:12  dave
 * Initial revision
 *
 * Revision 1.7  2007/02/08 08:39:08  dmenconi
 * got the activation stuff to work
 * removed a lot of debugs
 * a few other minor tweaks
 *
 * Revision 1.6  2007/02/06 15:26:48  dmenconi
 * fixed display login so it doesn't always display registration
 *
 * Revision 1.5  2007/02/06 06:05:05  dmenconi
 * changed params to loc_GetAuth so that we pass PARAM array instead of 3 other variables!
 *
 * Revision 1.4  2007/01/19 04:37:28  dmenconi
 * fixed problem with it not accepting cookies
 *
 * Revision 1.1  2005/11/29 20:26:03  dave
 * Initial revision
 *
 * Revision 1.5  2005/11/22 02:12:31  dave
 * checks to see if cookie has expired and, if it has, diallows it
 *
 * Revision 1.4  2005/11/21 12:26:45  dave
 * reworked cookies so there is only one string in cookie not an  array
 * rewored cookies so they contain the expiration date of the cookie
 *
 * Revision 1.3  2005/11/20 02:52:25  dave
 * login seems to work
 *
 * Revision 1.2  2005/11/20 00:02:21  dave
 * code is in but bugs persist
 *
 * Revision 1.1  2005/11/16 21:14:18  dave
 * Initial revision
 *
*/
?>
