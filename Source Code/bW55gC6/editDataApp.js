var editDataApp = angular.module('editDataApp', ['ngHandsontable','ngSanitize']);

editDataApp.controller('editCtrl', function($scope, $http, $filter, hotRegisterer, $rootScope, $interval, $window){

$scope.projectID='bW55gC6';
  $scope.projectName=""; //needs to be updated in copy process
  $scope.projectDescription="All data uploaded to '' can be edited, updated, or deleted below." //needs to be updated in copy process
  $scope.dataTypes = ['Project', 'Branch', 'Set', 'Condition', 'Replicate'];
  $scope.deleteTypes = ['Branch', 'Set', 'Condition', 'Replicate'];
  $scope.renameDataType = "";
  $scope.renameDataSpecific = [];
  $scope.renameName = "";
  $scope.renameDataSelectedData = null;
  $scope.original_tree = {};
  $scope.display_tree = {};
  $scope.editOperation = "";
  $scope.currentEdit="";
  $scope.activeProcesses = [];
  $scope.finishedProcesses = [];
  $scope.originalProjectDescription = "";
  $scope.userProjectDescription = "";
  $scope.dataFilters = [{filter:"No Filter", value:"NONE"}, {filter:"Filter by Total Measurements", value:"TOTAL"}, {filter:"Filter by Measurements per Condition", value:"COND"}];

  $http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
  $http({
    method: 'POST',
    url: "queryProjectInfo.php",
    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
  }).then (function success(response){
    $scope.projectName=response.data.name;
    $scope.projectDescription = "All data uploaded to '"+$scope.projectName+"' can be edited, updated, or deleted below.";
    $scope.originalProjectDescription = response.data.description;
    $scope.userProjectDescription = response.data.description;
  });


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

$scope.sortProcess = function(process) {
  var date = new Date(process.time);
  return date;
};

$scope.uploadSettings = {};
$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
$http({
  method: 'POST',
  url: "queryAllFileUploadInfo.php",
  headers: {'Content-Type': 'application/x-www-form-urlencoded'}
}).then (function success(response){
  $scope.uploadSettings = response.data;
});

  //Code for 'Rename Data'
  $scope.renameDataTypeChange = function(){
    $scope.renameName = "";
    $scope.renameDataSelectedData = null;
    $http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
    $http({
      method: 'POST',
      url: "queryRename.php",
      data: $.param({
        t:$scope.renameDataType
      }),
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).then (function success(response){
      $scope.renameDataSpecific = [];
      angular.forEach(response.data, function(d)
      {
        var tmpArray = {name:d.name, id:d.id, group:d.group};
        $scope.renameDataSpecific.push(tmpArray);
      });
    });
  }

  $scope.$watch('renameDataSelectedData', function(){
    $scope.renameName===null ? 
    $scope.renameForm.renameDataSpecificSelect.$invalid=true : $scope.renameForm.renameDataSpecificSelect.$invalid=false;
  });

  $scope.doRename = function()
  {
    $scope.rename_tree_data = angular.copy($scope.rawTreeData);
    var renameIDs = []; var parentIDs =[];
    renameIDs[$scope.renameDataSelectedData.id]="";
    $scope.getRenameParentIDs($scope.rename_tree_data, $scope.renameDataSelectedData.id, parentIDs);
    $scope.rename_tree=angular.copy($scope.original_tree);
    $scope.updateRenameTree($scope.rename_tree[0], renameIDs, parentIDs);
    $scope.display_tree = $scope.rename_tree[0];
    $scope.editOperation = "Confirm Updated Names";
    $scope.editDescription = "Nodes to be renamed are shown below in green, please review the proposed changes before confirming."
    $scope.buttonText = "Confirm Rename";
    $scope.currentEdit="RENAME";
    $scope.showModal();
  }

  $scope.updateRenameTree = function(obj, idList, parentList)
  {
    if (idList[obj.name]!==undefined)
    {
      obj.color="#14D600";
      obj.childColor="#14D600";
      obj.border="#0E8F00";
      obj.bold=true;
      obj.value = $scope.renameName;
    }
    if (parentList[obj.name]!==undefined)
    {
     if (obj._children!==undefined)
     {
      if (obj._children.length>0)
      {
        obj.children = obj._children;
        obj._children = undefined;
      }
    }
  }

  angular.forEach(obj.children, function(d){
    $scope.updateRenameTree(d, idList, parentList);
  });
  angular.forEach(obj._children, function(d){
    $scope.updateRenameTree(d, idList, parentList);
  });
}

$scope.getRenameParentIDs = function(treeData, id, parentList)
{
  var selection = treeData.filter(function(d){return d.name===id;});
  angular.forEach(selection, function(d){
    parentList[d.parent]="";
    $scope.getDeleteParentIDs(treeData, d.parent, parentList);
  });
}

//Code for 'Update Project Description'

$scope.$watch('userProjectDescription', function(){
 $scope.userProjectDescription===$scope.originalProjectDescription ? $scope.descriptionForm.$invalid = true :  $scope.descriptionForm.$invalid = false;
});

$scope.updateDescription = function()
{
  if($scope.originalProjectDescription!==$scope.userProjectDescription && $scope.userProjectDescription.length > 1)
  {
    angular.element(document.getElementById('loader-overlay')).css({'display':'block'});
    $http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
    $http({
      method: 'POST',
      url: "updateProjectDescription.php",
      data: $.param({
        n:$scope.userProjectDescription
      }),
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).then (function success(response){
      console.log(response);
      $scope.errorMessage = response.data.message;
      response.data.result ? $scope.errorStatus = "Success!" : $scope.errorStatus = "Unexpected error!";
      $scope.updateAllData();
      angular.element(document.getElementById('errorReportModal')).appendTo('body').modal('show');
      $scope.originalProjectDescription = $scope.userProjectDescription;
    });
  }
}


  //Code for 'Reselect Controls'
  $scope.allSets = [];
  $scope.controlSetData = [];
  $scope.control_condition = "";

  $http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
  $http({
    method: 'POST',
    url: "queryAllSets.php",
    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
  }).then (function success(response){
    angular.forEach(response.data, function(d)
    {
      var tmpArray = {name:d.name, id:d.id, group:d.group};
      $scope.allSets.push(tmpArray);
    });
  });

  $scope.controlSetChange = function()
  {
    if ($scope.allSets.length>0)
    {
      $scope.controlSetData = [];
      $http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
      $http({
        method: 'POST',
        url: "querySetQuantData.php",
        data: $.param({
          si:$scope.selectedControlSet.id
        }),
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
      }).then (function success(response){
        angular.forEach(response.data, function(d)
        {
          var tmpArray = {header:d.header, condName:d.cond, repName:d.rep, control:d.control};
          d.control==="1" ? $scope.control_condition = d.cond : null;
          $scope.controlSetData.push(tmpArray);
        });
        $scope.controlSetData =$scope.controlSetData.filter(function(item){return item.control!==null && item.control!==undefined;});
      });
      $scope.controlSetData =$scope.controlSetData.filter(function(item){return item.control!==null && item.control!==undefined;});
      $scope.reselectControlForm.$invalid=true;
    }
  }

  $scope.controlTableAfterChange = function(changes, source)
  {
    if(changes!==null)
    {
      if(changes[0][1]==="control")
      {
        var row = changes[0][0];
        var condName = $scope.controlSetData[row].condName;
        var state = changes[0][3];
        var oldState = changes[0][2];
        state==="1" &&  condName===$scope.control_condition ? $scope.reselectControlForm.$invalid=true : $scope.reselectControlForm.$invalid=false;
        angular.forEach($scope.controlSetData.filter(function(item){return item.control!==null && item.control!==undefined;}), function(cond){
          cond.condName===condName ? cond.control=state : cond.control="0";
        });
      }
    }
  }

  $scope.updateControls = function()
  {
    $scope.control_tree=angular.copy($scope.original_tree);
    var control_cond_name = "";
    var names = [];
    angular.forEach($scope.controlSetData, function(d){
      if (d.control=="1")
      {
       control_cond_name = d.condName; names.push(d.condName); names.push(d.repName);
     }
   });
    var idList = []; var parentList = [];
    $scope.control_tree_data = angular.copy($scope.rawTreeData);
    $scope.getControlTreeIDs($scope.control_tree_data, names, idList);
    var finalIDList = [];
    angular.forEach(idList, function (d){
      $scope.getControlParentIDs($scope.control_tree_data, d, parentList);
      finalIDList[d]="";
    });
    $scope.updateControlTree($scope.control_tree[0], finalIDList, parentList);
    $scope.display_tree = $scope.control_tree[0];
    $scope.editOperation = "Confirm Control Condition Updates";
    $scope.editDescription = "New control selections are shown below in green and existing control conditions are displayed in red. Please review these changes before confirming."
    $scope.buttonText = "Confirm Control Update";
    $scope.currentEdit="CONTROL";
    $scope.showModal();
  }

  $scope.getControlTreeIDs = function(treeData, nameList, mainList)
  {
   angular.forEach(nameList, function(d){
     var selection = treeData.filter(function(f){return f.value===d;});
     angular.forEach(selection, function(e){
      mainList.push(e.name);
    });
   });
 }
 $scope.getControlParentIDs = function(treeData, id, parentList)
 {
  var selection = treeData.filter(function(d){return d.name===id;});
  angular.forEach(selection, function(d){
    parentList[d.parent]="";
    $scope.getControlParentIDs(treeData, d.parent, parentList);
  });
}

$scope.updateControlTree = function(obj, idList, parentList)
{
  if (idList[obj.name]!==undefined)
  {
    obj.color="#14D600";
    obj.childColor="#14D600";
    obj.border="#0E8F00";
    obj.bold=true;
    if (obj._children!==undefined)
    {
      if (obj._children.length>0)
      {
        obj.children = obj._children;
        obj._children = undefined;
      }
    }
  }
  else
  {
      /* obj.color="#fff";
        obj.border="steelblue";
        obj.childColor="lightsteelblue";
         obj.bold=false;
         obj.control="0";*/
       }
       if (parentList[obj.name]!==undefined)
       {
         if (obj._children!==undefined)
         {
          if (obj._children.length>0)
          {
            obj.children = obj._children;
            obj._children = undefined;
          }
        }
      }

      angular.forEach(obj.children, function(d){
        $scope.updateControlTree(d, idList, parentList);
      });
      angular.forEach(obj._children, function(d){
        $scope.updateControlTree(d, idList, parentList);
      });
    }

//Code for 'Change Sample Type'
$scope.typeData = [];
$scope.originalTypeData = [];
$scope.chosenColumns = [];
$scope.typeDataValid = false;
$scope.organisms = {};
$scope.standardIdentifiers = {};

$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
$http({
  method: 'POST',
  url: "queryOrganisms.php",
  headers: {'Content-Type': 'application/x-www-form-urlencoded'}
}).then (function success(response){
  $scope.organisms=response.data;
});

$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
$http({
  method: 'POST',
  url: "queryStandardIdentifiers.php",
  headers: {'Content-Type': 'application/x-www-form-urlencoded'}
}).then (function success(response){
  $scope.standardIdentifiers=response.data;
});

$scope.typeSetChange = function()
{
  if($scope.selectedTypeSet===undefined || $scope.selectedTypeSet===null)
  {
    return;
  }
  if($scope.selectedTypeSet.id===undefined || $scope.selectedTypeSet.id===null)
  {
    return;
  }
  $scope.organismType = {};
  $scope.chosenColumns = [];
  $scope.standardIdentifierType = {};
  $scope.standardIdentifierCol = {};
  $scope.typeDataValid = false;
  $http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
  $http({
    method: 'POST',
    url: "querySampleType.php",
    data: $.param({
      si:$scope.selectedTypeSet.id
    }),
    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
  }).then (function success(response){
    if (response.data.length===1)
    {
     $scope.originalTypeData = response.data[0];
     var feature_descriptors_list = JSON.parse(response.data[0].feature_descriptors);
     var identifiers_list = JSON.parse(response.data[0].identifier);
     angular.forEach(identifiers_list, function(d){
      $scope.chosenColumns.push({group:"Unique Identifier Column", name:"Column Header: " + d.header + " | Identifier Name: " + d.userName, id:d.header});
    });
     angular.forEach(feature_descriptors_list, function(d){
      $scope.chosenColumns.push({group:"Feature Descriptor Columns", name:"Column Header: " + d.header + " | Descriptor Name: " + d.userName, id:d.header});
    });
     var selectedColumn = $scope.chosenColumns.filter(function(item){return item.id===$scope.originalTypeData.standard_id_column;});
     selectedColumn.length===1 ? $scope.standardIdentifierCol = selectedColumn[0] : null;
     var selectedOrganism = $scope.organisms.filter(function(item){return item.id===$scope.originalTypeData.organism_id;});
     selectedOrganism.length===1 ? $scope.organismType = selectedOrganism[0] : null; 
     var selectedIDType = $scope.standardIdentifiers.filter(function(item){return item.id===$scope.originalTypeData.standard_id_type;});
     selectedIDType.length===1 ? $scope.standardIdentifierType = selectedIDType[0] : null;
     $scope.checkTypeData();
   }
 });
}

$scope.organismTypeChange = function()
{
  $scope.checkTypeData();
}

$scope.standardIDColumnChange = function()
{
 $scope.checkTypeData();
}

$scope.standardIDTypeChange = function()
{
 $scope.checkTypeData();
}

$scope.checkTypeData = function()
{
  if ($scope.originalTypeData!==undefined)
  {
    var undefCt = 0; 
    $scope.standardIdentifierCol.id===undefined ? undefCt++ : null;
    $scope.standardIdentifierType.id===undefined ? undefCt++ : null;
    $scope.organismType.id===undefined ? undefCt++ : null;
    undefCt < 3 && undefCt >= 1 ?$scope.typeDataValid = false : null;
    if ($scope.standardIdentifierCol.id===undefined && $scope.standardIdentifierType.id===undefined && $scope.organismType.id===undefined)
    {
      if($scope.originalTypeData.organism_id==="-1" || $scope.standard_id_column==="" || $scope.standard_id_type==="-1")
      {
        $scope.typeDataValid = false;
      }
      else
      {
        $scope.typeDataValid = true;
      }
    }
    if($scope.standardIdentifierCol.id!==undefined && $scope.standardIdentifierType.id!==undefined && $scope.organismType.id!==undefined)
    {
     $scope.originalTypeData.organism_id===$scope.organismType.id && $scope.originalTypeData.standard_id_column===$scope.standardIdentifierCol.id 
     && $scope.originalTypeData.standard_id_type===$scope.standardIdentifierType.id ? $scope.typeDataValid = false : $scope.typeDataValid = true;
   }
 }
 else
 {
  $scope.typeDataValid = false;
}
}

$scope.clearSampleType = function()
{
  $scope.organismType = {};
  $scope.standardIdentifierType = {};
  $scope.standardIdentifierCol = {};
  $scope.checkTypeData();
}

$scope.updateSampleType = function()
{
  if($scope.selectedTypeSet.id!==undefined)
  { 
    $scope.type_tree=angular.copy($scope.original_tree);
    $scope.type_tree_data = angular.copy($scope.rawTreeData);
    var sets = []; var parentList= []; var idList = [];
    sets.push($scope.selectedTypeSet.id);
    $scope.getTypeParentIDs($scope.type_tree_data, $scope.selectedTypeSet.id, parentList); idList[$scope.selectedTypeSet.id]="";
    $scope.updateTypeTree($scope.type_tree[0], idList, parentList);
    $scope.display_tree = $scope.type_tree[0];
    $scope.editOperation = "Confirm Sample Type Updates";
    var orgName = $scope.organismType.name!==undefined ? $scope.organismType.name : "Not Specified";
    var standardIDColumn = $scope.standardIdentifierCol.id!==undefined ? $scope.standardIdentifierCol.id : "Not Specified";
    var standardIDType = $scope.standardIdentifierType.id!==undefined ? $scope.standardIdentifierType.name : "Not Specified";
    $scope.editDescription = "The set will be updated with the following type settings – Organism: " + orgName 
    + " | Standard Identifier Column: " + standardIDColumn + " | Standard Identifier: " + standardIDType;
    $scope.buttonText = "Confirm Sample Type Update";
    $scope.currentEdit="TYPE";
    $scope.showModal();
  }
}

$scope.getTypeParentIDs = function(treeData, id, parentList)
{
  var selection = treeData.filter(function(d){return d.name===id;});
  angular.forEach(selection, function(d){
    parentList[d.parent]="";
    $scope.getTransformParentIDs(treeData, d.parent, parentList);
  });
}

$scope.updateTypeTree = function(obj, idList, parentList)
{
  if (idList[obj.name]!==undefined)
  {
    obj.color="#14D600";
    obj.childColor="#14D600";
    obj.border="#0E8F00";
    obj.bold=true;
    if (obj._children!==undefined)
    {
      if (obj._children.length>0)
      {
        obj.children = obj._children;
        obj._children = undefined;
      }
    }
  }
  else
  {
      /* obj.color="#fff";
        obj.border="steelblue";
        obj.childColor="lightsteelblue";
         obj.bold=false;
         obj.control="0";*/
       }
       if (parentList[obj.name]!==undefined)
       {
         if (obj._children!==undefined)
         {
          if (obj._children.length>0)
          {
            obj.children = obj._children;
            obj._children = undefined;
          }
        }
      }

      angular.forEach(obj.children, function(d){
        $scope.updateTransformTree(d, idList, parentList);
      });
      angular.forEach(obj._children, function(d){
        $scope.updateTransformTree(d, idList, parentList);
      });
    }

 //Code for Update Data Filter
 $scope.filterDataValid = false;
 $scope.originalFilterData = {};
 $scope.totalReplicates = 0;
 $scope.calcReplicates = 0;
 $scope.filterSetChange = function()
 {
  if($scope.selectedFilterSet!==null)
  {
    $scope.minTotalReps = 0;
    $scope.minRepsPerCond = 0;
    $scope.minConditions = 0;
    $scope.filterDataValid = false;
    $http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
    $http({
      method: 'POST',
      url: "querySetFilter.php",
      data: $.param({
        si:$scope.selectedFilterSet.id
      }),
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).then (function success(response){
      if(response.data.filter!==undefined && response.data.condCount!==undefined && response.data.repCount!==undefined){
       var filter_obj = JSON.parse(response.data.filter);
       $scope.originalFilterData = {filter:filter_obj, condCount:response.data.condCount, repCount:response.data.repCount};
       $scope.totalReplicates = $scope.originalFilterData.repCount;
       $scope.totalConditions = $scope.originalFilterData.condCount;
       switch($scope.originalFilterData.filter.type)
       {
        case 'TOTAL': $scope.minTotalReps = $scope.originalFilterData.filter.p1; $scope.calcReplicates = (($scope.minTotalReps)/100)*$scope.totalReplicates; break;
        case 'COND' : $scope.minRepsPerCond = $scope.originalFilterData.filter.p1; $scope.minConditions=$scope.originalFilterData.filter.p2; break;
      }
      angular.forEach($scope.dataFilters, function(d){
        filter_obj.type===d.value ? $scope.dataFilter = d : null;
      });
    }
  });
  }
}

$scope.totalRepsChange = function()
{
  if($scope.minTotalReps!=="")
  {
    if($scope.minTotalReps>= 0 && $scope.minTotalReps<=100)
    {
      $scope.calcReplicates = Math.round((($scope.minTotalReps)/100)*$scope.totalReplicates);
    }
  }
}

$scope.totalFilterValid = function()
{
  if($scope.minTotalReps==="")
  {
    return false;
  }
  if($scope.minTotalReps>= 0 && $scope.minTotalReps<=100)
  {
    return true;
  }
  return false;
}

$scope.condFilterValid = function()
{
  if ($scope.minRepsPerCond==="" || $scope.minConditions==="")
  {
    return false;
  }
  if ($scope.minRepsPerCond >=0 && $scope.minRepsPerCond<=100 && $scope.minConditions >=0 && $scope.minConditions <= $scope.totalConditions)
  {
    return true;
  }
  return false;
}

$scope.checkFilterValid = function()
{
    //check if the selected filter is the same as the original 
    if($scope.dataFilter.value===$scope.originalFilterData.filter.type)
    {
      //if yes check for differences in settings
      switch($scope.dataFilter.value)
      {
        case 'NONE': $scope.filterDataValid = false; break;
        case 'TOTAL': 
        if(!$scope.totalFilterValid())
        { 
          $scope.filterDataValid = false;
        }
        else
        {
          $scope.filterDataValid = parseInt($scope.minTotalReps)!==parseInt($scope.originalFilterData.filter.p1); 
        }
        break;
        case 'COND': 
        if(!$scope.condFilterValid())
        {
          $scope.filterDataValid = false;
        }
        else
        {
          $scope.filterDataValid = (parseInt($scope.minRepsPerCond)!==parseInt($scope.originalFilterData.filter.p1)) || (parseInt($scope.minConditions)!==parseInt($scope.originalFilterData.filter.p2));
        }
        break;
      }
    }
    else
    {
       //if no then check for complete settings
       switch($scope.dataFilter.value)
       {
        case 'NONE': $scope.filterDataValid = true; break;
        case 'TOTAL': $scope.filterDataValid = $scope.totalFilterValid(); break;
        case 'COND': $scope.filterDataValid = $scope.condFilterValid(); break;
      }
    }
  }

  $scope.updateFilterType = function()
  {
    if($scope.selectedFilterSet.id!==undefined)
    { 
      $scope.filter_tree=angular.copy($scope.original_tree);
      $scope.filter_tree_data = angular.copy($scope.rawTreeData);
      var sets = []; var parentList= []; var idList = [];
      sets.push($scope.selectedFilterSet.id);
      $scope.getFilterParentIDs($scope.filter_tree_data, $scope.selectedFilterSet.id, parentList); idList[$scope.selectedFilterSet.id]="";
      $scope.updateFilterTree($scope.filter_tree[0], idList, parentList);
      $scope.display_tree = $scope.filter_tree[0];
      $scope.editOperation = "Confirm Data Filter Updates";
      switch($scope.dataFilter.value)
      {
        case 'NONE': $scope.editDescription = "No data filter will be applied to the set shown in green. Please confirm this change before proceeding."; break;
        case 'TOTAL': $scope.editDescription = "Values observed in less than " + $scope.calcReplicates + " of " + $scope.totalReplicates + " replicates will be eliminated from the set shown in green. Please confirm this change before proceeding."; break;
        case 'COND': $scope.editDescription = "Values observed in fewer than " + $scope.minRepsPerCond + "% of replicates in at least " + $scope.minConditions + " conditions will be eliminated from the set shown in green. Please confirm this change before proceeding."; break;
      }
      $scope.buttonText = "Confirm Data Filter Update";
      $scope.currentEdit="FILTER";
      $scope.showModal();
    }
  }

  $scope.getFilterParentIDs = function(treeData, id, parentList)
  {
    var selection = treeData.filter(function(d){return d.name===id;});
    angular.forEach(selection, function(d){
      parentList[d.parent]="";
      $scope.getTransformParentIDs(treeData, d.parent, parentList);
    });
  }

  $scope.updateFilterTree = function(obj, idList, parentList)
  {
    if (idList[obj.name]!==undefined)
    {
      obj.color="#14D600";
      obj.childColor="#14D600";
      obj.border="#0E8F00";
      obj.bold=true;
      if (obj._children!==undefined)
      {
        if (obj._children.length>0)
        {
          obj.children = obj._children;
          obj._children = undefined;
        }
      }
    }
    else
    {
      /* obj.color="#fff";
        obj.border="steelblue";
        obj.childColor="lightsteelblue";
         obj.bold=false;
         obj.control="0";*/
       }
       if (parentList[obj.name]!==undefined)
       {
         if (obj._children!==undefined)
         {
          if (obj._children.length>0)
          {
            obj.children = obj._children;
            obj._children = undefined;
          }
        }
      }

      angular.forEach(obj.children, function(d){
        $scope.updateTransformTree(d, idList, parentList);
      });
      angular.forEach(obj._children, function(d){
        $scope.updateTransformTree(d, idList, parentList);
      });
    }

  //Code for 'Retransform Data'
  $scope.transformData = [];
  $scope.originalTransformData = [];

  $http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
  $http({
    method: 'POST',
    url: "queryTransform.php",
    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
  }).then (function success(response){
    angular.forEach(response.data, function(d)
    {
      var tmpArray = {name:d.name, id:d.id, branch:d.branch_name, log2:d.log2};
      $scope.transformData.push(tmpArray);
      $scope.originalTransformData.push({name:d.name, id:d.id, branch:d.branch_name, log2:d.log2});
    });
    $scope.transformData =$scope.transformData.filter(function(item){return item.name!==null && item.name!==undefined;});
    $scope.retransformForm.$invalid=true;
  });

  $scope.transformTableAfterChange = function(changes, source)
  {
    if(changes!==null)
    {
      var eq = true;
      angular.forEach($scope.transformData, function(d){
        if (d.id!==undefined)
        {
          var match = $scope.originalTransformData.filter(function(item){return item.id===d.id});
          if (match.length===1)
          {
            match[0].log2===d.log2 ? null : eq = false;
          }
        }
      });
      eq===true ? $scope.retransformForm.$invalid=true : $scope.retransformForm.$invalid=false;
    }
  }

  $scope.updateTransform = function()
  {
      //put code to handle updating log2 transformation here
      $scope.transform_tree=angular.copy($scope.original_tree);
      $scope.transform_tree_data = angular.copy($scope.rawTreeData);
      //update tree so that all set nodes are collapsed but that set nodes where log2 is in play are colored.
      var sets = []; var parentList= []; var idList = [];
      angular.forEach($scope.transformData.filter(function(item){return item.id!==null;}),function(d){
        if (d.log2==="1")
        {
          sets.push(d.id); $scope.getTransformParentIDs($scope.transform_tree_data, d.id, parentList); idList[d.id]="";
        }
      });

      $scope.updateTransformTree($scope.transform_tree[0], idList, parentList);
      $scope.display_tree = $scope.transform_tree[0];
      $scope.editOperation = "Confirm Log2 Transform Updates";
      $scope.editDescription = "All sets where quantitative values will be log2 transformed are displayed below in green. Please confirm that these transformations are correct before proceeding."
      $scope.buttonText = "Confirm Log2 Transform Update";
      $scope.currentEdit="LOG2";
      $scope.showModal();
    }

    $scope.getTransformParentIDs = function(treeData, id, parentList)
    {
      var selection = treeData.filter(function(d){return d.name===id;});
      angular.forEach(selection, function(d){
        parentList[d.parent]="";
        $scope.getTransformParentIDs(treeData, d.parent, parentList);
      });
    }

    $scope.updateTransformTree = function(obj, idList, parentList)
    {
      if (idList[obj.name]!==undefined)
      {
        obj.color="#14D600";
        obj.childColor="#14D600";
        obj.border="#0E8F00";
        obj.bold=true;
        if (obj._children!==undefined)
        {
          if (obj._children.length>0)
          {
            obj.children = obj._children;
            obj._children = undefined;
          }
        }
      }
      else
      {
      /* obj.color="#fff";
        obj.border="steelblue";
        obj.childColor="lightsteelblue";
         obj.bold=false;
         obj.control="0";*/
       }
       if (parentList[obj.name]!==undefined)
       {
         if (obj._children!==undefined)
         {
          if (obj._children.length>0)
          {
            obj.children = obj._children;
            obj._children = undefined;
          }
        }
      }

      angular.forEach(obj.children, function(d){
        $scope.updateTransformTree(d, idList, parentList);
      });
      angular.forEach(obj._children, function(d){
        $scope.updateTransformTree(d, idList, parentList);
      });
    }

  //Code for 'Change Imputation Settings'
  $scope.imputationData = [];
  $scope.originalImputationData = [];

  $http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
  $http({
    method: 'POST',
    url: "queryImputation.php",
    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
  }).then (function success(response){
    angular.forEach(response.data, function(d)
    {
      var tmpArray = {name:d.name, id:d.id, branch:d.branch_name, impute:d.impute};
      $scope.imputationData.push(tmpArray);
      $scope.originalImputationData.push({name:d.name, id:d.id, branch:d.branch_name, impute:d.impute});
    });
    $scope.imputationData =$scope.imputationData.filter(function(item){return item.name!==null && item.name!==undefined;});
    $scope.imputationForm.$invalid=true;
  });

  $scope.imputationTableAfterChange = function(changes, source)
  {
    if(changes!==null)
    {
      var eq = true;
      angular.forEach($scope.imputationData, function(d){
        if (d.id!==undefined)
        {
          var match = $scope.originalImputationData.filter(function(item){return item.id===d.id});
          if (match.length===1)
          {
            match[0].impute===d.impute ? null : eq = false;
          }
        }
      });
      eq===true ? $scope.imputationForm.$invalid=true : $scope.imputationForm.$invalid=false;
    }
  }

  $scope.updateImputation = function()
  {
       //put code to handle updating log2 transformation here
       $scope.impute_tree = angular.copy($scope.original_tree);
       $scope.imputation_tree_data = angular.copy($scope.rawTreeData);
      //update tree so that all set nodes are collapsed but that set nodes where log2 is in play are colored.
      var sets = []; var parentList= []; var idList = [];
      angular.forEach($scope.imputationData.filter(function(item){return item.id!==null;}),function(d){
        if (d.impute==="1")
        {
          sets.push(d.id); $scope.getImputationParentIDs($scope.imputation_tree_data, d.id, parentList); idList[d.id]="";
        }
      });

      $scope.updateImputationTree($scope.impute_tree[0], idList, parentList);
      $scope.display_tree = $scope.impute_tree[0];
      $scope.editOperation = "Confirm Missing Value Imputation Updates";
      $scope.editDescription = "All sets where missing quantitative values will be imputed are displayed below in green. Please confirm that these imputation settings are correct before proceeding."
      $scope.buttonText = "Confirm Missing Value Imputation Update";
      $scope.currentEdit="IMPUTE";
      $scope.showModal();
    }

    $scope.getImputationParentIDs = function(treeData, id, parentList)
    {
      var selection = treeData.filter(function(d){return d.name===id;});
      angular.forEach(selection, function(d){
        parentList[d.parent]="";
        $scope.getImputationParentIDs(treeData, d.parent, parentList);
      });
    }

    $scope.updateImputationTree = function(obj, idList, parentList)
    {
      if (idList[obj.name]!==undefined)
      {
        obj.color="#14D600";
        obj.childColor="#14D600";
        obj.border="#0E8F00";
        obj.bold=true;
        if (obj._children!==undefined)
        {
          if (obj._children.length>0)
          {
            obj.children = obj._children;
            obj._children = undefined;
          }
        }
      }
      else
      {
      /* obj.color="#fff";
        obj.border="steelblue";
        obj.childColor="lightsteelblue";
         obj.bold=false;
         obj.control="0";*/
       }
       if (parentList[obj.name]!==undefined)
       {
         if (obj._children!==undefined)
         {
          if (obj._children.length>0)
          {
            obj.children = obj._children;
            obj._children = undefined;
          }
        }
      }

      angular.forEach(obj.children, function(d){
        $scope.updateImputationTree(d, idList, parentList);
      });
      angular.forEach(obj._children, function(d){
        $scope.updateImputationTree(d, idList, parentList);
      });
    }


  //Code for 'Delete Data'
  $scope.deleteDataType = "";
  $scope.deleteDataSpecific = [];
  $scope.deleteDataSelectedData = null;

  $scope.deleteDataTypeChange = function(){
    $scope.deleteDataSelectedData = null;
    $http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
    $http({
      method: 'POST',
      url: "queryRename.php",
      data: $.param({
        t:$scope.deleteDataType
      }),
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).then (function success(response){
      $scope.deleteDataSpecific = [];
      angular.forEach(response.data, function(d)
      {
        var tmpArray = {name:d.name, id:d.id, group:d.group};
        $scope.deleteDataSpecific.push(tmpArray);
      });
    });
  }

  $scope.doDelete = function()
  {
    //Temp Tree Update Data
    $scope.delete_tree_data = angular.copy($scope.rawTreeData);
    var deleteIDs = []; var parentIDs =[];
    deleteIDs[$scope.deleteDataSelectedData.id]="";
    $scope.getDeleteTreeIDs($scope.delete_tree_data, $scope.deleteDataSelectedData.id, deleteIDs);
    $scope.getDeleteParentIDs($scope.delete_tree_data, $scope.deleteDataSelectedData.id, parentIDs);
    $scope.delete_tree=angular.copy($scope.original_tree);
    $scope.updateDeleteTree($scope.delete_tree[0], deleteIDs, parentIDs);
    $scope.display_tree = $scope.delete_tree[0];
    $scope.editOperation = "Confirm Data Removal";
    $scope.editDescription = "All nodes shown below in green will be deleted. This action is permanent and cannot be undone. Please confirm these changes before proceeding."
    $scope.buttonText = "Confirm Data Removal";
    $scope.currentEdit="DELETE";
    $scope.showModal();
  }


  $scope.updateDeleteTree = function(obj, idList, parentList)
  {
    if (idList[obj.name]!==undefined)
    {
      obj.color="#14D600";
      obj.childColor="#14D600";
      obj.border="#0E8F00";
      obj.bold=true;
      if (obj._children!==undefined)
      {
        if (obj._children.length>0)
        {
          obj.children = obj._children;
          obj._children = undefined;
        }
      }
    }
    if (parentList[obj.name]!==undefined)
    {
     if (obj._children!==undefined)
     {
      if (obj._children.length>0)
      {
        obj.children = obj._children;
        obj._children = undefined;
      }
    }
  }

  angular.forEach(obj.children, function(d){
    $scope.updateDeleteTree(d, idList, parentList);
  });
  angular.forEach(obj._children, function(d){
    $scope.updateDeleteTree(d, idList, parentList);
  });
}

$scope.getDeleteTreeIDs = function(treeData, id, mainList)
{
 var selection = treeData.filter(function(d){return d.parent===id;});
 angular.forEach(selection, function(d){
  mainList[d.name]="";
  $scope.getDeleteTreeIDs(treeData, d.name, mainList);
});
}
$scope.getDeleteParentIDs = function(treeData, id, parentList)
{
  var selection = treeData.filter(function(d){return d.name===id;});
  angular.forEach(selection, function(d){
    parentList[d.parent]="";
    $scope.getDeleteParentIDs(treeData, d.parent, parentList);
  });
}

//Master handle function here, you need to call out to the appropriate functions
$scope.doEdits = function()
{
  angular.element(document.getElementById('loader-overlay')).css({'display':'block'});
  var phpURL = "";
  var dObj={};
  switch($scope.currentEdit)
  {
    case "RENAME":
    phpURL ="renameData.php";
    dObj = {t:$scope.renameDataType, n:$scope.renameName, i:$scope.renameDataSelectedData.id, cn:$scope.renameDataSelectedData.name};
    break;
    case "CONTROL":
    phpURL ="updateControl.php";
    dObj={i:$scope.selectedControlSet.id, c:$scope.controlSetData.filter(function(item){return item.header!==null && item.header!==undefined;}), n:$scope.selectedControlSet.name};
    break;
    case "LOG2":
    phpURL ="updateLog2Transform.php";
    dObj={d:$scope.transformData.filter(function(item){return item.id!==null && item.id!==undefined;})};
    break;
    case "IMPUTE":
    phpURL ="updateImputation.php";
    dObj={d:$scope.imputationData.filter(function(item){return item.id!==null && item.id!==undefined;})};
    break;
    case "DELETE":
    phpURL="deleteData.php"
    dObj = {t:$scope.deleteDataType, i:$scope.deleteDataSelectedData.id, n:$scope.deleteDataSelectedData.name};
    break;
    case "TYPE":
    phpURL="updateSampleType.php"
    dObj = {oi:($scope.organismType.id===undefined?"-1":$scope.organismType.id),
    sic:($scope.standardIdentifierCol.id===undefined?"":$scope.standardIdentifierCol.id),
    sit:($scope.standardIdentifierType.id===undefined?"-1":$scope.standardIdentifierType.id),
    si:$scope.selectedTypeSet.id};
    break;
    case "FILTER":
    phpURL="updateDataFilter.php";
    if($scope.dataFilter.value==='NONE'){ dObj ={t:$scope.dataFilter.value, p1:0,p2:0,si:$scope.selectedFilterSet.id}}
      if($scope.dataFilter.value==='TOTAL'){ dObj ={t:$scope.dataFilter.value, p1:$scope.minTotalReps,p2:0,si:$scope.selectedFilterSet.id}}
        if($scope.dataFilter.value==='COND'){ dObj ={t:$scope.dataFilter.value, p1:$scope.minRepsPerCond,p2:$scope.minConditions,si:$scope.selectedFilterSet.id}}
          break;
        default:
        break;
      }

      $http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
      $http({
        method: 'POST',
        url: phpURL,
        data: $.param(dObj), 
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
      }).then (function success(response){
        $scope.errorMessage = response.data.message;
        response.data.result ? $scope.errorStatus = "Success!" : $scope.errorStatus = "Unexpected error!";
        $scope.updateAllData();
        $scope.updateProcessList();
        angular.element(document.getElementById('errorReportModal')).appendTo('body').modal('show');
      });

    }

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

  $scope.updateAllData = function()
  {

  //File upload settings reset
  $scope.uploadSettings = {};
  $http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
  $http({
    method: 'POST',
    url: "queryAllFileUploadInfo.php",
    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
  }).then (function success(response){
    $scope.uploadSettings = response.data;
  });


  //Project Name and Description Reset
  $http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
  $http({
    method: 'POST',
    url: "queryProjectInfo.php",
    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
  }).then (function success(response){
    $scope.projectName=response.data.name;
    $scope.projectDescription = "All data uploaded to '"+$scope.projectName+"' can be edited, updated, or deleted below.";
    $scope.originalProjectDescription = response.data.description;
    $scope.userProjectDescription = response.data.description;
  });


  //Rename reset
  $scope.renameDataType = "";
  $scope.renameDataSpecific = [];
  $scope.renameName = "";
  $scope.renameDataSelectedData = null;
  $scope.original_tree = {};
  $scope.display_tree = {};
  $scope.editOperation = "";
  $scope.currentEdit="";

//Control select reset
$scope.allSets = [];
$scope.controlSetData = [];
$scope.control_condition = "";
$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
$http({
  method: 'POST',
  url: "queryAllSets.php",
  headers: {'Content-Type': 'application/x-www-form-urlencoded'}
}).then (function success(response){
  angular.forEach(response.data, function(d)
  {
    var tmpArray = {name:d.name, id:d.id, group:d.group};
    $scope.allSets.push(tmpArray);
  });
});

//Sample Type Reset
$scope.organismType = {};
$scope.chosenColumns = [];
$scope.standardIdentifierType = {};
$scope.standardIdentifierCol = {};
$scope.typeDataValid = false;
$scope.typeData = [];
$scope.originalTypeData = [];
$scope.chosenColumns = [];
$scope.typeDataValid = false;

    //Data Filter Reset
    $scope.filterDataValid = false;
    $scope.originalFilterData = {};
    $scope.totalReplicates = 0;
    $scope.calcReplicates = 0;
    $scope.minTotalReps = 0;
    $scope.minRepsPerCond = 0;
    $scope.minConditions = 0;
    $scope.filterDataValid = false;

//Transform reset
$scope.transformData = [];
$scope.originalTransformData = [];

$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
$http({
  method: 'POST',
  url: "queryTransform.php",
  headers: {'Content-Type': 'application/x-www-form-urlencoded'}
}).then (function success(response){
  angular.forEach(response.data, function(d)
  {
    var tmpArray = {name:d.name, id:d.id, branch:d.branch_name, log2:d.log2};
    $scope.transformData.push(tmpArray);
    $scope.originalTransformData.push({name:d.name, id:d.id, branch:d.branch_name, log2:d.log2});
  });
  $scope.transformData =$scope.transformData.filter(function(item){return item.name!==null && item.name!==undefined;});
  $scope.retransformForm.$invalid=true;
});

//Imputation reset
$scope.imputationData = [];
$scope.originalImputationData = [];
$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
$http({
  method: 'POST',
  url: "queryImputation.php",
  headers: {'Content-Type': 'application/x-www-form-urlencoded'}
}).then (function success(response){
  angular.forEach(response.data, function(d)
  {
    var tmpArray = {name:d.name, id:d.id, branch:d.branch_name, impute:d.impute};
    $scope.imputationData.push(tmpArray);
    $scope.originalImputationData.push({name:d.name, id:d.id, branch:d.branch_name, impute:d.impute});
  });
  $scope.imputationData =$scope.imputationData.filter(function(item){return item.name!==null && item.name!==undefined;});
  $scope.imputationForm.$invalid=true;
});

//Delete reset
$scope.deleteDataType = "";
$scope.deleteDataSpecific = [];
$scope.deleteDataSelectedData = null;

//original tree reset
$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
$http({
  method: 'POST',
  url: "queryTreeEdit.php",
  data: $.param({
    t:$scope.renameDataType
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
  node.color="#fff";
  node.border="steelblue";
  node.childColor="lightsteelblue";
  node.bold=false;
  if (node.control==="1")
  {
    node.childColor="#FF9292";
    node.border="#FF2525";
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
                          parent._children.push(node);
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
$scope.original_tree = treeData;
});


}

  //Code to get existing data tree here
  $http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
  $http({
    method: 'POST',
    url: "queryTreeEdit.php",
    data: $.param({
      t:$scope.renameDataType
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
   $scope.rawTreeData = response.data;
   var treeData = [];
   var repCount = 0;

   angular.forEach(response.data, function(node){
    node.color="#fff";
    node.border="steelblue";
    node.childColor="lightsteelblue";
    node.bold=false;
    if (node.control==="1")
    {
      node.childColor="#FF9292";
      node.border="#FF2525";
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
                          parent._children.push(node);
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
$scope.original_tree = treeData;
});


  //Table render
  $scope.render = function()
  {
    hotRegisterer.getInstance('controlTable').render();
    hotRegisterer.getInstance('transformTable').render();
    hotRegisterer.getInstance('imputationTable').render();
  }

  $scope.showModal = function()
  {
    angular.element(document.getElementById('checkDataModal')).modal('show');
    $rootScope.$broadcast('modalShow',{});
  }

});

editDataApp.directive('rename', function($http){
  var toId;
  return {
    restrict: 'A',
    require: 'ngModel',
    link: function (scope, elm, attr, ctrl)
    {

      scope.$watch(attr.ngModel, function(value) {
        if(toId) clearTimeout(toId);

        toId = setTimeout(function(){
          if (scope.renameName==="" || scope.renameName===undefined)
          {
            ctrl.$setValidity('length', false);
            scope.$digest();
          }
          else
          {
            ctrl.$setValidity('length', true);
            $http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
            $http({
              method: 'POST',
              url: "queryRenameName.php",
              data: $.param({
                t:scope.renameDataType,
                n:scope.renameName,
                i:scope.renameDataSelectedData.id
              }),
              headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            }).then (function success(response){
              if(response.data=="true")
              {
                ctrl.$setValidity('uniqueName', false);
              }
              else
              {
                ctrl.$setValidity('uniqueName', true);
              }
            });
          }
        });
});
}
}
});

editDataApp.filter('myDateFormat', function myDateFormat($filter){
  return function(text){
    var  tempdate= new Date(text.replace(/-/g,"/"));
    return $filter('date')(tempdate, "medium");
  }
});

editDataApp.filter('finished', function (){
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

editDataApp.filter('running', function (){
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

editDataApp.directive('scrollOnClick', function() {
  return {
    restrict: 'A',
    link: function(scope, $elm, attrs) {
      var idToScroll = attrs.href;
      $elm.on('click', function() {
        var $target;
        if (idToScroll) {
          $target = $(idToScroll);
        } else {
          $target = $elm;
        }
        $("body").animate({scrollTop: $target.offset().top}, "slow");
      });
    }
  }
});

editDataApp.directive('projectHierarchyTree', [function(){
  var margin_tree = null;
  var width_tree = null;
  var height_tree = null;
  var duration_tree = 1500;
  var root_tree= null;
  var tree = null;
  var diagonal_tree = null;
  var tree_chart = null;
  var tree_i=0;
      //margin stuff here
      //angular.element(window)[0].innerWidth
      var full_page_width = Math.min($('#body')[0].clientWidth * 0.93, 2000);
      margin_tree = {top: (full_page_width * .02), right:(full_page_width * .02), 
        bottom: (full_page_width * .01), left: (full_page_width * .02)},
        width_tree = (full_page_width * .82) - margin_tree.left - margin_tree.right,
        height_tree = (full_page_width * .45) - margin_tree.top - margin_tree.bottom;

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
            var branch_label = tree_chart.append("text").text("Branch").attr("y",100).attr("x",100).attr("opacity", "0").attr("font-size","1em").attr("id", "branchLabelTree").attr("font-style", "italic");
            var set_label = tree_chart.append("text").text("Set").attr("y",100).attr("x",100).attr("opacity", "0").attr("font-size","1em").attr("id", "setLabelTree").attr("font-style", "italic");
            var condition_label = tree_chart.append("text").text("Conditions").attr("y",100).attr("x",100).attr("opacity", "0").attr("font-size","1em").attr("id", "conditionLabelTree").attr("font-style", "italic");
            var replicate_label = tree_chart.append("text").text("Replicates").attr("y",100).attr("x",100).attr("opacity", "0").attr("font-size","1em").attr("id", "replicateLabelTree").attr("font-style", "italic");


          // watch for data changes and re-render
          scope.$watch('data', function(newVals, oldVals) {
           //scope.update(scope.data);
         });

          scope.$on('modalShow', function(event, data){
            scope.update(scope.$parent.display_tree);
          });

          var repCount = 0;

          scope.update= function(data)
          {
            if (data != null)
            {
              treeDisplayed = true;
              repCount = 0;
              condCount = 0;
              scope.recurse(scope.$parent.display_tree);
              var tmpHeight = Math.max((repCount*22),500);
              tmpHeight = Math.max(tmpHeight, (condCount* 20));
              var full_page_width = Math.min($('#body')[0].clientWidth * 0.93, 2000);
              width_tree = Math.max((full_page_width * 0.82),400) - margin_tree.left - margin_tree.right;
              tree_height = tmpHeight - margin_tree.top - margin_tree.bottom;

              $('#hierarchyTreePane').attr("height", tmpHeight + margin_tree.top + margin_tree.bottom);
              $('#hierarchyTreePane').attr("width", "100%");

              //width_tree = ($('#hierarchyTreePane').width()) ;

              tree = d3.layout.tree()
              .size([tmpHeight, width_tree]);

              diagonal_tree = d3.svg.diagonal()
              .projection(function(d) { return [d.y, d.x]; });

              root_tree = scope.$parent.display_tree;

              var nodes = tree.nodes(root_tree).reverse(),
              links = tree.links(nodes);

            // Normalize for fixed-depth.
            var maxDepth = d3.max(nodes, function(d){ return d.depth;});

            if (maxDepth == 4)
            {
              nodes.forEach(function(d) { d.y = ((d.depth) *(width_tree*.94)/(Math.max(maxDepth,1))) + margin_tree.left + 110; d.x0===undefined?d.x0=0:null; d.y0===undefined?d.y0=0:null;});
            }
            else
            {
              nodes.forEach(function(d) { d.y = ((d.depth) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110; d.x0===undefined?d.x0=0:null; d.y0===undefined?d.y0=0:null;});
            }

            switch(maxDepth)
            {
              case 0:
              project_label.transition().duration(1500).attr("opacity", 1).attr("y", tree_height + 65).attr("x",((0) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
              branch_label.transition().duration(1500).attr("opacity", 0).attr("y", tree_height + 65).attr("x",((1) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
              set_label.transition().duration(1500).attr("opacity", 0).attr("y", tree_height + 65).attr("x",((2) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
              condition_label.transition().duration(1500).attr("opacity", 0).attr("y", tree_height + 65).attr("x",((3) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
              replicate_label.transition().duration(1500).attr("opacity", 0).attr("y", tree_height + 65).attr("x",((4) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
              break;
              case 1:
              project_label.transition().duration(1500).attr("opacity", 1).attr("y", tree_height + 65).attr("x",((0) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1500);
              branch_label.transition().duration(1500).attr("opacity", 1).attr("y", tree_height + 65).attr("x",((1) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1500);
              set_label.transition().duration(1500).attr("opacity", 0).attr("y", tree_height + 65).attr("x",((2) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1500);
              condition_label.transition().duration(1500).attr("opacity", 0).attr("y", tree_height + 65).attr("x",((3) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1500);
              replicate_label.transition().duration(1500).attr("opacity", 0).attr("y", tree_height + 65).attr("x",((4) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1500);
              break;
              case 2:
              project_label.transition().duration(1500).attr("opacity", 1).attr("y", tree_height + 65).attr("x",((0) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1500);
              branch_label.transition().duration(1500).attr("opacity", 1).attr("y", tree_height + 65).attr("x",((1) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1500);
              set_label.transition().duration(1500).attr("opacity", 1).attr("y", tree_height + 65).attr("x",((2) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1500);
              condition_label.transition().duration(1500).attr("opacity", 0).attr("y", tree_height + 65).attr("x",((3) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1500);
              replicate_label.transition().duration(1500).attr("opacity", 0).attr("y", tree_height + 65).attr("x",((4) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1500);
              break;
              case 3:
              project_label.transition().duration(1500).attr("opacity", 1).attr("y", tree_height + 65).attr("x",((0) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1500);
              branch_label.transition().duration(1500).attr("opacity", 1).attr("y", tree_height + 65).attr("x",((1) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1500);
              set_label.transition().duration(1500).attr("opacity", 1).attr("y", tree_height + 65).attr("x",((2) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1500);
              condition_label.transition().duration(1500).attr("opacity", 1).attr("y", tree_height + 65).attr("x",((3) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 105).attr("text-anchor","middle").transition().duration(1500);
              replicate_label.transition().duration(1500).attr("opacity", 0).attr("y", tree_height + 65).attr("x",((4) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1500);
              break;
              case 4:
              project_label.transition().duration(1500).attr("opacity", 1).attr("y", tree_height + 65).attr("x",((0) *(width_tree*.94)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1500);
              branch_label.transition().duration(1500).attr("opacity", 1).attr("y", tree_height + 65).attr("x",((1) *(width_tree*.94)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1500);
              set_label.transition().duration(1500).attr("opacity", 1).attr("y", tree_height + 65).attr("x",((2) *(width_tree*.94)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1500);
              condition_label.transition().duration(1500).attr("opacity", 1).attr("y", tree_height + 65).attr("x",((3) *(width_tree*.94)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1500);
              replicate_label.transition().duration(1500).attr("opacity", 1).attr("y", tree_height + 65).attr("x",((4) *(width_tree*.94)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1500);
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
    .style("fill", function(d) { return d._children ? d.childColor : d.color; })
    .style("stroke", function(d){ return d.border; });

    nodeEnter.append("text")
    .attr("x", function(d) { return d.children || d._children ? -13 : 13; })
    .attr("dy", ".2em")
    .attr("class","nodeText")
    .attr("text-anchor", function(d) { return d.children || d._children ? "end" : "start"; })
    .text(function(d) { return (d.value); })
    .style("fill-opacity", 1e-6)
    .style("font-style", function(d){
      return d.bold ? "italic" : "normal";
    }).style("font-weight", function(d){
      return d.bold ? "bold" : "normal";
    });

    // Transition nodes to their new position.
    var nodeUpdate = node.transition()
    .duration(duration_tree)
    .attr("transform", function(d) { return "translate(" + d.y + "," + d.x + ")"; });

    nodeUpdate.select("circle")
    .attr("r", function (d){ return 10 * (width_tree/1600)})
    .style("fill", function(d) { return d._children ? d.childColor : d.color; })
    .style("stroke", function(d){ return d.border; });

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
