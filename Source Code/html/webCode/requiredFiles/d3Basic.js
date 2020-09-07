
var condCount = 0;

angular.module('coonDataApp')
.directive('projectHierarchyTree', [ function (){

  var margin_tree = null;
  var width_tree = null;
  var height_tree = null;
  var duration_tree = 1000;
  var root_tree=null;
  var tree = null;
  var diagonal_tree = null;
  var tree_chart = null;
  var tree_i=0;
      //margin stuff here
      //angular.element(window)[0].innerWidth
      var full_page_width = $('#page-content')[0].clientWidth;
      margin_tree = {top: (full_page_width * .02), right:(full_page_width * .02), 
        bottom: (full_page_width * .01), left: (full_page_width * .02)},
        width_tree = (full_page_width * .85) - margin_tree.left - margin_tree.right,
        height_tree = (full_page_width * .50) - margin_tree.top - margin_tree.bottom;


        return {
          restrict: 'EA',
          scope: {
            data: "=",
            label: "@",
            onClick: "&"
          },
          link: function(scope, iElement, iAttrs) {

            var tree_chart =  d3.select('#treeColumn').append("svg")
            .attr("width", width_tree + margin_tree.right + margin_tree.left)
            .attr("height", height_tree + margin_tree.top + margin_tree.bottom)
            .attr("id", "hierarchyTreePane")
            .append("g");

            var project_label = tree_chart.append("text").text("Project").attr("y",100).attr("x",100).attr("opacity", "0").attr("font-size","1em").attr("id", "projectLabelTree").attr("font-style", "italic");
            var branch_label = tree_chart.append("text").text("Branches").attr("y",100).attr("x",100).attr("opacity", "0").attr("font-size","1em").attr("id", "branchLabelTree").attr("font-style", "italic");
            var set_label = tree_chart.append("text").text("Sets").attr("y",100).attr("x",100).attr("opacity", "0").attr("font-size","1em").attr("id", "setLabelTree").attr("font-style", "italic");
            var condition_label = tree_chart.append("text").text("Conditions").attr("y",100).attr("x",100).attr("opacity", "0").attr("font-size","1em").attr("id", "conditionLabelTree").attr("font-style", "italic");
            var replicate_label = tree_chart.append("text").text("Replicates").attr("y",100).attr("x",100).attr("opacity", "0").attr("font-size","1em").attr("id", "replicateLabelTree").attr("font-style", "italic");
             
            window.onresize = function() {
              scope.$root.$broadcast('myresize');
            };

          scope.$on('myresize', function()
          {
              scope.update(scope.data);
          });
            
            
          // watch for data changes and re-render
          scope.$watch('data', function(newVals, oldVals) {
           scope.update(scope.data);
         });

          var repCount = 0;

          scope.update= function(data)
          {
            if (data != null)
            {
              treeDisplayed = true;
              repCount = 0;
              condCount = 0;
              scope.recurse(scope.$parent.root_tree);
              var tmpHeight = Math.max((repCount*20),500);
              tmpHeight = Math.max(tmpHeight, (condCount* 20));
              var full_page_width = $('#page-content')[0].clientWidth;
              width_tree = Math.max((full_page_width * 0.85),400) - margin_tree.left - margin_tree.right;
              tree_height = tmpHeight - margin_tree.top - margin_tree.bottom;

              $('#hierarchyTreePane').attr("height", tmpHeight + margin_tree.top + margin_tree.bottom);
              $('#hierarchyTreePane').attr("width", "100%");

              //width_tree = ($('#hierarchyTreePane').width()) ;

              tree = d3.layout.tree()
              .size([tmpHeight, width_tree]);

              diagonal_tree = d3.svg.diagonal()
              .projection(function(d) { return [d.y, d.x]; });

              root_tree = scope.$parent.root_tree;

              var nodes = tree.nodes(root_tree).reverse(),
              links = tree.links(nodes);


            // Normalize for fixed-depth.
              var maxDepth = d3.max(nodes, function(d){ return d.depth;});

            if (maxDepth == 4)
            {
              nodes.forEach(function(d) { d.y = ((d.depth) *(width_tree*.94)/(Math.max(maxDepth,1))) + margin_tree.left + 110; });
            }
            else
            {
              nodes.forEach(function(d) { d.y = ((d.depth) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110; });
            }

            switch(maxDepth)
            {
                case 0:
                  project_label.transition().duration(1000).attr("opacity", 1).attr("y", tree_height + 55).attr("x",((0) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
                  branch_label.transition().duration(1000).attr("opacity", 0).attr("y", tree_height + 55).attr("x",((1) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
                  set_label.transition().duration(1000).attr("opacity", 0).attr("y", tree_height + 55).attr("x",((2) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
                  condition_label.transition().duration(1000).attr("opacity", 0).attr("y", tree_height + 55).attr("x",((3) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
                  replicate_label.transition().duration(1000).attr("opacity", 0).attr("y", tree_height + 55).attr("x",((4) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
                  break;
                case 1:
                   project_label.transition().duration(1000).attr("opacity", 1).attr("y", tree_height + 55).attr("x",((0) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
                  branch_label.transition().duration(1000).attr("opacity", 1).attr("y", tree_height + 55).attr("x",((1) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
                  set_label.transition().duration(1000).attr("opacity", 0).attr("y", tree_height + 55).attr("x",((2) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
                  condition_label.transition().duration(1000).attr("opacity", 0).attr("y", tree_height + 55).attr("x",((3) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
                  replicate_label.transition().duration(1000).attr("opacity", 0).attr("y", tree_height + 55).attr("x",((4) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
                  break;
                case 2:
                  project_label.transition().duration(1000).attr("opacity", 1).attr("y", tree_height + 55).attr("x",((0) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
                  branch_label.transition().duration(1000).attr("opacity", 1).attr("y", tree_height + 55).attr("x",((1) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
                  set_label.transition().duration(1000).attr("opacity", 1).attr("y", tree_height + 55).attr("x",((2) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
                  condition_label.transition().duration(1000).attr("opacity", 0).attr("y", tree_height + 55).attr("x",((3) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
                  replicate_label.transition().duration(1000).attr("opacity", 0).attr("y", tree_height + 55).attr("x",((4) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
                  break;
                case 3:
                  project_label.transition().duration(1000).attr("opacity", 1).attr("y", tree_height + 55).attr("x",((0) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
                  branch_label.transition().duration(1000).attr("opacity", 1).attr("y", tree_height + 55).attr("x",((1) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
                  set_label.transition().duration(1000).attr("opacity", 1).attr("y", tree_height + 55).attr("x",((2) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
                  condition_label.transition().duration(1000).attr("opacity", 1).attr("y", tree_height + 55).attr("x",((3) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 105).attr("text-anchor","middle").transition().duration(1000);
                  replicate_label.transition().duration(1000).attr("opacity", 0).attr("y", tree_height + 55).attr("x",((4) *(width_tree*1.04)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
                  break;
                case 4:
                  project_label.transition().duration(1000).attr("opacity", 1).attr("y", tree_height + 55).attr("x",((0) *(width_tree*.94)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
                  branch_label.transition().duration(1000).attr("opacity", 1).attr("y", tree_height + 55).attr("x",((1) *(width_tree*.94)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
                  set_label.transition().duration(1000).attr("opacity", 1).attr("y", tree_height + 55).attr("x",((2) *(width_tree*.94)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
                  condition_label.transition().duration(1000).attr("opacity", 1).attr("y", tree_height + 55).attr("x",((3) *(width_tree*.94)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
                  replicate_label.transition().duration(1000).attr("opacity", 1).attr("y", tree_height + 55).attr("x",((4) *(width_tree*.94)/(Math.max(maxDepth,1))) + margin_tree.left + 110).attr("text-anchor","middle").transition().duration(1000);
                  break;
            }


    // Update the nodes?
    var node = tree_chart.selectAll("g.node")
    .data(nodes, function(d) { return d.id || (d.id = ++tree_i); });

    // Enter any new nodes at the parent's previous position.
    var nodeEnter = node.enter().append("g")
    .attr("class", "node")
    .attr("transform", function(d) { return "translate(" + data.y0 + "," + data.x0 + ")"; })
    .on("click", scope.click_tree);


    nodeEnter.append("circle")
    .attr("r", 1e-6)
    .style("fill", function(d) { if (d.control=="TRUE") { return d._children ? "#FF9292" : "#fff"; } return d._children ? "lightsteelblue" : "#fff"; })
    .style("stroke", function(d){ if (d.control=="TRUE"){return "#FF2525";} return "steelblue"; });

    nodeEnter.append("text")
    .attr("x", function(d) { return d.children || d._children ? -13 : 13; })
    .attr("dy", ".2em")
    .attr("class","nodeText")
    .attr("text-anchor", function(d) { return d.children || d._children ? "end" : "start"; })
    .text(function(d) { return (d.value); })
    .style("fill-opacity", 1e-6);

    // Transition nodes to their new position.
    var nodeUpdate = node.transition()
    .duration(duration_tree)
    .attr("transform", function(d) { return "translate(" + d.y + "," + d.x + ")"; });

    nodeUpdate.select("circle")
    .attr("r", function (d){ return 10 * (width_tree/1600)})
    .style("fill", function(d) {  if (d.control=="TRUE") { return d._children ? "#FF9292" : "#fff"; } return d._children ? "lightsteelblue" : "#fff"; })
    .style("stroke", function(d) {if (d.control=="TRUE"){return "#FF2525";} return "steelblue"; });

    nodeUpdate.select("text")
    .style("fill-opacity", .9);

    // Transition exiting nodes to the parent's new position.
    var nodeExit = node.exit().transition()
    .duration(duration_tree)
    .attr("transform", function(d) { return "translate(" + data.y + "," + data.x + ")"; })
    .remove();

    nodeExit.select("circle")
    .attr("r", 1e-6);

    nodeExit.select("text")
    .style("fill-opacity", 1e-6);

    // Update the links?
    var link = tree_chart.selectAll("path.link")
    .data(links, function(d) { return d.target.id; });

    // Enter any new links at the parent's previous position.
    link.enter().insert("path", "g")
    .attr("class", "link")
    .attr("d", function(d) {
      var o = {x: data.x0, y: data.y0};
      return diagonal_tree({source: o, target: o});
    });

    // Transition links to their new position.
    link.transition()
    .duration(duration_tree)
    .attr("d", diagonal_tree);

    // Transition exiting nodes to the parent's new position.
    link.exit().transition()
    .duration(duration_tree)
    .attr("d", function(d) {
      var o = {x: data.x, y: data.y};
      return diagonal_tree({source: o, target: o});
    })
    .remove();

    // Stash the old positions for transition.
    nodes.forEach(function(d) {
      d.x0 = d.x;
      d.y0 = d.y;
    });
    tree_chart.selectAll('.nodeText').each(scope.insertLinebreaks);
    if (spinner!= null)
    {
      spinner.stop();
    }
  }

}

scope.insertLinebreaks = function (d) {
  if (d.parent== "null")
  {
    var el = d3.select(this);
    var words = d.value.toString().split(' ');
    el.text('');

    for (var i = 0; i < words.length; i++) {
      var tspan = el.append('tspan').text(words[i] + " ");
      if (el.text().length> 15)
        tspan.attr('x', 0).attr('dy', '15');
  }
}
}

scope.shortName = function(data)
{
  var returnString = "";
  var tmpString = "";
  var parts = data.split(" ");
  angular.forEach(parts, function function_name(argument)
  {
    tmpString += argument + " ";
    returnString += argument + " ";
    if (tmpString.length > 15)
    {
      returnString += "\r\n";
      tmpString = "";
    }
  });
  return returnString;
}

scope.recurse= function (data)
{
  if (data.name.charAt(data.name.length-1)=='C')
  {
    condCount++;
  }
  if (data.children != null)
  {
    angular.forEach(data.children, function function_name (argument) {
     scope.recurse(argument);

   });
  }
  else
  {
      if (data.children == null && data._children==null)
      {
      repCount++;
    }
  }

}

scope.click_tree = function(d) {
  if (d.children) {
    d._children = d.children;
    d.children = null;
  } else {
    d.children = d._children;
    d._children = null;
  }
  scope.update(d);
}

}       
};
}]);



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




function GetShortString(inString)
{
    var parts = inString.split(" ");
    var outString = "";
    var currCount = "";
    var currString = "";
    parts.forEach(function(d)
    {
        outString += d + " ";
        currString += d + " ";
        if (currString.length>50)
        {
            outString += "<br>";
            currString = "";
        }
    });
    return outString;
}

d3.selection.prototype.moveToFront = function() {
  return this.each(function(){
    this.parentNode.appendChild(this);
  });
};


