<?php
/**
 * Created by PhpStorm.
 * User: Misumoo
 * Date: 5/7/2015
 * Time: 12:11 PM
 */

session_start();

class userInfo {
  public $userfirstname;
  public $usertoken;
}

require 'class.Database.php';
require 'class.Email.php';
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
} //task getCustomers

if($task == "addCustomer") {
  $customername = $request->customername;
  echo addCustomer($userid, $customername);
} //task addCustomer

if($task == "addService") {
  $servicename = $request->servicename;
  $hourlyrate = $request->hourlyrate;
  echo addService($userid, $servicename, $hourlyrate);
} //task addService

if($task == "insertNewTime") {
  $customerid = $request->customerid;
  $serviceid = $request->serviceid;
  $hours = $request->hours;
  $date = $request->date;
  $desc = $request->desc;
  $weeklyid = getNewWeeklyID($userid);
  echo insertNewTime($userid, $customerid, $serviceid, $hours, $date, $desc, $weeklyid);
} //task insertNewTime

if($task == "getTimes") {
  $date = $request->date;
  echo getTimes($userid, $date);
} //task getTimes

if($task == "deleteRow") {
  $weeklyid = $request->weeklyid;
  $data = deleteRow($userid, $weeklyid);
  echo json_encode($data);
} //task deleteRow

if($task == "deleteSingle") {
  $timeid = $request->timeid;
  $data = deleteSingle($userid, $timeid);
  echo json_encode($data);
} //task deleteSingle

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
  $serviceid = $request->serviceid;
  $weeklyid = $request->weeklyid;
  $data = saveService("", $serviceid, $weeklyid, $userid);
  echo $data;
} //saveService

if($task == "saveCustomer") {
  $customerid = $request->customerid;
  $weeklyid = $request->weeklyid;
  $data = saveCustomer("", $customerid, $weeklyid, $userid);
  echo $data;
} //saveCustomer

if($task == "getNewWeeklyID") {
  $data = getNewWeeklyID($userid);
  $data = array("message" => "", "weeklyid" => $data, "success" => true);
  return json_encode($data);
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

if($task == "getAllEntries") {
  $billed = true;
  $data = getAllEntries($userid, $billed);
  echo $data;
} //task getAllEntries

if($task == "loadAllInvoices") {
  $data = loadAllInvoices($userid);
  echo $data;
} //task loadAllInvoices

if($task == "generateInvoiceNumber") {
  $data = generateInvoiceNumber($userid);
  echo $data;
} //task generateInvoiceNumber

if($task == "saveInvoice") {
  $invoiceid = $request->invoiceid;
  $timeids = $request->timeids;
  $data = saveInvoice($userid, $invoiceid, $timeids);
  echo $data;
} //task saveInvoice
#######################################################################################################################
#####################################################END TASKS#########################################################
#######################################################################################################################

/**
 * @param $userid
 * @param $service
 * @return object
 * Look up a service id by name.
 */
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
  $mysqli = new mysqli(Database::dbserver, Database::dbuser, Database::dbpass, Database::dbname);

  $rs = $mysqli->query($sql);
  while($row = $rs->fetch_assoc()) {
    $serviceid = $row['ServiceID'];
  }
  $rs->free();
  $mysqli->close();

  $data = array("message" => "Good", "ServiceID" => $serviceid, "success" => true, "sql" => $sql);
  return json_encode($data);
}

/**
 * @param $description
 * @param $weeklyid
 * @param $userid
 * @return object
 * Update a description.
 */
function saveDescription($description, $weeklyid, $userid) {
  $task = "update";

  $description = convertForInsert($description);
  $weeklyid = convertForInsert($weeklyid);
  $userid = convertForInsert($userid);

  if($task == "update") {
    $sql = "UPDATE `tbl_timesheet` SET Description = ".$description." WHERE WeeklyID = ".$weeklyid." AND UserID = ".$userid.";";
  }

  $mysqli = new mysqli(Database::dbserver, Database::dbuser, Database::dbpass, Database::dbname);

  $mysqli->query($sql);
  $mysqli->close();

  $data = array("message" => $task, "sql" => $sql);
  return json_encode($data);
} //saveDescription

/**
 * @param $userid
 * @param $invoiceid
 * @param $timeids
 * @return object
 * Saves our invoice, this will update our times to set invoice id
 */
