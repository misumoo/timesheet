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
    when('/resetdo/:email', {
      templateUrl: 'views/resetdo.html',
      controller:  'RegisterController'
    }).
    when('/timesheet', {
      templateUrl: 'views/timesheet.html',
      controller: 'SheetController'
    }).
    when('/timesheetold', {
      templateUrl: 'views/timesheetold.html',
      controller: 'SheetController'
    }).
    otherwise({
      redirectTo: '/home'
    });
}]);

tsApp.controller('HeaderController', [ '$scope', '$cookies', '$http', '$location',
  function($scope, $cookies, $http, $location) {

    $scope.isActive = function (viewLocation) {
      return viewLocation === $location.path();
    };

    $scope.setupCookies = function() {
      if($cookies.userfirstname != "" && $cookies.userfirstname !== undefined) {
        $scope.loggedIn = true;
        $scope.username = $cookies.userfirstname;
      } else {
        $scope.loggedIn = false;
        $scope.username = "";
      }
    };

    $scope.setupCookies();

}]); //HeaderController