var margin_outlierVolcano=null;
var width_outlierVolcano=null;
var height_outlierVolcano=null;
var x_outlierVolcano=null;
var y_outlierVolcano=null;
var xAxis_outlierVolcano=null;
var yAxis_outlierVolcano=null;
var zoom_outlierVolcano=null;
var chart_outlierVolcano=null;
var tip_outlierVolcano=null;
var max_x_outlierVolcano = 5;
var min_x_outlierVolcano = -5;
var max_y_outlierVolcano = 10;
var min_y_outlierVolcano = 0;
var y_label_outlierVolcano = null;
var outlierVolcano_Y_10 = null;
var outlierVolcano_Y_parentheses = null;
var outlierVolcano_Y_p = null;
var outlierVolcano_Y_value = null;
var x_label_outlierVolcano = null;
var outlierVolcano_x_abundance = null;
var outlierVolcano_x_2 = null;
var outlierVolcano_x_parentheses = null;
var outlierVolcano_x_condition = null;
var outlierVolcano_x_control = null;
var outlierVolcanoHoldPt = null;
var outlierVolcano_Chart_Parent = null;

angular.module('coonDataApp')
.directive('outlierVolcano',  ['$timeout', function ($timeout) {


        return {
          restrict: 'EA',
          scope: {
            data: "=",
            label: "@",
            onClick: "&",
            maxCondition: '@attr1',
            selectedMolecule: '@attr2',
            testingCorrection: '@attr3'
          },

          link: function(scope, iElement, iAttrs) {

            var full_page_width = $('#page-content')[0].clientWidth;;
            margin_outlierVolcano = {top: Math.max((full_page_width * .025), 10), right:Math.max((full_page_width * 0.005), 50),
            bottom: Math.max((full_page_width * 0.005), 50), left: Math.max((full_page_width * 0.005), 50)},
            width_outlierVolcano = (full_page_width * 0.38) - margin_outlierVolcano.left - margin_outlierVolcano.right,
            height_outlierVolcano = (width_outlierVolcano * .8) - margin_outlierVolcano.top - margin_outlierVolcano.bottom;

            x_outlierVolcano = d3.scale.linear()
            .range([0, width_outlierVolcano])
            .domain([min_x_outlierVolcano, max_x_outlierVolcano]);

            y_outlierVolcano = d3.scale.linear()
            .range([height_outlierVolcano, 0])
            .domain([min_y_outlierVolcano, max_y_outlierVolcano]);

            xAxis_outlierVolcano = d3.svg.axis()
            .scale(x_outlierVolcano)
            .orient("bottom");

            yAxis_outlierVolcano = d3.svg.axis()
            .scale(y_outlierVolcano)
            .orient("left");

            zoom_outlierVolcano = d3.behavior.zoom()
            .x(x_outlierVolcano)
            .y(y_outlierVolcano)
            .scaleExtent([1, 50])
            .on("zoom", zoomedoutlierVolcano);

            chart_outlierVolcano = d3.select('#outlierVolcanoColumn').append("svg").attr("id", "outlierVolcanoSVG")
            .attr("height", height_outlierVolcano + margin_outlierVolcano.top + margin_outlierVolcano.bottom)
            .attr("width", width_outlierVolcano + margin_outlierVolcano.left + margin_outlierVolcano.right)
            .call(zoom_outlierVolcano).on("dblclick.zoom", null)
            .append("g")
            .attr("id", "chart_outlierVolcano_body")
            .attr("transform", "translate(" + margin_outlierVolcano.left + "," + margin_outlierVolcano.top + ")")
            .attr("width", width_outlierVolcano)
            .attr("height", height_outlierVolcano);


            chart_outlierVolcano.append("g")
            .attr("class", "x axis")
            .attr("transform", "translate(0," + height_outlierVolcano + ")")
            .call(xAxis_outlierVolcano);

            chart_outlierVolcano.append("g")
            .attr('transform', 'translate(0,0)')
            .attr("class", "y axis")
            .call(yAxis_outlierVolcano);

            chart_outlierVolcano.append("svg:clipPath")
            .attr("id", "clip_outlierVolcano")
            .append("svg:rect")
            .attr("id", "clip-rect-outlierVolcano")
            .attr("x", "0")
            .attr("y", "0")
            .attr('width', width_outlierVolcano)
            .attr('height', height_outlierVolcano)
            ;

            tip_outlierVolcano = d3.tip()
            .attr('class', 'd3-tip')
            .offset([-10, 0]);

            chart_outlierVolcano.call(tip_outlierVolcano);

            y_label_outlierVolcano = chart_outlierVolcano.append("text")
            .attr("id", "y_label_outlierVolcano")
            .attr("class", "y_label_outlierVolcano")
            .attr("text-anchor", "end")
            .attr("y", -40)
            .attr("x","0")
            .attr("transform", "rotate(-90)")
            .style("text-transform", "none")
            .text("-log");

            outlierVolcano_Y_10 = chart_outlierVolcano.append("tspan");
            outlierVolcano_Y_parentheses = y_label_outlierVolcano.append("tspan").attr("class", "y_label");
            outlierVolcano_Y_p = y_label_outlierVolcano.append("tspan").attr("class", "y_label");
            outlierVolcano_Y_value = y_label_outlierVolcano.append("tspan").attr("class", "y_label");
            outlierVolcano_Y_10.text("10").style("font-size", "smaller").attr("baseline-shift", "sub");
            outlierVolcano_Y_parentheses.text("(");
              outlierVolcano_Y_p.text("p").style("font-style", "italic");
              outlierVolcano_Y_value.text("-value)");

              x_label_outlierVolcano = chart_outlierVolcano.append("text")
              .attr("id", "x_label_outlierVolcano")
              .attr("text-anchor", "end")
              .attr("x", width_outlierVolcano)
              .attr("y", height_outlierVolcano+40)
              .style("text-transform", "none")
              .attr("class", "y_label")
              .text("");

              outlierVolcano_x_abundance = x_label_outlierVolcano.append("tspan");
              outlierVolcano_x_abundance.text(scope.selectedMolecule + " abundance log").attr("class", "y_label");
              outlierVolcano_x_2 = x_label_outlierVolcano.append("tspan");
              outlierVolcano_x_2.text("2").style("font-size", "smaller").attr("baseline-shift", "sub");
              outlierVolcano_x_parentheses = x_label_outlierVolcano.append("tspan").attr("class", "y_label");
              outlierVolcano_x_parentheses.text("(").attr("class", "y_label");
              outlierVolcano_x_condition = x_label_outlierVolcano.append("tspan").attr("class", "y_label");
              outlierVolcano_x_condition.text("condition").style("text-transform", "lowercase").attr("class", "y_label");
              outlierVolcano_x_control = x_label_outlierVolcano.append("tspan").attr("class", "y_label");
              outlierVolcano_x_control.text("/control)");


          scope.$on('myresize', function()
          {
             var full_page_width =  $('#page-content')[0].clientWidth;
            margin_outlierVolcano = {top: Math.max((full_page_width * .025), 10), right:Math.max((full_page_width * 0.005), 50),
            bottom: Math.max((full_page_width * 0.005), 50), left: Math.max((full_page_width * 0.005), 50)},
            width_outlierVolcano = Math.max((full_page_width * 0.38),350) - margin_outlierVolcano.left - margin_outlierVolcano.right,
            height_outlierVolcano = (width_outlierVolcano * .8) - margin_outlierVolcano.top - margin_outlierVolcano.bottom;

             chart_outlierVolcano.attr("height", height_outlierVolcano + margin_outlierVolcano.top + margin_outlierVolcano.bottom)
            .attr("width", width_outlierVolcano + margin_outlierVolcano.left + margin_outlierVolcano.right)
            ;

            $('#outlierVolcanoSVG').attr("height", height_outlierVolcano + margin_outlierVolcano.top + margin_outlierVolcano.bottom)
            .attr("width", width_outlierVolcano + margin_outlierVolcano.left + margin_outlierVolcano.right);
            
            x_outlierVolcano = d3.scale.linear()
            .range([0, width_outlierVolcano])
            .domain([min_x_outlierVolcano, max_x_outlierVolcano]);

            y_outlierVolcano = d3.scale.linear()
            .range([height_outlierVolcano, 0])
            .domain([min_y_outlierVolcano, max_y_outlierVolcano]);

            xAxis_outlierVolcano = d3.svg.axis()
            .scale(x_outlierVolcano)
            .orient("bottom");

            yAxis_outlierVolcano = d3.svg.axis()
            .scale(y_outlierVolcano)
            .orient("left");

            zoom_outlierVolcano
            .x(x_outlierVolcano)
            .y(y_outlierVolcano)
            .scaleExtent([1, 50])
           ;

          
         $("#chart_outlierVolcano_body")
            .attr("width", width_outlierVolcano)
            .attr("height", height_outlierVolcano)
            .attr("transform", "translate(" + margin_outlierVolcano.left + "," + margin_outlierVolcano.top + ")");

              x_label_outlierVolcano
              .attr("x", width_outlierVolcano)
              .attr("y", height_outlierVolcano+40);

              $('#clip-rect-outlierVolcano')
            .attr("x", "0")
            .attr("y", "0")
            .attr('width', width_outlierVolcano)
            .attr('height', height_outlierVolcano)
            ;

            chart_outlierVolcano.selectAll("circle")
            .attr("cx", function (d) {
              return x_outlierVolcano(d.fc);
            })
            .attr("cy", function (d) {
              return y_outlierVolcano((-Math.log10(d.p)));
            });

           chart_outlierVolcano.selectAll("g.y.axis")
          .transition()
          .duration(0)
          .attr('transform', 'translate(0,0)')
          .call(yAxis_outlierVolcano);

          chart_outlierVolcano.selectAll("g.x.axis")
          .transition()
          .duration(0)
          .attr("transform", "translate(0," + height_outlierVolcano + ")")
          .call(xAxis_outlierVolcano);
          });


          // watch for data changes and re-render

          scope.$watch('data', function(newVals, oldVals) {
           scope.update(scope.data);
         });

            scope.$watch('testingCorrection', function(){
              scope.update(scope.data);
            });


          scope.update= function(data)
          {           
            if (data != null && data.length > 0)
            {
              var useData = [];
              data.forEach(function(d)
              {
                  {
                    useData.push(d);
                  }
              });
            if (spinner != null)
            {
              spinner.stop();
            }          
             
              var count = 0;
             useData.forEach(function (d) {
              count++;
              d.vis = "visible";
              d.opacity = 0.6;
              d.si = (10);
              d.c = "gray";
              if (scope.testingCorrection=="uncorrected")
              {
                   d.p = +d.p_value;
              }
               if (scope.testingCorrection=="fdradjusted")
              {
                   d.p = +d.p_value_fdr;
              }
               if (scope.testingCorrection=="bonferroni")
              {
                  d.p = +d.p_value_bonferroni;
              }
              d.fc = +d.fold_change;
              d.i = d.condition_id;
              d.q = +d.quant_val;
              d.sw = "0px";
              d.highlight = false;
              if (d.condition_id==scope.maxCondition)
              {
                d.si = 20;
                d.c = "red";
                d.opacity = 1;
                d.sw="2px";
              }
             
      
              d.transition = 2100;
              d.delay = 0;
              if (useData.length > 5000)
              {
                d.transition = 0;
              }
        });


             var minY = d3.min(useData, function (d) {
              return -Math.log10(d.p);
            });
             var maxY = d3.max(useData, function (d) {
              return -Math.log10(d.p);
            });
             var minX = d3.min(useData, function (d) {
              return d.fc;
            });
             var maxX = d3.max(useData, function (d) {
              return d.fc;
            });

            
             minY = 0;

             {
              minX--;
              maxX++;
              maxY++;
              min_x_outlierVolcano = minX;
              max_x_outlierVolcano = maxX;
              min_y_outlierVolcano = minY;
              max_y_outlierVolcano = maxY;
             }


             x_outlierVolcano = d3.scale.linear().range([0,width_outlierVolcano])
             .domain([min_x_outlierVolcano,max_x_outlierVolcano]);
             xAxis_outlierVolcano.scale(x_outlierVolcano).orient("bottom");
             y_outlierVolcano = d3.scale.linear().range([height_outlierVolcano,0])
             .domain([min_y_outlierVolcano,max_y_outlierVolcano]);
             yAxis_outlierVolcano.scale(y_outlierVolcano).orient("left");

             yAxis_outlierVolcano.scale(y_outlierVolcano);
             xAxis_outlierVolcano.scale(x_outlierVolcano);
             zoom_outlierVolcano.x(x_outlierVolcano);
             zoom_outlierVolcano.y(y_outlierVolcano);

             chart_outlierVolcano.selectAll("circle")
             .data(useData, function (d) {
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
              return x_outlierVolcano(d.fc);
            })
             .attr("cy", function (d) {
              return y_outlierVolcano(-Math.log10(d.p));
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
            })
             .attr("visibility", function(d)
              {
                return d.vis;
              });

             chart_outlierVolcano.append("g").attr("clip-path", "url(#clip_outlierVolcano)").selectAll("circle")
             .data(useData, function(d){return d.i;})
             .enter()
             .append("circle")
             .attr("r", function (d) {
              return (d.si / 2);
            })
             .attr("cx", x_outlierVolcano(0))
             .attr("cy", y_outlierVolcano(-.2))
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
              return x_outlierVolcano(d.fc);
            })
             .attr("cy", function (d) {
              return y_outlierVolcano(-Math.log10(d.p));
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
            })
             .attr("visibility", function(d)
              {
                return d.vis;
              });

             chart_outlierVolcano.selectAll("circle")
             .on("mouseover", function (d) {
              var moleculeEntry = moleculeDict[d.mol_id];
              d3.select(this).style("stroke", "black").style("stroke-opacity", 1).style("opacity", 1).style("fill", "#FFC10B").style("stroke-width", "2px");
              tip_outlierVolcano.html(function (i) {
                var namePart = "<strong style='color:dodgerblue'>Molecule Identifier:</strong> <span style='color:white'>" + moleculeEntry.name + "</span> <br>";
                moleculeEntry.metadata.forEach(function(u)
                {
                  namePart += "<strong style='color:dodgerblue'>" + u.name + ":</strong> <span style='color:white'>" + GetShortString(u.text) + "</span> <br>";
                });
                namePart += "<br>";
                d.p_value = +d.p_value;
                d.p_value_fdr = +d.p_value_fdr;
                d.p_value_bonferroni = +d.p_value_bonferroni;
                return (namePart
                   + "<strong style='color:dodgerblue'>" + "Condition name: " + "</strong> <span style='color:white'>" + d.condition_name + " </span><br>"
                  + "<strong style='color:dodgerblue'>" + "LFQ fold change: " + "</strong> <span style='color:white'>" + (Math.round(d.fc*10000)/10000) + " </span><br>"
                  + "<strong style='color:dodgerblue'>" + "P-Value: " + "</strong> <span style='color:white'>" + d.p_value.toExponential(4) + " </span><br>"
                  + "<strong style='color:dodgerblue'>" + "FDR adjusted Q-Value: " + "</strong> <span style='color:white'>" + d.p_value_fdr.toExponential(4) + " </span><br>"
                  + "<strong style='color:dodgerblue'>" + "Bonferroni adjusted P-Value: " + "</strong> <span style='color:white'>" + d.p_value_bonferroni.toExponential(4) + " </span><br>"
                  );
              });
              d3.select(this).moveToFront();
              tip_outlierVolcano.show();
            }).on("dblclick.zoom", null)
            .on("mouseout", function (d) {
              var moleculeEntry = moleculeDict[d.i];
              var selDataPoint = d3.select(this);
             {
                 d3.select(this).style("stroke", "black").style("stroke-opacity", 1).style("opacity", d.opacity).style("fill", d.c).style("stroke-width", d.sw);
             }

              tip_outlierVolcano.hide();
            });

            chart_outlierVolcano.selectAll("circle")
            .data(useData, function(d){return d.i;})
            .exit()
            .remove();

            chart_outlierVolcano.selectAll("g.y.axis")
            .transition()
            .duration(2100)
            .call(yAxis_outlierVolcano);

            chart_outlierVolcano.selectAll("g.x.axis")
            .transition()
            .duration(2100)
            .call(xAxis_outlierVolcano);
            outlierVolcano_x_abundance.text(scope.selectedMolecule + " abundance log");
            }
             if (spinner != null)
            {
              spinner.stop();
            }

}

}
}
}]);

 d3.selection.prototype.moveToBack = function() {  
        return this.each(function() { 
            var firstChild = this.parentNode.firstChild; 
            if (firstChild) { 
                this.parentNode.insertBefore(this, firstChild); 
            } 
        });
    };

function zoomedoutlierVolcano()
{
 var panX = d3.event.translate[0];
 var panY = d3.event.translate[1];
 var scale = d3.event.scale;

 panX = panX > 0 ? 0 : panX;
 var maxX = -(scale - 1) * width_outlierVolcano - 10;
 panX = panX < maxX ? maxX : panX;

 panY = panY > 0 ? 0 : panY;
 var maxY = -(scale - 1) * height_outlierVolcano - 10;
 panY = panY < maxY ? maxY : panY;

 zoom_outlierVolcano.translate([panX, panY]);
 chart_outlierVolcano.select(".x.axis").call(xAxis_outlierVolcano);
 chart_outlierVolcano.select(".y.axis").call(yAxis_outlierVolcano);
 chart_outlierVolcano.selectAll("circle")
 .attr("cx", function (d) {
  return x_outlierVolcano(d.fc);
})
 .attr("cy", function (d) {
  return y_outlierVolcano(-Math.log10(d.p));
})
 .attr("r", function (d) {
  return (d.si / 2);
})
 ;
}