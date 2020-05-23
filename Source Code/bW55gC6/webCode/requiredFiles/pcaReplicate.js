//Put all PCA stuff in here
var margin_pcarep=null;
var width_pcarep=null;
var height_pcarep=null;
var x_pcarep=null;
var y_pcarep=null;
var xAxis_pcarep=null;
var yAxis_pcarep=null;
var zoom_pcarep=null;
var chart_pcarep=null;
var tip_pcarep=null;
var max_x_pcarep = 5;
var min_x_pcarep = -5;
var max_y_pcarep = 10;
var min_y_pcarep = -10;
var y_label_pcarep = null;
var pcarep_Y_10 = null;
var pcarep_Y_parentheses = null;
var pcarep_Y_p = null;
var pcarep_Y_value = null;
var x_label_pcarep = null;
var pcarep_x_abundance = null;
var pcarep_x_2 = null;
var pcarep_x_parentheses = null;
var pcarep_x_condition = null;
var pcarep_x_control = null;

angular.module('coonDataApp')
.directive('pcaReplicate',  ['$timeout', function ($timeout) {
  return {
          restrict: 'EA',
          scope: {
            data: "=",
            label: "@",
            onClick: "&",
            pcarepXAxisName: '@attr1',
            pcarepYAxisName: '@attr2',
            pcarepXAxisFraction: '@attr3',
            pcarepYAxisFraction: '@attr4'
          },

          link: function(scope, iElement, iAttrs) {

           var full_page_width = $('#page-content')[0].clientWidth;
            margin_pcarep = {top: Math.max((full_page_width * .025), 20), right:Math.max((full_page_width * 0.075), 50),
            bottom: Math.max((full_page_width * 0.075), 50), left: Math.max((full_page_width * 0.075), 50)},
            width_pcarep = Math.max((full_page_width * 0.80),350) - margin_pcarep.left - margin_pcarep.right,
            height_pcarep = (.7 * width_pcarep) - margin_pcarep.top - margin_pcarep.bottom;

               x_pcarep = d3.scale.linear()
            .range([0, width_pcarep])
            .domain([min_x_pcarep, max_x_pcarep]);

            y_pcarep = d3.scale.linear()
            .range([height_pcarep, 0])
            .domain([min_y_pcarep, max_y_pcarep]);

            xAxis_pcarep = d3.svg.axis()
            .scale(x_pcarep)
            .orient("bottom");

            yAxis_pcarep = d3.svg.axis()
            .scale(y_pcarep)
            .orient("left");

            zoom_pcarep = d3.behavior.zoom()
            .x(x_pcarep)
            .y(y_pcarep)
            .scaleExtent([1, 50])
            .on("zoom", zoomed_pcarep);

            chart_pcarep = d3.select('#pcaRepColumn').append("svg").attr("id", "pcaRepSVG")
            .attr("height", height_pcarep + margin_pcarep.top + margin_pcarep.bottom)
            .attr("width", width_pcarep + margin_pcarep.left + margin_pcarep.right)
            .call(zoom_pcarep).on("dblclick.zoom", null)
            .append("g")
            .attr("id", "chart_pcarep_body")
            .attr("transform", "translate(" + margin_pcarep.left + "," + margin_pcarep.top + ")")
            .attr("width", width_pcarep)
            .attr("height", height_pcarep);


            chart_pcarep.append("g")
            .attr("class", "x axis")
            .attr("transform", "translate(0," + height_pcarep + ")")
            .call(xAxis_pcarep);

            chart_pcarep.append("g")
            .attr('transform', 'translate(0,0)')
            .attr("class", "y axis")
            .call(yAxis_pcarep);

            chart_pcarep.append("svg:clipPath")
            .attr("id", "clip_pcarep")
            .append("svg:rect")
            .attr("id", "clip-rect-pcarep")
            .attr("x", "0")
            .attr("y", "0")
            .attr('width', width_pcarep)
            .attr('height', height_pcarep)
            ;

            tip_pcarep = d3.tip()
            .attr('class', 'd3-tip')
            .offset([-10, 0]);

            chart_pcarep.call(tip_pcarep);

            y_label_pcarep = chart_pcarep.append("text")
            .attr("id", "y_label_pcarep")
            .attr("class", "y_label_pcarep")
            .attr("text-anchor", "end")
            .attr("y", -40)
            .attr("x","0")
            .attr("transform", "rotate(-90)")
            .style("text-transform", "none")
            .text(scope.pcarepYAxisName + " (" + scope.pcarepYAxisFraction + "% Variance)");

            x_label_pcarep = chart_pcarep.append("text")
            .attr("id", "x_label_pcarep")
            .attr("text-anchor", "end")
            .attr("x", width_pcarep)
            .attr("y", height_pcarep + 40)
            .style("text-transform", "none")
            .attr("class", "y_label")
            .text(scope.pcarepXAxisName + " (" + scope.pcarepXAxisFraction + "% Variance)");


          scope.$on('myresize', function()
          {                         

            var full_page_width =  $('#page-content')[0].clientWidth;
             margin_pcarep = {top: Math.max((full_page_width * .025), 20), right:Math.max((full_page_width * 0.075), 50),
             bottom: Math.max((full_page_width * 0.075), 50), left: Math.max((full_page_width * 0.075), 50)};
             width_pcarep = Math.max((full_page_width * 0.80),350) - margin_pcarep.left - margin_pcarep.right;
             height_pcarep = (.7 * width_pcarep) - margin_pcarep.top - margin_pcarep.bottom;

             chart_pcarep.attr("height", height_pcarep + margin_pcarep.top + margin_pcarep.bottom)
            .attr("width", width_pcarep + margin_pcarep.left + margin_pcarep.right)
            ;

            $('#pcaRepSVG').attr("height", height_pcarep + margin_pcarep.top + margin_pcarep.bottom)
            .attr("width", width_pcarep + margin_pcarep.left + margin_pcarep.right);
            
            x_pcarep = d3.scale.linear()
            .range([0, width_pcarep])
            .domain([min_x_pcarep, max_x_pcarep]);

            y_pcarep = d3.scale.linear()
            .range([height_pcarep, 0])
            .domain([min_y_pcarep, max_y_pcarep]);

            xAxis_pcarep = d3.svg.axis()
            .scale(x_pcarep)
            .orient("bottom");

            yAxis_pcarep = d3.svg.axis()
            .scale(y_pcarep)
            .orient("left");

            zoom_pcarep
            .x(x_pcarep)
            .y(y_pcarep)
            .scaleExtent([1, 50])
           ;

         
         $("#chart_pcarep_body")
            .attr("width", width_pcarep)
            .attr("height", height_pcarep)
            .attr("transform", "translate(" + margin_pcarep.left + "," + margin_pcarep.top + ")");

              x_label_pcarep
              .attr("x", width_pcarep)
              .attr("y", height_pcarep+40);

              $('#clip-rect-pcarep')
            .attr("x", "0")
            .attr("y", "0")
            .attr('width', width_pcarep)
            .attr('height', height_pcarep)
            ;

            chart_pcarep.selectAll("circle")
            .attr("cx", function (d) {
              return x_pcarep(d.pc_x_vector);
            })
            .attr("cy", function (d) {
              return y_pcarep((d.pc_y_vector));
            });

           chart_pcarep.selectAll("g.y.axis")
          .transition()
          .duration(0)
          .attr('transform', 'translate(0,0)')
          .call(yAxis_pcarep);

          chart_pcarep.selectAll("g.x.axis")
          .transition()
          .duration(0)
          .attr("transform", "translate(0," + height_pcarep + ")")
          .call(xAxis_pcarep);
        
          });

        	scope.$watch('data', function() { 
            scope.update(scope.data);
          });

         	scope.$watch('pcarepXAxisName', function() { 
            scope.updateAxisLabels();
          });

             scope.$watch('pcarepXAxisFraction', function() { 
            scope.updateAxisLabels();
          });

            scope.$watch('pcarepYAxisName', function() { 
             scope.updateAxisLabels();
          });

            scope.$watch('pcarepYAxisFraction', function() { 
             scope.updateAxisLabels();
          });

        scope.updateAxisLabels = function()
        {
        	y_label_pcarep.text(scope.pcarepYAxisName + " (" + scope.pcarepYAxisFraction + "% Variance)");
            x_label_pcarep.text(scope.pcarepXAxisName + " (" + scope.pcarepXAxisFraction + "% Variance)");
        }

          scope.update= function(data)
          {
            if (spinner != null)
            {
              spinner.stop();
            }
            if (data.length > 0)
            {
             data.forEach(function (d) {
              d.opacity = 0.95;
              d.si = (16);
              d.c = scope.$parent.conditionColorDict[d.condition_name];
              d.x = +d.pc_x_vector;
              d.y = +d.pc_y_vector;
              d.i = d.replicate_name;
              d.sw = "1px";

          d.transition = 2100;
          d.delay = 0;
        });

             var minY = d3.min(data, function (d) {
              return d.y;
            });
             var maxY = d3.max(data, function (d) {
              return d.y;
            });
             var minX = d3.min(data, function (d) {
              return d.x;
            });
             var maxX = d3.max(data, function (d) {
              return d.x;
            });
              var xRange = maxX-minX;
              var yRange = maxY-minY;
              minX-= (xRange * 0.05);
              maxX+= (xRange * 0.05);
              maxY+= (yRange * 0.05);
              minY-= (yRange * 0.05);
              min_x_pcarep = minX;
              max_x_pcarep = maxX;
              min_y_pcarep = minY;
              max_y_pcarep = maxY;
             

             x_pcarep = d3.scale.linear().range([0,width_pcarep])
             .domain([min_x_pcarep,max_x_pcarep]);
             xAxis_pcarep.scale(x_pcarep).orient("bottom");
              y_pcarep = d3.scale.linear().range([height_pcarep,0])
             .domain([min_y_pcarep,max_y_pcarep]);
             yAxis_pcarep.scale(y_pcarep).orient("left");

             yAxis_pcarep.scale(y_pcarep);
             xAxis_pcarep.scale(x_pcarep);
             zoom_pcarep.x(x_pcarep);
             zoom_pcarep.y(y_pcarep);

             chart_pcarep.selectAll("circle")
             .data(data, function (d) {
              return d.i;
            })
             .transition()
             .duration(function (d) {
              return d.transition;
            })
             .delay(function(d)
             {
               return d.delay;
             })
             .attr("cx", function (d) {
              return x_pcarep(d.pc_x_vector);
            })
             .attr("cy", function (d) {
              return y_pcarep(d.pc_y_vector);
            })
             .attr("r", function (d) {
              return (d.si / 2);
            })
             .style("fill", function (d) {
              return d.c
            })
             .style("opacity", function (d) {
              return d.opacity
            })
             .style("stroke-width", function(d)
             {
              return d.sw;
            })
             .style("stroke", function(d)
             {
              return "black";
            });

             chart_pcarep.append("g").attr("clip-path", "url(#clip_pcarep)").selectAll("circle")
             .data(data, function(d){return d.i;})
             .enter()
             .append("circle")
             .attr("r", function (d) {
              return (d.si / 2);
            })
             .attr("cx", x_pcarep(0))
             .attr("cy", y_pcarep(0))
             .style("fill", function (d) {
              return d.c;
            })
             .style("opacity", function (d) {
              return d.opacity
            })
             .style("stroke-width", function(d)
             {
              return d.sw;
            })
             .style("stroke", function(d)
             {
              return "black";
            }).attr("class", "myCircle")
             .transition()
             .duration(function(d){
              return d.transition;
            })
             .delay(function(d)
             {
               return d.delay;
             })
             .attr("cx", function (d) {
              return x_pcarep(d.pc_x_vector);
            })
             .attr("cy", function (d) {
              return y_pcarep(d.pc_y_vector);
            })
             .attr("r", function (d) {
              return (d.si / 2);
            })
             .style("fill", function (d) {
              return d.c
            })
             .style("stroke-width", function(d)
             {
              return d.sw;
            })
             .style("stroke", function(d)
             {
              return "black";
            })
             .style("opacity", function (d) {
              return d.opacity
            });

            chart_pcarep.selectAll("circle")
        .on("mouseover", function (d) {
            d3.select(this).style("stroke", "black").style("stroke-opacity", 1).style("opacity", 1).style("fill", "#FFC10B").style("stroke-width", "2px");
            tip_pcarep.html(function (i) {
                var namePart = "<strong style='color:dodgerblue'>Replicate Name:</strong> <span style='color:white'>" + d.replicate_name + "</span> <br><strong style='color:dodgerblue'>Condition Name:</strong> <span style='color:white'>" + d.condition_name + "</span> <br><br>";
                namePart += "<strong style='color:dodgerblue'>" + scope.pcarepXAxisName + " Scaled Vector: </strong> <span style='color:white'>" + d.pc_x_vector + "</span> <br>";
                namePart += "<strong style='color:dodgerblue'>" + scope.pcarepYAxisName + " Scaled Vector: </strong> <span style='color:white'>" + d.pc_y_vector + "</span> <br><br>";
                namePart += "<strong style='color:dodgerblue'>" + scope.pcarepXAxisName + " Variance Fraction: </strong> <span style='color:white'>" + d.pc_x_fraction + "</span> <br>";
                namePart += "<strong style='color:dodgerblue'>" + scope.pcarepYAxisName + " Variance Fraction: </strong> <span style='color:white'>" + d.pc_y_fraction + "</span>";
                return (namePart)});
            d3.select(this).moveToFront();
            tip_pcarep.show();
        })
        .on("mouseout", function (d) {
            {
                d3.select(this).style("stroke", "black").style("stroke-opacity", 1).style("opacity", d.opacity).style("fill", d.c).style("stroke-width", "1px");
            }

            tip_pcarep.hide();
        });

            chart_pcarep.selectAll("circle")
            .data(data, function(d){return d.i;})
            .exit()
            .remove();

            chart_pcarep.selectAll("g.y.axis")
            .transition()
            .duration(2100)
            .call(yAxis_pcarep);

            chart_pcarep.selectAll("g.x.axis")
            .transition()
            .duration(2100)
            .call(xAxis_pcarep);
            }

}
    }
    }

}]);

function zoomed_pcarep() {
    var panX = d3.event.translate[0];
    var panY = d3.event.translate[1];
    var scale = d3.event.scale;

    panX = panX > 0 ? 0 : panX;
    var maxX = -(scale - 1) * width_pcarep - 10;
    panX = panX < maxX ? maxX : panX;

    panY = panY > 0 ? 0 : panY;
    var maxY = -(scale - 1) * height_pcarep - 10;
    panY = panY < maxY ? maxY : panY;

    zoom_pcarep.translate([panX, panY]);
    chart_pcarep.select(".x.axis").call(xAxis_pcarep);
    chart_pcarep.select(".y.axis").call(yAxis_pcarep);
    chart_pcarep.selectAll("circle")
        .attr("cx", function (d) {
            return x_pcarep(d.pc_x_vector);
        })
        .attr("cy", function (d) {
            return y_pcarep(d.pc_y_vector);
        })
        .attr("r", function (d) {
            return (d.si / 2);
        });
}