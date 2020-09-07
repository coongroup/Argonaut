var inviteDataApp = angular.module('inviteDataApp', ['ngSanitize', 'ui.select']);

inviteDataApp.controller('inviteCtrl', function($scope, $http, $filter, $window, $interval){

  $scope.collabEmails = [];
  $scope.errorEmails = [];
  $scope.inviteValid = false;
  $scope.permissionLevel = null;
  $scope.select = null;
  $scope.collabs = {};

  $scope.projectID=-1;
  $scope.projectDescription=""; 

  $http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
  $http({
    method: 'POST',
    url: "queryProjectInfo.php",
    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
  }).then (function success(response){
    $scope.projectName=response.data.name;
    $scope.projectDescription ="Send electronic invitations to collaborators granting them access to the '" + $scope.projectName + "' web portal below.";
  });

  $http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
  $http({
   method: 'POST',
   url: "queryEditOverviewInfo.php",
   headers: {'Content-Type': 'application/x-www-form-urlencoded'}
 }).then (function success(response){
   $scope.totalMeas = response.data.totalMeasurements;
   $scope.uniqueMol = response.data.uniqueMolecules;
   $scope.filesUploaded = response.data.filesUploaded;
   $scope.invitedCollabs = response.data.invitedCollaborators;
 });

 $scope.invites = [];
 $scope.events = [];
 $http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
 $http({
   method: 'POST',
   url: "getSentInvites.php",
   headers: {'Content-Type': 'application/x-www-form-urlencoded'}
 }).then (function success(response){
  angular.forEach(response.data, function(d){
    $scope.invites.push({label:d.label, activity:d.display, color:d.color, inviteStatus:d.inviteStatus, invitationDate:d.invitation, permission:d.permission, inviter:d.invited});
  });
});

 $scope.sortProcess = function(process) {
  var date = new Date(process.time);
  return date;
};

$scope.processes={};

$interval(function(){ $scope.updateProcessList(); }, 5000);

$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
$http({
  method: 'POST',
  url: "queryProcesses.php",
  headers: {'Content-Type': 'application/x-www-form-urlencoded'}
}).then (function success(response){
  angular.forEach(response.data, function(d){
   if(d.failed===1){d.color="bg-red";}else{d.color = d.completed==="1" ? "bg-green" : "bg-blue";}
   d.task==="UPLOAD" ? d.icon="icon-upload" : null;
   d.task==="REPROCESS" ? d.icon="icon-calculator" : null;
   d.task==="EDIT" ? d.icon="icon-calculator" : null;
   d.task==="CONTROL" ? d.icon="icon-compass" : null;
   d.task==="LOG2" ? d.icon="icon-calculator" : null;
   d.task==="TYPE" ? d.icon="icon-flask" : null;
   d.task==="IMPUTE" ? d.icon="icon-plug" : null;
   d.task==="FILTER" ? d.icon="icon-scissors" : null;
   d.task==="UPLOAD" && d.completed=="0" ? d.display = "Uploading file '" + d.name + "'": null;
   d.task==="CONTROL" && d.completed=="0" ? d.display = "Changing controls in '" + d.name + "'": null;
   d.task==="LOG2" && d.completed=="0" ? d.display = "Updating Log2 transformation settings in '" + d.name + "'": null;
   d.task==="IMPUTE" && d.completed=="0" ? d.display = "Updating imputation settings in '" + d.name + "'": null;
   d.task==="EDIT" && d.completed=="0" ? d.display = "Updating data from '" + d.name + "'": null;
   d.task==="REPROCESS" && d.completed=="0" ? d.display = "Updating data from '" + d.name + "'": null;
   d.task==="TYPE" && d.completed=="0" ? d.display = "Changing sample type settings in '" + d.name + "'": null;
   d.task==="FILTER" && d.completed=="0" ? d.display = "Changing data filter settings in '" + d.name + "'": null;
   d.task==="UPLOAD" && d.completed=="1" ? d.display = "Uploaded file '" + d.name + "' (Completed in " + d.delta + " minutes)": null;
   d.task==="CONTROL" && d.completed=="1" ? d.display = "Changed controls in '" + d.name + "' (Completed in " + d.delta + " minutes)": null;
   d.task==="LOG2" && d.completed=="1" ? d.display = "Updated Log2 transformation settings in '" + d.name + "' (Completed in " + d.delta + " minutes)": null;
   d.task==="IMPUTE" && d.completed=="1" ? d.display = "Updated imputation in '" + d.name + "' (Completed in " + d.delta + " minutes)": null;
   d.task==="REPROCESS" && d.completed=="1" ? d.display = "Updated data from '" + d.name + "' (Completed in " + d.delta + " minutes)": null;
   d.task==="EDIT" && d.completed=="1" ? d.display = "Updated data from '" + d.name + "' (Completed in " + d.delta + " minutes)": null;
   d.task==="TYPE" && d.completed=="1" ? d.display = "Changed sample type settings in '" + d.name + "' (Completed in " + d.delta + " minutes)": null;
   d.task==="FILTER" && d.completed=="1" ? d.display = "Changed data filter settings in '" + d.name + "' (Completed in " + d.delta + " minutes)": null;
   d.task==="UPLOAD" && d.completed=="1" && d.failed===1 ? d.display = "Failed to upload file '" + d.name + ".' Site admins have been contacted.": null;
   d.task==="CONTROL" && d.completed=="1" && d.failed===1 ? d.display = "Failed to change controls in '" + d.name + ".' Site admins have been contacted.": null;
   d.task==="LOG2" && d.completed=="1" && d.failed===1 ? d.display = "Failed to update Log2 transformation settings in '" + d.name + ".' Site admins have been contacted.": null;
   d.task==="IMPUTE" && d.completed=="1" && d.failed===1 ? d.display = "Failed to update imputation settings in '" + d.name + ".' Site admins have been contacted.": null;
   d.task==="REPROCESS" && d.completed=="1" && d.failed===1 ? d.display = "Failed to update data from '" + d.name + ".' Site admins have been contacted.": null;
   d.task==="EDIT" && d.completed=="1" && d.failed===1 ? d.display = "Failed to update data from '" + d.name + ".' Site admins have been contacted.": null;
   d.task==="TYPE" && d.completed=="1" && d.failed===1 ? d.display = "Failed to changed sample type settings in '" + d.name + ".' Site admins have been contacted.": null;
   d.task==="FILTER" && d.completed=="1" && d.failed===1 ? d.display = "Failed to changed data filter settings in '" + d.name + ".' Site admins have been contacted.": null;
   $scope.processes[d.key.toString()] = {display:d.display, delta:d.delta, progress:d.progress, time:d.time, name:d.name, completed:d.completed==="1", icon:d.icon, key:d.key, color:d.color};
 });
});


$scope.updateProcessList = function()
{
 $http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
 $http({
  method: 'POST',
  url: "queryProcesses.php",
  headers: {'Content-Type': 'application/x-www-form-urlencoded'}
}).then (function success(response){
  angular.forEach(response.data, function(d){
   if(d.failed===1){d.color="bg-red";}else{d.color = d.completed==="1" ? "bg-green" : "bg-blue";}
   d.task==="UPLOAD" ? d.icon="icon-upload" : null;
   d.task==="REPROCESS" ? d.icon="icon-calculator" : null;
   d.task==="EDIT" ? d.icon="icon-calculator" : null;
   d.task==="CONTROL" ? d.icon="icon-compass" : null;
   d.task==="LOG2" ? d.icon="icon-calculator" : null;
   d.task==="TYPE" ? d.icon="icon-flask" : null;
   d.task==="IMPUTE" ? d.icon="icon-plug" : null;
   d.task==="FILTER" ? d.icon="icon-scissors" : null;
   d.task==="UPLOAD" && d.completed=="0" ? d.display = "Uploading file '" + d.name + "'": null;
   d.task==="CONTROL" && d.completed=="0" ? d.display = "Changing controls in '" + d.name + "'": null;
   d.task==="LOG2" && d.completed=="0" ? d.display = "Updating Log2 transformation settings in '" + d.name + "'": null;
   d.task==="IMPUTE" && d.completed=="0" ? d.display = "Updating imputation settings in '" + d.name + "'": null;
   d.task==="EDIT" && d.completed=="0" ? d.display = "Updating data from '" + d.name + "'": null;
   d.task==="REPROCESS" && d.completed=="0" ? d.display = "Updating data from '" + d.name + "'": null;
   d.task==="TYPE" && d.completed=="0" ? d.display = "Changing sample type settings in '" + d.name + "'": null;
   d.task==="FILTER" && d.completed=="0" ? d.display = "Changing data filter settings in '" + d.name + "'": null;
   d.task==="UPLOAD" && d.completed=="1" ? d.display = "Uploaded file '" + d.name + "' (Completed in " + d.delta + " minutes)": null;
   d.task==="CONTROL" && d.completed=="1" ? d.display = "Changed controls in '" + d.name + "' (Completed in " + d.delta + " minutes)": null;
   d.task==="LOG2" && d.completed=="1" ? d.display = "Updated Log2 transformation settings in '" + d.name + "' (Completed in " + d.delta + " minutes)": null;
   d.task==="IMPUTE" && d.completed=="1" ? d.display = "Updated imputation in '" + d.name + "' (Completed in " + d.delta + " minutes)": null;
   d.task==="REPROCESS" && d.completed=="1" ? d.display = "Updated data from '" + d.name + "' (Completed in " + d.delta + " minutes)": null;
   d.task==="EDIT" && d.completed=="1" ? d.display = "Updated data from '" + d.name + "' (Completed in " + d.delta + " minutes)": null;
   d.task==="TYPE" && d.completed=="1" ? d.display = "Changed sample type settings in '" + d.name + "' (Completed in " + d.delta + " minutes)": null;
   d.task==="FILTER" && d.completed=="1" ? d.display = "Changed data filter settings in '" + d.name + "' (Completed in " + d.delta + " minutes)": null;
   d.task==="UPLOAD" && d.completed=="1" && d.failed===1 ? d.display = "Failed to upload file '" + d.name + ".' Site admins have been contacted.": null;
   d.task==="CONTROL" && d.completed=="1" && d.failed===1 ? d.display = "Failed to change controls in '" + d.name + ".' Site admins have been contacted.": null;
   d.task==="LOG2" && d.completed=="1" && d.failed===1 ? d.display = "Failed to update Log2 transformation settings in '" + d.name + ".' Site admins have been contacted.": null;
   d.task==="IMPUTE" && d.completed=="1" && d.failed===1 ? d.display = "Failed to update imputation settings in '" + d.name + ".' Site admins have been contacted.": null;
   d.task==="REPROCESS" && d.completed=="1" && d.failed===1 ? d.display = "Failed to update data from '" + d.name + ".' Site admins have been contacted.": null;
   d.task==="EDIT" && d.completed=="1" && d.failed===1 ? d.display = "Failed to update data from '" + d.name + ".' Site admins have been contacted.": null;
   d.task==="TYPE" && d.completed=="1" && d.failed===1 ? d.display = "Failed to changed sample type settings in '" + d.name + ".' Site admins have been contacted.": null;
   d.task==="FILTER" && d.completed=="1" && d.failed===1 ? d.display = "Failed to changed data filter settings in '" + d.name + ".' Site admins have been contacted.": null;
   $scope.processes[d.key.toString()] = {display:d.display, delta:d.delta, progress:d.progress, time:d.time, name:d.name, completed:d.completed==="1", icon:d.icon, key:d.key, color:d.color};
 });
});
}

$scope.uploadSettings = {};
$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
$http({
  method: 'POST',
  url: "queryAllFileUploadInfo.php",
  headers: {'Content-Type': 'application/x-www-form-urlencoded'}
}).then (function success(response){
  $scope.uploadSettings = response.data;
});

$scope.redirectEdit = function(id)
{
  $window.location.href="dashboardEdit.php#" + id;
}
$scope.redirectViz = function()
{
  $window.location.href="dashboardVisualization.php";
}
$scope.redirectUpload = function()
{
  $window.location.href="dashboardUpload.php";
}
$scope.redirectMain = function()
{
  $window.location.href="main.php";
}
$scope.redirectInvite = function()
{
  $window.location.href="dashboardInvite.php";
}
$scope.redirectOverview = function()
{
  $window.location.href="dashboard.php";
}

$scope.onSelected = function($item, $select, $event)
{
  $scope.select = $select;
  $scope.collabEmails = [];
  $scope.errorEmails = [];
  angular.forEach($select.selected, function(d){
    validateEmail(d) ? $scope.collabEmails.push(d) : $scope.errorEmails.push(d);
  });
  $scope.checkValidity();
}

$scope.onRemove = function ($select) {
  $scope.select = $select;
  $scope.collabEmails = [];
  $scope.errorEmails = [];
  angular.forEach($select.selected, function(d){
    validateEmail(d) ? $scope.collabEmails.push(d) : $scope.errorEmails.push(d);
  });
  $scope.checkValidity();
}

$scope.checkValidity = function()
{
  if($scope.select.selected===undefined)
  {
   $scope.inviteValid = false;
   return;
 }
 if($scope.collabEmails.length===0 || $scope.errorEmails.length>0 || $scope.permissionLevel===undefined)
 {
  $scope.inviteValid = false;
  return;
}

$scope.inviteValid = true;
return;

}

$scope.clearFields = function()
{
  $scope.select.selected = [];
  $scope.inviteValid = false;
}

$scope.sendInvites = function()
{
  if($scope.inviteValid)
  {
    $http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
    $http({
      method: 'POST',
      url: "sendInvite.php",
      data:  $.param({m:$scope.inviteMessage, e:JSON.stringify($scope.collabEmails), pl:$scope.permissionLevel}),
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).then (function success(response){
      $scope.clearFields();
      $scope.updateInvites();
          //update the side list
        });
  }
}

$scope.updateInvites = function()
{
  $scope.invites = [];
  $http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
  $http({
   method: 'POST',
   url: "getSentInvites.php",
   headers: {'Content-Type': 'application/x-www-form-urlencoded'}
 }).then (function success(response){
  angular.forEach(response.data, function(d){
    $scope.invites.push({label:d.label, activity:d.display, color:d.color, inviteStatus:d.inviteStatus, invitationDate:d.invitation, permission:d.permission, inviter:d.invited});
  });
});
}

});

inviteDataApp.filter('myDateFormat', function myDateFormat($filter){
  return function(text){
    var  tempdate= new Date(text.replace(/-/g,"/"));
    return $filter('date')(tempdate, "medium");
  }
});



inviteDataApp.filter('finished', function (){
  return function(items){
   var filtered = [];
   angular.forEach(items, function(item){
    if (item.completed)
    {
      filtered.push(item);
    }
  });
   return filtered.sort(function(a, b){return new Date(b.time)-new Date(a.time)});
 }
});

inviteDataApp.filter('running', function (){
  return function(items){
   var filtered = [];
   angular.forEach(items, function(item){
    if (!item.completed)
    {
      filtered.push(item);
    }
  });
   return filtered.sort(function(a, b){return new Date(b.time)-new Date(a.time)});
 }
});


function validateEmail(email) {
  var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
  return re.test(email);
}
