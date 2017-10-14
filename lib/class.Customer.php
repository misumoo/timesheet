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
 * TODO: Separate the model functions to another class. CustomerModel
 */
class Customer {
  private $customerID = "";
  private $customerName = "";
  private $addr = "";
  private $addr2 = "";
  private $city = "";
  private $state = "";
  private $zip = "";
  private $phone = "";
  private $userID = "";

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
      $this->setCustomerID($row['CustomerID']);
      $this->setCustomerName($row['CustomerName']);
      $this->setAddr($row['Addr1']);
      $this->setAddr2($row['Addr2']);
      $this->setCity($row['City']);
      $this->setState($row['State']);
      $this->setZip($row['Zip']);
      $this->setPhone($row['Phone']);
      $this->setUserID($row['UserID']);
    }

    $rs->free();
    $mysqli->close();
  }

  public function updateCustomer() {
    $sql = "
      UPDATE tbl_customers SET 
        CustomerName = ".convertForInsert($this->getCustomerName()).",
        Addr1 = ".convertForInsert($this->getAddr()).",
        Addr2 = ".convertForInsert($this->getAddr2()).",
        City = ".convertForInsert($this->getCity()).",
        State = ".convertForInsert($this->getState()).",
        Zip = ".convertForInsert($this->getZip()).",
        Phone = ".convertForInsert($this->getPhone())."
      WHERE UserID = ".convertForInsert($this->getUserID())."
      AND CustomerID = ".convertForInsert($this->getCustomerID());

    $mysqli = new mysqli(Database::dbserver, Database::dbuser, Database::dbpass, Database::dbname);
    $mysqli->query($sql);
    $mysqli->close();
  }

  /**
   * @return string
   */
  public function getCustomerID()
  {
    return $this->customerID;
  }

  /**
   * @param string $customerID
   */
  public function setCustomerID($customerID)
  {
    $this->customerID = $customerID;
  }

  /**
   * @return string
   */
  public function getCustomerName()
  {
    return $this->customerName;
  }

  /**
   * @param string $customerName
   */
  public function setCustomerName($customerName)
  {
    $this->customerName = $customerName;
  }

  /**
   * @return string
   */
  public function getAddr()
  {
    return $this->addr;
  }

  /**
   * @param string $addr
   */
  public function setAddr($addr)
  {
    $this->addr = $addr;
  }

  /**
   * @return string
   */
  public function getAddr2()
  {
    return $this->addr2;
  }

  /**
   * @param string $addr2
   */
  public function setAddr2($addr2)
  {
    $this->addr2 = $addr2;
  }

  /**
   * @return string
   */
  public function getCity()
  {
    return $this->city;
  }

  /**
   * @param string $city
   */
  public function setCity($city)
  {
    $this->city = $city;
  }

  /**
   * @return string
   */
  public function getState()
  {
    return $this->state;
  }

  /**
   * @param string $state
   */
  public function setState($state)
  {
    $this->state = $state;
  }

  /**
   * @return string
   */
  public function getZip()
  {
    return $this->zip;
  }

  /**
   * @param string $zip
   */
  public function setZip($zip)
  {
    $this->zip = $zip;
  }

  /**
   * @return string
   */
  public function getPhone()
  {
    return $this->phone;
  }

  /**
   * @param string $phone
   */
  public function setPhone($phone)
  {
    $this->phone = $phone;
  }

  /**
   * @return string
   */
  public function getUserID()
  {
    return $this->userID;
  }

  /**
   * @param string $userID
   */
  public function setUserID($userID)
  {
    $this->userID = $userID;
  }
}