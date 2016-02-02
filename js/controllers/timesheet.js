/**
 * Created by Misumoo on 5/9/2015.
 */

tsApp.controller('SheetController', [ '$scope', '$cookies', '$http', '$filter', '$sce', '$location', '$timeout',
  function($scope, $cookies, $http, $filter, $sce, $location, $timeout) {

    var serviceBase = 'lib/_handle.php';

    $scope.$on('$locationChangeStart', function( event, newUrl, oldUrl ) {
      if($scope.ReadyToLeave == false && $scope.ReadyToLeave !== 'undefined') {
        var answer = confirm("Unsaved changes detected! Are you sure you want to leave this page?");
        if (!answer) {
          event.preventDefault();
        }
      }
    });


    /////////////////////////////////////////////////////
    /////////////////datepicker settings/////////////////
    /////////////////////////////////////////////////////
    $scope.today = function() {
      $scope.dt = new Date();
    };

    $scope.open = function($event) {
      $event.preventDefault();
      $event.stopPropagation();

      $scope.opened = true;
    };

    $scope.todayAdd = function() {
      $scope.add_Date = new Date();
    };

    $scope.openAdd = function($event) {
      $event.preventDefault();
      $event.stopPropagation();

      $scope.openedAdd = true;
    };

    $scope.dateOptions = {
      formatYear: 'yy',
      startingDay: 1
    };

    $scope.formats = ['shortDate', 'yyyy/MM/dd', 'dd.MM.yyyy', 'MM/dd/yyyy'];
    $scope.format = $scope.formats[3];
    /////////////////////////////////////////////////////
    /////////////////datepicker settings/////////////////
    /////////////////////////////////////////////////////

    $scope.findPreviousMonday = function(date) {
      if($filter('date')(new Date(date.setDate(date.getDate())), 'EEEE') == "Monday") {
        $scope.setupDays(date);
        return date;
      }
      while($filter('date')(new Date(date.setDate(date.getDate()-1)), 'EEEE') != "Monday") {} // this subtracts one day from our date until we get monday
      $scope.setupDays(date);
      return date;
    };

    $scope.setupDays = function(date) {
      dDate = new Date(($filter('date')(date, 'yyyy/MM/dd')));
      $scope.DateM = ($filter('date')(new Date(dDate.setDate(dDate.getDate())), 'dd'));
      $scope.DateMFull = ($filter('date')(new Date(dDate.setDate(dDate.getDate())), 'yyyy/MM/dd'));
      $scope.DateTu = ($filter('date')(new Date(dDate.setDate(dDate.getDate()+1)), 'dd'));
      $scope.DateTuFull = ($filter('date')(new Date(dDate.setDate(dDate.getDate())), 'yyyy/MM/dd'));
      $scope.DateW = ($filter('date')(new Date(dDate.setDate(dDate.getDate()+1)), 'dd'));
      $scope.DateWFull = ($filter('date')(new Date(dDate.setDate(dDate.getDate())), 'yyyy/MM/dd'));
      $scope.DateTh = ($filter('date')(new Date(dDate.setDate(dDate.getDate()+1)), 'dd'));
      $scope.DateThFull = ($filter('date')(new Date(dDate.setDate(dDate.getDate())), 'yyyy/MM/dd'));
      $scope.DateF = ($filter('date')(new Date(dDate.setDate(dDate.getDate()+1)), 'dd'));
      $scope.DateFFull = ($filter('date')(new Date(dDate.setDate(dDate.getDate())), 'yyyy/MM/dd'));
      $scope.DateSa = ($filter('date')(new Date(dDate.setDate(dDate.getDate()+1)), 'dd'));
      $scope.DateSaFull = ($filter('date')(new Date(dDate.setDate(dDate.getDate())), 'yyyy/MM/dd'));
      $scope.DateSu = ($filter('date')(new Date(dDate.setDate(dDate.getDate()+1)), 'dd'));
      $scope.DateSuFull = ($filter('date')(new Date(dDate.setDate(dDate.getDate())), 'yyyy/MM/dd'));
    };

    $scope.addCustomer = function() {
      $http.post(serviceBase, {
        customername: $scope.customername,
        task: "addCustomer"
      }).success(function() {
        $scope.timesheet['InsertCustomer'].$setPristine();
        $scope.customername='';
        $scope.getCustomers();
      }).error(function() {
        alert("Error inserting");
      });
    }; //addCustomer

    $scope.getCustomers = function() {
      $http.post(serviceBase, {
        task: "getCustomers"
      }).success(function(response) {
        $scope.customers = response.customers;
      }).error(function() {
        alert("Error retrieving Customers");
      });
    }; //getCustomers

    $scope.addService = function() {
      $http.post(serviceBase, {
        servicename: $scope.servicename,
        hourlyrate: $scope.hourlyrate,
        task: "addService"
      }).success(function() {
        $scope.timesheet['InsertService'].$setPristine();
        $scope.servicename='';
        $scope.hourlyrate='';
        $scope.getServices();
      }).error(function() {
        alert("Error inserting");
      });
    }; //addService

    $scope.getServices = function() {
      $http.post(serviceBase, {
        task: "getServices"
      }).success(function(response) {
        $scope.services = response.services;
      }).error(function() {
        alert("Error getting services");
      });
    }; // getServices

    $scope.getTimes = function() {
      var cancelprocess = false;

      //if we notice changes are made, see if they want to cancel first
      if($scope.ReadyToLeave == false && $scope.ReadyToLeave !== 'undefined') {
        var answer = confirm("Unsaved changes detected! Are you sure you want to change the date?");
        if (!answer) {
          cancelprocess = true;
          $scope.dt = $filter('date')(new Date($scope.DateSuFull), $scope.formats[3]);
        }
      }

      if(!cancelprocess) {
        $http.post(serviceBase, {
          date: ($filter('date')($scope.findPreviousMonday($scope.dt), 'yyyy/MM/dd')),
          task: "getTimes"
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
              //$scope.addnewrow();
            }
            $scope.numRows = $scope.times.length;
          }
        }).error(function() {
          alert("Error retrieving times");
        });
      }
    }; //getTimes

    $scope.setupNumboxes = function() {

    }; //setupNumboxes

    $scope.setup = function() {
      $scope.today();
      $scope.getCustomers();
      $scope.getTimes();
      $scope.todayAdd();
      $scope.getServices();
    }; //setup

    $scope.trigger = function(index) {
      $scope.testReadyToLeave();
    };

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

    $scope.getSum = function(index) {
      myNum = parseInt($scope.times[index].M) + parseInt($scope.times[index].Tu);
      //console.log(myNum);
      myNum = parseInt(myNum) + parseInt($scope.times[index].W);
      myNum = parseInt(myNum) + parseInt($scope.times[index].Th);
      myNum = parseInt(myNum) + parseInt($scope.times[index].F);
      myNum = parseInt(myNum) + parseInt($scope.times[index].Sa);
      myNum = parseInt(myNum) + parseInt($scope.times[index].Su);

      //console.log(myNum);
      //$scope.times[index].Sum = myNum;
    };

    $scope.resetForm = function (index) {
      //Todo: Figure out how to copy this form for each ng-repeat and set up a $setPristine()
      //This is a bit out of my comprehension of Angular right now
      alert("todo");
      console.log($scope.timesheet['weekly_' + index].iM.$dirty);
    }; //resetForm

    $scope.deleteRow = function(index) {
      $http.post(serviceBase, {
        weeklyid: $scope.timesheet['weekly_' + index].WeeklyID.$modelValue,
        task: "deleteRow"
      }).success(function(response) {
        if(response.success) {
          $scope.getTimes();
        } else {
          alert("Error deleting row");
        }
      }).error(function() {
        alert("Error deleting row");
      });
      //console.log($scope.timesheet['weekly_' + index]);
      //console.log(JSON.stringify($scope.timesheet['weekly_' + index]));
    }; //deleteRow

    $scope.insertNewTime = function() {
      cancelProcess = false;

      customerid = $scope.add_Customer;
      serviceid = $scope.add_Service;
      hours = $scope.add_Hours;
      desc = $scope.add_Desc;
      date = $filter('date')(new Date($scope.add_Date), $scope.formats[1]);

      (customerid == null ? cancelProcess = true : "");
      (serviceid == null ? cancelProcess = true : "");
      (hours == null ? cancelProcess = true : "");

      if(!cancelProcess) {
        $http.post(serviceBase, {
          task: "insertNewTime",
          customerid: customerid,
          serviceid: serviceid,
          hours: hours,
          date: date,
          desc: desc
        }).success(function(response) {
          //success
          $scope.timesheet['InsertNewRecord'].$setPristine();
          $scope.add_Hours = null;
          $scope.add_Desc = null;
          $scope.getTimes();
        }).error(function() {
          alert("Save unsuccessful, please try again.");
        });
      } else {
        alert("Data needs to be filled out in order to insert a new record.");
      }

    }; //insertNewTime

    $scope.saveRow = function(index) {
      weeklyid = $scope.times[index].WeeklyID;
      insert = (weeklyid == "");
      if(insert) {
        //we need to get a weekly id first, then come back around and rerun this with the weeklyid
        $scope.getNewWeeklyID(index);
      } else {
        //not inserting, update our row on everything that is dirty
        $scope.timesheet['weekly_' + index].$setPristine();
        $scope.trigger();

        //($scope.timesheet['weekly_' + index].iCust.$dirty ? $scope.saveCustomer($scope.times[index].Customer, $scope.times[index].WeeklyID, index) : "");
        //($scope.timesheet['weekly_' + index].iService.$dirty ? $scope.saveService($scope.times[index].Service, $scope.times[index].WeeklyID, index) : "");
        ($scope.timesheet['weekly_' + index].ta_Desc.$dirty ? $scope.saveDesc($scope.times[index].Description, $scope.times[index].WeeklyID, index) : "");
        ($scope.timesheet['weekly_' + index].iM.$dirty ? $scope.saveTime($scope.times[index].M, $scope.DateMFull, $scope.times[index].WeeklyID, $scope.times[index].MTimeID, index, "m") : "");
        ($scope.timesheet['weekly_' + index].iTu.$dirty ? $scope.saveTime($scope.times[index].Tu, $scope.DateTuFull, $scope.times[index].WeeklyID, $scope.times[index].TuTimeID, index, "tu") : "");
        ($scope.timesheet['weekly_' + index].iW.$dirty ? $scope.saveTime($scope.times[index].W, $scope.DateWFull, $scope.times[index].WeeklyID, $scope.times[index].WTimeID, index, "w") : "");
        ($scope.timesheet['weekly_' + index].iTh.$dirty ? $scope.saveTime($scope.times[index].Th, $scope.DateThFull, $scope.times[index].WeeklyID, $scope.times[index].ThTimeID, index, "th") : "");
        ($scope.timesheet['weekly_' + index].iF.$dirty ? $scope.saveTime($scope.times[index].F, $scope.DateFFull, $scope.times[index].WeeklyID, $scope.times[index].FTimeID, index, "f") : "");
        ($scope.timesheet['weekly_' + index].iSa.$dirty ? $scope.saveTime($scope.times[index].Sa, $scope.DateSaFull, $scope.times[index].WeeklyID, $scope.times[index].SaTimeID, index, "sa") : "");
        ($scope.timesheet['weekly_' + index].iSu.$dirty ? $scope.saveTime($scope.times[index].Su, $scope.DateSuFull, $scope.times[index].WeeklyID, $scope.times[index].SuTimeID, index, "su") : "");
      }
    }; //saveRow

    $scope.getNewWeeklyID = function(index) {
      $http.post(serviceBase, {
        task: "getNewWeeklyID"
      }).success(function(response) {
        //success
        $scope.times[index].WeeklyID = response.weeklyid;
        if(response.weeklyid == "") {
          $scope.timesheet['weekly_' + index].$setDirty();
          //something went wrong, we need to end this or it will infinitely lookup
          console.log("Something went wrong with inserting a new row. Please try again.");
        } else {
          //otherwise we can go ahead and save again, this time it will save the rest of the information.
          $scope.saveRow(index);
        }
      }).error(function() {
        alert("Save unsuccessful, please try again.");
      });
    }; //saveDesc

    $scope.saveCustomer = function(customer, weeklyid, index) {
      customer = typeof customer !== 'undefined' ? customer : "";
      weeklyid = typeof weeklyid !== 'undefined' ? weeklyid : "";
      $http.post(serviceBase, {
        customer: customer,
        weeklyid: weeklyid,
        task: "saveCustomer"
      }).success(function(response) {
        //success
        $scope.timesheet['weekly_' + index].iCust.$setPristine();
      }).error(function() {
        $scope.timesheet['weekly_' + index].$setDirty();
        alert("Save unsuccessful, please try again.");
      });
    }; //saveCustomer

    $scope.saveService = function(service, weeklyid, index) {
      service = typeof service !== 'undefined' ? service : "";
      weeklyid = typeof weeklyid !== 'undefined' ? weeklyid : "";
      $http.post(serviceBase, {
        service: service,
        weeklyid: weeklyid,
        task: "saveService"
      }).success(function(response) {
        //success
        $scope.timesheet['weekly_' + index].iService.$setPristine();
      }).error(function() {
        $scope.timesheet['weekly_' + index].$setDirty();
        alert("Save unsuccessful, please try again.");
      });
    }; //saveService

    $scope.saveDesc = function(description, weeklyid, index) {
      description = typeof description !== 'undefined' ? description : "";
      weeklyid = typeof weeklyid !== 'undefined' ? weeklyid : "";
      $http.post(serviceBase, {
        description: description,
        weeklyid: weeklyid,
        task: "saveDescription"
      }).success(function(response) {
        //success
        $scope.timesheet['weekly_' + index].ta_Desc.$setPristine();
      }).error(function() {
        $scope.timesheet['weekly_' + index].$setDirty();
        alert("Save unsuccessful, please try again.");
      });
    }; //saveDesc

    $scope.saveTime = function(amount, date, weeklyid, timeid, index, day) {
      amount = typeof amount !== 'undefined' ? amount : "";
      date = typeof date !== 'undefined' ? date : "";
      weeklyid = typeof weeklyid !== 'undefined' ? weeklyid : "";
      timeid = typeof timeid !== 'undefined' ? timeid : "";
      $http.post(serviceBase, {
        amount: amount,
        weeklyid: weeklyid,
        timeid: timeid,
        date: date,
        task: "saveSingle"
      }).success(function(response) {
        //this seems like the easiest way to handle it, a switch
        id = response.id != '0' ? response.id : "";
        switch(day) {
          case "m":
            $scope.timesheet['weekly_' + index].iM.$setPristine();
            (response.message == "insert" || response.message == "delete" ? $scope.times[index].MTimeID = id : ""); break;
          case "tu":
            $scope.timesheet['weekly_' + index].iTu.$setPristine();
            (response.message == "insert" || response.message == "delete" ? $scope.times[index].TuTimeID = id : ""); break;
          case "w":
            $scope.timesheet['weekly_' + index].iW.$setPristine();
            (response.message == "insert" || response.message == "delete" ? $scope.times[index].WTimeID = id : ""); break;
          case "th":
            $scope.timesheet['weekly_' + index].iTh.$setPristine();
            (response.message == "insert" || response.message == "delete" ? $scope.times[index].ThTimeID = id : ""); break;
          case "f":
            $scope.timesheet['weekly_' + index].iF.$setPristine();
            (response.message == "insert" || response.message == "delete" ? $scope.times[index].FTimeID = id : ""); break;
          case "sa":
            $scope.timesheet['weekly_' + index].iSa.$setPristine();
            (response.message == "insert" || response.message == "delete" ? $scope.times[index].SaTimeID = id : ""); break;
          case "su":
            $scope.timesheet['weekly_' + index].iSu.$setPristine();
            (response.message == "insert" || response.message == "delete" ? $scope.times[index].SuTimeID = id : ""); break;
        }
      }).error(function() {
        $scope.timesheet['weekly_' + index].$setDirty();
        alert("Save unsuccessful, please try again.");
      });
    }; //saveTime

    $scope.addnewrow = function() {
      try {
        $scope.times.push({
          "WeeklyID": "",
          "Description": "",
          "ServiceID": "",
          "Service": "",
          "CustomerID": "",
          "Customer": "",
          "HourlyRate": "",
          "MDate": "",
          "MTimeID": "",
          "TuDate": "",
          "TuTimeID": "",
          "WDate": "",
          "WTimeID": "",
          "ThDate": "",
          "ThTimeID": "",
          "FDate": "",
          "FTimeID": "",
          "SaDate": "",
          "SaTimeID": "",
          "SuDate": "",
          "SuTimeID": ""
        });
      } catch (e) {
        $scope.times = [{}];
      }
    }; //addnewrow

    $scope.test = function() {
      console.log("Ping!");
    };

  }]); //SheetController

tsApp.directive("modalShow", function ($parse) {
  return {
    restrict: "A",
    link: function (scope, element, attrs) {
      //Hide or show the modal
      scope.showModal = function (visible, elem) {
        if (!elem)
          elem = element;
        if (visible)
          $(elem).modal("show");
        else
          $(elem).modal("hide");
        };
      //Watch for changes to the modal-visible attribute
      scope.toggleModal = function() {
        scope.showModal(true);
      };
    }
  };
});