/**
 * Created by Misumoo on 4/11/2016.
 */

tsApp.controller('InvoiceController', [ '$scope', '$cookies', '$http', '$filter', '$sce', '$location', '$timeout', '$routeParams',
  function($scope, $cookies, $http, $filter, $sce, $location, $timeout, $routeParams) {

    var serviceBase = 'lib/_handle.php';

    $scope.$on('$locationChangeStart', function( event, newUrl, oldUrl ) {
      if($scope.ReadyToLeave == false && $scope.ReadyToLeave !== 'undefined') {
        var answer = confirm("Unsaved changes detected! Are you sure you want to leave this page?");
        if (!answer) {
          event.preventDefault();
        }
      }
    });

    var invoiceid = $routeParams.invoiceid;
    $scope.invoiceid = (invoiceid != "" && invoiceid != undefined ? invoiceid : "");
    $scope.invoicelist = [];
    $scope.toslice = [];
    $scope.today = ($filter('date')(new Date(), 'MM/dd/yyyy'));
    $scope.todayformatted = ($filter('date')(new Date(), 'yyyy-MM-dd'));
    $scope.invoicetotal = "TODO";

    //TODO: Chris - Add functionality to this. Personal information
    $scope.Company = "";
    $scope.Phone = "";
    $scope.Address1 = "";
    $scope.Address2 = "";
    $scope.City = "";
    $scope.State = "";
    $scope.Zip = "";

    $scope.logo = "";

    $scope.setup = function() {
      $scope.fetchAllTimes();
      $scope.setModalButton();
      $scope.loadAllInvoices();
      $scope.resetClientData();
      if($scope.invoiceid != "") {
        //we have an id! load the information
        $scope.loadInvoice($scope.invoiceid);
      } else {
        //otherwise pop up the dialog to add to invoice
        $scope.toggleModal('dialogAddToInvoice');
      }
    }; //setup

    $scope.trigger = function(index) {
      $scope.testReadyToLeave();
    }; //trigger

    $scope.testReadyToLeave = function() {
      //this function checks all save buttons if enabled, if there is a button enabled we send a message
      dirty = false;
      $timeout(function() {
        // we just need a few ms for inputs to update before we test
        $("button[name='save']").each(function() {
          if(!this.disabled && !dirty) {
            //we have a button enabled
            $scope.ReadyToLeave = false;
            dirty = true;
          }
          if(!dirty) {
            $scope.ReadyToLeave = true;
          }
        });
      }, 2);
    };

    $scope.test = function() {
      console.log("Ping!");
    };

    $scope.save = function() {
      cancelprocess = $scope.invoicelist == "";
      $scope['InvoiceForm'].$setPristine();

      //first we need an invoiceid
      if($scope.invoiceid == "") {
        //we do not have an invoice number, we need to generate one.
        $scope.generateInvoiceNumber();
      } else {
        $scope.saveInvoice();
      }
    };

    //this loops through our current invoice list and gets all time ids, putting a comma between each
    $scope.listInvoiceIds = function() {
      var timeids = "";

      $($scope.invoicelist).each(function() {
        //this lists all of our time id's for our sql statement
        timeids = timeids + (timeids != "" ? "," : "");
        timeids = timeids + this.TimeID;
      });

      return timeids;
    };

    $scope.generateInvoiceNumber = function() {
      $http.post(serviceBase, {
        task: "generateInvoiceNumber"
      }).success(function(response) {
        $scope.trigger();
        if(response.message == "No userid or token") {
          //we're not logged in
          console.log("Issue - " + response.message);
          $location.path('/login');
        }

        if(response.success) {
          if(response.invoiceid != "") {
            $scope.invoiceid = response.invoiceid;
          } else {
            $scope.invoiceid = "";
          }
        }

        console.log(response.sql);

        $scope.saveInvoice();
        $scope.loadAllInvoices();
      }).error(function() {
        alert("Error retrieving records");
      });
    };

    //this calculates the total of our invoice
    $scope.calcTotal = function(){
      var total = 0;
      for(var i = 0; i < $scope.invoicelist.length; i++){
        hourlyrate = parseFloat($scope.invoicelist[i].HourlyRate);
        hours = parseFloat($scope.invoicelist[i].Hours);
        hourlyrate = (isNaN(hourlyrate) ? 0 : hourlyrate); //convert it to a 0 if it's not a number
        hours = (isNaN(hours) ? 0 : hours); //convert it to a 0 if it's not a number
        total += (hourlyrate * hours);
      }
      return parseFloat(total).toFixed(2);
    };

    //this calculates the amount of a single row of our invoice
    $scope.calcAmount = function(index){
      var total = 0;

      hourlyrate = parseFloat($scope.invoicelist[index].HourlyRate);
      hours = parseFloat($scope.invoicelist[index].Hours);
      hourlyrate = (isNaN(hourlyrate) ? 0 : hourlyrate); //convert it to a 0 if it's not a number
      hours = (isNaN(hours) ? 0 : hours); //convert it to a 0 if it's not a number
      total += (hourlyrate * hours);

      return parseFloat(total).toFixed(2);
    };

    $scope.saveInvoice = function() {
      var timeids = "";
      timeids = $scope.listInvoiceIds();

      if($scope.invoiceid == "") {
        alert("An issue occured with saving - no invoice id");
        return false;
      }

      $http.post(serviceBase, {
        task: "saveInvoice",
        invoiceid: $scope.invoiceid,
        timeids: timeids,
        invoicedate: $scope.todayformatted,
        customerid: $scope.invoicelist[0].CustomerID //TODO: This should probably be static, though it will work for now.
      }).success(function(response) {
        $scope.trigger();
        if(response.message == "No userid or token") {
          //we're not logged in
          console.log("Issue - " + response.message);
          $location.path('/login');
        }

        if(response.success) {
          alert("Saved!");
        }

      }).error(function() {
        alert("Error retrieving records");
      });
    };

    $scope.toggleModal = function(id) {
      $("#" + id).modal('toggle');
      $('.modal-backdrop').removeClass("modal-backdrop");
    };

    $scope.addItems = function() {
      $scope.toslice = []; //empty the array

      $("input[class='checkadd']").each(function(i, obj) {
        if($("#" + obj.id).is(':checked')) {
          //then we need to add it to our other object
          $scope.invoicelist.push($scope.times[i]);
          //we need to splice it from this object
          //fun thing, when we delete one we make the i one less, so if you try to add two
          //it will fail because it doesn't actually see a second..
          $scope.toslice.push(i);
        }
      });

      //loop through our items to slice away made earlier, but in reverse so we don't interrupt the flow
      $($scope.toslice.reverse()).each(function(i, obj) {
        $scope.times.splice(obj,1);
      });

      $scope.setModalButton();
      $scope["invoice"]['InvoiceForm'].$setDirty();
    };

    $scope.selectRow = function(index) {
      $scope.toggleCheckbox("chk_" + index);
      $scope.setModalButton();
    };

    $scope.selectAll = function() {
      $("input[class='checkadd']").each(function(i, obj) {
        if($("#chk_SelectAll").is(':checked')) {
          //if our select all button is checked, we want to select everything
          $("#" + obj.id).prop( "checked", true );
        } else {
          //otherwise we want to unselect it all
          $("#" + obj.id).prop( "checked", false );
        }
      });
      $scope.setModalButton();
    };

    $scope.wipe = function() {
      $scope.invoicelist = [];
      $scope.invoiceid = "";
      $scope.fetchAllTimes();
      $scope['InvoiceForm'].$setPristine();
    };

    $scope.setModalButton = function() {
      // we just need a few ms for inputs to update before we test
      $timeout(function() {
        var checkboxes = $("input[class='checkadd']");
        $("#addItems").attr("disabled", !checkboxes.is(":checked"));
      }, 2);
      $scope.trigger();
    };

    $scope.toggleCheckbox = function (id) {
      //toggle the checkbox
      if($("#" + id).is(':checked')) {
        $("#" + id).prop( "checked", false );
      } else {
        $("#" + id).prop( "checked", true );
      }
    };

    $scope.fetchAllTimes = function() {
      var data = [];
      var cancelprocess = false;

      if(!cancelprocess) {
        $http.post(serviceBase, {
          task: "getAllEntries",
          includebilled: false
        }).success(function(response) {
          $scope.trigger();
          if(response.message == "No userid or token") {
            //we're not logged in
            console.log("Issue - " + response.message);
            $location.path('/login');
          }

          if(response.success) {
            if(response.records != "") {
              $scope.times = response.records;
            } else {
              $scope.times = "";
            }
          }
        }).error(function() {
          alert("Error retrieving records");
        });
      }
    };

    $scope.loadInvoice = function(invoiceid) {
      $scope.invoiceid = invoiceid;
      $http.post(serviceBase, {
        task: "loadInvoice",
        invoiceid: $scope.invoiceid
      }).success(function(response) {
        $scope.trigger();
        if(response.message == "No userid or token") {
          //we're not logged in
          console.log("Issue - " + response.message);
          $location.path('/login');
        }

        if(response.success) {
          if(response.records != "") {
            $scope.invoicelist = response.records;
            $scope.toCompany = response.customer.CustomerName;
            $scope.toPhone = response.customer.Phone;
            $scope.toAddress1 = response.customer.Addr1;
            $scope.toAddress2 = response.customer.Addr2;
            $scope.toCity = response.customer.City;
            $scope.toState = response.customer.State;
            $scope.toZip = response.customer.Zip;
          } else {
            $scope.invoicelist = "";
            $scope.resetClientData();
          }
        }

        $scope['InvoiceForm'].$setPristine();

        $("#dialogInvoices").modal("hide");
      }).error(function() {
        alert("Error retrieving records");
      });
    };

    $scope.resetClientData = function() {
      $scope.toCompany = "";
      $scope.toPhone = "";
      $scope.toAddress1 = "";
      $scope.toAddress2 = "";
      $scope.toCity = "";
      $scope.toState = "";
      $scope.toZip = "";
    };

    $scope.loadAllInvoices = function() {
      var data = [];
      var cancelprocess = false;

      if(!cancelprocess) {
        $http.post(serviceBase, {
          task: "loadAllInvoices"
        }).success(function(response) {
          $scope.trigger();
          if(response.message == "No userid or token") {
            //we're not logged in
            console.log("Issue - " + response.message);
            $location.path('/login');
          }

          if(response.success) {
            if(response.records != "") {
              $scope.invoices = response.records;
            } else {
              $scope.invoices = "";
            }
          }
        }).error(function() {
          alert("Error retrieving records");
        });
      }
    };
}]); //InvoiceController