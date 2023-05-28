<?php

date_default_timezone_set('Etc/UTC');
session_start();

//connect to database function
//MySQL LOgin
$dbuser = '';
$dbpass = '';
$server = 'localhost:3306';
$dbname = '';
function connectToDB() {
	global $server, $dbuser, $dbpass, $dbname;
	$conn = mysqli_connect($server, $dbuser, $dbpass, $dbname);
	if (!$conn) {
		die("Connection failed: " . mysqli_connect_error() . "<br>");
	}
	return $conn;
}

$time = gmdate("Y-m-d\TH:i:s");
$thisurl = '/';

$me = $_SESSION['id'];
$myEmail = $_SESSION['email'];
$sessionDate = $_SESSION['date'];

if (!isset($_REQUEST["act"])) {
    checkAuth();
}

if (isset($_REQUEST["act"])) {
    $action = $_REQUEST["act"];

    if ($action == 'info') {
        if (verifySession($me, $myEmail, $sessionDate)) {
            $id = $me;
            if (isset($_REQUEST["id"])) {
                if(checkPermissionLevel() >= 2) {
                    $id = $_REQUEST["id"];
                }
            }
            returnUserInfo($id);
        }
    }
    if ($action == 'get') {
        
        if (verifySession($me, $myEmail, $sessionDate)) {
            returnUserTickets($me);
        }
    }
    if ($action == 'display') {
        if (verifySession($me, $myEmail, $sessionDate)) {
            if (isset($_REQUEST['ticket'])) {
                $hash = $_REQUEST['ticket'];
                returnTicketContents($hash);
            }
        }
    }
    if ($action == 'submit') {
        if (verifySession($me, $myEmail, $sessionDate)) {
            if (isset($_REQUEST["cc"]) && isset($_REQUEST["subject"]) && isset($_REQUEST["msg"])) {
                $cc = $_REQUEST["cc"];
                $subject = $_REQUEST["subject"];
                $msg = $_REQUEST["msg"];
                $tickethash = $_REQUEST["ticket"];
                if ($msg != '') {
                    submitTicket($_SESSION['id'], $cc, $subject, $msg, $tickethash);
                }
            }
        } else {
            echo "not authenticated";
        }
    }
    if ($action == 'auth') {
        if (!verifySession($me, $myEmail, $sessionDate)) {
            if (isset($_REQUEST["email"]) && isset($_REQUEST["pass"])) {
                $email = $_REQUEST["email"];
                $pass = $_REQUEST["pass"];
                authenticateUser($email, $pass);
            }
        }
    }
    if ($action == 'adduser') {
        if (verifySession($me, $myEmail, $sessionDate)) {
            if (checkPermissionLevel() >= 2) {
                if (isset($_REQUEST["name"]) && isset($_REQUEST["email"]) && isset($_REQUEST["pass"])) {
                    $name = $_REQUEST["name"];
                    $email = $_REQUEST["email"];
                    $pass = $_REQUEST["pass"];
                    $address = $_REQUEST["address"];
                    $city = $_REQUEST["city"];
                    $province = $_REQUEST["province"];
                    $postal = $_REQUEST["postal"];
                    $phone = $_REQUEST["phone"];
                    addClient($name, $email, $pass, $address, $city, $province, $postal, $phone);
                }
            }
        }
    }
    if ($action == 'verify') {
        if (isset( $_REQUEST["ver"])) {
            $uverify = $_REQUEST["ver"];
            if ($uverify != '') {
                verifyClient($uverify);
            } else {
                echo "no verification code provided";
            }
        }
    }
    if ($action == 'change') {
        if (verifySession($me, $myEmail, $sessionDate)) {
            $id = $me;
            if (isset($_REQUEST["id"])) {
                if(checkPermissionLevel() >= 2) {
                    $id = $_REQUEST["id"];
                }
            }
            foreach ($_REQUEST as $key => $value)  { 
                if ($key != "act") {
                    changeUserInfo($key, $value, $id); 
                }  
            }  
        }
    }
    if ($action == 'logout') {
        logOut();
    }
}

function checkAuth() {
    global $me, $myEmail, $sessionDate;
    $data = new stdClass();
    if (verifySession($me, $myEmail, $sessionDate)) {
        $data->authenticated=true;
    } else {
        $data->authenticated=false;
    }
    sendToClient($data);
}

