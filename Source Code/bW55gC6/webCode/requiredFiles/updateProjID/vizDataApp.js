var vizDataApp = angular.module('vizDataApp', ['ngHandsontable','ngSanitize']);

vizDataApp.controller('vizCtrl', function($scope, $http, $filter, hotRegisterer, $rootScope, $interval, $window){

  $scope.projectID=-1; 
  $scope.projectName=""; 
  $scope.projectDescription="Data visualizations for the web portal can be customized below.";

  $scope.activeProcesses = [];
  $scope.finishedProcesses = [];
  $scope.volcanoCompat = false;
  $scope.barCompat = false;
  $scope.scatterCompat = false;
  $scope.pcaRepCompat = false;
  $scope.pcaCondCompat = false;
  $scope.outlierCompat = false;
  $scope.goCompat = false;

  $scope.volcanoOn = false;
  $scope.barOn = false;
  $scope.scatterOn = false;
  $scope.pcaRepOn = false;
  $scope.pcaCondOn = false;
  $scope.outlierOn = false;
  $scope.goOn = false;

  $scope.originalVizData = [];

  $http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
  $http({
    method: 'POST',
    url: "queryProjectInfo.php",
    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
  }).then (function success(response){
    $scope.projectName=response.data.name;
    $scope.projectDescription = "Data visualizations for the '"+$scope.projectName+"' web portal can be customized below.";
  });


  $http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
  $http({
    method: 'POST',
    url: "queryVizCompat.php",
    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
  }).then (function success(response){
   response.data.bar !== undefined ? $scope.barCompat = response.data.bar : null;
   response.data.volcano !== undefined ? $scope.volcanoCompat = response.data.volcano : null;
   response.data.pcacond !== undefined ? $scope.pcaCondCompat = response.data.pcacond : null;
   response.data.pcarep !== undefined ? $scope.pcaRepCompat = response.data.pcarep : null;
   response.data.scatter !== undefined ? $scope.scatterCompat = response.data.scatter : null;
   response.data.outlier !== undefined ? $scope.outlierCompat = response.data.outlier : null;
   response.data.go !== undefined ? $scope.goCompat = response.data.go : null;
   response.data.hcheatmap!==undefined ? $scope.hcHeatCompat = response.data.hcheatmap : null;
 });

  $http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
  $http({
    method: 'POST',
    url: "queryActiveViz.php",
    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
  }).then (function success(response){
    $scope.originalVizData = [];
    angular.forEach(response.data, function(d){
      switch(d.visualization_id)
      {
        case "volcano":
        $scope.volcanoOn = d.visualization_on==="1";
        d.visualization_on==="1" || d.visualization_on===1 ?  $scope.originalVizData[d.visualization_id]=true :  $scope.originalVizData[d.visualization_id]=false;
        break;
        case "bar":
        $scope.barOn = d.visualization_on==="1";
        d.visualization_on==="1" || d.visualization_on===1 ?  $scope.originalVizData[d.visualization_id]=true :  $scope.originalVizData[d.visualization_id]=false;
        break;
        case "scatter":
        $scope.scatterOn = d.visualization_on==="1";
        d.visualization_on==="1" || d.visualization_on===1 ?  $scope.originalVizData[d.visualization_id]=true :  $scope.originalVizData[d.visualization_id]=false;
        break;
        case "outlier":
        $scope.outlierOn = d.visualization_on==="1";
        d.visualization_on==="1" || d.visualization_on===1 ?  $scope.originalVizData[d.visualization_id]=true :  $scope.originalVizData[d.visualization_id]=false;
        break;
        case "pcarep":
        $scope.pcaRepOn = d.visualization_on==="1";
        d.visualization_on==="1" || d.visualization_on===1 ?  $scope.originalVizData[d.visualization_id]=true :  $scope.originalVizData[d.visualization_id]=false;
        break;
        case "pcacond":
        $scope.pcaCondOn = d.visualization_on==="1";
        d.visualization_on==="1" || d.visualization_on===1 ?  $scope.originalVizData[d.visualization_id]=true :  $scope.originalVizData[d.visualization_id]=false;
        break;
        case "go":
        $scope.goOn = d.visualization_on==="1";
        d.visualization_on==="1" || d.visualization_on===1 ?  $scope.originalVizData[d.visualization_id]=true :  $scope.originalVizData[d.visualization_id]=false;
        break;
        case "hcheatmap":
        $scope.hcHeatOn = d.visualization_on==="1";
        d.visualization_on==="1" || d.visualization_on===1 ? $scope.originalVizData[d.visualization_id]=true : $scope.originalVizData[d.visualization_id]=false;
      }
    });
$scope.vizForm.$invalid = true;
});

$scope.$watch('volcanoOn',function(){ $scope.checkUpdateStatus(); });
$scope.$watch('barOn', function(){ $scope.checkUpdateStatus(); });
$scope.$watch('scatterOn', function(){ $scope.checkUpdateStatus(); });
$scope.$watch('outlierOn', function(){ $scope.checkUpdateStatus(); });
$scope.$watch('pcaRepOn', function(){ $scope.checkUpdateStatus(); });
$scope.$watch('pcaCondOn', function(){ $scope.checkUpdateStatus(); });
$scope.$watch('goOn', function(){ $scope.checkUpdateStatus(); });
$scope.$watch('hcHeatOn', function(){ $scope.checkUpdateStatus(); });

$scope.checkUpdateStatus = function()
{
  if( $scope.originalVizData['volcano']===undefined || $scope.originalVizData['bar']===undefined || $scope.originalVizData['outlier']===undefined || 
    $scope.originalVizData['scatter']===undefined || $scope.originalVizData['pcacond']===undefined || $scope.originalVizData['pcarep']===undefined || 
    $scope.originalVizData['go']===undefined || $scope.originalVizData['hcheatmap']===undefined){
   $scope.vizForm.$invalid = true; return;
} 
$scope.vizForm.$invalid = true;
if ($scope.volcanoOn!== $scope.originalVizData['volcano'])
{
  $scope.vizForm.$invalid = false;
  return;
}
if($scope.barOn!== $scope.originalVizData['bar'])
{
  $scope.vizForm.$invalid = false;
  return;
}
if($scope.scatterOn!== $scope.originalVizData['scatter'])
{
  $scope.vizForm.$invalid = false;
  return;
}
if($scope.outlierOn!== $scope.originalVizData['outlier'])
{
  $scope.vizForm.$invalid = false;
  return;
}
if($scope.pcaRepOn!== $scope.originalVizData['pcarep'] )
{
  $scope.vizForm.$invalid = false;
  return;
}
if($scope.pcaCondOn!== $scope.originalVizData['pcacond'])
{
  $scope.vizForm.$invalid = false;
  return;
}
if($scope.goOn!== $scope.originalVizData['go'])
{
  $scope.vizForm.$invalid = false;
  return;
}
if($scope.hcHeatOn!== $scope.originalVizData['hcheatmap'])
{
  $scope.vizForm.$invalid = false;
  return;
}
}

$scope.updateWebPortal = function()
{
  angular.element(document.getElementById('loader-overlay')).css({'display':'block'});
  $http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
  $http({
    method: 'POST',
    url: "updateVisualizations.php",
    data: $.param({
      v:$scope.volcanoOn, o:$scope.outlierOn, b:$scope.barOn, s:$scope.scatterOn, pcac:$scope.pcaCondOn, pcar:$scope.pcaRepOn, g:$scope.goOn, hch:$scope.hcHeatOn
    }),
    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
  }).then (function success(response){
    console.log(response);
    $scope.updateActiveVisualizations();
    $scope.errorMessage = response.data.message;
    response.data.result ? $scope.errorStatus = "Success!" : $scope.errorStatus = "Unexpected error!";
    angular.element(document.getElementById('errorReportModal')).appendTo('body').modal('show');
    $scope.vizForm.$invalid = true;
  });
}

$scope.sortProcess = function(process) {
  var date = new Date(process.time);
  return date;
};

$scope.dismiss=function()
{
 angular.element(document.getElementById('loader-overlay')).css({'display':'none'});
}

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
$scope.redirectOverview = function()
{
  $window.location.href="dashboard.php";
}
$scope.redirectInvite = function()
{
  $window.location.href="dashboardInvite.php";
}

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

$scope.updateActiveVisualizations = function()
{
 $http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
 $http({
  method: 'POST',
  url: "queryActiveViz.php",
  headers: {'Content-Type': 'application/x-www-form-urlencoded'}
}).then (function success(response){
  $scope.originalVizData = [];
  angular.forEach(response.data, function(d){
    switch(d.visualization_id)
    {
      case "volcano":
      d.visualization_on==="1" || d.visualization_on===1 ?  $scope.originalVizData[d.visualization_id]=true :  $scope.originalVizData[d.visualization_id]=false;
      break;
      case "bar":
      d.visualization_on==="1" || d.visualization_on===1 ?  $scope.originalVizData[d.visualization_id]=true :  $scope.originalVizData[d.visualization_id]=false;
      break;
      case "scatter":
      d.visualization_on==="1" || d.visualization_on===1 ?  $scope.originalVizData[d.visualization_id]=true :  $scope.originalVizData[d.visualization_id]=false;
      break;
      case "outlier":
      d.visualization_on==="1" || d.visualization_on===1 ?  $scope.originalVizData[d.visualization_id]=true :  $scope.originalVizData[d.visualization_id]=false;
      break;
      case "pcarep":
      d.visualization_on==="1" || d.visualization_on===1 ?  $scope.originalVizData[d.visualization_id]=true :  $scope.originalVizData[d.visualization_id]=false;
      break;
      case "pcacond":
      d.visualization_on==="1" || d.visualization_on===1 ?  $scope.originalVizData[d.visualization_id]=true :  $scope.originalVizData[d.visualization_id]=false;
      break;
      case "go":
      d.visualization_on==="1" || d.visualization_on===1 ?  $scope.originalVizData[d.visualization_id]=true :  $scope.originalVizData[d.visualization_id]=false;
      break;
      case "hcheatmap":
      d.visualization_on==="1" || d.visualization_on===1 ?  $scope.originalVizData[d.visualization_id]=true :  $scope.originalVizData[d.visualization_id]=false;
      break;
    }
  });
});
}


});

vizDataApp.filter('finished', function (){
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

vizDataApp.filter('running', function (){
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

vizDataApp.filter('myDateFormat', function myDateFormat($filter){
  return function(text){
    var  tempdate= new Date(text.replace(/-/g,"/"));
    return $filter('date')(tempdate, "medium");
  }
});

vizDataApp   .directive('bootstrapSwitch', [
  function() {
    return {
      restrict: 'A',
      require: '?ngModel',
      link: function(scope, element, attrs, ngModel) {
        element.bootstrapSwitch();

        element.on('switchChange.bootstrapSwitch', function(event, state) {
          if (ngModel) {
            scope.$apply(function() {
              ngModel.$setViewValue(state);
            });
          }
        });

        scope.$watch(attrs.ngModel, function(newValue, oldValue) {
          if (newValue) {
            element.bootstrapSwitch('state', true, true);
          } else {
            element.bootstrapSwitch('state', false, true);
          }
        });
      }
    };
  }
  ]);
