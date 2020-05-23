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
<title> PROJECTNAMEHERE </title>
<meta name="description" content="">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.0/angular.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.0/angular-sanitize.js"></script>
<script type="text/javascript" src="select.js"></script>
<link rel="stylesheet" href="select.css">
<link rel="stylesheet" href="https://netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.css">
<link rel="stylesheet" href="bootstrap-table.css">
<script src="jquery.min.js"></script>
<script src="jq-multi-select.js"></script>
<script src="coonDataApp.js"></script>
<script src="d3.min.js"></script> 
<script src="d3tip.js"></script>
<script src="d3-save-svg.min.js"></script>
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
   .errorBar{
      stroke: "black";
      stroke-width: 1px;
      shape-rendering: crispEdges;
   }
   td {
      word-break: break-word;
   }
   .ui-select-choices-row
   {
      position: "relative";
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
         <h1 class="hero-heading wow fadeInDown" data-wow-duration="0.6s">PROJECTNAMEHERE</h1>
         <p class="hero-text wow bounceInUp" data-wow-duration="0.9s" data-wow-delay="0.2s" style="opacity:1">PROJECTDESCRIPTIONHERE</p>
      </div>
      <!--<div class="hero-overlay bg-black"></div>-->
   </div>
   <div id="page-content" class="col-md-10 center-margin frontend-components mrg25T">
      <ul class="nav-responsive nav nav-justified nav-pills" style="font-size:1.2em;">
         <li class="active">
            <a href="#projectOverviewTab" data-toggle="tab">Project Overview</a>
         </li>
         <li class>
            <a href="#dataLookupTab" data-toggle="tab">Data Lookup</a>
         </li>
          </ul>
      <div class="tab-content">
         <div class="tab-pane active" id="projectOverviewTab" style="padding-left:0px; margin-top:20px">
            <h2>Figures of Merit</h2>
            <div class="col-lg-12 row" style="margin-bottom:20px">
               <div ng-controller="branchCtrl">
                  <div class="col-md-2" style="padding-left:0px">
                     <br>
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
      <div class="tab-pane" id="dataLookupTab" style="padding-left:0px; margin-top:20px">
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
<div class="row">
</div>
</div>
</body>
<div style="position:absolute;width:100%;padding-top:0px; color:gray;  ">
   <div class="footer-pane" style="border:gray">
      <div class="container clearfix">
         <div class="logo">© 2016 Coon Labs. All Rights Reserved.</div>
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
   <!-- Widgets init for demo -->
   <!-- Theme layout -->
   <script type="text/javascript" src="../assets/themes/frontend/layout.js"></script>
   <!-- Theme switcher -->

   <!-- Tabs -->
   <script type="text/javascript" src="../assets/widgets/tabs-ui/tabs.js"></script>
   <script type="text/javascript" src="../assets/widgets/tabs/tabs.js"></script>
   <script type="text/javascript" src="../assets/widgets/tabs/tabs-responsive.js"></script>
   <script src="bootstrap-table.js"></script>