function startSession($id, $email, $date) {
    $sessionKey = md5($id) . md5($email) . md5($date);
    $sessionKey = md5($sessionKey);
	$conn = connectToDB();
	$sql = sanitizeIt("UPDATE users SET session = '%s' WHERE id='%s' and email='%s'", $sessionKey, $id, $email);
	if(mysqli_query($conn, $sql)) {
		$_SESSION['id'] = $id;
		$_SESSION['email'] = $email;
		$_SESSION['date'] = $date;
	}
}
function logOut() {
    global $me;
	$conn = connectToDB();
	$sql = sanitizeIt("UPDATE users SET session = '' WHERE id='%s'", $me);
	if(mysqli_query($conn, $sql)) {
		if(session_destroy()) {
            echo 'logged out';
            header( "Refresh:3; url='index.html'");
            echo "<meta http-equiv='refresh' content=3;URL=\'res/index.html'>";
        }
	}
}

function verifySession($id, $email, $date) {
    $sessionKey = md5($id) . md5($email) . md5($date);
    $sessionKey = md5($sessionKey);
	$conn = connectToDB();
    $sql = sanitizeIt("SELECT id, email, session FROM users WHERE (id = '%s' and email = '%s' and session='%s')", $id, $email, $sessionKey);
	$result = mysqli_query($conn, $sql);
	$row = mysqli_fetch_array($result,MYSQLI_ASSOC); 
	$count = mysqli_num_rows($result);
	if($count == 1) {
		return true;
	} 
	return false;
}

function checkPermissionLevel() {
    global $me;
    $conn = connectToDB();
    $sql = sanitizeIt("SELECT level FROM users WHERE id = '%s'", $me);
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_array($result,MYSQLI_ASSOC); 
    $count = mysqli_num_rows($result);	
    if($count == 1) {
        return $row['level'];
    }
    return 0;
}

function authenticateUser($email, $pass) {
	global $time;
    $data = new stdClass();
	$conn = connectToDB();
	$sql = sanitizeIt("SELECT id, email, password, level FROM users WHERE (email = '%s' and password = '%s')", $email, $pass);
	$result = mysqli_query($conn, $sql);
	$row = mysqli_fetch_array($result,MYSQLI_ASSOC); 
	$count = mysqli_num_rows($result);	
	if($count == 1) {
		if ($row['level'] != 0){
            
			startSession($row['id'], $row['email'], $time);
            
            $data->result=true;
            $data->id = $row['id'];
            $data->email = $row['email'];
            $data->time = $time;
            
		} else {
			 $data->result = "email not verified";
		}
	}	else {
        $data->result = false;
	}
    sendToClient($data);
	mysqli_close($conn);
}

function addClient($name, $email, $pass, $address, $city, $province, $postal, $phone) {
	global $thisurl, $time;
    $data = new stdClass();
	$conn = connectToDB();
	if (!checkUserExists($email)) {
        $time = strval($time);
		//verification key
		$vkey = md5("verify:" . $email . $time);
		//user id key (used for linking user to other tables)
		$ukey = $name . $email . $time;
		$ukey = encryptIt($ukey);


		$sql = sanitizeIt("INSERT INTO users (id, email, password, level, regdate, actionurl) VALUES ('%s', '%s', '%s', '0', '$time', '%s');", $ukey, $email, $pass, $vkey);
        $sql .= sanitizeIt("INSERT INTO profiles (id, name, address, city, province, postal, phone) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s');", $ukey, $name, $address, $city, $province, $postal, $phone);
		if (mysqli_multi_query($conn, $sql)) {
			$link  = $thisurl.'api.php?act=verify&ver='. $vkey;
			$msg = "Please click the following link to verify your account... " . $link;
			//$link ="<a href='". $link."'>" . $link . "</a>";
			$html = "Please click the following link to verify your account... <br>" . $link;
			//sendMail($uemail, $uname, "Email Verification", $msg, $html);
            $data->result = "success";
            $data->link = $link;
		} else {
            $data->result = "Error: " . $sql . "<br>" . mysqli_error($conn);
		}
	} else {
		$data->result = "Username/Email already exists";
	}
    sendToClient($data);
	mysqli_close($conn);
}
function verifyClient($ver) {
	$conn = connectToDB();
	$sql = sanitizeIt("SELECT actionurl FROM users WHERE actionurl = '%s'", $ver);
	if (mysqli_query($conn, $sql)) {
		$sql = sanitizeIt("UPDATE users SET level = 1, actionurl = '' WHERE actionurl = '%s'", $ver);
		$result = mysqli_query($conn, $sql);
		echo "user group adjusted successfully";
		return true;
	} else {
		echo "no such verification code exists";
		return false;
	}
	mysqli_close($conn);
}
function checkUserExists($email) {
	$conn = connectToDB();
	$sql = sanitizeIt("SELECT email FROM users WHERE email = '%s'", $email);
	$result = mysqli_query($conn, $sql);
	$count = mysqli_num_rows($result);
	if ($count == 0) {
		return false;
	}
	return true;
}


