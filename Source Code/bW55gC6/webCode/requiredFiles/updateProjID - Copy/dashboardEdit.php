<?php
require("config.php");
if(empty($_SESSION['user']))
{
	header("Location: main.php");
	die("Redirecting to main.php");
}
$projectID=-1;
$query = "SELECT 1 FROM project_permissions WHERE permission_level>=2 AND user_id=:user_id AND project_id=:project_id";
$query_params = array(':user_id' => $_SESSION['user'],
	':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetch();
if(!$row)
{
	header("Location: main.php");
	die("Redirecting to main.php");
}

?>
<!DOCTYPE html> 
<html lang="en">
<head>

	<style>
	/* Loading Spinner */
	.spinner{margin:0;width:70px;height:18px;margin:-35px 0 0 -9px;position:absolute;top:50%;left:50%;text-align:center}.spinner > div{width:18px;height:18px;background-color:#333;border-radius:100%;display:inline-block;-webkit-animation:bouncedelay 1.4s infinite ease-in-out;animation:bouncedelay 1.4s infinite ease-in-out;-webkit-animation-fill-mode:both;animation-fill-mode:both}.spinner .bounce1{-webkit-animation-delay:-.32s;animation-delay:-.32s}.spinner .bounce2{-webkit-animation-delay:-.16s;animation-delay:-.16s}@-webkit-keyframes bouncedelay{0%,80%,100%{-webkit-transform:scale(0.0)}40%{-webkit-transform:scale(1.0)}}@keyframes bouncedelay{0%,80%,100%{transform:scale(0.0);-webkit-transform:scale(0.0)}40%{transform:scale(1.0);-webkit-transform:scale(1.0)}}
	</style>


	<meta charset="UTF-8">
	<!--[if IE]><meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'><![endif]-->
	<title> Monarch UI - Bootstrap Frontend &amp; Admin Template </title>
	<meta name="description" content="">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

	<link rel="stylesheet" type="text/css" href="assets/bootstrap/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="handsontable.full.css">

	<link rel="stylesheet" href="fileUpload/css/jquery.fileupload.css">
	<link rel="stylesheet" href="fileUpload/css/jquery.fileupload-ui.css">

	<link rel="stylesheet" href="select.css">



	<!-- Favicons -->

	<link rel="apple-touch-icon-precomposed" sizes="144x144" href="assets/images/icons/apple-touch-icon-144-precomposed.png">
	<link rel="apple-touch-icon-precomposed" sizes="114x114" href="assets/images/icons/apple-touch-icon-114-precomposed.png">
	<link rel="apple-touch-icon-precomposed" sizes="72x72" href="assets/images/icons/apple-touch-icon-72-precomposed.png">
	<link rel="apple-touch-icon-precomposed" href="assets/images/icons/apple-touch-icon-57-precomposed.png">
	<link rel="shortcut icon" href="assets/images/icons/favicon.png">


	<!-- HELPERS -->

	<link rel="stylesheet" type="text/css" href="assets/helpers/animate.css">
	<link rel="stylesheet" type="text/css" href="assets/helpers/backgrounds.css">
	<link rel="stylesheet" type="text/css" href="assets/helpers/boilerplate.css">
	<link rel="stylesheet" type="text/css" href="assets/helpers/border-radius.css">
	<link rel="stylesheet" type="text/css" href="assets/helpers/grid.css">
	<link rel="stylesheet" type="text/css" href="assets/helpers/page-transitions.css">
	<link rel="stylesheet" type="text/css" href="assets/helpers/spacing.css">
	<link rel="stylesheet" type="text/css" href="assets/helpers/typography.css">
	<link rel="stylesheet" type="text/css" href="assets/helpers/utils.css">
	<link rel="stylesheet" type="text/css" href="assets/helpers/colors.css">

	<!-- ELEMENTS -->

	<link rel="stylesheet" type="text/css" href="assets/elements/badges.css">
	<link rel="stylesheet" type="text/css" href="assets/elements/buttons.css">
	<link rel="stylesheet" type="text/css" href="assets/elements/content-box.css">
	<link rel="stylesheet" type="text/css" href="assets/elements/dashboard-box.css">
	
	<link rel="stylesheet" type="text/css" href="assets/elements/images.css">
	<link rel="stylesheet" type="text/css" href="assets/elements/info-box.css">
	<link rel="stylesheet" type="text/css" href="assets/elements/invoice.css">
	<link rel="stylesheet" type="text/css" href="assets/elements/loading-indicators.css">
	<link rel="stylesheet" type="text/css" href="assets/elements/menus.css">
	<link rel="stylesheet" type="text/css" href="assets/elements/panel-box.css">
	<link rel="stylesheet" type="text/css" href="assets/elements/response-messages.css">
	<link rel="stylesheet" type="text/css" href="assets/elements/responsive-tables.css">
	<link rel="stylesheet" type="text/css" href="assets/elements/ribbon.css">
	<link rel="stylesheet" type="text/css" href="assets/elements/social-box.css">
	<link rel="stylesheet" type="text/css" href="assets/elements/tables.css">
	<link rel="stylesheet" type="text/css" href="assets/elements/tile-box.css">
	<link rel="stylesheet" type="text/css" href="assets/elements/timeline.css">



	<!-- ICONS -->

	<link rel="stylesheet" type="text/css" href="assets/icons/fontawesome/fontawesome.css">
	<link rel="stylesheet" type="text/css" href="assets/icons/linecons/linecons.css">
	<link rel="stylesheet" type="text/css" href="assets/icons/spinnericon/spinnericon.css">


	<!-- WIDGETS -->

	<link rel="stylesheet" type="text/css" href="assets/widgets/accordion-ui/accordion.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/calendar/calendar.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/carousel/carousel.css">

	<link rel="stylesheet" type="text/css" href="assets/widgets/chosen/chosen.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/colorpicker/colorpicker.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/datatable/datatable.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/dialog/dialog.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/dropdown/dropdown.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/dropzone/dropzone.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/file-input/fileinput.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/input-switch/inputswitch.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/input-switch/inputswitch-alt.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/ionrangeslider/ionrangeslider.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/jcrop/jcrop.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/jgrowl-notifications/jgrowl.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/loading-bar/loadingbar.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/maps/vector-maps/vectormaps.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/markdown/markdown.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/modal/modal.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/multi-select/multiselect.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/multi-upload/fileupload.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/nestable/nestable.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/noty-notifications/noty.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/popover/popover.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/pretty-photo/prettyphoto.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/progressbar/progressbar.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/range-slider/rangeslider.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/slidebars/slidebars.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/slider-ui/slider.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/summernote-wysiwyg/summernote-wysiwyg.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/tabs-ui/tabs.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/theme-switcher/themeswitcher.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/timepicker/timepicker.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/tocify/tocify.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/tooltip/tooltip.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/touchspin/touchspin.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/uniform/uniform.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/wizard/wizard.css">

	<!-- SNIPPETS -->

	<link rel="stylesheet" type="text/css" href="assets/snippets/chat.css">
	<link rel="stylesheet" type="text/css" href="assets/snippets/files-box.css">
	<link rel="stylesheet" type="text/css" href="assets/snippets/login-box.css">
	<link rel="stylesheet" type="text/css" href="assets/snippets/notification-box.css">
	<link rel="stylesheet" type="text/css" href="assets/snippets/progress-box.css">
	<link rel="stylesheet" type="text/css" href="assets/snippets/todo.css">
	<link rel="stylesheet" type="text/css" href="assets/snippets/user-profile.css">
	<link rel="stylesheet" type="text/css" href="assets/snippets/mobile-navigation.css">

	<!-- APPLICATIONS -->

	<link rel="stylesheet" type="text/css" href="assets/applications/mailbox.css">

	<!-- Admin theme -->

	<link rel="stylesheet" type="text/css" href="assets/themes/admin/layout.css">
	<link rel="stylesheet" type="text/css" href="assets/themes/admin/color-schemes/default.css">

	<!-- Components theme -->

	<link rel="stylesheet" type="text/css" href="assets/themes/components/default.css">
	<link rel="stylesheet" type="text/css" href="assets/themes/components/border-radius.css">

	<!-- Admin responsive -->

	<link rel="stylesheet" type="text/css" href="assets/helpers/responsive-elements.css">
	<link rel="stylesheet" type="text/css" href="assets/helpers/admin-responsive.css">

	<!-- JS Core -->

	<script type="text/javascript" src="assets/js-core/jquery-core.js"></script>
	<script type="text/javascript" src="assets/js-core/jquery-ui-core.js"></script>
	<script type="text/javascript" src="assets/js-core/jquery-ui-widget.js"></script>
	<script type="text/javascript" src="assets/js-core/jquery-ui-mouse.js"></script>
	<script type="text/javascript" src="assets/js-core/jquery-ui-position.js"></script>
	<!--<script type="text/javascript" src="assets/js-core/transition.js"></script>-->
	<script type="text/javascript" src="assets/js-core/modernizr.js"></script>
	<script type="text/javascript" src="assets/js-core/jquery-cookie.js"></script>


	<script type="text/javascript">
	$(window).load(function(){
		setTimeout(function() {
			$('#loading').fadeOut( 400, "linear" );
		}, 300);
	});
	</script>

	<script type="text/javascript">

	var opts = {
		lines: 15 // The number of lines to draw
		, length: 28 // The length of each line
		, width: 11 // The line thickness
		, radius: 41 // The radius of the inner circle
		, scale: 0.35 // Scales overall size of the spinner
		, corners: 1 // Corner roundness (0..1)
		, color: '#ffffff' // #rgb or #rrggbb or array of colors
		, opacity: 0.35 // Opacity of the lines
		, rotate: 90 // The rotation offset
		, direction: 1 // 1: clockwise, -1: counterclockwise
		, speed: 1 // Rounds per second
		, trail: 60 // Afterglow percentage
		, fps: 20 // Frames per second when using setTimeout() as a fallback for CSS
		, zIndex: 2e9 // The z-index (defaults to 2000000000)
		, className: 'spinnerCircle' // The CSS class to assign to the spinner
		, top: '50%' // Top position relative to parent
		, left: '50%' // Left position relative to parent
		, shadow: true // Whether to render a shadow
		, hwaccel: false // Whether to use hardware acceleration
		, position: 'absolute' // Element positioning
		, backgroundColor: '#ffffff' // Element positioning
	}

	var spinner = null;

	var treeDisplayed = false;
	$(window).load(function(){
		setTimeout(function() {
			$('#loading').fadeOut( 400, "linear" );
			if (!treeDisplayed)
			{
					 // spinner = new Spinner(opts).spin(document.getElementById('treePane')); 
					}
				}, 1500);
	});

	</script>

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

	#testDiv
	{width:100%;
	}
	.handsontable-container
	{
		width: 100%;
	}

	input[type=file]{ 
		color:transparent;
	}

	.bootstrap-touchspin
	{
		width: 100px;
		float:left;
	}

	.myClass input
	{
		float:left;
	}

	@media (min-width: 768px) {
		.modal-xl {
			width: 95%;
			max-width:2000px;
		}
	}
	.capitalize {
		text-transform: capitalize;
	}
	textarea {
		resize: none;
	}

	.modal {
		text-align: center;
	}

	@media screen and (min-width: 768px) { 
		.modal:before {
			display: inline-block;
			vertical-align: middle;
			content: " ";
			height: 100%;
		}
	}

	.modal-dialog {
		display: inline-block;
		text-align: left;
		vertical-align: middle;
	}

	</style>


</head>


<body id="body">
	<div id="sb-site">

		<div id="loader-overlay" class="ui-front loader ui-widget-overlay bg-black opacity-80" style="display: none;"><img src="assets/images/spinner/loader-light.gif" alt=""></div>
		<div class="sb-slidebar bg-black sb-right sb-style-overlay">
			<div class="scrollable-content scrollable-slim-sidebar">
				<div class="pad15A">
				</div>
			</div>
		</div>
		<div id="loading">
			<div class="spinner">
				<div class="bounce1"></div>
				<div class="bounce2"></div>
				<div class="bounce3"></div>
			</div>
		</div>

		<div ng-app="editDataApp" id="editDataID">
			<div ng-controller="editCtrl" id="editCtrlID">
				<div id="page-wrapper">
					<div id="page-header" class="bg-blue">
						<div id="mobile-navigation">
							<button id="nav-toggle" class="collapsed" data-toggle="collapse" data-target="#page-sidebar"><span></span></button>
							<a href="index.html" class="logo-content-small" title="MonarchUI"></a>
						</div>
						<div id="header-logo" class="logo-bg">
							<a href="index.html" class="logo-content-big" title="MonarchUI">
								Coon Lab Data Online
								<span>The perfect solution for user interfaces</span>
							</a>
							<a href="index.html" class="logo-content-small" title="MonarchUI">
								Coon Lab Data Online
								<span>The perfect solution for user interfaces</span>
							</a>
							<a id="close-sidebar" href="#" title="Close sidebar">
								<i class="glyph-icon icon-angle-left"></i>
							</a>
						</div>
						<div id="header-nav-right">
							<a href="#" class="hdr-btn" id="fullscreen-btn" title="Fullscreen">
								<i class="glyph-icon icon-arrows-alt"></i>
							</a>

							<div class="dropdown" id="notifications-btn">
								<a data-toggle="dropdown" href="#" title="">
									<!-- <span class="small-badge bg-yellow"></span> -->
									<i class="glyph-icon icon-linecons-cog icon-spin"></i>
								</a>
								<div class="dropdown-menu box-lg float-right">

									<div class="popover-title display-block clearfix pad10A">
										{{projectName}} Processes
									</div>
									<div class="scrollable-content scrollable-slim-box">
										<ul class="no-border notifications-box">
											<div class="popover-title display-block clearfix pad5A" style="text-align:center">
												In Queue
											</div>
											<div ng-repeat="process in activeProcesses">
												<li>
												<span class="bg-blue icon-notification glyph-icon" ng-class="process.icon"></span>
												<span class="notification-text" style="color:#3e4855">{{process.name}}</span>
												<div class="notification-time">
													{{process.time | myDateFormat}}
													<span class="glyph-icon icon-clock-o"></span>
												</div>
											</li>
											</div>
											<div class="popover-title display-block clearfix pad5A" style="text-align:center">
												Completed
											</div>
											<div ng-repeat="process in completedProcesses">
												<li>
												<span class="bg-green icon-notification glyph-icon" ng-class="process.icon"></span>
												<span class="notification-text" style="color:#3e4855">{{process.name}}</span>
												<div class="notification-time">
													{{process.time | myDateFormat}}
													<span class="glyph-icon icon-clock-o"></span>
												</div>
											</li>
											</div>
										</ul>
									</div>
								</div>
							</div>
							<a class="header-btn" id="logout-btn" href="logout.php" title="Lockscreen page example">
								<i class="glyph-icon icon-linecons-lock"></i>
							</a>

						</div>
					</div>
				</div>

				<div id="page-sidebar">
					<div class="scroll-sidebar" id="scrollSidebar">

						<ul id="sidebar-menu" id="sidebarMenu">
							<li class="header"><span>Review</span></li>
							<li>
								<a href="index.html" title="Admin Dashboard">
									<i class="glyph-icon icon-eye"></i>
									<span>Project Overview</span>
								</a>
								<a href="index.html" title="Project Website">
									<i class="glyph-icon icon-desktop"></i>
									<span>View Project Website</span>
								</a>
							</li>
							<li class="divider"></li>

							<li class="header"><span>Edit Project</span></li>
							<li>
								<a href="#" title="Elements">
									<i class="glyph-icon icon-upload"></i>
									<span>Upload New Data</span>
								</a>

							</li>
							<li>
								<a href="#" title="Dashboard boxes">
									<i class="glyph-icon icon-edit"></i>
									<span>Edit Existing Data</span>
								</a>
								<div class="sidebar-submenu">

									<ul>
										<li><a href="#renamePanel" title="Chart boxes" scroll-on-click><span>Rename Data</span></a></li>
										<li><a href="#controlPanel" title="Tile boxes" scroll-on-click><span>Reselect Controls</span></a></li>
										<li><a href="#transformPanel" title="Social boxes" scroll-on-click><span>Retransform Data</span></a></li>
										<li><a href="#imputationPanel" title="Social boxes" scroll-on-click><span>Update Imputation Settings</span></a></li>
										<li><a href="#deletePanel" title="Panel boxes" scroll-on-click><span>Delete Data</span></a></li>
										<!--<li><a href="#deletePanel" title="Panel boxes"><span>Create Virtual Dataset</span></a></li>-->
									</ul>

								</div><!-- .sidebar-submenu -->
							</li>
							<li>
								<a href="#" title="Widgets">
									<i class="glyph-icon icon-users"></i>
									<span>Invite Collaborators</span>
								</a>

							</li>
							<li>
								<a href="#" title="Forms UI">
									<i class="glyph-icon icon-bar-chart"></i>
									<span>Manage Visualizations</span>
								</a>

							</li>

						</ul><!-- #sidebar-menu -->


					</div>
				</div>
				<div id="page-content-wrapper">
					<div id="page-content">
						<div class="container">
							<div id="page-title">
								<h1><span style="font-weight:bold">{{projectName}}–Edit Data</span></h1>
								<p style="font-size:1.5em">{{projectDescription}}</p>
							</div>
						</div>
						<div class="row" style="padding-bottom:1%">
							<div class="row" style="padding-bottom:1%">
								<div class="col-md-12">
									<div class="panel" id="renamePanel">
										<div class="panel-body">
											<h3 class="title-hero">
												Rename Data
											</h3>
											<form id="renameForm" name="renameForm">
												<!--need to select a specific data type (branch, set, etc.)-->
												<div class="col-sm-4">
													<label class=" control-label">Select Data Type</label>
													<select class="form-control" name="renameDataTypeSelect" ng-model="renameDataType" ng-options="x as x for x in dataTypes" ng-change="renameDataTypeChange()" required><option value="" ng-if="false"></option></select>
												</div>
												<!--need to select a specific node to rename-->
												<div class="col-sm-4">
													<label class=" control-label">Select Specific Data</label>
													<select class="form-control" name="renameDataSpecificSelect" ng-model="renameDataSelectedData" ng-options="x as x.name group by x.group for x in renameDataSpecific track by x.id" required><option value="" ng-if="false"></option></select>
												</div>
												<!--need to a new & unique name-->
												<div class="col-sm-4">
													<label class=" control-label">New Name</label>
													<input type="text" class="form-control" placeholder="Enter a unique data name"  title="" name="newDataName" ng-model="renameName" ng-disabled="renameForm.renameDataSpecificSelect.$invalid" ng-minlength="1" ng-maxlength="255" required ng-class="{'parsley-error' : renameForm.newDataName.$error.uniqueName && !renameForm.newDataName.$pristine}" rename>
													<span ng-show="renameForm.newDataName.$error.uniqueName" class="parsley-required">Specified name is already in use!</span>
													<button class="btn btn-success float-right" style="margin-top:2%" ng-disabled="renameForm.$invalid" ng-click="doRename()">Rename Data</button>
												</div>
											</form>
										</div>
									</div>
								</div>
							</div>

							<div class="row" style="padding-bottom:1%">
								<div class="col-md-12">
									<div class="panel" id="controlPanel">
										<div class="panel-body" id="uploadPanelBody">
											<h3 class="title-hero">
												Reselect Controls
											</h3>
											<form id="reselectControlForm" name="reselectControlForm">
												<div class="col-sm-4">
													<label class=" control-label">Select Set to Edit</label>
													<select class="form-control" ng-model="selectedControlSet" required ng-options="x as x.name group by x.group for x in allSets | orderBy:'group' track by x.id" ng-change="controlSetChange()"><option value="" ng-if="false"></option></select>
												</div>
												<div class="col-sm-8"></div>
												<div class="col-sm-12">
													<label class=" control-label" style="margin-top:1%">Quantitative Data Columns from Set</label>
													<hot-table settings="{colHeaders: colHeaders, contextMenu: ['row_above', 'row_below', 'remove_row'], stretchH: 'all', outsideClickDeselects : false}"
													row-headers="false"
													min-rows="15"
													datarows="controlSetData"
													height="350" id="controlDataTable" hot-id="controlTable" on-after-change="controlTableAfterChange">
													<hot-column data="header" title="'Column Header'" class="ng-isolate-scope" read-only></hot-column>
													<hot-column data="condName" title="'Condition Name'" read-only></hot-column>
													<hot-column data="repName" title="'Replicate Name'" read-only></hot-column>
													<hot-column data="control" title="'Control'" type="'checkbox'" width="100" checked-template="'1'" unchecked-template="'0'"></hot-column>
												</hot-table></div>
												<div class="col-sm-8"></div>
												<div class="col-sm-4">
													<button class="btn btn-success float-right" ng-disabled="reselectControlForm.$invalid" style="margin-top:2%" ng-click="updateControls()">Update Controls</button>
												</div>
											</form>
										</div>
									</div>
								</div>
							</div>

							<div class="row" style="padding-bottom:1%">
								<div class="col-md-12">
									<div class="panel" id="transformPanel">
										<div class="panel-body" id="uploadPanelBody">
											<h3 class="title-hero">
												Retransform Data
											</h3>
											<form id="retransformForm" name="retransformForm">
												<div class="col-sm-12">
													<label class=" control-label" style="margin-top:0%">Quantitative Data Columns from Set</label>
													<hot-table settings="{colHeaders: colHeaders, contextMenu: ['row_above', 'row_below', 'remove_row'], stretchH: 'all', outsideClickDeselects : false}"
													row-headers="false"
													min-rows="15"
													datarows="transformData"
													height="350" id="transformDataTable" hot-id="transformTable" on-after-change="transformTableAfterChange">
													<hot-column data="name" title="'Set Name'" class="ng-isolate-scope" read-only></hot-column>
													<hot-column data="branch" title="'Branch Name'" read-only></hot-column>
													<hot-column data="log2" title="'Log2 Transform Quantitative Values'" type="'checkbox'" width="100" checked-template="'1'" unchecked-template="'0'"></hot-column>
												</hot-table></div>
												<div class="col-sm-8"></div>
												<div class="col-sm-4">
													<button class="btn btn-success float-right" style="margin-top:2%" ng-click="updateTransform()" ng-disabled="retransformForm.$invalid">Retransform Data</button>
												</div>
											</form>
										</div>
									</div>
								</div>
							</div>

							<div class="row" style="padding-bottom:1%">
								<div class="col-md-12">
									<div class="panel" id="imputationPanel">
										<div class="panel-body" id="uploadPanelBody">
											<h3 class="title-hero">
												Change Imputation Settings
											</h3>
											<form id="imputationForm" name="imputationForm">
												<div class="col-sm-12">
													<label class=" control-label" style="margin-top:0%">Quantitative Data Columns from Set</label>
													<hot-table settings="{colHeaders: colHeaders, contextMenu: ['row_above', 'row_below', 'remove_row'], stretchH: 'all', outsideClickDeselects : false}"
													row-headers="false"
													min-rows="15"
													datarows="imputationData"
													height="350" id="imputationDataTable" hot-id="imputationTable" on-after-change="imputationTableAfterChange">
													<hot-column data="name" title="'Set Name'" class="ng-isolate-scope" read-only></hot-column>
													<hot-column data="branch" title="'Branch Name'" read-only></hot-column>
													<hot-column data="impute" title="'Impute Missing Quantitative Values'" type="'checkbox'" width="100" checked-template="'1'" unchecked-template="'0'"></hot-column>
												</hot-table></div>
												<div class="col-sm-8"></div>
												<div class="col-sm-4">
													<button class="btn btn-success float-right" style="margin-top:2%" ng-click="updateImputation()" ng-disabled="imputationForm.$invalid">Update Imputation Settings</button>
												</div>
											</form>
										</div>
									</div>
								</div>
							</div>

							<div class="row" style="padding-bottom:1%">
								<div class="col-md-12">
									<div class="panel" id="deletePanel">
										<div class="panel-body" id="uploadPanelBody">
											<h3 class="title-hero">
												Delete Data
											</h3>
											<form name="deleteDataForm" id="deleteDataForm">
												<div class="col-sm-6">
													<label class=" control-label">Select Data Type</label>
													<select class="form-control" name="deleteDataTypeSelect" ng-model="deleteDataType" ng-options="x as x for x in dataTypes" ng-change="deleteDataTypeChange()" required><option value="" ng-if="false"></option></select>
												</div>
												<div class="col-sm-6">
													<label class=" control-label">Select Specific Data</label>
													<select class="form-control" name="deleteDataSpecificSelect" ng-model="deleteDataSelectedData" ng-options="x as x.name group by x.group for x in deleteDataSpecific | orderBy:'group' track by x.id" required><option value="" ng-if="false"></option></select>
												</div>
												<div class="col-sm-6"></div>
												<div class="col-sm-6">
													<button class="btn btn-danger float-right" style="margin-top:2%; margin-left:15px" ng-disabled="deleteDataForm.$invalid" ng-click="doDelete()">Delete Data</button>
												</div>
											</form>
										</div>
									</div>
								</div>
							</div>

							<div class="modal fade" id="checkDataModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
								<div class="modal-dialog modal-xl">
									<div class="modal-content" style="text-align:center; padding:25px">
										<div class="modal-body">
											<h1 style="padding-bottom:15px;">{{editOperation}}</h1>
											<h3 style="padding-bottom:10px;">{{editDescription}}</h3>
											<hr style="margin-top:5px; margin-bottom:5px">
											<div id="treePanel">
												<div id="treePane">
													<project-hierarchy-tree data="display_tree"></project-hierarchy-tree>
												</div>
											</div>
											<hr style="margin-top:5px; margin-bottom:5px">
											<!-- <div class="row" style ="text-align:left;">
												<div class="col-xs-3" style="">
													<p  class="capitalize"><span style="font-weight:bold">Affected nodes are shown in green.</p></div>
												</div>
												<hr style="margin-top:5px; margin-bottom:5px">
											</div> -->

											<form id="submitDataForm">
												<div class="form-group" style="padding-bottom:15px">
													<button type="submit" class="btn btn-success" style="float:right;margin-right:15px;" ng-click="doEdits()"data-dismiss="modal" target="checkDataModal"><span class="glyphicon glyphicon-check"></span> {{buttonText}}</button>
													<button type="submit" class="btn btn-info" style="float:right; margin-right:15px;" data-dismiss="modal" target="checkDataModal"><span class="glyphicon glyphicon-ban-circle"></span> Continue Editing</button>
												</div>
											</form>
										</div>
									</div>
								</div>
							</div>

							<div class="modal fade" id="errorReportModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="false" data-keyboard="false">
								<div class="modal-dialog modal-lg">
									<div class="modal-content" style="text-align:center; padding:25px">
										<div class="modal-body">
											<h1 style="padding-bottom:15px;">{{errorStatus}}</h1>

											<hr style="margin-top:5px; margin-bottom:5px">
											<h3 style="padding-top:10px; padding-bottom:10px">{{errorMessage}}</h3>
											<hr style="margin-top:5px; margin-bottom:5px">
											<div class="form-group" style="padding-bottom:5px">
												<button type="submit" class="btn btn-success" style="float:right;margin-right:0px;margin-top:10px; margin-bottom:10px" data-dismiss="modal" ng-click="dismiss()" target="errorReportModal"><span class="glyphicon glyphicon-check"></span> Dismiss</button>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>


	<script src="jquery.min.js"></script>
	<script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.5.0/angular.js"></script>
	<script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.5.0/angular-sanitize.js"></script>

	<script src="d3.min.js"></script> 
	<script src="d3tip.js"></script>
	<script src="d3-save-svg.min.js"></script>

	<script src="editDataApp.js"></script>
	<script src="handsontable.full.js"></script>
	<script src="ngHandsontable.js"></script>
	<script src="dataFactory.js"></script>
	<script type="text/javascript" src="select.js"></script>
	<script type="text/javascript" src="angular-chosen.min.js"></script>
	<script type="text/javascript" src="chosen-add-option.js"></script>
	<script type="text/javascript" src="assets/widgets/chosen/chosen-demo.js"></script>

	<!-- WIDGETS -->

	<script type="text/javascript" src="assets/bootstrap/js/bootstrap.js"></script>
	<script type="text/javascript" src="assets/widgets/touchspin/touchspin.js"></script>
	<script>

	$("input[name='touchspin-demo-1']").TouchSpin({
		min: 0,
		max: 100,
		step: 1,
		decimals: 0,
		boostat: 5,
		maxboostedstep: 10,
		verticalbuttons:!0,verticalupclass:"glyph-icon icon-plus",verticaldownclass:"glyph-icon icon-minus"
	});
	var target = document.querySelector('#body');
	var observer = new WebKitMutationObserver(function(mutations) {
		mutations.forEach(function(mutation) {
			setTimeout(function(){
				angular.element(document.getElementById('reselectControlForm')).scope().render();}, 600);
		});    
	});
	observer.observe(target, { attributes: true, childList: false, characterData: false, subtree: false });

	
	</script>

	<!-- Bootstrap Dropdown -->

	<!-- <script type="text/javascript" src="assets/widgets/dropdown/dropdown.js"></script> -->

	<!-- Bootstrap Tooltip -->

	<!-- <script type="text/javascript" src="assets/widgets/tooltip/tooltip.js"></script> -->

	<!-- Bootstrap Popover -->

	<!-- <script type="text/javascript" src="assets/widgets/popover/popover.js"></script> -->

	<!-- Bootstrap Progress Bar -->

	<script type="text/javascript" src="assets/widgets/progressbar/progressbar.js"></script>

	<!-- Bootstrap Buttons -->

	<script type="text/javascript" src="assets/widgets/button/button.js"></script> 

	<!-- Bootstrap Collapse -->

	<!-- <script type="text/javascript" src="assets/widgets/collapse/collapse.js"></script> -->

	<!-- Superclick -->

	<script type="text/javascript" src="assets/widgets/superclick/superclick.js"></script>

	<!-- Input switch alternate -->

	<script type="text/javascript" src="assets/widgets/input-switch/inputswitch-alt.js"></script>

	<!-- Slim scroll -->

	<script type="text/javascript" src="assets/widgets/slimscroll/slimscroll.js"></script>

	<!-- Slidebars -->

	<script type="text/javascript" src="assets/widgets/slidebars/slidebars.js"></script>
	<script type="text/javascript" src="assets/widgets/slidebars/slidebars-demo.js"></script>

<!-- PieGage 

<script type="text/javascript" src="assets/widgets/charts/piegage/piegage.js"></script>
<script type="text/javascript" src="assets/widgets/charts/piegage/piegage-demo.js"></script>-->

<!-- Screenfull -->

<script type="text/javascript" src="assets/widgets/screenfull/screenfull.js"></script>

<!-- Content box -->

<script type="text/javascript" src="assets/widgets/content-box/contentbox.js"></script>

<!-- Overlay -->

<script type="text/javascript" src="assets/widgets/overlay/overlay.js"></script>

<!-- Widgets init for demo -->

<script type="text/javascript" src="assets/js-init/widgets-init.js"></script>

<!-- Theme layout -->

<script type="text/javascript" src="assets/themes/admin/layout.js"></script>

<!-- Theme switcher -->

<script type="text/javascript" src="assets/widgets/theme-switcher/themeswitcher.js"></script>


</body>
</html>
