 var margin_scatter = null;
 var width_scatter = null;
 var height_scatter = null;
 var x_scatter = null;
 var y_scatter = null;
 var xAxis_scatter = null;
 var yAxis_scatter = null;
 var zoom_scatter = null;
 var chart_scatter = null;
 var tip_scatter = null;
 var max_x_scatter = 5;
 var min_x_scatter = -5;
 var max_y_scatter = 10;
 var min_y_scatter = -10;
 var y_label_scatter = null;
 var scatter_Y_10 = null;
 var scatter_Y_parentheses = null;
 var scatter_Y_p = null;
 var scatter_Y_value = null;
 var x_label_scatter = null;
 var scatter_x_abundance = null;
 var scatter_x_2 = null;
 var scatter_x_parentheses = null;
 var scatter_x_condition = null;
 var scatter_x_control = null;

 angular.module('coonDataApp')
     .directive('corrScatter', ['$timeout', function($timeout) {

         return {
             restrict: 'EA',
             scope: {
                 data: "=",
                 label: "@",
                 onClick: "&",
                 condXAxis: '@attr1',
                 condYAxis: '@attr2',
                 pValueCutoff: '@attr3',
                 fcCutoff: '@attr4',
                 showAxes: '@attr5',
                 showFoldChange: '@attr6',
                 showPearson: '@attr7',
                 sharedChangers: '@attr8',
                 speedMode: '@attr9', 
                 testingCorrection: '@attr10'
             },
             link: function(scope, iElement, iAttrs) {

                var selectedMolecule = null;

                 var full_page_width = $('.tab-content')[0].clientWidth;
                 margin_scatter = {
                         top: Math.max((full_page_width * .025), 20),
                         right: Math.max((full_page_width * 0.075), 50),
                         bottom: Math.max((full_page_width * 0.075), 50),
                         left: Math.max((full_page_width * 0.075), 50)
                     },
                     width_scatter = Math.max((full_page_width * 0.80),350) - margin_scatter.left - margin_scatter.right,
                     height_scatter = (.7 * width_scatter) - margin_scatter.top - margin_scatter.bottom;

                 x_scatter = d3.scale.linear()
                     .range([0, width_scatter])
                     .domain([min_x_scatter, max_x_scatter]);

                 y_scatter = d3.scale.linear()
                     .range([height_scatter, 0])
                     .domain([min_y_scatter, max_y_scatter]);

                 xAxis_scatter = d3.svg.axis()
                     .scale(x_scatter)
                     .orient("bottom");

                 yAxis_scatter = d3.svg.axis()
                     .scale(y_scatter)
                     .orient("left");

                 zoom_scatter = d3.behavior.zoom()
                     .x(x_scatter)
                     .y(y_scatter)
                     .scaleExtent([1, 50])
                     .on("zoom", zoomed_scatter);

                 chart_scatter = d3.select('#ScatterFullPlot').append("svg").attr("id", "scatterSVG")
                     .attr("height", height_scatter + margin_scatter.top + margin_scatter.bottom)
                     .attr("width", width_scatter + margin_scatter.left + margin_scatter.right)
                     .call(zoom_scatter)
                     .append("g")
                     .attr("id", "chart_scatter_body")
                     .attr("transform", "translate(" + margin_scatter.left + "," + margin_scatter.top + ")")
                     .attr("width", width_scatter)
                     .attr("height", height_scatter);

                 chart_scatter.append("g")
                     .attr("class", "x axis")
                     .attr("transform", "translate(0," + height_scatter + ")")
                     .call(xAxis_scatter);

                 chart_scatter.append("g")
                     .attr('transform', 'translate(0,0)')
                     .attr("class", "y axis")
                     .call(yAxis_scatter);

                 chart_scatter.append("svg:clipPath")
                     .attr("id", "clip_scatter")
                     .append("svg:rect")
                     .attr("id", "clip-rect-scatter")
                     .attr("x", "0")
                     .attr("y", "0")
                     .attr('width', width_scatter)
                     .attr('height', height_scatter);

                 tip_scatter = d3.tip()
                     .attr('class', 'd3-tip')
                     .offset([-10, 0]);

                 chart_scatter.call(tip_scatter);

                 y_label_scatter = chart_scatter.append("text")
                     .attr("id", "y_label_scatter")
                     .attr("class", "y_label_scatter")
                     .attr("text-anchor", "end")
                     .attr("y", -40)
                     .attr("x", "0")
                     .attr("transform", "rotate(-90)")
                     .style("text-transform", "none")
                     .text("");

                 scatter_y_abundance = y_label_scatter.append("tspan");
                 scatter_y_abundance.text("Molecule abundance log").attr("class", "y_label");
                 scatter_y_2 = y_label_scatter.append("tspan");
                 scatter_y_2.text("2").style("font-size", "smaller").attr("baseline-shift", "sub");
                 scatter_y_parentheses = y_label_scatter.append("tspan").attr("class", "y_label");
                 scatter_y_parentheses.text("(").attr("class", "y_label");
                 scatter_y_condition = y_label_scatter.append("tspan").attr("class", "y_label");
                 scatter_y_condition.text(scope.condYAxis).style("text-transform", "lowercase").attr("class", "y_label");
                 scatter_y_control = y_label_scatter.append("tspan").attr("class", "y_label");
                 scatter_y_control.text("/control)");
                 x_label_scatter = chart_scatter.append("text")
                     .attr("id", "x_label_scatter")
                     .attr("text-anchor", "end")
                     .attr("x", width_scatter)
                     .attr("y", height_scatter + 40)
                     .style("text-transform", "none")
                     .attr("class", "y_label")
                     .text("");

                 scatter_x_abundance = x_label_scatter.append("tspan");
                 scatter_x_abundance.text("Molecule abundance log").attr("class", "y_label");
                 scatter_x_2 = x_label_scatter.append("tspan");
                 scatter_x_2.text("2").style("font-size", "smaller").attr("baseline-shift", "sub");
                 scatter_x_parentheses = x_label_scatter.append("tspan").attr("class", "y_label");
                 scatter_x_parentheses.text("(").attr("class", "y_label");
                 scatter_x_condition = x_label_scatter.append("tspan").attr("class", "y_label");
                 scatter_x_condition.text(scope.condXAxis).style("text-transform", "lowercase").attr("class", "y_label");
                 scatter_x_control = x_label_scatter.append("tspan").attr("class", "y_label");
                 scatter_x_control.text("/control)");


                 scope.$on('myresize', function() {
                     var full_page_width = $('.tab-content')[0].clientWidth;
                     margin_scatter = {
                         top: Math.max((full_page_width * .025), 20),
                         right: Math.max((full_page_width * 0.075), 50),
                         bottom: Math.max((full_page_width * 0.075), 50),
                         left: Math.max((full_page_width * 0.075), 50)
                     };
                     width_scatter = Math.max((full_page_width * 0.80),350) - margin_scatter.left - margin_scatter.right;
                     height_scatter = (.7 * width_scatter) - margin_scatter.top - margin_scatter.bottom;

                     chart_scatter.attr("height", height_scatter + margin_scatter.top + margin_scatter.bottom)
                         .attr("width", width_scatter + margin_scatter.left + margin_scatter.right);

                     $('#scatterSVG').attr("height", height_scatter + margin_scatter.top + margin_scatter.bottom)
                         .attr("width", width_scatter + margin_scatter.left + margin_scatter.right);

                     x_scatter = d3.scale.linear()
                         .range([0, width_scatter])
                         .domain([min_x_scatter, max_x_scatter]);

                     y_scatter = d3.scale.linear()
                         .range([height_scatter, 0])
                         .domain([min_y_scatter, max_y_scatter]);

                     xAxis_scatter = d3.svg.axis()
                         .scale(x_scatter)
                         .orient("bottom");

                     yAxis_scatter = d3.svg.axis()
                         .scale(y_scatter)
                         .orient("left");

                     zoom_scatter
                         .x(x_scatter)
                         .y(y_scatter)
                         .scaleExtent([1, 50]);


                     $("#chart_scatter_body")
                         .attr("width", width_scatter)
                         .attr("height", height_scatter)
                         .attr("transform", "translate(" + margin_scatter.left + "," + margin_scatter.top + ")");

                     x_label_scatter
                         .attr("x", width_scatter)
                         .attr("y", height_scatter + 40);

                     $('#clip-rect-scatter')
                         .attr("x", "0")
                         .attr("y", "0")
                         .attr('width', width_scatter)
                         .attr('height', height_scatter);


                     chart_scatter.selectAll("g.y.axis")
                         .transition()
                         .duration(0)
                         .attr('transform', 'translate(0,0)')
                         .call(yAxis_scatter);

                     chart_scatter.selectAll("g.x.axis")
                         .transition()
                         .duration(0)
                         .attr("transform", "translate(0," + height_scatter + ")")
                         .call(xAxis_scatter);

                     chart_scatter.selectAll("circle")
                         .attr("cx", function(d) {
                             return x_scatter(d.fc1);
                         })
                         .attr("cy", function(d) {
                             return y_scatter((d.fc2));
                         })
                         .attr("r", function(d) {
                             return (d.si / 2.5);
                         });
                     chart_scatter.selectAll(".lrline").attr("d", line_scatter);
                     chart_scatter.selectAll(".axisline").attr("d", line_scatter);
                     chart_scatter.selectAll(".fcline").attr("d", line_scatter);

                 });


                 scope.$watch('condXAxis', function() {
                     scope.labelChange();
                 });

                 scope.$watch('condYAxis', function() {
                     scope.labelChange();
                 });

                 scope.$watch('data', function() {
                     scope.update(scope.data);
                 });

                 scope.$watch('pValueCutoff', function() {
                     scope.highlightChange();
                     if (scope.speedMode == "true") {
                         startScatterSpin();
                         scope.update(scope.data);
                     }
                 });

                 scope.$watch('fcCutoff', function() {
                     scope.highlightChange();
                     if (scope.speedMode == "true") {
                         startScatterSpin();
                         scope.update(scope.data);
                     }
                 });

                 scope.$watch('sharedChangers', function() {
                     scope.highlightChange();
                 });

                 scope.$watch('showAxes', function() {
                     if (scope.showAxes == "true") {
                         chart_scatter.selectAll(".axisline").style("stroke-opacity", "1");
                     } else {
                         chart_scatter.selectAll(".axisline").style("stroke-opacity", "0");
                     }
                 });

                 scope.$watch('showPearson', function() {
                     if (scope.showPearson == "true") {
                         chart_scatter.selectAll(".lrline").style("stroke-opacity", "1");
                     } else {
                         chart_scatter.selectAll(".lrline").style("stroke-opacity", "0");
                     }
                 });

                 scope.$watch('showFoldChange', function() {
                     if (scope.showFoldChange == "true") {
                         chart_scatter.selectAll(".fcline").style("stroke-opacity", "1");
                     } else {
                         chart_scatter.selectAll(".fcline").style("stroke-opacity", "0");
                     }
                 });

                 scope.$watch('speedMode', function() {
                     startScatterSpin();
                     scope.update(scope.data);
                 });

                 scope.$watch('testingCorrection', function(){
                    scope.update(scope.data);
                 });

                 scope.labelChange = function() {
                     scatter_y_condition.text(scope.condYAxis);
                     scatter_x_condition.text(scope.condXAxis);
                 }

                 scope.highlightChange = function() {
                     chart_scatter.selectAll("circle").style("fill", function(d) {
                         d.si = (8);
                         d.opacity = 0.3;
                         if (scope.data.length < 200) {
                             d.si = 16;
                             d.opacity = 0.5;
                         }
                         d.pValue1 = -Math.log10(+d.p1);
                        
                          d.pValue2 = -Math.log10(+d.p2);
                             if (scope.testingCorrection=="fdradjusted")
                             {
                                d.pValue1 = -Math.log10(+d.p1_fdr);
                                d.pValue2 = -Math.log10(+d.p2_fdr);
                             }
                              if (scope.testingCorrection=="bonferroni")
                             {
                                d.pValue1 = -Math.log10(+d.p1_bonferroni);
                                d.pValue2 = -Math.log10(+d.p2_bonferroni);
                             }
                         if (scope.sharedChangers == "shared") {
                             if (Math.abs(d.fc1) > scope.fcCutoff && Math.abs(d.fc2) > scope.fcCutoff && d.pValue1 > -Math.log10(scope.pValueCutoff) && d.pValue2 > -Math.log10(scope.pValueCutoff)) {
                                 d.c = "#009F0A";
                                 d.si = 12;
                                 d.opacity = 0.7;
                                 if (scope.data.length < 200) {
                                     d.si = 18;
                                 }
                                 return d.c;
                             }
                             d.c = "gray";
                             return "gray";
                         } else {
                             if (Math.abs(d.fc1) > scope.fcCutoff && d.pValue1 > -Math.log10(scope.pValueCutoff)) {
                                 if (Math.abs(d.fc2) < scope.fcCutoff || d.pValue2 < -Math.log10(scope.pValueCutoff)) {
                                     d.c = "dodgerblue";
                                     d.si = 12;
                                     d.opacity = 0.7;
                                     if (scope.data.length < 200) {
                                         d.si = 18;
                                     }
                                     return d.c;
                                 }
                             }
                             if (Math.abs(d.fc2) > scope.fcCutoff && d.pValue2 > -Math.log10(scope.pValueCutoff)) {
                                 if (Math.abs(d.fc1) < scope.fcCutoff || d.pValue1 < -Math.log10(scope.pValueCutoff)) {
                                     d.c = "red";
                                     d.si = 12;
                                     d.opacity = 0.7;
                                     if (scope.data.length < 200) {
                                         d.si = 18;
                                     }
                                     return d.c;
                                 }

                             }
                             d.c = "gray";
                             return "gray";
                         }
                     }).attr("r", function(d) {
                         return d.si / 2.5
                     }).style("opacity", function(d) {
                         return d.opacity;
                     });
                 }

                 scope.update = function(data) {
                     if (data.length > 0) {
                         var useData = []; //use this data if speed mode is on
                         data.forEach(function(d) {
                             d.opacity = 0.3;
                             d.si = (8);
                             if (data.length < 200) {
                                 d.si = 16;
                                 d.opacity = 0.5;
                             }
                             d.c = "gray";
                             d.pValue1 = -Math.log10(+d.p1);
                             d.pValue2 = -Math.log10(+d.p2);
                             if (scope.testingCorrection=="fdradjusted")
                             {
                                d.pValue1 = -Math.log10(+d.p1_fdr);
                                d.pValue2 = -Math.log10(+d.p2_fdr);
                             }
                              if (scope.testingCorrection=="bonferroni")
                             {
                                d.pValue1 = -Math.log10(+d.p1_bonferroni);
                                d.pValue2 = -Math.log10(+d.p2_bonferroni);
                             }
                             d.fc1 = +d.fc1;
                             d.fc2 = +d.fc2;
                             d.i = d.unique_identifier_id;
                             if (scope.sharedChangers == "shared") {
                                 if (Math.abs(d.fc1) > scope.fcCutoff && Math.abs(d.fc2) > scope.fcCutoff && d.pValue1 > -Math.log10(scope.pValueCutoff) && d.pValue2 > -Math.log10(scope.pValueCutoff)) {
                                     d.c = "#009F0A";
                                     d.si = 12;
                                     d.opacity = 0.7;
                                     if (data.length < 200) {
                                         d.si = 18;
                                     }
                                 }
                             } else {
                                 if (Math.abs(d.fc1) > scope.fcCutoff && d.pValue1 > -Math.log10(scope.pValueCutoff)) {
                                     if (Math.abs(d.fc2) < scope.fcCutoff || d.pValue2 < -Math.log10(scope.pValueCutoff)) {
                                         d.c = "dodgerblue";
                                         d.si = 12;
                                         d.opacity = 0.7;
                                         if (data.length < 200) {
                                             d.si = 18;
                                         }
                                     }

                                 }
                                 if (Math.abs(d.fc2) > scope.fcCutoff && d.pValue2 > -Math.log10(scope.pValueCutoff)) {
                                     if (Math.abs(d.fc1) < scope.fcCutoff || d.pValue1 < -Math.log10(scope.pValueCutoff)) {
                                         d.c = "red";
                                         d.si = 12;
                                         d.opacity = 0.7;
                                         if (data.length < 200) {
                                             d.si = 18;
                                         }
                                     }
                                 }
                             }
                             d.transition = 3000;
                             d.delay = 0;

                             //logic for speed mode here 
                             if (scope.speedMode == "true") {
                                 if ((Math.abs(d.fc1) > scope.fcCutoff && d.pValue1 > -Math.log10(scope.pValueCutoff)) || (Math.abs(d.fc2) > scope.fcCutoff) && d.pValue2 > -Math.log10(scope.pValueCutoff)) {
                                     useData.push(d);
                                 }
                             } else {
                                 useData.push(d);
                             }
                         });

                         var minY = d3.min(useData, function(d) {
                             return d.fc2;
                         });
                         var maxY = d3.max(useData, function(d) {
                             return d.fc2;
                         });
                         var minX = d3.min(useData, function(d) {
                             return d.fc1;
                         });
                         var maxX = d3.max(useData, function(d) {
                             return d.fc1;
                         });

                         minX--;
                         maxX++;
                         maxY++;
                         minY--;

                         min_x_scatter = minX;
                         max_x_scatter = maxX;
                         min_y_scatter = minY;
                         max_y_scatter = maxY;

                         var lr = linearRegression(data);
                         lrData = [];
                         //y = mx+b
                         var ptOne = (lr.slope * minX) + lr.intercept;
                         var ptTwo = (lr.slope * maxX) + lr.intercept;
                         lrData.push([{
                             x: minX,
                             y: ptOne
                         }, {
                             x: maxX,
                             y: ptTwo
                         }]);

                         if (lr.slope < 0) {
                             lr.r2 *= -1;
                         }

                         var textVal = lr.r2 * 100000;
                         textVal = Math.round(textVal);
                         textVal /= 100000;

                         var slopeVal = lr.slope * 1000000;
                         slopeVal = Math.round(slopeVal);
                         slopeVal /= 1000000;

                         scope.$parent.pearson = textVal;
                         scope.$parent.slope = slopeVal;

                         var axes = [];
                         axes.push([{
                             x: minX,
                             y: 0
                         }, {
                             x: maxX,
                             y: 0
                         }]);
                         axes.push([{
                             x: 0,
                             y: minY
                         }, {
                             x: 0,
                             y: maxY
                         }]);

                         var fcLines = [];
                         fcLines.push([{
                             x: minX - 10,
                             y: minX - 9
                         }, {
                             x: maxX + 10,
                             y: maxX + 11
                         }]);
                         fcLines.push([{
                             x: minX - 10,
                             y: minX - 11
                         }, {
                             x: maxX + 10,
                             y: maxX + 9
                         }]);


                         x_scatter = d3.scale.linear().range([0, width_scatter])
                             .domain([min_x_scatter, max_x_scatter]);
                         xAxis_scatter.scale(x_scatter).orient("bottom");
                         y_scatter = d3.scale.linear().range([height_scatter, 0])
                             .domain([min_y_scatter, max_y_scatter]);
                         yAxis_scatter.scale(y_scatter).orient("left");

                         yAxis_scatter.scale(y_scatter);
                         xAxis_scatter.scale(x_scatter);
                         zoom_scatter.x(x_scatter);
                         zoom_scatter.y(y_scatter);

                         //Pearson lines
                         chart_scatter.selectAll(".lrline")
                             .data(lrData)
                             .attr("class", "lrline")
                             .attr("stroke-dasharray", "2 2")
                             .attr("stroke-dashoffset", "2")
                             .attr("stroke-opacity", function(d) {
                                 if (scope.showPearson == "true") {
                                     return 1;
                                 }
                                 return 0;
                             })
                             .transition()
                             .duration(2000)
                             .attr("d", line_scatter)
                             .attr("stroke-width", "3px")
                             .attr("stroke", "lawngreen")
                             .attr("stroke-opacity", function(d) {
                                 if (scope.showPearson == "true") {
                                     return 1;
                                 }
                                 return 0;
                             });

                         chart_scatter.append("g").attr("clip-path", "url(#clip_scatter)").selectAll(".lrline")
                             .data(lrData)
                             .enter().append("path")
                             .attr("class", "lrline")
                             .attr("d", line_scatter)
                             .attr("stroke-width", "3px")
                             .attr("stroke", "lawngreen")
                             .attr("stroke-opacity", function(d) {
                                 if (scope.showPearson == "true") {
                                     return 1;
                                 }
                                 return 0;
                             })
                             .attr("stroke-dasharray", function() {
                                 return (this.getTotalLength() + " " + this.getTotalLength());
                             })
                             .attr("stroke-dashoffset", function() {
                                 return (this.getTotalLength());
                             })
                             .transition()
                             .duration(2000)
                             .attr("stroke-dasharray", "2 2")
                             .attr("stroke-dashoffset", "2")
                             .attr("stroke-opacity", function(d) {
                                 if (scope.showPearson == "true") {
                                     return 1;
                                 }
                                 return 0;
                             });

                         //axis lines
                         chart_scatter.selectAll(".axisline")
                             .data(axes)
                             .attr("class", "axisline")
                             .attr("stroke-dasharray", "2 2")
                             .attr("stroke-dashoffset", "2")
                             .attr("stroke-opacity", function(d) {
                                 if (scope.showAxes == "true") {
                                     return 1;
                                 }
                                 return 0;
                             })
                             .transition()
                             .duration(2000)
                             .attr("d", line_scatter)
                             .attr("stroke-width", "1px")
                             .attr("stroke", "darkgray")
                             .attr("stroke-opacity", function(d) {
                                 if (scope.showAxes == "true") {
                                     return 1;
                                 }
                                 return 0;
                             });

                         chart_scatter.append("g").attr("clip-path", "url(#clip_scatter)").selectAll(".axisline")
                             .data(axes)
                             .enter().append("path")
                             .attr("class", "axisline")
                             .attr("d", line_scatter)
                             .attr("stroke-width", "1px")
                             .attr("stroke", "darkgray")
                             .attr("stroke-dasharray", function() {
                                 return (this.getTotalLength() + " " + this.getTotalLength());
                             })
                             .attr("stroke-dashoffset", function() {
                                 return (this.getTotalLength());
                             })
                             .attr("stroke-opacity", function(d) {
                                 if (scope.showAxes == "true") {
                                     return 1;
                                 }
                                 return 0;
                             })
                             .transition()
                             .duration(2000)
                             .attr("stroke-dasharray", "2 2")
                             .attr("stroke-dashoffset", "2")
                             .attr("stroke-opacity", function(d) {
                                 if (scope.showAxes == "true") {
                                     return 1;
                                 }
                                 return 0;
                             });

                         //fold change lines
                         chart_scatter.selectAll(".fcline")
                             .data(fcLines)
                             .attr("class", "fcline")
                             .attr("stroke-dasharray", "2 2")
                             .attr("stroke-dashoffset", "2")
                             .attr("stroke-opacity", function(d) {
                                 if (scope.showFoldChange == "true") {
                                     return 1;
                                 }
                                 return 0;
                             })
                             .transition()
                             .duration(2000)
                             .attr("d", line_scatter)
                             .attr("stroke-width", "1px")
                             .attr("stroke", "darkgray")
                             .attr("stroke-opacity", function(d) {
                                 if (scope.showFoldChange == "true") {
                                     return 1;
                                 }
                                 return 0;
                             });

                         chart_scatter.append("g").attr("clip-path", "url(#clip_scatter)").selectAll(".fcline")
                             .data(fcLines)
                             .enter().append("path")
                             .attr("class", "fcline")
                             .attr("d", line_scatter)
                             .attr("stroke-width", "1px")
                             .attr("stroke", "darkgray")
                             .attr("stroke-dasharray", function() {
                                 return (this.getTotalLength() + " " + this.getTotalLength());
                             })
                             .attr("stroke-dashoffset", function() {
                                 return (this.getTotalLength());
                             })
                             .attr("stroke-opacity", function(d) {
                                 if (scope.showFoldChange == "true") {
                                     return 1;
                                 }
                                 return 0;
                             })
                             .transition()
                             .duration(2000)
                             .attr("stroke-dasharray", "2 2")
                             .attr("stroke-dashoffset", "2")
                             .attr("stroke-opacity", function(d) {
                                 if (scope.showFoldChange == "true") {
                                     return 1;
                                 }
                                 return 0;
                             });

                         chart_scatter.selectAll("circle")
                             .data(useData, function(d) {
                                 return d.i;
                             })
                             .transition()
                             .duration(function(d) {
                                 return d.transition;
                             })
                             .attr("cx", function(d) {
                                 return x_scatter(d.fc1);
                             })
                             .attr("cy", function(d) {
                                 return y_scatter(d.fc2);
                             })
                             .attr("r", function(d) {
                                 return ((d.si / 2.5));
                             })
                             .style("fill", function(d) {
                                 return d.c;
                             })
                             .style("opacity", function(d) {
                                 return d.opacity;
                             });

                         chart_scatter.append("g").attr("clip-path", "url(#clip_scatter)").selectAll("circle")
                             .data(useData, function(d) {
                                 return d.i;
                             })
                             .enter()
                             .append("circle")
                             .attr("r", function(d) {
                                 return (d.si / 3);
                             })
                             .attr("cx", x_scatter(0))
                             .attr("cy", y_scatter(-.2))
                             .style("fill", function(d) {
                                 return d.c;
                             })
                             .style("opacity", function(d) {
                                 return d.opacity
                             })
                             .transition()
                             .duration(function(d) {
                                 return d.transition;
                             })
                             .attr("cx", function(d) {
                                 return x_scatter(d.fc1);
                             })
                             .attr("cy", function(d) {
                                 return y_scatter(d.fc2);
                             })
                             .attr("r", function(d) {
                                 return (d.si / 2.5);
                             })
                             .style("fill", function(d) {
                                 return d.c
                             })
                             .style("opacity", function(d) {
                                 return d.opacity
                             });

                         chart_scatter.selectAll("circle")
                             .on("mouseover", function(d) {
                                 var moleculeEntry = moleculeDict[d.i];
                                 d3.select(this).style("stroke", "black").style("stroke-opacity", 1).style("opacity", 1).style("fill", "#FFC10B").style("stroke-width", "2px");
                                 tip_scatter.html(function(i) {
                                     var namePart = "<strong style='color:dodgerblue'>Molecule Identifier:</strong> <span style='color:white'>" + moleculeEntry.name + "</span> <br><br>";
                                     moleculeEntry.metadata.forEach(function(u) {
                                         namePart += "<strong style='color:dodgerblue'>" + u.name + ":</strong> <span style='color:white'>" + GetShortString(u.text) + "</span> <br>";
                                     });
                                     namePart += "<br>";
                                     d.p1 = +d.p1;
                                     d.p1_fdr = +d.p1_fdr;
                                     d.p1_bonferroni = + d.p1_bonferroni;
                                     d.p2 = +d.p2;
                                     d.p2_fdr = +d.p2_fdr;
                                     d.p2_bonferroni = +d.p2_bonferroni;
                                     return (namePart +
                                         "<strong style='color:dodgerblue'>" + "<span>" + scope.condXAxis + "</span>" + " Delta LFQ: " + "</strong> <span style='color:white'>" + d.fc1 + " </span><br>" +
                                         "<strong style='color:dodgerblue'>" + "<span>" + scope.condXAxis + "</span>" + " P-value: " + "</strong> <span style='color:white'>" + d.p1.toExponential(4) + " </span><br>" +
                                         "<strong style='color:dodgerblue'>" + "<span>" + scope.condXAxis + "</span>" + " FDR-adjusted Q-value: " + "</strong> <span style='color:white'>" + d.p1_fdr.toExponential(4) + " </span><br>" +
                                         "<strong style='color:dodgerblue'>" + "<span>" + scope.condXAxis + "</span>" + " Bonferroni-adjusted P-value: " + "</strong> <span style='color:white'>" + d.p1_bonferroni.toExponential(4) + " </span><br><br>" +
                                         "<strong style='color:dodgerblue'>" + "<span>" + scope.condYAxis + "</span>" + " Delta LFQ: " + "</strong> <span style='color:white'>" + d.fc2 + " </span><br>" +
                                         "<strong style='color:dodgerblue'>" + "<span>" + scope.condYAxis + "</span>" + " P-value: " + "</strong> <span style='color:white'>" + d.p2.toExponential(4) + " </span><br>" +
                                         "<strong style='color:dodgerblue'>" + "<span>" + scope.condXAxis + "</span>" + " FDR-adjusted Q-value: " + "</strong> <span style='color:white'>" + d.p2_fdr.toExponential(4) + " </span><br>" +
                                         "<strong style='color:dodgerblue'>" + "<span>" + scope.condXAxis + "</span>" + " Bonferroni-adjusted P-value: " + "</strong> <span style='color:white'>" + d.p2_bonferroni.toExponential(4) + " </span>"
                                     )
                                 });
                                 d3.select(this).moveToFront();
                                 tip_scatter.show();
                             })
                             .on("mouseout", function(d) {
                                 var moleculeEntry = moleculeDict[d.i];
                                 if (d3.select(this)[0][0].style.fill != "orange")
                                 {
                                 d3.select(this).style("fill", function(d) {
                                     return d.c;
                                 }).style("stroke-opacity", 0).style("opacity", function(d) {
                                     return d.opacity
                                 }).style("stroke-width", "0px").style("stroke", "white");
                             }
                                 tip_scatter.hide();
                             })
                             .on("click", function(d)
                             {
                                if (selectedMolecule != null)
                                {
                                     selectedMolecule.style("fill", function(d) {
                                     return d.c;
                                 }).style("stroke-opacity", 0).style("opacity", function(d) {
                                     return d.opacity
                                 }).style("stroke-width", "0px").style("stroke", "white");
                                }
                                var moleculeEntry = moleculeDict[d.i];
                                 var tooltipText = "";
                                 var tooltipQuantText = "";
                                  tooltipText = "<p><br><strong style='color:dodgerblue'>Molecule Identifier:</strong> <span style='color:white'>" + moleculeEntry.name + "</span> <br><br>";
                                     moleculeEntry.metadata.forEach(function(u) {
                                         tooltipText += "<strong style='color:dodgerblue'>" + u.name + ":</strong> <span style='color:white'>" + GetShortString(u.text) + "</span> <br>";
                                     });
                                     tooltipText += "<br></p>";
                                tooltipQuantText = "<p><strong style='color:dodgerblue'>" + "<span>" + scope.condXAxis + "</span>" + " Delta LFQ: " + "</strong> <span style='color:white'>" + d.fc1 + " </span><br>" +
                                         "<strong style='color:dodgerblue'>" + "<span>" + scope.condXAxis + "</span>" + " P-value: " + "</strong> <span style='color:white'>" + d.p1.toExponential(4) + " </span><br>" +
                                         "<strong style='color:dodgerblue'>" + "<span>" + scope.condXAxis + "</span>" + " FDR-adjusted Q-value: " + "</strong> <span style='color:white'>" + d.p1_fdr.toExponential(4) + " </span><br>" +
                                         "<strong style='color:dodgerblue'>" + "<span>" + scope.condXAxis + "</span>" + " Bonferroni-adjusted P-value: " + "</strong> <span style='color:white'>" + d.p1_bonferroni.toExponential(4) + " </span><br><br>" +
                                         "<strong style='color:dodgerblue'>" + "<span>" + scope.condYAxis + "</span>" + " Delta LFQ: " + "</strong> <span style='color:white'>" + d.fc2 + " </span><br>" +
                                         "<strong style='color:dodgerblue'>" + "<span>" + scope.condYAxis + "</span>" + " P-value: " + "</strong> <span style='color:white'>" + d.p2.toExponential(4) + " </span><br>" +
                                         "<strong style='color:dodgerblue'>" + "<span>" + scope.condXAxis + "</span>" + " FDR-adjusted Q-value: " + "</strong> <span style='color:white'>" + d.p2_fdr.toExponential(4) + " </span><br>" +
                                         "<strong style='color:dodgerblue'>" + "<span>" + scope.condXAxis + "</span>" + " Bonferroni-adjusted P-value: " + "</strong> <span style='color:white'>" + d.p2_bonferroni.toExponential(4) + " </span></p>";
                                    scope.$parent.tooltipText = tooltipText;
                                    scope.$parent.tooltipQuantText = tooltipQuantText;
                                    scope.$parent.selectedMolecule = moleculeEntry.name;
                                    scope.$apply();
                                selectedMolecule = d3.select(this);
                                selectedMolecule.style("fill", "orange").style("opacity", 1).style("stroke-width", "2px").style("stroke", "black");

                             });



                         chart_scatter.selectAll("circle")
                             .data(useData, function(d) {
                                 return d.i;
                             })
                             .exit()
                             .remove();

                         chart_scatter.selectAll(".lrline")
                             .data(lrData)
                             .exit()
                             .transition()
                             .duration(2000)
                             .remove();


                         chart_scatter.selectAll(".axisline")
                             .data(axes)
                             .exit()
                             .transition()
                             .duration(2000)
                             .remove();

                         chart_scatter.selectAll(".fcline")
                             .data(fcLines)
                             .exit()
                             .transition()
                             .duration(2000)
                             .remove();


                         chart_scatter.selectAll("g.y.axis")
                             .transition()
                             .duration(2000)
                             .call(yAxis_scatter);

                         chart_scatter.selectAll("g.x.axis")
                             .transition()
                             .duration(2000)
                             .call(xAxis_scatter);
                     }
                     if (spinner != null) {
                         spinner.stop();
                     }
                 }
             }
         }
     }]);

 function zoomed_scatter() {
     var panX = d3.event.translate[0];
     var panY = d3.event.translate[1];
     var scale = d3.event.scale;

     panX = panX > 0 ? 0 : panX;
     var maxX = -(scale - 1) * width_scatter - 10;
     panX = panX < maxX ? maxX : panX;

     panY = panY > 0 ? 0 : panY;
     var maxY = -(scale - 1) * height_scatter - 10;
     panY = panY < maxY ? maxY : panY;

     zoom_scatter.translate([panX, panY]);
     chart_scatter.select(".x.axis").call(xAxis_scatter);
     chart_scatter.select(".y.axis").call(yAxis_scatter);
     chart_scatter.selectAll("circle")
         .attr("cx", function(d) {
             return x_scatter(d.fc1);
         })
         .attr("cy", function(d) {
             return y_scatter((d.fc2));
         })
         .attr("r", function(d) {
             return (d.si / 2.5);
         });
     chart_scatter.selectAll(".lrline").attr("d", line_scatter);
     chart_scatter.selectAll(".axisline").attr("d", line_scatter);
     chart_scatter.selectAll(".fcline").attr("d", line_scatter);
 }

 function linearRegression(data) {
     var lr = {};
     var n = data.length;
     var sum_x = 0;
     var sum_y = 0;
     var sum_xy = 0;
     var sum_xx = 0;
     var sum_yy = 0;

     for (var i = 0; i < data.length; i++) {
         sum_x += (+data[i].fc1);
         sum_y += (+data[i].fc2);
         sum_xy += ((+data[i].fc1) * (+data[i].fc2));
         sum_xx += ((+data[i].fc1) * (+data[i].fc1));
         sum_yy += ((+data[i].fc2) * (+data[i].fc2));
     }

     lr['slope'] = (n * sum_xy - sum_x * sum_y) / (n * sum_xx - sum_x * sum_x);
     lr['intercept'] = (sum_y - lr.slope * sum_x) / n;
     lr['r2'] = Math.pow((n * sum_xy - sum_x * sum_y) / Math.sqrt((n * sum_xx - sum_x * sum_x) * (n * sum_yy - sum_y * sum_y)), 2);

     return lr;
 }

 var line_scatter = d3.svg.line()
     .x(function(d) {
         return x_scatter(d.x);
     })
     .y(function(d) {
         return y_scatter(d.y);
     });

 function startScatterSpin() {
     spinner = new Spinner(opts).spin(document.getElementById('ScatterFullPlot'));
 }