function returnUserInfo($id) {
    $data = new stdClass();
    $conn = connectToDB();
    $sql = sanitizeIt("SELECT name, address, city, province, postal, phone, domains FROM profiles WHERE id = '%s'", $id);
	$result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_array($result,MYSQLI_ASSOC); 
	$count = mysqli_num_rows($result);	
	if($count == 1) {
		$data->result = "success";
        $data->id=$id;
        $data->name = $row['name'];
        $data->email = getEmailFromID($id);
        $data->address = $row['address'];
        $data->city = $row['city'];
        $data->province = $row['province'];
        $data->postal = $row['postal'];
        $data->phone = $row['phone'];
        $data->domains = $row['domains'];
	}else{
	    $data->result = "none";
    }
    sendToClient($data);
}

function changeUserInfo($name, $value, $id) {
    $data = new stdClass();
    $conn = connectToDB();
    if ($name == "email") {
        $sql = sanitizeIt("UPDATE users SET $name = '%s' WHERE id='%s'", $value, $id);
    /*} else  if ($name=='domains') {
        $sql = sprintf("UPDATE domains SET $name = '%s' WHERE id='%s'",
        mysqli_real_escape_string($conn, $value),
        mysqli_real_escape_string($conn, $id));*/
    } else {
        $sql = sanitizeIt("UPDATE profiles SET $name = '%s' WHERE id='%s'", $value, $id);
    }
	$result = mysqli_query($conn, $sql);
	if($result) {
		$data->result = "successfully updated";

	} else {
		$data->result = "not updated";
	}
    sendToClient($data);
}
function submitTicket($from, $cc, $subject, $content, $ticket) {
	global $time;
	$conn = connectToDB();
    $tickets ="";
    $messages ="";
	if ($subject== 'Subject') {
		$subject= "No Subject";
	}
	if ($ticket == '') {
			//create new ticket
		$key = $from . $subject. $time;
		$key = md5($key);
		$tickets = sanitizeIt("INSERT INTO tickets (tickethash, datecreated, requester, cc, subject, lasttopost, lastupdated) VALUES ('%s', '$time', '%s', '%s', '%s', '%s', '$time');", $key, $from, $cc, $subject, $from);
        
		$messages = sanitizeIt("INSERT INTO messages (tickethash, id, content, date) VALUES ('%s', '%s', '%s', '$time');", $key, $from, $content);
	} else {
			//add to existing message
		//$subject= $row["subject"];			
		$tickets = sanitizeIt("UPDATE tickets SET lasttopost = '%s', lastupdated = '$time' WHERE tickethash = '%s';", $from, $ticket);
		$messages = sanitizeIt("INSERT INTO messages (tickethash, id, content, date) VALUES ('%s', '%s', '%s', '$time');", $ticket, $from, $content);

	}
    $sql = $tickets . $messages;
	if (mysqli_multi_query($conn, $sql)) {
			//success
		echo "success";
	} else {
		echo "Error: " . $sql . "<br>" . mysqli_error($conn);
	}
	mysqli_close($conn);
}

function returnUserTickets() {
    global $me;
    $posts = [];
    $conn = connectToDB();
	if (checkPermissionLevel() == 1) {
			//user view
		$sql = sanitizeIt("SELECT tickethash, requester, subject, lasttopost, lastupdated, datecreated, deleted  FROM tickets WHERE requester = '%s'", $me);
	}
	if (checkPermissionLevel() == 2) {
			//admin view
		$sql = "SELECT tickethash, requester, subject, lasttopost, lastupdated, datecreated, deleted  FROM tickets";
	}
	$result = mysqli_query($conn, $sql);
    global $thisurl;
	while($row = mysqli_fetch_array($result)) {
        $data = new stdClass();
		if ($row['lasttopost'] != $me) {
			//this message is new
            $data->status = "new";
			//$data = array("status"=>"new", "subject"=>$row['subject'], "author"=>getEmailFromID($row['requester']), "lasttopost"=>$row['lasttopost'], "lastupdated"=>$row['lastupdated'], "url"=>$thisurl . 'api.php?act=checkmsg&newmsg=yes&tickethash=' . $row['tickethash']);
		} else {
			//this message is checked
			//$data = array("status"=>"old", "subject"=>$row['subject'], "author"=>getEmailFromID($row['requester']), "lasttopost"=>$row['lasttopost'], "lastupdated"=>$row['lastupdated'], "url"=>$thisurl . 'api.php?act=checkmsg&newmsg=yes&tickethash=' . $row['tickethash']);
            $data->status = "pending";
		}
        
        $data->subject = $row['subject'];
        $data->lastToPost = getNameFromID($row['lasttopost']);
        $data->dateCreated = $row['datecreated'];
        $data->lastUpdated = $row['lastupdated'];
        $data->hash = $row['tickethash'];
        
        array_push($posts, $data);
        
	}
    sendToClient($posts);
    mysqli_close($conn);
}

