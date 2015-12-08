/**
 * Created by Misumoo on 5/8/2015.
 */

tsApp.controller('RegisterController', ['$scope', '$cookies', '$http', '$location',
  function($scope, $cookies, $http, $location) {
    var serviceBase = 'views/_handle.php';

    $scope.test = function() {
      $cookies.fruit = 'Apple';
      $scope.myFruit = $cookies['fruit'];
    };

    $scope.login = function() { //our submit button has been triggered -- form with ng-submit="login()"
      //attempt to log in with username and email

      $http.post(serviceBase, {
        email: $scope.user.email,
        password: $scope.user.password,
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
        firstname: $scope.user.firstname,
        lastname: $scope.user.lastname,
        email: $scope.user.email,
        password: $scope.user.password,
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

    //$scope.test();
}]); //RegisterController