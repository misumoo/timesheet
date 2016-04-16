/**
 * Created by Misumoo on 4/11/2016.
 */


tsApp.controller('InvoiceController', [ '$scope', '$cookies', '$http', '$filter', '$sce', '$location', '$timeout',
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

    $scope.invoicelist = [];
    $scope.toslice = [];

    $scope.setup = function() {
      $scope.generateAllEntries();
      $scope.toggleModal('dialogAddToInvoice');
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

    $scope.toggleModal = function(id) {
      $("#" + id).modal('toggle');
      $('.modal-backdrop').removeClass("modal-backdrop");
    };

    $scope.addItems = function() {
      $scope.toslice = [];
      $("input[class='checkadd']").each(function(i, obj) {
        if($("#" + obj.id).is(':checked')) {
          console.log(i);
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
    };

    $scope.setModalButton = function() {
      var checkboxes = $("input[class='checkadd']");
      $("#addItems").attr("disabled", !checkboxes.is(":checked"));
    };

    $scope.toggleCheckbox = function (id) {
      //toggle the checkbox
      if($("#" + id).is(':checked')) {
        $("#" + id).prop( "checked", false );
      } else {
        $("#" + id).prop( "checked", true );
      }
    };

    /**
     * Bootstrap/AngularJS sort/filter table
     * http://www.bootply.com/jIEfKezm84
     */
    $scope.generateAllEntries = function() {
      var data = [];
      var cancelprocess = false;

      if(!cancelprocess) {
        $http.post(serviceBase, {
          task: "getAllEntries"
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
}]); //InvoiceController