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

  <script src="jquery.min.js"></script>
  <script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.5.0/angular.js"></script>
  <script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.5.0/angular-sanitize.js"></script>

  <script src="jquery.min.js"></script>
  <script src="overviewDataApp.js"></script>
  <script src="d3.min.js"></script> 
  <script src="d3tip.js"></script>
  <script src="d3-save-svg.min.js"></script>
  <script src="moment.js"></script>
  <!-- Favicons -->

  <link rel="apple-touch-icon-precomposed" sizes="144x144" href="assets/images/icons/apple-touch-icon-144-precomposed.png">
  <link rel="apple-touch-icon-precomposed" sizes="114x114" href="assets/images/icons/apple-touch-icon-114-precomposed.png">
  <link rel="apple-touch-icon-precomposed" sizes="72x72" href="assets/images/icons/apple-touch-icon-72-precomposed.png">
  <link rel="apple-touch-icon-precomposed" href="assets/images/icons/apple-touch-icon-57-precomposed.png">
  <link rel="shortcut icon" href="assets/images/icons/favicon.png">



  <link rel="stylesheet" type="text/css" href="assets/bootstrap/css/bootstrap.css">


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
  <link rel="stylesheet" type="text/css" href="assets/elements/forms.css">
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

    <div ng-app="overviewDataApp" id="overviewDataID">
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
      <div ng-controller="overviewCtrl">
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
                    <li><a href="chart-boxes.html" title="Chart boxes"><span>Rename Data</span></a></li>
                    <li><a href="tile-boxes.html" title="Tile boxes"><span>Reselect Controls</span></a></li>
                    <li><a href="social-boxes.html" title="Social boxes"><span>Retransform Data</span></a></li>
                    <li><a href="panel-boxes.html" title="Panel boxes"><span>Delete Data</span></a></li>
                    <li><a href="panel-boxes.html" title="Panel boxes"><span>Create Virtual Dataset</span></a></li>
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


              <!-- Sparklines charts -->

              <script type="text/javascript" src="assets/widgets/charts/sparklines/sparklines.js"></script>
              <script type="text/javascript" src="assets/widgets/charts/sparklines/sparklines-demo.js"></script>

              <!-- Flot charts -->


              <!-- PieGage charts -->

              <script type="text/javascript" src="assets/widgets/charts/piegage/piegage.js"></script>
              <script type="text/javascript" src="assets/widgets/charts/piegage/piegage-demo.js"></script>

              <div id="page-title">
                <h1><span style="font-weight:bold">{{projectName}}–Project Overview</span></h1>
                <p style="font-size:1.5em">{{projectDescription}}</p>


              </div>
            </div>

            <div class="row" style="padding-bottom:1%">
              <div class="col-md-3">
                <div class="tile-box bg-blue">
                  <div class="tile-header">
                    Total Measurements
                  </div>
                  <div class="tile-content-wrapper">
                    <div class="tile-content" ng-model="totalMeas">
                      {{totalMeas}}
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-3">
               <div class="tile-box bg-blue">
                <div class="tile-header">
                  Unique Molecules
                </div>
                <div class="tile-content-wrapper">
                  <div class="tile-content" ng-model="uniqueMol">
                    {{uniqueMol}}
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-3">
             <div class="tile-box bg-blue">
              <div class="tile-header">
                Files Uploaded
              </div>
              <div class="tile-content-wrapper">
                <div class="tile-content" ng-model="filesUploaded">
                  {{filesUploaded}}
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
           <div class="tile-box bg-blue">
            <div class="tile-header">
              Invited Collaborators
            </div>
            <div class="tile-content-wrapper" ng-model="invitedCollabs">
              <div class="tile-content">
                {{invitedCollabs}}
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-9">
          <div class="panel" id="treePanel">
            <div class="panel-body">
              <h3 class="title-hero">
                Project Hierarchy
              </h3>
              <div class="example-box-wrapper">
                <div ng-controller="hierarchyTreeCtrl" id="treeCtrl">
                  <div id="treePane">
                    <project-hierarchy-tree data="root_tree" attr1="{{repCount}}"></project-hierarchy-tree>
                  </div>
                </div>
              </div>
            </div>
          </div>



        </div>
        <div class="col-md-3">
          <div class="panel">
            <div class="panel-body">
              <h3 class="title-hero">
                Recent activity
              </h3>
              <div class="example-box-wrapper">
                <div class="timeline-box timeline-box-left" id="timeLine">
                  <div class="tl-row" ng-repeat="event in events">
                    <div class="tl-item float-right">
                      <div class="tl-icon" ng-class="event.color">
                        <i class="glyph-icon" ng-class="event.icon"></i>
                      </div>
                      <div class="popover right">
                        <div class="arrow"></div>
                        <div class="popover-content">
                          <div class="tl-label bs-label label-success">{{event.activity}}</div>
                          <p class="tl-content">{{event.description}}</p>
                          <div class="tl-time">
                            <i class="glyph-icon icon-clock-o"> {{event.time | myDateFormat }}</i>

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
</div>


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

<!-- WIDGETS -->

<script type="text/javascript" src="assets/bootstrap/js/bootstrap.js"></script>

<!-- Bootstrap Dropdown -->

<!-- <script type="text/javascript" src="assets/widgets/dropdown/dropdown.js"></script> -->

<!-- Bootstrap Tooltip -->

<!-- <script type="text/javascript" src="assets/widgets/tooltip/tooltip.js"></script> -->

<!-- Bootstrap Popover -->

<!-- <script type="text/javascript" src="assets/widgets/popover/popover.js"></script> -->

<!-- Bootstrap Progress Bar -->

<script type="text/javascript" src="assets/widgets/progressbar/progressbar.js"></script>

<!-- Bootstrap Buttons -->

<!-- <script type="text/javascript" src="assets/widgets/button/button.js"></script> -->

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

<!-- PieGage -->

<script type="text/javascript" src="assets/widgets/charts/piegage/piegage.js"></script>
<script type="text/javascript" src="assets/widgets/charts/piegage/piegage-demo.js"></script>

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

</div>
</body>
</html>
