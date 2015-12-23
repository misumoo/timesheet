/**
 * Created by Misumoo on 8/24/2015.
 */

tsApp.controller('HomeController', ['$scope', '$cookies', '$http', '$location',
  function($scope, $cookies, $http, $location) {
    var serviceBase = 'lib/_handle.php';

    $scope.setup = function() {
      $scope.getTasks();
    }; //setup

    $scope.getTasks = function() {
      $http.post(serviceBase, {
        task: "getTasks"
      }).success(function(response) {
        if(response.success) {
          if(response.records != "") {
            $scope.tasks = response.records;
          }
          //$scope.numRows = $scope.tasks.length;
        }
      }).error(function() {
        alert("Error retrieving tasks");
      });
    }; //getTasks

    $scope.addNewTask = function() {
      cancelprocess = false;
      taskdescription = typeof $scope.taskdescription !== 'undefined' ? $scope.taskdescription : "";
      if(taskdescription == "") {
        cancelprocess = true;
      }

      if(!cancelprocess) {
        $http.post(serviceBase, {
          taskdescription: taskdescription,
          task: "addTask"
        }).success(function(response) {
          if(response.success) {
            $scope.taskdescription = "";
            $scope.getTasks();
          }
        }).error(function() {
          alert("Error adding task");
        });
      }
    }; //setupNewTask

}]); //HomeController