<?php
/**
 * Created by PhpStorm.
 * User: Misumoo
 * Date: 5/7/2015
 * Time: 12:11 PM
 */

session_start();

class db {
  const dbserver = "localhost";
  const dbuser = 'creatkd5_chris';
  const dbpass = 'HdzGdhXjTNSJ6WS3';
  const dbname = 'creatkd5_tspro';
}
class userInfo {
  public $userfirstname;
  public $usertoken;
}

require 'phpmailer/PHPMailerAutoload.php';

(isset($_COOKIE['usertoken']) ? $usertoken = $_COOKIE['usertoken'] : $usertoken = "");
$userid = getUserIDFromToken($usertoken);
//our json data
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$task = $request->task;
//first thing before we perform anything is to make sure our user is actually in the database
if(!$userid || $usertoken == "") { // no id is bound to this token
  //as a non logged in user, our only tasks can be login and register
  if($task != "login" && $task != "register" && $task != "resetDo" && $task != "resetInit") {
    $task = "";
    $data = array("success" => false, "message" => "No userid or token");
    echo json_encode($data);
    exit();
  }
}

#######################################################################################################################
#########################################################TASKS#########################################################
#######################################################################################################################
if($task == "getCustomers") {
  echo getCustomers($userid);
} //task CustomerName

if($task == "addCustomer") {
  $customername = $request->customername;
  echo addCustomer($userid, $customername);
} //task addProject

if($task == "getTimes") {
  $date = $request->date;
  echo getTimes($userid, $date);
} //task getTimes

if($task == "login") {
  $username = $request->email;
  $userpass = $request->password;

  $data = login($username, $userpass);

  if(!$data) {
    $data = json_encode(array("success" => false));
  } else {
    $data = login($username, $userpass);
  }
  echo $data;
} //task login

if($task == "register") {
  $firstname = $request->firstname;
  $lastname = $request->lastname;
  $email = $request->email;
  $userpass = $request->password;
  $data = register($firstname, $lastname, $email, $userpass);
  echo $data;
} //task register

if($task == "resetInit") {
  $email = $request->email;
  $data = resetInit($email);
  echo $data;
} //task resetInit

if($task == "resetDo") {
  $email = $request->email;
  $confirmationcode = $request->confirmationcode;
  $password = $request->password;
  $data = resetDo($email, $confirmationcode, $password);
  echo $data;
} //task resetInit


if($task == "saveSingle") {
  $amount = $request->amount;
  $weeklyid = $request->weeklyid;
  $timeid = $request->timeid;
  $date = $request->date;
  $data = timeControl($weeklyid, $userid, $date, $amount, $timeid);
  echo $data;
} //saveSingle

if($task == "saveDescription") {
  $description = $request->description;
  $weeklyid = $request->weeklyid;
  $data = saveDescription($description, $weeklyid, $userid);
  echo $data;
} //saveDescription

if($task == "saveService") {
  $service = $request->service;
  $weeklyid = $request->weeklyid;
  $data = saveService($service, $weeklyid, $userid);
  echo $data;
} //saveService

if($task == "saveCustomer") {
  $customer = $request->customer;
  $weeklyid = $request->weeklyid;
  $data = saveCustomer($customer, $weeklyid, $userid);
  echo $data;
} //saveCustomer

if($task == "getNewWeeklyID") {
  $data = getNewWeeklyID($userid);
  echo $data;
} //getNewWeeklyID

if($task == "getServices") {
  $data = getServices($userid);
  echo $data;
} //getServices

if($task == "lookupService") {
  $service = $request->service;
  $data = lookupService($userid, $service);
  echo $data;
} //lookupService

if($task == "getTasks") {
  echo getTasks();
} //task getTimes

if($task == "addTask") {
  $description = $request->taskdescription;
  echo addTask($description);
} //task addProject
#######################################################################################################################
#####################################################END TASKS#########################################################
#######################################################################################################################



