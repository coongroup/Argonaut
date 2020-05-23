
var coonDataApp =angular.module('coonDataApp', ['ngSanitize', 'ui.select', 'jq-multi-select']);

// setup dependency injection

var moleculeDict = [];
var tableDict = [];
var molIDDict= [];
var metadataDict = [];
var metadataTerms = [];


coonDataApp.controller('editCtrl', function ($scope, $http){
	$scope.canEdit = false;
	$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
	$http({
		method: 'POST',
		url: "getPermissionLevel.php",
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	}).then (function success(response){
		response.data >=2 ? $scope.canEdit = true : $scope.canEdit = false;
	});
});

coonDataApp.controller('tabCtrl', function ($scope, $http, $rootScope){
	$scope.outlierTabClick = function()
	{
		$rootScope.$broadcast('outlierTabClick', null);
	}
	$scope.volcanoTabClick = function()
	{
		$rootScope.$broadcast('volcanoTabClick', null);
	}
	$scope.scatterTabClick = function()
	{
		$rootScope.$broadcast('scatterTabClick', null);
	}
	$scope.pcaCondTabClick = function()
	{
		$rootScope.$broadcast('pcaCondTabClick', null);
	}
	$scope.pcaRepTabClick = function()
	{
		$rootScope.$broadcast('pcaRepTabClick', null);
	}
	$scope.hcHeatTabClick = function()
	{
		$rootScope.$broadcast('hcHeatTabClick', null);
	}
	$scope.barTabClick = function()
	{
		$rootScope.$broadcast('barTabClick', null);
	}
	$scope.goTabClick = function()
	{
		$rootScope.$broadcast('goTabClick', null);
	}
	$scope.hcHeatTabClick = function()
	{
		$rootScope.$broadcast('hcHeatTabClick', null);
	}
});


//var coonDataApp = angular.module('coonDataApp', []);
coonDataApp.controller('branchCtrl', function($scope, $http){


	$scope.project_branch_data = [];
	$scope.data = null;

	$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
	$http({
		method: 'POST',
		url: "queryBranchesFromProject.php",
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	}).then (function success(response){
		$scope.project_branch_data = (response.data);
		if($scope.data===null){
			$scope.data=$scope.project_branch_data[0];
			$scope.changedValue($scope.data.branch_id);
		}
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
			$scope.project_branch_avg_rep_cv = parseFloat(d.avg_rep_cv).toFixed(2) + "%";
			$scope.project_branch_avg_meas_per_rep = d.avg_meas_per_rep;
			$scope.project_branch_avg_meas_per_cond = d.avg_meas_per_cond;
			$scope.project_branch_avg_overlap_cond = d.avg_meas_overlap_cond;
		})
	});
}

});


