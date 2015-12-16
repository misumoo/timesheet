/**
 * Created by Misumoo on 5/8/2015.
 */

tsApp.controller('RegisterController', ['$scope', '$cookies', '$http', '$location', '$routeParams',
  function($scope, $cookies, $http, $location, $routeParams) {
    var serviceBase = 'views/_handle.php';

    var email = $routeParams.email;
    (email != "" && email != undefined ? $scope.email = email : "");

    $scope.test = function() {
      $cookies.fruit = 'Apple';
      $scope.myFruit = $cookies['fruit'];
    };

    $scope.resetInit = function() {
      $http.post(serviceBase, {
        email: $scope.email,
        task: "resetInit"
      }).success(function(response) {
        if(response.success == true) {
          $location.path('/resetdo/' + $scope.email);
        } else {
          alert("Invalid username/password");
        }
      }).error(function() {
        alert("Error reaching server");
      });
    }; //resetInit

    $scope.resetDo = function() {
      console.log($scope.email);
      console.log($scope.password);
    };

    $scope.login = function() { //our submit button has been triggered -- form with ng-submit="login()"
      //attempt to log in with username and email

      $http.post(serviceBase, {
        email: $scope.email,
        password: $scope.password,
        task: "login"
      }).success(function(response) {
        if(response.success == true) {
          $cookies.usertoken = response.usertoken;
          $cookies.userfirstname = response.userfirstname;
          $location.path('/timesheet');
        } else {
          alert("Invalid username/password");
        }
      }).error(function() {
        alert("Error reaching server");
      });
    }; //login

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