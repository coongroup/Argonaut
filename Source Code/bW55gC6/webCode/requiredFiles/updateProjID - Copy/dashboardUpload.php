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

	<link rel="stylesheet" type="text/css" href="assets/widgets/charts/justgage/justgage.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/charts/morris/morris.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/charts/piegage/piegage.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/charts/xcharts/xcharts.css">

	<link rel="stylesheet" type="text/css" href="assets/widgets/chosen/chosen.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/colorpicker/colorpicker.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/datatable/datatable.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/datepicker/datepicker.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/datepicker-ui/datepicker.css">
	<link rel="stylesheet" type="text/css" href="assets/widgets/daterangepicker/daterangepicker.css">
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
	<link rel="stylesheet" type="text/css" href="assets/widgets/xeditable/xeditable.css">

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

		<div ng-app="uploadDataApp" id="uploadDataID">
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


				</div>
				<div ng-controller="uploadCtrl" id="uploadCtrlID">
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
											<li><a ng-click="redirectEdit('renamePanel')" title="Chart boxes" scroll-on-click><span>Rename Data</span></a></li>
										<li><a ng-click="redirectEdit('controlPanel')" title="Tile boxes" scroll-on-click><span>Reselect Controls</span></a></li>
										<li><a ng-click="redirectEdit('transformPanel')" title="Social boxes" scroll-on-click><span>Retransform Data</span></a></li>
										<li><a ng-click="redirectEdit('imputationPanel')" title="Social boxes" scroll-on-click><span>Update Imputation Settings</span></a></li>
										<li><a ng-click="redirectEdit('deletePanel')" title="Panel boxes" scroll-on-click><span>Delete Data</span></a></li>
											<!--<li><a href="panel-boxes.html" title="Panel boxes"><span>Create Virtual Dataset</span></a></li>-->
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
									<h1><span style="font-weight:bold">{{projectName}}–Data Upload</span></h1>
									<p style="font-size:1.5em">{{projectDescription}}</p>


								</div>
							</div>
	<form name="uploadForm" id="uploadFormID" novalidate>
							<div class="row" style="padding-bottom:1%">
								<div class="row" style="padding-bottom:1%">
									<div class="col-md-12">
										<div class="panel">
											<div class="panel-body">
												<h3 class="title-hero">
													Peak Table Upload
												</h3>

											
													<!--need to add a set name-->
													<div class="col-sm-4">
														<label class=" control-label">Specify Set Name</label>
														<input type="text" class="form-control" placeholder="Enter a unique dataset name" ng-model="setName" name="setName"  ng-class="{'parsley-error' : uploadForm.setName.$error.uniqueSetName}"required set-name>
														<span ng-show="uploadForm.setName.$error.uniqueSetName" class="parsley-required">Specified set name is not unique.</span>
													</div>
													<!--need to add a branch drop down-->
													<div class="col-sm-4">
														<label class=" control-label">Select Branch</label>
														<select class="form-control" ng-model="selectedBranch" ng-options="x as x.branch_name for x in branches track by x.branch_id"><option value="" ng-if="false"></option></select>
													</div>
													<!--need to add an optional new branch -->
													<div class="col-sm-4">
														<label class=" control-label">Create a New Branch (Optional)</label>
														<input type="text" class="form-control" placeholder="Specify a unique dataset name"  title="" name="newBranchName" ng-minlength="1" ng-model="newBranchName" ng-class="{'parsley-error' : uploadForm.newBranchName.$error.uniqueBranchName}" branch-name>
														<span ng-show="uploadForm.newBranchName.$error.uniqueBranchName" class="parsley-required">Specified branch name is invalid.</span>
														<button class="btn btn-info float-right" style="margin-top:2%" ng-click="addBranch()" ng-disabled="uploadForm.newBranchName.$error.uniqueBranchName || uploadForm.newBranchName.$error.length || uploadForm.newBranchName.$pristine">Create Branch</button>
													</div>

													<div class="col-md-12">
														<li class="divider"></li>
													</div>
													<div class="col-sm-4">
														<label class=" control-label">Select Delimiter</label>
														<select class="form-control" ng-model="delimiter" ng-options="x as x.delimiter for x in delimiters">
															<option value="" ng-if="false"></option>
												 <!-- <option label="Comma (',')" value="COMMA" selected>Comma (','')</option>
													<option label="Tab ('\t')" value="TAB">Tab ('\t')</option>
													<option label="Semicolon (';')" value="SEMICOLON">Semicolon (';'')</option>
													<option label="Whitespace (' ')" value="WHITESPACE">Whitespace (' ')</option> -->
												</select>

											</div>
											<div class="col-sm-8">
												<label class=" control-label">Select a File for Upload (Plain Text)</label><br>

												<input type="file" accept=".txt" id="files" nv-file-select="" uploader="uploader" ng-click="uploader.clearQueue(); clear();" filters="customFilter, queueLimit" multiple ng-disabled="uploadForm.setName.$invalid"/>
												<table class="table">
													<thead>
														<tr>
															<th width="50%">Name</th>
															<th ng-show="uploader.isHTML5">Size</th>
															<th ng-show="uploader.isHTML5">Progress</th>
															<th>Status</th>
															<th>Actions</th>
														</tr>
													</thead>
													<tbody>
														<tr ng-repeat="item in uploader.queue">
															<td><strong>{{ item.file.name }}</strong></td>
															<td ng-show="uploader.isHTML5" nowrap>{{ item.file.size/1024/1024|number:2 }} MB</td>
															<td ng-show="uploader.isHTML5">
																<div class="progressbar" style="margin-bottom: 0;">
																	<div class="progress-bar bg-blue " role="progressbar" ng-style="{ 'width': item.progress + '%' }"><div class="progress-overlay"></div></div>
																</div>
															</td>
															<td class="text-center">
																<span ng-show="item.isSuccess"><i class="glyphicon glyphicon-ok"></i></span>
																<span ng-show="item.isCancel"><i class="glyphicon glyphicon-ban-circle"></i></span>
																<span ng-show="item.isError"><i class="glyphicon glyphicon-remove"></i></span>
															</td>
															<td nowrap>
																<button type="button" class="btn btn-success btn-xs" ng-click="item.upload()" ng-disabled="item.isReady || item.isUploading || item.isSuccess">
																	<span class="glyphicon glyphicon-upload"></span> Upload
																</button>
																<button type="button" class="btn btn-warning btn-xs" ng-click="item.cancel()" ng-disabled="!item.isUploading">
																	<span class="glyphicon glyphicon-ban-circle"></span> Cancel
																</button>
																<button type="button" class="btn btn-danger btn-xs" ng-click="item.remove(); clear() ">
																	<span class="glyphicon glyphicon-trash"></span> Remove
																</button>
															</td>
														</tr>
													</tbody>
												</table>

												<div>
													<div>
														Upload progress:
														<div class="progress" style="">
															<div class="progress-bar bg-blue " role="progressbar" ng-style="{ 'width': uploader.progress + '%' }"><div class="progress-overlay"></div><div class="progress-label">{{uploader.progress + '%'}}</div></div>
														</div>
													</div>
													<button type="button" class="btn btn-success btn-s" ng-click="uploader.uploadAll()" ng-disabled="!uploader.getNotUploadedItems().length">
														<span class="glyphicon glyphicon-upload"></span> Upload all
													</button>
													<button type="button" class="btn btn-warning btn-s" ng-click="uploader.cancelAll()" ng-disabled="!uploader.isUploading">
														<span class="glyphicon glyphicon-ban-circle"></span> Cancel all
													</button>
													<button type="button" class="btn btn-danger btn-s" ng-click="uploader.clearQueue(); clear()" ng-disabled="!uploader.queue.length">
														<span class="glyphicon glyphicon-trash"></span> Remove all
													</button>
												</div>


											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-md-12">
									<div class="panel">
										<div class="panel-body" id="uploadPanelBody">
											<h3 class="title-hero">
												Data Organization
											</h3>

											<div class="col-xs-5">
												<div id="testDiv">
													<label class=" control-label">Organize/Edit Headers</label>
													<hot-table settings="{colHeaders: colHeaders, contextMenu: ['row_above', 'row_below', 'remove_row'], stretchH: 'all', overflow: 'hidden', outsideClickDeselects : false}"
													row-headers="false"
													min-spare-rows="34"
													datarows="tableItems.headers" 
													height="800" hot-id="table1" on-after-selection="headerTableChange">
													<hot-column data="header" title="'Column Headers'" class="ng-isolate-scope" read-only></hot-column>
												</hot-table>
											</div>
										</div>
										<div class="col-xs-1 text-center"> 
											<span class="btn btn-default" style="width:80%" id="uniqueIDLeftButton" ng-disabled="identifierTableSelection.length!=1" ng-click="identifierOffClick()">◄◄◄</span>
											<span class="btn btn-default" style="width:80%" id="featureDescriptorLeftButton" ng-disabled="featureTableSelection.length==0" ng-click="featureOffClick()">◄◄◄</span>
											<span class="btn btn-default" style="width:80%" id="quantDataLeftButton" ng-disabled="quantTableSelection.length==0" ng-click="quantOffClick()">◄◄◄</span>
										</div>
										<div class="col-xs-1 text-center"> 
											<span class="btn btn-default" style="width:80%" id="uniqueIDRightButton" ng-disabled="headerTableSelection.length!=1 || identifierSelected" ng-click="identifierOnClick()">►►►</span>
											<span class="btn btn-default" style="width:80%" id="featureDescriptorRightButton" ng-disabled="headerTableSelection.length<1" ng-click="featureOnClick()">►►►</span>
											<span class="btn btn-default" style="width:80%" id="quantDataRightButton" ng-disabled="headerTableSelection.length<1" ng-click="quantOnClick()">►►►</span>
										</div>

										<div class="col-xs-5" id="rightTableCol">
											<label class=" control-label">Unique Identifier Column</label>
											<hot-table settings="{colHeaders: colHeaders, contextMenu: ['row_above', 'row_below', 'remove_row'], stretchH: 'all', outsideClickDeselects : false}"
											row-headers="false"
											min-spare-rows="3"
											datarows="tableItems.identifiers"
											height="100" id="uniqueIdentifierTable" hot-id="table2" on-after-selection="identifierTableChange">
											<hot-column title="'Column Header'" data="header" class="ng-isolate-scope" read-only></hot-column>
											<hot-column data="userName" title="'Identifier Name'" class="ng-isolate-scope"></hot-column>
										</hot-table>

										<label class=" control-label" style="margin-top:2%">Feature Descriptor Columns</label>
										<hot-table settings="{colHeaders: colHeaders, contextMenu: ['row_above', 'row_below', 'remove_row'], stretchH: 'all', outsideClickDeselects : false}"
										row-headers="false"
										min-spare-rows="7"
										datarows="tableItems.features"
										height="170" id="featureDescriptorTable" hot-id="table3" on-after-selection="featureTableChange">
										<hot-column data="header" title="'Column Header'" class="ng-isolate-scope" read-only></hot-column>
										<hot-column data="userName" title="'Descriptor Name'"></hot-column>
									</hot-table>

									<label class=" control-label" style="margin-top:2%">Quantitative Data Columns</label>
									<hot-table settings="{colHeaders: colHeaders, contextMenu: ['row_above', 'row_below', 'remove_row'], stretchH: 'all', outsideClickDeselects : false}"
									row-headers="false"
									min-spare-rows="19"
									datarows="tableItems.quant"
									height="450" id="quantDataTable" hot-id="table4" on-after-selection="quantTableChange" on-after-change="quantTableAfterChange">
									<hot-column data="header" title="'Column Header'" class="ng-isolate-scope" read-only></hot-column>
									<hot-column data="condName" title="'Condition Name'"></hot-column>
									<hot-column data="repName" title="'Replicate Name'"></hot-column>
									<hot-column data="control" title="'Control'" type="'checkbox'" width="100" checked-template="'Yes'" unchecked-template="'No'"></hot-column>
								</hot-table>
							</div>

							<!--it might be cool to have a tree which auto updates when you make changes...-->

						</div>

					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-md-12" style="margin-bottom:40px">
					<div class="panel">
						<div class="panel-body" id="uploadPanelBody">
							<h3 class="title-hero">
								Data Processing
							</h3>
							<p style="font-style:italic; margin-bottom:15px; text-align:center">Note: All negative, null, non-numeric, and empty quantitative values will be set to zero prior to optional missing value imputation. Zero values are excluded from downstream statistical analyses. Data filtering is performed prior to optional missing value imputation.</p>
							<div class="col-xs-5">
								<label class=" control-label">Apply Data Filter</label>
								<select class="form-control" ng-model="dataFilter" ng-options="x as x.filter for x in dataFilters" style="margin-bottom:10px">
									<option value="" ng-if="false"></option>
								</select>
								<div id="totalContainer" style="text-align:center" ng-hide="dataFilter.value!='TOTAL'">
									<label for="minTotalRepBox">Retain Values Observed in at Least</label>
									<input class="form-control" type="text" ng-model="minTotalReps" name="minTotalReps" style="width:50px; display:inline-block" range range-min="0" range-max="100"
									ng-class="{'parsley-error' : uploadForm.minTotalReps.$error.validPercent}"></input>
									<label for="minTotalRepBox">% of Replicates</label>
									<p style="margin-top:5px">Values observed in less than {{calcReplicates}} of {{totalReplicates}} replicates will be eliminated from the data set</p>
									<span ng-show="uploadForm.minTotalReps.$error.validPercent" class="parsley-required">Please enter a valid percentage between 0 and 100.</span>
								</div>
								<div id="condContainer" style="text-align:center" ng-hide="dataFilter.value!='COND'"> 
									<label for="minPercentRepBox">Retain Values Observed in at Least</label>
									<input class="form-control" type="text" ng-model="minRepsPerCond" name="minRepsPerCond" style="width:50px; display:inline-block" range range-min="0" range-max="100"
									ng-class="{'parsley-error' : uploadForm.minRepsPerCond.$error.validPercent}"></input>
									<label for="minPercentRepBox">% of Replicates in</label>
									<input class="form-control" type="text" ng-model="minConditions" name="minConditions" style="width:60px; display:inline-block" condrange range-min="0" range-max="{{totalConditions}}"
									ng-class="{'parsley-error' : uploadForm.minConditions.$error.validCond}"></input>
									<label for="minTotalRepBox"> of {{totalConditions}} Conditions</label>
									<p style="margin-top:5px">Values observed in fewer than {{minRepsPerCond}}% of replicates in at least {{minConditions}} conditions will be eliminated from the data set</p>
									<span ng-show="uploadForm.minRepsPerCond.$error.validPercent" class="parsley-required" style="display:block">Please enter a valid percentage between 0 and 100.</span>
									<span ng-show="uploadForm.minConditions.$error.validCond" class="parsley-required" style="display:block">Please enter a valid number of conditions between 0 and {{totalConditions}}.</span>
								</div>
							</div>
							<div class="col-xs-1"></div>
							<div class="col-xs-3">
								<label class=" control-label">Transform and Impute</label>
                        <div class="checkbox" style="margin-top:0">
                            <label>
                                <input type="checkbox" ng-model="log2Transform" checked="true" id="log2Check">
                                Log<sub>2</sub> Transform Quantitative Values
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" ng-model="impute" checked="true" id="imputeCheck">
                                Impute Missing Quantitative Values
                            </label>
                        </div>
							</div>
							
						<div class="col-xs-3">
							<button class="btn btn-danger" style="margin-top:5%;text-align:center;margin-right:25px;" ng-click="uploader.clearQueue(); clear()"><span class="glyphicon glyphicon-ban-circle"></span> Discard Changes</button>			
								<button class="btn btn-success " ng-click="showModal()" style="margin-top:5%; text-align:center" ng-disabled="!formValid() || uploadForm.setName.$invalid || 
								(dataFilter.value=='COND' && (uploadForm.minRepsPerCond.$error.validPercent || uploadForm.minConditions.$error.validCond)) || 
								(dataFilter.value=='TOTAL' && uploadForm.minTotalReps.$error.validPercent) || redundantHeaders"><span class="glyphicon glyphicon-save"></span> Save Data</button>
							
						</div>
					</div>
				</div>
			</div>
		</div>

<div class="modal fade" id="checkDataModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" style="text-align:center; padding:25px">
            <div class="modal-body">
               <h1 style="padding-bottom:15px;">Confirm Data Organization</h1>
               <h3 style="padding-bottom:10px;">Please review the data organization below before proceeding.</h3>
               	<hr style="margin-top:5px; margin-bottom:5px">
               		<div id="treePanel">
               			<div id="treePane">
               			 			<project-hierarchy-tree data="root_tree" attr1="{{totalReplicates}}"></project-hierarchy-tree>
               			</div>
               		</div>
               		<hr style="margin-top:5px; margin-bottom:5px">
               		<div class="row" style ="text-align:left;margin-bottom:5px">
               			<div class="col-xs-2" style="">
               			<p><span style="font-weight:bold">Input File: </span>{{uploadFileName}}</p></div>
               			<div class="col-xs-2" style="">
               				<p><span style="font-weight:bold">Unique Identifier: </span><span ng-repeat="x in tableItems.identifiers | filter:identifierFilter" >{{x.header}}</span></p></div>
               				<div class="col-xs-8" style="">
               				<p><span style="font-weight:bold">Feature Descriptors: </span><span ng-repeat="x in tableItems.features | filter:identifierFilter" >{{x.header}} {{$last ? '' : ($index==tableItems.features.length-2) ? ' and ' : ', '}}</span></p></div>
               				</div>
               					<div class="row" style ="text-align:left;">
               				<div class="col-xs-6" style="">
               			<p><span style="font-weight:bold">Data Filter: </span>
               				<span ng-if="dataFilter.value=='COND'">Values observed in fewer than {{minRepsPerCond}}% of replicates in at least {{minConditions}} conditions will be eliminated from the data set.</span>
               				<span ng-if="dataFilter.value=='NONE'">No filter applied.</span>
               				<span ng-if="dataFilter.value=='TOTAL'">Values observed in less than {{calcReplicates}} of {{totalReplicates}} replicates will be eliminated from the data set.</span>
               			</p></div>
               			<div class="col-xs-3" style="">
               			<p  class="capitalize"><span style="font-weight:bold">Log<sub>2</sub> Transform Quantitative Data: </span>{{log2Transform}}</p></div>
               			<div class="col-xs-3" style="">
               			<p  class="capitalize"><span style="font-weight:bold">Impute Missing Quantitative Values: </span>{{impute}}</p></div>
               		</div>
               		<hr style="margin-top:5px; margin-bottom:5px">
               	</div>
								
               <form id="submitDataForm">
                <div class="form-group" style="padding-bottom:15px">
                	 <button type="submit" class="btn btn-success" style="float:right;margin-right:15px;" ng-click="saveData()" ng-disabled="!formValid() || uploadForm.setName.$invalid || 
								(dataFilter.value=='COND' && (uploadForm.minRepsPerCond.$error.validPercent || uploadForm.minConditions.$error.validCond)) || 
								(dataFilter.value=='TOTAL' && uploadForm.minTotalReps.$error.validPercent) || redundantHeaders" data-dismiss="modal" target="checkDataModal"><span class="glyphicon glyphicon-save"></span> Confirm Data Organization</button>
                       <button type="submit" class="btn btn-info" style="float:right; margin-right:15px;" data-dismiss="modal" target="checkDataModal"><span class="glyphicon glyphicon-ban-circle"></span> Continue Editing</button>
                         
                </div>
               </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="errorReportModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="false" data-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="text-align:center; padding:25px">
            <div class="modal-body">
               <h2 style="padding-bottom:15px;">{{uploadFileName}} Error Report</h1>
              
               	<hr style="margin-top:5px; margin-bottom:5px">
               	<div ng-hide="criticalErrors.length==0">
               		<h2 style="padding-top:5px;padding-bottom:5px" class="parsley-required">Critical Errors</h2>
               		<h5 style="padding-bottom:10px">{{uploadFileName}} cannot be properly uploaded until the following issues are resolved offline. Please edit the file accordingly and resubmit!</h5>
               		<textarea name="" rows="8" class="form-control textarea-no-resize" readonly ng-model="criticalErrorString"></textarea>
               			<hr style="margin-top:5px; margin-bottom:5px">
               	</div>
               	<div ng-hide="noncriticalErrors.length==0">
               		<h2 style="padding-top:5px;padding-bottom:5px" class="parsley-required">Non-Critical Errors</h2>
               		<h5 style="padding-bottom:10px">The following errors exist but can be managed during data processing (i.e., duplicate identifiers will be appended with an additional numerical value to make them unique). To continue with the data upload click 'Proceed' otherwise click 'Cancel' to edit the file offline.</h5>
               		<textarea name="" rows="8" class="form-control textarea-no-resize" readonly ng-model="noncriticalErrorString"></textarea>
               			<hr style="margin-top:5px; margin-bottom:5px">
               	</div>
               	<div ng-show="criticalErrors.length==0 && noncriticalErrors.length==0">
               		<h3 style="padding-top:5px;padding-bottom:5px;color:green">Success!</h3>
               		<h5 style="padding-bottom:5px">No errors were found in {{uploadFileName}}. Please select 'Proceed' to begin adding data to the {{projectName}} web portal or 'Cancel' to terminate the upload.</h5>
               			<hr style="margin-top:5px; margin-bottom:5px">
               	</div>
               	</div>
               <form id="finalDataSubmitForm">
                <div class="form-group" style="padding-bottom:15px">
                	 <button type="submit" class="btn btn-success" style="float:right;margin-right:15px;" ng-hide="criticalErrors.length>0" ng-disabled="criticalErrors.length>0" ng-click="confirmUpload()" data-dismiss="modal" target="checkDataModal"><span class="glyphicon glyphicon-check"></span> Proceed</button>
                       <button type="submit" class="btn btn-danger" style="float:right; margin-right:15px;" ng-click=" cancelUpload()" data-dismiss="modal" target="checkDataModal"><span class="glyphicon glyphicon-ban-circle"></span> Cancel</button>
                         
                </div>
               </form>
            </div>
        </div>
    </div>
</div>

	</form>

</div>
</div>
</div>
</div>
</div>
</div>
</div>

<script src="jquery.min.js"></script>
<script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.5.0/angular.js"></script>
<script src="angular-file-upload.min.js"></script>

<script src="d3.min.js"></script> 
<script src="d3tip.js"></script>
<script src="d3-save-svg.min.js"></script>

<script src="uploadDataApp.js"></script>
<script src="handsontable.full.js"></script>
<script src="ngHandsontable.js"></script>
<script src="dataFactory.js"></script>

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
			angular.element(document.getElementById('uploadFormID')).scope().render();}, 600);
	});    
});
observer.observe(target, { attributes: true, childList: false, characterData: false, subtree: false });

function show()
{
	console.log("heye");
	$('#checkDataModal').modal('show');
}
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

</script>

<script>
jQuery(document).ready(function() {
	$('#uniqueIDLeftButton').css({top:$('#uniqueIdentifierTable').offset().top - $('#rightTableCol').offset().top + 40});
	$('#featureDescriptorLeftButton').css({top:$('#featureDescriptorTable').offset().top - $('#rightTableCol').offset().top -32 + 80});
	$('#quantDataLeftButton').css({top:$('#quantDataTable').offset().top - $('#rightTableCol').offset().top -64 + 200});
	$('#uniqueIDRightButton').css({top:$('#uniqueIdentifierTable').offset().top - $('#rightTableCol').offset().top + 40});
	$('#featureDescriptorRightButton').css({top:$('#featureDescriptorTable').offset().top - $('#rightTableCol').offset().top-32 + 80});
	$('#quantDataRightButton').css({top:$('#quantDataTable').offset().top - $('#rightTableCol').offset().top -64 + 200});
});


</script>


</div>
</div>
</div>
</body>
</html>