#######################################################################################################################
#######################################################FUNCTIONS#######################################################
#######################################################################################################################
function lookupService($userid, $service) {
  $data = "";
  $serviceid = "";
  $service = convertForInsert($service);
  $userid = convertForInsert($userid);

  if($service == "") {
    $data = array("message" => "Blank", "ServiceID" => "", "success" => true);
    return json_encode($data);
  }

  $sql = "SELECT ServiceID, ServiceName FROM tbl_services WHERE ServiceName = ".$service." AND UserID = ".$userid;
  $mysqli = new mysqli(db::dbserver, db::dbuser, db::dbpass, db::dbname);

  $rs = $mysqli->query($sql);
  while($row = $rs->fetch_assoc()) {
    $serviceid = $row['ServiceID'];
  }
  $rs->free();
  $mysqli->close();

  $data = array("message" => "Good", "ServiceID" => $serviceid, "success" => true, "sql" => $sql);
  return json_encode($data);
}

function saveDescription($description, $weeklyid, $userid) {
  $task = "update";

  $description = convertForInsert($description);
  $weeklyid = convertForInsert($weeklyid);
  $userid = convertForInsert($userid);

  if($task == "update") {
    $sql = "UPDATE `tbl_timesheet` SET Description = ".$description." WHERE WeeklyID = ".$weeklyid." AND UserID = ".$userid.";";
  }

  $mysqli = new mysqli(db::dbserver, db::dbuser, db::dbpass, db::dbname);

  $mysqli->query($sql);
  $mysqli->close();

  $data = array("message" => $task, "sql" => $sql);
  return json_encode($data);
} //saveDescription

function saveService($service, $weeklyid, $userid) {
  $task = "update";

  $service = convertForInsert($service);
  $weeklyid = convertForInsert($weeklyid);
  $userid = convertForInsert($userid);

  if($task == "update") {
    $sql = "UPDATE `tbl_timesheet` SET ServiceName = ".$service." WHERE WeeklyID = ".$weeklyid." AND UserID = ".$userid.";";
  }

  $mysqli = new mysqli(db::dbserver, db::dbuser, db::dbpass, db::dbname);

  $mysqli->query($sql);
  $mysqli->close();

  $data = array("message" => $task, "sql" => $sql);
  return json_encode($data);
} //saveService

function saveCustomer($customer, $weeklyid, $userid) {
  $task = "update";

  $customer = convertForInsert($customer);
  $weeklyid = convertForInsert($weeklyid);
  $userid = convertForInsert($userid);

  if($task == "update") {
    $sql = "UPDATE `tbl_timesheet` SET CustomerName = ".$customer." WHERE WeeklyID = ".$weeklyid." AND UserID = ".$userid.";";
  }

  $mysqli = new mysqli(db::dbserver, db::dbuser, db::dbpass, db::dbname);

  $mysqli->query($sql);
  $mysqli->close();

  $data = array("message" => $task, "sql" => $sql);
  return json_encode($data);
} //saveCustomer

function timeControl($weeklyid, $userid, $date, $hours, $timeid) {
  $sql = "";

  $hours = convertToCurrency($hours);

  //assign our tasks
  //if we have no amount and no time id, we don't need to be in here
  if($hours == "0.00" && $timeid == "") {
    $data = array("message" => "No changes needed.");
    return json_encode($data);
  }

  if($timeid == "") {
    $timetask = "insert"; //no time id, insert
  } else {
    $timetask = "update"; //update if we have a timeid
  }

  ($hours == "0.00" && $timeid != "" ? $timetask = "delete" : "");

  //setup our items for insertion
  $weeklyid = convertForInsert($weeklyid);
  $userid = convertForInsert($userid);
  $date = convertForInsert($date);
  $hours = convertForInsert($hours);
  $timeid = convertForInsert($timeid);

  ###########################
  #          TASKS          #
  ###########################
  if($timetask == "delete") {
    //check to see if we have a timeid before actually continuing..
    if($timeid == "") {
      $data = array("message" => "unchanged", "id" => "");
      return json_encode($data);
    }
    $sql = "DELETE FROM tbl_time WHERE UserID = ".$userid." AND TimeID = ".$timeid.";";
  }

  if($timetask == "insert") {
    $sql = "INSERT INTO `tbl_time` (TimeID, UserID, Hours, HourDate, WeeklyID) VALUES (NULL, ".$userid.", ".$hours.", ".$date.", ".$weeklyid.");";
  }

  if($timetask == "update") {
    $sql = "UPDATE `tbl_time` SET Hours = ".$hours.", HourDate = ".$date." WHERE TimeID = ".$timeid." AND UserID = ".$userid.";";
  }
  ###########################
  #        END TASKS        #
  ###########################

  $mysqli = new mysqli(db::dbserver, db::dbuser, db::dbpass, db::dbname);

  $mysqli->query($sql);
  $insertid = $mysqli->insert_id;

  $mysqli->close();

  $data = array("message" => $timetask, "id" => $insertid);
  return json_encode($data);
} //timeControl