function saveInvoice($userid, $invoiceid, $timeids) {
  $task = "update";

  $invoiceid = convertForInsert($invoiceid);
  $userid = convertForInsert($userid);
  //$timeids = convertForInsert($timeids);

  if($task == "update") {
    $sql = "UPDATE `tbl_time` SET InvoiceID = ".$invoiceid." WHERE UserID = " . $userid . " AND TimeID IN (" . $timeids . ");";
  }

  $mysqli = new mysqli(Database::dbserver, Database::dbuser, Database::dbpass, Database::dbname);

  $mysqli->query($sql);
  $mysqli->close();

  $data = array("message" => $task, "sql" => $sql);
  return json_encode($data);
}

/**
 * @param $service
 * @param $serviceid
 * @param $weeklyid
 * @param $userid
 * @return object
 * Update a service.
 */
function saveService($service, $serviceid, $weeklyid, $userid) {
  $service = convertForInsert($service);
  $serviceid = convertForInsert($serviceid);
  $weeklyid = convertForInsert($weeklyid);
  $userid = convertForInsert($userid);

  if($serviceid == NULL) {
    $sql = "UPDATE `tbl_timesheet` SET ServiceName = ".$service." WHERE WeeklyID = ".$weeklyid." AND UserID = ".$userid.";";
  } else {
    $sql = "UPDATE `tbl_timesheet` SET ServiceID = ".$serviceid." WHERE WeeklyID = ".$weeklyid." AND UserID = ".$userid.";";
  }

  $mysqli = new mysqli(Database::dbserver, Database::dbuser, Database::dbpass, Database::dbname);

  $mysqli->query($sql);
  $mysqli->close();

  $data = array("sql" => $sql);
  return json_encode($data);
} //saveService

/**
 * @param $customer
 * @param $customerid
 * @param $weeklyid
 * @param $userid
 * @return object
 * Saves a customer.
 */
function saveCustomer($customer, $customerid, $weeklyid, $userid) {
  $customer = convertForInsert($customer);
  $customerid = convertForInsert($customerid);
  $weeklyid = convertForInsert($weeklyid);
  $userid = convertForInsert($userid);

  if($customerid == NULL) {
    $sql = "UPDATE `tbl_timesheet` SET CustomerName = ".$customer." WHERE WeeklyID = ".$weeklyid." AND UserID = ".$userid.";";
  } else {
    $sql = "UPDATE `tbl_timesheet` SET CustomerID = ".$customerid." WHERE WeeklyID = ".$weeklyid." AND UserID = ".$userid.";";
  }

  $mysqli = new mysqli(Database::dbserver, Database::dbuser, Database::dbpass, Database::dbname);

  $mysqli->query($sql);
  $mysqli->close();

  $data = array("sql" => $sql);
  return json_encode($data);
} //saveCustomer

/**
 * @param $weeklyid
 * @param $userid
 * @param $date
 * @param $hours
 * @param $timeid
 * @return object
 * The time control. Any updating that needs to be done this will do it. Add, insert, delete and update.
 */
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

  $mysqli = new mysqli(Database::dbserver, Database::dbuser, Database::dbpass, Database::dbname);

  $mysqli->query($sql);
  $insertid = $mysqli->insert_id;

  $mysqli->close();

  $data = array("message" => $timetask, "id" => $insertid);
  return json_encode($data);
} //timeControl

/**
 * @param $email
 * @return object
 * @throws phpmailerException
 * Initiate a password reset. This will email the user with a token thrown in the database to allow them to reset.
 */
function resetInit($email) {
  $success = true;
  $msg = "";
  $name = "TODO";
  $resetcode = strtoupper(bin2hex(openssl_random_pseudo_bytes(4)));

  $sql = "UPDATE tbl_users_reset SET Expired = 1 WHERE Email = ".convertForInsert($email).";
          INSERT INTO tbl_users_reset
          (ResetID, Email, ResetTimeStamp, IPAddress, ResetCode) VALUES
          (NULL, ".convertForInsert($email).", NULL, ".convertForInsert($_SERVER['REMOTE_ADDR']).", ".convertForInsert($resetcode).")";
  $mysqli = new mysqli(Database::dbserver, Database::dbuser, Database::dbpass, Database::dbname);

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
//  $mail->SMTPDebug = 3;                  // Enable verbose debug output
//  $mail->SMTPDebug = 1;

  $mail->isSMTP();                         // Set mailer to use SMTP
//  $mail->Mailer = 'smtp';
//  $mail->Host = 'smtp.gmail.com';        // Specify main and backup SMTP servers
//  $mail->SMTPAuth = true;                // Enable SMTP authentication
//  $mail->Username = Email::USER_NAME;    // SMTP username
//  $mail->Password = Email::PASSWORD;     // SMTP password
//  $mail->SMTPSecure = 'tls';             // Enable TLS encryption, `ssl` also accepted
//  $mail->Port = 587;                     // TCP port to connect to

  $mail->SetFrom(Email::USER_NAME, 'Timesheet');

  $mail->From = Email::USER_NAME;
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
} //resetInit

