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
Coon Lab Data Online Site Guide
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

<script src="spin.min.js"></script>
<!--
   <script src="scripts/services/d3.js"></script>
   <script src="scripts/services/d3Tip.js"></script> -->


<script type="text/javascript" src="angular-chosen.min.js"></script>
<script type="text/javascript" src="chosen-add-option.js"></script>
<script type="text/javascript" src="assets/widgets/chosen/chosen-demo.js"></script>
<script src="jquery.validate.js"></script>
<script src="moment.js"></script>

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
<script type="text/javascript" src="assets/widgets/tocify/tocify.js"></script>
<script type="text/javascript" src="assets/widgets/sticky/sticky.js"></script>
<link rel="stylesheet" type="text/css" href="assets/widgets/tocify/tocify.css">


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

img.center {
    display: block;
    margin: 0 auto;
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

<script type="text/javascript">
    $(function() {
        var toc = $("#tocify-menu").tocify({context: ".toc-tocify", showEffect: "fadeIn",extendPage:false,selectors: "h2, h3, h4" });
    });
    jQuery(document).ready(function($) {

        /* Sticky bars */

        $(function() { "use strict";

            $('.sticky-nav').hcSticky({
                top: 50,
                innerTop: 50,
                stickTo: 'document'
            });

        });

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

<body>
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

<div class="row" style="padding-top:2%;padding-right:1%;padding-left:1%;padding-bottom:1%"><div class="col-md-2"></div><div class="col-md-10"><h1 style="text-align:center;font-size:52px">Coon Lab Data Online Site Guide</h1></div></div>

<div class="row" style="padding-top:1%;padding-right:2%;padding-left:2%">
    <div class="col-md-3">
        <div class="sticky-nav">
            <div id="tocify-menu"></div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="toc-tocify">
                    <h2 class="mrg20B">What is Coon Lab Data Online?</h2>
                    <p class="mrg20B font-gray-dark" style="line-height: 2em;text-align:justify;padding-left:1%;padding-right:1%">Coon Lab Data Online is a web-based platform designed to enable mass spectrometrists and biological and medical researchers alike to create, customize, and share interactive data visualization portals without having to write any code whatsoever. Our unique framework allows researchers to create new projects and upload generic spreadsheets of mass spectrometry (MS) data from a broad range of MS quantitation packages. Once data has been uploaded, Coon Lab Data Online goes to work and automatically performs statistical calculations and other data analyses behind-the-scenes. Meanwhile, users are able to choose individual charts and graphs from a panel of data visualizations, and Coon Lab Data Online builds all of these selections into a brand-new data exploration portal. Data security is of paramount importance to us and we make sure that all user data remains private, but provide a simple and secure way for users to share their tailor-made web portals with collaborators around the world. Our goal is simple, to provide all researchers with a simple and straightforward way to rapidly explore MS data and gain new biological insight faster than ever.</p>
                    
                    <h2 class="mrg20B">What data can I upload?</h2>
                    <p class="mrg20B font-gray-dark" style="line-height: 2em;text-align:justify;padding-left:1%;padding-right:1%">Our platform takes spreadsheets of quantitative MS data in plain text form. The vast majority of MS processing tools support exporting of peak tables where columns correspond to individual MS experiments, rows corresponds to molecules (protein, metabolite, lipid, etc.), and each cell reflects a quantified abundance from a single MS analysis. Coon Lab Data Online expects that uploaded spreadsheets contain molecular abundances (LFQ intensities, peak areas, etc.) and allows users to group together replicate analyses, and select experimental controls. Most exported peak tables also contain supporting metadata describing each profiled molecule that is useful to reference when exploring data online. Our platform allows users to easily store and access all of this metadata when exploring data online.  </p>

                    <h2 class="mrg20B">How does Coon Lab Data Online organize my data?</h2>
                    <p class="mrg20B font-gray-dark" style="line-height: 2em;text-align:justify;padding-left:1%;padding-right:1%">All data uploaded to a user’s project is stored in a tree structure which reflects the hierarchical organization of project data and can be adapted to almost any MS experiment design. These organizational trees consist of five levels (project, branch, set, condition, and replicate) each of which works to accurately describe a project’s data hierarchy. Here we’ll provide a description of each of the levels, and provide an illustrative example of how a project tree is created for a multi-omic experiment.</p>
                    <h3 class="mrg20B" style="padding-left:1%;padding-right:1%">Example project tree</h3>
                    <p class="mrg20B font-gray-dark" style="line-height: 2em;text-align:justify;padding-left:1%;padding-right:1%">For our example, let’s say that we performed discovery proteomic, metabolomic, and lipidomic experiments on three different yeast knockout strains and a wild-type control, all in biological triplicate, in a project we are calling ‘Amazing Multi-Omic Study.’ Subsequent quantitative data processing should yield three separate peak tables, one for each of the profiled omes. A high-level overview of the inputs and outputs in our experiment is shown below. </br></br> <img src="siteGuideImages/CLDO Images-06.svg" style="width:75%;" class="center"></img> </br>The first node in our tree is a ‘project’ node, fittingly called ‘Amazing Multi-Omic Study.’ This node—at the project level—will contain all of the data that we upload to Coon Lab Data Online for this project. Next we need to create branch nodes. The branch level contains individual nodes which hold all of the data that a user wishes to compare directly. For instance, in this illustrative study we will want to create separate proteomic, metabolomic, and lipidomic branches as the measurements from these distinct omes are often not compared directly. As such we will create separate ‘Protein,’ ‘Metabolite,’ and ‘Lipid’ branches. Our experimental tree–thus far–is shown below.</br> </br> <img src="siteGuideImages/CLDO Images-03.svg" style="width:58%;" class="center"> </br>Each of these branches contains ‘set’ nodes. Each ‘set’ node contains all of the data from a single peak table (i.e., data “set”). We have a single spreadsheet from each profiled ome and will create a new set within each of our three branches. Moving down, each ‘set’ node contains ‘condition’ nodes. Most MS profiling experiments contain replicate analyses often from multiple different conditions. Each ‘condition’ node contains one or more ‘replicate’ nodes, each of which represents a single MS analysis and a single column of peak table data. Finally, within each set of uploaded data we can designate a single ‘condition’ as an experimental control which all other data is normalized and compared against. These control ‘condition’ nodes are displayed in red below. </br> </br> <img src="siteGuideImages/CLDO Images-04.svg" style="width:85%;" class="center"> </br> </p>
                    
                    <h2 class="mrg20B">How do I upload data from a new project?</h2>
                    <p class="mrg20B font-gray-dark" style="line-height: 2em;text-align:justify;padding-left:1%;padding-right:1%">First, create a new project in the main menu by clicking ‘New Project’ and providing a unique name and description. Select the project from the dropdown and click the ‘Edit Project’ button. This will take you to a project overview dashboard which displays all project activity. From there, click the ‘Upload Data’ link in the side bar to navigate to the upload page where you can add new data to your project. First, pick a unique name for your data set and enter it in the ‘Specify Set Name’ text box. Then, choose which branch you want your file data to be added to. You can create new branches from this window in the ‘Create New Branch’ box if you wish. After naming your new set and selecting a branch, click the ‘Choose Files’ button and select your file from the browser. Note: all uploaded spreadsheets must contain .txt extensions. After selecting your file it is automatically transferred to the Coon Lab Data Online server and all headers will be displayed in the ‘Organize/Edit Headers’ list. Tab individual headers into their associated ‘Unique Identifier Column,’ ‘Feature Descriptor Columns,’ and/or ‘Quantitative Data Columns.’ For each quantitative data column you must provide a replicate and condition name. Replicates having shared condition names will be grouped together for downstream statistical processing. </p>
                    <p class="mrg20B font-gray-dark" style="line-height: 2em;text-align:justify;padding-left:1%;padding-right:1%">After organizing your peak table, you can optionally specify a sample type (organism) and indicate a column containing standard molecular identifiers (i.e., Uniprot IDs, gene names, etc.). Providing this information will enable analyses such as GO enrichment. </p>
                    <p class="mrg20B font-gray-dark" style="line-height: 2em;text-align:justify;padding-left:1%;padding-right:1%">In the ‘Data Processing’ panel you can apply an optional filter to your data to remove measurements observed across a small number of replicates or conditions. Additionally, here you can apply an optional log2-transformation to your quantitative data if it has not already been transformed. As a note, Coon Lab Data Online expects all data to be log2-transformed for downstream statistical analysis. Also, you can choose to impute missing values for conditions having more than one replicates. Coon Lab Data Online imputes missing values by randomly sampling from a normal distribution of measurements. Our algorithm analyzes your uploaded data and finds the optimal mean, and standard deviation for said distribution.</p>
                    <p class="mrg20B font-gray-dark" style="line-height: 2em;text-align:justify;padding-left:1%;padding-right:1%">After providing all input data, click the ‘Save Data’ button which will display an upload review modal. Make sure all settings are correct before clicking ‘Confirm Data Upload.’ Coon Lab Data Online will check your inputs and will return an error report. If no errors are found, you can again confirm the upload and statistical processing will begin immediately.</p>

                    <h2 class="mrg20B">How do I know if my data has been uploaded?</h2>
                    <p class="mrg20B font-gray-dark" style="line-height: 2em;text-align:justify;padding-left:1%;padding-right:1%">You can monitor the progress of all file uploads by clicking the 'Cog' icon at the top of any of the edit project pages. Under the ‘In Queue’ heading, a progress bar indicates the processing status of each uploaded file. Once a file upload has completed, the process will be displayed under the ‘Completed’ heading, and all data can be accessed in the project website.</p>

                    <h2 class="mrg20B">Can I see my previous file upload settings?</h2>
                    <p class="mrg20B font-gray-dark" style="line-height: 2em;text-align:justify;padding-left:1%;padding-right:1%">Yes! You can review all file upload settings by clicking the ‘Params’ button at the top of any of the edit project pages.</p>

                    <h2 class="mrg20B">What do I do if I make a mistake in uploading my data?</h2>
                    <p class="mrg20B font-gray-dark" style="line-height: 2em;text-align:justify;padding-left:1%;padding-right:1%">You can edit the data uploaded to each project endlessly in the ‘Edit Existing Data’ page. Here you can reselect controls, change sample types, update data filters, reset log2-transformation and missing value imputation settings, and you can completely delete a set (peak table). Depending on the mistake you can attempt to fix it here, or just delete the data and begin again!</p>
       
                    <h2 class="mrg20B">How do I add visualizations to my web portal?</h2>
                    <p class="mrg20B font-gray-dark" style="line-height: 2em;text-align:justify;padding-left:1%;padding-right:1%">After you have uploaded data to your project you can customize the charts and graphs in your web portal inside the ‘Manage Visualizations’ page. This page contains a menu of available visualizations that can be turned on and off by clicking the ‘Enabled’ slider. A compatibility check is performed automatically to determine which visualizations will work with your data. After making your selections click ‘Update Project Website’ which will add these to your web portal automatically.</p>

                    <h2 class="mrg20B">Can I share my portal with other collaborators?</h2>
                    <p class="mrg20B font-gray-dark" style="line-height: 2em;text-align:justify;padding-left:1%;padding-right:1%">Absolutely! Under the ‘Invite Collaborators’ page you can send invitations to as many collaborators as you like. Simply enter a list of appropriate email addresses and each collaborator will receive a unique 20-digit code. By entering that code under ‘Accept Invitation’ in the main menu they will be granted access. You can choose the appropriate permission level for each collaborator. Read-only allows collaborators to view the web portal, but not make any edits. Read & Edit will allow your collaborators to view the website and make changes to the underlying data. Project Owner permissions will allow collaborators to do all of those things, and they will also have the power to delete the project entirely if they wish. After sending each invitation a new entry will appear in the sent invitations panel. The invitation will remain red until your collaborator accepts, at which point it will turn green and display their full name.</p>
                    
                    <h2 class="mrg20B">How do I delete my web portal and is it gone forever?</h2>
                    <p class="mrg20B font-gray-dark" style="line-height: 2em;text-align:justify;padding-left:1%;padding-right:1%">You can delete your project’s web portal by selecting it from the main menu drop down, clicking ‘Delete Project’ and entering the name in the modal window. Once you do this your website and all associated data will be deleted permanently from the server and cannot be recovered. However, you can recreate web portals at any time.</p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>