function resetInit($email) {
  $success = true;
  $msg = "";
  $name = "TODO";
  $resetcode = strtoupper(bin2hex(openssl_random_pseudo_bytes(4)));

  $sql = "UPDATE tbl_users_reset SET Expired = 1 WHERE Email = ".convertForInsert($email).";
          INSERT INTO tbl_users_reset
          (ResetID, Email, ResetTimeStamp, IPAddress, ResetCode) VALUES
          (NULL, ".convertForInsert($email).", NULL, ".convertForInsert($_SERVER['REMOTE_ADDR']).", ".convertForInsert($resetcode).")";
  $mysqli = new mysqli(db::dbserver, db::dbuser, db::dbpass, db::dbname);

  $mysqli->multi_query($sql);

  $useremail = $email;

  $mailsubject = "Code for resetting your password.";
  $mailfooter = "
    <p>
      Footer TODO
    </p>
  ";
  $mailbody = '
    <html>
      <body>
        <span style="font-family: Calibri; font-size: 15px;">
          Hello '.$name.',<br />
          <br />
          Your reset code is: <b>'.$resetcode.'</b><br />
          '.$mailfooter.'
        </span>
      </body>
    </html>
  ';

  $mail = new PHPMailer;
  //$mail->SMTPDebug = 3;                     // Enable verbose debug output
  //$mail->SMTPDebug = 1;

  $mail->isSMTP();                          // Set mailer to use SMTP
//  $mail->Mailer = 'smtp';
//  $mail->Host = 'smtp.gmail.com';           // Specify main and backup SMTP servers
//  $mail->SMTPAuth = true;                   // Enable SMTP authentication
//  $mail->Username = 'tsprodb@gmail.com';    // SMTP username
//  $mail->Password = 'tr[]pp@3!!a';          // SMTP password
//  $mail->SMTPSecure = 'tls';                // Enable TLS encryption, `ssl` also accepted
//  $mail->Port = 587;                        // TCP port to connect to

  $mail->SetFrom('tsprodb@gmail.com', 'Timesheet');

  $mail->From = 'tsprodb@gmail.com';
  $mail->FromName = 'Timesheet';

  $mail->addAddress($useremail, '');        // Add a recipient
  $mail->Subject  = $mailsubject;
  $mail->Body     = $mailbody;
  $mail->AltBody  = 'Please use a service that will view emails as HTML.';

  if(!$mail->send()) {
    $success = false;
    $msg = $mail->ErrorInfo;
  }

  $data =  array("success" => $success, "message" => $msg);
  return json_encode($data);
} //login

function resetDo($email, $confirmationcode, $password) {
  $success = true;
  $msg = "";
  $userid = "";
  $reset = false;

  $sql = "SELECT * FROM tbl_users_reset WHERE Email = ".convertForInsert($email)." AND Expired = '0'";
  $mysqli = new mysqli(db::dbserver, db::dbuser, db::dbpass, db::dbname);
  $rs = $mysqli->query($sql);
  while($row = $rs->fetch_assoc()) {
    if($row['ResetCode'] == trim($confirmationcode)) {
      $reset = true;
    }
  }

  if($reset) {
    $sql = "SELECT UserID FROM tbl_users WHERE Email = ".convertForInsert($email);
    $mysqli = new mysqli(db::dbserver, db::dbuser, db::dbpass, db::dbname);
    $rs = $mysqli->query($sql);
    while($row = $rs->fetch_assoc()) {
      $userid = $row['UserID'];
    }
    if($userid != "") {
      $salt = generateSalt($userid);
      $salted = encryptPassword($password, $salt);
      updatePassword($salted, $userid);
      $sql = "UPDATE tbl_users_reset SET Expired = 1 WHERE Email = ".convertForInsert($email);
      $mysqli->query($sql);
    }
  } else {
    $success = false;
    $msg = "Code or email was incorrect.";
  }

  $data =  array("success" => $success, "message" => $msg);
  return json_encode($data);
}