function returnTicketContents($ticket) {
    global $time;
    $posts = [];
	$conn = connectToDB();
	$sql = "SELECT tickethash, id, content, date FROM messages WHERE tickethash = '$ticket'";
	$result = mysqli_query($conn, $sql);
	while($row = mysqli_fetch_array($result)) {
        $data = new stdClass();
        $data->author = getNameFromID($row['id']);
        $data->message = $row['content'];
        $data->date = $row['date'];

        array_push($posts, $data);
	}
    sendToClient($posts);
    mysqli_close($conn);
}



function sendToClient($data) {
    echo json_encode($data);
}

function encryptIt($str) {
	$str = md5($str);
	$str = strrev($str);
	$str = md5($str);
	return $str;
}
function sanitizeIt(){
    $conn = connectToDB();
    $numOfArgs = func_num_args();
    $args = func_get_args();
    $str = $args[0];
    array_shift($args);
    for ($i = 0; $i<$numOfArgs; $i++) {
        $args[$i] = htmlentities($args[$i]);
        $args[$i] = str_replace("!", "&#33;", $args[$i]);
        $args[$i] = str_replace("$", "&#36;", $args[$i]);
        $args[$i] = str_replace("%", "&#37;", $args[$i]);
        $args[$i] = str_replace("^", "&#94;", $args[$i]);
        $args[$i] = str_replace("*", "&#33;", $args[$i]);
        $args[$i] = str_replace("?", "&#63;", $args[$i]);
        $args[$i] = str_replace("<", "&lt;", $args[$i]);
        $args[$i] = str_replace(">", "&gt;", $args[$i]);
        $args[$i] = str_replace("+", "&#43;", $args[$i]);
        $args[$i] = str_replace("-", "&#45;", $args[$i]);
        $args[$i] = str_replace("=", "&#61;", $args[$i]);
        $args[$i] = str_replace("(", "&#40;", $args[$i]);
        $args[$i] = str_replace(")", "&#41;", $args[$i]);
        $args[$i] = str_replace("[", "&#91;", $args[$i]);
        $args[$i] = str_replace("]", "&#93;", $args[$i]);
        $args[$i] = str_replace("{", "&#123;", $args[$i]);
        $args[$i] = str_replace("|", "&#124;", $args[$i]);
        $args[$i] = str_replace("}", "&#125;", $args[$i]);
        $args[$i] = str_replace("/", "&#47;", $args[$i]);
        $args[$i] = str_replace(":", "&#58;", $args[$i]);
        $args[$i] = str_replace("`", "&#96;", $args[$i]);
        $args[$i] = str_replace("~", "&#126;", $args[$i]);
        
        $args[$i] = preg_replace("/[^a-zA-Z0-9\s@_.,&#;]/", "", $args[$i]);
        $args[$i] = mysqli_real_escape_string($conn, $args[$i]);
    }
    
    $str = vsprintf($str, $args);
    return $str;
}

function getEmailFromID($id) {
	$conn = connectToDB();
	$sql = sanitizeIt("SELECT email FROM users WHERE id = '%s'", $id);
	$result = mysqli_query($conn, $sql);
	$row = mysqli_fetch_array($result,MYSQLI_ASSOC); 
	$result = mysqli_query($conn, $sql);
	$r = $row['email'];
	mysqli_close($conn);
	return $r;
}
function getNameFromID($id) {
	$conn = connectToDB();
	$sql = sanitizeIt("SELECT name FROM profiles WHERE id = '%s'", $id);
	$result = mysqli_query($conn, $sql);
	$row = mysqli_fetch_array($result,MYSQLI_ASSOC); 
	$result = mysqli_query($conn, $sql);
	$r = $row['name'];
	mysqli_close($conn);
	return $r;
}

?>