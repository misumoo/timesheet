<?php
/**
 * Created by PhpStorm.
 * User: Misumoo
 * Date: 10/10/2017
 * Time: 7:16 PM
 */

/**
 * Class Customer
 * Anything to do with a customer will be done in here.
 * CRUD
 * TODO: Create and Delete
 */
class Customer {
  var $CustomerID = "";
  var $CustomerName = "";
  var $Addr1 = "";
  var $Addr2 = "";
  var $City = "";
  var $State = "";
  var $Zip = "";
  var $Phone = "";
  var $UserID = "";

  public function getCustomerByID($customerid, $userid) {
    $sql = "
      SELECT
        *
      FROM tbl_customers
      WHERE UserID = ".$userid."
      AND CustomerID = ".$customerid."
    ";

    $mysqli = new mysqli(Database::dbserver, Database::dbuser, Database::dbpass, Database::dbname);
    $rs = $mysqli->query($sql);

    while($row = $rs->fetch_assoc()) {
      $this->CustomerID = $row['CustomerID'];
      $this->CustomerName = $row['CustomerName'];
      $this->Addr1 = $row['Addr1'];
      $this->Addr2 = $row['Addr2'];
      $this->City = $row['City'];
      $this->State = $row['State'];
      $this->Zip = $row['Zip'];
      $this->Phone = $row['Phone'];
      $this->UserID = $row['UserID'];
    }

    $rs->free();
    $mysqli->close();
  }

  public function updateCustomer() {
    $sql = "
      UPDATE tbl_customers SET 
        CustomerName = ".convertForInsert($this->CustomerName).",
        Addr1 = ".convertForInsert($this->Addr1).",
        Addr2 = ".convertForInsert($this->Addr2).",
        City = ".convertForInsert($this->City).",
        State = ".convertForInsert($this->State).",
        Zip = ".convertForInsert($this->Zip).",
        Phone = ".convertForInsert($this->Phone)."
      WHERE UserID = ".convertForInsert($this->UserID)."
      AND CustomerID = ".convertForInsert($this->CustomerID);

    $mysqli = new mysqli(Database::dbserver, Database::dbuser, Database::dbpass, Database::dbname);
    $mysqli->query($sql);
    $mysqli->close();
  }
}