function register($firstname, $lastname, $email, $userpass) {
  $success = true;
  $msg = "";
  $insertid = "";
  $checkemail = "";

  if($email == "" || $userpass == "" || $lastname == "" || $firstname == "") {
    return false;
  }

  $oldpass = $userpass;

  $email = convertForInsert($email);
  $userpass = convertForInsert($userpass);
  $lastname = convertForInsert($lastname);
  $firstname = convertForInsert($firstname);

  $sql = "SELECT Email FROM tbl_users WHERE Email = ".$email;
  $mysqli = new mysqli(db::dbserver, db::dbuser, db::dbpass, db::dbname);

  $rs = $mysqli->query($sql);
  while($row = $rs->fetch_assoc()) {
    $checkemail = $row['Email'];
  }
  $rs->free();
  $mysqli->close();

  if($checkemail != "") {
    //we have an email address already, bail
    $success = false;
    $msg = "Email already exists.";
  }

  if($success) {
    $sql = "INSERT INTO tbl_users
          (UserID, LastName, FirstName, Email, Password) VALUES
          (NULL, $lastname, $firstname, $email, $userpass)";
    $mysqli = new mysqli(db::dbserver, db::dbuser, db::dbpass, db::dbname);

    $mysqli->query($sql);
    $userid = $mysqli->insert_id;

    $salt = generateSalt($userid);
    $salted = encryptPassword($oldpass, $salt);
    updatePassword($salted, $userid);

    $mysqli->close();
  }

  $data = array("success" => $success, "message" => $msg, "id" => $insertid);
  return json_encode($data);
} //register

function login($username, $userpass) {
  if($username == "" || $userpass == "") {
    return false;
  }
  $salt = "";

  $sql = "SELECT Salt, UserID FROM tbl_users WHERE Email = ".convertForInsert($username);
  $mysqli = new mysqli(db::dbserver, db::dbuser, db::dbpass, db::dbname);
  $rs = $mysqli->query($sql);
  while($row = $rs->fetch_assoc()) {
    $userid = $row['UserID'];
    $salt = ($row['Salt'] == "" ? generateSalt($userid) : $row['Salt']);
  }

  $salted = encryptPassword($userpass, $salt);

  $rs->free();
  $mysqli->close();

  $sql = "SELECT UserID, FirstName FROM tbl_users WHERE Email = ".convertForInsert($username)." AND Password = ".convertForInsert($salted);
  $mysqli = new mysqli(db::dbserver, db::dbuser, db::dbpass, db::dbname);

  $rs = $mysqli->query($sql);
  if($rs->num_rows < 1) {
    //we don't have this user
    return false;
  } else {
    while($row = $rs->fetch_assoc()) {
      $data =  array("success" => true, "usertoken" => generateToken($row['UserID']), "userfirstname" => $row['FirstName']);
      return json_encode($data);
    }
    //return true;
  }
} //login

function getServices($userid) {
  $data = "";
  $dbdata = "";

  $sql = "SELECT ServiceID, ServiceName FROM tbl_services WHERE UserID = ".$userid;
  $mysqli = new mysqli(db::dbserver, db::dbuser, db::dbpass, db::dbname);

  $rs = $mysqli->query($sql);
  while($row = $rs->fetch_assoc()) {
    $dbdata = array(
      "ServiceID" => $row["ServiceID"],
      "ServiceName" => $row['ServiceName']
    );

    $data[] = $dbdata;
  }
  $rs->free();
  $mysqli->close();

  $data = array("message" => "", "services" => $data, "success" => true);
  return json_encode($data);
} //getServices

function getCustomers($userid) {
  $data = "";
  $dbdata = "";

  $sql = "SELECT CustomerID, CustomerName FROM tbl_customers WHERE UserID = ".$userid;
  $mysqli = new mysqli(db::dbserver, db::dbuser, db::dbpass, db::dbname);

  $rs = $mysqli->query($sql);
  while($row = $rs->fetch_assoc()) {
    $dbdata = array(
      "CustomerID" => $row["CustomerID"],
      "CustomerName" => $row['CustomerName']
    );

    $data[] = $dbdata;
  }
  $rs->free();
  $mysqli->close();

  $data = array("message" => "", "customers" => $data, "success" => true);
  return json_encode($data);
} //getCustomers

