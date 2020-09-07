var margin_fullVolcano=null;
var width_fullVolcano=null;
var height_fullVolcano=null;
var x_fullVolcano=null;
var y_fullVolcano=null;
var xAxis_fullVolcano=null;
var yAxis_fullVolcano=null;
var zoom_fullVolcano=null;
var chart_fullVolcano=null;
var tip_fullVolcano=null;
var max_x_fullVolcano = 5;
var min_x_fullVolcano = -5;
var max_y_fullVolcano = 10;
var min_y_fullVolcano = 0;
var y_label_fullVolcano = null;
var fullVolcano_Y_10 = null;
var fullVolcano_Y_parentheses = null;
var fullVolcano_Y_p = null;
var fullVolcano_Y_value = null;
var x_label_fullVolcano = null;
var fullVolcano_x_abundance = null;
var fullVolcano_x_2 = null;
var fullVolcano_x_parentheses = null;
var fullVolcano_x_condition = null;
var fullVolcano_x_control = null;
var fullVolcanoHoldPt = null;
var fullVolcano_Chart_Parent = null;

angular.module('coonDataApp')
.directive('fullVolcano',  ['$timeout', function ($timeout) {

      //margin stuff here
      //angular.element(window)[0].innerWidth
      margin_fullVolcano = {top: angular.element(window)[0].innerWidth * .02, right:angular.element(window)[0].innerWidth * .02,
        bottom: angular.element(window)[0].innerWidth * .09, left: angular.element(window)[0].innerWidth * .02},
        width_fullVolcano = angular.element(window)[0].innerWidth * .8 - margin_fullVolcano.left - margin_fullVolcano.right,
        height_fullVolcano = (.6 * width_fullVolcano) - margin_fullVolcano.top - margin_fullVolcano.bottom;
        

        return {
          restrict: 'EA',
          scope: {
            data: "=",
            label: "@",
            onClick: "&",
            pValueCutoff: '@attr1',
            foldChangeCutoff: '@attr2', 
            fixedScale: '@attr3',
            fixedScaleX: '@attr4',
            fixedScaleY: '@attr5',
            speedMode: '@attr7',
            testingCorrection: '@attr8'
          },

          link: function(scope, iElement, iAttrs) {

            var selectedMolecule = null;

            var full_page_width = $('.tab-content')[0].clientWidth;
            margin_fullVolcano = {top: Math.max((full_page_width * .025), 20), right:Math.max((full_page_width * 0.075), 50),
            bottom: Math.max((full_page_width * 0.075), 50), left: Math.max((full_page_width * 0.075), 50)},
            width_fullVolcano = Math.max((full_page_width * 0.80),350) - margin_fullVolcano.left - margin_fullVolcano.right,
            height_fullVolcano = (.7 * width_fullVolcano) - margin_fullVolcano.top - margin_fullVolcano.bottom;

            x_fullVolcano = d3.scale.linear()
            .range([0, width_fullVolcano])
            .domain([min_x_fullVolcano, max_x_fullVolcano]);

            y_fullVolcano = d3.scale.linear()
            .range([height_fullVolcano, 0])
            .domain([min_y_fullVolcano, max_y_fullVolcano]);

            xAxis_fullVolcano = d3.svg.axis()
            .scale(x_fullVolcano)
            .orient("bottom");

            yAxis_fullVolcano = d3.svg.axis()
            .scale(y_fullVolcano)
            .orient("left");

            zoom_fullVolcano = d3.behavior.zoom()
            .x(x_fullVolcano)
            .y(y_fullVolcano)
            .scaleExtent([1, 50])
            .on("zoom", zoomedfullVolcano);

            chart_fullVolcano = d3.select('#fullVolcanoColumn').append("svg").attr("id", "fullVolcanoSVG")
            .attr("height", height_fullVolcano + margin_fullVolcano.top + margin_fullVolcano.bottom)
            .attr("width", width_fullVolcano + margin_fullVolcano.left + margin_fullVolcano.right)
            .call(zoom_fullVolcano).on("dblclick.zoom", null)
            .append("g")
            .attr("id", "chart_fullVolcano_body")
            .attr("transform", "translate(" + margin_fullVolcano.left + "," + margin_fullVolcano.top + ")")
            .attr("width", width_fullVolcano)
            .attr("height", height_fullVolcano);


            chart_fullVolcano.append("g")
            .attr("class", "x axis")
            .attr("transform", "translate(0," + height_fullVolcano + ")")
            .call(xAxis_fullVolcano);

            chart_fullVolcano.append("g")
            .attr('transform', 'translate(0,0)')
            .attr("class", "y axis")
            .call(yAxis_fullVolcano);

            chart_fullVolcano.append("svg:clipPath")
            .attr("id", "clip_fullVolcano")
            .append("svg:rect")
            .attr("id", "clip-rect-fullVolcano")
            .attr("x", "0")
            .attr("y", "0")
            .attr('width', width_fullVolcano)
            .attr('height', height_fullVolcano)
            ;

            tip_fullVolcano = d3.tip()
            .attr('class', 'd3-tip')
            .offset([-10, 0]);

            chart_fullVolcano.call(tip_fullVolcano);

            y_label_fullVolcano = chart_fullVolcano.append("text")
            .attr("id", "y_label_fullVolcano")
            .attr("class", "y_label_fullVolcano")
            .attr("text-anchor", "end")
            .attr("y", -40)
            .attr("x","0")
            .attr("transform", "rotate(-90)")
            .style("text-transform", "none")
            .text("-log");

            fullVolcano_Y_10 = chart_fullVolcano.append("tspan");
            fullVolcano_Y_parentheses = y_label_fullVolcano.append("tspan").attr("class", "y_label");
            fullVolcano_Y_p = y_label_fullVolcano.append("tspan").attr("class", "y_label");
            fullVolcano_Y_value = y_label_fullVolcano.append("tspan").attr("class", "y_label");
            fullVolcano_Y_10.text("10").style("font-size", "smaller").attr("baseline-shift", "sub");
            fullVolcano_Y_parentheses.text("(");
              fullVolcano_Y_p.text("p").style("font-style", "italic");
              fullVolcano_Y_value.text("-value)");

              x_label_fullVolcano = chart_fullVolcano.append("text")
              .attr("id", "x_label_fullVolcano")
              .attr("text-anchor", "end")
              .attr("x", width_fullVolcano)
              .attr("y", height_fullVolcano+40)
              .style("text-transform", "none")
              .attr("class", "y_label")
              .text("");

              fullVolcano_x_abundance = x_label_fullVolcano.append("tspan");
              fullVolcano_x_abundance.text("Molecule abundance log").attr("class", "y_label");
              fullVolcano_x_2 = x_label_fullVolcano.append("tspan");
              fullVolcano_x_2.text("2").style("font-size", "smaller").attr("baseline-shift", "sub");
              fullVolcano_x_parentheses = x_label_fullVolcano.append("tspan").attr("class", "y_label");
              fullVolcano_x_parentheses.text("(").attr("class", "y_label");
              fullVolcano_x_condition = x_label_fullVolcano.append("tspan").attr("class", "y_label");
              fullVolcano_x_condition.text(scope.$parent.conditionName.replace("–","").trim()).style("text-transform", "lowercase").attr("class", "y_label");
              fullVolcano_x_control = x_label_fullVolcano.append("tspan").attr("class", "y_label");
              fullVolcano_x_control.text("/control)");


          scope.$on('myresize', function()
          {
             var full_page_width =  $('.tab-content')[0].clientWidth;
             margin_fullVolcano = {top: Math.max((full_page_width * .025), 20), right:Math.max((full_page_width * 0.075), 50),
             bottom: Math.max((full_page_width * 0.075), 50), left: Math.max((full_page_width * 0.075), 50)};
            width_fullVolcano = Math.max((full_page_width * 0.80),350) - margin_fullVolcano.left - margin_fullVolcano.right;
             height_fullVolcano = (.7 * width_fullVolcano) - margin_fullVolcano.top - margin_fullVolcano.bottom;

             chart_fullVolcano.attr("height", height_fullVolcano + margin_fullVolcano.top + margin_fullVolcano.bottom)
            .attr("width", width_fullVolcano + margin_fullVolcano.left + margin_fullVolcano.right)
            ;

            $('#fullVolcanoSVG').attr("height", height_fullVolcano + margin_fullVolcano.top + margin_fullVolcano.bottom)
            .attr("width", width_fullVolcano + margin_fullVolcano.left + margin_fullVolcano.right);
            
            x_fullVolcano = d3.scale.linear()
            .range([0, width_fullVolcano])
            .domain([min_x_fullVolcano, max_x_fullVolcano]);

            y_fullVolcano = d3.scale.linear()
            .range([height_fullVolcano, 0])
            .domain([min_y_fullVolcano, max_y_fullVolcano]);

            xAxis_fullVolcano = d3.svg.axis()
            .scale(x_fullVolcano)
            .orient("bottom");

            yAxis_fullVolcano = d3.svg.axis()
            .scale(y_fullVolcano)
            .orient("left");

            zoom_fullVolcano
            .x(x_fullVolcano)
            .y(y_fullVolcano)
            .scaleExtent([1, 50])
           ;

          
         $("#chart_fullVolcano_body")
            .attr("width", width_fullVolcano)
            .attr("height", height_fullVolcano)
            .attr("transform", "translate(" + margin_fullVolcano.left + "," + margin_fullVolcano.top + ")");

              x_label_fullVolcano
              .attr("x", width_fullVolcano)
              .attr("y", height_fullVolcano+40);

              $('#clip-rect-fullVolcano')
            .attr("x", "0")
            .attr("y", "0")
            .attr('width', width_fullVolcano)
            .attr('height', height_fullVolcano)
            ;

            chart_fullVolcano.selectAll("circle")
            .attr("cx", function (d) {
              return x_fullVolcano(d.fc);
            })
            .attr("cy", function (d) {
              return y_fullVolcano((-Math.log10(d.p)));
            });

           chart_fullVolcano.selectAll("g.y.axis")
          .transition()
          .duration(0)
          .attr('transform', 'translate(0,0)')
          .call(yAxis_fullVolcano);

          chart_fullVolcano.selectAll("g.x.axis")
          .transition()
          .duration(0)
          .attr("transform", "translate(0," + height_fullVolcano + ")")
          .call(xAxis_fullVolcano);
          });


          // watch for data changes and re-render

          scope.$watch('data', function(newVals, oldVals) {
           scope.update(scope.data);
         });

          scope.$watch('pValueCutoff', function() { 
            if (scope.speedMode=="true")
            {
              spinner = new Spinner(opts).spin(document.getElementById('fullVolcanoColumn'));
              scope.update(scope.data);
            }
            else
              {
              scope.chartSettingsChange();
            }
          });

             scope.$watch('foldChangeCutoff', function() { 
            if (scope.speedMode=="true")
            {
              spinner = new Spinner(opts).spin(document.getElementById('fullVolcanoColumn'));
              scope.update(scope.data);
            }
            else
              {
              scope.chartSettingsChange();
            }
          });

            scope.$watch('fixedScale', function() { 
             scope.fixedScaleChange();
          });

            scope.$watch('fixedScaleX', function() { 
             scope.fixedScaleChange();
          });

            scope.$watch('fixedScaleY', function() { 
             scope.fixedScaleChange();
          });

            scope.$watch('speedMode', function(){
              spinner = new Spinner(opts).spin(document.getElementById('fullVolcanoColumn'));
              scope.update(scope.data);
            });

            scope.$watch('testingCorrection', function(){
              scope.update(scope.data);
            });



          scope.fixedScaleChange = function()
          {
               if (scope.data!= null)
              {
                var minY = d3.min(scope.data, function (d) {
                  return -Math.log10(d.p);
                });
                var maxY = d3.max(scope.data, function (d) {
                  return -Math.log10(d.p);
                });
                var minX = d3.min(scope.data, function (d) {
                  return d.fc;
                });
                var maxX = d3.max(scope.data, function (d) {
                  return d.fc;
                });
                minY = 0;
                maxY*=1.05;
                minX--;
                maxX++;

                if (scope.fixedScale=="true")
                {
                   if (minX < -scope.fixedScaleX || maxX > scope.fixedScaleX || maxY > scope.fixedScaleY)
                  {
                      scope.$parent.overflow = "Warning: Data points outside plot bounds!";
                  }
                  else
                  {
                      scope.$parent.overflow = "";
                  }
                  maxY = scope.fixedScaleY;
                  minX = -scope.fixedScaleX;
                  maxX = scope.fixedScaleX;
                }
                else
                {
                   scope.$parent.overflow = "";
                }

                min_x_fullVolcano = minX;
                min_y_fullVolcano = minY;
                max_x_fullVolcano = maxX;
                max_y_fullVolcano = maxY;

                x_fullVolcano = d3.scale.linear().range([0,width_fullVolcano])
               .domain([min_x_fullVolcano,max_x_fullVolcano]);
               xAxis_fullVolcano.scale(x_fullVolcano).orient("bottom");
               y_fullVolcano = d3.scale.linear().range([height_fullVolcano,0])
               .domain([min_y_fullVolcano,max_y_fullVolcano]);
               yAxis_fullVolcano.scale(y_fullVolcano).orient("left");

               yAxis_fullVolcano.scale(y_fullVolcano);
               xAxis_fullVolcano.scale(x_fullVolcano);
               zoom_fullVolcano.x(x_fullVolcano);
               zoom_fullVolcano.y(y_fullVolcano);

              chart_fullVolcano.selectAll("circle")
              .attr("cx", function (d) {
                return x_fullVolcano(d.fc);
              })
              .attr("cy", function (d) {
                return y_fullVolcano(-Math.log10(d.p));
              });

               chart_fullVolcano.selectAll("g.y.axis")
              .transition()
              .duration(0)
              .call(yAxis_fullVolcano);

               chart_fullVolcano.selectAll("g.x.axis")
              .transition()
              .duration(0)
              .call(xAxis_fullVolcano);

              }
          }

          scope.chartSettingsChange = function()
          {
             var currCircles = chart_fullVolcano.selectAll("circle");
             currCircles[0].forEach(function(d)
             {
                d.__data__.c = "gray";
                d.__data__.si= 4;
                var absVal = Math.abs(d.__data__.fc);
                if (scope.testingCorrection=="uncorrected")
                {
                     d.__data__.p = +d.__data__.p_value;
                }
                 if (scope.testingCorrection=="fdradjusted")
                {
                     d.__data__.p = +d.__data__.p_value_fdr;
                }
                 if (scope.testingCorrection=="bonferroni")
                {
                    d.__data__.p = +d.p_value_bonferroni;
                }
                if (d.__data__.p < scope.pValueCutoff)
                {
                  d.__data__.si = Math.round((-Math.log10(d.__data__.p) + 1) * 3.5);
                   d.__data__.si = Math.min(d.__data__.si, 20);
                  if (absVal < scope.foldChangeCutoff)
                  {
                    d.__data__.c = "dodgerblue";
                  }
                  else
                  {
                    d.__data__.c = "green";
                  }
                }
             });
             chart_fullVolcano.selectAll("circle")
             .style("fill", function (d) {
                return d.c;
              })
              .attr("r", function (d) {
                return (d.si/2);
              });
          }

          scope.update= function(data)
          {           
            if (data != null)
            {
              var useData = [];
              data.forEach(function(d)
              {
                 if (scope.speedMode=="true")
                  {
                      if ((Math.abs(+d.fc)>scope.foldChangeCutoff && (+d.p_value) < scope.pValueCutoff))
                      {
                       useData.push(d);
                    }
                  }
                  else
                  {
                    useData.push(d);
                  }
              });
                   if (spinner != null)
            {
              spinner.stop();
            }          
              var maxYVal = d3.max(scope.data, function (d) {
                  return -Math.log10(+d.p_value);
                });

              var count = 0;
             useData.forEach(function (d) {
              count++;
              d.vis = "visible";
              d.opacity = 0.6;
              d.si = (4);
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
              d.fc = +d.fc;
              d.i = d.unique_identifier_id;
              d.q = +d.quant_val;
              d.sw = "0px";
              d.highlight = false;
              var absVal = Math.abs(d.fc);
              if (-Math.log10(d.p) > (-Math.log10(scope.pValueCutoff)))
              {
            //(int)((-Math.Log10(strain.p_value_dict[key]) + 1) * 4);
            d.si = Math.round((-Math.log10(d.p) + 1) * 3.5);
            d.si = Math.min(d.si, 20);
            if (absVal < scope.foldChangeCutoff)
            {
              d.c = "dodgerblue";
            }
            else
            {
              d.c = "green";
            }
          }
          if (data.length<200)
          {
            d.si += 10;
          }
        //  d.transition=500;
          //d.delay = Math.round((count%40))*40;
          d.transition = 2100;
          d.delay = 0;
          if (useData.length > 5000)
          {
            d.transition = 0;
          }
          if (scope.speedMode=="true")
          {
              if (Math.abs(d.fc)<scope.foldChangeCutoff || d.p > scope.pValueCutoff)
              {
                d.vis = "hidden";
              }
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

             if (scope.fixedScale=="true")
             { 
                  min_x_fullVolcano = -scope.fixedScaleX;
                  max_x_fullVolcano = scope.fixedScaleX;
                  min_y_fullVolcano = 0;
                  max_y_fullVolcano = scope.fixedScaleY;
                  if (minX < -scope.fixedScaleX || maxX > scope.fixedScaleX || maxY > scope.fixedScaleY)
                  {
                      scope.$parent.overflow = "Warning: Data points outside plot bounds!";
                  }
                  else
                  {
                      scope.$parent.overflow = "";
                  }
               }
             else
             {
              scope.$parent.overflow = "";
              minX--;
              maxX++;
              maxY*=1.05;
              min_x_fullVolcano = minX;
              max_x_fullVolcano = maxX;
              min_y_fullVolcano = minY;
              max_y_fullVolcano = maxY;
             }


             x_fullVolcano = d3.scale.linear().range([0,width_fullVolcano])
             .domain([min_x_fullVolcano,max_x_fullVolcano]);
             xAxis_fullVolcano.scale(x_fullVolcano).orient("bottom");
             y_fullVolcano = d3.scale.linear().range([height_fullVolcano,0])
             .domain([min_y_fullVolcano,max_y_fullVolcano]);
             yAxis_fullVolcano.scale(y_fullVolcano).orient("left");

             yAxis_fullVolcano.scale(y_fullVolcano);
             xAxis_fullVolcano.scale(x_fullVolcano);
             zoom_fullVolcano.x(x_fullVolcano);
             zoom_fullVolcano.y(y_fullVolcano);

             chart_fullVolcano.selectAll("circle")
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
              return x_fullVolcano(d.fc);
            })
             .attr("cy", function (d) {
              return y_fullVolcano(-Math.log10(d.p));
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
             .attr("class", "circle")
             .attr("visibility", function(d)
              {
                return d.vis;
              });

             chart_fullVolcano.append("g").attr("clip-path", "url(#clip_fullVolcano)").selectAll("circle")
             .data(useData, function(d){return d.i;})
             .enter()
             .append("circle")
             .attr("r", function (d) {
              return (d.si / 2);
            })
             .attr("cx", x_fullVolcano(0))
             .attr("cy", y_fullVolcano(-.2))
             .attr("class", "circle")
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
              return x_fullVolcano(d.fc);
            })
             .attr("cy", function (d) {
              return y_fullVolcano(-Math.log10(d.p));
            })
             .attr("r", function (d) {
              return (d.si / 2);
            })
             .attr("class", "circle")
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

             chart_fullVolcano.selectAll("circle")
             .on("mouseover", function (d) {
              var moleculeEntry = moleculeDict[d.i];
              d3.select(this).style("stroke", "black").style("stroke-opacity", 1).style("opacity", 1).style("fill", "#FFC10B").style("stroke-width", "2px");
              tip_fullVolcano.html(function (i) {
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
                  + "<strong style='color:dodgerblue'>" + "<span>" + scope.$parent.conditionName.replace("–","").trim() + "</span>" + " LFQ: " + "</strong> <span style='color:white'>" + d.q + " </span><br>"
                  + "<strong style='color:dodgerblue'>" + "Control LFQ: " + "</strong> <span style='color:white'>" + (Math.round((d.q- d.fc)*10000)/10000) + " </span><br>"
                  + "<strong style='color:dodgerblue'>" + "LFQ fold change: " + "</strong> <span style='color:white'>" + (Math.round(d.fc*10000)/10000) + " </span><br><br>"
                  + "<strong style='color:dodgerblue'>" + "P-Value: " + "</strong> <span style='color:white'>" + d.p_value.toExponential(4) + " </span><br>"
                  + "<strong style='color:dodgerblue'>" + "FDR adjusted Q-Value: " + "</strong> <span style='color:white'>" + d.p_value_fdr.toExponential(4) + " </span><br>"
                  + "<strong style='color:dodgerblue'>" + "Bonferroni adjusted P-Value: " + "</strong> <span style='color:white'>" + d.p_value_bonferroni.toExponential(4) + " </span><br>"
                  );
              });
              d3.select(this).moveToFront();
              tip_fullVolcano.show();
            }).on("dblclick.zoom", null)
            .on("mouseout", function (d) {
              var moleculeEntry = moleculeDict[d.i];
              var selDataPoint = d3.select(this);
             
             if (d3.select(this)[0][0].style.fill!="orange")
             {
                 d3.select(this).style("stroke", "black").style("stroke-opacity", 1).style("opacity", d.opacity).style("fill", d.c).style("stroke-width", d.sw);
             }

              tip_fullVolcano.hide();
            })
            .on("click", function(d){
                  if (selectedMolecule != null)
                  {
                     selectedMolecule.style("stroke", "black").style("stroke-opacity", 1).style("opacity", d.opacity).style("fill", d.c).style("stroke-width", d.sw);
                  }
                  var moleculeEntry = moleculeDict[d.i];
                  var tooltipText = "<p><strong style='color:dodgerblue'>Molecule Identifier:</strong> <span style='color:white'>" + moleculeEntry.name + "</span> <br>";
                   moleculeEntry.metadata.forEach(function(u)
                  {
                    tooltipText += "<strong style='color:dodgerblue'>" + u.name + ":</strong> <span style='color:white'>" + (u.text) + "</span> <br>";
                  });
                   tooltipText += "<br></p>";
                  var tooltipText2 = "<p><strong style='color:dodgerblue'>" + "<span>" + scope.$parent.conditionName.replace("–","").trim() + "</span>" + " LFQ: " + "</strong> <span style='color:white'>" + d.q + " </span><br>"
                  + "<strong style='color:dodgerblue'>" + "Control LFQ: " + "</strong> <span style='color:white'>" + (Math.round((d.q- d.fc)*10000)/10000) + " </span><br>"
                  + "<strong style='color:dodgerblue'>" + "LFQ fold change: " + "</strong> <span style='color:white'>" + (Math.round(d.fc*10000)/10000) + " </span><br><br>"
                  + "<strong style='color:dodgerblue'>" + "P-Value: " + "</strong> <span style='color:white'>" + d.p_value.toExponential(4) + " </span><br>"
                  + "<strong style='color:dodgerblue'>" + "FDR adjusted Q-Value: " + "</strong> <span style='color:white'>" + d.p_value_fdr.toExponential(4) + " </span><br>"
                  + "<strong style='color:dodgerblue'>" + "Bonferroni adjusted P-Value: " + "</strong> <span style='color:white'>" + d.p_value_bonferroni.toExponential(4) + " </span><br></p>";
                  scope.$parent.tooltipText = tooltipText;
                  scope.$parent.tooltipQuantText = tooltipText2;
                  scope.$parent.selectedMolecule = moleculeEntry.name;
                  scope.$apply();

                  d3.select(this).style("stroke", "black").style("stroke-opacity", 1).style("opacity", 1).style("fill", "orange").style("stroke-width", "2px").attr("class", "selVolc");
                  selectedMolecule = d3.select(this);
            });

            chart_fullVolcano.selectAll("circle")
            .data(useData, function(d){return d.i;})
            .exit()
            .remove();

            chart_fullVolcano.selectAll("g.y.axis")
            .transition()
            .duration(2100)
            .call(yAxis_fullVolcano);

            chart_fullVolcano.selectAll("g.x.axis")
            .transition()
            .duration(2100)
            .call(xAxis_fullVolcano);
            fullVolcano_x_condition.text(scope.$parent.conditionName.replace("–","").trim())
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

function zoomedfullVolcano()
{
 var panX = d3.event.translate[0];
 var panY = d3.event.translate[1];
 var scale = d3.event.scale;

 panX = panX > 0 ? 0 : panX;
 var maxX = -(scale - 1) * width_fullVolcano - 10;
 panX = panX < maxX ? maxX : panX;

 panY = panY > 0 ? 0 : panY;
 var maxY = -(scale - 1) * height_fullVolcano - 10;
 panY = panY < maxY ? maxY : panY;

 zoom_fullVolcano.translate([panX, panY]);
 chart_fullVolcano.select(".x.axis").call(xAxis_fullVolcano);
 chart_fullVolcano.select(".y.axis").call(yAxis_fullVolcano);
 chart_fullVolcano.selectAll("circle")
 .attr("cx", function (d) {
  return x_fullVolcano(d.fc);
})
 .attr("cy", function (d) {
  return y_fullVolcano(-Math.log10(d.p));
})
 .attr("r", function (d) {
  return (d.si / 2);
})
 ;
}