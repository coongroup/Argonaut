var overviewDataApp = angular.module('overviewDataApp', ['ngSanitize']);

overviewDataApp.controller('overviewCtrl', function($scope, $http, $filter, $window, $interval){

$scope.projectID='YdgiHoj';
	$scope.projectName="";
	$scope.projectDescription=""; 
	$scope.totalMeas = 0;
	$scope.uniqueMol = 0;
	$scope.filesUploaded =0;
	$scope.invitedCollabs = 0;

 $http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
 $http({
  method: 'POST',
  url: "queryProjectInfo.php",
  headers: {'Content-Type': 'application/x-www-form-urlencoded'}
}).then (function success(response){
  console.log(response.data);
  $scope.projectName=response.data.name;
  $scope.projectDescription =response.data.description;
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
$scope.events = [];
$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
$http({
 method: 'POST',
 url: "queryProjectActivity.php",
 headers: {'Content-Type': 'application/x-www-form-urlencoded'}
}).then (function success(response){
  angular.forEach(response.data, function(d){
   if(d.activity=="CREATION")
   {
     $scope.events.push({icon:"icon-flask", label:"label-success", color:"bg-green", activity:"Project Created", description:d.description, time: d.t});
   }
   if(d.activity=="UPLOAD")
   {
     $scope.events.push({icon:"icon-upload", label:"label-warning", color:"bg-orange", activity:"File Uploaded", description:d.description, time: d.t});
   }
   if(d.activity.indexOf("Name Update") !== -1)
   {
     $scope.events.push({icon:"icon-pencil", label:"label-info", color:"bg-blue", activity:d.activity, description:d.description, time: d.t});
   }
   if(d.activity.indexOf("DELETE") !== -1)
   {
     $scope.events.push({icon:"icon-eraser", label:"label-danger", color:"bg-red", activity:d.activity, description:d.description, time: d.t});
   }
   if(d.activity.indexOf("CONTROL") !== -1)
   {
     $scope.events.push({icon:"icon-compass", label:"label-yellow", color:"bg-yellow", activity:d.activity, description:d.description, time: d.t});
   }
   if(d.activity.indexOf("LOG2") !== -1)
   {
     $scope.events.push({icon:"icon-calculator", label:"label-purple", color:"bg-purple", activity:d.activity, description:d.description, time: d.t});
   }
   if(d.activity.indexOf("IMPUTATION") !== -1)
   {
     $scope.events.push({icon:"icon-plug", label:"label-azure", color:"bg-azure", activity:d.activity, description:d.description, time: d.t});
   }
   if(d.activity.indexOf("TYPE RESELECT") !== -1)
   {
     $scope.events.push({icon:"icon-flask", label:"label-primary", color:"bg-primary", activity:d.activity, description:d.description, time: d.t});
   }
   if(d.activity.indexOf("FILTER") !== -1)
   {
     $scope.events.push({icon:"icon-scissors", label:"bg-twitter", color:"bg-twitter", activity:d.activity, description:d.description, time: d.t});
   }
   if(d.activity.indexOf("FAILURE") !== -1)
   {
     $scope.events.push({icon:"icon-exclamation-circle", label:"label-danger", color:"bg-red", activity:"Data Processing Failure", description:d.description, time: d.t});
   }
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
});

overviewDataApp.filter('myDateFormat', function myDateFormat($filter){
  return function(text){
    var  tempdate= new Date(text.replace(/-/g,"/"));
    return $filter('date')(tempdate, "medium");
  }
});

overviewDataApp.controller('hierarchyTreeCtrl',  function($scope, $http){
  $scope.title = "hierarchyTreeCtrl";
  $http({
    method:'POST',
    url: "queryAllConditions.php",
    headers:{'Content-Type': 'application/x-www-form-urlencoded'}
  }).then (function success(responseOne){
    var controlDict = [];
    angular.forEach(responseOne.data, function(d){
     controlDict[d.condition_id] = "FALSE";
     if (d.is_control==1)
     {
      controlDict[d.condition_id]="TRUE";
    }
  });

    $http({
      method: 'POST',
      url: "queryTree.php",
      data: $.param({
       pi: 1
     }),
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).then (function success(response){
      response.data.sort(function(a, b) {

        return b.value.localeCompare(a.value);

      });
      var dataMap = response.data.reduce(function(map, node) {
        map[node.name] = node;
        return map;
      }, {}); 

      var treeData = [];
      var repCount = 0;

      angular.forEach(response.data, function(node){
       if (node.name.charAt(node.name.length-1)=='C')
       {
        node.control = controlDict[node.name];
      }
      var parent = dataMap[node.parent];
      if (parent) {
                        // create child array if it doesn't exist
                        if (node.name.charAt(node.name.length-1)=='R')
                        {
                        	if (parent._children==undefined)
                        	{
                        		parent._children = [];
                        	}
                        	parent._children.push
                        	(node);
                        	node.control = controlDict[parent.name];
                        }
                        else
                        {
                          if (parent.children==undefined)
                          {
                           parent.children = [];
                         }
                         parent.children.push(node);
                       }
                     } else {
                        // parent is null or missing
                        treeData.push(node);
                      }
                      if (node.name.charAt(node.name.length-1)=='R')
                      {
                        repCount++;
                      };

                    });

angular.forEach(dataMap, function(node){
 if (node.children != null)
 {
   if (node.children.length > 10)
   {
    node._children = node.children;
    node.children = null;
  }
}
});
$scope.repCount= repCount;
var root_tree = null;
root_tree = treeData[0];
$scope.root_tree = root_tree;
});
});
});

overviewDataApp.filter('finished', function (){
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

overviewDataApp.filter('running', function (){
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

overviewDataApp.directive('projectHierarchyTree', [function(){
	var margin_tree = null;
  var width_tree = null;
  var height_tree = null;
  var duration_tree = 1000;
  var root_tree=null;
  var tree = null;
  var diagonal_tree = null;
  var tree_chart = null;
  var tree_i=0;
      //margin stuff here
      //angular.element(window)[0].innerWidth
      var full_page_width = $('#treePanel')[0].clientWidth;
      margin_tree = {top: (full_page_width * .02), right:(full_page_width * .02), 
        bottom: (full_page_width * .01), left: (full_page_width * .02)},
        width_tree = (full_page_width * .72) - margin_tree.left - margin_tree.right,
        height_tree = (full_page_width * .50) - margin_tree.top - margin_tree.bottom;

        return {
          restrict: 'EA',
          scope: {
            data: "=",
            label: "@",
            onClick: "&"
          },
          link: function(scope, iElement, iAttrs) {

            var tree_chart =  d3.select('#treePane').append("svg")
            .attr("width", width_tree + margin_tree.right + margin_tree.left)
            .attr("height", height_tree + margin_tree.top + margin_tree.bottom)
            .attr("id", "hierarchyTreePane")
            .append("g");

            var project_label = tree_chart.append("text").text("Project").attr("y",100).attr("x",100).attr("opacity", "0").attr("font-size","1em").attr("id", "projectLabelTree").attr("font-style", "italic");
            var branch_label = tree_chart.append("text").text("Branches").attr("y",100).attr("x",100).attr("opacity", "0").attr("font-size","1em").attr("id", "branchLabelTree").attr("font-style", "italic");
            var set_label = tree_chart.append("text").text("Sets").attr("y",100).attr("x",100).attr("opacity", "0").attr("font-size","1em").attr("id", "setLabelTree").attr("font-style", "italic");
            var condition_label = tree_chart.append("text").text("Conditions").attr("y",100).attr("x",100).attr("opacity", "0").attr("font-size","1em").attr("id", "conditionLabelTree").attr("font-style", "italic");
            var replicate_label = tree_chart.append("text").text("Replicates").attr("y",100).attr("x",100).attr("opacity", "0").attr("font-size","1em").attr("id", "replicateLabelTree").attr("font-style", "italic");



            scope.$watch(
             function () {
               return [$('#treePanel')[0].offsetWidth, $('#treePanel')[0].offsetHeight].join('x');
             },
             function (value) {
              scope.update(scope.data);
            }
            )

            window.onresize = function() {
              scope.$root.$broadcast('myresize');
            };



            scope.$on('myresize', function()
            {
              scope.update(scope.data);
            });
            
            
          // watch for data changes and re-render
          scope.$watch('data', function(newVals, oldVals) {
           scope.update(scope.data);
         });

          var repCount = 0;

          scope.update= function(data)
          {
            if (data != null)
            {
              treeDisplayed = true;
              repCount = 0;
              condCount = 0;
              scope.recurse(scope.$parent.root_tree);
              var tmpHeight = Math.max((repCount*20),500);
              tmpHeight = Math.max(tmpHeight, (condCount* 20));
              var full_page_width = $('#treePanel')[0].clientWidth;
              width_tree = Math.max((full_page_width * 0.72),400) - margin_tree.left - margin_tree.right;
              tree_height = tmpHeight - margin_tree.top - margin_tree.bottom;

              $('#hierarchyTreePane').attr("height", tmpHeight + margin_tree.top + margin_tree.bottom);
              $('#hierarchyTreePane').attr("width", "100%");

              //width_tree = ($('#hierarchyTreePane').width()) ;

              tree = d3.layout.tree()
              .size([tmpHeight, width_tree]);

              diagonal_tree = d3.svg.diagonal()
              .projection(function(d) { return [d.y, d.x]; });

              root_tree = scope.$parent.root_tree;

              var nodes = tree.nodes(root_tree).reverse(),
              links = tree.links(nodes);


            // Normalize for fixed-depth.
            var maxDepth = d3.max(nodes, function(d){ return d.depth;});

            if (maxDepth == 4)
            {
              nodes.forEach(function(d) { d.y = ((d.depth) *(width_tree*.94)/(Math.max(maxDepth,1))) + margin_tree.left + 110; });
            }
            else
            {
              nodes.forEach(function(d) { d.y = ((d.depth) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110; });
            }

            switch(maxDepth)
            {
              case 0:
              project_label.transition().duration(1000).attr("opacity", 1).attr("y", tree_height + 55).attr("x",((0) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
              branch_label.transition().duration(1000).attr("opacity", 0).attr("y", tree_height + 55).attr("x",((1) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
              set_label.transition().duration(1000).attr("opacity", 0).attr("y", tree_height + 55).attr("x",((2) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
              condition_label.transition().duration(1000).attr("opacity", 0).attr("y", tree_height + 55).attr("x",((3) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
              replicate_label.transition().duration(1000).attr("opacity", 0).attr("y", tree_height + 55).attr("x",((4) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
              break;
              case 1:
              project_label.transition().duration(1000).attr("opacity", 1).attr("y", tree_height + 55).attr("x",((0) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
              branch_label.transition().duration(1000).attr("opacity", 1).attr("y", tree_height + 55).attr("x",((1) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
              set_label.transition().duration(1000).attr("opacity", 0).attr("y", tree_height + 55).attr("x",((2) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
              condition_label.transition().duration(1000).attr("opacity", 0).attr("y", tree_height + 55).attr("x",((3) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
              replicate_label.transition().duration(1000).attr("opacity", 0).attr("y", tree_height + 55).attr("x",((4) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
              break;
              case 2:
              project_label.transition().duration(1000).attr("opacity", 1).attr("y", tree_height + 55).attr("x",((0) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
              branch_label.transition().duration(1000).attr("opacity", 1).attr("y", tree_height + 55).attr("x",((1) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
              set_label.transition().duration(1000).attr("opacity", 1).attr("y", tree_height + 55).attr("x",((2) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
              condition_label.transition().duration(1000).attr("opacity", 0).attr("y", tree_height + 55).attr("x",((3) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
              replicate_label.transition().duration(1000).attr("opacity", 0).attr("y", tree_height + 55).attr("x",((4) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
              break;
              case 3:
              project_label.transition().duration(1000).attr("opacity", 1).attr("y", tree_height + 55).attr("x",((0) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
              branch_label.transition().duration(1000).attr("opacity", 1).attr("y", tree_height + 55).attr("x",((1) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
              set_label.transition().duration(1000).attr("opacity", 1).attr("y", tree_height + 55).attr("x",((2) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
              condition_label.transition().duration(1000).attr("opacity", 1).attr("y", tree_height + 55).attr("x",((3) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 105).attr("text-anchor","middle").transition().duration(1000);
              replicate_label.transition().duration(1000).attr("opacity", 0).attr("y", tree_height + 55).attr("x",((4) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
              break;
              case 4:
              project_label.transition().duration(1000).attr("opacity", 1).attr("y", tree_height + 55).attr("x",((0) *(width_tree*.94)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
              branch_label.transition().duration(1000).attr("opacity", 1).attr("y", tree_height + 55).attr("x",((1) *(width_tree*.94)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
              set_label.transition().duration(1000).attr("opacity", 1).attr("y", tree_height + 55).attr("x",((2) *(width_tree*.94)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
              condition_label.transition().duration(1000).attr("opacity", 1).attr("y", tree_height + 55).attr("x",((3) *(width_tree*.94)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
              replicate_label.transition().duration(1000).attr("opacity", 1).attr("y", tree_height + 55).attr("x",((4) *(width_tree*.94)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
              break;
            }


    // Update the nodes?
    var node = tree_chart.selectAll("g.node")
    .data(nodes, function(d) { return d.id || (d.id = ++tree_i); });

    // Enter any new nodes at the parent's previous position.
    var nodeEnter = node.enter().append("g")
    .attr("class", "node")
    .attr("transform", function(d) { return "translate(" + data.y0 + "," + data.x0 + ")"; })
    .on("click", scope.click_tree);


    nodeEnter.append("circle")
    .attr("r", 1e-6)
    .style("fill", function(d) { if (d.control=="TRUE") { return d._children ? "#FF9292" : "#fff"; } return d._children ? "lightsteelblue" : "#fff"; })
    .style("stroke", function(d){ if (d.control=="TRUE"){return "#FF2525";} return "steelblue"; });

    nodeEnter.append("text")
    .attr("x", function(d) { return d.children || d._children ? -13 : 13; })
    .attr("dy", ".2em")
    .attr("class","nodeText")
    .attr("text-anchor", function(d) { return d.children || d._children ? "end" : "start"; })
    .text(function(d) { return (d.value); })
    .style("fill-opacity", 1e-6);

    // Transition nodes to their new position.
    var nodeUpdate = node.transition()
    .duration(duration_tree)
    .attr("transform", function(d) { return "translate(" + d.y + "," + d.x + ")"; });

    nodeUpdate.select("circle")
    .attr("r", function (d){ return 10 * (width_tree/1600)})
    .style("fill", function(d) {  if (d.control=="TRUE") { return d._children ? "#FF9292" : "#fff"; } return d._children ? "lightsteelblue" : "#fff"; })
    .style("stroke", function(d) {if (d.control=="TRUE"){return "#FF2525";} return "steelblue"; });

    nodeUpdate.select("text")
    .style("fill-opacity", .9);

    // Transition exiting nodes to the parent's new position.
    var nodeExit = node.exit().transition()
    .duration(duration_tree)
    .attr("transform", function(d) { return "translate(" + data.y + "," + data.x + ")"; })
    .remove();

    nodeExit.select("circle")
    .attr("r", 1e-6);

    nodeExit.select("text")
    .style("fill-opacity", 1e-6);

    // Update the links?
    var link = tree_chart.selectAll("path.link")
    .data(links, function(d) { return d.target.id; });

    // Enter any new links at the parent's previous position.
    link.enter().insert("path", "g")
    .attr("class", "link")
    .attr("d", function(d) {
      var o = {x: data.x0, y: data.y0};
      return diagonal_tree({source: o, target: o});
    });

    // Transition links to their new position.
    link.transition()
    .duration(duration_tree)
    .attr("d", diagonal_tree);

    // Transition exiting nodes to the parent's new position.
    link.exit().transition()
    .duration(duration_tree)
    .attr("d", function(d) {
      var o = {x: data.x, y: data.y};
      return diagonal_tree({source: o, target: o});
    })
    .remove();

    // Stash the old positions for transition.
    nodes.forEach(function(d) {
      d.x0 = d.x;
      d.y0 = d.y;
    });
    tree_chart.selectAll('.nodeText').each(scope.insertLinebreaks);
    if (spinner!= null)
    {
      spinner.stop();
    }
  }

}

scope.insertLinebreaks = function (d) {
  if (d.parent== "null")
  {
    var el = d3.select(this);
    var words = d.value.toString().split(' ');
    el.text('');

    for (var i = 0; i < words.length; i++) {
      var tspan = el.append('tspan').text(words[i] + " ");
      if (el.text().length> 15)
        tspan.attr('x', 0).attr('dy', '15');
    }
  }
}

scope.shortName = function(data)
{
  var returnString = "";
  var tmpString = "";
  var parts = data.split(" ");
  angular.forEach(parts, function function_name(argument)
  {
    tmpString += argument + " ";
    returnString += argument + " ";
    if (tmpString.length > 15)
    {
      returnString += "\r\n";
      tmpString = "";
    }
  });
  return returnString;
}

scope.recurse= function (data)
{
  if (data.name.charAt(data.name.length-1)=='C')
  {
    condCount++;
  }
  if (data.children != null)
  {
    angular.forEach(data.children, function function_name (argument) {
     scope.recurse(argument);

   });
  }
  else
  {
    if (data.children == null && data._children==null)
    {
      repCount++;
    }
  }

}

scope.click_tree = function(d) {
  if (d.children) {
    d._children = d.children;
    d.children = null;
  } else {
    d.children = d._children;
    d._children = null;
  }
  scope.update(d);
}

}       
};
}]);