function getNewWeeklyID($userid) {
  $userid = convertForInsert($userid);

  $sql = "INSERT INTO `tbl_timesheet` (WeeklyID, UserID) VALUES (NULL, ".$userid.");";
  $mysqli = new mysqli(db::dbserver, db::dbuser, db::dbpass, db::dbname);

  $mysqli->query($sql);
  $insertid = $mysqli->insert_id;

  $mysqli->close();

  $data = array("message" => "", "weeklyid" => $insertid, "success" => true);
  return json_encode($data);
} //getNewWeeklyID

function addCustomer($userid, $customername) {
  $response = "";
  $insertid = "";

  $userid = convertForInsert($userid);
  $customername = convertForInsert($customername);

  $sql = "INSERT INTO `tbl_customers` (CustomerID, CustomerName, UserID, HourlyRate) VALUES (NULL, ".$customername.", ".$userid.", NULL);";
  $mysqli = new mysqli(db::dbserver, db::dbuser, db::dbpass, db::dbname);

  $mysqli->query($sql);
  $insertid = $mysqli->insert_id;

  $mysqli->close();

  return $sql;
} //addCustomer

function getTimes($userid, $date) {
  $data = "";
  $currentrecord = "";
  $dbdata = "";

  $m = date('Y-m-d',strtotime($date));
  $tu = date('Y-m-d',strtotime($date . "+1 days"));
  $w = date('Y-m-d',strtotime($date . "+2 days"));
  $th = date('Y-m-d',strtotime($date . "+3 days"));
  $f = date('Y-m-d',strtotime($date . "+4 days"));
  $sa = date('Y-m-d',strtotime($date . "+5 days"));
  $su = date('Y-m-d',strtotime($date . "+6 days"));

  $sql = "
    SELECT
      a.WeeklyID,
      a.Description,
      a.ServiceName,
      a.CustomerName,
      b.ServiceID,
      #b.ServiceName,
      b.HourlyRate,
      c.CustomerID,
      #c.CustomerName,
      d.TimeID,
      Date_Format(d.HourDate,'%Y-%m-%d') AS HourDate,
      d.Hours
    FROM tbl_timesheet a
    LEFT JOIN tbl_services b
      ON b.ServiceID = a.ServiceID
    LEFT JOIN tbl_customers c
      ON c.CustomerID = a.CustomerID
    LEFT JOIN tbl_time d
      ON d.WeeklyID = a.WeeklyID
    WHERE a.UserID = '".$userid."'
      AND d.HourDate BETWEEN '".$m."' AND '".$su."'
    ";
  $mysqli = new mysqli(db::dbserver, db::dbuser, db::dbpass, db::dbname);

  $rs = $mysqli->query($sql);
  while($row = $rs->fetch_assoc()) {
    if($currentrecord != $row['WeeklyID']) {
      $currentrecord = $row['WeeklyID'];
      ($dbdata != "" ? $data[] = $dbdata : "");

      $dbdata = array(
          "WeeklyID" => $currentrecord,
          "Description" => $row['Description'],
          "ServiceID" => $row['ServiceID'],
          "Service" => $row['ServiceName'],
          "CustomerID" => $row['CustomerID'],
          "Customer" => $row['CustomerName'],
          "HourlyRate" => $row['HourDate']
      );
    }
    switch ($row['HourDate']) {
      case $m:
        $day = "M"; break;
      case $tu:
        $day = "Tu"; break;
      case $w:
        $day = "W"; break;
      case $th:
        $day = "Th"; break;
      case $f:
        $day = "F"; break;
      case $sa:
        $day = "Sa"; break;
      case $su:
        $day = "Su"; break;
    }
    $dbdata[$day] = $row['Hours'];
    $dbdata[$day."Date"] = $row['HourDate'];
    $dbdata[$day."TimeID"] = $row['TimeID'];
  }
  ($dbdata != "" ? $data[] = $dbdata : ""); //because of the way the array is pushed, the records will end before the loop can push it

  $rs->free();
  $mysqli->close();

  $data = array("success" => true, "records" => $data);
  return json_encode($data);
} //getTimes

function createUser($username, $useremail, $userpassword, $firstname, $lastname) {

} //createUser

