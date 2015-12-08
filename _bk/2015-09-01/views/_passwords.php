<?php
/**
 * Created by PhpStorm.
 * User: Misumoo
 * Date: 8/31/2015
 * Time: 1:06 AM
 */

session_start();

class db {
  const dbserver = "localhost";
  const dbuser = 'creatkd5_chris';
  const dbpass = 'nq#0&mvRlTb;';
  const dbname = 'creatkd5_tspro';
}
class userInfo {
  public $userfirstname;
  public $usertoken;
}

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

$tbody = "";
$thead = "
  <thead>
    <tr>
      <td>User ID</td>
      <td>Email</td>
      <td>Salt</td>
      <td>Old Password</td>
      <td>Password</td>
      <td>Salted</td>
      <td>Match?</td>
    </tr>
  </thead>
  ";

$sql = "SELECT * FROM tbl_users";
$mysqli = new mysqli(db::dbserver, db::dbuser, db::dbpass, db::dbname);

$rs = $mysqli->query($sql);
while($row = $rs->fetch_assoc()) {
  $userid = $row['UserID'];
  $email = $row['Email'];
  $salt = ($row['Salt'] == "" ? generateSalt($userid) : $row['Salt']);
  $oldpass = $row['OldPassword'];
  $password = $row['Password'];
  $salted = encryptPassword($oldpass, $salt);
  updatePassword($salted, $userid);

  $tbody .= "<tr>
      <td>".$userid."</td>
      <td>".$email."</td>
      <td>".$salt."</td>
      <td>".$oldpass."</td>
      <td>".$password."</td>
      <td>".$salted."</td>
      <td>".($salted == $password ? "true" : "false")."</td>
    </tr>";
}
$rs->free();
$mysqli->close();
$tbody = "<tbody>".$tbody."</tbody>";

$table = "<table>".$thead.$tbody."</table>";

echo $table;