/**
 * @param $email
 * @param $confirmationcode
 * @param $password
 * @return object
 */
function resetDo($email, $confirmationcode, $password) {
  $success = true;
  $msg = "";
  $userid = "";
  $reset = false;

  $sql = "SELECT * FROM tbl_users_reset WHERE Email = ".convertForInsert($email)." AND Expired = '0'";
  $mysqli = new mysqli(Database::dbserver, Database::dbuser, Database::dbpass, Database::dbname);
  $rs = $mysqli->query($sql);
  while($row = $rs->fetch_assoc()) {
    if($row['ResetCode'] == trim($confirmationcode)) {
      $reset = true;
    }
  }

  if($reset) {
    $sql = "SELECT UserID FROM tbl_users WHERE Email = ".convertForInsert($email);
    $mysqli = new mysqli(Database::dbserver, Database::dbuser, Database::dbpass, Database::dbname);
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
} //resetDo

/**
 * @param $firstname
 * @param $lastname
 * @param $email
 * @param $userpass
 * @return bool|object
 * Register a new user.
 */
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
  $mysqli = new mysqli(Database::dbserver, Database::dbuser, Database::dbpass, Database::dbname);

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
    $mysqli = new mysqli(Database::dbserver, Database::dbuser, Database::dbpass, Database::dbname);

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

/**
 * @param $username
 * @param $userpass
 * @return bool|object
 * Login.
 */
function login($username, $userpass) {
  if($username == "" || $userpass == "") {
    return false;
  }
  $salt = "";

  $sql = "SELECT Salt, UserID FROM tbl_users WHERE Email = ".convertForInsert($username);
  $mysqli = new mysqli(Database::dbserver, Database::dbuser, Database::dbpass, Database::dbname);
  $rs = $mysqli->query($sql);
  while($row = $rs->fetch_assoc()) {
    $userid = $row['UserID'];
    $salt = ($row['Salt'] == "" ? generateSalt($userid) : $row['Salt']);
  }

  $salted = encryptPassword($userpass, $salt);

  $rs->free();
  $mysqli->close();

  $sql = "SELECT UserID, FirstName FROM tbl_users WHERE Email = ".convertForInsert($username)." AND Password = ".convertForInsert($salted);
  $mysqli = new mysqli(Database::dbserver, Database::dbuser, Database::dbpass, Database::dbname);

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

/**
 * @param $userid
 * @return object
 * Get array of services.
 */
function getServices($userid) {
  $data = "";
  $msg = "";
  $dbdata = "";
  $cancelprocess = false;

  if($userid == "") {
    $msg = "No userid or token";
    $cancelprocess = true;
  }

  if(!$cancelprocess) {
    $sql = "SELECT ServiceID, ServiceName FROM tbl_services WHERE UserID = " . $userid;
    $mysqli = new mysqli(Database::dbserver, Database::dbuser, Database::dbpass, Database::dbname);

    $rs = $mysqli->query($sql);
    while ($row = $rs->fetch_assoc()) {
      $dbdata = array(
        "ServiceID" => $row["ServiceID"],
        "ServiceName" => $row['ServiceName']
      );

      $data[] = $dbdata;
    }
    $rs->free();
    $mysqli->close();
  }

  $data = array("message" => $msg, "services" => $data, "success" => true);
  return json_encode($data);
} //getServices

/**
 * @param $userid
 * @return object
 * Get array of customers.
 */
function getCustomers($userid) {
  $data = "";
  $msg = "";
  $dbdata = "";
  $cancelprocess = false;

  if($userid == "") {
    $msg = "No userid or token";
    $cancelprocess = true;
  }

  if(!$cancelprocess) {
    $sql = "SELECT CustomerID, CustomerName FROM tbl_customers WHERE UserID = ".$userid;
    $mysqli = new mysqli(Database::dbserver, Database::dbuser, Database::dbpass, Database::dbname);

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
  }

  $data = array("message" => $msg, "customers" => $data, "success" => true);
  return json_encode($data);
} //getCustomers

/**
 * @param $userid
 * @return mixed
 * Generate a new weekly ID for this user.
 */
function getNewWeeklyID($userid) {
  $mysqli = new mysqli(Database::dbserver, Database::dbuser, Database::dbpass, Database::dbname);

  $userid = convertForInsert($userid);

  $sql = "INSERT INTO `tbl_timesheet` (WeeklyID, UserID) VALUES (NULL, ".$userid.");";

  $mysqli->query($sql);
  $insertid = $mysqli->insert_id;

  $mysqli->close();

  return $insertid;
} //getNewWeeklyID

/**
 * @param $userid
 * @param $customername
 * @return mixed
 * Add a Customer to the database.
 */
function addCustomer($userid, $customername) {
  $mysqli = new mysqli(Database::dbserver, Database::dbuser, Database::dbpass, Database::dbname);

  $userid = convertForInsert($userid);
  $customername = convertForInsert($customername);

  $sql = "INSERT INTO `tbl_customers` (CustomerID, CustomerName, UserID) VALUES (NULL, ".$customername.", ".$userid.");";

  $mysqli->query($sql);
  $insertid = $mysqli->insert_id;

  $mysqli->close();

  return $insertid;
} //addCustomer

/**
 * @param $userid
 * @param $servicename
 * @param $hourlyrate
 * @return mixed
 * Add a service to the database.
 */
function addService($userid, $servicename, $hourlyrate) {
  $mysqli = new mysqli(Database::dbserver, Database::dbuser, Database::dbpass, Database::dbname);

  $userid = convertForInsert($userid);
  $servicename = convertForInsert($servicename);
  $hourlyrate = convertForInsert($hourlyrate);

  $sql = "INSERT INTO `tbl_services` (ServiceID, ServiceName, UserID, HourlyRate) VALUES (NULL, ".$servicename.", ".$userid.", ".$hourlyrate.");";

  $mysqli->query($sql);
  $insertid = $mysqli->insert_id;

  $mysqli->close();

  return $insertid;
} //addService

/**
 * @param $userid
 * @param $customerid
 * @param $serviceid
 * @param $hours
 * @param $date
 * @param $desc
 * @param $weeklyid
 * @return object
 * Add a new time to the database.
 */
function insertNewTime($userid, $customerid, $serviceid, $hours, $date, $desc, $weeklyid) {
  $savedesc = saveDescription($desc, $weeklyid, $userid);
  $savecustomer = saveCustomer("", $customerid, $weeklyid, $userid);
  $saveservice = saveService("", $serviceid, $weeklyid, $userid);

  $userid = convertForInsert($userid);
  $hours = convertForInsert($hours);
  $date = convertForInsert($date);


  $sql = "INSERT INTO `tbl_time` (TimeID, UserID, Hours, HourDate, WeeklyID) VALUES (NULL, ".$userid.", ".$hours.", ".$date.", ".$weeklyid.");";
  $mysqli = new mysqli(Database::dbserver, Database::dbuser, Database::dbpass, Database::dbname);

  $mysqli->query($sql);
  $insertid = $mysqli->insert_id;

  $mysqli->close();

  $data = array("inserid" => $insertid, "savecustomer" => $savecustomer, "saveservice" => $saveservice, "savedesc" => $savedesc);
  return json_encode($data);
} //insertNewTime

/**
 * @param $userid
 * @param $date
 * @return object
 * Returns all of our times for the timesheet.
 */
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
      b.ServiceID,
      b.ServiceName,
      b.HourlyRate,
      c.CustomerID,
      c.CustomerName,
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
  $mysqli = new mysqli(Database::dbserver, Database::dbuser, Database::dbpass, Database::dbname);

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

/**
 * @param $userid
 * @return object
 * This will return all of a users time entries.
 */
function getAllEntries($userid, $billed) {
  $data = "";

  $sql = "
    SELECT
      a.WeeklyID,
      a.Description,
      b.ServiceID,
      b.ServiceName,
      b.HourlyRate,
      c.CustomerID,
      c.CustomerName,
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
    ";

  $mysqli = new mysqli(Database::dbserver, Database::dbuser, Database::dbpass, Database::dbname);
  $rs = $mysqli->query($sql);

  while($row = $rs->fetch_assoc()) {
    $dbdata = array(
      "TimeID" => $row['TimeID'],
      "WeeklyID" => $row['WeeklyID'],
      "Description" => $row['Description'],
      "ServiceID" => $row['ServiceID'],
      "Service" => $row['ServiceName'],
      "CustomerID" => $row['CustomerID'],
      "Customer" => $row['CustomerName'],
      "TimeDate" => $row['HourDate'],
      "Hours" => $row['Hours'],
      "InvoiceNumber" => NULL,
    );

    $data[] = $dbdata; //push our data into the $data object
  }

  $rs->free();
  $mysqli->close();

  $data = array("success" => true, "records" => $data);
  return json_encode($data);
}

/**
 * @param $userid
 * @return object
 * Load all of the invoices from a user
 */
function loadAllInvoices($userid) {
  $data = "";

  $sql = "
    SELECT
      *
    FROM tbl_invoices
    WHERE UserID = '".$userid."'
    ";

  $mysqli = new mysqli(Database::dbserver, Database::dbuser, Database::dbpass, Database::dbname);
  $rs = $mysqli->query($sql);

  while($row = $rs->fetch_assoc()) {
    $dbdata = array(
      "InvoiceID" => $row['InvoiceID'],
      "InvoiceNumber" => $row['InvoiceNumber'],
    );

    $data[] = $dbdata; //push our data into the $data object
  }

  $rs->free();
  $mysqli->close();

  $data = array("success" => true, "records" => $data);
  return json_encode($data);
}

/**
 * @param $userid
 * @param $weeklyid
 * @return object
 * Delete a row, WeeklyID
 */
function deleteRow($userid, $weeklyid) {
  $mysqli = new mysqli(Database::dbserver, Database::dbuser, Database::dbpass, Database::dbname);
  $weeklyid = convertForInsert($weeklyid);
  $userid = convertForInsert($userid);

  $sql = "DELETE FROM tbl_time WHERE UserID = ".$userid." AND WeeklyID = ".$weeklyid;
  $rs = $mysqli->query($sql);
//  $rs->free();

  $sql = "DELETE FROM tbl_timesheet WHERE UserID = ".$userid." AND WeeklyID = ".$weeklyid;
  $rs = $mysqli->query($sql);
//  $rs->free();

  $data = array("success" => true);
  return $data;
} //deleteRow

/**
 * @param $userid
 * @param $timeid
 * @return object
 * Deletes a single record from our time table.
 */
function deleteSingle($userid, $timeid) {
  $mysqli = new mysqli(Database::dbserver, Database::dbuser, Database::dbpass, Database::dbname);
  $timeid = convertForInsert($timeid);
  $userid = convertForInsert($userid);

  $sql = "DELETE FROM tbl_time WHERE UserID = ".$userid." AND TimeID = ".$timeid;
  $rs = $mysqli->query($sql);
//  $rs->free();

  //$sql = "DELETE FROM tbl_timesheet WHERE UserID = ".$userid." AND WeeklyID = ".$weeklyid;
  //$rs = $mysqli->query($sql);
//  $rs->free();

  $data = array("success" => true, "TimeID" => $timeid);
  return $data;
} //deleteSingle

/**
 * @param $username
 * @param $useremail
 * @param $userpassword
 * @param $firstname
 * @param $lastname
 * TODO: Create User. Actually not sure if this is needed as we have a register user already.
 */
function createUser($username, $useremail, $userpassword, $firstname, $lastname) {

} //createUser

/**
 * @param $usertoken
 * @return bool|string
 * Get our UserID from our token. Token is generated on login and stored in cookies.
 */
function getUserIDFromToken($usertoken) {
  $userid = "";
  $usertoken = convertForInsert($usertoken);

  $sql = "SELECT UserID from tbl_users WHERE UserToken = ".$usertoken;
  $mysqli = new mysqli(Database::dbserver, Database::dbuser, Database::dbpass, Database::dbname);

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

/**
 * @param $userid
 * @return string
 * Generate a token to store in the database.
 */
function generateToken($userid) {
  $mysqli = new mysqli(Database::dbserver, Database::dbuser, Database::dbpass, Database::dbname);
  $token = bin2hex(openssl_random_pseudo_bytes(32));

  if($userid == "") {
    return $token;
  }

  $sql = "UPDATE tbl_users SET UserToken = ".convertForInsert($token)." WHERE UserID = ".convertForInsert($userid);

  $mysqli->query($sql);
  $mysqli->close();

  return $token;
} //generateToken

/**
 * @param $str
 * @return string
 * Converts a string for insert. Escapes it, puts quotes around it. Nulls if it is nothing.
 */
function convertForInsert($str) {
  $mysqli = new mysqli(Database::dbserver, Database::dbuser, Database::dbpass, Database::dbname);

  if ($str != "") {
    $str = "'".$mysqli->real_escape_string($str)."'";
  } else {
    $str = "NULL";
  }
  return $str;
} //convertForInsert

/**
 * @param $amt
 * @return float|string
 * Convert string/float to currency (***.**)
 */
function convertToCurrency($amt) {
  if($amt == "" || $amt == NULL || $amt == "NULL") {
    $amt = "0.00";
  }

  $amt = round($amt, 2);

  if(strpos($amt, ".") == false) {
    $amt = $amt.".00";
  }

  if(strlen($amt) - strpos($amt, ".") == 2) {
    //add a 0 to the end
    $amt = $amt."0";
  }

  return $amt;
} //convertToCurrency

/**
 * @return object
 * Get task list from database.
 */
function getTasks() {
  $tasks = "";
  $dbdata = "";

  $sql = "SELECT * FROM tbl_tasks";
  $mysqli = new mysqli(Database::dbserver, Database::dbuser, Database::dbpass, Database::dbname);

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

/**
 * @param $description
 * @return object
 * Add a task to the database.
 */
function addTask($description) {
  $description = convertForInsert($description);

  $sql = "INSERT INTO `tbl_tasks` (TaskID, TaskDescription) VALUES (NULL, ".$description.");";
  $mysqli = new mysqli(Database::dbserver, Database::dbuser, Database::dbpass, Database::dbname);

  $mysqli->query($sql);
  $insertid = $mysqli->insert_id;

  $mysqli->close();

  $data = array("success" => true, "id" => $insertid);
  return json_encode($data);
} //addTask

/**
 * @param $userid
 * @return object
 *
 */
function generateInvoiceNumber($userid) {
  $userid = convertForInsert($userid);

  $sql = "INSERT INTO `tbl_invoices` (InvoiceID, UserID) VALUES (NULL, ".$userid.");";
  $mysqli = new mysqli(Database::dbserver, Database::dbuser, Database::dbpass, Database::dbname);

  $mysqli->query($sql);
  $insertid = $mysqli->insert_id;

  $mysqli->close();

  $data = array("success" => true, "invoiceid" => $insertid);
  return json_encode($data);
}

/**
 * @param $userid
 * @return string
 * Generate salt to add to a password before encrypting.
 */
function generateSalt($userid) {
  $randsalt = bin2hex(openssl_random_pseudo_bytes(4));
  //if we have to generate salt, we need to update it as well
  $sql = "UPDATE tbl_users SET Salt = '".$randsalt."' WHERE UserID = '".$userid."'";
  $mysqli = new mysqli(Database::dbserver, Database::dbuser, Database::dbpass, Database::dbname);

  $mysqli->query($sql);
  $mysqli->close();

  return $randsalt;
} //generateToken

/**
 * @param $pass
 * @param $salt
 * @return string
 * Encrypt our password to sha256
 */
function encryptPassword($pass, $salt) {
  $encrypt = hash('sha256', $pass.$salt);
  return $encrypt;
} //encryptPassword

/**
 * @param $pass
 * @param $userid
 * @return bool
 * Update our password.
 */
function updatePassword($pass, $userid) {
  $sql = "UPDATE tbl_users SET Password = '".$pass."' WHERE UserID = '".$userid."'";
  $mysqli = new mysqli(Database::dbserver, Database::dbuser, Database::dbpass, Database::dbname);

  $mysqli->query($sql);
  $mysqli->close();

  return true;
} //updatePassword
?>
