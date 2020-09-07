var uploadDataApp = angular.module('uploadDataApp', ['ngHandsontable', 'angularFileUpload']);

uploadDataApp.controller('uploadCtrl', function($scope, $http, $filter, FileUploader, hotRegisterer, $rootScope, $location, $window, $interval){

$scope.projectID=-1;
	$scope.projectName="";
	$scope.projectDescription="Upload peak tables in .txt format from '' below.";
	$scope.totalMeas = 0;
	$scope.uniqueMol = 0;
	$scope.filesUploaded =0;
	$scope.invitedCollabs = 0;
	$scope.minTotalReps = 50;
	$scope.minRepsPerCond = 50;
	$scope.minConditions = 0;
	$scope.totalConditions=0;
	$scope.totalReplicates = 0;
	$scope.calcReplicates = 0;
	$scope.onlyNumbers = /^\d+$/;
	$scope.header = "";
	$scope.delimiters = [{delimiter:"Tab ('\\t')", value:"TAB"}];
	$scope.delimiter = $scope.delimiters[0];
	$scope.dataFilters = [{filter:"No Filter", value:"NONE"}, {filter:"Filter by Total Measurements", value:"TOTAL"}, {filter:"Filter by Measurements per Condition", value:"COND"}];
	$scope.dataFilter = $scope.dataFilters[0];
	$scope.significanceTests = [{test:"Student's t-test", value:"STUDENT"}, {test:"Welch", value:"WELCH"}];
	$scope.significanceTest = $scope.significanceTests[0];
	$scope.tableItems = [];
	$scope.masterItemList = [];
	$scope.headerTableSelection=[];
	$scope.identifierTableSelection=[];
	$scope.featureTableSelection=[];
	$scope.quantTableSelection=[];
	$scope.identifierSelected = false;
	$scope.tableItems.features =[];
	$scope.tableItems.quant = [];
	$scope.log2Transform = true;
	$scope.impute = true;
	$scope.root_tree = {};
	$scope.redundantHeaders=false;
	$scope.confirmModalVisible = false;
	$scope.uploadFileName="";
	$scope.organisms = {};
	$scope.standardIdentifiers = {};
	$scope.chosenColumns = [];
	$scope.organismCheck = false;
	$scope.sampleTypeComplete=false;

	$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
	$http({
		method: 'POST',
		url: "queryProjectInfo.php",
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	}).then (function success(response){
		$scope.projectName=response.data.name;
		$scope.projectDescription = "Upload peak tables in .txt format from '"+$scope.projectName+"' below.";
	});

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

$scope.updateFileUploadSettings = function()
{
	$scope.uploadSettings = {};
	$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
	$http({
		method: 'POST',
		url: "queryAllFileUploadInfo.php",
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	}).then (function success(response){
		$scope.uploadSettings = response.data;
	});
}

$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
$http({
	method: 'POST',
	url: "queryBranchesFromProject.php",
	headers: {'Content-Type': 'application/x-www-form-urlencoded'}
}).then (function success(response){
	$scope.branches = [];
	angular.forEach(response.data, function(d)
	{
		var tmpArray = {branch_id:d.branch_id, branch_name:d.branch_name};
		$scope.branches.push(tmpArray);
	});
	$scope.selectedBranch = $scope.branches[0];
});

$scope.addBranch = function()
{
	$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
	$http({
		method: 'POST',
		url: "addNewBranch.php",
		data: $.param({
			bn:$scope.newBranchName
		}),
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	}).then (function success(response){
		$scope.newBranchName="";
		$http({
			method: 'POST',
			url: "queryBranchesFromProject.php",
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
		}).then (function success(response){
			$scope.branches = [];
			angular.forEach(response.data, function(d)
			{
				var tmpArray = {branch_id:d.branch_id, branch_name:d.branch_name};
				$scope.branches.push(tmpArray);
			});
			$scope.selectedBranch = $scope.branches[$scope.branches.length-1];
		});
	});
}



$scope.$watch('delimiter', function(){
	$scope.tableItems = [];
	$scope.tableItems.headers=[];
	$scope.masterItemList = [];
	$scope.tableItems.identifiers =[];
	$scope.tableItems.features =[];
	$scope.tableItems.quant =[];
	$scope.headerTableSelection = [];
	$scope.identifierTableSelection = [];
	$scope.featureTableSelection = [];
	$scope.quantTableSelection = [];
	$scope.identifierSelected = false;
	var array = [];
	var array = [];
	if ($scope.delimiter.value=="COMMA")
	{
		array = $scope.header.split(',');
	}
	if ($scope.delimiter.value=="TAB")
	{
		array = $scope.header.split('\t');
	}
	if ($scope.delimiter.value=="WHITESPACE")
	{
		array = $scope.header.split(' ');
	}
	if ($scope.delimiter.value=="SEMICOLON")
	{
		array = $scope.header.split(';');
	}
	var indexCount = 0;
	angular.forEach(array, function(d){
		var tmpArray = {header:d,index:indexCount};
		$scope.masterItemList[d]=indexCount;
		indexCount++;
		$scope.tableItems.headers.push(tmpArray);
	});
	$scope.checkRedundantHeaders(array);
	$scope.redundantHeaders ? alert("There are currently redundant headers in your file! Please select a new delimiter or reload the file to continue.") : null;
});

$scope.$watch('header', function(){
	$scope.tableItems = [];
	$scope.tableItems.headers=[];
	$scope.masterItemList = [];
	$scope.tableItems.identifiers =[];
	$scope.tableItems.features =[];
	$scope.tableItems.quant =[];
	$scope.headerTableSelection = [];
	$scope.identifierTableSelection = [];
	$scope.featureTableSelection = [];
	$scope.quantTableSelection = [];
	$scope.identifierSelected = false;
	var array = [];
	if ($scope.delimiter.value=="COMMA")
	{
		array = $scope.header.split(',');
	}
	if ($scope.delimiter.value=="TAB")
	{
		array = $scope.header.split('\t');
	}
	if ($scope.delimiter.value=="WHITESPACE")
	{
		array = $scope.header.split(' ');
	}
	if ($scope.delimiter.value=="SEMICOLON")
	{
		array = $scope.header.split(';');
	}
	var indexCount = 0;
	angular.forEach(array, function(d){
		var tmpArray = {header:d,index:indexCount};
		$scope.masterItemList[d]=indexCount;
		indexCount++;
		$scope.tableItems.headers.push(tmpArray);
	});
	$scope.checkRedundantHeaders(array);
	$scope.redundantHeaders ? alert("There are currently redundant headers in your file! Please select a new delimiter or reload the file to continue.") : null;
});

$scope.$watchCollection(function() { 
	return angular.toJson([$scope.tableItems.quant]); 
}, function(){
	if ($scope.tableItems.quant !==undefined)
	{
		$scope.totalConditions = 0;
		$scope.totalReplicates = 0;
		if($scope.tableItems.quant.length > 0)
		{
			var reps = $scope.tableItems.quant.filter(function(i) {
				if (i.header === undefined) {
			    return false; // skip
			}
			else
			{
				if(i.header===null)
				{
					return false;
				}
			}
			return true;
		});
			$scope.createTempTree(reps);
			$scope.totalReplicates = reps.length;
			$scope.calcReplicates = Math.round($scope.totalReplicates * ($scope.minTotalReps/100));
			var set = new Set(reps.map(function(a){return a.condName;}));
			$scope.totalConditions = set.size;
		}

	}
	else
	{
		$scope.totalConditions = 0;
	}
});

//Code here to update standard identifer column options
$scope.$watchCollection(function() { 
	return angular.toJson([$scope.tableItems.features]); 
}, function(){
	$scope.updateStandardIDColumnOptions();
});

$scope.$watchCollection(function() { 
	return angular.toJson([$scope.tableItems.identifiers]); 
}, function(){
	$scope.updateStandardIDColumnOptions();
});


$scope.checkStandardIDInputs = function()
{
	if ($scope.organism===null || $scope.organism===undefined || $scope.standardColumn===null || $scope.standardColumn===undefined || $scope.standardIdentifier===null || $scope.standardIdentifier===undefined)
	{
		$scope.sampleTypeComplete = false;
	}
	else
	{
		$scope.sampleTypeComplete = true;
	}
}

$scope.updateStandardIDColumnOptions = function()
{
	$scope.chosenColumns = [];
	if ($scope.tableItems.identifiers!==undefined)
	{
		angular.forEach($scope.tableItems.identifiers, function(d)
		{
			if(d.header !== undefined && d.header!==null)
			{
				var tmpArray = {group:"Unique Identifier Column", name:"Column Header: " + d.header + " | Identifier Name: " + d.userName, id:d.header};
				$scope.chosenColumns.push(tmpArray);
			}
		});
	}
	if ($scope.tableItems.features!==undefined)
	{
		angular.forEach($scope.tableItems.features, function(d)
		{
			if(d.header !== undefined && d.header!==null)
			{
				var tmpArray = {group:"Feature Descriptor Columns", name:"Column Header: " + d.header + " | Descriptor Name: " + d.userName, id:d.header};
				$scope.chosenColumns.push(tmpArray);
			}
		});
	}
}

$scope.$watch('standardIdentifier', function(){
	$scope.checkOrganismInputs();
});
$scope.$watch('standardColumn', function(){
	$scope.checkOrganismInputs();
});
$scope.$watch('organism', function(){
	$scope.checkOrganismInputs();
});

$scope.checkOrganismInputs = function()
{
	$scope.organismCheck = ($scope.standardIdentifier!==undefined && $scope.standardIdentifier!==null &&
		$scope.standardColumn!==undefined && $scope.standardColumn!==null &&
		$scope.organism!==undefined && $scope.organism!==null);
}

$scope.clearSampleType =function()
{
	$scope.standardIdentifier=undefined; $scope.standardColumn=undefined; $scope.organism=undefined;
	$scope.checkStandardIDInputs();
}

$scope.$watch('selectedBranch', function()
{
	$scope.updateTree();
});
$scope.$watch('setName', function()
{
	$scope.updateTree();
});

$scope.updateTree = function()
{
	var reps = $scope.tableItems.quant.filter(function(i) {
		if (i.header === undefined) {
			    return false; // skip
			}
			else
			{
				if(i.header===null)
				{
					return false;
				}
			}
			return true;
		});
	$scope.createTempTree(reps);
}

$scope.createTempTree = function(reps)
{
	if ($scope.selectedBranch !==undefined)
	{
		$scope.tempTreeString = "";
		$scope.tempTreeString = '[{\"name\":\"' + $scope.projectID + '\",\"parent\":\"null\",\"value\":\"' + $scope.projectName + '\",\"control\":\"No\"},{\"name\":\"' + $scope.selectedBranch.branch_id + '\",\"parent\":\"'+$scope.projectID + '\",\"value\":\"' + $scope.selectedBranch.branch_name + '\",\"control\":\"No\"},';

		$scope.tempTreeString += '{\"name\":\"' + "Temp-S" + '\",\"parent\":\"' +  $scope.selectedBranch.branch_id + '\",\"value\":\"'+$scope.setName+'\",\"control\":\"No\"},';
		var repCount = 1;
		var condCount = 1;
		var condSet = new Set(reps.map(function(a){return a.condName;}));
		if (condSet.size > 0)
		{
			angular.forEach(condSet, function(cond){
				var currReps = reps.filter(function(x){ return x.condName ===cond;});
		//add current condition
		$scope.tempTreeString += '{\"name\":\"' + condCount + '-C\",\"parent\":\"' + "Temp-S" + '\",\"value\":\"' + cond + '\",\"control\":\"' + currReps[0].control + '\"},';
		
		angular.forEach(currReps, function(rep){
			//addcurrent rep and close
			$scope.tempTreeString += '{\"name\":\"' + repCount + '-R\",\"parent\":\"' + condCount + '-C\",\"value\":\"' + rep.repName + '\",\"control\":\"' + rep.control + '\"},';
			repCount++;
		});
		condCount++;
	});
		}
		$scope.tempTreeString = $scope.tempTreeString.substring(0, $scope.tempTreeString.length-1);
		$scope.tempTreeString += "]";
		var parseData = jQuery.parseJSON($scope.tempTreeString);
		var dataMap = parseData.reduce(function(map, node) {
			map[node.name] = node;
			return map;
		}, {});
		var dataMap = parseData.reduce(function(map, node) {
			map[node.name] = node;
			return map;
		}, {});

            // create the tree array
            var treeData = [];
            var repCount = 0;
            parseData.forEach(function(node) {
                // add to parent
                var parent = dataMap[node.parent];
                if (parent) {
                    // create child array if it doesn't exist
                    (parent.children || (parent.children = []))
                        // add node to child array
                        .push(node);
                    } else {
                    // parent is null or missing
                    treeData.push(node);
                }
                if (node.name.charAt(node.name.length-1)=='R')
                {
                	repCount++;
                };
            });
            $scope.root_tree = treeData[0];
        }
    }

    $scope.$watch('minTotalReps', function(){
    	$scope.calcReplicates = Math.round($scope.totalReplicates * ($scope.minTotalReps/100));
    });

    $scope.showModal = function()
    {
    	angular.element(document.getElementById('checkDataModal')).modal('show');
    	$rootScope.$broadcast('modalShow',{});
    }

    $scope.formValid = function()
    {
    	if($scope.tableItems.identifiers.filter(function(x){ return x.header !==null && x.header!==undefined;}).length==1
    		&&($scope.tableItems.quant.filter(function(x){ return x.header !==null && x.header!==undefined;}).length>0))
    	{
    		return true;
    	}
    	return false;
    }

    $scope.checkRedundantHeaders = function(array)
    {
    	if (array.length===0)
    	{
    		$scope.redundantHeaders=false; return;
    	}
    	if (array.length===1)
    	{
    		if (array[0]==="")
    		{
    			$scope.redundantHeaders=false; return;
    		}
    	}
    	var set = new Set(array);
    	set.size!==array.length ? $scope.redundantHeaders=true : $scope.redundantHeaders=false;
    }

    $scope.clear = function()
    {
    	$scope.header = "";
    	$scope.uploadFileName="";
    }

    $scope.clear2 = function()
    {
    	$scope.setName = "";
    }

    $scope.render = function()
    {
    	hotRegisterer.getInstance('table1').render();
    	hotRegisterer.getInstance('table2').render();
    	hotRegisterer.getInstance('table3').render();
    	hotRegisterer.getInstance('table4').render();
    }

    $scope.headerTableChange = function(row, col, row2, col2)
    {
    	var tmpData = this.getData(row, col, row2, col2);
    	$scope.headerTableSelection = [];
    	angular.forEach(tmpData, function(d)
    	{
    		if (d[0]!=null)
    		{
    			$scope.headerTableSelection.push(d);
    		}
    	});
    	$scope.$apply();
    }
    $scope.headerTableDeselect = function()
    {
    	$scope.headerTableSelection = [];
    	$scope.$apply();
    }

    $scope.identifierTableChange = function(row, col, row2, col2)
    {
    	var tmpData = this.getData(row, 0, row2, 0);
    	$scope.identifierTableSelection = [];
    	angular.forEach(tmpData, function(d)
    	{
    		if (d[0]!=null)
    		{
    			$scope.identifierTableSelection.push(d);
    		}
    	});
    	$scope.$apply();
    }

    $scope.identiferTableChangeDeselect = function()
    {
    	$scope.identifierTableSelection = [];
    	$scope.$apply();
    }

    $scope.featureTableChange = function(row, col, row2, col2)
    {
    	var tmpData = this.getData(row, 0, row2, 0);
    	$scope.featureTableSelection = [];
    	angular.forEach(tmpData, function(d)
    	{
    		if (d[0]!=null)
    		{
    			$scope.featureTableSelection.push(d);
    		}
    	});
    	$scope.$apply();
    }

    $scope.featureTableChangeDeselect = function()
    {
    	$scope.featureTableSelection = [];
    	$scope.$apply();
    }

    $scope.quantTableChange = function(row, col, row2, col2)
    {
    	var tmpData = this.getData(row, 0, row2, 0);
    	$scope.quantTableSelection = [];
    	angular.forEach(tmpData, function(d)
    	{
    		if (d[0]!=null)
    		{
    			$scope.quantTableSelection.push(d);
    		}
    	});

    	$scope.$apply();
    }

    $scope.quantTableAfterChange = function(changes, source)
    {
    	if(changes!==null)
    	{
    		if(changes[0][1]==="control")
    		{
    			var row = changes[0][0];
    			var condName = $scope.tableItems.quant[row].condName;
    			var state = changes[0][3];
    			var oldState = changes[0][2];
    			angular.forEach($scope.tableItems.quant.filter(function(item){return item.control!==null && item.control!==undefined;}), function(cond){
    				cond.condName===condName ? cond.control=state : cond.control="No";
    			});
    		}
    		if (changes[0][1]==="condName")
    		{
    			var condName = changes[0][3];
    			var setTrue = false;
    			angular.forEach($scope.tableItems.quant.filter(function(item){return item.condName===condName;}), function(cond){
    				cond.control==="Yes" ? setTrue=true : null ;
    			});
    			setTrue ? angular.forEach($scope.tableItems.quant.filter(function(item){return item.control!==null && item.control!==undefined;}), function(cond){
    				cond.condName===condName ? cond.control="Yes" : cond.control="No";
    			}) : null;
    		}
    	}
    }

    $scope.quantTableChangeDeselect = function()
    {
    	$scope.quantTableSelection = [];
    	$scope.$apply();
    }

    $scope.identifierOnClick = function()
    {
    	$scope.tableItems.identifiers = [];
    	angular.forEach($scope.headerTableSelection, function(d){
    		$scope.tableItems.identifiers.push({header:d[0], userName:d[0]});
    		$scope.tableItems.headers = $filter('filter')($scope.tableItems.headers, function(item){return item.header!=d[0]});
    		$scope.identifierSelected = true;
    	});
    	$scope.headerTableSelection = [];
    	$scope.identifierTableSelection = [];
    	hotRegisterer.getInstance('table1').deselectCell();
    	hotRegisterer.getInstance('table2').deselectCell();
    }

    $scope.identifierOffClick = function()
    {
    	var index = $scope.masterItemList[$scope.identifierTableSelection[0][0]];
    	var value = $scope.identifierTableSelection[0][0];
    	$scope.tableItems.identifiers = [];
    	$scope.identifierSelected = false;
    	if (index > $scope.tableItems.headers.length-34)
    	{
    		indexCount = $scope.tableItems.headers.length-34;
    	}
    	$scope.tableItems.headers.splice(index, 0, {header:value,index:index});
    	$scope.tableItems.headers.sort(function(a,b){
    		if (a.index==null)
    		{
    			a.index=10000;
    		}
    		if(b.index==null)
    		{
    			b.index=10000;
    		}
    		return a.index - b.index;
    	});
    	$scope.identifierTableSelection = [];
    	$scope.headerTableSelection = [];
    	hotRegisterer.getInstance('table1').deselectCell();
    	hotRegisterer.getInstance('table2').deselectCell();
    }

    $scope.featureOnClick = function()
    {
    	$scope.tableItems.features = $filter('filter')($scope.tableItems.features, function(item){return item.header!=null});
    	angular.forEach($scope.headerTableSelection, function(d){
    		$scope.tableItems.features.push({header:d[0], userName:d[0]});
    		$scope.tableItems.headers = $filter('filter')($scope.tableItems.headers, function(item){return item.header!=d[0]});
    	});
    	$scope.headerTableSelection = [];
    	$scope.featureTableSelection = [];
    	hotRegisterer.getInstance('table1').deselectCell();
    	hotRegisterer.getInstance('table3').deselectCell();
    }

    $scope.featureOffClick = function()
    {
    	var holdArray = [];
    	angular.forEach($scope.featureTableSelection, function(d){
    		var value = d[0];
    		var index = $scope.masterItemList[value];
    		holdArray.push({header:value,index:index});
    	});

    	angular.forEach(holdArray, function(d){
    		if (d.index > $scope.tableItems.headers.length-34)
    		{
    			d.index = $scope.tableItems.headers.length-34;
    		}
    		$scope.tableItems.headers.splice(d.index, 0, {header:d.header,index:d.index});
    		$scope.tableItems.features = $filter('filter')($scope.tableItems.features, function(item){return item.header!=d.header});
    	});
    	$scope.tableItems.headers.sort(function(a,b){
    		if (a.index==null)
    		{
    			a.index=10000;
    		}
    		if(b.index==null)
    		{
    			b.index=10000;
    		}
    		return a.index - b.index;
    	});
    	$scope.headerTableSelection = [];
    	$scope.featureTableSelection = [];
    	hotRegisterer.getInstance('table1').deselectCell();
    	hotRegisterer.getInstance('table3').deselectCell();
    }

    $scope.quantOnClick = function()
    {
    	$scope.tableItems.quant = $filter('filter')($scope.tableItems.quant, function(item){return item.header!=null});
    	angular.forEach($scope.headerTableSelection, function(d){
    		$scope.tableItems.quant.push({header:d[0], condName:d[0], repName:d[0], control:"No"});
    		$scope.tableItems.headers = $filter('filter')($scope.tableItems.headers, function(item){return item.header!=d[0]});
    	});
    	$scope.headerTableSelection = [];
    	$scope.quantTableSelection = [];
    	hotRegisterer.getInstance('table1').deselectCell();
    	hotRegisterer.getInstance('table4').deselectCell();
    }

    $scope.quantOffClick = function()
    {
    	var holdArray = [];
    	angular.forEach($scope.quantTableSelection, function(d){
    		var value = d[0];
    		var index = $scope.masterItemList[value];
    		holdArray.push({header:value,index:index});
    	});

    	angular.forEach(holdArray, function(d){
    		$scope.tableItems.headers.splice(d.index, 0, {header:d.header,index:d.index});
    		$scope.tableItems.quant = $filter('filter')($scope.tableItems.quant, function(item){return item.header!=d.header});
    	});
    	$scope.tableItems.headers.sort(function(a,b){
    		if (a.index==null)
    		{
    			a.index=10000;
    		}
    		if(b.index==null)
    		{
    			b.index=10000;
    		}
    		return a.index - b.index;
    	});
    	$scope.headerTableSelection = [];
    	$scope.quantTableSelection = [];
    	hotRegisterer.getInstance('table1').deselectCell();
    	hotRegisterer.getInstance('table4').deselectCell();
    }

    var uploader = $scope.uploader = new FileUploader({
    	url: 'readFile.php', 
    	queueLimit:1
    });

    $scope.identifierFilter = function (item) {
    	return (item.header !== undefined && item.header !== null);
    };

    uploader.filters.push({
    	name: 'customFilter',
    	fn: function(item /*{text|plain}*/, options) {
    		var type = '|' + item.type.slice(item.type.lastIndexOf('/') + 1) + '|';
    		return '|txt|csv|tsv|plain|'.indexOf(type) !== -1;
    	}
    });

        // CALLBACKS

        uploader.onWhenAddingFileFailed = function(item /*{File|FileLikeObject}*/, filter, options) {
           // console.info('onWhenAddingFileFailed', item, filter, options);
       };
       uploader.onAfterAddingFile = function(fileItem) {
          //  console.info('onAfterAddingFile', fileItem);
      };
      uploader.onAfterAddingAll = function(addedFileItems) {
          //  console.info('onAfterAddingAll', addedFileItems);
      };
      uploader.onBeforeUploadItem = function(item) {
           // console.info('onBeforeUploadItem', item);
       };
       uploader.onProgressItem = function(fileItem, progress) {
          //  console.info('onProgressItem', fileItem, progress);
      };
      uploader.onProgressAll = function(progress) {
          //  console.info('onProgressAll', progress);
      };
      uploader.onSuccessItem = function(fileItem, response, status, headers) {
      	$scope.uploadFileName = fileItem.file.name;
      	if (status==200)
      	{
      		$scope.header = response;
      	}

      };
      uploader.onErrorItem = function(fileItem, response, status, headers) {
          //  console.info('onErrorItem', fileItem, response, status, headers);
      };
      uploader.onCancelItem = function(fileItem, response, status, headers) {
          //  console.info('onCancelItem', fileItem, response, status, headers);

      };
      uploader.onCompleteItem = function(fileItem, response, status, headers) {
          //  console.info('onCompleteItem', fileItem, response, status, headers);
      };
      uploader.onCompleteAll = function() {
        //    console.info('onCompleteAll');
    };

    $scope.saveData = function()
    {
    	angular.element(document.getElementById('loader-overlay')).css({'display':'block'});
    	var id = JSON.stringify($scope.tableItems.identifiers.filter(function(item){return item.header !==undefined && item.header !==null}));
    	var fd = JSON.stringify($scope.tableItems.features.filter(function(item){return item.header!==undefined && item.header !==null}));
    	var q = JSON.stringify($scope.tableItems.quant.filter(function(item){return item.header!==undefined && item.header !==null}));
    	var log2 = $scope.log2Transform ? 1:0;
    	var imp = $scope.impute?1:0;
    	var filter = "";
    	switch($scope.dataFilter.value)
    	{
    		case "NONE": filter = JSON.stringify({"type":"NONE", "p1":"0", "p2":"0"}); break;
    		case "TOTAL" : filter = JSON.stringify({"type":"TOTAL", "p1":  $scope.minTotalReps  , "p2":"0"}); break;
    		case "COND": filter = JSON.stringify({"type":"COND", "p1":  $scope.minRepsPerCond , "p2":  $scope.minConditions }); break;
    	}
    	
    	var test = $scope.significanceTest.value;
    	var bi = $scope.selectedBranch.branch_id;
    	var sn = $scope.setName;
    	var delim = $scope.delimiter.value;
    	var o = $scope.organism!==undefined && $scope.organism!==null ? $scope.organism.id : -1;
    	var sc = $scope.standardColumn!==undefined && $scope.standardColumn!==null ? $scope.standardColumn.id : "";
    	var st = $scope.standardIdentifier!==undefined && $scope.standardIdentifier!==null ? $scope.standardIdentifier.id : -1;
    	$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
    	$http({
    		method: 'POST',
    		url: "validateData.php",
    		data: $.param({
    			id: id, fd: fd, q: q, log2: log2, imp: imp, filter: filter, bi: bi, sn: sn, delim: delim, o: o, sc: sc, st: st, test: test
    		}),
    		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    	}).then(function success(response){
    		var responseData = response.data;
    		console.log(response);
    		$scope.criticalErrorString = "";
    		$scope.noncriticalErrorString = "";
    		$scope.criticalErrors = responseData.critical;
    		$scope.noncriticalErrors = responseData.noncritical;
    		angular.forEach($scope.criticalErrors, function(item){
    			$scope.criticalErrorString += item + "\n";
    		});
    		angular.forEach($scope.noncriticalErrors, function(item){
    			$scope.noncriticalErrorString += item + "\n";
    		});
    			//angular.element(document.getElementById('loader-overlay')).css({'display':'none'});
    			angular.element(document.getElementById('errorReportModal')).appendTo('body').modal('show');
    		}).catch(function error(response){
    			console.log(response);
    			alert("Unexpected server error! Please resubmit the file.");
    			angular.element(document.getElementById('loader-overlay')).css({'display':'none'});
    			$scope.uploader.clearQueue(); $scope.clear(); 
    		});
    	}

    	$scope.cancelUpload = function()
    	{
    		$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
    		$http({
    			method: 'POST',
    			url: "cancelUpload.php",
    			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    		}).then(function success(response){
    			angular.element(document.getElementById('loader-overlay')).css({'display':'none'});
    			$scope.uploader.clearQueue(); $scope.clear(); $scope.setName = "";
    		}).catch(function error(response){
    			
    		});
    	}

    	$scope.confirmUpload = function()
    	{
    		$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
    		$http({
    			method: 'POST',
    			url: "saveData.php",
    			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    		}).then(function success(response){
    			angular.element(document.getElementById('loader-overlay')).css({'display':'none'});
    			$scope.uploader.clearQueue(); $scope.clear(); $scope.setName = "";  $scope.updateFileUploadSettings();$scope.updateProcessList();
    		}).catch(function error(response){
    			
    		});
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

    });

uploadDataApp.directive('setName', function($http){
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
						url: "querySetByName.php",
						data: $.param({
							bi:scope.selectedBranch.branch_id,
							sn: scope.setName
						}),
						headers: {'Content-Type': 'application/x-www-form-urlencoded'}
					}).then (function success(response){
						if(response.data=="true")
						{
							ctrl.$setValidity('uniqueSetName', false);
						}
						else
						{
							ctrl.$setValidity('uniqueSetName', true);
						}
					});
				});
			});
		}
	}
});

