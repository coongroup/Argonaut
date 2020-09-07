var coonDataApp =angular.module('coonDataApp', ['ngSanitize', 'ui.select']);

// setup dependency injection

var moleculeDict = [];
var tableDict = [];
var molIDDict= [];

//var coonDataApp = angular.module('coonDataApp', []);
coonDataApp.controller('branchCtrl', function($scope, $http){
$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
$http({
	method: 'POST',
	url: "queryBranchesFromProject.php",
	headers: {'Content-Type': 'application/x-www-form-urlencoded'}
}).then (function success(response){
	$scope.project_branch_data = (response.data);
});
//Intialize these values to 0
$scope.project_branch_dataset_count = 0;
$scope.project_branch_condition_count = 0;
$scope.project_branch_replicate_count = 0;
$scope.project_branch_measurement_count = 0;
$scope.project_branch_avg_rep_cv = 0;
$scope.project_branch_avg_meas_per_rep = 0;
$scope.project_branch_avg_meas_per_cond = 0;
$scope.project_branch_avg_overlap_cond = 0;

$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
$http({
	method: 'POST',
	url: "queryMolecules.php",
	headers: {'Content-Type': 'application/x-www-form-urlencoded'}
}).then (function success(response){
	if (response.data.length>0)
	{
		angular.forEach(response.data, function(d)
        {
            moleculeDict[d.unique_identifier_id] = [];
            moleculeDict[d.unique_identifier_id].name = d.unique_identifier_text;
            moleculeDict[d.unique_identifier_id].metadata = [];
            molIDDict[d.unique_identifier_text] = d.unique_identifier_id;
            tableDict.push({molecule_name: d.unique_identifier_text});
        });
		$http({
			method: 'POST',
			url: "queryMoleculeMetadata.php",
			data: $.param({
			v1: 0,
			v2: 60000
			}),
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
		}).then (function success(response2){
			angular.forEach(response2.data, function(d)
			 {
			 	moleculeDict[d.unique_identifier_id].metadata.push({name: d.feature_metadata_name, text: d.feature_metadata_text});
			 });

			$http({
			method: 'POST',
			url: "queryMoleculeMetadata.php",
			data: $.param({
			v1: 60001,
			v2: 120000
			}),
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
		}).then (function success(response2){
			angular.forEach(response2.data, function(d)
			 {
			 	moleculeDict[d.unique_identifier_id].metadata.push({name: d.feature_metadata_name, text: d.feature_metadata_text});
			 });
		});
		});
	}
});

$scope.changedValue = function(item)
{
	//query branch statistics
	$http({
		method: 'POST',
		url: "queryBranchStats.php",
		data: $.param({
			bi: item
		}),
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	}).then (function success(response){
		$scope.project_branch_statistics = (response.data);

		//Probably want to do some rounding here 
		response.data.forEach(function(d)
		{
			$scope.project_branch_dataset_count = d.set_count;
			$scope.project_branch_condition_count = d.condition_count;
			$scope.project_branch_replicate_count = d.replicate_count;
			$scope.project_branch_measurement_count = d.quant_measurement_count;
			$scope.project_branch_avg_rep_cv = d.avg_rep_cv + "%";
			$scope.project_branch_avg_meas_per_rep = d.avg_meas_per_rep;
			$scope.project_branch_avg_meas_per_cond = d.avg_meas_per_cond;
			$scope.project_branch_avg_overlap_cond = d.avg_meas_overlap_cond;
		})
	});
}

});


