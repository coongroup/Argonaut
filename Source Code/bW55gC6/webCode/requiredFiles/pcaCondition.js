//Put all PCA stuff in here
var margin_pca=null;
var width_pca=null;
var height_pca=null;
var x_pca=null;
var y_pca=null;
var xAxis_pca=null;
var yAxis_pca=null;
var zoom_pca=null;
var chart_pca=null;
var tip_pca=null;
var max_x_pca = 5;
var min_x_pca = -5;
var max_y_pca = 10;
var min_y_pca = -10;
var y_label_pca = null;
var pca_Y_10 = null;
var pca_Y_parentheses = null;
var pca_Y_p = null;
var pca_Y_value = null;
var x_label_pca = null;
var pca_x_abundance = null;
var pca_x_2 = null;
var pca_x_parentheses = null;
var pca_x_condition = null;
var pca_x_control = null;

angular.module('coonDataApp')
.directive('pcaCondition',  ['$timeout', function ($timeout) {
  return {
          restrict: 'EA',
          scope: {
            data: "=",
            label: "@",
            onClick: "&",
            pcaXAxisName: '@attr1',
            pcaYAxisName: '@attr2',
            pcaXAxisFraction: '@attr3',
            pcaYAxisFraction: '@attr4'
          },

          link: function(scope, iElement, iAttrs) {

           var full_page_width = $('#page-content')[0].clientWidth;
            margin_pca = {top: Math.max((full_page_width * .025), 20), right:Math.max((full_page_width * 0.075), 50),
            bottom: Math.max((full_page_width * 0.075), 50), left: Math.max((full_page_width * 0.075), 50)},
            width_pca = Math.max((full_page_width * 0.80),350) - margin_pca.left - margin_pca.right,
            height_pca = (.7 * width_pca) - margin_pca.top - margin_pca.bottom;

               x_pca = d3.scale.linear()
            .range([0, width_pca])
            .domain([min_x_pca, max_x_pca]);

            y_pca = d3.scale.linear()
            .range([height_pca, 0])
            .domain([min_y_pca, max_y_pca]);

            xAxis_pca = d3.svg.axis()
            .scale(x_pca)
            .orient("bottom");

            yAxis_pca = d3.svg.axis()
            .scale(y_pca)
            .orient("left");

            zoom_pca = d3.behavior.zoom()
            .x(x_pca)
            .y(y_pca)
            .scaleExtent([1, 50])
            .on("zoom", zoomed_pca);

            chart_pca = d3.select('#pcaCondColumn').append("svg").attr("id", "pcaSVG")
            .attr("height", height_pca + margin_pca.top + margin_pca.bottom)
            .attr("width", width_pca + margin_pca.left + margin_pca.right)
            .call(zoom_pca).on("dblclick.zoom", null)
            .append("g")
            .attr("id", "chart_pca_body")
            .attr("transform", "translate(" + margin_pca.left + "," + margin_pca.top + ")")
            .attr("width", width_pca)
            .attr("height", height_pca);


            chart_pca.append("g")
            .attr("class", "x axis")
            .attr("transform", "translate(0," + height_pca + ")")
            .call(xAxis_pca);

            chart_pca.append("g")
            .attr('transform', 'translate(0,0)')
            .attr("class", "y axis")
            .call(yAxis_pca);

            chart_pca.append("svg:clipPath")
            .attr("id", "clip_pca")
            .append("svg:rect")
            .attr("id", "clip-rect-pca")
            .attr("x", "0")
            .attr("y", "0")
            .attr('width', width_pca)
            .attr('height', height_pca)
            ;

            tip_pca = d3.tip()
            .attr('class', 'd3-tip')
            .offset([-10, 0]);

            chart_pca.call(tip_pca);

            y_label_pca = chart_pca.append("text")
            .attr("id", "y_label_pca")
            .attr("class", "y_label_pca")
            .attr("text-anchor", "end")
            .attr("y", -40)
            .attr("x","0")
            .attr("transform", "rotate(-90)")
            .style("text-transform", "none")
            .text(scope.pcaYAxisName + " (" + scope.pcaYAxisFraction + "% Variance)");

            x_label_pca = chart_pca.append("text")
            .attr("id", "x_label_pca")
            .attr("text-anchor", "end")
            .attr("x", width_pca)
            .attr("y", height_pca + 40)
            .style("text-transform", "none")
            .attr("class", "y_label")
            .text(scope.pcaXAxisName + " (" + scope.pcaXAxisFraction + "% Variance)");


          scope.$on('myresize', function()
          {
                          var full_page_width =  $('#page-content')[0].clientWidth;
             margin_pca = {top: Math.max((full_page_width * .025), 20), right:Math.max((full_page_width * 0.075), 50),
             bottom: Math.max((full_page_width * 0.075), 50), left: Math.max((full_page_width * 0.075), 50)};
             width_pca = Math.max((full_page_width * 0.80),350) - margin_pca.left - margin_pca.right;
             height_pca = (.7 * width_pca) - margin_pca.top - margin_pca.bottom;

             chart_pca.attr("height", height_pca + margin_pca.top + margin_pca.bottom)
            .attr("width", width_pca + margin_pca.left + margin_pca.right)
            ;

            $('#pcaSVG').attr("height", height_pca + margin_pca.top + margin_pca.bottom)
            .attr("width", width_pca + margin_pca.left + margin_pca.right);
            
            x_pca = d3.scale.linear()
            .range([0, width_pca])
            .domain([min_x_pca, max_x_pca]);

            y_pca = d3.scale.linear()
            .range([height_pca, 0])
            .domain([min_y_pca, max_y_pca]);

            xAxis_pca = d3.svg.axis()
            .scale(x_pca)
            .orient("bottom");

            yAxis_pca = d3.svg.axis()
            .scale(y_pca)
            .orient("left");

            zoom_pca
            .x(x_pca)
            .y(y_pca)
            .scaleExtent([1, 50])
           ;

          
         $("#chart_pca_body")
            .attr("width", width_pca)
            .attr("height", height_pca)
            .attr("transform", "translate(" + margin_pca.left + "," + margin_pca.top + ")");

              x_label_pca
              .attr("x", width_pca)
              .attr("y", height_pca+40);

              $('#clip-rect-pca')
            .attr("x", "0")
            .attr("y", "0")
            .attr('width', width_pca)
            .attr('height', height_pca)
            ;

            chart_pca.selectAll("circle")
            .attr("cx", function (d) {
              return x_pca(d.pc_x_vector);
            })
            .attr("cy", function (d) {
              return y_pca((d.pc_y_vector));
            });

           chart_pca.selectAll("g.y.axis")
          .transition()
          .duration(0)
          .attr('transform', 'translate(0,0)')
          .call(yAxis_pca);

          chart_pca.selectAll("g.x.axis")
          .transition()
          .duration(0)
          .attr("transform", "translate(0," + height_pca + ")")
          .call(xAxis_pca);
        
          });

        	scope.$watch('data', function() { 
            scope.update(scope.data);
          });

         	scope.$watch('pcaXAxisName', function() { 
            scope.updateAxisLabels();
          });

             scope.$watch('pcaXAxisFraction', function() { 
            scope.updateAxisLabels();
          });

            scope.$watch('pcaYAxisName', function() { 
             scope.updateAxisLabels();
          });

            scope.$watch('pcaYAxisFraction', function() { 
             scope.updateAxisLabels();
          });

        scope.updateAxisLabels = function()
        {
        	y_label_pca.text(scope.pcaYAxisName + " (" + scope.pcaYAxisFraction + "% Variance)");
            x_label_pca.text(scope.pcaXAxisName + " (" + scope.pcaXAxisFraction + "% Variance)");
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
              d.opacity = 0.8;
              d.si = (20);
              d.c = scope.$parent.conditionColorDict[d.condition_name];
              d.x = +d.pc_x_vector;
              d.y = +d.pc_y_vector;
              d.i = d.condition_name;
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
              min_x_pca = minX;
              max_x_pca = maxX;
              min_y_pca = minY;
              max_y_pca = maxY;
             

             x_pca = d3.scale.linear().range([0,width_pca])
             .domain([min_x_pca,max_x_pca]);
             xAxis_pca.scale(x_pca).orient("bottom");
             y_pca = d3.scale.linear().range([height_pca,0])
             .domain([min_y_pca,max_y_pca]);
             yAxis_pca.scale(y_pca).orient("left");

             yAxis_pca.scale(y_pca);
             xAxis_pca.scale(x_pca);
             zoom_pca.x(x_pca);
             zoom_pca.y(y_pca);

             chart_pca.selectAll("circle")
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
              return x_pca(d.pc_x_vector);
            })
             .attr("cy", function (d) {
              return y_pca(d.pc_y_vector);
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

             chart_pca.append("g").attr("clip-path", "url(#clip_pca)").selectAll("circle")
             .data(data, function(d){return d.i;})
             .enter()
             .append("circle")
             .attr("r", function (d) {
              return (d.si / 2);
            })
             .attr("cx", x_pca(0))
             .attr("cy", y_pca(0))
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
              return x_pca(d.pc_x_vector);
            })
             .attr("cy", function (d) {
              return y_pca(d.pc_y_vector);
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

            chart_pca.selectAll("circle")
        .on("mouseover", function (d) {
            d3.select(this).style("stroke", "black").style("stroke-opacity", 1).style("opacity", 1).style("fill", "#FFC10B").style("stroke-width", "2px");
            tip_pca.html(function (i) {
                var namePart = "<strong style='color:dodgerblue'>Condition Name:</strong> <span style='color:white'>" + d.condition_name + "</span> <br><br>";
                namePart += "<strong style='color:dodgerblue'>" + scope.pcaXAxisName + " Scaled Vector: </strong> <span style='color:white'>" + d.pc_x_vector + "</span> <br>";
                namePart += "<strong style='color:dodgerblue'>" + scope.pcaYAxisName + " Scaled Vector: </strong> <span style='color:white'>" + d.pc_y_vector + "</span> <br><br>";
                namePart += "<strong style='color:dodgerblue'>" + scope.pcaXAxisName + " Variance Fraction: </strong> <span style='color:white'>" + d.pc_x_fraction + "</span> <br>";
                namePart += "<strong style='color:dodgerblue'>" + scope.pcaYAxisName + " Variance Fraction: </strong> <span style='color:white'>" + d.pc_y_fraction + "</span>";
                return (namePart)});
            d3.select(this).moveToFront();
            tip_pca.show();
        })
        .on("mouseout", function (d) {
            {
                d3.select(this).style("stroke", "black").style("stroke-opacity", 1).style("opacity", d.opacity).style("fill", d.c).style("stroke-width", "1px");
            }

            tip_pca.hide();
        });

            chart_pca.selectAll("circle")
            .data(data, function(d){return d.i;})
            .exit()
            .remove();

            chart_pca.selectAll("g.y.axis")
            .transition()
            .duration(2100)
            .call(yAxis_pca);

            chart_pca.selectAll("g.x.axis")
            .transition()
            .duration(2100)
            .call(xAxis_pca);
            }

}
    }
    }

}]);

function zoomed_pca() {
    var panX = d3.event.translate[0];
    var panY = d3.event.translate[1];
    var scale = d3.event.scale;

    panX = panX > 0 ? 0 : panX;
    var maxX = -(scale - 1) * width_pca - 10;
    panX = panX < maxX ? maxX : panX;

    panY = panY > 0 ? 0 : panY;
    var maxY = -(scale - 1) * height_pca - 10;
    panY = panY < maxY ? maxY : panY;

    zoom_pca.translate([panX, panY]);
    chart_pca.select(".x.axis").call(xAxis_pca);
    chart_pca.select(".y.axis").call(yAxis_pca);
    chart_pca.selectAll("circle")
        .attr("cx", function (d) {
            return x_pca(d.pc_x_vector);
        })
        .attr("cy", function (d) {
            return y_pca(d.pc_y_vector);
        })
        .attr("r", function (d) {
            return (d.si / 2);
        });
}