uploadDataApp.filter('myDateFormat', function myDateFormat($filter){
	return function(text){
		var  tempdate= new Date(text.replace(/-/g,"/"));
		return $filter('date')(tempdate, "medium");
	}
});

uploadDataApp.filter('finished', function (){
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

uploadDataApp.filter('running', function (){
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

uploadDataApp.directive('branchName', function($http){
	var toId;
	return {
		restrict: 'A',
		require: 'ngModel',
		link: function (scope, elm, attr, ctrl)
		{

			scope.$watch(attr.ngModel, function(value) {
				if(toId) clearTimeout(toId);

				toId = setTimeout(function(){
					if (scope.newBranchName==="" || scope.newBranchName===undefined)
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
							url: "queryBranchByName.php",
							data: $.param({
								bn:scope.newBranchName,
							}),
							headers: {'Content-Type': 'application/x-www-form-urlencoded'}
						}).then (function success(response){
							if(response.data=="true")
							{
								ctrl.$setValidity('uniqueBranchName', false);
							}
							else
							{
								ctrl.$setValidity('uniqueBranchName', true);
							}
						});
					}
				});
			});
		}
	}
});

uploadDataApp.directive('range', function() {
	return {
		restrict: 'A',
		require: 'ngModel',
		link: function (scope, element, attrs, ngModel) {
			if (!ngModel) return;
			ngModel.$parsers.push(function(val) {
				var parsed = val.replace(/[^0-9]+/g, '').replace(/^0+/, '');
				if (parseInt(parsed) >= parseInt(attrs.rangeMax)) parsed = attrs.rangeMax;
				if (parseInt(parsed) <= parseInt(attrs.rangeMin)) parsed = attrs.rangeMin
					if(!parseInt(parsed) && val.length>0)
					{
						parsed = "0";
					}
					ngModel.$setValidity('validPercent', val!=='');
					if (val !== parsed) {
						ngModel.$setViewValue(parsed);
						ngModel.$render();
					}
					return parsed;
				});
		}
	};
});

uploadDataApp.directive('condrange', function() {
	return {
		restrict: 'A',
		require: 'ngModel',
		link: function (scope, element, attrs, ngModel) {
			if (!ngModel) return;
			ngModel.$parsers.push(function(val) {
				var parsed = val.replace(/[^0-9]+/g, '').replace(/^0+/, '');
				if (parseInt(parsed) >= parseInt(attrs.rangeMax)) parsed = attrs.rangeMax;
				if (parseInt(parsed) <= parseInt(attrs.rangeMin)) parsed = attrs.rangeMin
					if(!parseInt(parsed) && val.length>0)
					{
						parsed = "0";
					}
					ngModel.$setValidity('validCond', val!=='');
					if (val !== parsed) {
						ngModel.$setViewValue(parsed);
						ngModel.$render();
					}
					return parsed;
				});
		}
	};
});

uploadDataApp.directive('projectHierarchyTree', [function(){
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

              root_tree = scope.$parent.root_tree;

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
    .style("fill", function(d) { if (d.control=="Yes") { return d._children ? "#FF9292" : "#fff"; } return d._children ? "lightsteelblue" : "#fff"; })
    .style("stroke", function(d){ if (d.control=="Yes"){return "#FF2525";} return "steelblue"; });

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
    .style("fill", function(d) {  if (d.control=="Yes") { return d._children ? "#FF9292" : "#fff"; } return d._children ? "lightsteelblue" : "#fff"; })
    .style("stroke", function(d) {if (d.control=="Yes"){return "#FF2525";} return "steelblue"; });

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
