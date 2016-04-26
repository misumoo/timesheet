/**
 * Created by Misumoo on 5/8/2015.
 */

tsApp.controller('RegisterController', ['$scope', '$cookies', '$http', '$location', '$routeParams',
  function($scope, $cookies, $http, $location, $routeParams) {
    var serviceBase = 'lib/_handle.php';

    var email = $routeParams.email;
    (email != "" && email != undefined ? $scope.email = email : "");

    $scope.resetInit = function() {
      $http.post(serviceBase, {
        email: $scope.email,
        task: "resetInit"
      }).success(function(response) {
        if(response.success == true) {
          $location.path('/resetdo/' + $scope.email);
        } else {
          alert("Unknown Error");
        }
      }).error(function() {
        alert("Error reaching server");
      });
    }; //resetInit

    $scope.resetDo = function() {
      $http.post(serviceBase, {
        email: $scope.email,
        confirmationcode: $scope.confirmationcode,
        password: $scope.password,
        task: "resetDo"
      }).success(function(response) {
        if(response.success == true) {
          $location.path('/login');
        } else {
          alert("Error occured. Please try again.");
        }
      }).error(function() {
        alert("Error reaching server");
      });
    };

    $scope.register = function() { //our submit button has been triggered -- form with ng-submit="register()"
      $http.post(serviceBase, {
        firstname: $scope.firstname,
        lastname: $scope.lastname,
        email: $scope.email,
        password: $scope.password,
        task: "register"
      }).success(function(response) {
        if(response.success == true) {
          //$cookies.usertoken = response.usertoken;
          //$cookies.userfirstname = response.userfirstname;
          alert("Success");
          $location.path('/login');
        } else {
          alert(response.message);
        }
      }).error(function() {
        alert("Error reaching server");
      });
    }; //register
}]); //RegisterController