coonDataApp
.controller('hierarchyTreeCtrl',  function($scope, $http){
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


coonDataApp
.controller('fullVolcanoCtrl',  function($scope, $http){
	$scope.title = "fullVolcanoCtrl";
	$scope.pValueCutoff="0.05";
	$scope.foldChangeCutoff="1";
	$scope.fixedScale=false;
	$scope.fixedScaleX = 2;
	$scope.fixedScaleY = 5;
	$scope.overflow = '';
	$scope.branchName = "";
	$scope.conditionName = "– Condition";
	$scope.fullVolcanoBranch = "";
	$scope.fullVolcanoCondition = "";
	$scope.isDirty = false;
	$scope.speedMode=false;
	$scope.testingCorrection="uncorrected";
	$scope.selectedMolecule = '';
	$scope.tooltipText = '';
	$scope.tooltipQuantText = '';

	$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
	$http({
		method: 'POST',
		url: "queryBranchesFromProject.php",
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	}).then (function success(response){
		$scope.volcano_full_branch_data = (response.data);
		$scope.isDirty = true;
	});


	$scope.branchChanged= function(branch_id, branch_name)
	{
		$scope.branchName = branch_name;
		$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
	$http({
		method: 'POST',
		url: "queryConditions.php",
		data:  $.param({bi: branch_id}),
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	}).then (function success(response){
		$scope.volcano_full_conditions = (response.data);
		$scope.isDirty = true;
		$scope.tooltipText = "";
		$scope.selectedMolecule ="";
		$scope.tooltipQuantText = "";
	});
	}

	$scope.conditionChanged = function(condition_id, condition_name)
	{
		spinner = new Spinner(opts).spin(document.getElementById('fullVolcanoColumn'));
		$scope.conditionName = "– " + condition_name;
	$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
	$http({
		method: 'POST',
		url: "queryFullVolcano.php",
		data:  $.param({ci: condition_id}),
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	}).then (function success(response){
		$scope.tooltipText = "";
		$scope.tooltipQuantText = "";
		$scope.selectedMolecule ="";
		$scope.volcano_full_plot_data = (response.data);
		$scope.isDirty = true;
	});
	}

	$scope.downloadData = function()
	{
		if ($scope.volcano_full_plot_data!=null)
		{
			var csvRows = [];
			var pValueTitle = "P-Value";
			if ($scope.testingCorrection=="bonferroni")
			{
				pValueTitle = "Bonferroni Adjusted P-Value";
			}
			if ($scope.testingCorrection=="fdradjusted")
			{
				pValueTitle = "FDR-Adjusted Q-Value";
			}
			csvRows.push("Molecule Name\tFold Change\t" + pValueTitle + "\tVisible in Plot\n");
			$scope.volcano_full_plot_data.forEach(function(d)
			{
				var visible = "TRUE";
				if (d.vis=="hidden")
				{
					visible = "FALSE";
				}
				csvRows.push(moleculeDict[d.i].name + "\t" + d.fc + "\t" + d.p + "\t" + visible + "\n");
			});
			var csvString = csvRows.join("");
			var a         = document.createElement('a');
			a.href        = 'data:attachment/csv,' +  encodeURIComponent(csvString);
			a.target      = '_blank';
			a.download    = $scope.branchName + "_" + $scope.conditionName.replace("–","").trim() + "_VolcanoData.txt";

			document.body.appendChild(a);
			a.click();
		}
		else
		{
			alert("No plot selected!");
		}
	}

	$scope.downloadSVG = function()
	{
		if ($scope.volcano_full_plot_data!=null)
		{
			var config = {
				filename: $scope.branchName + "_" + $scope.conditionName.replace("–","").trim() + "_VolcanoPlot"
			}
			d3_save_svg.save($('#fullVolcanoSVG')[0], config);
			chart_fullVolcano.selectAll("circle")[0].forEach(function (d){
				d.style.overflowX ="";
				d.style.overflowY ="";
				d.style.zIndex ="";
				d.style.perspectiveOrigin="";
				d.style.transformOrigin="";
				d.style.cx = "";
				d.style.cy = "";
				d.style.r = "";
			});
			chart_fullVolcano.selectAll(".domain")[0].forEach(function(d)
			{
				d.style.overflowX = "";
				d.style.overflowY ="";
				d.style.perspectiveOrigin="";
				d.style.transformOrigin="";
				d.style.d="";
			});
		}
		else
		{
			alert("No plot selected!");
		}
	}
});


coonDataApp.filter('propsFilter', function() {
  return function(items, props) {
    var out = [];

    if (angular.isArray(items)) {
      items.forEach(function(item) {
        var itemMatches = false;

        var keys = Object.keys(props);
        for (var i = 0; i < keys.length; i++) {
          var prop = keys[i];
          var text = props[prop].toLowerCase();
          if (item[prop].toString().toLowerCase().indexOf(text) !== -1) {
            itemMatches = true;
            break;
          }
        }

        if (itemMatches) {
          out.push(item);
        }
      });
    } else {
      // Let the output be the input untouched
      out = items;
    }

    return out;
  };
});

coonDataApp.filter('setDecimal', function ($filter) {
    return function (input, places) {
        if (isNaN(input)) return input;
        // If we want 1 decimal place, we want to mult/div by 10
        // If we want 2 decimal places, we want to mult/div by 100, etc
        // So use the following to create that factor
        var factor = "1" + Array(+(places > 0 && places + 1)).join("0");
        return Math.round(input * factor) / factor;
    };
});

var myGlobal = null;

coonDataApp.controller('DemoCtrl', function($scope, $http, $timeout) {

  $scope.disabled = undefined;
  $scope.searchEnabled = undefined;
  $scope.searchRes = [];
  $scope.item = {};
  $scope.tableData = [];
  $scope.allSearchRes =[];
 
	$scope.searchMedia = function($select)
	{
		if ($select.search.length >=0)
  		{
  			$scope.searchRes = [];
  			$scope.allSearchRes = [];
  			var mySearch =  $http({
			method: 'POST',
			url: "queryTable.php",
			data:  $.param({d: $select.search}),
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			}).then (function success(response){
			if (response.data.length < 100)
			{
				$scope.searchRes = response.data;
				$scope.allSearchRes = response.data;
			}
			else
			{
				$scope.allSearchRes =response.data;
				for (var i = 0; i < 100; i++)
				{
					$scope.searchRes.push(response.data[i]);
				}
				document.getElementById('choices').onscroll = function(){ $scope.infiniteScroll($select)};
			}

		});
		 return mySearch;
  		}
  		else
  		{
  			$scope.searchRes = [];
  		}
	}

	$scope.infiniteScroll = function($select)
	{
		if ($scope.searchRes.length < $scope.allSearchRes.length)
		{
			var id = "ui-select-choices-row-0-" + ($scope.searchRes.length-1);
		    var aEl = document.getElementById(id);
		    var bEl = document.getElementById('choices');
		    if (aEl != undefined && bEl != undefined)
		    {
			var aTerm = aEl.getBoundingClientRect().bottom;
			var bTerm = bEl.getBoundingClientRect().bottom;
			if (aTerm != undefined && bTerm != undefined)
			{
				var diff = aTerm - bTerm;
				if (diff < 10)
				{
					var currSearchResLength = $scope.searchRes.length;
					for (var i =currSearchResLength; i < (currSearchResLength + 100); i++)
					{
						if (i == $scope.allSearchRes.length)
						{
							return;
						}
						$scope.searchRes.push($scope.allSearchRes[i]);
					}
					$select.refreshItems();
				} 
			}
			}
		}
	}

	$scope.myCall = function()
	{
		if ($scope.item.term==undefined)
		{
			alert("Please enter a query term!");
			return;
		}
		if ($scope.item.term.length==0)
		{
			alert("Please enter a query term!");
			return;
		}
		//do stuff here
		var query_term_text = $scope.item.term[0].query_term_text;
		if (query_term_text.includes("(Molecule)"))
		{
			currentLookupType = "MOLECULE";
			$scope.MoleculeQueryColumns();
		}
		if (query_term_text.includes("(Condition)"))
		{
			currentLookupType = "CONDITION";
			$scope.ConditionQueryColumns();
		}
		if (query_term_text.includes("(Replicate)"))
		{
			currentLookupType = "REPLICATE";
			$scope.ReplicateQueryColumns();
		}
		spinner = new Spinner(opts).spin(document.getElementById('dataLookupColumn'));

		$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
		$http({
			method: 'POST',
			url: "queryTableData.php",
			data:  $.param({t: query_term_text}),
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
		}).then (function success(response){
			$scope.tableData = (response.data);
			spinner.stop();
		});
	}

	$scope.$watch('tableData', function() { 
		if ($scope.tableData.length > 0)
		{
			tableOneData = [];
                if (currentLookupType=="REPLICATE") {
                   $scope.tableData.forEach(function (d) {
                        tableOneData.push({
                            molName: d.unique_identifier_text,
                            repQuantVal: d.quant_value,
                            avgQuantVal: d.avg_quant_value,
                            stdDevQuantVal: d.std_dev_quant_value,
                            cvQuantVal: d.cv_quant_values,
                            allQuantVal: d.all_quant_values,
                            fcMeanNorm: d.fold_change_mean_norm,
                            pValueMeanNorm: d.p_value_mean_norm,
                            fcControlNorm: d.fold_change_control_norm,
                            pValueControlNorm: d.p_value_control_norm,
                            repName: d.replicate_name,
                            condName: d.condition_name,
                            setName: d.set_name,
                            branchName: d.branch_name,
                            pValueMeanNormFDR: d.fdr_p_value_mean_norm,
                            pValueMeanNormBonferroni: d.bonferroni_p_value_mean_norm,
                            pValueControlNormFDR: d.fdr_p_value_control_norm,
                            pValueControlNormBonferroni: d.bonferroni_p_value_control_norm
                        });
                    });
                }
                else
                {
                   $scope.tableData.forEach(function (d) {
                        tableOneData.push({
                            molName: d.unique_identifier_text,
                            repQuantVal: d.quant_value,
                            avgQuantVal: d.avg_quant_value,
                            stdDevQuantVal: d.std_dev_quant_value,
                            cvQuantVal: d.cv_quant_values,
                            allQuantVal: d.all_quant_values,
                            fcMeanNorm: d.fold_change_mean_norm,
                            pValueMeanNorm: d.p_value_mean_norm,
                            fcControlNorm: d.fold_change_control_norm,
                            pValueControlNorm: d.p_value_control_norm,
                            repName: d.replicate_name,
                            condName: d.condition_name,
                            setName: d.set_name,
                            branchName: d.branch_name,
                            pValueMeanNormFDR: d.fdr_p_value_mean_norm,
                            pValueMeanNormBonferroni: d.bonferroni_p_value_mean_norm,
                            pValueControlNormFDR: d.fdr_p_value_control_norm,
                            pValueControlNormBonferroni: d.bonferroni_p_value_control_norm
                        });
                    });
                }

                $('#LookupTableOne').bootstrapTable('load',tableOneData);
                $('#LookupTableOne').bootstrapTable('resetView');
                $('#LookupTableOne').bootstrapTable('refreshOptions', {
                    pagination: true,
                    search: true,
                    pageSize: 50
                });
                $('#LookupTableOne').bootstrapTable('refresh');
		}
		else
		{
			 RefreshBootstrapTable();
		} 
	});

	$scope.downloadData = function()
	{
		var currentTableData = $('#LookupTableOne').bootstrapTable('getData');

		if (currentTableData.length > 0)
		{
			var displayNames = [];
			var csvString = "";
			$('#LookupTableOne').bootstrapTable('getOptions').columns[0].forEach(function(d){ 
				if (d.visible==true)
				{
					csvString += d.title + "\t";
					displayNames.push(d.field);
				}
			});

			csvString +=  "\n";

			$('#LookupTableOne').bootstrapTable('getData').forEach(function(d){
				displayNames.forEach(function (f)
				{
					csvString += d[f] + "\t";
				});
				csvString += "\n";
			});

			var a         = document.createElement('a');
			a.href        = 'data:attachment/csv,' +  encodeURIComponent(csvString);
			a.target      = '_blank';
			a.download    = $scope.item.term[0].query_term_text + "_LookupData.txt"
			document.body.appendChild(a);
			a.click();
		}
	}

	$scope.MoleculeQueryColumns = function()
	{
	    var table = $('#LookupTableOne');
	    table.bootstrapTable('hideColumn', 'molName');
	    table.bootstrapTable('showColumn', 'avgQuantVal');
	    table.bootstrapTable('hideColumn', 'repQuantVal');
	    table.bootstrapTable('showColumn', 'stdDevQuantVal');
	    table.bootstrapTable('showColumn', 'cvQuantVal');
	    table.bootstrapTable('showColumn', 'allQuantVal');
	    table.bootstrapTable('showColumn', 'fcMeanNorm');
	    table.bootstrapTable('showColumn', 'pValueMeanNorm');
	    table.bootstrapTable('showColumn', 'fcControlNorm');
	    table.bootstrapTable('showColumn', 'pValueControlNorm');
	    table.bootstrapTable('hideColumn', 'repName');
	    table.bootstrapTable('showColumn', 'condName');
	    table.bootstrapTable('showColumn', 'setName');
	    table.bootstrapTable('showColumn', 'branchName');
	    table.bootstrapTable('showColumn', 'pValueControlNormBonferroni');
	    table.bootstrapTable('showColumn', 'pValueControlNormFDR');
	    table.bootstrapTable('showColumn', 'pValueMeanNormBonferroni');
	    table.bootstrapTable('showColumn', 'pValueMeanNormFDR');
	}

	$scope.ReplicateQueryColumns = function()
	{
	    var table = $('#LookupTableOne');
	    table.bootstrapTable('showColumn', 'molName');
	    table.bootstrapTable('hideColumn', 'avgQuantVal');
	    table.bootstrapTable('showColumn', 'repQuantVal');
	    table.bootstrapTable('hideColumn', 'stdDevQuantVal');
	    table.bootstrapTable('hideColumn', 'cvQuantVal');
	    table.bootstrapTable('hideColumn', 'allQuantVal');
	    table.bootstrapTable('hideColumn', 'stdDevQuantVal');
	    table.bootstrapTable('hideColumn', 'fcMeanNorm');
	    table.bootstrapTable('hideColumn', 'pValueMeanNorm');
	    table.bootstrapTable('hideColumn', 'fcControlNorm');
	    table.bootstrapTable('hideColumn', 'pValueControlNorm');
	    table.bootstrapTable('hideColumn', 'repName');
	    table.bootstrapTable('showColumn', 'condName');
	    table.bootstrapTable('showColumn', 'setName');
	    table.bootstrapTable('showColumn', 'branchName');
	    table.bootstrapTable('hideColumn', 'pValueControlNormBonferroni');
	    table.bootstrapTable('hideColumn', 'pValueControlNormFDR');
	    table.bootstrapTable('hideColumn', 'pValueMeanNormBonferroni');
	    table.bootstrapTable('hideColumn', 'pValueMeanNormFDR');
	}

	$scope.ConditionQueryColumns = function()
	{
	    var table = $('#LookupTableOne');
	    table.bootstrapTable('showColumn', 'molName');
	    table.bootstrapTable('showColumn', 'avgQuantVal');
	    table.bootstrapTable('hideColumn', 'repQuantVal');
	    table.bootstrapTable('showColumn', 'stdDevQuantVal');
	    table.bootstrapTable('showColumn', 'cvQuantVal');
	    table.bootstrapTable('showColumn', 'allQuantVal');
	    table.bootstrapTable('showColumn', 'stdDevQuantVal');
	    table.bootstrapTable('showColumn', 'fcMeanNorm');
	    table.bootstrapTable('showColumn', 'pValueMeanNorm');
	    table.bootstrapTable('showColumn', 'fcControlNorm');
	    table.bootstrapTable('showColumn', 'pValueControlNorm');
	    table.bootstrapTable('hideColumn', 'repName');
	    table.bootstrapTable('hideColumn', 'condName');
	    table.bootstrapTable('showColumn', 'setName');
	    table.bootstrapTable('showColumn', 'branchName');
	    table.bootstrapTable('showColumn', 'pValueControlNormBonferroni');
	    table.bootstrapTable('showColumn', 'pValueControlNormFDR');
	    table.bootstrapTable('showColumn', 'pValueMeanNormBonferroni');
	    table.bootstrapTable('showColumn', 'pValueMeanNormFDR');
	}
});


coonDataApp
.controller('pcaCtrl', function($scope, $http){
	$scope.title = "pcaCtrl";
	$scope.pca_branch = [];
	$scope.pca_branch_data = [];
	$scope.pcaBranch = "";
	$scope.pca_components = [{name:"PC1", value:1},{name:"PC2", value:2},{name:"PC3", value:3},{name:"PC4", value:4},{name:"PC5", value:5},
	{name:"PC6", value:6},{name:"PC7", value:7},{name:"PC8", value:8},{name:"PC9", value:9},{name:"PC10", value:10}];
	$scope.pcaXAxis = {};
	$scope.pcaYAxis = {};
	$scope.pcaXFraction = 0;
	$scope.pcaYFraction = 0;
	$scope.conditionColorDict = [];
	$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
	$http({
		method: 'POST',
		url: "queryBranchesFromProject.php",
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	}).then (function success(response){
		$scope.pca_branch = (response.data);
	});

	$scope.$watch('pcaBranch', function()
	{
		if ($scope.pcaBranch != "")
		{
			$scope.pcaXAxis = $scope.pca_components[0];
			$scope.pcaYAxis = $scope.pca_components[1];
			$scope.updatePCAData();
		}	
	});

	$scope.updatePCAData = function()
	{
		 if ($scope.pcaXAxis.name != undefined && $scope.pcaYAxis.name != undefined)
		 {
		 	$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
			$http({
				method: 'POST',
				url: "queryPCA.php",
				data:  $.param({bi: $scope.pcaBranch.branch_id,
								c1: $scope.pcaXAxis.value,
								c2: $scope.pcaYAxis.value}),
				headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			}).then (function success(response){
				$scope.pca_branch_data = (response.data);
				 if (response.data.length > 0)
				 {
				 	$scope.pcaXFraction = (response.data[0].pc_x_fraction* 100)
				 	$scope.pcaYFraction = (response.data[0].pc_y_fraction* 100)

				 	response.data.forEach(function(d)
				 	{
				 		if ($scope.conditionColorDict[d.condition_name]==undefined)
				 		{
				 			$scope.conditionColorDict[d.condition_name]=colorArray[colorIndex];
				 			colorIndex++;
				 			if (colorIndex >= colorArray.length)
				 			{
				 				colorIndex=0;
				 			}
				 		}
				 	});
				 }
			});
		 }
	}


	$scope.downloadData = function()
	{
		if ($scope.pca_branch_data!=null)
		{
			var csvRows = [];
			csvRows.push($scope.pcaXAxis.name + " Variance Fraction: " + $scope.pcaXFraction + "\n");
			csvRows.push($scope.pcaYAxis.name + " Variance Fraction: " + $scope.pcaYFraction + "\n\n");
			csvRows.push("Condition Name\t" + $scope.pcaXAxis.name + "\t" + $scope.pcaYAxis.name + "\n");
			$scope.pca_branch_data.forEach(function(d)
			{
				csvRows.push(d.condition_name + "\t" + d.pc_x_vector + "\t" + d.pc_y_vector + "\n");
			});
			var csvString = csvRows.join("");
			var a         = document.createElement('a');
			a.href        = 'data:attachment/csv,' +  encodeURIComponent(csvString);
			a.target      = '_blank';
			a.download    = $scope.pcaBranch.branch_name + "_PCA.txt";

			document.body.appendChild(a);
			a.click();
		}
		else
		{
			alert("No plot selected!");
		}
	}

	$scope.downloadSVG = function()
	{
		if ($scope.pca_branch_data!=null)
		{
			var config = {
				filename: $scope.pcaBranch.branch_name + "_PCA"
			}
			d3_save_svg.save($('#pcaSVG')[0], config);
			chart_pca.selectAll("circle")[0].forEach(function (d){
				d.style.overflowX ="";
				d.style.overflowY ="";
				d.style.zIndex ="";
				d.style.perspectiveOrigin="";
				d.style.transformOrigin="";
				d.style.cx = "";
				d.style.cy = "";
				d.style.r = "";
			});
			chart_pca.selectAll(".domain")[0].forEach(function(d)
			{
				d.style.overflowX = "";
				d.style.overflowY ="";
				d.style.perspectiveOrigin="";
				d.style.transformOrigin="";
				d.style.d="";
			});
		}
		else
		{
			alert("No plot selected!");
		}
	}
});

coonDataApp
.controller('condScatterCtrl', function($scope, $http){
	$scope.title = "condScatterCtrl";
	$scope.cond_scatter_branch = [];
	$scope.cond_scatter_conditions = [];
	$scope.cond_scatter_data = [];
	$scope.condOne = {};
	$scope.condTwo = {};
	$scope.condOne.condition_name="Condition one";
	$scope.condTwo.condition_name="Condition two";
	$scope.selectedBranch = "";
	$scope.molecule = {
        selected: 'shared'
      };
    $scope.showAxes = true;
    $scope.showFoldChange = false;
    $scope.showBestFit = true;
    $scope.pValueCutoff = 0.05;
    $scope.foldChangeCutoff = 0.7;
    $scope.pearson = 0;
    $scope.slope = 0;
    $scope.speedMode= false;
    $scope.testingCorrection="uncorrected";
    $scope.selectedMolecule = "";
    $scope.tooltipText = "";
    $scope.tooltipQuantText = "";
   
	$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
	$http({
		method: 'POST',
		url: "queryBranchesFromProject.php",
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	}).then (function success(response){
		$scope.cond_scatter_branch = (response.data);
	});
    
    $scope.branchChanged = function()
    {
		$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
	$http({
		method: 'POST',
		url: "queryConditions.php",
		data:  $.param({bi: $scope.selectedBranch.branch_id}),
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	}).then (function success(response){
		$scope.cond_scatter_conditions = (response.data);
    });

	$scope.conditionChanged = function()
	{
		if ($scope.condOne!=undefined && $scope.condTwo !=undefined)
		{
			if ($scope.condOne.condition_id!=undefined && $scope.condTwo.condition_id!= undefined)
			{
				spinner = new Spinner(opts).spin(document.getElementById('ScatterFullPlot'));
				$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
				$http({
					method: 'POST',
					url: "queryCondCondScatter.php",
					data:  $.param({c1: $scope.condOne.condition_id, c2:$scope.condTwo.condition_id}),
					headers: {'Content-Type': 'application/x-www-form-urlencoded'}
				}).then (function success(response){
					$scope.cond_scatter_data = (response.data);
				});
			}
		}
	}

	$scope.downloadData = function()
	{
		if ($scope.cond_scatter_data!=null)
		{
			var csvRows = [];
			var pValueTitle = "P-Value";
			if ($scope.testingCorrection=="fdradjusted")
			{
				pValueTitle = "FDR-Adjusted Q-Value";
			}
			if ($scope.testingCorrection=="bonferroni")
			{
				pValueTitle = "Bonferroni-Adjusted P-Value";
			}
			csvRows.push ("Molecule ID\t" + $scope.condOne.condition_name + " Fold Change\t" + $scope.condOne.condition_name + " " + pValueTitle +"\t" + $scope.condTwo.condition_name + " Fold Change\t" + $scope.condTwo.condition_name + " " + pValueTitle + "\tVisible in Plot\n");
			$scope.cond_scatter_data.forEach(function(d)
			{
				var visible = "TRUE";
				if (d.vis=="hidden")
				{
					visible = "FALSE";
				}
				csvRows.push(moleculeDict[d.i].name + "\t" + d.fc1 + "\t" + d.p1 + "\t" + d.fc2 + "\t" + d.p2 + "\t" + visible + "\n");
			});
			var csvString = csvRows.join("");
			var a         = document.createElement('a');
			a.href        = 'data:attachment/csv,' +  encodeURIComponent(csvString);
			a.target      = '_blank';
			a.download    = $scope.selectedBranch.branch_name + "_"  + $scope.condOne.condition_name + "_vs_" + $scope.condTwo.condition_name +  "_CorrelationPlot.txt";

			document.body.appendChild(a);
			a.click();
		}
		else
		{
			alert("No plot selected!");
		}
	}

	$scope.downloadSVG = function()
	{
		if ($scope.cond_scatter_data!=null)
		{
			var config = {
				filename: $scope.selectedBranch.branch_name + "_"  + $scope.condOne.condition_name + "_vs_" + $scope.condTwo.condition_name +  "_CorrelationScatter"
			}
			d3_save_svg.save($('#scatterSVG')[0], config);
			chart_scatter.selectAll("circle")[0].forEach(function (d){
				d.style.overflowX ="";
				d.style.overflowY ="";
				d.style.zIndex ="";
				d.style.perspectiveOrigin="";
				d.style.transformOrigin="";
				d.style.cx = "";
				d.style.cy = "";
				d.style.r = "";
			});
			chart_scatter.selectAll(".lrline")[0].forEach(function(d)
			{
				d.style.overflowX = "";
				d.style.overflowY ="";
				d.style.perspectiveOrigin="";
				d.style.transformOrigin="";
				d.style.stroke = "";
				d.style.strokeDasharray="";
				d.style.strokeDashoffset="";
				d.style.strokeWidth="";
				d.style.d="";
			});
			chart_scatter.selectAll(".fcline")[0].forEach(function(d)
			{
				d.style.overflowX = "";
				d.style.overflowY ="";
				d.style.perspectiveOrigin="";
				d.style.transformOrigin="";
				d.style.stroke = "";
				d.style.strokeDasharray="";
				d.style.strokeDashoffset="";
				d.style.strokeWidth="";
				d.style.d="";
			});
			chart_scatter.selectAll(".axisline")[0].forEach(function(d)
			{
				d.style.overflowX = "";
				d.style.overflowY ="";
				d.style.perspectiveOrigin="";
				d.style.transformOrigin="";
				d.style.stroke = "";
				d.style.strokeDasharray="";
				d.style.strokeDashoffset="";
				d.style.strokeWidth="";
				d.style.d="";
			});
			chart_scatter.selectAll("g.x.axis")[0].forEach(function(d)
			{
				d.style.overflowX = "";
				d.style.overflowY ="";
				d.style.perspectiveOrigin="";
				d.style.transformOrigin="";
			});
			chart_scatter.selectAll("g.y.axis")[0].forEach(function(d)
			{
				d.style.overflowX = "";
				d.style.overflowY ="";
				d.style.perspectiveOrigin="";
				d.style.transformOrigin="";
			});
			chart_scatter.selectAll(".domain")[0].forEach(function(d)
			{
				d.style.overflowX = "";
				d.style.overflowY ="";
				d.style.perspectiveOrigin="";
				d.style.transformOrigin="";
				d.style.d="";
			});
		}
		else
		{
			alert("No plot selected!");
		}
	}
}

});

coonDataApp
.controller('barChartCtrl', function($scope, $http, $timeout){
	$scope.title = "barChartCtrl";
	$scope.item = {};
	$scope.bar_chart_branch = [];
	$scope.bar_chart_order = [{name:"Alphabetical", value:"alphabetical"}, {name:"Highest to Lowest", value:"ranked"}, {name: "Lowest to Highest", value:"reversed"}];
	$scope.bar_chart_molecule_data = [];
	$scope.barFhartSearchResults = [];
	$scope.allSearchRes = [];
	$scope.searchRes =[];
	$scope.order = {};
    $scope.pValueCutoff = 0.05;
    $scope.foldChangeCutoff = 0.7;
    $scope.selectedMolecule = {};
    $scope.selectedBranch = {};
    $scope.quantData = {};
    $scope.metaData="";
    $scope.testingCorrection="uncorrected";
    $scope.tooltipQuantText = "";
   
	$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
	$http({
		method: 'POST',
		url: "queryBranchesFromProject.php",
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
		}).then (function success(response){
		$scope.bar_chart_branch = (response.data);
		$scope.selectedBranch = response.data[0];
		$scope.order = $scope.bar_chart_order[0];
	});

	$scope.branchChanged = function()
	{
		$scope.searchRes = [];
		$scope.allSearchRes = [];
	}

	$scope.sortData = function()
	{
		if ($scope.quantData.length > 1)
		{
			if ($scope.order.name=="Alphabetical")
			{ 
				$scope.quantData.sort(function(a, b) {
                return a.condition_name.localeCompare(b.condition_name);
            	});
			}
			if ($scope.order.name=="Lowest to Highest")
			{
				$scope.quantData.sort(function(a, b) {
                return (a.fold_change - b.fold_change);
            	});
			}
			if ($scope.order.name=="Highest to Lowest")
			{
				$scope.quantData.sort(function(a, b) {
                return (b.fold_change - a.fold_change);
            	});
			}
		}
	}

	$scope.searchMedia = function($select)
	{
		if ($select.search.length >=0 && $scope.selectedBranch != undefined)
  		{
  			if ($scope.selectedBranch.branch_id != undefined)
  			{
  				console.log("he in here");
	  			$scope.searchRes = [];
				$scope.allSearchRes = [];
	  			var mySearch =  $http({
				method: 'POST',
				url: "queryBarChartMolecules.php",
				data:  $.param({s: $select.search, bi:$scope.selectedBranch.branch_id}),
				headers: {'Content-Type': 'application/x-www-form-urlencoded'}
				}).then (function success(response){
				if (response.data.length < 100)
				{
					$scope.searchRes = response.data;
					$scope.allSearchRes = response.data;
				}
				else
				{
					$scope.allSearchRes =response.data;
					for (var i = 0; i < 100; i++)
					{
						$scope.searchRes.push(response.data[i]);
					}
					document.getElementById('barChartChoices').onscroll = function(){ $scope.infiniteScroll($select)};
				}

			});
		}
			
		 return mySearch;
  		}
  		else
  		{
  			$scope.searchRes = [];
  		}
	}

	$scope.infiniteScroll = function($select)
	{
		if ($scope.searchRes.length < $scope.allSearchRes.length)
		{
			var id = "ui-select-choices-row-1-" + ($scope.searchRes.length-1);
		    var aEl = document.getElementById(id);
		    var bEl = document.getElementById('barChartChoices');
		    if (aEl != undefined && bEl != undefined)
		    {
			var aTerm = aEl.getBoundingClientRect().bottom;
			var bTerm = bEl.getBoundingClientRect().bottom;
			if (aTerm != undefined && bTerm != undefined)
			{
				var diff = aTerm - bTerm;
				if (diff < 10)
				{
					var currSearchResLength = $scope.searchRes.length;
					for (var i =currSearchResLength; i < (currSearchResLength + 100); i++)
					{
						if (i == $scope.allSearchRes.length)
						{
							return;
						}
						$scope.searchRes.push($scope.allSearchRes[i]);
					}
					$select.refreshItems();
				} 
			}
			}
		}
	}

	$scope.onSelected = function($item, $select)
	{
		if ($item.name != $scope.selectedMolecule.name)
			{  
				$scope.selectedMolecule = $item;
				if ($scope.selectedMolecule.molecule_id!=undefined && $scope.selectedMolecule.molecule_id!= null)
				{
				var moleculeEntry = moleculeDict[$scope.selectedMolecule.molecule_id];
	            var namePart = 
	                "<p><strong>Molecule Identifier:</strong> <span>" + moleculeEntry.name + "</span> <br>";
		            moleculeEntry.metadata.forEach(function(u)
		            {
		                namePart += "<strong>" + u.name + ":</strong> <span>" + (u.text) + "</span> <br>";
		            });
	        	}
	        	namePart+= "<p>"
            $scope.tooltipText = namePart;
            $scope.tooltipQuantText = "";
			
		 	$select.selected=[];
		 	$select.select($item);
			$http({
				method: 'POST',
				url: "queryBarChartData.php",
				data:  $.param({ i:$scope.selectedMolecule.molecule_id, bi:$scope.selectedBranch.branch_id}),
				headers: {'Content-Type': 'application/x-www-form-urlencoded'}
				}).then (function success(response){
					$scope.quantData = response.data;
					$scope.sortData();
				});
		}
	}

	$scope.onRemove = function()
	{
		$scope.selectedMolecule = {};
	}

	$scope.downloadData = function()
	{
		if ($scope.quantData.length > 0)
		{
			var csvRows = [];
			csvRows.push ("Condition Name\tFold Change\tStandard Deviation\tP-Value\n");
			$scope.quantData.forEach(function(d)
			{
				csvRows.push(d.condition_name + "\t" + d.fold_change + "\t" + d.std_dev + "\t" + d.p_value + "\n");
			});
			var csvString = csvRows.join("");
			var a         = document.createElement('a');
			a.href        = 'data:attachment/csv,' +  encodeURIComponent(csvString);
			a.target      = '_blank';
			a.download    = $scope.selectedMolecule.name + "_QuantData.txt";

			document.body.appendChild(a);
			a.click();
		}
		else
		{
			alert("No molecule selected!");
		}
	}

	$scope.downloadSVG = function()
	{
		if ($scope.quantData.length > 0)
		{
			var config = {
				filename: $scope.selectedMolecule.name + "_BarChart"
			}
			d3_save_svg.save($('#barChartSVG')[0], config);
			chart_barChart.selectAll(".bar")[0].forEach(function (d){
				d.style.overflowX ="";
				d.style.overflowY ="";
				d.style.zIndex ="";
				d.style.perspectiveOrigin="";
				d.style.transformOrigin="";
				d.style.x = "";
				d.style.y = "";
				d.style.r = "";
				d.style.shapeRendering = "";
				d.style.borderBottomColor = "";
				d.style.borderLeftColor = "";
				d.style.borderTopColor = "";
				d.style.opacity = "";
				d.style.outlineColor = "";
				d.style.columnRuleColor = "";
				d.style.webkitTextEmphasisColor = "";
				d.style.webkitTextFillColor = "";
				d.style.webkitTextStrokeColor = "";

			});
			chart_barChart.selectAll(".errorBar")[0].forEach(function(d)
			{
				d.style.overflowX = "";
				d.style.overflowY ="";
				d.style.perspectiveOrigin="";
				d.style.transformOrigin="";
				d.style.stroke = "";
				d.style.strokeDasharray="";
				d.style.strokeDashoffset="";
				d.style.strokeWidth="";
				d.style.d="";
				d.style.shapeRendering = "";
			});
			
			chart_barChart.selectAll("g.x.axis")[0].forEach(function(d)
			{
				d.style.overflowX = "";
				d.style.overflowY ="";
				d.style.perspectiveOrigin="";
				d.style.transformOrigin="";
				d.style.shapeRendering = "";
			});
			chart_barChart.selectAll("g.y.axis")[0].forEach(function(d)
			{
				d.style.overflowX = "";
				d.style.overflowY ="";
				d.style.perspectiveOrigin="";
				d.style.transformOrigin="";
				d.style.shapeRendering = "";
			});
			chart_barChart.selectAll(".domain")[0].forEach(function(d)
			{
				d.style.overflowX = "";
				d.style.overflowY ="";
				d.style.perspectiveOrigin="";
				d.style.transformOrigin="";
				d.style.d="";
				d.style.fill = "";
				d.style.shapeRendering = "";
				d.style.stroke = "";
			});
		}
		else
		{
			alert("No plot selected!");
		}
	}

});

coonDataApp
.controller('pcaRepCtrl', function($scope, $http){
	$scope.title = "pcaRepCtrl";
	$scope.pca_branch = [];
	$scope.pca_branch_data = [];
	$scope.pcaBranch = "";
	$scope.pca_components = [{name:"PC1", value:1},{name:"PC2", value:2},{name:"PC3", value:3},{name:"PC4", value:4},{name:"PC5", value:5},
	{name:"PC6", value:6},{name:"PC7", value:7},{name:"PC8", value:8},{name:"PC9", value:9},{name:"PC10", value:10}];
	$scope.pcaXAxis = {};
	$scope.pcaYAxis = {};
	$scope.pcaXFraction = 0;
	$scope.pcaYFraction = 0;
	$scope.conditionColorDict = [];
	$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
	$http({
		method: 'POST',
		url: "queryBranchesFromProject.php",
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	}).then (function success(response){
		$scope.pca_branch = (response.data);
	});

	$scope.$watch('pcaBranch', function()
	{
		if ($scope.pcaBranch != "")
		{
			$scope.pcaXAxis = $scope.pca_components[0];
			$scope.pcaYAxis = $scope.pca_components[1];
			$scope.updatePCAData();
		}	
	});

	$scope.updatePCAData = function()
	{
		 if ($scope.pcaXAxis.name != undefined && $scope.pcaYAxis.name != undefined)
		 {
		 	$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
			$http({
				method: 'POST',
				url: "queryPCARep.php",
				data:  $.param({bi: $scope.pcaBranch.branch_id,
								c1: $scope.pcaXAxis.value,
								c2: $scope.pcaYAxis.value}),
				headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			}).then (function success(response){
				 if (response.data.length > 0)
				 {
				 	$scope.pcaXFraction = (response.data[0].pc_x_fraction* 100)
				 	$scope.pcaYFraction = (response.data[0].pc_y_fraction* 100)

				 	response.data.forEach(function(d)
				 	{
				 		if ($scope.conditionColorDict[d.condition_name]==undefined)
				 		{
				 			$scope.conditionColorDict[d.condition_name]=colorArray[colorIndex];
				 			colorIndex++;
				 			if (colorIndex >= colorArray.length)
				 			{
				 				colorIndex=0;
				 			}
				 		}
				 	});
				 }
				 $scope.pca_branch_data = (response.data);
			});
		 }
	}


	$scope.downloadData = function()
	{
		if ($scope.pca_branch_data!=null)
		{
			var csvRows = [];
			csvRows.push($scope.pcaXAxis.name + " Variance Fraction: " + $scope.pcaXFraction + "\n");
			csvRows.push($scope.pcaYAxis.name + " Variance Fraction: " + $scope.pcaYFraction + "\n\n");
			csvRows.push("Replicate Name\tCondition Name\t" + $scope.pcaXAxis.name + "\t" + $scope.pcaYAxis.name + "\n");
			$scope.pca_branch_data.forEach(function(d)
			{
				csvRows.push(d.replicate_name + "\t" + d.condition_name + "\t" + d.pc_x_vector + "\t" + d.pc_y_vector + "\n");
			});
			var csvString = csvRows.join("");
			var a         = document.createElement('a');
			a.href        = 'data:attachment/csv,' +  encodeURIComponent(csvString);
			a.target      = '_blank';
			a.download    = $scope.pcaBranch.branch_name + "_PCA-Replicates_" + $scope.pcaXAxis.name + "_" + $scope.pcaYAxis.name + ".txt";

			document.body.appendChild(a);
			a.click();
		}
		else
		{
			alert("No plot selected!");
		}
	}

	$scope.downloadSVG = function()
	{
		if ($scope.pca_branch_data!=null)
		{
			var config = {
				filename: $scope.pcaBranch.branch_name + "_PCA-Replicates_" + $scope.pcaXAxis.name + "_" + $scope.pcaYAxis.name
			}
			d3_save_svg.save($('#pcaRepSVG')[0], config);
			chart_pca.selectAll("circle")[0].forEach(function (d){
				d.style.overflowX ="";
				d.style.overflowY ="";
				d.style.zIndex ="";
				d.style.perspectiveOrigin="";
				d.style.transformOrigin="";
				d.style.cx = "";
				d.style.cy = "";
				d.style.r = "";
			});
			chart_pca.selectAll(".domain")[0].forEach(function(d)
			{
				d.style.overflowX = "";
				d.style.overflowY ="";
				d.style.perspectiveOrigin="";
				d.style.transformOrigin="";
				d.style.d="";
			});
		}
		else
		{
			alert("No plot selected!");
		}
	}
});

coonDataApp
.controller('outlierCtrl', function($scope, $http){
	$scope.title = "outlierCtrl";
	$scope.algorithms = [{name:"Y3K–uGPS", value:"UGPS"},{name:"Single Measurement", value:"SINGLE"}];
	$scope.outlier_branch_data = {};
	$scope.outlierBranch = {};
	$scope.algorithm = {};
	$scope.outlierData = {};
	$scope.testingCorrection = "uncorrected";
	$scope.pValueCutoff = 0.05;
	$scope.foldChangeCutoff = 0;
	$scope.quantData = {};
	$scope.selectedMolecule = "Molecule";
	$scope.maxCondition = "";
	$scope.displayMolecule = "";
	$scope.displayCondition = "";
	
	$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
	$http({
		method: 'POST',
		url: "queryBranchesFromProject.php",
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	}).then (function success(response){
		$scope.outlier_branch_data = (response.data);
		//$scope.algorithm = $scope.algorithms[0];
	});

	$scope.branchChanged = function()
	{
		$scope.queryOutlierData();
	}
	
	$scope.algorithmChanged = function()
	{
		$scope.queryOutlierData();
	}

	$scope.queryOutlierData = function()
	{
		if ($scope.outlierBranch.branch_id != undefined && $scope.algorithm.value != undefined)
		{
			spinner = new Spinner(opts).spin(document.getElementById('outlierTableWrapper'));
			$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
			$http({
				method: 'POST',
				url: "queryOutlierData.php",
				data:  $.param({bi: $scope.outlierBranch.branch_id,
								v: $scope.algorithm.value}),
				headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			}).then (function success(response){
				$scope.outlierData = (response.data);
				$scope.updateTableData();
				spinner.stop();
			});
		}
	}

	$scope.$watch('pValueCutoff', function()
	{
		$scope.updateTableData();
	});

	$scope.$watch('foldChangeCutoff', function()
	{
		$scope.updateTableData();
	});

	$scope.$watch('testingCorrection', function()
	{
		$scope.updateTableData();
	});

	$scope.updateTableData = function()
	{
		var tableData = [];
		if ($scope.outlierData.length>0)
		{
		$scope.outlierData.forEach(function(d)
		{
			d.p_value = +d.p_value_control_norm;
			d.fold_change_control_norm = +d.fold_change_control_norm;
			d.distance = +d.distance;
			if ($scope.testingCorrection=="fdradjusted")
			{
				d.p_value = +d.fdr_p_value_control_norm;
			}
			if ($scope.testingCorrection=="bonferroni")
			{
				d.p_value = +d.bonferroni_p_value_control_norm;
			}
			if (Math.abs(d.fold_change_control_norm) > $scope.foldChangeCutoff && d.p_value < $scope.pValueCutoff)
			{
				tableData.push({molName: d.unique_identifier_text, regulation: d.regulation, condName: d.condition_name, distance: d.distance.toFixed(6), foldChange: d.fold_change_control_norm.toFixed(6), pValue: d.p_value.toExponential(4), molecule_id:d.unique_identifier_id, max_cond:d.max_regulated_condition_id});
			}
			});

		}
		
		 $('#OutlierTableOne').bootstrapTable('load',tableData);
                $('#OutlierTableOne').bootstrapTable('resetView');
                $('#OutlierTableOne').bootstrapTable('refreshOptions', {
                    pagination: true,
                    search: true,
                    pageSize: 50
                });
         $('#OutlierTableOne').bootstrapTable('refresh');
     
	}

	$('#OutlierTableOne').bootstrapTable().on('click-row.bs.table', function (e, row, $element) {
		$('#OutlierTableOne tr.success').removeClass("success");
		$element.attr("class", "success");
		$scope.maxCondition = row.max_cond;
		$scope.selectedMolecule = row.molName;
		$scope.displayMolecule = " – " + row.molName;
		$scope.displayCondition = " – " + row.condName;
		$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
		$http({
			method: 'POST',
			url: "queryBarChartData.php",
			data:  $.param({bi: $scope.outlierBranch.branch_id,
							i: row.molecule_id}),
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
		}).then (function success(response){
			$scope.quantData = (response.data);
			var moleculeEntry = moleculeDict[row.molecule_id];
                var namePart = "<p><strong>Molecule Identifier:</strong> <span>" + moleculeEntry.name + "</span> <br>";
                moleculeEntry.metadata.forEach(function(u)
                {
                  namePart += "<strong style='color:dodgerblue'>" + u.name + ":</strong> <span style='color:white'>" + GetShortString(u.text) + "</span> <br>";
                });
                $scope.metadataText = namePart;

                $scope.quantData.forEach(function(d)
                {
                	if (d.condition_id==row.max_cond)
                	{
                		d.p_value = +d.p_value;
                		d.p_value_fdr = +d.p_value_fdr;
                		d.p_value_bonferroni = +d.p_value_bonferroni;
                		var quantPart = "<p><strong style='color:dodgerblue'>" + "LFQ fold change: " + "</strong> <span style='color:white'>" + (Math.round(d.fold_change*10000)/10000) + " </span><br>"
		                  + "<strong style='color:dodgerblue'>" + "P-Value: " + "</strong> <span style='color:white'>" + d.p_value.toExponential(4) + " </span><br>"
		                  + "<strong style='color:dodgerblue'>" + "FDR adjusted Q-Value: " + "</strong> <span style='color:white'>" + d.p_value_fdr.toExponential(4) + " </span><br>"
		                  + "<strong style='color:dodgerblue'>" + "Bonferroni adjusted P-Value: " + "</strong> <span style='color:white'>" + d.p_value_bonferroni.toExponential(4) + " </span><br></p>";
                  			$scope.quantDataText = quantPart;
                	}
                });
		});
	});
});

var globalElement = null;