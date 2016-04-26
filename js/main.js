var tsApp = angular.module('tsApp', ['ngCookies', 'appControllers', 'ngRoute', 'ui.bootstrap', 'ngSanitize', 'ngAnimate']);

var appControllers = angular.module('appControllers', ['ngCookies']);

tsApp.config(['$routeProvider', function($routeProvider) {
  $routeProvider.
    when('/home', {
      templateUrl: 'views/home.html',
      controller:  'HomeController'
    }).
    when('/login', {
      templateUrl: 'views/login.html',
      controller:  'RegisterController'
    }).
    when('/register', {
      templateUrl: 'views/register.html',
      controller:  'RegisterController'
    }).
    when('/reset', {
      templateUrl: 'views/reset.html',
      controller:  'RegisterController'
    }).
    when('/resetdo/', {
      templateUrl: 'views/resetdo.html',
      controller:  'RegisterController'
    }).
    when('/resetdo/:email', {
      templateUrl: 'views/resetdo.html',
      controller:  'RegisterController'
    }).
    when('/timesheet', {
      templateUrl: 'views/timesheet.html',
      controller: 'SheetController'
    }).
    when('/invoice', {
      templateUrl: 'views/invoice.html',
      controller: 'InvoiceController'
    }).
    when('/invoice/:invoiceid', {
      templateUrl: 'views/invoice.html',
      controller: 'InvoiceController'
    }).
    otherwise({
      redirectTo: '/home'
    });
}]);

tsApp.controller('HeaderController', [ '$scope', '$cookies', '$http', '$location', '$timeout',
  function($scope, $cookies, $http, $location, $timeout) {

    var serviceBase = 'lib/_handle.php';

    $scope.isActive = function (viewLocation) {
      return viewLocation === $location.path();
    };

    $scope.setupCookies = function() {
      console.log($scope.loggedin);
      if($cookies.userfirstname != "" && $cookies.userfirstname !== undefined) {
        $scope.loggedin = true;
        $scope.username = $cookies.userfirstname;
      } else {
        $scope.loggedin = false;
        $scope.username = "";
      }
    };

    $scope.logout = function() {
      $cookies.userfirstname = "";
      $cookies.usertoken = "";
      $scope.setupCookies();
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
          $scope.loggedin = true;
          $scope.username = response.userfirstname;
          $scope.setupCookies();
          $timeout(function() {
            $location.path('/timesheet');
          }, 2);
        } else {
          alert("Invalid username/password");
        }
      }).error(function() {
        alert("Error reaching server");
      });
    }; //login

    $scope.setupCookies();

}]); //HeaderController

tsApp.service('sharedProperties', function () {
  var username = '';
  var loggedin = false;

  return {
    getUsername: function () {
      return username;
    },
    getLoggedIn: function () {
      return loggedin;
    },
    setUsername: function(value) {
      username = value;
    },
    setLoggedIn: function(value) {
      loggedin = value;
    }
  };
});