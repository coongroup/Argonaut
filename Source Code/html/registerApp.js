var registerApp = angular.module('registerAccountApp', []);

registerApp.controller('registerCtrl', function($scope, $http){
	$scope.username = "";
	$scope.first_name = "";
	$scope.last_name = "";
	$scope.email = "";
});

registerApp.directive('username', function($http){
	 var toId;
	return {
		restrict: 'A',
		require: 'ngModel',
		link: function (scope, elm, attr, ctrl)
		{

			  scope.$watch(attr.ngModel, function(value) {
			  	if(toId) clearTimeout(toId);

			toId = setTimeout(function(){

			$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
				$http({
					method: 'POST',
					url: "queryUsername.php",
					data: $.param({
						un: scope.username
					}),
					headers: {'Content-Type': 'application/x-www-form-urlencoded'}
				}).then (function success(response){
					if(response.data=="true")
					{
						ctrl.$setValidity('uniqueUsername', false);
					}
					else
					{
						ctrl.$setValidity('uniqueUsername', true);
					}
			});
			});
		});
	}
}
});

registerApp.directive('useremail', function($http){
	 var toId;
	return {
		restrict: 'A',
		require: 'ngModel',
		link: function (scope, elm, attr, ctrl)
		{

			  scope.$watch(attr.ngModel, function(value) {
			  	if(toId) clearTimeout(toId);

			toId = setTimeout(function(){

			$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
				$http({
					method: 'POST',
					url: "queryEmail.php",
					data: $.param({
						em: scope.email
					}),
					headers: {'Content-Type': 'application/x-www-form-urlencoded'}
				}).then (function success(response){
					if(response.data=="true")
					{
						ctrl.$setValidity('registeredEmail', false);
					}
					else
					{
						ctrl.$setValidity('registeredEmail', true);
					}
			});
			});
		});
	}
}
});