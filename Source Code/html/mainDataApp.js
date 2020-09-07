var mainDataApp = angular.module('mainDataApp', []);

mainDataApp.directive('chosen', function($timeout) {
	var linker = function (scope, element, attr) {

		scope.$watch('projects', function() {
			$timeout(function() {
				element.trigger('chosen:updated');
			}, 0, false);
		}, true);

		$timeout(function() {
			element.chosen();
		}, 0, false);
	};

	return {
		restrict: 'A',
		link: linker
	};
});

mainDataApp.controller('mainPageCtrl', function($scope, $http, $window)
{
	$scope.selected_project = "";
	$scope.newProjectName = '';
	$scope.newProjectDescription = '';
	$scope.user_pref_name ="";
	$scope.projectInviteCode = "";
	$scope.viewSite=false;
	$scope.editable=false;
	$scope.createProj = false;
	
	
	$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
	$http({
		method: 'POST',
		url: "queryUserInfo.php",
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	}).then (function success(response){
		$scope.user_pref_name = response.data;
	});

    $http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
	$http({
		method: 'POST',
		url: "queryUserPermissions.php",
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	}).then (function success(response){
		$scope.createProj = response.data==="1" || response.data===1;
	});
    
	$scope.projects = [];


	$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
	$http({
		method: 'POST',
		url: "getEditableProjects.php",
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	}).then (function success(response){
		angular.forEach(response.data, function(d)
		{
			var group = "Permanent Projects";
			if (d.is_perm_project==0)
			{
				group = "Temporary Projects";
			}
			var tmpArray = {project_name:d.project_name, project_id:d.project_id, group: group};
			$scope.projects.push(tmpArray);
		});

	});

	$scope.changedValue = function()
	{
		if ($scope.project!==null)
		{
			$scope.selected_project = $scope.project.project_name;
			$scope.viewSite=true;

			$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
			$http({
				method: 'POST',
				url: "getPermissionLevel.php",
				data: $.param({p: $scope.project.project_id}),
				headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			}).then (function success(response){
				if(response.data!==undefined)
				{
					$scope.editable = response.data>=2;
				}
			});
		}
	}


	$scope.getProjects = function()
	{
		$scope.projects = [];
		$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
		$http({
			method: 'POST',
			url: "getEditableProjects.php",
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
		}).then (function success(response){
		//	$scope.projects = response.data;

		angular.forEach(response.data, function(d)
		{
			var group = "Permanent Projects";
			if (d.is_perm_project==0)
			{
				group = "Temporary Projects";
			}
			var tmpArray = {project_name:d.project_name, project_id:d.project_id, group: group};
			$scope.projects.push(tmpArray);
		});
	});
	}

/*	$scope.deleteProject = function(item)
	{
		var alert = false;
		if (item!=undefined)
		{
			alert = true;
		}
		$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
		$http({
			method: 'POST',
			url: "deleteProject.php",
			data: $.param({alert:alert, pi: $scope.project.project_id}),
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
		}).then (function success(response){
			$scope.getProjects();
		});
	}*/

	$scope.addProject = function()
	{
		angular.element(document.getElementById('loader-overlay')).css({'display':'block'});
		$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
			$http({
				method: 'POST',
				url: "addNewProject.php",
				data: $.param({pn:$scope.newProjectName, pd: $scope.newProjectDescription, pp:$scope.projTime }),
				headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			}).then (function success(response){
				$scope.newProjectName="";
				$scope.newProjectDescription="";
				$scope.getProjects();
				 $scope.errorMessage = response.data.message;
	    		response.data.result ? $scope.errorStatus = "Success!" : $scope.errorStatus = "Unexpected error!";
	    		 angular.element(document.getElementById('errorReportModal')).appendTo('body').modal('show');

	    		console.log(response);
			});
	}

	$scope.dismiss=function()
	{
		angular.element(document.getElementById('errorReportModal')).appendTo('body').modal('hide');
		angular.element(document.getElementById('loader-overlay')).css({'display':'none'});
	}

	$scope.navToWebPortal = function()
	{
		$scope.project!==undefined ? $window.location.href= 'DV/' + $scope.project.project_id + "/main.php" : null;
	}

	$scope.navToEdit = function()
	{
		$scope.project!==undefined ? $window.location.href= 'DV/' + $scope.project.project_id + "/dashboard.php" : null;
	}

	$scope.navToGuide = function()
	{
		$window.location.href= "/siteGuide.php";
	}

	$scope.deleteProject =function()
	{
		if ($scope.selected_project===$scope.project.project_name)
		{
			angular.element(document.getElementById('loader-overlay')).css({'display':'block'});
				$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
			$http({
				method: 'POST',
				url: "deleteProject.php",
				data: $.param({pi: $scope.project.project_id}),
				headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			}).then (function success(response){
				$scope.userDeleteName="";
				$scope.getProjects();
				$scope.viewSite = false; $scope.editable = false;
				$scope.errorMessage = response.data.message;
	    		response.data.result ? $scope.errorStatus = "Success!" : $scope.errorStatus = "Unexpected error!";
	    		 angular.element(document.getElementById('errorReportModal')).appendTo('body').modal('show');
			});
		}
	}

	$scope.acceptInvitation = function()
	{
		if($scope.projectInviteCode.length===20)
		{
			angular.element(document.getElementById('loader-overlay')).css({'display':'block'});
				$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
			$http({
				method: 'POST',
				url: "acceptInvite.php",
				data: $.param({ic: $scope.projectInviteCode}),
				headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			}).then (function success(response){
				 $scope.projectInviteCode="";
				$scope.getProjects();
				$scope.viewSite = false; $scope.editable = false;
				$scope.errorMessage = response.data.message;
	    		response.data.result ? $scope.errorStatus = "Success!" : $scope.errorStatus = "Unexpected error!";
	    		 angular.element(document.getElementById('errorReportModal')).appendTo('body').modal('show');
			});
		}
	}

});

mainDataApp.directive('invite', function($http){
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
						url: "checkInviteCode.php",
						data: $.param({
							pc: scope.projectInviteCode
						}),
						headers: {'Content-Type': 'application/x-www-form-urlencoded'}
					}).then (function success(response){
						if(response.data=="true")
						{
							ctrl.$setValidity('validInvite', true);
						}
						else
						{
							ctrl.$setValidity('validInvite', false);
						}
					});
				});
			});
		}
	}
});

mainDataApp.directive('newproject', function($http){
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
						url: "checkProjectName.php",
						data: $.param({
							pn: scope.newProjectName
						}),
						headers: {'Content-Type': 'application/x-www-form-urlencoded'}
					}).then (function success(response){
						if(response.data=="true")
						{
							ctrl.$setValidity('uniqueProjectName', true);
						}
						else
						{
							ctrl.$setValidity('uniqueProjectName', false);
						}
					});
				});
			});
		}
	}
});