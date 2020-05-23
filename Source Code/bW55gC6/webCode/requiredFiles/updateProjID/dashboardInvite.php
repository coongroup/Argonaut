<?php
require("config.php");
if(empty($_SESSION['user']))
{
  header("Location: main.php");
  die("Redirecting to main.php");
}

$projectID=-1;
$query = "SELECT 1 FROM project_permissions WHERE permission_level>=3 AND user_id=:user_id AND project_id=:project_id";
$query_params = array(':user_id' => $_SESSION['user'],
  ':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetch();
if(!$row)
{
 header("Location: dashboard.php");
 die("Redirecting to dashboard.php");
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
  <title> Dev Project–Overview </title>
  <meta name="description" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

  <script src="jquery.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.0/angular.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.0/angular-sanitize.js"></script>
<script type="text/javascript" src="select.js"></script>
<link rel="stylesheet" href="select.css">

  <script src="inviteDataApp.js"></script>
  <script src="d3.min.js"></script> 
  <script src="d3tip.js"></script>
  <script src="d3-save-svg.min.js"></script>
  <script src="moment.js"></script>
   <link rel="apple-touch-icon-precomposed" sizes="144x144" href="../assets/images/icons/apple-touch-icon-144-precomposed.png">
  <link rel="apple-touch-icon-precomposed" sizes="114x114" href="../assets/images/icons/apple-touch-icon-114-precomposed.png">
  <link rel="apple-touch-icon-precomposed" sizes="72x72" href="../assets/images/icons/apple-touch-icon-72-precomposed.png">
  <link rel="apple-touch-icon-precomposed" href="../assets/images/icons/apple-touch-icon-57-precomposed.png">
  <link rel="shortcut icon" href="../assets/images/icons/favicon.png">
  <link rel="stylesheet" type="text/css" href="../assets/bootstrap/css/bootstrap.css">
  <link rel="stylesheet" type="text/css" href="../assets/helpers/animate.css">
  <link rel="stylesheet" type="text/css" href="../assets/helpers/backgrounds.css">
  <link rel="stylesheet" type="text/css" href="../assets/helpers/boilerplate.css">
  <link rel="stylesheet" type="text/css" href="../assets/helpers/border-radius.css">
  <link rel="stylesheet" type="text/css" href="../assets/helpers/grid.css">
  <link rel="stylesheet" type="text/css" href="../assets/helpers/page-transitions.css">
  <link rel="stylesheet" type="text/css" href="../assets/helpers/spacing.css">
  <link rel="stylesheet" type="text/css" href="../assets/helpers/typography.css">
  <link rel="stylesheet" type="text/css" href="../assets/helpers/utils.css">
  <link rel="stylesheet" type="text/css" href="../assets/helpers/colors.css">
  <link rel="stylesheet" type="text/css" href="../assets/elements/content-box.css">
  <link rel="stylesheet" type="text/css" href="../assets/elements/info-box.css">
  <link rel="stylesheet" type="text/css" href="../assets/elements/social-box.css">
  <link rel="stylesheet" type="text/css" href="../assets/elements/tile-box.css">
  <link rel="stylesheet" type="text/css" href="../assets/elements/timeline.css">
  <link rel="stylesheet" type="text/css" href="../assets/icons/fontawesome/fontawesome.css">
  <link rel="stylesheet" type="text/css" href="../assets/icons/linecons/linecons.css">
  <link rel="stylesheet" type="text/css" href="../assets/icons/spinnericon/spinnericon.css">
  <link rel="stylesheet" type="text/css" href="../assets/widgets/accordion-ui/accordion.css">
  <link rel="stylesheet" type="text/css" href="../assets/widgets/dropdown/dropdown.css">
  <link rel="stylesheet" type="text/css" href="../assets/elements/badges.css">
  <link rel="stylesheet" type="text/css" href="../assets/widgets/popover/popover.css">
  <link rel="stylesheet" type="text/css" href="../assets/widgets/slidebars/slidebars.css">
  <link rel="stylesheet" type="text/css" href="../assets/snippets/notification-box.css">
  <link rel="stylesheet" type="text/css" href="../assets/themes/admin/layout.css">
  <link rel="stylesheet" type="text/css" href="../assets/themes/admin/color-schemes/default.css">
  <link rel="stylesheet" type="text/css" href="../assets/themes/components/default.css">
  <link rel="stylesheet" type="text/css" href="../assets/themes/components/border-radius.css">
  <link rel="stylesheet" type="text/css" href="../assets/helpers/responsive-elements.css">
  <link rel="stylesheet" type="text/css" href="../assets/helpers/admin-responsive.css">
  <link rel="stylesheet" type="text/css" href="../assets/widgets/modal/modal.css">
  <link rel="stylesheet" type="text/css" href="../assets/widgets/dialog/dialog.css">

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

 .slimScrollDiv{
  height: 560px !important;
}

.scrollable-content.scrollable-slim-box
{
  height: 530px !important;
}

#header-logo .logo-content-big, .logo-content-small {
  left: 23px;
}

#page-title h2
 {
   font-size:26px;
   text-transform: uppercase;
   font-weight: 300;
   font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
 }

  #page-title p
 {
  font-size:14px;
   font-weight: 300;
   font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
 }

 h3
 {
  font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
 }

 .title-hero
 {
  opacity:1.0;
 }

 .timeline-box .tl-row .tl-item .tl-content
 {
  opacity:1;
 }

   .timeline-box .tl-row .tl-item .tl-time
 {
  opacity:.65;
 }

 .example-box-wrapper > .dropdown
 {
 	margin-bottom:5px;
 }

 textarea {
   resize: none;
}

</style>


</head>


<body id="body">
  <div id="sb-site">


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

    <div ng-app="inviteDataApp" id="inviteDataID">
      <div ng-controller="inviteCtrl" id="inviteCtrlID">
        <div id="page-wrapper">
          <div id="page-header" class="bg-gradient-8">
            <div id="mobile-navigation">
              <button id="nav-toggle" class="collapsed" data-toggle="collapse" data-target="#page-sidebar"><span></span></button>
              <a href="../../main.php" class="logo-content-small" title="MonarchUI"></a>
            </div>
            <div id="header-logo" class="logo-bg">
              <a href="../../main.php" class="logo-content-big" title="MonarchUI">
                Coon Lab Data Online
                <span>The perfect solution for user interfaces</span>
              </a>
              <a href="../../main.php" class="logo-content-small" title="MonarchUI">
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

            <div class="dropdown" id="settings-btn">
              <a data-toggle="dropdown" href="#" title="">
                <!-- <span class="small-badge bg-yellow"></span> -->
                <i class="glyph-icon icon-linecons-params icon-spin"></i>
              </a>
              <div class="dropdown-menu box-lg float-right">

                <div class="popover-title display-block clearfix pad10A">
                  {{projectName}} File Upload Settings
                </div>
                <div style="height:250px">
                  <div class="scrollable-slim-box">
                    <ul class="no-border notifications-box">
                      <div ng-repeat="upload in uploadSettings">
                        <li>
                          <div class="row" style="padding-left:3%; padding-right:3%;padding-bottom:1%">
                            <span class="notification-text" style="color:#3e4855; font-weight:bold"><span class="icon-notification glyph-icon icon-file-o" ng-class="upload.failed===1 ? 'bg-red' : 'bg-green'"></span>{{upload.File}}</span>
                            
                            <div class="notification-time">
                              {{upload.date | myDateFormat}}
                              <span class="glyph-icon icon-clock-o"></span>
                            </div>
                          </div>
                          <div class="row" style="padding-left:10%; padding-right:5%;">
                            <p><span style="font-weight:bold">Set Name:</span> {{upload.SetName}}</p>
                            <p><span style="font-weight:bold">Filter:</span> {{upload.Filter}}</p>
                            <p class="pull-left" style="padding-right:10%"><span style="font-weight:bold">Log<sub>2</sub> Transform Data:</span> {{upload.Log2}}</p> <p class="pull-left"><span style="font-weight:bold">Impute Missing Values:</span> {{upload.Impute}}</p> 
                          </div>
                           <div class="row" style="padding-left:10%; padding-right:5%;" ng-show="upload.HasOrganism">
                            <p class="pull-left" style="padding-right:10%"><span style="font-weight:bold">Sample Type:</span> {{upload.Organism}}</p> <p class="pull-left" style="padding-right:10%"><span style="font-weight:bold">Identifier Type:</span> {{upload.StandardIDType}}</p> 
                            <p class="pull-left" style="padding-right:10%"><span style="font-weight:bold">Standard Identifier Column:</span> {{upload.StandardIDColumn}}</p> 
                          </div>
                           
                        </li>
                      </div>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="dropdown" id="notifications-btn">
              <a data-toggle="dropdown" href="#" title="">
                <!-- <span class="small-badge bg-yellow"></span> -->
                <i class="glyph-icon icon-linecons-cog icon-spin"></i>
              </a>
              <div class="dropdown-menu box-lg float-right">

                <div class="popover-title display-block clearfix pad10A">
                  {{projectName}} Processes
                </div>
                <div style="height:250px">
                  <div class="scrollable-slim-box">
                    <ul class="no-border notifications-box">
                      <div class="popover-title display-block clearfix pad5A" style="text-align:center">
                        In Queue
                      </div>
                      <div ng-repeat="(key, process) in processes | running">
                        <li>
                          <div class="row" style="padding-left:3%; padding-right:3%;padding-bottom:1%">
                            <span class="notification-text" style="color:#3e4855"><span class="bg-blue icon-notification glyph-icon" ng-class="process.icon"></span>{{process.display}}</span>
                            <div class="notification-time">
                              {{process.time | myDateFormat}}
                              <span class="glyph-icon icon-clock-o"></span>
                            </div>
                          </div>
                          <div class="progressbar-small progressbar">
                            <div class="progressbar-value bg-blue" ng-style="{'width' : process.progress + '%' }">
                              <div class="progress-overlay"></div>
                            </div>
                          </div>
                        </li>
                      </div>
                      <div class="popover-title display-block clearfix pad5A" style="text-align:center">
                        Completed
                      </div>
                      <div ng-repeat="(key, process) in processes | finished">
                        <li>

                          <span class="notification-text" style="color:#3e4855; word-wrap: break-word;"> <span class="icon-notification glyph-icon" ng-class="[process.icon, process.color]"></span>{{process.display}}</span>
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
            </div>
            <a class="header-btn" id="logout-btn" href="../../logout.php" title="Lockscreen page example">
              <i class="glyph-icon icon-linecons-lock"></i>
            </a>

          </div>


        </div>
        <div id="page-sidebar" class="bg-gradient-8 font-inverse">
          <div class="scroll-sidebar" id="scrollSidebar">

           <ul id="sidebar-menu" id="sidebarMenu">
              <li class="header"><span>Explore</span></li>
              <li>
              <a ng-click="redirectMain()" title="Project Website">
                  <i class="glyph-icon icon-desktop"></i>
                  <span>View Project Website</span>
                </a>
              </li>
                <li class="divider" style="margin-top:5%;margin-bottom:5%"></li>
              <li class="header"><span>Review</span></li>
              <li>
                <a title="Admin Dashboard"  ng-click="redirectOverview()">
                  <i class="glyph-icon icon-eye"></i>
                  <span>Project Overview</span>
                </a>
              </li>
               <li class="divider" style="margin-top:5%;margin-bottom:5%"></li>

              <li class="header"><span>Edit Project</span></li>
              <li>
                <a ng-click="redirectUpload()" title="Elements">
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
                   <li><a ng-click="redirectEdit('descriptionPanel')" title="Tile boxes" scroll-on-click><span>Update Project Description</span></a></li>
                   <li><a ng-click="redirectEdit('controlPanel')" title="Tile boxes" scroll-on-click><span>Reselect Controls</span></a></li>
                   <li><a ng-click="redirectEdit('typePanel')" title="Social boxes" scroll-on-click><span>Change Sample Type</span></a></li>
                   <li><a ng-click="redirectEdit('filterPanel')" title="Social boxes" scroll-on-click><span>Update Data Filter</span></a></li>
                   <li><a ng-click="redirectEdit('transformPanel')" title="Social boxes" scroll-on-click><span>Retransform Data</span></a></li>
                   <li><a ng-click="redirectEdit('imputationPanel')" title="Social boxes" scroll-on-click><span>Update Imputation Settings</span></a></li>
                   <li><a ng-click="redirectEdit('deletePanel')" title="Panel boxes" scroll-on-click><span>Delete Data</span></a></li>
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
              <a ng-click="redirectViz()" title="Forms UI">
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
              <h2>{{projectName}} – Invite Collaborators</h2>
              <p>{{projectDescription}}</p>


            </div>
          </div>

    <div class="row">
      <div class="col-md-9">
        <div class="panel" id="invitePanel" style="height:730px">
          <div class="panel-body">
          	<form id="inviteForm" name="inviteForm">
            <h3 class="title-hero">
              Send Invitations
            </h3>
            <div class="col-xs-9">
            <div class="example-box-wrapper">
            	<p style="margin-bottom:5px">Enter Collaborator Email Addresses (Type Address and hit Enter)</p>
                <ui-select multiple tagging tagging-label="false" search-enabled="false" ng-model="collabs.entered" theme="bootstrap" sortable="true" ng-disabled="ctrl.disabled" style="width: 100%;" title="Enter Collaborator Email Addresses" on-select="onSelected($item, $select, $event)" on-remove="onRemove($select)" style="margin-bottom:5px">
			    <ui-select-match allow-clear="true" placeholder="Enter email addresses here">{{$item}}</ui-select-match>
			    <ui-select-choices repeat="collab in collabEmails | filter:$select.search">
			      {{color}}
			    </ui-select-choices>
			  </ui-select>
			  <span ng-hide="errorEmails.length===0" class="parsley-required">
			  	<p>The entered addresses are not valid emails: {{errorEmails}}</p>
			  </span>
            </div>
            </div>
            <div class="col-xs-3">
            	<p style="margin-bottom:5px">Permission Level</p>
            	<select class="form-control" ng-model="permissionLevel" style="margin-bottom:10px" ng-change="checkValidity()">
            		<option style="display:none" value="">Select a Permission Level</option>
					<option value="1">Read Only</option>
					<option value="2">Read & Edit</option>
					<option value="3">Project Owner</option>
				</select>
            </div>
            <div class="col-xs-12">
            	<p style="margin-bottom:5px">Include a Message</p>
            	<textarea name="" ng-model="inviteMessage" rows="23" class="form-control textarea-no-resize" style="margin-bottom:20px"></textarea>
            </div>
            <button ng-disabled="!inviteValid || inviteMessage===undefined || inviteMessage.length<1" type="submit" class="btn btn-success" style="float:right;margin-right:10px;" ng-click="sendInvites()"><span class="glyphicon glyphicon-envelope"></span> Send Invitations</button>
        	</form>
          </div>
        </div>



      </div>
      <div class="col-md-3">
        <div class="panel" style="height:730px" id="collaboratorsPanel">
          <div class="panel-body" >
            <h3 class="title-hero">
              Sent Invitations
            </h3>
            <div class="scrollable-content scrollable-slim-box">
              <div class="timeline-box timeline-box-left" id="timeLine">
                <div class="tl-row" ng-repeat="invite in invites">
                  <div class="tl-item float-right">
                    <div class="tl-icon" ng-class="invite.color">
                      <i class="glyph-icon icon-user"></i>
                    </div>
                    <div class="popover right">
                      <div class="arrow"></div>
                      <div class="popover-content">
                        <div class="tl-label bs-label" ng-class="invite.label">{{invite.activity}}</div>
                        <p class="tl-content" style='font-weight:bold;"Helvetica Neue", Helvetica, Arial, sans-serif;margin-bottom:4px'>{{invite.inviteStatus}} <span style="font-weight:normal">{{invite.invitationDate}}</span></p>
                        <p class="tl-content" style='font-weight:bold;"Helvetica Neue", Helvetica, Arial, sans-serif;margin-bottom:4px'>Permission Level: <span style="font-weight:normal">{{invite.permission}}</span></p>
                        <p class="tl-content" style='font-weight:bold;"Helvetica Neue", Helvetica, Arial, sans-serif;margin-bottom:4px'>Invited By: <span style="font-weight:normal">{{invite.inviter}}</span></p>
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
</div>
</div>
</div>

<script type="text/javascript" src="../assets/bootstrap/js/bootstrap.js"></script>
<script type="text/javascript" src="../assets/widgets/superclick/superclick.js"></script>
<script type="text/javascript" src="../assets/widgets/input-switch/inputswitch-alt.js"></script>
<script type="text/javascript" src="../assets/widgets/slimscroll/slimscroll.js"></script>
<script type="text/javascript" src="../assets/widgets/screenfull/screenfull.js"></script>
<script type="text/javascript" src="../assets/js-init/widgets-init.js"></script>
<script type="text/javascript" src="../assets/themes/admin/layout.js"></script>
</div>
</body>

<script>

var target = document.querySelector('#body');
var observer = new WebKitMutationObserver(function(mutations) {
  mutations.forEach(function(mutation) {
    setTimeout(function(){
      angular.element(document.getElementById('treeCtrl')).scope().$digest();}, 400);
  });    
});
observer.observe(target, { attributes: true, childList: false, characterData: false, subtree: false });


</script>
</html>