function getUserIDFromToken($usertoken) {
  $userid = "";
  $usertoken = convertForInsert($usertoken);

  $sql = "SELECT UserID from tbl_users WHERE UserToken = ".$usertoken;
  $mysqli = new mysqli(db::dbserver, db::dbuser, db::dbpass, db::dbname);

  $rs = $mysqli->query($sql);
  if($rs->num_rows < 1 || $usertoken == "") {
    //we don't have this token, return to login
    return false;
  } else {
    while ($row = $rs->fetch_assoc()) {
      $userid = $row['UserID'];
    }
  }
  $rs->free();
  $mysqli->close();

  return $userid;
} //getUserIDFromToken

function generateToken($userid) {
  $token = bin2hex(openssl_random_pseudo_bytes(32));

  if($userid == "") {
    return $token;
  }

  $sql = "UPDATE tbl_users SET UserToken = ".convertForInsert($token)." WHERE UserID = ".convertForInsert($userid);
  $mysqli = new mysqli(db::dbserver, db::dbuser, db::dbpass, db::dbname);

  $mysqli->query($sql);
  $mysqli->close();

  return $token;
} //generateToken

function convertForInsert($str) {
  if ($str != "") {
    $str = "'".$str."'";
  } else {
    $str = "NULL";
  }
  return $str;
} //convertForInsert

function convertToCurrency($amt) {
  if($amt == "" || $amt == NULL || $amt == "NULL") {
    $amt = "0.00";
  }

  $amt = round($amt, 2);

  if(strpos($amt, ".") == false) {
    $amt = $amt.".00";
  }

//  if(strpos($amt, "$") == false) {
//    $amt = "$".$amt;
//  }

  if(strlen($amt) - strpos($amt, ".") == 2) {
    //add a 0 to the end
    $amt = $amt."0";
  }

  return $amt;
} //convertToCurrency


function getTasks() {
  $tasks = "";
  $dbdata = "";

  $sql = "SELECT * FROM tbl_tasks";
  $mysqli = new mysqli(db::dbserver, db::dbuser, db::dbpass, db::dbname);

  $rs = $mysqli->query($sql);
  while($row = $rs->fetch_assoc()) {
    $dbdata['TaskID'] = $row['TaskID'];
    $dbdata['TaskDescription'] = $row['TaskDescription'];
    $tasks[] = $dbdata;
  }

  $rs->free();
  $mysqli->close();

  $data = array("success" => true, "records" => $tasks);
  return json_encode($data);
} //getTasks

function addTask($description) {
  $description = convertForInsert($description);

  $sql = "INSERT INTO `tbl_tasks` (TaskID, TaskDescription) VALUES (NULL, ".$description.");";
  $mysqli = new mysqli(db::dbserver, db::dbuser, db::dbpass, db::dbname);

  $mysqli->query($sql);
  $insertid = $mysqli->insert_id;

  $mysqli->close();

  $data = array("success" => true, "id" => $insertid);
  return json_encode($data);
} //addCustomer

function generateSalt($userid) {
  $randsalt = bin2hex(openssl_random_pseudo_bytes(4));
  //if we have to generate salt, we need to update it as well
  $sql = "UPDATE tbl_users SET Salt = '".$randsalt."' WHERE UserID = '".$userid."'";
  $mysqli = new mysqli(db::dbserver, db::dbuser, db::dbpass, db::dbname);

  $mysqli->query($sql);
  $mysqli->close();

  return $randsalt;
} //generateToken

function encryptPassword($pass, $salt) {
  $encrypt = hash('sha256', $pass.$salt);
  return $encrypt;
} //generateToken

function updatePassword($pass, $userid) {
  $sql = "UPDATE tbl_users SET Password = '".$pass."' WHERE UserID = '".$userid."'";
  $mysqli = new mysqli(db::dbserver, db::dbuser, db::dbpass, db::dbname);

  $mysqli->query($sql);
  $mysqli->close();

  return true;
} //generateToken
#######################################################################################################################
####################################################END FUNCTIONS######################################################
#######################################################################################################################

//<!--<a href="#">Inbox <span class="badge">42</span></a>-->
//<!---->
//<!--<button class="btn btn-primary" type="button">-->
//<!--  Messages <span class="badge">4</span>-->
//<!--</button>-->
//<!--<button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#myModal">-->
//<!--  Launch demo modal-->
//<!--</button>-->
//<!---->
//<!--<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal" data-whatever="@123">Open modal for @123</button>-->
//<!--<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal" data-whatever="@open">Open modal for @open</button>-->
//<!--<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal" data-whatever="@getbootstrap">Open modal for @getbootstrap</button>-->
?>
