<?php
   require("config.php");
   
   ob_end_clean();
   if (empty($_SESSION['user'])) {
     header("Location: index.php");
     die("Redirecting to index.php");
   }
   ?>
<!DOCTYPE html>
<style>
   /* Loading Spinner */
   .spinner{margin:0;width:70px;height:18px;margin:-35px 0 0 -9px;position:absolute;top:50%;left:50%;text-align:center}.spinner > div{width:18px;height:18px;background-color:#333;border-radius:100%;display:inline-block;-webkit-animation:bouncedelay 1.4s infinite ease-in-out;animation:bouncedelay 1.4s infinite ease-in-out;-webkit-animation-fill-mode:both;animation-fill-mode:both}.spinner .bounce1{-webkit-animation-delay:-.32s;animation-delay:-.32s}.spinner .bounce2{-webkit-animation-delay:-.16s;animation-delay:-.16s}@-webkit-keyframes bouncedelay{0%,80%,100%{-webkit-transform:scale(0.0)}40%{-webkit-transform:scale(1.0)}}@keyframes bouncedelay{0%,80%,100%{transform:scale(0.0);-webkit-transform:scale(0.0)}40%{transform:scale(1.0);-webkit-transform:scale(1.0)}}
</style>
<meta charset="UTF-8">
<!--[if IE]>
<meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'>
<![endif]-->
<title> H3K proteomics data </title>
<meta name="description" content="">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.0/angular.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.0/angular-sanitize.js"></script>
<script type="text/javascript" src="select.js"></script>
<link rel="stylesheet" href="https://netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.css">
<link rel="stylesheet" href="bootstrap-table.css">
<script src="jquery.min.js"></script>
<link rel="stylesheet" type="text/css" href="../assets/widgets/multi-select/multiselect.css">
<script type="text/javascript" src="../assets/widgets/multi-select/multiselect.js"></script>
<script src="d3.min.js"></script> 
<script src="d3tip.js"></script>
<script src="d3-save-svg.min.js"></script>
<script src="jq-multi-select.js"></script>
<script src="coonDataApp.js"></script>
<script src="spin.min.js"></script>
<!--
   <script src="scripts/services/d3.js"></script>
   <script src="scripts/services/d3Tip.js"></script> -->
<script src="globalCalls.js"></script>
<script src="d3Basic.js"></script>
<script src="fullVolcano.js"></script>
<script src="pcaCondition.js"></script>
<script src="corrScatter.js"></script>
<script src="moleculeBarChart.js"></script>
<script src="pcaReplicate.js"></script>
<script src="outlierVolcano.js"></script>
<script src="goVolcano.js"></script>
<script src="hcHeatMap.js"></script>
<script src="clusterLinePlot.js"></script>
<!-- Favicons -->
<link rel="apple-touch-icon-precomposed" sizes="144x144" href="../assets/images/icons/apple-touch-icon-144-precomposed.png">
<link rel="apple-touch-icon-precomposed" sizes="114x114" href="../assets/images/icons/apple-touch-icon-114-precomposed.png">
<link rel="apple-touch-icon-precomposed" sizes="72x72" href="../assets/images/icons/apple-touch-icon-72-precomposed.png">
<link rel="apple-touch-icon-precomposed" href="../assets/images/icons/apple-touch-icon-57-precomposed.png">
<link rel="shortcut icon" href="../assets/images/icons/favicon.png">
<!-- HELPERS -->
<link rel="stylesheet" type="text/css" href="../assets/helpers/animate.css">
<link rel="stylesheet" type="text/css" href="../assets/helpers/backgrounds.css">
<link rel="stylesheet" type="text/css" href="../assets/helpers/boilerplate.css">
<link rel="stylesheet" type="text/css" href="../assets/helpers/grid.css">
<link rel="stylesheet" type="text/css" href="../assets/helpers/spacing.css">
<link rel="stylesheet" type="text/css" href="../assets/helpers/typography.css">
<link rel="stylesheet" type="text/css" href="../assets/helpers/utils.css">
<link rel="stylesheet" type="text/css" href="../assets/helpers/colors.css">
<!-- ELEMENTS -->
<link rel="stylesheet" type="text/css" href="../assets/elements/buttons.css">
<link rel="stylesheet" type="text/css" href="../assets/elements/content-box.css">
<link rel="stylesheet" type="text/css" href="../assets/elements/forms.css">
<link rel="stylesheet" type="text/css" href="../assets/elements/menus.css">
<link rel="stylesheet" type="text/css" href="../assets/elements/tables.css">
<link rel="stylesheet" type="text/css" href="../assets/elements/tile-box.css">
<!-- FRONTEND ELEMENTS -->
<link rel="stylesheet" type="text/css" href="../assets/frontend-elements/footer.css">
<link rel="stylesheet" type="text/css" href="../assets/frontend-elements/hero-box.css">
<!-- ICONS -->
<link rel="stylesheet" type="text/css" href="../assets/icons/fontawesome/fontawesome.css">
<!-- WIDGETS -->
<link rel="stylesheet" type="text/css" href="../assets/widgets/dropdown/dropdown.css">
<link rel="stylesheet" type="text/css" href="../assets/widgets/input-switch/inputswitch.css">
<link rel="stylesheet" type="text/css" href="../assets/widgets/input-switch/inputswitch-alt.css">
<link rel="stylesheet" type="text/css" href="../assets/widgets/modal/modal.css">
<link rel="stylesheet" type="text/css" href="../assets/widgets/theme-switcher/themeswitcher.css">
<link rel="stylesheet" type="text/css" href="../assets/widgets/progressbar/progressbar.css">
<link rel="stylesheet" type="text/css" href="../assets/widgets/colorpicker/colorpicker.css">
<link rel="stylesheet" type="text/css" href="../assets/widgets/jgrowl-notifications/jgrowl.css">
<!-- FRONTEND WIDGETS -->
<link rel="stylesheet" type="text/css" href="../assets/widgets/layerslider/layerslider.css">
<link rel="stylesheet" type="text/css" href="../assets/widgets/owlcarousel/owlcarousel.css">
<link rel="stylesheet" type="text/css" href="../assets/widgets/fullpage/fullpage.css">
<!-- Frontend theme -->
<link rel="stylesheet" type="text/css" href="../assets/themes/frontend/layout.css">
<link rel="stylesheet" type="text/css" href="../assets/themes/frontend/color-schemes/default.css">
<!-- Components theme -->
<link rel="stylesheet" type="text/css" href="../assets/themes/components/default.css">
<link rel="stylesheet" type="text/css" href="../assets/themes/components/border-radius.css">
<!-- Frontend responsive -->
<link rel="stylesheet" type="text/css" href="../assets/helpers/frontend-responsive.css">
<!-- JS Core -->
<link rel="stylesheet" href="select.css">
<script type="text/javascript" src="../assets/js-core/jquery-ui-widget.js"></script>
<style>
   .axis path,
   .axis line {
   fill: none;
   stroke: #000;
   shape-rendering: crispEdges;
   }
   .node {
   cursor: pointer;
   }
   .node circle {
   fill: #fff;
   stroke-width: 1.5px;
   }
   .link {
   fill: none;
   stroke: #ccc;
   stroke-width: 1.5px;
   }
   .d3-tip {
   font-family: "Helvetica Neue", Helvetica, sans-serif;
   line-height: 1;
   font-weight: bold;
   padding: 12px;
   background: rgba(0, 0, 0, 0.8);
   color: #fff;
   border-radius: 2px;
   }
   /* Creates a small triangle extender for the tooltip */
   .d3-tip:after {
   box-sizing: border-box;
   display: inline;
   font-size: 10px;
   width: 100%;
   line-height: 1;
   color: rgba(0, 0, 0, 0.8);
   content: "\25BC";
   position: absolute;
   text-align: center;
   }
   /* Style northward tooltips differently */
   .d3-tip.n:after {
   margin: -1px 0 0 0;
   top: 100%;
   left: 0;
   }
   .select2 > .select2-choice.ui-select-match {
   /* Because of the inclusion of Bootstrap */
   height: 29px;
   }
   .selectize-control > .selectize-dropdown {
   top: 36px;
   }
   .ui-select-toggle
   {
   /*height:48px;*/
   }
   .errorBar{
   stroke: "black";
   stroke-width: 1px;
   shape-rendering: crispEdges;
   stroke-linecap:"butt";
   fill:"none";
   stroke-linejoin:"miter";
   }
   td {
   word-break: break-word;
   }
   .ui-select-bootstrap > .ui-select-match > .btn{
   line-height: 20px;
   }
   .ui-select-search{
   padding: 0px 0px;
   text-indent: 10px;
   }
</style>
<script type="text/javascript">
   var treeDisplayed = false;
   $(window).load(function(){
     setTimeout(function() {
       $('#loading').fadeOut( 400, "linear" );
       if (!treeDisplayed)
       {
         spinner = new Spinner(opts).spin(document.getElementById('treeColumn')); 
      }
   }, 1500);
   });
</script>
<div id="loading">
   <div class="spinner">
      <div class="bounce1"></div>
      <div class="bounce2"></div>
      <div class="bounce3"></div>
   </div>
</div>
<body ng-app="coonDataApp" id="app">
   <div class="wrapper-sticky sticky-active">
   <div class="main-header bg-header wow fadeInDown animated animated sticky" style="padding-left:50px; padding-right:50px">
        <div ng-controller="editCtrl">
         <div class="container" style="margin:0px; min-width: 100%; max-width: 100%; width:100%">
            <a href="../../main.php" class="header-logo" title="Coon Lab Data Online"></a><!-- .header-logo -->
            <div class="right-header-btn">
               <div class="right-header-btn">
                  <a href="../../logout.php" class="button" title="" data-placement="bottom" data-id="#popover-search" data-original-title="Search">
                  <p class="glyph-icon icon-sign-out" style="font-size: 120%; line-height:100%"> Logout</p>
                  </a>
               </div>
            </div>
            <div class="right-header-btn">
               <div class="right-header-btn">
                  <a href="dashboardVisualization.php" class="button" title="" data-placement="bottom" data-id="#popover-search" data-original-title="Search" ng-show="canEdit">
                  <p class="glyph-icon icon-bar-chart" style="font-size: 120%; line-height:100%"> Manage Visualizations</p>
                  </a>
               </div>
            </div>
            <div class="right-header-btn">
               <div class="right-header-btn">
                  <a href="dashboardInvite.php" class="button" title="" data-placement="bottom" data-id="#popover-search" data-original-title="Search" ng-show="canEdit">
                  <p class="glyph-icon icon-users" style="font-size: 120%; line-height:100%"> Invite Collaborators</p>
                  </a>
               </div>
            </div>
            <div class="right-header-btn">
               <div class="right-header-btn">
                  <a href="dashboardEdit.php" class="button" title="" data-placement="bottom" data-id="#popover-search" data-original-title="Search" ng-show="canEdit">
                  <p class="glyph-icon icon-edit" style="font-size: 120%; line-height:100%"> Edit Data</p>
                  </a>
               </div>
            </div>
            <div class="right-header-btn">
               <div class="right-header-btn">
                  <a href="dashboardUpload.php" class="button" title="" data-placement="bottom" data-id="#popover-search" data-original-title="Search" ng-show="canEdit">
                  <p class="glyph-icon icon-upload" style="font-size: 120%; line-height:100%"> Upload Data</p>
                  </a>
               </div>
            </div>
            <!-- .header-logo -->
            <!-- .container -->
         </div>
      </div>
      <!-- .main-header -->
   </div>
   <div class="hero-box hero-box-smaller poly-bg-1 font-inverse" data-top-bottom="background-position: 50% 0px;" data-bottom-top="background-position: 50% -600px;">
      <div class="container">
         <h1 class="hero-heading wow fadeInDown" data-wow-duration="0.6s">{{$scope.project.metadata.projectTitle}}</h1>
         <p class="hero-text wow bounceInUp" data-wow-duration="0.9s" data-wow-delay="0.2s" style="opacity:1">{{$scope.project.metadata.projectTitle}}</p>
      </div>
      <!--<div class="hero-overlay bg-black"></div>-->
   </div>
   <div id="page-content" class="col-md-10 center-margin frontend-components mrg25T">
   <div ng-controller="tabCtrl">
   <ul class="nav-responsive nav nav-justified nav-pills" style="font-size:1.2em;">
   <li class="active">
      <a href="#projectOverviewTab" data-toggle="tab">Project Overview</a>
   </li>
   <li class>
      <a href="#dataLookupTab" data-toggle="tab">Data Lookup</a>
   </li>
<li class>
	<a href="#outlierTab" data-toggle="tab" ng-click="outlierTabClick()">Outlier Analysis</a>
</li>
<li class>
	<a href="#volcanoFullProfileTab" data-toggle="tab" ng-click="volcanoTabClick()">Volcano–Full Profiles</a>
</li>
<li class>
    <a href="#barChartTab" data-toggle="tab" ng-click="barTabClick()">Bar Chart–Molecules</a>
 </li>
<li class>
	<a href="#scatterCorrelationTable" data-toggle="tab" ng-click="scatterTabClick()">Scatter–Correlations</a>
</li>
</ul>
</div>
</div>
<div class="tab-content">
   <div class="tab-pane active" id="projectOverviewTab" style="padding-left:0px; margin-top:20px">
   <div class="col-md-10 center-margin">
      <h2>Project at-a-Glance</h2>
      <div class="col-lg-12 row" style="margin-bottom:20px">
         <div ng-controller="branchCtrl">
            <div class="col-md-2" style="padding-left:0px">
               <br>
               <p style="padding-bottom:5px">Select a Branch</p>
               <select class="form-control"  ng-model='data' required="required" data-ng-options="v as v.branch_name for v in project_branch_data track by v.branch_id" ng-change="changedValue(data.branch_id)" >
                  <option style="display:none" value="">Select a Branch</option>
               </select>
            </div>
            <div class="col-md-10" style="padding-left:0px; padding-right:0px">
               <br>
               <div class="col-lg-3">
                  <div class="example-box-wrapper">
                     <div class="tile-box bg-primary">
                        <div class="tile-header">
                           Datasets
                        </div>
                        <div class="tile-content-wrapper">
                           <div class="tile-content" ng-bind ="project_branch_dataset_count">
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="col-lg-3">
                  <div class="example-box-wrapper">
                     <div class="tile-box bg-primary">
                        <div class="tile-header">
                           Conditions
                        </div>
                        <div class="tile-content-wrapper">
                           <div class="tile-content" ng-bind="project_branch_condition_count">
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="col-lg-3">
                  <div class="example-box-wrapper">
                     <div class="tile-box bg-primary">
                        <div class="tile-header">
                           Replicates
                        </div>
                        <div class="tile-content-wrapper">
                           <div class="tile-content" ng-bind ="project_branch_replicate_count">
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="col-lg-3">
                  <div class="example-box-wrapper">
                     <div class="tile-box bg-primary">
                        <div class="tile-header">
                           Measurements
                        </div>
                        <div class="tile-content-wrapper">
                           <div class="tile-content" ng-bind="project_branch_measurement_count | number">
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="col-lg-3">
                  <div class="example-box-wrapper">
                     <div class="tile-box bg-primary">
                        <div class="tile-header">
                           Inter-Replicate Coefficient of Variation (Average)
                        </div>
                        <div class="tile-content-wrapper">
                           <div class="tile-content" ng-bind="project_branch_avg_rep_cv ">
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="col-lg-3">
                  <div class="example-box-wrapper">
                     <div class="tile-box bg-primary">
                        <div class="tile-header">
                           Measurements Per Replicate (Average)
                        </div>
                        <div class="tile-content-wrapper">
                           <div class="tile-content" ng-bind="project_branch_avg_meas_per_rep | number">
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="col-lg-3">
                  <div class="example-box-wrapper">
                     <div class="tile-box bg-primary">
                        <div class="tile-header">
                           Measurements Per Condition (Average)
                        </div>
                        <div class="tile-content-wrapper">
                           <div class="tile-content" ng-bind="project_branch_avg_meas_per_cond | number">
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="col-lg-3">
                  <div class="example-box-wrapper">
                     <div class="tile-box bg-primary">
                        <div class="tile-header">
                           Overlap Between Conditions (Average)
                        </div>
                        <div class="tile-content-wrapper">
                           <div class="tile-content" ng-bind="project_branch_avg_overlap_cond | number">
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <div class="col-lg-12 row" style="top-margin:0px; padding-bottom:20px" id="treeColumn">
         <h2>Project Hierarchy</h2>
         <br>
         <div ng-controller="hierarchyTreeCtrl">
            <div id="treePane">
               <project-hierarchy-tree data="root_tree" attr1="{{repCount}}"></project-hierarchy-tree>
            </div>
         </div>
      </div>
      <div class="col-lg-12 row" style="top-margin:0px; padding-bottom:3%">
         <div ng-controller="downloadCtrl" id="downloadCtrlID">
            <h2 style="padding-bottom:1.5%">Download Project Materials</h2>
            <br>
            <div class="col-xs-9">
               <select class="form-control" name="fileSelect" ng-model="dataset" required="required" ng-options="v as v.display for v in file_list track by v.file">
                  <option style="display:none" value="">Select a Dataset</option>
               </select>
            </div>
            <div class="col-xs-3">
               <a target="_self" href="{{dataset.file}}" download="{{dataset.display}}.txt">
               <button class="btn btn-alt btn-hover btn-primary pull-right"  ng-disabled="dataset===undefined"> <span>Download Selected Dataset</span>
               <i class="glyph-icon icon-download"></i></button></a>
            </div>
         </div>
      </div>
   </div>
</div>
<div class="tab-pane" id="dataLookupTab" style="padding-left:0px; margin-top:20px">
   <div class="col-md-10 center-margin">
      <div ng-controller="DemoCtrl as ctrl" id="lookupCtrlID">
         <h2 style="margin-bottom:20px">Enter Search Term</h2>
         <div class="col-lg-12 row" style="margin-bottom:10px">
            <ui-select multiple limit="1" ng-model="item.term" theme="bootstrap" style="width: 100%;margin-bottom:20px" id="queryBox">
               <ui-select-match placeholder="Query Term">{{$item.query_term_text}}</ui-select-match>
               <ui-select-choices limit="25" refresh="searchMedia($select)" refresh-delay="400" repeat="searchRes in searchRes" id="choices">
                  <div ng-bind-html="searchRes.query_term_text | highlight: $select.search"></div>
               </ui-select-choices>
            </ui-select>
            <button class="btn btn-alt btn-hover btn-primary pull-right" style="width:250px; margin-bottom:10px; margin-top:10px" ng-click="myCall(selectionModel)">
            <span>Query Associated Data</span>
            <i class="glyph-icon icon-search"></i>
            </button>
         </div>
         <div class="col-lg-12 row" style="margin-bottom:20px" id="dataLookupColumn">
            <h2 style="margin-bottom:0px">Queried Data</h2>
            <table data-toggle="table" data-cache="false"  data-height="700" id="LookupTableOne"  data-pagination="true"  data-show-columns="true" data-search="true" style="font-size:12px;">
               <thead style="font-size:12px">
                  <tr>
                     <th data-field="repName" data-sortable="true" style="text-transform:none">Replicate Name</th>
                     <th data-field="condName" data-sortable="true" style="text-transform:none">Condition Name</th>
                     <th data-field="setName" data-sortable="true" style="text-transform:none">Set Name</th>
                     <th data-field="branchName" data-sortable="true" style="text-transform:none">Branch Name</th>
                     <th data-field="molName" data-sortable="true" style="text-transform:none">Molecule Name</th>
                     <th data-field="uniprot" data-sortable="true" style="text-transform:none">Molecule Identifier</th>
                     <th data-field="repQuantVal" data-sortable="true" >Replicate Quant Value</th>
                     <th data-field="avgQuantVal" data-sortable="true" >Avg. Quant Value</th>
                     <th data-field="stdDevQuantVal" data-sortable="true">SD Quant Value</th>
                     <th data-field="cvQuantVal" data-sortable="true">CV Quant Value</th>
                     <th data-field="allQuantVal" data-sortable="true" style="text-transform:none">All Quant Values</th>
                     <th data-field="fcMeanNorm" data-sortable="true" style="text-transform:none">Fold Change (Mean Normalized)</th>
                     <th data-field="pValueMeanNorm" data-sortable="true" style="text-transform:none">P-Value (Mean Normalized)</th>
                     <th data-field="pValueMeanNormFDR" data-sortable="true" style="text-transform:none">FDR-adjusted Q-Value (Mean Normalized)</th>
                     <th data-field="pValueMeanNormBonferroni" data-sortable="true" style="text-transform:none">Bonferroni-adjusted P-Value (Mean Normalized)</th>
                     <th data-field="fcControlNorm" data-sortable="true" style="text-transform:none">Fold Change (Control Normalized)</th>
                     <th data-field="pValueControlNorm" data-sortable="true" style="text-transform:none">P-Value (Control Normalized)</th>
                     <th data-field="pValueControlNormFDR" data-sortable="true" style="text-transform:none">FDR-adjusted Q-Value (Control Normalized)</th>
                     <th data-field="pValueControlNormBonferroni" data-sortable="true" style="text-transform:none">Bonferroni Adjusted P-Value (Control Normalized)</th>
                  </tr>
               </thead>
            </table>
            <button class="btn btn-alt btn-hover btn-primary pull-right" style="width:250px; margin-bottom:10px; margin-top:10px" ng-click="downloadData()">
            <span>Download Table Data</span>
            <i class="glyph-icon icon-file-text-o"></i>
            </button>
         </div>
      </div>
   </div>
</div>
<div class="tab-pane" id="outlierTab" style="padding-left:0px; margin-top:20px">
   <div class="col-md-10 center-margin">
      <div ng-controller="outlierCtrl" id="outlierCtrlID">
         <div class="col-lg-5 row" style="margin-bottom:10px">
            <h2 style="margin-bottom:10px">Outlier Analysis</h2>
            <!--Volcano plot goes here, wrap in a div then put text below-->
            <div id="outlierVolcanoColumn" style="padding-bottom:20px">
               <outlier-volcano data="quantData" attr1="{{maxCondition}}", attr2="{{selectedMolecule}}", attr3="{{testingCorrection}}"></outlier-volcano>
            </div>
            <div class="panel-group" id="accordionOutlierMetadata" style="margin-top:20px; width:90%; padding-left:5%">
               <div class="panel">
                  <div class="panel-heading">
                     <h4 class="panel-title">
                        <a data-toggle="collapse" data-parent="#accordionOutlierMetadata" href="#collapseOutlierMetadata" aria-expanded="false" class="collapsed">
                        Molecule Metadata {{displayMolecule}}
                        </a>
                     </h4>
                  </div>
                  <div id="collapseOutlierMetadata" class="panel-collapse collapse" aria-expanded="false" style="height: 0px;">
                     <div class="panel-body">
                        <div>
                           <div ng-bind-html="metadataText" style="padding-bottom:0px;word-break: break-all"></div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="panel-group" id="accordionOutlierQuant" style="margin-top:20px; width:90%; padding-left:5%">
               <div class="panel">
                  <div class="panel-heading">
                     <h4 class="panel-title">
                        <a data-toggle="collapse" data-parent="#accordionOutlierQuant" href="#collapseOutlierQuant" aria-expanded="false" class="collapsed">
                        Quantitative Data{{displayCondition}}
                        </a>
                     </h4>
                  </div>
                  <div id="collapseOutlierQuant" class="panel-collapse collapse" aria-expanded="false" style="height: 0px;">
                     <div class="panel-body">
                        <div>
                           <div ng-bind-html="quantDataText" style="padding-bottom:0px;word-break: break-all"></div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <p style="text-align:center;padding-bottom:20px;width:90%;padding-top:10px"><b>Figure Legend: </b>Fold changes in {{displayMolecule}} abundance log<sub>2</sub>(condition/control) versus statistical significance (-log<sub>10</sub>[<i>p</i>-value]) are shown above. The red data point ({{displayMolecule}} changes in {{displayCondition}}) was identified as a characteristic outlier by the {{algorithm.name}} algorithm.</p>
            
         </div>
         <div class="col-lg-7 row" style="margin-bottom:10px;">
            <h2 style="margin-bottom:20px">Datapoints of Interest</h2>
            <div class="col-lg-6 row" style="margin-left:0px; margin-right:0px; padding-left:0px; padding-right:0px">
               <h4 style="margin-bottom:10px">Select a Branch</h4>
               <select id="outlierAlgorithmSelect" style="margin-bottom:10px; width:90%" class="form-control pull-left"  ng-model='outlierBranch' required="required" data-ng-options="v as v.branch_name for v in outlier_branch_data track by v.branch_id" ng-change="branchChanged()">
                  <option style="display:none" value=""></option>
               </select>
            </div>
            <div class="col-lg-6 row" style=" margin-left:0px; margin-right:0px; padding-left:0px; padding-right:0px">
               <h4 style="margin-bottom:10px; margin-left:10%">Select an Algorithm</h4>
               <select id="outlierAlgorithmSelect" style="margin-bottom:10px; width:90%" class="form-control pull-right"  ng-model='algorithm' required="required" data-ng-options="v as v.name for v in algorithms track by v.value" ng-change="algorithmChanged()">
                  <option style="display:none" value=""></option>
               </select>
            </div>
            <div id="outlierTableWrapper">
               <table data-toggle="table" data-cache="false"  data-height="500" id="OutlierTableOne"  data-pagination="true"  data-show-columns="true" data-search="true" style="font-size:12px;">
                  <thead style="font-size:12px">
                     <tr style=" word-break: break-word;">
                        <th data-field="molName" data-sortable="true" style="text-transform:none;">Molecule Name</th>
                        <th data-field="regulation" data-sortable="true" >Regulation</th>
                        <th data-field="condName" data-sortable="true" >Condition</th>
                        <th data-field="distance" data-sortable="true">Distance</th>
                        <th data-field="foldChange" data-sortable="true">Fold Change</th>
                        <th data-field="pValue" data-sortable="true">P-Value</th>
                     </tr>
                  </thead>
               </table>
            </div>
            <div class="panel-group" id="accordionOutlier" style="margin-top:20px">
               <div class="panel">
                  <div class="panel-heading">
                     <h4 class="panel-title">
                        <a data-toggle="collapse" data-parent="#accordionOutlier" href="#collapseOutlier" aria-expanded="false" class="collapsed">
                        Returned Result Filters
                        </a>
                     </h4>
                  </div>
                  <div id="collapseOutlier" class="panel-collapse collapse" aria-expanded="false" style="height: 0px;">
                     <div class="panel-body">
                        <div class="col-md-5">
                           <h5 style="margin:5px; padding-bottom:10px">Multiple Testing Correction</h5>
                           <form name="myForm">
                              <input type="radio" ng-model="testingCorrection" value="uncorrected">
                              Uncorrected
                              <br/>
                              <input type="radio" ng-model="testingCorrection" value="fdradjusted">
                              FDR
                              <br/>
                              <input type="radio" ng-model="testingCorrection" value="bonferroni">
                              Bonferroni
                              <br/>
                           </form>
                           <h5 style="margin:5px; margin-top:15px">List Molecule Entry By</h5>
                           <form name="outlierMoleculeListingForm">
                              <select id="outlierMoleculeTermDropDown" style="margin-bottom:10px" class="form-control" ng-change="moleculeNameChanged()" ng-model='outlierMoleculeTerm' required="required" data-ng-options="v for v in moleculeSeekTerms">
                                 <option style="display:none" value=""></option>
                              </select>
                           </form>
                        </div>
                        <div class="col-md-7">
                           <h5 style="margin:5px">P-Value Cutoff</h5>
                           <input class="form-control" id="" ng-model="pValueCutoff" style="margin-bottom:15px">
                           <h5 style="margin:5px">Fold Change Cutoff (±)</h5>
                           <input type="text" class="form-control" id=""  ng-model="foldChangeCutoff" style="margin-bottom:10px">
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<div class="tab-pane" id="volcanoFullProfileTab" style="padding-left:0px; margin-top:20px">
   <div class="col-md-10 center-margin">
      <div ng-controller="fullVolcanoCtrl" id="volcCtrl">
         <h2>Volcano Plot-Full Perturbation Profile {{conditionName}}</h2>
         <h5 style="color:red; font-style:italic">{{overflow}}</h5>
         <div class="col-lg-12 row" style="margin-bottom:0px">
            <div class="col-md-10" style="padding-left:0px; padding-right:0px">
               <!-- Volcano plot here-->
               <div id="fullVolcanoColumn">
                  <full-volcano data="volcano_full_plot_data" attr1="{{pValueCutoff}}", attr2="{{foldChangeCutoff}}", attr3="{{fixedScale}}"
                  attr4 ="{{fixedScaleX}}" attr5="{{fixedScaleY}}", attr6="{{overflow}}", attr7="{{speedMode}}", attr8="{{testingCorrection}}"></full-volcano>
               </div>
               <div class="panel-group" id="accordionVolcMeta" style="margin-top:0px; width:95%; padding-bottom:40px; text-align:block">
                  <div class="panel">
                     <div class="panel-heading">
                        <h4 class="panel-title">
                           <a data-toggle="collapse" data-parent="#accordionVolcMeta" href="#collapseVolcMeta" aria-expanded="false" class="collapsed"; style="opacity:1">
                           Tooltip Information (Click Datapoint to Populate)
                           </a>
                        </h4>
                     </div>
                     <div id="collapseVolcMeta" class="panel-collapse collapse" aria-expanded="false" style="height: 0px;">
                        <div class="panel-body">
                           <div class="col-md-8">
                              <div>
                                 <h3 style="padding-bottom:10px">Molecule Metadata - {{selectedMolecule}}</h3>
                                 <div ng-bind-html="tooltipText" style="padding-bottom:0px; word-break: break-all"></div>
                              </div>
                           </div>
                           <div class="col-md-4">
                              <div>
                                 <h3 style="padding-bottom:10px">Tooltip Information</h3>
                                 <div ng-bind-html="tooltipQuantText" style="padding-bottom:0px"></div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="col-md-2" style="padding-left:0px; padding-right:0px">
               <h4 style="margin-bottom:10px">Select a Branch</h4>
               <select id="fullVolcanoBranchSelect" style="margin-bottom:10px" class="form-control"  ng-model='fullVolcanoBranch' required="required" data-ng-options="v as v.branch_name for v in volcano_full_branch_data track by v.branch_id" ng-change="branchChanged(fullVolcanoBranch.branch_id,fullVolcanoBranch.branch_name)">
                  <option style="display:none" value=""></option>
               </select>
               <h4 style="margin-bottom:10px">Select a Condition</h4>
               <select id="fullVolcanoConditionSelect" style="margin-bottom:20px" class="form-control"  ng-model='fullVolcanoCondition' required="required" data-ng-options="v as v.condition_name for v in volcano_full_conditions | orderBy:'condition_name' track by v.condition_id" ng-change="conditionChanged(fullVolcanoCondition.condition_id, fullVolcanoCondition.condition_name)">
                  <option style="display:none" value=""></option>
               </select>
               <hr style="opacity:1">
               <div class="panel-group" id="accordion" style="margin-top:20px">
                  <div class="panel">
                     <div class="panel-heading">
                        <h4 class="panel-title">
                           <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="false" class="collapsed">
                           Chart Settings
                           </a>
                        </h4>
                     </div>
                     <div id="collapseOne" class="panel-collapse collapse" aria-expanded="false" style="height: 0px;">
                        <div class="panel-body">
                           <h5 style="margin:5px">Multiple Testing Correction</h5>
                           <form name="myForm">
                              <input type="radio" ng-model="testingCorrection" value="uncorrected">
                              Uncorrected
                              <br/>
                              <input type="radio" ng-model="testingCorrection" value="fdradjusted">
                              FDR
                              <br/>
                              <input type="radio" ng-model="testingCorrection" value="bonferroni">
                              Bonferroni
                              <br/>
                           </form>
                           <h5 style="margin:5px; padding-top:10px">P-Value Cutoff</h5>
                           <input class="form-control" id="" ng-model="pValueCutoff" style="margin-bottom:15px">
                           <h5 style="margin:5px">Fold Change Cutoff (±)</h5>
                           <input type="text" class="form-control" id=""  ng-model="foldChangeCutoff" style="margin-bottom:10px">
                           <label class="checkbox-inline">
                           <input type="checkbox" id="" ng-model="speedMode" style="margin-bottom:15px">
                           Speed Mode
                           </label>
                           </br>
                           <label class="checkbox-inline" style="margin-top:10px">
                           <input type="checkbox" id="" ng-model="fixedScale" style="margin-bottom:15px;">
                           Fixed Scale
                           </label>
                           <h5 style="margin:5px; padding-top:5px">X-Axis Min/Max</h5>
                           <input type="text" class="form-control" id="" ng-model="fixedScaleX" style="margin-bottom:15px" ng-disabled="!fixedScale">
                           <h5 style="margin:5px">Y-Axis Max</h5>
                           <input disabled type="text" class="form-control" ng-model="fixedScaleY" style="margin-bottom:15px" ng-disabled="!fixedScale">
                        </div>
                     </div>
                  </div>
               </div>
               <hr style="opacity:1">
               <!--Datapoint Search panel-->
               <div class="panel-group" id="accordion3" style="margin-top:20px">
                  <div class="panel">
                     <div class="panel-heading">
                        <h4 class="panel-title">
                           <a data-toggle="collapse" data-parent="#accordion3" href="#collapseThree" aria-expanded="false" class="collapsed">
                           Datapoint Search
                           </a>
                        </h4>
                     </div>
                     <div id="collapseThree" class="panel-collapse collapse" aria-expanded="false" style="height: 0px;">
                        <div class="panel-body">
                           <h5 style="margin:5px">Search for Datapoint on...</h5>
                           <form name="volcanoTooltipCustomForm">
                              <select id="moleculeSeekDropDown" style="margin-bottom:10px" class="form-control"  ng-model='moleculeSeekTerm' required="required" data-ng-options="v for v in moleculeSeekTerms">
                                 <option style="display:none" value=""></option>
                              </select>
                           </form>
                           <h5 style="margin:5px; margin-top:15px">Enter Query Text</h5>
                           <ui-select multiple ng-model="queryTerm.terms" theme="bootstrap" style="width: 100%;margin-bottom:20px;" id="queryBox2" append-to-body="true"  on-remove="tagDeselect($item)" on-select="tagSelect($item)">
                              <ui-select-match placeholder="Query Term"><span id="{{$item.$$hashKey}}">{{$item.text}}</span></ui-select-match>
                              <ui-select-choices limit="25" refresh="searchMedia($select)" refresh-delay="400" repeat="searchRes in searchRes" id="choices" style="position:relative">
                                 <div ng-bind-html="searchRes.text | highlight: $select.search"></div>
                              </ui-select-choices>
                           </ui-select>
                        </div>
                     </div>
                  </div>
               </div>
               <hr style="opacity:1">
               <!--Tooltip customization panel-->
               <div class="panel-group" id="accordion2" style="margin-top:20px">
                  <div class="panel">
                     <div class="panel-heading">
                        <h4 class="panel-title">
                           <a data-toggle="collapse" data-parent="#accordion2" href="#collapseTwo" aria-expanded="false" class="collapsed">
                           Tooltip Customization
                           </a>
                        </h4>
                     </div>
                     <div id="collapseTwo" class="panel-collapse collapse" aria-expanded="false" style="height: 0px;">
                        <div class="panel-body">
                           <h5 style="margin:5px">Feature Descriptor Options</h5>
                           <form name="volcanoTooltipCustomForm">
                              <label ng-repeat="term in featureMetadataTerms" class="checkbox" style="font-weight:100">
                              <input
                                 type="checkbox"
                                 ng-model="term.selected"
                                 >{{term.name}}
                              </label>
                           </form>
                           <label class="checkbox-inline" style="margin-top:10px">
                           <input type="checkbox" id="" ng-model="volcanoShortenLongTerms" style="margin-bottom:15px;">
                           Shorten Long Terms
                           </label>
                        </div>
                     </div>
                  </div>
               </div>
               <hr style="opacity:1">
               <button class="btn btn-alt btn-hover btn-primary" style="width:100%; margin-bottom:10px; margin-top:10px" ng-click="downloadData()">
               <span>Download Chart Data</span>
               <i class="glyph-icon icon-file-text-o"></i>
               </button>
               <button class="btn btn-alt btn-hover btn-primary" style="width:100%; margin-bottom:10px; margin-top:10px" ng-click="downloadSVG()">
               <span>Download Chart SVG</span>
               <i class="glyph-icon icon-file-image-o"></i>
               </button>
                <hr style="opacity:1">
               <p style="text-align:center;padding-bottom:10px"><b>Figure Legend: </b>Fold changes in molecule abundance (condition/control) versus statistical significance (-log<sub>10</sub>[<i>p</i>-value]).</p>
               <p style="padding-bottom:0px"><span style="color:green; font-size:20px;opacity:0.7">●</span> <i>P</i> < {{pValueCutoff}} and |log<sub>2</sub>(fold change)| > {{foldChangeCutoff}} </p>
               <p style="padding-bottom:0px"><span style="color:dodgerblue; font-size:20px;opacity:0.7">●</span> <i>P</i> < {{pValueCutoff}} and |log<sub>2</sub>(fold change)| < {{foldChangeCutoff}} </p>
               <p style="padding-bottom:20px"><span style="color:gray; font-size:20px;opacity:0.7">●</span> <i>P</i> > {{pValueCutoff}} </p>
            </div>
         </div>
      </div>
   </div>
</div>
<div class="tab-pane" id="barChartTab" style="padding-left:0px; margin-top:20px">
   <div class="col-md-10 center-margin">
      <div ng-controller="barChartCtrl">
         <h2>Fold Changes - {{selectedMolecule.name}}</h2>
         <div class="col-md-10" style="padding-left:0px; padding-right:0px" id="sdfg">
            <div id="BarChartFullPlot">
               <molecule-bar-chart data="quantData" attr1="{{selectedMolecule.name}}", attr2="{{pValueCutoff}}", attr3="{{foldChangeCutoff}}" attr4="{{order}}", attr5="{{testingCorrection}}"></molecule-bar-chart> 
            </div>
            <div class="panel-group" id="accordionBarChartMeta" style="margin-top:0px; width:90%; padding-bottom:40px; text-align:block">
               <div class="panel">
                  <div class="panel-heading">
                     <h4 class="panel-title">
                        <a data-toggle="collapse" data-parent="#accordionBarChartMeta" href="#collapseBarChartMeta" aria-expanded="false" class="collapsed"; style="opacity:1">
                        Tooltip Information (Click Bar to Populate)
                        </a>
                     </h4>
                  </div>
                  <div id="collapseBarChartMeta" class="panel-collapse collapse" aria-expanded="false" style="height: 0px;">
                     <div class="panel-body">
                        <div class="col-md-8">
                           <div>
                              <h2 style="padding-bottom:10px;">Molecule Metadata - {{selectedMolecule.name}}</h2>
                              <div ng-bind-html="tooltipText" style="padding-bottom:0px;word-break: break-all"></div>
                           </div>
                        </div>
                        <div class="col-md-4">
                           <div>
                              <h2 style="padding-bottom:10px">Tooltip Information</h2>
                              <div ng-bind-html="tooltipQuantText" style="padding-bottom:0px"></div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div class="col-md-2" style="padding-left:0px; padding-right:0px">
            <!-- code here needs to be filled in -->
            <h4 style="margin-bottom:10px">Select a Branch</h4>
            <select id="barChartBranchSelect" style="margin-bottom:10px" class="form-control"  ng-model="selectedBranch" required="required" data-ng-options="v as v.branch_name for v in bar_chart_branch track by v.branch_id" ng-change="branchChanged()">
               <option style="display:none" value=""></option>
            </select>
            <h4 style="margin-bottom:10px">Bar Ordering</h4>
            <select id="pcaCondXAxisSelect" style="margin-bottom:20px" class="form-control"  ng-model="order" required="required" data-ng-options="v as v.name for v in bar_chart_order track by v.value" ng-change="sortData()">
               <option style="display:none" value=""></option>
            </select>
            <h4 style="margin-bottom:10px">Select Molecule</h4>
            <ui-select multiple limit="1" ng-model="item.term" theme="bootstrap" style="width: 100%;margin-bottom:0px;" id="barChartQueryBox" on-select="onSelected($item, $select, $event)" on-remove="onRemove()">
               <ui-select-match placeholder="Query Term">{{$item.name}}</ui-select-match>
               <ui-select-choices refresh="searchMedia($select)" refresh-delay="400" repeat="searchRes in searchRes" id="barChartChoices">
                  <div ng-bind-html="searchRes.name | highlight: $select.search"></div>
               </ui-select-choices>
            </ui-select>
            <hr style="opacity:1">
            <div class="panel-group" id="accordion4" style="margin-top:20px">
               <div class="panel">
                  <div class="panel-heading">
                     <h4 class="panel-title">
                        <a data-toggle="collapse" data-parent="#accordion4" href="#collapse4" aria-expanded="false" class="collapsed">
                        Chart Settings
                        </a>
                     </h4>
                  </div>
                  <div id="collapse4" class="panel-collapse collapse" aria-expanded="false" style="height: 0px;">
                     <div class="panel-body">
                        <h5 style="margin:5px">Multiple Testing Correction</h5>
                        <form name="myForm">
                           <input type="radio" ng-model="testingCorrection" value="uncorrected">
                           Uncorrected
                           <br/>
                           <input type="radio" ng-model="testingCorrection" value="fdradjusted">
                           FDR
                           <br/>
                           <input type="radio" ng-model="testingCorrection" value="bonferroni">
                           Bonferroni
                           <br/>
                        </form>
                        <h5 style="margin:5px">P-Value Cutoff</h5>
                        <input class="form-control" id="" ng-model="pValueCutoff"  style="margin-bottom:15px">
                        <h5 style="margin:5px">Fold Change Cutoff (±)</h5>
                        <input type="text" class="form-control" ng-model="foldChangeCutoff" id="" style="margin-bottom:20px">
                     </div>
                  </div>
               </div>
            </div>
            <hr style="opacity:1">
            <button class="btn btn-alt btn-hover btn-primary" style="width:100%; margin-bottom:10px; margin-top:0px" ng-click="downloadData()">
            <span>Download Chart Data</span>
            <i class="glyph-icon icon-file-text-o"></i>
            </button>
            <button class="btn btn-alt btn-hover btn-primary" style="width:100%; margin-bottom:10px; margin-top:10px" ng-click="downloadSVG()">
            <span>Download Chart SVG</span>
            <i class="glyph-icon icon-file-image-o"></i>
            </button> 
            <hr style="opacity:1">
            <p style="text-align:center;padding-bottom:10px"><b>Figure Legend: </b>Log<sub>2</sub> fold changes in {{selectedMolecule.name}} abundance (log<sub>2</sub>[condition/control]) are displayed along the x-axis. Error bars represent ±1 standard deviation.</p>
            <p style="padding-bottom:0px"><span style="color:green; font-size:20px;opacity:0.7;">■</span><i> P</i> < {{pValueCutoff}} and |log<sub>2</sub>(fold change)| > {{foldChangeCutoff}} </p>
            <p style="padding-bottom:0px"><span style="color:dodgerblue; font-size:20px;opacity:0.7">■</span> <i>P</i> < {{pValueCutoff}} and |log<sub>2</sub>(fold change)| < {{foldChangeCutoff}} </p>
            <p style="padding-bottom:20px"><span style="color:gray; font-size:20px;opacity:0.7">■</span> <i>P</i> > {{pValueCutoff}} </p>
         </div>
      </div>
   </div>
</div>
<div class="tab-pane" id="scatterCorrelationTable" style="padding-left:0px; margin-top:20px">
   <div class="col-md-10 center-margin">
      <h2>Condition vs Condition Correlation Analysis</h2>
      <div ng-controller="condScatterCtrl" id="condScatterCtrlID">
         <h4 style="margin-top:10px">R<sup>2</sup> Value: {{pearson | setDecimal:3}} | Slope: {{slope | setDecimal:3}}</h4>
         <div class="col-md-10" style="padding-left:0px; padding-right:0px">
            <div id="ScatterFullPlot"> 
               <corr-scatter data="cond_scatter_data" attr1="{{condOne.condition_name}}", attr2="{{condTwo.condition_name}}", attr3="{{pValueCutoff}}", attr4="{{foldChangeCutoff}}", attr5="{{showAxes}}", attr6="{{showFoldChange}}", attr7="{{showBestFit}}", attr8="{{molecule.selected}}", attr9="{{speedMode}}", attr10="{{testingCorrection}}"></corr-scatter>
            </div>
            <div class="panel-group" id="accordionCorr" style="margin-top:0px; width:90%; padding-bottom:40px;">
               <div class="panel">
                  <div class="panel-heading">
                     <h4 class="panel-title">
                        <a data-toggle="collapse" data-parent="#accordionCorr" href="#collapseCorr" aria-expanded="false" class="collapsed"; style="opacity:1">
                        Tooltip Information (Click Datapoint to Populate)
                        </a>
                     </h4>
                  </div>
                  <div id="collapseCorr" class="panel-collapse collapse" aria-expanded="false" style="height: 0px;">
                     <div class="panel-body">
                        <div class="col-md-8">
                           <div>
                              <h2 style="padding-bottom:10px;">Molecule Metadata - {{selectedMolecule}}</h2>
                              <div ng-bind-html="tooltipText" style="padding-bottom:0px; word-break: break-all"></div>
                           </div>
                        </div>
                        <div class="col-md-4">
                           <div>
                              <h2 style="padding-bottom:10px">Tooltip Information</h2>
                              <div ng-bind-html="tooltipQuantText" style="padding-bottom:0px"></div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div class="col-md-2" style="padding-left:0px; padding-right:0px">
            <!-- code here needs to be filled in -->
            <h4 style="margin-bottom:10px">Select a Branch</h4>
            <select id="corrScatterBranchSelect" style="margin-bottom:10px" class="form-control"  ng-model="selectedBranch" required="required" data-ng-options="v as v.branch_name for v in cond_scatter_branch track by v.branch_id" ng-change="branchChanged()">
               <option style="display:none" value=""></option>
            </select>
            <h4 style="margin-bottom:10px">Condition One</h4>
            <select id="pcaCondXAxisSelect" style="margin-bottom:10px" class="form-control"  ng-model="condOne" required="required" data-ng-options="v as v.condition_name for v in cond_scatter_conditions | orderBy:'condition_name' track by v.condition_id" ng-change="conditionChanged()">
               <option style="display:none" value=""></option>
            </select>
            <h4 style="margin-bottom:10px">Condition Two</h4>
            <select id="pcaCondYAxisSelect" style="margin-bottom:20px" class="form-control"  ng-model="condTwo" required="required" data-ng-options="v as v.condition_name for v in cond_scatter_conditions | orderBy:'condition_name' track by v.condition_id" ng-change="conditionChanged()">
               <option style="display:none" value=""></option>
            </select>
            <hr style="opacity:1">
            <div class="panel-group" id="accordionFive" style="margin-top:20px">
               <div class="panel">
                  <div class="panel-heading">
                     <h4 class="panel-title">
                        <a data-toggle="collapse" data-parent="#accordionFive" href="#collapseFive" aria-expanded="false" class="collapsed">
                        Chart Settings
                        </a>
                     </h4>
                  </div>
                  <div id="collapseFive" class="panel-collapse collapse" aria-expanded="false" style="height: 0px;">
                     <div class="panel-body">
                        <h5 style="margin:5px">Multiple Testing Correction</h5>
                        <form name="myForm">
                           <input type="radio" ng-model="testingCorrection" value="uncorrected">
                           Uncorrected
                           <br/>
                           <input type="radio" ng-model="testingCorrection" value="fdradjusted">
                           FDR
                           <br/>
                           <input type="radio" ng-model="testingCorrection" value="bonferroni">
                           Bonferroni
                           <br/>
                        </form>
                        <h5 style="margin:5px">P-Value Cutoff</h5>
                        <input class="form-control" id="" ng-model="pValueCutoff"  style="margin-bottom:15px">
                        <h5 style="margin:5px">Fold Change Cutoff (±)</h5>
                        <input type="text" class="form-control" ng-model="foldChangeCutoff" id="" style="margin-bottom:15px">
                        <label class="checkbox-inline" style="margin-bottom:10px">
                        <input type="checkbox" id="" ng-model="speedMode" style="margin-bottom:15px">
                        Speed Mode
                        </label>
                        </br>
                        <form name="myForm">
                           <input type="radio" ng-model="molecule.selected" value="shared">
                           Shared Changes
                           <br/>
                           <input type="radio" ng-model="molecule.selected" value="unique">
                           Unique Changes
                           <br/>
                        </form>
                        <label class="checkbox-inline">
                        <input type="checkbox" id="" ng-model="showAxes" style="margin-bottom:15px">
                        Show Axes
                        </label>
                        <br>
                        <label class="checkbox-inline">
                        <input type="checkbox" id="" ng-model="showBestFit" style="margin-bottom:15px">
                        Show Line of Best Fit
                        </label>
                        <br>
                        <label class="checkbox-inline">
                        <input type="checkbox" id="" ng-model="showFoldChange" style="margin-bottom:15px">
                        Show ±1 Fold Change
                        </label>
                        <br>
                     </div>
                  </div>
               </div>
            </div>
            <hr style="opacity:1">
            <!--Datapoint Search panel-->
            <div class="panel-group" id="accordion7" style="margin-top:20px">
               <div class="panel">
                  <div class="panel-heading">
                     <h4 class="panel-title">
                        <a data-toggle="collapse" data-parent="#accordion7" href="#collapseSeven" aria-expanded="false" class="collapsed">
                        Datapoint Search
                        </a>
                     </h4>
                  </div>
                  <div id="collapseSeven" class="panel-collapse collapse" aria-expanded="false" style="height: 0px;">
                     <div class="panel-body">
                        <h5 style="margin:5px">Search for Datapoint on...</h5>
                        <form name="volcanoTooltipCustomForm">
                           <select id="moleculeSeekDropDown" style="margin-bottom:10px" class="form-control"  ng-model='moleculeSeekTerm' required="required" data-ng-options="v for v in moleculeSeekTerms">
                              <option style="display:none" value=""></option>
                           </select>
                        </form>
                        <h5 style="margin:5px; margin-top:15px">Enter Query Text</h5>
                        <ui-select multiple ng-model="queryTerm.terms" theme="bootstrap" style="width: 100%;margin-bottom:20px;" id="queryBox3" append-to-body="true"  on-remove="tagDeselect($item)" on-select="tagSelect($item)">
                           <ui-select-match placeholder="Query Term"><span id="{{$item.$$hashKey}}">{{$item.text}}</span></ui-select-match>
                           <ui-select-choices limit="25" refresh="searchMedia($select)" refresh-delay="400" repeat="searchRes in searchRes" id="choices" style="position:relative">
                              <div ng-bind-html="searchRes.text | highlight: $select.search"></div>
                           </ui-select-choices>
                        </ui-select>
                     </div>
                  </div>
               </div>
            </div>
            <hr style="opacity:1">
            <!--Tooltip customization panel-->
            <div class="panel-group" id="accordion6" style="margin-top:20px">
               <div class="panel">
                  <div class="panel-heading">
                     <h4 class="panel-title">
                        <a data-toggle="collapse" data-parent="#accordion6" href="#collapseSix" aria-expanded="false" class="collapsed">
                        Tooltip Customization
                        </a>
                     </h4>
                  </div>
                  <div id="collapseSix" class="panel-collapse collapse" aria-expanded="false" style="height: 0px;">
                     <div class="panel-body">
                        <h5 style="margin:5px">Feature Descriptor Options</h5>
                        <form name="volcanoTooltipCustomForm">
                           <label ng-repeat="term in featureMetadataTerms" class="checkbox" style="font-weight:100">
                           <input
                              type="checkbox"
                              ng-model="term.selected"
                              >{{term.name}}
                           </label>
                        </form>
                        <label class="checkbox-inline" style="margin-top:10px">
                        <input type="checkbox" id="" ng-model="scatterShortenLongTerms" style="margin-bottom:15px;">
                        Shorten Long Terms
                        </label>
                     </div>
                  </div>
               </div>
            </div>
            <hr style="opacity:1">
            <button class="btn btn-alt btn-hover btn-primary" style="width:100%; margin-bottom:10px; margin-top:0px" ng-click="downloadData()">
            <span>Download Chart Data</span>
            <i class="glyph-icon icon-file-text-o"></i>
            </button>
            <button class="btn btn-alt btn-hover btn-primary" style="width:100%; margin-bottom:10px; margin-top:10px" ng-click="downloadSVG()">
            <span>Download Chart SVG</span>
            <i class="glyph-icon icon-file-image-o"></i>
            </button>
             <hr style="opacity:1">
            <p style="text-align:center;padding-bottom:15px"><b>Figure Legend: </b>Comparison of fold changes in molecule abundance (log<sub>2</sub>[condition/control]) for all molecules quantified accross {{condOne.condition_name}} and {{condTwo.condition_name}}. The Pearson coefficient is reported as a metric of profile similarity.</p>
            <div ng-hide="molecule.selected==='unique'">
               <p style="padding-bottom:40px"><span style="color:green; font-size:20px;opacity:0.7">●</span> <i>P</i> < {{pValueCutoff}} and |log<sub>2</sub>(fold change)| > {{foldChangeCutoff}} in both {{condOne.condition_name}} and {{condTwo.condition_name}}.</p>
            </div>
            <div ng-hide="molecule.selected==='shared'">
                <p style="padding-bottom:0px"><span style="color:dodgerblue; font-size:20px;opacity:0.7">●</span> <i>P</i> < {{pValueCutoff}} and |log<sub>2</sub>(fold change)| > {{foldChangeCutoff}} in only {{condOne.condition_name}}.</p>
               <p style="padding-bottom:40px"><span style="color:red; font-size:20px;opacity:0.7">●</span> <i>P</i> < {{pValueCutoff}} and |log<sub>2</sub>(fold change)| > {{foldChangeCutoff}} in only {{condTwo.condition_name}}.</p>
            </div>
         </div>
      </div>
   </div>
</div>
</div>
<div class="row">
</div>
</div>
</body>
<div style="position:absolute;width:100%;padding-top:0px; color:gray;  ">
   <div class="footer-pane" style="border:gray">
      <div class="container clearfix">
         <div class="logo">© 2017 Coon Labs. All Rights Reserved.</div>
         <div class="footer-nav-bottom">
            <a href="https://coonlabs.com" title="Portfolio">Coon Labs Homepage</a>
         </div>
      </div>
   </div>
   <!-- FRONTEND ELEMENTS -->
   <!-- Skrollr -->
   <script type="text/javascript" src="../assets/widgets/skrollr/skrollr.js"></script>
   <!-- Owl carousel -->

   <!-- HG sticky -->
   <script type="text/javascript" src="../assets/widgets/sticky/sticky.js"></script>
   <!-- WOW -->
   <script type="text/javascript" src="../assets/widgets/wow/wow.js"></script>

   <!-- WIDGETS -->
   <!-- Bootstrap Dropdown -->
   <script type="text/javascript" src="../assets/widgets/dropdown/dropdown.js"></script>
   <!-- Bootstrap Tooltip -->

   <!-- Bootstrap Popover -->

   <!-- Bootstrap Progress Bar -->

   <!-- Bootstrap Buttons -->
   <script type="text/javascript" src="../assets/widgets/button/button.js"></script>
   <!-- Bootstrap Collapse -->
   <script type="text/javascript" src="../assets/widgets/collapse/collapse.js"></script>
   <!-- Superclick -->

   <!-- Input switch alternate -->
   <script type="text/javascript" src="../assets/widgets/input-switch/inputswitch-alt.js"></script>
   <!-- Slim scroll -->
   <script type="text/javascript" src="../assets/widgets/slimscroll/slimscroll.js"></script>
   <!-- Content box -->
   <script type="text/javascript" src="../assets/widgets/content-box/contentbox.js"></script>
   <!-- Overlay -->
   <script type="text/javascript" src="../assets/widgets/overlay/overlay.js"></script>
   <script type="text/javascript" src="../assets/widgets/jgrowl-notifications/jgrowl.js"></script>
   <!-- Widgets init for demo -->
   <!-- Theme layout -->
   <script type="text/javascript" src="../assets/themes/frontend/layout.js"></script>
   <script type="text/javascript" src="../assets/widgets/colorpicker/colorpicker.js"></script>
<script type="text/javascript" src="../assets/widgets/colorpicker/colorpicker-demo.js"></script>
   <!-- Theme switcher -->

   <!-- Tabs -->
   <script type="text/javascript" src="../assets/widgets/tabs-ui/tabs.js"></script>
   <script type="text/javascript" src="../assets/widgets/tabs/tabs.js"></script>
   <script type="text/javascript" src="../assets/widgets/tabs/tabs-responsive.js"></script>
   <script src="bootstrap-table.js"></script>
