/**
 * Created by Misumoo on 10/10/2015.
 */

tsApp.controller('CustomerController', ['$scope', '$cookies', '$http', '$filter', '$location', '$routeParams',
  function($scope, $cookies, $http, $filter, $location, $routeParams) {
    var serviceBase = 'lib/_handle.php';

    $scope.customer = {
      customerid: "",
      customername: "",
      addr1: "",
      addr2: "",
      city: "",
      state: "",
      zip: "",
      phone: ""
    };

    $scope.setup = function() {
      $scope.getAllCustomers();
    };

    $scope.editCustomer = function(idx) {
      var customerid = $scope.pagedItems[$scope.currentPage][idx]["CustomerID"];
      $scope.getSingleCustomerFull(customerid);
      $scope.customer.customername.focus();
    }; //editCustomer

    //Delete a single record, one time id
    $scope.getSingleCustomerFull = function(customerid) {
        $http.post(serviceBase, {
          customerid: customerid,
          task: "getSingleCustomerFull"
        }).success(function(response) {
          if(response.success) {
            console.log(response);
            $scope.customer.customerid = response.records.CustomerID;
            $scope.customer.customername = response.records.CustomerName;
            $scope.customer.addr1 = response.records.Addr1;
            $scope.customer.addr2 = response.records.Addr2;
            $scope.customer.city = response.records.City;
            $scope.customer.state = response.records.State;
            $scope.customer.zip = response.records.Zip;
            $scope.customer.phone = response.records.Phone;
          } else {
            alert("Error getting customer information.");
          }
        }).error(function() {
          alert("Error editing record");
        });
    }; //getSingleCustomerFull

    $scope.deleteCustomer = function() {

    };

    $scope.updateCustomer = function() {
      $http.post(serviceBase, {
        customerid: $scope.customer.customerid,
        customername: $scope.customer.customername,
        addr1: $scope.customer.addr1,
        addr2: $scope.customer.addr2,
        city: $scope.customer.city,
        state: $scope.customer.state,
        zip: $scope.customer.zip,
        phone: $scope.customer.phone,
        task: "updateCustomer"
      }).success(function(response) {
        console.log(response);
        if(response.success) {

        } else {
          alert("Error updating customer information.");
        }
      }).error(function() {
        alert("Error updating record");
      });
    };

    /**
     * Bootstrap/AngularJS sort/filter table
     * http://www.bootply.com/jIEfKezm84
     */
    $scope.getAllCustomers = function() {
      var data = [];
      var cancelprocess = false;

      if(!cancelprocess) {
        $http.post(serviceBase, {
          task: "getAllCustomers"
        }).success(function(response) {
          if(response.message == "No userid or token") {
            //we're not logged in
            console.log("Issue - " + response.message);
            $location.path('/login');
          }

          if(response.success) {
            if(response.records != "") {
              $scope.allCustomers = response.records;
              //This is already thrown a filter as all of that is preprocessed, so this will reset the search allowing all records to display.
            } else {
              $scope.allCustomers = "";
            }
            $scope.search();
          }
        }).error(function() {
          alert("Error retrieving records");
        });
      }
    };

    $scope.sortingOrder = 'TimeDate'; //default sort
    $scope.reverse = true; //Sort ASC by default
    $scope.pageSizes = [5,10,25,50];
    $scope.filteredItems = [];
    $scope.groupedItems = [];
    $scope.itemsPerPage = 10;
    $scope.pagedItems = [];
    $scope.currentPage = 0;
    $scope.allCustomers = "";


    var searchMatch = function (haystack, needle) {
      if (!needle) {
        return true;
      }
      return haystack.toLowerCase().indexOf(needle.toLowerCase()) !== -1;
    };

    // init the filtered items
    $scope.search = function () {
      $scope.filteredItems = $filter('filter')($scope.allCustomers, function (item) {
        for(var attr in item) {
          //our item attr is going to be our value
          //if it is null, this will throw an error. wrapped in a try catch to silently fail.
          try{
            if (searchMatch(item[attr], $scope.query)){
              return true;
            }
          } catch(e) {}
        }
        return false;
      });
      // take care of the sorting order
      if ($scope.sortingOrder !== '') {
        $scope.filteredItems = $filter('orderBy')($scope.filteredItems, $scope.sortingOrder, $scope.reverse);
      }
      $scope.currentPage = 0;
      // now group by pages
      $scope.groupToPages();
    };

    // show items per page
    $scope.perPage = function () {
      $scope.groupToPages();
    };

    // calculate page in place
    $scope.groupToPages = function () {
      $scope.pagedItems = [];

      for (var i = 0; i < $scope.filteredItems.length; i++) {
        if (i % $scope.itemsPerPage === 0) {
          $scope.pagedItems[Math.floor(i / $scope.itemsPerPage)] = [ $scope.filteredItems[i] ];
        } else {
          $scope.pagedItems[Math.floor(i / $scope.itemsPerPage)].push($scope.filteredItems[i]);
        }
      }
    };

    // $scope.deleteItem = function (idx) {
    //   //Time ID
    //   var timeid = $scope.pagedItems[$scope.currentPage][idx]["CustomerID"];
    //
    //   var itemToDelete = $scope.pagedItems[$scope.currentPage][idx];
    //   var idxInItems = $scope.allCustomers.indexOf(itemToDelete);
    //
    //   $scope.deleteSingle(customerid);
    // };
    // $scope.deleteSingle = function(customerid) {
    //   var answer = confirm("Are you sure you want to delete this item?");
    //   if (answer) {
    //     $http.post(serviceBase, {
    //       customerid: customerid,
    //       task: "deleteCustomer"
    //     }).success(function(response) {
    //       if(response.success) {
    //         $scope.getAllCustomers();
    //         $scope.allCustomers.splice(idxInItems,1);
    //         $scope.search();
    //       } else {
    //         alert("Error deleting record");
    //       }
    //     }).error(function() {
    //       alert("Error deleting record");
    //     });
    //   }
    // }; //deleteSingle

    $scope.range = function (start, end) {
      var ret = [];
      if (!end) {
        end = start;
        start = 0;
      }
      for (var i = start; i < end; i++) {
        ret.push(i);
      }
      return ret;
    };

    $scope.prevPage = function () {
      if ($scope.currentPage > 0) {
        $scope.currentPage--;
      }
    };

    $scope.nextPage = function () {
      if ($scope.currentPage < $scope.pagedItems.length - 1) {
        $scope.currentPage++;
      }
    };

    $scope.setPage = function () {
      $scope.currentPage = this.n;
    };

    // functions have been describe process the data for display
    $scope.search();


    // change sorting order
    $scope.sort_by = function(newSortingOrder) {
      if ($scope.sortingOrder == newSortingOrder)
        $scope.reverse = !$scope.reverse;

      $scope.sortingOrder = newSortingOrder;
    };

  }]); //CustomerController