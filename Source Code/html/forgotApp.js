var forgotApp = angular.module('forgotAccountApp', []);

forgotApp.controller('forgotCtrl', function($scope, $http){
	$scope.forgotEmail = "";
});

forgotApp.directive('useremail', function($http){
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
						em: scope.forgotEmail
					}),
					headers: {'Content-Type': 'application/x-www-form-urlencoded'}
				}).then (function success(response){
					if(response.data=="true")
					{
						ctrl.$setValidity('registeredEmail', true);
					}
					else
					{
						ctrl.$setValidity('registeredEmail', false);
					}
			});
			});
		});
	}
}
});