//Gene ontology controller
coonDataApp.controller('goCtrl',function ($scope, $http, $interval){
	$scope.goBranches = {};
	$scope.goConditions ={};
	$scope.goTerms = [];
	$scope.shortGOTerms = [];
	$scope.goTermsLookup = [];
	$scope.item = {};
	$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
	$http({
		method: 'POST',
		url: "queryBranchesFromProject.php",
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	}).then (function success(response){
		$scope.goBranches = (response.data);
	});

	$scope.goFCCutoff = 1;
	$scope.fcSymbol = ">";
	$scope.pValueSymbol="<";
	$scope.goPValueCutoff=0.05;
	$scope.cutoffTerms="oneMet";
	$scope.repeatAllConds=true;
	$scope.goConditionName="Condition";
	$scope.goCondition = {};
	$scope.allMolIDs = [];
	$scope.termName = "";
	$scope.termFullName = "";
	$scope.termExtID = "";
	$scope.termNamespace = "";
	$scope.termDef = "";
	$scope.featureMetadataTerms = [];
	$scope.goShortenLongTerms = true;
	$scope.volcanoData = null;
	$scope.highlight = {};
	$scope.completedGOAnalyses = [];
	$scope.monitorGOProcesses = false;
	$scope.goProcesses = {};
	$scope.goTableData = [];
	$scope.select = null;
	$scope.testingCorrection="uncorrected";
	$scope.goBranch = null;
	$scope.goCondition = null;
	$scope.previousGoAnalysis = null;
	$scope.currAnalysisID = -1;

	$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
	$http({
		method: 'POST',
		url: "queryFeatureMetadataTerms.php",
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	}).then (function success(response){
		angular.forEach(response.data, function(d){
			$scope.featureMetadataTerms.push({"name": d, "selected": true});});
		
	});

	$scope.$on('goTabClick', function()
	{
		if ($scope.goBranch===null)
		{
			$scope.goBranch = $scope.goBranches[0];
			$scope.branchChange();
		}
	});

	$scope.branchChange = function()
	{
		$scope.goConditions ={};
		$scope.allMolIDs = [];
		$scope.currAnalysisID=-1;
		if(!$scope.goBranch.branch_id!==undefined)
		{
			$http({
				method: 'POST',
				url: "queryConditions.php",
				data:  $.param({bi: $scope.goBranch.branch_id}),
				headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			}).then (function success(response){
				$scope.goConditions = response.data;
				if($scope.goCondition===null)
				{
					$scope.goCondition = $scope.goConditions[0];
					$scope.conditionChange();
				}
			});
		}
	}

	$scope.conditionChange = function()
	{
		$scope.goConditionName = "Condition";
		$scope.previousGoAnalysis = null;
		$scope.currAnalysisID=-1;
		if($scope.goCondition.condition_id!==null)
		{
			$scope.goConditionName = $scope.goCondition.condition_name;
			$scope.goTerms = [];
			$scope.allMolIDs = [];
			$scope.shortGOTerms = [];
			$scope.goTermsLookup = [];
			$scope.onRemove();
			var goOpts = opts;
			goOpts.top="200px";
			spinner = new Spinner(goOpts).spin(document.getElementById('goVolcanoColumn'));
			$http({
				method: 'POST',
				url: "queryGOTermsFromCondition.php",
				data:  $.param({ci: $scope.goCondition.condition_id}),
				headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			}).then (function success(response){
				$scope.goTerms = response.data;
				angular.forEach($scope.goTerms, function(d){
					d.display = d.term + " (" + d.namespace + " | " + d.matching_molecules + " Proteins Matched)";
					var tmp ={display:d.term + " (" + d.namespace + " | " + d.matching_molecules + " Proteins Matched)", term_id:d.term_id};
					if($scope.shortGOTerms.length<100)
					{
						$scope.shortGOTerms.push(tmp);
					}
					$scope.goTermsLookup.push(tmp);
				});
			});

			$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
				$http({
					method: 'POST',
					url: "queryFullVolcano.php",
					data:  $.param({ci: $scope.goCondition.condition_id}),
					headers: {'Content-Type': 'application/x-www-form-urlencoded'}
				}).then (function success(response){
					//
					$scope.volcanoData = response.data;
				});
				$scope.getCompletedGOAnalyses();

			var goTableData = [];
			$('#GOTableOne').bootstrapTable('load',goTableData);
					$('#GOTableOne').bootstrapTable('resetView');
					$('#GOTableOne').bootstrapTable('refreshOptions', {
						pagination: true,
						search: true,
						pageSize: 50
					});
					$('#GOTableOne').bootstrapTable('refresh');
		}
	}

	$scope.getCompletedGOAnalyses = function()
	{
		if($scope.goCondition!==undefined)
		{
			$scope.completedGOAnalyses = [];
			$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
				$http({
					method: 'POST',
					url: "queryGOAnalyses.php",
					data:  $.param({ci: $scope.goCondition.condition_id}),
					headers: {'Content-Type': 'application/x-www-form-urlencoded'}
				}).then (function success(response){
					$scope.completedGOAnalyses = response.data;
					if($scope.previousGoAnalysis===null && $scope.completedGOAnalyses.length > 0)
					{
						$scope.previousGoAnalysis = response.data[0];
						$scope.getPreviousGOAnalysis();
					}
				});
		}
	}

	$scope.getPreviousGOAnalysis = function()
	{
		if($scope.previousGoAnalysis!== undefined && $scope.previousGoAnalysis!==null)
		{
			if($scope.previousGoAnalysis.id!=$scope.currAnalysisID)
			{
			//$scope.goTableData = [];
			goTableData = [];
			$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
				$http({
					method: 'POST',
					url: "queryGOResults.php",
					data:  $.param({ai: $scope.previousGoAnalysis.id}),
					headers: {'Content-Type': 'application/x-www-form-urlencoded'}
				}).then (function success(response){
					$scope.goResults = response.data;
					$scope.currAnalysisID = $scope.previousGoAnalysis.id;
					angular.forEach(response.data, function(d){
						var currEnrich = (parseFloat(d.a)/(parseFloat(d.a)+parseFloat(d.c)))/(parseFloat(d.b)/(parseFloat(d.b)+parseFloat(d.d)));
						goTableData.push({goTerm:d.term, pVal:d.p_value, pValFDR:d.p_value_fdr, pValBonferroni:d.p_value_bonferroni, extID:d.external_id, goID:d.go_term_id, enrich:currEnrich.toFixed(4)});
					});
					$('#GOTableOne').bootstrapTable('load',goTableData);
					$('#GOTableOne').bootstrapTable('resetView');
					$('#GOTableOne').bootstrapTable('refreshOptions', {
						pagination: true,
						search: true,
						pageSize: 50
					});
					$('#GOTableOne').bootstrapTable('refresh');
				});
			}
		}
		else
		{
			RefreshBootstrapTable();
		} 
	}

	$scope.deleteAllGOAnalyses = function()
	{
		$scope.previousGoAnalysis=null;
		$scope.currAnalysisID=-1;
		goTableData = [];
		$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
		$http({
			method: 'POST',
			url: "deleteAllGOAnalyses.php",
			data:  $.param({bi: $scope.goBranch.branch_id}),
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
		}).then (function success(response){
			$scope.getCompletedGOAnalyses();
			$('#GOTableOne').bootstrapTable('load',goTableData);
			$('#GOTableOne').bootstrapTable('resetView');
			$('#GOTableOne').bootstrapTable('refreshOptions', {
				pagination: true,
				search: true,
				pageSize: 50
			});
			$('#GOTableOne').bootstrapTable('refresh');
			alert("All previously calculate GO enrichments with in the branch '" + $scope.goBranch.branch_name + "' have been removed.");
		});

	}

	$scope.deleteCurrentGOAnalysis = function()
	{
		$scope.currAnalysisID=-1;
		goTableData = [];
		$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
		$http({
			method: 'POST',
			url: "deleteCurrentGOAnalysis.php",
			data:  $.param({ai: $scope.previousGoAnalysis.id}),
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
		}).then (function success(response){
			$scope.previousGoAnalysis=null;
			$scope.getCompletedGOAnalyses();
			$('#GOTableOne').bootstrapTable('load',goTableData);
			$('#GOTableOne').bootstrapTable('resetView');
			$('#GOTableOne').bootstrapTable('refreshOptions', {
				pagination: true,
				search: true,
				pageSize: 50
			});
			$('#GOTableOne').bootstrapTable('refresh');
			alert("Your selected GO analysis has been removed from our server.");
		});
	}

	$scope.searchMedia = function($select)
	{
		//this is where you set up your existing go terms
		if($select!==null)
		{
			$scope.select = $select;
		}
		if($scope.goTerms.length > 0)
		{
			if ($select.search.length >=0)
			{
				$scope.shortGOTerms = [];
				$scope.goTermsLookup = $scope.goTerms.filter(function(item){return item.display.toLowerCase().indexOf($select.search.toLowerCase())!=-1;});
				if($scope.goTermsLookup.length > 100)
				{
					for (var i = 0; i < 100; i++)
					{
						$scope.shortGOTerms.push($scope.goTermsLookup[i]);
					}
				}
				else
				{
					angular.forEach($scope.goTermsLookup, function(d){
						$scope.shortGOTerms.push(d);
					});
				}
			}
			else
			{
				$scope.shortGOTerms = [];
				$scope.goTermsLookup = [];
				angular.forEach($scope.goTerms, function(d){
					$scope.goTermsLookup.push(d);
				});
				if($scope.goTerms.length > 100)
				{
					for (var i = 0; i < 100; i++)
					{
						$scope.shortGOTerms.push($scope.goTermsLookup[i]);
					}
				}
				else
				{
					angular.forEach($scope.goTermsLookup, function(d){
						$scope.shortGOTerms.push(d);
					});
				}
			}
		}
			document.getElementById('goTermBar').onscroll = function(){ $scope.infiniteScroll($select)};
			$select.refreshItems();
	}

	$scope.infiniteScroll = function($select)
	{
		if($select!==null)
		{
			$scope.select = $select;
		}
		if ($scope.shortGOTerms.length < $scope.goTermsLookup.length)
		{
			var id = "ui-select-choices-row-1-" + ($scope.shortGOTerms.length-1);
			var aEl = document.getElementById(id);
			var bEl = document.getElementById('goTermBar');
			if (aEl != undefined && bEl != undefined)
			{
				var aTerm = aEl.getBoundingClientRect().bottom;
				var bTerm = bEl.getBoundingClientRect().bottom;
				if (aTerm != undefined && bTerm != undefined)
				{
					var diff = aTerm - bTerm;
					if (diff < 10)
					{
						var currSearchResLength = $scope.shortGOTerms.length;
						for (var i =currSearchResLength; i < (currSearchResLength + 100); i++)
						{
							if (i == $scope.goTermsLookup.length)
							{
								return;
							}
							$scope.shortGOTerms.push($scope.goTermsLookup[i]);
						}
						$select.refreshItems();
					} 
				}
			}
		}
	}

	$scope.onRemove = function()
	{
		$scope.item.term = {};
		$scope.termName = "";
		$scope.termFullName = "";
		$scope.termExtID = "";
		$scope.termNamespace = "";
		$scope.termDef =  "";
		$scope.goTerm = undefined;
		//$scope.select = null;
	}

	$scope.onSelected = function($item, $select)
	{
		if($scope.item.term.length===0)
		{
			$scope.item.term = $item;
			$select.selected=[];
		}
		else
		{
			if($scope.item.term.length===1)
			{
				if ($item.display != $scope.item.term[0].display)
				{ 
					$scope.item.term = $item;
					$select.selected=[];
					$select.select($item);
				}
			}
		}
	}

	$scope.externalSelect = function($item, $select)
	{
		/*if($scope.item.term===undefined)
		{
			$scope.item.term = $item;
			$select.selected = [];
			$select.select($item);
			console.log("here");
		}*/
		//console.log($select);
		/*$scope.item.term = $item;
			$scope.select.selected = [];
			$scope.select.select($item);*/
	}

	$scope.goQueryTermChange = function()
	{
		if($scope.item!==undefined)
		{
			if($scope.item.term!==undefined)
			{
				if($scope.item.term.length==1)
				{
					var matchingTerm = $scope.goTerms.filter(function(item){return item.term_id===$scope.item.term[0].term_id;});
					if(matchingTerm.length==1)
					{
						$scope.goTerm = matchingTerm[0];
						$scope.goTermChange();
					}
				}
				else
				{
					var goOpts = opts;
					goOpts.top="200px";
					spinner = new Spinner(goOpts).spin(document.getElementById('goVolcanoColumn'));
					$scope.allMolIDs = [];
				}
			}
		}
	}

	$scope.goQueryTermChangeFromTable = function($item)
	{
		var matchingTerm = $scope.goTerms.filter(function(d){return d.term_id===$item.term_id;});
		if (matchingTerm.length===1)
		{
			if($scope.goTerm!==null && $scope.goTerm!==undefined)
			{
				if($scope.goTerm.term_id!==$item.term_id)
				{
					$scope.goTerm = matchingTerm[0];
					$scope.select.selected=[];
					$scope.select.select(matchingTerm[0]);
					$scope.goTermChange();
				}
			}
			else
			{
				$scope.goTerm = matchingTerm[0];
				$scope.select.selected=[];
				$scope.select.select(matchingTerm[0]);
				$scope.goTermChange();
			}
		}
	}

	$scope.goTermChange = function()
	{
		if($scope.goTerm.term_ID!==null)
		{
			spinner.stop();
			var goOpts = opts;
			goOpts.top="200px";
			spinner = new Spinner(goOpts).spin(document.getElementById('goVolcanoColumn'));
			$scope.allMolIDs = $scope.goTerm.all_mol_ids;
			$scope.termName = $scope.goTerm.display;
			$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
				$http({
					method: 'POST',
					url: "queryGOTermInfo.php",
					data:  $.param({ti: $scope.goTerm.term_id}),
					headers: {'Content-Type': 'application/x-www-form-urlencoded'}
				}).then (function success(response){
					$scope.termFullName = response.data.termFullName!==undefined ? response.data.termFullName : "";
					$scope.termExtID = response.data.termExtID!==undefined ? response.data.termExtID : "";
					$scope.termNamespace = response.data.termNamespace!==undefined ? response.data.termNamespace : "";
					$scope.termDef = response.data.termDef!==undefined ? response.data.termDef : "";
				});
		}
	}

	$scope.highlightMoleculeCutoffs = function()
	{
		var goOpts = opts;
			goOpts.top="200px";
			spinner = new Spinner(goOpts).spin(document.getElementById('goVolcanoColumn'));
		$scope.highlight ={display:"Fold Change " + $scope.fcSymbol + " " + $scope.goFCCutoff + " and P-value " + $scope.pValueSymbol + " " + $scope.goPValueCutoff, fcSymbol:$scope.fcSymbol,
			fcCutoff:$scope.goFCCutoff, pValSymbol:$scope.pValueSymbol, pValueCutoff:$scope.goPValueCutoff, time:new Date()};
	}

	$scope.startGOProcessing = function()
	{
		$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
		$http({
			method: 'POST',
			url: "addGOProcess.php",
			data:  $.param({fcs: $scope.fcSymbol, fcc: $scope.goFCCutoff, pvs: $scope.pValueSymbol, pvc: $scope.goPValueCutoff, ci: $scope.goCondition.condition_id, r: $scope.repeatAllConds ? 1 : 0, bi: $scope.goBranch.branch_id}),
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
		}).then (function success(response){
			if(response.data.message!==undefined)
			{
				alert(response.data.message);
				if(response.data.result)
				{
					$scope.start();
				}
			}
		});
	}

	var promise;
	$scope.start = function()
	{
		$scope.stop(); 
		promise = $interval(function(){ $scope.getProcessProgress(); }, 2500);
		$scope.monitorGOProcesses = true;
	}

	$scope.stop = function() {
      $interval.cancel(promise);
    }

	$scope.getProcessProgress = function()
	{
		$scope.getCompletedGOAnalyses();
		$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
		$http({
			method: 'POST',
			url: "checkGOProcesses.php",
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
		}).then (function success(response){
			var totalRunning = 0;
			angular.forEach(response.data, function(d){
				var analysis_id = d.process_id;
				var complete = +d.complete; complete!==1 ? totalRunning++ : null;
				var progress = +d.progress;
				var display = d.display;
				if(complete!==1)
				{
					if ($scope.goProcesses["P" + analysis_id.toString()]===undefined)
					{
						$scope.goProcesses["P" + analysis_id.toString()] = {display: display, progress: progress, id:analysis_id};
					}
					else
					{
						$scope.goProcesses["P" + analysis_id.toString()].progress = progress;
					}
				}
				else
				{
					$scope.goProcesses["P" + analysis_id.toString()]=undefined;
				}

			});
			if (totalRunning===0)
			{
				$scope.goProcesses = {};
				$scope.monitorGOProcesses = false;
				$scope.stop();
			}
		});
	}

	$scope.downloadSVG = function()
	{
		if ($scope.volcanoData!=null)
		{
			var filename = $scope.goTerm!==undefined ? $scope.goConditionName + "_" + $scope.goTerm.term + "_AnnotatedVolcanoPlot" : $scope.goConditionName+ "_AnnotatedVolcanoPlot";
			var config = {
				filename:filename
			}
			d3_save_svg.save($('#goVolcanoSVG')[0], config);
			chart_goVolcano.selectAll("circle")[0].forEach(function (d){
				d.style.overflowX ="";
				d.style.overflowY ="";
				d.style.zIndex ="";
				d.style.perspectiveOrigin="";
				d.style.transformOrigin="";
				d.style.cx = "";
				d.style.cy = "";
				d.style.r = "";
			});
			chart_goVolcano.selectAll(".domain")[0].forEach(function(d)
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

	$scope.downloadData = function()
	{
		if ($scope.volcanoData!=null)
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
			$scope.goTerm!==undefined ? csvRows.push("Molecule Name\tFold Change\t" + pValueTitle + "\tMatches Term: " + $scope.goTerm.term + "\n") : csvRows.push("Molecule Name\tFold Change\t" + pValueTitle + "\n");
			var goTermDict = [];
			var fileName = $scope.goTerm!==undefined ? $scope.goConditionName + "_" + $scope.goTerm.term + "_AnnotatedVolcanoPlotData.txt" : $scope.goConditionName+ "_AnnotatedVolcanoPlotData.txt";
			if ($scope.goTerm !== undefined)
			{
				angular.forEach($scope.goTerm.all_mol_ids, function(d){
					goTermDict[d]="";
				});
			}
			$scope.volcanoData.forEach(function(d)
			{
				var visible = "TRUE";
				if (d.vis=="hidden")
				{
					visible = "FALSE";
				}
				if ($scope.goTerm!==undefined)
				{
					var contained = goTermDict[d.i]!==undefined ? "TRUE" : "FALSE";
					csvRows.push(moleculeDict[d.i].name + "\t" + d.fc + "\t" + d.p + "\t" + contained + "\n");
				}
				else
				{
					csvRows.push(moleculeDict[d.i].name + "\t" + d.fc + "\t" + d.p + "\n");
				}
			});

			var csvString = csvRows.join("");

			var a = window.document.createElement('a');
			a.href = window.URL.createObjectURL(new Blob([csvString], {type: 'text/csv'}));
			a.download = fileName


			// Append anchor to body.
			document.body.appendChild(a)
			a.click();

			// Remove anchor from body
			document.body.removeChild(a)
		}
		else
		{
			alert("No plot selected!");
		}
	}

	$scope.downloadGOData = function()
	{
		if ($scope.goResults!=null && $scope.goResults!==undefined && $scope.previousGoAnalysis!==undefined && $scope.previousGoAnalysis!==null)
		{
			var csvRows = [];
			csvRows.push("Cutoff Settings: " + $scope.previousGoAnalysis.display+"\n\n");
			csvRows.push("GO Term\tExternal ID\tP-Value\tFDR-Adjusted Q-Value\tBonferroni-Adjusted P-Value\n");
			angular.forEach($scope.goResults, function(d){
				csvRows.push(d.term + "\t" + d.external_id + "\t" + d.p_value + "\t" + d.p_value_fdr + "\t" + d.p_value_bonferroni + "\n");
			});
			var fileName = $scope.goCondition.condition_name + "_GOEnrichmentResults.txt";

			var csvString = csvRows.join("");

			var a = window.document.createElement('a');
			a.href = window.URL.createObjectURL(new Blob([csvString], {type: 'text/csv'}));
			a.download = fileName


			// Append anchor to body.
			document.body.appendChild(a)
			a.click();

			// Remove anchor from body
			document.body.removeChild(a)
		}
		else
		{
			alert("No data selected!");
		}
	}

	$('#GOTableOne').bootstrapTable().on('click-row.bs.table', function (e, row, $element) {
	$('#GOTableOne tr.success').removeClass("success");
	$element.attr("class", "success");
	var result = $scope.goTerms.filter(function(item){return item.term_id===row.goID});
	if(result.length===1)
	{
		//$scope.item.term[0] = result[0];
		$scope.goQueryTermChangeFromTable(result[0]);
	}
});

});


coonDataApp.filter('running', function (){
  return function(items){
   var filtered = [];
   angular.forEach(items, function(item){
   if(item!==undefined)
   {
   		if (item.progress!==100 && item.progress!=="100")
   		{
   			filtered.push(item);
   		}
   	}
   });
   return filtered.sort(function(a, b){return a.id - b.id});
  }
});

coonDataApp.controller('downloadCtrl', function ($scope, $http){
		//$scope.file_list = [];
		
	$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
	$http({
		method: 'POST',
		url: "queryDataFiles.php",
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	}).then (function success(response){
		$scope.file_list = response.data;
	});
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
	$scope.featureMetadataTerms = [];
	$scope.moleculeSeekTerms = ["Molecule identifier"];
	$scope.volcanoShortenLongTerms = true;
	$scope.moleculeSeekTerm = $scope.moleculeSeekTerms[0];
	$scope.searchRes = [];
	$scope.queryTerm = [];
	$scope.colorPairs = [{"color": "#FD4703", "text": "white"},{"color": "#B610BF", "text": "white"},{"color": "#0288D9", "text": "white"},{"color": "#00AB38", "text": "white"},{"color": "#FFCE00", "text": "black"},
	{"color": "#FF822A", "text": "white"},{"color": "#CC72F5", "text": "white"},{"color": "#07B9FC", "text": "white"},{"color": "#9AF000", "text": "black"},{"color": "#FFE63B", "text": "black"},
	{"color": "#EA0034", "text": "white"},{"color": "#8200AC", "text": "white"},{"color": "#0047BD", "text": "white"},{"color": "#009543", "text": "white"},{"color": "#FFB300", "text": "black"}];
	$scope.colorIndex = 0;
	$scope.highLightList = {};

	$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
	$http({
		method: 'POST',
		url: "queryBranchesFromProject.php",
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	}).then (function success(response){
		$scope.volcano_full_branch_data = (response.data);
		$scope.isDirty = true;
	});
	$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
	$http({
		method: 'POST',
		url: "queryFeatureMetadataTerms.php",
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	}).then (function success(response){
		angular.forEach(response.data, function(d){
			$scope.featureMetadataTerms.push({"name": d, "selected": true}); $scope.moleculeSeekTerms.push(d);});
		
	});

	$scope.$on('volcanoTabClick', function(){
		if($scope.fullVolcanoBranch==="")
		{
			$scope.fullVolcanoBranch=$scope.volcano_full_branch_data[0];
			$scope.branchChanged($scope.fullVolcanoBranch.branch_id, $scope.fullVolcanoBranch.branch_name);
		}
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
			if($scope.fullVolcanoCondition==="")
			{
				$scope.fullVolcanoCondition = $scope.volcano_full_conditions[0];
				$scope.conditionChanged($scope.fullVolcanoCondition.condition_id, $scope.fullVolcanoCondition.condition_name);
			}
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

			var header = "Molecule Name\t";
			var myMetadataTerms = [];

			$scope.volcano_full_plot_data.forEach(function(d){
				var count = 0;
				angular.forEach(moleculeDict[d.i].metadata, function(e){
					myMetadataTerms[count] = e.name;
					count++;
				});
				
			});
			angular.forEach(myMetadataTerms, function(d){
				header += d + "\t";
			});
			header += "Fold Change\t" + pValueTitle + "\tVisible in Plot\n";

			csvRows.push(header);
			$scope.volcano_full_plot_data.forEach(function(d)
			{
				var visible = "TRUE";
				if (d.vis=="hidden")
				{
					visible = "FALSE";
				}
				var currLine = moleculeDict[d.i].name + "\t";
				var count = 0;
				angular.forEach(moleculeDict[d.i].metadata, function (e){
					if (e.name===myMetadataTerms[count])
					{
						currLine += e.text;
					}
					currLine += "\t";
					count++;
				});
				currLine += d.fc + "\t" + d.p + "\t" + visible + "\n";
				csvRows.push(currLine);
			});

			var csvString = csvRows.join("");

			var a = window.document.createElement('a');
			a.href = window.URL.createObjectURL(new Blob([csvString], {type: 'text/csv'}));
			a.download = $scope.branchName + "_" + $scope.conditionName.replace("–","").trim() + "_VolcanoData.txt";


			// Append anchor to body.
			document.body.appendChild(a)
			a.click();

			// Remove anchor from body
			document.body.removeChild(a)
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

	$scope.searchMedia = function($select)
	{
		if ($select.search.length >=0)
		{
			$scope.searchRes = [];
			$scope.allSearchRes = [];
			var mySearch =  $http({
				method: 'POST',
				url: "queryVolcanoDataPoint.php",
				data:  $.param({d: $select.search, dt: $scope.moleculeSeekTerm}),
				headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			}).then (function success(response){
				
				$scope.searchRes = response.data;
			});
			return mySearch;
		}
		else
		{
			$scope.searchRes = [];
		}
	}

	$scope.tagSelect = function(x)
	{
		if ($scope.colorIndex >= $scope.colorPairs.length)
		{
			$scope.colorIndex = 0;
		}
		var currColorPair = $scope.colorPairs[$scope.colorIndex];
		x.colorPair = currColorPair;
		document.getElementById(x.$$hashKey).parentElement.parentElement.style.backgroundColor = currColorPair.color;
		document.getElementById(x.$$hashKey).parentElement.parentElement.style.color = currColorPair.text;
		$scope.colorIndex++;
		$scope.highLightList[x.unique_id]=currColorPair.color;
		x.text.length > 20 ? x.text = x.text.substr(0, 20) + "..." : null;
	}

	$scope.tagDeselect = function(x)
	{
		$scope.highLightList[x.unique_id]=undefined;
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
					var geneName = "";
					angular.forEach(moleculeDict[d.id].metadata, function(e){
						e.name==="Gene names" ? geneName = e.text : null; 
					});
					//geneName==="" ? geneName = d.unique_identifier_text : null;
					tableOneData.push({
						molName: geneName,
						uniprot: d.unique_identifier_text,
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
					var geneName = "";
					angular.forEach(moleculeDict[d.id].metadata, function(e){
						e.name==="Gene names" ? geneName = e.text : null; 
					});
					//geneName==="" ? geneName = d.unique_identifier_text : null;
					tableOneData.push({
						molName: geneName,
						uniprot: d.unique_identifier_text,
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

		var a = window.document.createElement('a');
		a.href = window.URL.createObjectURL(new Blob([csvString], {type: 'text/csv'}));
		a.download = $scope.item.term[0].query_term_text + "_LookupData.txt"


		// Append anchor to body.
		document.body.appendChild(a)
		a.click();

		// Remove anchor from body
		document.body.removeChild(a)
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
	$scope.condCount = 0;

	$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
	$http({
		method: 'POST',
		url: "queryBranchesFromProject.php",
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	}).then (function success(response){
		$scope.pca_branch = (response.data);
	});

	$scope.$on('pcaCondTabClick', function(){
		if ($scope.pcaBranch==="")
		{
			$scope.pcaBranch=$scope.pca_branch[0];
		}
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
					$scope.condCount = 0;
					$scope.pcaXFraction = (response.data[0].pc_x_fraction* 100)
					$scope.pcaYFraction = (response.data[0].pc_y_fraction* 100)

					response.data.forEach(function(d)
					{
						$scope.condCount++;
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

			var a = window.document.createElement('a');
			a.href = window.URL.createObjectURL(new Blob([csvString], {type: 'text/csv'}));
			a.download = $scope.pcaBranch.branch_name + "_PCA.txt";


			// Append anchor to body.
			document.body.appendChild(a)
			a.click();

			// Remove anchor from body
			document.body.removeChild(a)
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
	$scope.featureMetadataTerms = [];
	$scope.moleculeSeekTerms = ["Molecule identifier"];
	$scope.scatterShortenLongTerms = true;
	$scope.moleculeSeekTerm = $scope.moleculeSeekTerms[0];
	$scope.searchRes = [];
	$scope.queryTerm = [];
	$scope.colorPairs = [{"color": "#FD4703", "text": "white"},{"color": "#B610BF", "text": "white"},{"color": "#0288D9", "text": "white"},{"color": "#00AB38", "text": "white"},{"color": "#FFCE00", "text": "black"},
	{"color": "#FF822A", "text": "white"},{"color": "#CC72F5", "text": "white"},{"color": "#07B9FC", "text": "white"},{"color": "#9AF000", "text": "black"},{"color": "#FFE63B", "text": "black"},
	{"color": "#EA0034", "text": "white"},{"color": "#8200AC", "text": "white"},{"color": "#0047BD", "text": "white"},{"color": "#009543", "text": "white"},{"color": "#FFB300", "text": "black"}];
	$scope.colorIndex = 0;
	$scope.highLightList = {};

	$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
	$http({
		method: 'POST',
		url: "queryBranchesFromProject.php",
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	}).then (function success(response){
		$scope.cond_scatter_branch = (response.data);
	});

	$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
	$http({
		method: 'POST',
		url: "queryFeatureMetadataTerms.php",
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	}).then (function success(response){
		angular.forEach(response.data, function(d){
			$scope.featureMetadataTerms.push({"name": d, "selected": true}); $scope.moleculeSeekTerms.push(d);});
		
	});

	$scope.$on('scatterTabClick', function(){
		if($scope.selectedBranch==="")
		{
			$scope.selectedBranch = $scope.cond_scatter_branch[0];
			$scope.branchChanged();
		}
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
			$scope.condOne = $scope.cond_scatter_conditions[0];
			$scope.condTwo = $scope.cond_scatter_conditions[1];
			$scope.conditionChanged();
		});
	}


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

				var myMetadataTerms = [];
				$scope.cond_scatter_data.forEach(function(d){
					var count = 0;
					angular.forEach(moleculeDict[d.i].metadata, function(e){
						myMetadataTerms[count] = e.name;
						count++;
					});
				});
				var header = "Molecule ID\t";
				angular.forEach(myMetadataTerms, function(d){
					header += d + "\t";
				});
				header +=  $scope.condOne.condition_name + " Fold Change\t" + $scope.condOne.condition_name + " " + pValueTitle +"\t" + $scope.condTwo.condition_name + " Fold Change\t" + $scope.condTwo.condition_name + " " + pValueTitle + "\tVisible in Plot\n";
				csvRows.push (header);

				$scope.cond_scatter_data.forEach(function(d)
				{
					var visible = "TRUE";
					if (d.vis=="hidden")
					{
						visible = "FALSE";
					}
					var currLine = moleculeDict[d.i].name + "\t";
					var count = 0;
					angular.forEach(moleculeDict[d.i].metadata, function (e){
						if (e.name===myMetadataTerms[count])
						{
							currLine += e.text;
						}
						currLine += "\t";
						count++;
					});
					currLine += d.fc1 + "\t" + d.p1 + "\t" + d.fc2 + "\t" + d.p2 + "\t" + visible + "\n";
					csvRows.push(currLine);
				});

				var csvString = csvRows.join("");

				var a = window.document.createElement('a');
				a.href = window.URL.createObjectURL(new Blob([csvString], {type: 'text/csv'}));
				a.download = $scope.selectedBranch.branch_name + "_"  + $scope.condOne.condition_name + "_vs_" + $scope.condTwo.condition_name +  "_CorrelationPlot.txt";


				// Append anchor to body.
				document.body.appendChild(a)
				a.click();

				// Remove anchor from body
				document.body.removeChild(a)
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

		$scope.searchMedia = function($select)
		{
			if ($select.search.length >=0)
			{
				$scope.searchRes = [];
				$scope.allSearchRes = [];
				var mySearch =  $http({
					method: 'POST',
					url: "queryVolcanoDataPoint.php",
					data:  $.param({d: $select.search, dt: $scope.moleculeSeekTerm}),
					headers: {'Content-Type': 'application/x-www-form-urlencoded'}
				}).then (function success(response){

					$scope.searchRes = response.data;
				});
				return mySearch;
			}
			else
			{
				$scope.searchRes = [];
			}
		}

		$scope.tagSelect = function(x)
		{
			if ($scope.colorIndex >= $scope.colorPairs.length)
			{
				$scope.colorIndex = 0;
			}
			var currColorPair = $scope.colorPairs[$scope.colorIndex];
			x.colorPair = currColorPair;
			document.getElementById(x.$$hashKey).parentElement.parentElement.style.backgroundColor = currColorPair.color;
			document.getElementById(x.$$hashKey).parentElement.parentElement.style.color = currColorPair.text;
			$scope.colorIndex++;
			$scope.highLightList[x.unique_id]=currColorPair.color;
			x.text.length > 20 ? x.text = x.text.substr(0, 20) + "..." : null;
		}

		$scope.tagDeselect = function(x)
		{
			$scope.highLightList[x.unique_id]=undefined;
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
	$scope.displayInstructions = true;

	$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
	$http({
		method: 'POST',
		url: "queryBranchesFromProject.php",
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	}).then (function success(response){
		$scope.bar_chart_branch = (response.data);
		$scope.selectedBranch = response.data[0];
		$scope.order = $scope.bar_chart_order[0];
		$scope.branchChanged();
	});

	$scope.branchChanged = function()
	{
		$scope.searchRes = [];
		$scope.allSearchRes = [];
		$scope.allSearchRes = [];
		var mySearch =  $http({
			method: 'POST',
			url: "queryBarChartMolecules.php",
			data:  $.param({s: "", bi:$scope.selectedBranch.branch_id}),
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

	$scope.$on('barTabClick', function(){
		if($scope.displayInstructions)
		{
			$.jGrowl("Choose a molecule under the 'Select Molecule' header to view fold changes across all conditions within a given branch.", { sticky: true, theme: 'bg-google', header: 'Bar Chart–Molecules'  });
			$scope.displayInstructions = false;
		}
	});

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
			var id = "ui-select-choices-row-2-" + ($scope.searchRes.length-1);
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

			var a = window.document.createElement('a');
			a.href = window.URL.createObjectURL(new Blob([csvString], {type: 'text/csv'}));
			a.download = $scope.selectedMolecule.name + "_QuantData.txt";


			// Append anchor to body.
			document.body.appendChild(a)
			a.click();

			// Remove anchor from body
			document.body.removeChild(a)


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
	$scope.repCount = 0;
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

	$scope.$on('pcaRepTabClick', function(){
		if ($scope.pcaBranch==="")
		{
			$scope.pcaBranch=$scope.pca_branch[0];
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
					$scope.repCount = 0;
					response.data.forEach(function(d)
					{
						
						$scope.repCount++;
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

			var a = window.document.createElement('a');
			a.href = window.URL.createObjectURL(new Blob([csvString], {type: 'text/csv'}));
			a.download = $scope.pcaBranch.branch_name + "_PCA-Replicates_" + $scope.pcaXAxis.name + "_" + $scope.pcaYAxis.name + ".txt";


			// Append anchor to body.
			document.body.appendChild(a)
			a.click();

			// Remove anchor from body
			document.body.removeChild(a)
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

coonDataApp.controller('hcHeatMapCtrl', function($scope, $http, $timeout,$interval, $rootScope){

	$scope.projectBranches = {};
	$scope.branchConditions = {};
	$scope.branchReplicates = {};
	$scope.selectedValues = {'selected':[], 'all':[]};
	$http({
		method: 'POST',
		url: "queryBranchesFromProject.php",
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
		}).then (function success(response){
			$scope.projectBranches = response.data;
		});

	$scope.minFoldChange=-2;
	$scope.maxFoldChange=2;
	$scope.current_data = [];
	$scope.colorGradient = {};
	$scope.heatMapData = null;
	$scope.cust2 = false;
	$scope.cust3 = false;
	$scope.rgbLow = {};
	$scope.rgbHigh = {};
	$scope.rgbMid = {};
	$scope.minColorTwo= null;
	$scope.clusterCount = 50;
	$scope.conditions = null;
	$scope.clusterLineData = null;
	$scope.clusterKey = null;
	$scope.prevHeatMaps = [];
	$scope.chosenPrevAnalysis = null;
	$scope.clusterMolHighlight = "";

	$scope.goTermsMapped = false;
	$scope.goTermDataAvailable = false;
	$scope.goTermCompat = false;
	$scope.goClusterProgress = 0;
	$scope.heatMapFigLegendText = "";

$scope.linkages = [{id:1,name:'Average'},{id:2,name:'Complete'},{id:3,name:'Single'}];
$scope.distances = [{id:1,name:'Euclidean'},{id:2,name:'Canberra'},{id:3,name:'Cosine'},{id:4,name:'Manhattan'},{id:5,name:'Maximum'},{id:6,name:'Pearson'},{id:7,name:'Spearman'}]

	$scope.colorGradients = [{name:'Custom Two-Color',img:'../thumbnails/custom2.png', id:'custom2', group:'Custom'},
{name:'Custom Three-Color',img:'../thumbnails/custom3.png', id:'custom3', group:'Custom'},
	{name:'cbbrbg',img:'../thumbnails/cbbrbg.png', id:'cbbrbg', group:'Diverging'},
	{name:'bathymetry',img:'../thumbnails/bathymetry.png', id:'bathymetry', group:'Sequential'},
	{name:'autumn',img:'../thumbnails/autumn.png', id:'autumn', group:'Sequential'},
{name:'blackbody',img:'../thumbnails/blackbody.png', id:'blackbody', group:'Sequential'},
{name:'bluepink',img:'../thumbnails/nwk.png', id:'nwk', group:'Diverging'},
{name:'bluered',img:'../thumbnails/bluered.png', id:'bluered', group:'Sequential'},
{name:'bone',img:'../thumbnails/bone.png', id:'bone', group:'Sequential'},
{name:'cbblues',img:'../thumbnails/cbblues.png', id:'cbblues', group:'Sequential'},
{name:'cbbupu',img:'../thumbnails/cbbupu.png', id:'cbbupu', group:'Sequential'},
{name:'cbgreens',img:'../thumbnails/cbgreens.png', id:'cbgreens', group:'Sequential'},
{name:'cbpiyg',img:'../thumbnails/cbpiyg.png', id:'cbpiyg', group:'Diverging'},
{name:'cbPRGn',img:'../thumbnails/cbPRGn.png', id:'cbPRGn', group:'Diverging'},
{name:'cbpuor',img:'../thumbnails/cbpuor.png', id:'cbpuor', group:'Diverging'},
{name:'cbpurd',img:'../thumbnails/cbpurd.png', id:'cbpurd', group:'Sequential'},
{name:'cbrdbu',img:'../thumbnails/cbrdbu.png', id:'cbrdbu', group:'Diverging'},
{name:'cbrdpu',img:'../thumbnails/cbrdpu.png', id:'cbrdpu', group:'Sequential'},
{name:'cbrdyibu',img:'../thumbnails/cbrdyibu.png', id:'cbrdyibu', group:'Diverging'},
{name:'cbrdyign',img:'../thumbnails/cbrdyign.png', id:'cbrdyign', group:'Diverging'},
{name:'cbspectral',img:'../thumbnails/cbspectral.png', id:'cbspectral', group:'Diverging'},
{name:'cbyiorrd',img:'../thumbnails/cbyiorrd.png', id:'cbyiorrd', group:'Sequential'},
{name:'cdom',img:'../thumbnails/cdom.png', id:'cdom', group:'Sequential'},
{name:'chlorophyll',img:'../thumbnails/chlorophyll.png', id:'chlorophyll', group:'Sequential'},
{name:'cool',img:'../thumbnails/cool.png', id:'cool', group:'Sequential'},
{name:'copper',img:'../thumbnails/copper.png', id:'copper', group:'Sequential'},
{name:'cubehelix',img:'../thumbnails/cubehelix.png', id:'cubehelix', group:'Sequential'},
{name:'density',img:'../thumbnails/density.png', id:'density', group:'Sequential'},
{name:'earth',img:'../thumbnails/earth.png', id:'earth', group:'Sequential'},
{name:'electric',img:'../thumbnails/electric.png', id:'electric', group:'Sequential'},
{name:'freesurface-blue',img:'../thumbnails/freesurface-blue.png', id:'freesurface-blue', group:'Sequential'},
{name:'freesurface-red',img:'../thumbnails/freesurface-red.png', id:'freesurface-red', group:'Sequential'},
{name:'greens',img:'../thumbnails/greens.png', id:'greens', group:'Sequential'},
{name:'greys',img:'../thumbnails/greys.png', id:'greys', group:'Sequential'},
{name:'hot',img:'../thumbnails/hot.png', id:'hot', group:'Sequential'},
{name:'hsv',img:'../thumbnails/hsv.png', id:'hsv', group:'Sequential'},
{name:'inferno',img:'../thumbnails/inferno.png', id:'inferno', group:'Sequential'},
{name:'jet',img:'../thumbnails/jet.png', id:'jet', group:'Sequential'},
{name:'magma',img:'../thumbnails/magma.png', id:'magma', group:'Sequential'},
{name:'oxygen',img:'../thumbnails/oxygen.png', id:'oxygen', group:'Sequential'},
{name:'par',img:'../thumbnails/par.png', id:'par', group:'Sequential'},
{name:'phase',img:'../thumbnails/phase.png', id:'phase', group:'Sequential'},
{name:'picnic',img:'../thumbnails/picnic.png', id:'picnic', group:'Diverging'},
{name:'plasma',img:'../thumbnails/plasma.png', id:'plasma', group:'Sequential'},
{name:'portland',img:'../thumbnails/portland.png', id:'portland', group:'Sequential'},
{name:'rainbow-soft',img:'../thumbnails/rainbow-soft.png', id:'rainbow-soft', group:'Sequential'},
{name:'rainbow',img:'../thumbnails/rainbow.png', id:'rainbow', group:'Sequential'},
{name:'rdbu',img:'../thumbnails/rdbu.png', id:'rdbu', group:'Sequential'},
{name:'salinity',img:'../thumbnails/salinity.png', id:'salinity', group:'Sequential'},
{name:'spring',img:'../thumbnails/spring.png', id:'spring', group:'Sequential'},
{name:'summer',img:'../thumbnails/summer.png', id:'summer', group:'Sequential'},
{name:'temperature',img:'../thumbnails/temperature.png', id:'temperature', group:'Sequential'},
{name:'turbidity',img:'../thumbnails/turbidity.png', id:'turbidity', group:'Sequential'},
{name:'velocity-blue',img:'../thumbnails/velocity-blue.png', id:'velocity-blue', group:'Sequential'},
{name:'velocity-green',img:'../thumbnails/velocity-green.png', id:'velocity-green', group:'Sequential'},
{name:'viridis',img:'../thumbnails/viridis.png', id:'viridis', group:'Sequential'},
{name:'warm',img:'../thumbnails/warm.png', id:'warm', group:'Sequential'},
{name:'winter',img:'../thumbnails/winter.png', id:'winter', group:'Sequential'},
{name:'yignbu',img:'../thumbnails/yignbu.png', id:'yignbu', group:'Sequential'},
{name:'yiorrd',img:'../thumbnails/yiorrd.png', id:'yiorrd', group:'Sequential'}];

$scope.colorGradient = {};
$scope.chosenPrevAnalysis = {};

$scope.firstDisplay = true;
$scope.displayInstructions = true;


$scope.groups = ['Custom', 'Diverging', 'Sequential'];

	$scope.groupFind = function(item)
	{
		return item.group;
	}

	$scope.clusterType = function(item)
	{
		return item.type;
	}

	$scope.$on('hcHeatTabClick', function(){
		if($scope.chosenBranch===null || $scope.chosenBranch===undefined)
		{
			$scope.chosenBranch = $scope.projectBranches[0];
			$scope.queryBranchNodes();
			$scope.colorGradient.selected = $scope.colorGradients[16];
		}
		if($scope.displayInstructions)
		{
			setTimeout(function() {
				$.jGrowl("You can perform hierarchical clustering analyses by selecting appropriate inputs and clicking 'Update Heat Map' below. Previously computed analyses will be displayed immediately, other analyses will be made available upon completion. You can also perform GO enrichment on individual clusters inside the 'Gene Ontology' panel.", { sticky: true, theme: 'bg-google', header: 'Hierarchical Clustering' });
				$scope.displayInstructions = false;
			}, 250);
		}
	});

	$scope.queryBranchNodes = function()
	{
		$scope.selectedValues = {'selected':[], 'all':[]};
		//queryConditions -> branchConditions
		$http({
		method: 'POST',
		url: "queryConditions.php", 
		data: $.param({
			bi: $scope.chosenBranch.branch_id
		}),
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
		}).then (function success(response){
			$scope.branchConditions = response.data;
			if ($scope.useConds)
			{
				angular.forEach($scope.branchConditions, function(d){
					var currObj = {id:d.condition_id, name:d.condition_name};
					$scope.selectedValues['selected'].push(currObj);
					$scope.selectedValues['all'].push(currObj);
				});
			}
		});

		//queryReplicates -> branchReplicates
		$http({
		method: 'POST',
		url: "queryReplicatesFromBranch.php",
		data: $.param({
			bi: $scope.chosenBranch.branch_id
		}),
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
		}).then (function success(response){
			$scope.branchReplicates = response.data;
			if(!$scope.useConds)
			{
				angular.forEach($scope.branchReplicates, function(d){
					var currObj = {id:d.replicate_id, name:d.replicate_name};
					$scope.selectedValues['selected'].push(currObj);
					$scope.selectedValues['all'].push(currObj);
				});
			}
		});


		//WORD TO THE WISE
		//YOU CAN GRAB ALL OF THE SELECTED NODES USING THE FOLLOWING COMMAND
		// $('#mySelect :selected')
		//https://github.com/lou/multi-select/issues/175

		$http({
		method: 'POST',
		url: "queryExistingHeatMaps.php",
		data: $.param({
			bi: $scope.chosenBranch.branch_id
		}),
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
		}).then (function success(response){
			$scope.prevHeatMaps = response.data;
			if($scope.prevHeatMaps.length>0)
			{
				$scope.chosenPrevAnalysis.selected = $scope.prevHeatMaps[0];
				$scope.selectedMapChange();
			}
			else
			{
				$scope.rowLink = $scope.linkages[0];
				$scope.colLink = $scope.linkages[0];
				$scope.rowDist = $scope.distances[0];
				$scope.colDist = $scope.distances[0];
			}
		});

		$scope.checkClusteringProgress();
	}

	$scope.$on('afterSelectEvent', function(event, args) {
		$scope.checkForExistingAnalysis();
	});

	$scope.$on('afterDeselectEvent', function(event, args) {
		$scope.checkForExistingAnalysis();
	});

	$scope.clusterModeChange = function()
	{
		$scope.selectedValues = {'selected':[], 'all':[]};
		if ($scope.useConds)
		{
			angular.forEach($scope.branchConditions, function(d){
				var currObj = {id:d.condition_id, name:d.condition_name};
				$scope.selectedValues['selected'].push(currObj);
				$scope.selectedValues['all'].push(currObj);
			});
		}
		else
		{
			angular.forEach($scope.branchReplicates, function(d){
				var currObj = {id:d.replicate_id, name:d.replicate_name};
				$scope.selectedValues['selected'].push(currObj);
				$scope.selectedValues['all'].push(currObj);
			});
		}
	}

	$scope.selectedMapChange = function()
	{
		if($scope.chosenPrevAnalysis.selected!==undefined)
		{
			//Update all drowdown fields
			var currRowDist = $scope.distances.filter(function(d){return d.id===parseInt($scope.chosenPrevAnalysis.selected.rowDist);});
			currRowDist.length===1 ? $scope.rowDist = currRowDist[0] : null;
			var currRowLink = $scope.linkages.filter(function(d){return d.id===parseInt($scope.chosenPrevAnalysis.selected.rowLink);});
			currRowLink.length===1 ? $scope.rowLink = currRowLink[0] : null;
			var currColDist = $scope.distances.filter(function(d){return d.id===parseInt($scope.chosenPrevAnalysis.selected.colDist);});
			currColDist.length===1 ? $scope.colDist = currColDist[0] : null;
			var currColLink = $scope.linkages.filter(function(d){return d.id===parseInt($scope.chosenPrevAnalysis.selected.colLink);});
			currColLink.length===1 ? $scope.colLink = currColLink[0] : null;

			//Update use conditions/use replicates
			var currUseConds = parseInt($scope.chosenPrevAnalysis.selected.useConds);
			$scope.useConds = currUseConds===1;
			//var currExNodes = JSON.parse($scope.chosenPrevAnalysis.selected.exNodes);
			var currIncNodes = JSON.parse($scope.chosenPrevAnalysis.selected.incNodes);

			//Update 
			$scope.selectedValues = {'selected':[], 'all':[]};
			if ($scope.useConds)
			{
				$('.excludeHeader').html("Excluded Conditions");
				$('.includeHeader').html("Included Conditions");
				angular.forEach($scope.branchConditions, function(d){
					var currObj = {id:d.condition_id, name:d.condition_name};
					if (currIncNodes[d.condition_id]!==undefined)
					{
						$scope.selectedValues['selected'].push(currObj);
						$scope.selectedValues['all'].push(currObj);
					}
					else
					{
						$scope.selectedValues['all'].push(currObj);
					}
				});
			}
			else
			{
				$('.excludeHeader').html("Excluded Replicates");
				$('.includeHeader').html("Included Replicates");
				angular.forEach($scope.branchReplicates, function(d){
					var currObj = {id:d.replicate_id, name:d.replicate_name};
					if (currIncNodes[d.replicate_id]!==undefined)
					{
						$scope.selectedValues['selected'].push(currObj);
						$scope.selectedValues['all'].push(currObj);
					}
					else
					{
						$scope.selectedValues['all'].push(currObj);
					}
				});
			}
			$timeout(function() {
				$scope.checkForExistingAnalysis();
			}, 50);
		}
	}


	$scope.generateHeatMap = function()
	{
		var custom = 1;
		var minC = "";
		var maxC = "";
		var midC = "";
		$scope.heatMapData = null;

		$scope.colorGradient.selected.id==="custom2" ? custom=2 : null;
		$scope.colorGradient.selected.id==="custom3" ? custom=3 : null;
		if ($scope.colorGradient.selected.id==="custom2")
		{
			custom = 2; minC = angular.element(document.getElementById('colorpicker-tl-2-low'))[0].value; maxC = angular.element(document.getElementById('colorpicker-tl-2-high'))[0].value;
		}
		if ($scope.colorGradient.selected.id==="custom3")
		{
			custom = 3; minC = angular.element(document.getElementById('colorpicker-tl-3-low'))[0].value; 
			maxC = angular.element(document.getElementById('colorpicker-tl-3-high'))[0].value; midC = angular.element(document.getElementById('colorpicker-tl-3-mid'))[0].value;
		}
		var excludedNodes = [];

		 $('#mySelect :not(:selected)').each(function(i, selected){
		 	if($(selected)[0].nodeName==="OPTION")
		 	{
		 		excludedNodes.push($(selected)[0].value);
		 	}
		 });

		$timeout(function() {$http({
		method: 'POST',
		url: "queryHeatMap.php",
		data: $.param({
			cg: $scope.colorGradient.selected.id, min:$scope.minFoldChange, max:$scope.maxFoldChange, cust:custom, minC: minC, midC: midC, maxC: maxC, 
			cc: $scope.clusterCount, rd:$scope.rowDist.id, rl:$scope.rowLink.id, cd:$scope.colDist.id, cl:$scope.colLink.id, uc:$scope.useConds, ex:JSON.stringify(excludedNodes),
			bi: $scope.chosenBranch.branch_id
		}),
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
		}).then (function success(response){
			$scope.checkClusteringProgress();
			if (response.data.hasData)
			{
				rowClusters = angular.fromJson(response.data.rowClusters);
				columnClusters = angular.fromJson(response.data.columnClusters);
				$scope.conditions = response.data.columnNameOrderArray;
				$scope.heatMapData = response.data;
				$scope.clusterLineData = null;
				$scope.clusterMolCount= 0;
				$scope.clusterKey=null;
				$scope.heatMapFigLegendText = response.data.figLegend;
			}
			else
			{
				alert(response.data.message);
			}
			$scope.checkGoStatus();
		});}, 10);
	}

	$scope.prevAnalysisAvailable = false;

	$scope.checkForExistingAnalysis = function()
	{
		if($scope.heatMapSettingsForm.$valid)
		{
			var excludedNodes = [];
			 $('#mySelect :not(:selected)').each(function(i, selected){
			 	if($(selected)[0].nodeName==="OPTION")
			 	{
			 		excludedNodes.push($(selected)[0].value);
			 	}
			 });
			$http({
			method: 'POST',
			url: "checkForExistingHeatMap.php",
			data: $.param({
				rd:$scope.rowDist.id, rl:$scope.rowLink.id, cd:$scope.colDist.id, cl:$scope.colLink.id, uc:$scope.useConds, ex:JSON.stringify(excludedNodes), bi: $scope.chosenBranch.branch_id
			}),
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			}).then (function success(response){
				$scope.prevAnalysisAvailable = response.data==="1";
				if($scope.prevAnalysisAvailable && $scope.firstDisplay)
				{
					$scope.firstDisplay = false;
					$scope.generateHeatMap();
				}
			});		
		}
	}

	var checkClusteringProgress;
	$scope.clusterProgressData = [];
	$scope.monitorClusterProgress = false;
	var completeMaps = 0;

	//Need to update the logic in this function
	//Need to store processing maps somewhere
	$scope.checkClusteringProgress = function()
	{
		if (angular.isDefined(checkClusteringProgress)){ return; }
		checkClusteringProgress = $interval(function(){
			$http({
			method: 'POST',
			url: "queryClusteringProgress.php",
			data: $.param({
				bi:$scope.chosenBranch.branch_id
			}),
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			}).then (function success(response){
				var monitorContinue = false;
				$scope.clusterProgressData = response.data;
				var tmpComplete = 0;
				angular.forEach(response.data, function(d){
					//$scope.clusterProgressData[d.analysis_id.toString()+"_id"] = d;
					d.progress!==100 && d.progress!=="100" ? monitorContinue = true : null;
					d.progress===100 || d.progress==="100" ? tmpComplete++ : null;
				});
				if(completeMaps!==tmpComplete)
				{
					completeMaps = tmpComplete;
					$http({
						method: 'POST',
						url: "queryExistingHeatMaps.php",
						data: $.param({
							bi: $scope.chosenBranch.branch_id
						}),
						headers: {'Content-Type': 'application/x-www-form-urlencoded'}
						}).then (function success(response){
							$scope.prevHeatMaps = response.data;
						});
				} 
				if(!monitorContinue)
				{
					$scope.stopCheckClusteringProgress();
					$scope.monitorClusterProgress = false;
					$http({
						method: 'POST',
						url: "queryExistingHeatMaps.php",
						data: $.param({
							bi: $scope.chosenBranch.branch_id
						}),
						headers: {'Content-Type': 'application/x-www-form-urlencoded'}
						}).then (function success(response){
							$scope.prevHeatMaps = response.data;
						});
				}	
				else
				{
					$scope.monitorClusterProgress = true;
				}
				//$scope.stopCheckClusteringProgress();
			});
		}, 2000);
	}

	$scope.stopCheckClusteringProgress = function()
	{
		$scope.checkClusteringProgress();
		$interval.cancel(checkClusteringProgress);
		checkClusteringProgress = undefined;
	}

	$scope.checkGoStatus = function()
	{
		$scope.goTermsMapped = false;
		$scope.goTermDataAvailable = false;
		var currAnalysisID = -1;
		if ($scope.heatMapData!==null)
		{
			currAnalysisID = $scope.heatMapData.analysis_id;
		}
		$http({
		method: 'POST',
		url: "queryHeatMapGOStatus.php",
		data: $.param({
			bi: $scope.chosenBranch.branch_id,
			ai:currAnalysisID,
			cc:$scope.clusterCount
		}),
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
		}).then (function success(response){
			$scope.goTermsMapped = response.data.mapped;
			$scope.goTermDataAvailable = response.data.available;
			if ($scope.goTermDataAvailable)
			{
				$scope.goClusterProgress = 100;
				//go and fetch these results
			}
			else
			{
				$scope.goClusterProgress = 0;
				$scope.goTermCompat = $scope.goTermsMapped;
			}
		});
	}

	$scope.startClusterGOAnalysis = function()
	{
		$http({
		method: 'POST',
		url: "startGOClusterAnalysis.php",
		data: $.param({
			bi: $scope.chosenBranch.branch_id,
			ai:$scope.heatMapData.analysis_id,
			nc:$scope.clusterCount
		}),
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
		}).then (function success(response){
			//console.log(response);
			alert(response.data);
			//set up monitoring of go progress here. also it would be desirable to incorporate the modal windows...
			$scope.checkGOProgress();
		});
	}

	var checkGOProgress;

	$scope.checkGOProgress = function()
	{
		if (angular.isDefined(checkGOProgress)){ return; }
		checkGOProgress = $interval(function(){
			$http({
			method: 'POST',
			url: "queryGOClusterProgress.php",
			data: $.param({
				ai:$scope.heatMapData.analysis_id
			}),
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			}).then (function success(response){
				//console.log(response);
				if (response.data==="-1" || response.data===-1)
				{
					$scope.stopCheckGOProgress();
				}
				else
				{
					$scope.goClusterProgress = response.data;
					$scope.goClusterProgress==="100" || $scope.goClusterProgress===100 ? $scope.stopCheckGOProgress() : null;
				}
			});
		}, 1000);
	}

	$scope.stopCheckGOProgress = function()
	{
		$scope.checkGoStatus();
		$interval.cancel(checkGOProgress);
		checkGOProgress = undefined;
	}

	$scope.updateHeatMap = function()
	{
		var custom = 1;
		var minC = "";
		var maxC = "";
		var midC = "";

		$scope.colorGradient.selected.id==="custom2" ? custom=2 : null;
		$scope.colorGradient.selected.id==="custom3" ? custom=3 : null;
		if ($scope.colorGradient.selected.id==="custom2")
		{
			custom = 2; minC = angular.element(document.getElementById('colorpicker-tl-2-low'))[0].value; maxC = angular.element(document.getElementById('colorpicker-tl-2-high'))[0].value;
		}
		if ($scope.colorGradient.selected.id==="custom3")
		{
			custom = 3; minC = angular.element(document.getElementById('colorpicker-tl-3-low'))[0].value; 
			maxC = angular.element(document.getElementById('colorpicker-tl-3-high'))[0].value; midC = angular.element(document.getElementById('colorpicker-tl-3-mid'))[0].value;
		}

		$timeout(function() {$http({
		method: 'POST',
		url: "queryHeatMap.php",
		data: $.param({
			cg: $scope.colorGradient.selected.id, min:$scope.minFoldChange, max:$scope.maxFoldChange, cust:custom, minC: minC, midC: midC, maxC: maxC, cc: $scope.clusterCount
		}),
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
		}).then (function success(response){
			$scope.heatMapData = response.data;
			$scope.conditions = response.data.columnNameOrderArray;
		});}, 10);
		
	}

	$scope.$watch('clusterLineData', function() { 
		if ($scope.clusterLineData!==null)
		{
			var clusterMolecules = Object.keys($scope.clusterLineData);
			var profileTableData = [];
			
			angular.forEach(clusterMolecules, function(d){
				profileTableData.push({molName:d});
			});

			$scope.clusterMolCount = clusterMolecules.length;

			$('#clusterProfTable').bootstrapTable('load',profileTableData);
				$('#clusterProfTable').bootstrapTable('resetView');
				$('#clusterProfTable').bootstrapTable('refreshOptions', {
					pagination: false,
					search: true
				});
				$('#clusterProfTable').bootstrapTable('refresh');
			}
	});

	$scope.clusterEnriched = [];
	$scope.clusterEnrichSelected = [];

	$scope.$watch('clusterKey', function(){
		if($scope.clusterKey!==null && $scope.clusterKey!==undefined)
		{
			$http({
				method: 'POST',
				url: "queryClusterGOEnrichments.php",
				data: $.param({
					ai:$scope.heatMapData.analysis_id,
					bi:$scope.heatMapData.branch,
					ci:$scope.heatMapData.clusterIDs[$scope.clusterKey],
					nc:$scope.heatMapData.numClusters
				}),
				headers: {'Content-Type': 'application/x-www-form-urlencoded'}
				}).then (function success(response){
					$scope.clusterEnriched = [];
					$scope.clusterEnrichSelected = []; 
					angular.forEach(response.data, function(d){
						$scope.clusterEnriched.push({goTerm:d.term_name, extID:d.term_external, namespace:d.term_namespace, pValue:d.p, pValueFDR:d.pFDR, pValueBonf:d.pBonferroni, matched:d.a + " of " + (d.a + d.b), enrich:((d.a/(d.b+d.a))/(d.c/(d.d+d.a))).toFixed(6)});
						$scope.clusterEnrichSelected[d.term_external] = [];
						angular.forEach(d.molList, function(e){
							var split = e.split("_");
							split[1]==="T" ? $scope.clusterEnrichSelected[d.term_external].push({molName:moleculeDict[split[0]].name,hasTerm:" ● TRUE"}) : $scope.clusterEnrichSelected[d.term_external].push({molName:moleculeDict[split[0]].name,hasTerm:" ○ FALSE"});
						});
					});
					$('#clusterGOTableOne').bootstrapTable('load',$scope.clusterEnriched);
					$('#clusterGOTableOne').bootstrapTable('resetView');
					$('#clusterGOTableOne').bootstrapTable('refreshOptions', {
						pagination: false,
						search: true,
						
					});
					$('#clusterGOTableOne').bootstrapTable('refresh');
					$('#clusterGOTableOne').bootstrapTable('refresh');
					$('#clusterGOTableTwo').bootstrapTable('load',[]);
					$('#clusterGOTableTwo').bootstrapTable('resetView');
					$('#clusterGOTableTwo').bootstrapTable('refreshOptions', {
						pagination: false,
						search: true,
					});
					$('#clusterGOTableTwo').bootstrapTable('refresh');
				});
		}
		else
		{
			$scope.clusterEnriched = [];
			$scope.clusterEnrichSelected = [];
			$('#clusterGOTableOne').bootstrapTable('load',$scope.clusterEnriched);
			$('#clusterGOTableOne').bootstrapTable('resetView');
			$('#clusterGOTableOne').bootstrapTable('refreshOptions', {
				pagination: false,
				search: true,
			});
			$('#clusterGOTableOne').bootstrapTable('refresh');
			$('#clusterGOTableTwo').bootstrapTable('load',[]);
			$('#clusterGOTableTwo').bootstrapTable('resetView');
			$('#clusterGOTableTwo').bootstrapTable('refreshOptions', {
				pagination: false,
				search: true,
			});
			$('#clusterGOTableTwo').bootstrapTable('refresh');
		}
	});

	$('#clusterGOTableOne').bootstrapTable().on('click-row.bs.table', function (e, row, $element) {
		$('#clusterGOTableOne tr.success').removeClass("success");
		$element.attr("class", "success");
		$('#clusterGOTableTwo').bootstrapTable('load',$scope.clusterEnrichSelected[row.extID]);
			$('#clusterGOTableTwo').bootstrapTable('resetView');
			$('#clusterGOTableTwo').bootstrapTable('refreshOptions', {
				pagination: false,
				search: true,
			});
			$('#clusterGOTableTwo').bootstrapTable('refresh');
	});	


	$('#clusterProfTable').bootstrapTable().on('click-row.bs.table', function (e, row, $element) {
	$('#clusterProfTable tr.success').removeClass("success");
	$element.attr("class", "success");
		$('#clusterLineHighlight').attr("stroke", "darkgray").attr("stroke-width", "1px").attr("id", "");
		$('.' + row.molName.replace(";","_")).attr("stroke", "dodgerblue").attr("stroke-width", "4px").attr("id", "clusterLineHighlight");
		$('.' + row.molName.replace(";","_")).each(function(d){
			d3.select(this).moveToFront();
		});
		
		$scope.$apply(function(){
	        $scope.clusterMolHighlight = row.molName;
	      });

	});

	$scope.deleteAllClusterAnalyses = function()
	{
		$scope.chosenPrevAnalysis = {};
		$scope.heatMapData = {};
		$http({
		method: 'POST',
		url: "deleteAllHeatMaps.php",
		data: $.param({
			bi: $scope.chosenBranch.branch_id
		}),
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
		}).then (function success(response){
			$rootScope.$broadcast('deleteHeatMap', null);
			$scope.checkForExistingAnalysis();
			alert("All hierarchical clustering analyses from the branch '" + $scope.chosenBranch.branch_name + "' have been removed from our server.");
			$http({
				method: 'POST',
				url: "queryExistingHeatMaps.php",
				data: $.param({
					bi: $scope.chosenBranch.branch_id
				}),
				headers: {'Content-Type': 'application/x-www-form-urlencoded'}
				}).then (function success(response){
					$scope.prevHeatMaps = response.data;
				});
		});
	}

	$scope.deleteCurrentClusterAnalysis = function()
	{
		//pass branch id
		$http({
		method: 'POST',
		url: "deleteCurrentHeatMap.php",
		data: $.param({
			ai: $scope.heatMapData.analysis_id
		}),
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
		}).then (function success(response){
			$scope.chosenPrevAnalysis = {};
			$scope.heatMapData = null;
			$rootScope.$broadcast('deleteHeatMap', null);
			alert("Your currently selected hierarchical clustering analysis has been removed from our server.");
				$http({
					method: 'POST',
					url: "queryExistingHeatMaps.php",
					data: $.param({
						bi: $scope.chosenBranch.branch_id
					}),
					headers: {'Content-Type': 'application/x-www-form-urlencoded'}
					}).then (function success(response){
						$scope.prevHeatMaps = response.data;
						$scope.checkForExistingAnalysis();
						if($scope.prevHeatMaps.length>0)
						{
							$scope.chosenPrevAnalysis.selected = $scope.prevHeatMaps[0];
							$scope.selectedMapChange();
						}
					});
		});
	}

	$scope.downloadHeatMapSVG = function()
	{
		$timeout(function() {
		if($scope.heatMapData!==null)
		{
				var config = {filename: 'HierarchicalClusteringHeatMap_AnalysisID_' + $scope.heatMapData.analysis_id}
				d3_save_svg.save($('#heatMapSVG')[0], config);
				chart_fullVolcano.selectAll(".clusterRect")[0].forEach(function(d){
					d.style.fill = "";
					d.style.length = "";
					d.style.overflowX = "";
					d.style.overflowY = "";
					d.style.perspectiveOrigin="";
					d.style.transformOrigin="";
					d.style.x = "";
					d.style.y = "";
					d.style.transform="";
				});
				chart_fullVolcano.selectAll(".errorBar")[0].forEach(function(d){
					d.style.overflowY = "";
					d.style.overflowX = "";
					d.style.perspectiveOrigin = "";
					d.style.transformOrigin = "";
					d.style.fill = "";
					d.style.shapeRendering = "";
					d.style.stroke = "";
					d.style.d = "";
					d.style.transform = "";
				});
				chart_fullVolcano.selectAll('.heatmap')[0].forEach(function(d){
					d.style.imageRendering="";
					d.style.perspectiveOrigin = "";
					d.style.transformOrigin = "";
					d.style.transform="";
					d.style.x = "";
					d.style.y = "";
				});
		}
	},100);
	}

	$scope.downloadHeatMapCSV = function()
	{
		if($scope.heatMapData!==null)
		{
			//where to get data from
			var tmpDataArray = [];
			for (var i = 0; i < Object.keys($scope.heatMapData.rowNameOrderArray).length; i++)
			{
				tmpDataArray[i] = [];
			}
			angular.forEach($scope.heatMapData.dataArray, function(val, key)
			{
				var keyParts = key.split('_');
				tmpDataArray[parseInt(keyParts[1])][parseInt(keyParts[0])] = val;
			});
			var csvRows = [];
			csvRows.push($('#figLegend').text() + "\n\n");
			var header ="\t";
			angular.forEach($scope.heatMapData.columnNameOrderArray, function(d){
				header += d + "\t";
			});
			header += "Cluster\n";
			csvRows.push(header);
			var clusterIndex = Object.keys($scope.heatMapData.clusterRanges).length - 1;
			var clusterRange = $scope.heatMapData.clusterRanges[clusterIndex].split('_');
			var clusterMin = Object.keys($scope.heatMapData.rowNameOrderArray).length - clusterRange[0]+1;
			var clusterMax = Object.keys($scope.heatMapData.rowNameOrderArray).length - clusterRange[1]+1;

			for (var i = 0; i <  Object.keys($scope.heatMapData.rowNameOrderArray).length; i++)
			{
				var currLine = $scope.heatMapData.rowNameOrderArray[i] + "\t";
				for (var j = 0; j < tmpDataArray[i].length; j++)
				{
					currLine += tmpDataArray[i][j] + "\t";
				}
				currLine += "Cluster " + (clusterIndex+1);
				currLine += "\n";
				csvRows.push(currLine);

				if ( (i+1)===clusterMin)
				{
					clusterIndex--; 
					if (clusterIndex > 0)
					{
						clusterRange = $scope.heatMapData.clusterRanges[clusterIndex].split('_');

						clusterMin =  Object.keys($scope.heatMapData.rowNameOrderArray).length - clusterRange[0]+1;
						clusterMax = Object.keys($scope.heatMapData.rowNameOrderArray).length - clusterRange[1]+1;
					}
				}
			}
			var csvString = csvRows.join("");

			var a = window.document.createElement('a');
			a.href = window.URL.createObjectURL(new Blob([csvString], {type: 'text/csv'}));
			a.download = "HeatMapData_AnalysisID_" + $scope.heatMapData.analysis_id + ".txt";;

			// Append anchor to body.
			document.body.appendChild(a)
			a.click();

			// Remove anchor from body
			document.body.removeChild(a)

		}
	}

	$scope.downloadClusterPlotSVG = function()
	{
		if($scope.clusterLineData!==null)
		{
			var config = { filename: 'ProfilePlot_Cluster_' + $scope.heatMapData.clusterIDs[$scope.clusterKey]}
			d3_save_svg.save($('#clusterLinePlot')[0], config);
			chart_clusterLinePlot.selectAll(".clusterLine")[0].forEach(function(d)
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
			
			chart_clusterLinePlot.selectAll("g.x.axis")[0].forEach(function(d)
			{
				d.style.overflowX = "";
				d.style.overflowY ="";
				d.style.perspectiveOrigin="";
				d.style.transformOrigin="";
				d.style.shapeRendering = "";
			});
			chart_clusterLinePlot.selectAll("g.y.axis")[0].forEach(function(d)
			{
				d.style.overflowX = "";
				d.style.overflowY ="";
				d.style.perspectiveOrigin="";
				d.style.transformOrigin="";
				d.style.shapeRendering = "";
			});
			chart_clusterLinePlot.selectAll(".domain")[0].forEach(function(d)
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
	}

	$scope.downloadClusterPlotCSV = function()
	{
		if($scope.clusterLineData!==null)
		{
			var csvRows = [];
			var header = "\t";
			angular.forEach($scope.conditions, function(d){
				header += d + "\t";
			});
			header = header.substring(0, header.length-1);
			header+= "\n";
			csvRows.push(header);
			var mols = Object.keys($scope.clusterLineData);
			angular.forEach(mols, function(d)
			{
				var currLine = d + "\t";
				angular.forEach($scope.clusterLineData[d], function(e){
					currLine += e + "\t";
				});
				currLine = currLine.substring(0, currLine.length-1);
				currLine += "\n";
				csvRows.push(currLine);
			});
			var csvString = csvRows.join("");
			var a = document.createElement('a');
			a.href        = 'data:attachment/csv,' +  encodeURIComponent(csvString);
			a.target      = '_blank';
			a.download    = "ProfilePlotData_Cluster_"+  $scope.heatMapData.clusterIDs[$scope.clusterKey] + " .txt";

			document.body.appendChild(a);
			a.click();
		}
	}

	$scope.gradChange = function()
	{
		$scope.cust2 = $scope.colorGradient.selected.id==="custom2";
		$scope.cust3 = $scope.colorGradient.selected.id==="custom3";
	}

		$scope.hexToRgb =function(hex) {
		    var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
		    return result ? {
		        r: parseInt(result[1], 16),
		        g: parseInt(result[2], 16),
		        b: parseInt(result[3], 16)
		    } : null;
		}


	$scope.displayLabels = false;
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
  }
});


coonDataApp.directive('lowerThan', [
  function() {

    var link = function($scope, $element, $attrs, ctrl) {

      var validate = function(viewValue) {
        var comparisonModel = $attrs.lowerThan;

        if(!viewValue || !comparisonModel){
          // It's valid because we have nothing to compare against
          ctrl.$setValidity('lowerThan', true);
        }

        // It's valid if model is lower than the model we're comparing against
        ctrl.$setValidity('lowerThan', parseFloat(viewValue) < parseFloat(comparisonModel) );
        return viewValue;
      };

      ctrl.$parsers.unshift(validate);
      ctrl.$formatters.push(validate);

      $attrs.$observe('lowerThan', function(comparisonModel){
        // Whenever the comparison model changes we'll re-validate
        return validate(ctrl.$viewValue);
      });

    };

    return {
      require: 'ngModel',
      link: link
    };

  }
]);

coonDataApp
.controller('outlierCtrl', function($scope, $http){
	$scope.title = "outlierCtrl";
	$scope.algorithms = [{name:"Single Measurement", value:"SINGLE"}, {name:"Y3K uGPS", value:"UGPS"}];
	$scope.outlier_branch_data = {};
	$scope.outlierBranch = null;
	$scope.algorithm = null;
	$scope.outlierData = {};
	$scope.testingCorrection = "uncorrected";
	$scope.pValueCutoff = 0.05;
	$scope.foldChangeCutoff = 0;
	$scope.quantData = {};
	$scope.selectedMolecule = "Molecule";
	$scope.maxCondition = "";
	$scope.displayMolecule = "";
	$scope.displayCondition = "";
	$scope.featureMetadataTerms = [];
	$scope.moleculeSeekTerms = ["Molecule identifier"];
	$scope.volcanoShortenLongTerms = true;
	$scope.outlierMoleculeTerm = $scope.moleculeSeekTerms[0];
	$scope.displayInstructions = true;
	
	$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
	$http({
		method: 'POST',
		url: "queryBranchesFromProject.php",
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	}).then (function success(response){
		$scope.outlier_branch_data = (response.data);
		//$scope.algorithm = $scope.algorithms[0];
	});

	$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
	$http({
		method: 'POST',
		url: "queryFeatureMetadataTerms.php",
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	}).then (function success(response){
		angular.forEach(response.data, function(d){
			$scope.featureMetadataTerms.push({"name": d, "selected": true}); $scope.moleculeSeekTerms.push(d);});
		
	});

	$scope.$on('outlierTabClick', function(){
		if($scope.outlierBranch===null && $scope.algorithm===null)
		{
			$scope.outlierBranch = $scope.outlier_branch_data[0];
			$scope.algorithm = $scope.algorithms[1];
			$scope.queryOutlierData();
		}
		if($scope.displayInstructions)
		{
			$.jGrowl("You can explore characteristic molecular outliers in the dataset by clicking individual rows in the table below.", { sticky: true, theme: 'bg-google', header: 'Outlier Analysis' });
			$scope.displayInstructions = false;
		}
	});

	$scope.branchChanged = function()
	{
		$scope.queryOutlierData();
	}
	
	$scope.algorithmChanged = function()
	{
		$scope.queryOutlierData();
	}

	$scope.moleculeNameChanged = function()
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
					var molName = d.unique_identifier_text;
					if ($scope.outlierMoleculeTerm!=="Molecule identifier")
					{
						var moleculeEntry = moleculeDict[d.unique_identifier_id];
						if (moleculeEntry != undefined)
						{
							moleculeEntry.metadata.forEach(function(e){
								if (e.name===$scope.outlierMoleculeTerm)
								{
									molName=e.text;
								}
							});
						}
					}
					tableData.push({molName: molName, regulation: d.regulation, condName: d.condition_name, distance: d.distance.toFixed(6), foldChange: d.fold_change_control_norm.toFixed(6), pValue: d.p_value.toExponential(4), molecule_id:d.unique_identifier_id, max_cond:d.max_regulated_condition_id});
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
	$scope.displayMolecule =  row.molName;
	$scope.displayCondition =  row.condName;
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