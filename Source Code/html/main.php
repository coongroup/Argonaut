<?php
require("config.php");
if(empty($_SESSION['user']))
{
  header("Location: index.html");
  die("Redirecting to index.html");
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
<title>
  Coon Lab Data Online
</title>
<meta name="description" content="">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<script src="jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.0/angular.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.0/angular-sanitize.js"></script>
<script type="text/javascript" src="select.js"></script>
<link rel="stylesheet" href="select.css">
<link rel="stylesheet" href="https://netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.css">
<link rel="stylesheet" href="bootstrap-table.css">
<script src="mainDataApp.js"></script>
<script src="spin.min.js"></script>
<script type="text/javascript" src="angular-chosen.min.js"></script>
<script type="text/javascript" src="chosen-add-option.js"></script>
<script type="text/javascript" src="assets/widgets/chosen/chosen-demo.js"></script>
<script src="jquery.validate.js"></script>
<script src="moment.js"></script>

<link rel="apple-touch-icon-precomposed" sizes="144x144" href="../assets/images/icons/apple-touch-icon-144-precomposed.png">
<link rel="apple-touch-icon-precomposed" sizes="114x114" href="../assets/images/icons/apple-touch-icon-114-precomposed.png">
<link rel="apple-touch-icon-precomposed" sizes="72x72" href="../assets/images/icons/apple-touch-icon-72-precomposed.png">
<link rel="apple-touch-icon-precomposed" href="../assets/images/icons/apple-touch-icon-57-precomposed.png">
<link rel="shortcut icon" href="../assets/images/icons/favicon.png">
<link rel="stylesheet" type="text/css" href="../assets/helpers/boilerplate.css">
<link rel="stylesheet" type="text/css" href="../assets/helpers/grid.css">
<link rel="stylesheet" type="text/css" href="../assets/helpers/spacing.css">
<link rel="stylesheet" type="text/css" href="../assets/helpers/typography.css">
<link rel="stylesheet" type="text/css" href="../assets/helpers/utils.css">
<link rel="stylesheet" type="text/css" href="../assets/helpers/colors.css">
<link rel="stylesheet" type="text/css" href="../assets/icons/fontawesome/fontawesome.css">
<link rel="stylesheet" type="text/css" href="../assets/themes/frontend/layout.css">
<link rel="stylesheet" type="text/css" href="../assets/themes/frontend/color-schemes/default.css">
<link rel="stylesheet" type="text/css" href="../assets/helpers/frontend-responsive.css">
<link rel="stylesheet" type="text/css" href="assets/admin-all-demo.css">


<style>
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
div.modal-content{
  -webkit-box-shadow: none;
  -moz-box-shadow: none;
  -o-box-shadow: none;
  box-shadow: none;
}
td {
 word-break: break-word;
}
.btn-xl {
  padding: 18px 118px;
  font-size: 18px; //change this to your desired size
  line-height: normal;
  -webkit-border-radius: 8px;
  -moz-border-radius: 8px;
  border-radius: 8px;
}

.chosen-container
{
	border-color: #5c646f !important;
}

.btn
{
	border-color: #5c646f !important;
}

</style>

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

  $(window).load(function(){
   setTimeout(function() {
     $('#loading').fadeOut( 400, "linear" );
     spinner = new Spinner(opts).spin(document.getElementById('treeColumn')); 
   }, 400);
 });
  </script>

  <div id="loader-overlay" class="ui-front loader ui-widget-overlay bg-black opacity-80" style="display: none;"><img src="assets/images/spinner/loader-light.gif" alt=""></div>
  <div id="loading">
    <div class="spinner">
      <div class="bounce1"></div>
      <div class="bounce2"></div>
      <div class="bounce3"></div>
    </div>
  </div>

  <body ng-app="mainDataApp">
   <div ng-controller="mainPageCtrl" id="mainPageID">
     <div class="wrapper-sticky sticky-active">
      <div class="main-header bg-header wow fadeInDown animated animated sticky" style="padding-left:50px; padding-right:50px">
      	<div class="container" style="margin:0px; min-width: 100%; max-width: 100%; width:100%">
          <a href="main.php" class="header-logo" title="Coon Lab Data Online"></a><!-- .header-logo -->
          <div class="right-header-btn">
           <div class="right-header-btn">
            <a href="logout.php" class="button" title="" data-placement="bottom" data-id="#popover-search" data-original-title="Search">
              <i class="glyph-icon icon-sign-out" style="font-size: 125%; line-height:100%"> Logout</i>
            </a>
          </div>
        </div>
        <!-- .header-logo -->
        <!-- .container -->
      </div>
      <!-- .main-header -->
    </div>


    
  </div>
  
  <div class="row" style="margin-top:4%">
   <div class="col-lg-12">
     <p style="text-align:center;font-size:4.8em;font-family:Raleway;font-weight:lighter;padding-bottom:2.5%">Welcome Back {{user_pref_name}}</p>
     <p style="text-align:center;font-size:380%;font-family:Raleway;font-weight:lighter;padding-bottom:1.5%">Your Projects</p>
     <div class="form-group" style="padding-bottom:5%">
      <div class="col-sm-3"></div>
      <div class="col-sm-6" style="padding-bottom:5%;">
        <select chosen name="" id="projSelect" class="chosen-select" style="display: none;border-color:#5c646f" data-placeholder="Select a Project"
        ng-model="project" data-ng-options="v as v.project_name group by v.group for v in projects track by v.project_id" ng-change="changedValue()">
        <option><option>
        </select>
      </div>
    </div>
  </div>
  <div class="container">
    <form id="navFormID" name="navForm">
      <div class="remove-border dashboard-buttons clearfix">
       <div class="col-lg-12 display-block" style="padding-bottom:3%">
        <div class="col-md-4">
          <a href="#" class="btn vertical-button hover-green" title="" id="viewWebsiteButton" name="webView" ng-disabled="!viewSite" style="width: 300px;line-height:2.6em;border-color:#5c646f" ng-click="navToWebPortal()">
            <span class="glyph-icon icon-separator-vertical">
              <i class="glyph-icon icon-desktop" style="font-size:40px;"></i>
            </span>
            <span class="button-content" style="font-size:150%;" >View Project Website</span>
            <!--color:#3e4855-->
          </a>
        </div>
        <div class="col-md-4">
          <a href="#" class="btn vertical-button hover-orange" title="" id="editProjectButton" style="width: 300px;line-height:2.6em" ng-disabled="!editable" ng-click="navToEdit()">
            <span class="glyph-icon icon-separator-vertical">
              <i class="glyph-icon icon-edit" style="font-size:40px;"></i>
            </span>
            <span class="button-content" style="font-size:150%;">Edit Project</span>
          </a>
        </div>
        <div class="col-md-4">
          <a href="#" class="btn vertical-button hover-purple" title="" style="width: 300px;line-height:2.6em" data-toggle="modal" data-target="#acceptInviteModal">
            <span class="glyph-icon icon-separator-vertical">
              <i class="glyph-icon icon-gift" style="font-size:40px;"></i>
            </span>
            <span class="button-content" style="font-size:150%;">Accept Invitation</span>
          </a>
        </div>
      </div>
      <div class="col-lg-12 display-block" style="padding-bottom:10%">
       <div class="col-md-4">
        <a class="btn vertical-button hover-yellow" title="" style="width: 300px;line-height:2.6em" data-toggle="modal" ng-disabled="!createProj" data-target="#addProjectModal">
          <span class="glyph-icon icon-separator-vertical">
            <i class="glyph-icon icon-flask" style="font-size:40px;"></i>
          </span>
          <span class="button-content" style="font-size:150%;">Add New Project</span>
        </a>
      </div>
      <div class="col-md-4">
        <a href="#" class="btn vertical-button hover-azure" title="" id="deleteProjectButton" style="width: 300px;line-height:2.6em" onclick="ShowDeleteModal()">
          <span class="glyph-icon icon-separator-vertical">
            <i class="glyph-icon icon-trash-o" style="font-size:40px;"></i>
          </span>
          <span class="button-content" style="font-size:150%;">Delete Project</span>
        </a>
      </div>
      <div class="col-md-4">
        <a href="#" class="btn vertical-button hover-blue" title="" style="width: 300px;line-height:2.6em" ng-click="navToGuide()">
          <span class="glyph-icon icon-separator-vertical">
            <i class="glyph-icon icon-mortar-board" style="font-size:40px;"></i>
          </span>
          <span class="button-content" style="font-size:150%;">View Site Guide</span>
        </a>
      </div>
    </div>
  </div>
</form>
</div>


<div class="modal fade" id="addProjectModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content" style="padding:25px">
      <div class="modal-body">
       <h1 style="padding-bottom:25px;text-align:center">Add a new project</h1>
       <h4 style="padding-bottom:10px;text-align:left">Project Name</h4>
       <form id="newProjectForm" name="newProjectForm">
         <div class="form-group" style="padding-bottom:10px">
          <div class="input-group">
            <input type="text" class="form-control" id="newProjectName" name="inputProjectName" placeholder="Ex: Y3K" ng-model="newProjectName" ng-minlength="1" ng-maxlength="255" required ng-class="{'parsley-error' : newProjectForm.inputProjectName.$error.uniqueProjectName && !newProjectForm.inputProjectName.$pristine}" newproject>
          </div>
          <span ng-show="newProjectForm.inputProjectName.$error.uniqueProjectName" style="text-align:left" class="parsley-required">Project name currently in use!</span>
        </div>
        
        <h4 style="padding-bottom:10px;text-align:left">Project Description</h4>
        <div class="form-group" style="padding-bottom:10px">
          <div class="input-group">
            <textarea class="form-control textarea-no-resize textarea-md" id="newProjectDescription" placeholder="Ex: Proteome, Metabolome, and Lipidome Profiling of 174 Single Gene Deletion Strains" ng-model="newProjectDescription" required ng-minlength="1"></textarea>
          </div>
        </div>
        <div ng-init="projTime=true"></div>
        <div class="form-group" style="float:left">
          <label class="radio-inline">
            <input type="radio" id="tempProjectRadio" ng-model="projTime" ng-value="false">
            Temporary Project
          </label>
          <label class="radio-inline">
           <input type="radio" id="permProjectRadio" ng-model="projTime" ng-value="true">
           Permanent Project
         </label>
       </div>

       <div class="form-group" style="padding-bottom:10px">
         <button type="submit" class="btn btn-success" style="float:right" ng-click="addProject()" data-dismiss="modal" target="addProjectModal" ng-disabled="newProjectForm.$invalid">Add Project</button>
         <button type="submit" class="btn btn-danger" style="float:right; margin-right:15px" data-dismiss="modal" target="addProjectModal">Cancel</button>
       </div>
     </form>
   </div>
 </div>
</div>
</div>

<div class="modal fade" id="acceptInviteModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content" style="text-align:center; padding:25px">
      <div class="modal-body">
       <h1 style="padding-bottom:25px;">Accept project invitation</h1>
       <h4 style="padding-bottom:15px;">Please enter the 20-digit alphanumeric code you received via email below</h4>
       <form id="projectInviteForm" name="projectInviteForm">
         <div class="form-group" style="padding-bottom:10px">
          <div class="input-group">
            <input type="text" class="form-control" id="projectInvite" name="projectInviteCode" ng-minlength="20" ng-maxlength="20" 
            placeholder="Project Invitation Code" ng-model="projectInviteCode" required invite>
          </div>
        </div>
      </form>
      <div class="form-group" style="padding-bottom:10px">
       <button type="submit" class="btn btn-success" style="float:right" ng-disabled="projectInviteForm.$invalid" data-dismiss="modal" ng-click="acceptInvitation()">Accept Invitation</button>
       <button type="submit" class="btn btn-danger" style="float:right; margin-right:15px" data-dismiss="modal" target="addProjectModal">Cancel</button>
     </div>
     
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
          <button type="submit" class="btn btn-success" style="float:right;margin-right:0px;margin-top:10px; margin-bottom:10px" data-dismiss="modal" ng-click="dismiss()" target="errorReportModal"><span class="glyph-icon icon-check"></span> Dismiss</button>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="deleteProjectModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content" style="text-align:center; padding:25px">
      <div class="modal-body">
       <h1 style="padding-bottom:25px;">Are you sure?</h1>
       <h4 style="padding-bottom:25px;">This action is permanent and all data associated with '{{selected_project}}' will be lost. Are you sure you want to delete this project?</h4>
       <h4 style="padding-bottom:15px;">To confirm and proceed with deletion type '{{selected_project}}' in the box below and select 'Continue'</h4>
       <form id="deleteProjectForm">
         <div class="form-group" style="padding-bottom:10px">
          <div class="input-group">
            <input type="text" class="form-control" id="deleteProjectName" name="inputDeleteProjectName" placeholder="Project Name" ng-model="userDeleteName">
          </div>
        </div>
        
        <div class="form-group" style="float:left">
         <div class="checkbox">
          <label>
            <input type="checkbox" id="alertCollaboratorsCheckbox" ng-model="alert" checked="true">
            Alert Collaborators
          </label>
        </div>
      </div>

      <div class="form-group" style="padding-bottom:10px">
       <button type="submit" class="btn btn-success" style="float:right" ng-click="deleteProject()" ng-disabled="userDeleteName!=selected_project" data-dismiss="modal" target="deleteProjectModal">Continue</button>
       <button type="submit" class="btn btn-danger" style="float:right; margin-right:15px" data-dismiss="modal" target="deleteProjectModal">Cancel</button>
     </div>
   </form>
 </div>
</div>
</div>
</div>
</div>
</body>
<script>

var paramArray = [];

//New project name validate code

//All code to handle adding a new project and populating the list
function AddProject()
{
  var phpURL = "addProject.php";
  jQuery(document).ready(function () {
    jQuery.ajax({
      type: "POST",
      url: phpURL,
      data : {
       pn: $('#newProjectName').val(),
       pd: $('#newProjectDescription').val(),
       pp: $('#permProjectRadio')[0].checked
     },
     success: function (data) {
       CreateTree($('#newProjectName').val(), data);
       $('#addProjectModal').modal('hide');
       $('#newProjectName').val("");
       $('#newProjectDescription').val("");
       phpURL = "getEditableProjects.php";
       jQuery.ajax({
        url: phpURL,
        success: function (data2) {
          angular.element(document.getElementById('mainPageID')).scope().getProjects();
          $('#newProjectForm').valid();
        }
      });
     }
   });
  });
}

function CreateTree(name, id)
{
  var treeText = "[{\"name\":\"" + name + "\",\n";
  treeText += "\"parent\":\"null\",\n";
  treeText += "\"value\":\"" + id + "\",\n";
  treeText += "\"children\": [\n";
  treeText += "{\n";
  treeText += "\"name\":\"Main\",\n";
  treeText += "\"parent\":\"" + name + "\",\n";
  treeText += "\"value\":\"" + id + "-1B\",\n";
  treeText += "}\n";
  treeText += "]\n";
  treeText += "}\n";
  treeText += "]";

  var name = ($('#projectNameInput').val());

  jQuery(document).ready(function () {
    jQuery.ajax({
      type: "POST",
      url: "addTreeToDatabase.php",
      data: {pi: id, t: moment().format('YYYY-MM-DD HH:mm:ss'), tt: treeText, dtt: treeText, b: 1, s: 0, c: 0, r: 0},
      success: function (data) {
      }
    });
  });
}

function UpdateButtonFunctions()
{
	var phpURL = "getPermissionLevel.php";
	jQuery(document).ready(function () {
    jQuery.ajax({
      type: "POST",
      url: phpURL,
      data : {
       p: $('#projSelect').chosen().val()
     },
     success: function (data) {
       if (data==0)
       {
        $('#viewWebsiteButton').addClass('disabled');
        $('#editProjectButton').addClass('disabled');
        $('#deleteProjectButton').addClass('disabled');
      }
      if (data==1)
      {
        $('#viewWebsiteButton').removeClass('disabled');
        $('#editProjectButton').addClass('disabled');
        $('#deleteProjectButton').addClass('disabled');
      }
      if (data==2)
      {
        $('#viewWebsiteButton').removeClass('disabled');
        $('#editProjectButton').removeClass('disabled');
        $('#deleteProjectButton').addClass('disabled');
      }
      if (data == 3)
      {
        $('#viewWebsiteButton').removeClass('disabled');
        $('#editProjectButton').removeClass('disabled');
        $('#deleteProjectButton').removeClass('disabled');
      }
    }
  });
  });
}

jQuery(document).ready(function(){
  $('#projSelect').on('change', function(e) {
    UpdateButtonFunctions();
  });
});


function ShowDeleteModal()
{
	if($('#projSelect').chosen().val()!="")
	{
		$('#deleteProjectModal').modal('show');
	}
}

function NavToWebPage()
{
  if($('#projSelect').chosen().val()!="")
  {
    window.location = "/Projects/WebDeposition_v3/DV/" + $('#projSelect').chosen().val() + "/index.php";
  }
}

</script>



<script type="text/javascript" src="assets/bootstrap/js/bootstrap.js"></script>