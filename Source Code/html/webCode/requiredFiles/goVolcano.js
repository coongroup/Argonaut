var margin_goVolcano=null;
var width_goVolcano=null;
var height_goVolcano=null;
var x_goVolcano=null;
var y_goVolcano=null;
var xAxis_goVolcano=null;
var yAxis_goVolcano=null;
var zoom_goVolcano=null;
var chart_goVolcano=null;
var tip_goVolcano=null;
var max_x_goVolcano = 5;
var min_x_goVolcano = -5;
var max_y_goVolcano = 10;
var min_y_goVolcano = 0;
var y_label_goVolcano = null;
var goVolcano_Y_10 = null;
var goVolcano_Y_parentheses = null;
var goVolcano_Y_p = null;
var goVolcano_Y_value = null;
var x_label_goVolcano = null;
var goVolcano_x_abundance = null;
var goVolcano_x_2 = null;
var goVolcano_x_parentheses = null;
var goVolcano_x_condition = null;
var goVolcano_x_control = null;
var goVolcanoHoldPt = null;
var goVolcano_Chart_Parent = null;
var goHighlightColor ="deeppink";
var molHighlightColor="dodgerblue";
var _timeout;

angular.module('coonDataApp')
.directive('goVolcano',  ['$timeout', function ($timeout) {


        return {
          restrict: 'EA',
          scope: {
            data: "=",
            label: "@",
            onClick: "&",
            goTerm: '@attr1',
            testingCorrection: '@attr2',
            conditionName : '@attr3',
            highlight: '=',
            go: '='
          },

          link: function(scope, iElement, iAttrs) {


            var full_page_width = $('#page-content')[0].clientWidth
            margin_goVolcano = {top: Math.max((full_page_width * .025), 10), right:Math.max((full_page_width * 0.005), 50),
            bottom: Math.max((full_page_width * 0.005), 50), left: Math.max((full_page_width * 0.005), 50)},
            width_goVolcano = (full_page_width * 0.38) - margin_goVolcano.left - margin_goVolcano.right,
            height_goVolcano = (width_goVolcano * .8) - margin_goVolcano.top - margin_goVolcano.bottom;

            x_goVolcano = d3.scale.linear()
            .range([0, width_goVolcano])
            .domain([min_x_goVolcano, max_x_goVolcano]);

            y_goVolcano = d3.scale.linear()
            .range([height_goVolcano, 0])
            .domain([min_y_goVolcano, max_y_goVolcano]);

            xAxis_goVolcano = d3.svg.axis()
            .scale(x_goVolcano)
            .orient("bottom");

            yAxis_goVolcano = d3.svg.axis()
            .scale(y_goVolcano)
            .orient("left");

            zoom_goVolcano = d3.behavior.zoom()
            .x(x_goVolcano)
            .y(y_goVolcano)
            .scaleExtent([1, 50])
            .on("zoom", zoomedgoVolcano);

            chart_goVolcano = d3.select('#goVolcanoColumn').append("svg").attr("id", "goVolcanoSVG")
            .attr("height", height_goVolcano + margin_goVolcano.top + margin_goVolcano.bottom)
            .attr("width", width_goVolcano + margin_goVolcano.left + margin_goVolcano.right)
            .call(zoom_goVolcano).on("dblclick.zoom", null)
            .append("g")
            .attr("id", "chart_goVolcano_body")
            .attr("transform", "translate(" + margin_goVolcano.left + "," + margin_goVolcano.top + ")")
            .attr("width", width_goVolcano)
            .attr("height", height_goVolcano);


            chart_goVolcano.append("g")
            .attr("class", "x axis")
            .attr("transform", "translate(0," + height_goVolcano + ")")
            .call(xAxis_goVolcano);

            chart_goVolcano.append("g")
            .attr('transform', 'translate(0,0)')
            .attr("class", "y axis")
            .call(yAxis_goVolcano);

            chart_goVolcano.append("svg:clipPath")
            .attr("id", "clip_goVolcano")
            .append("svg:rect")
            .attr("id", "clip-rect-goVolcano")
            .attr("x", "0")
            .attr("y", "0")
            .attr('width', width_goVolcano)
            .attr('height', height_goVolcano)
            ;

            tip_goVolcano = d3.tip()
            .attr('class', 'd3-tip')
            .offset([-10, 0]);

            chart_goVolcano.call(tip_goVolcano);

            y_label_goVolcano = chart_goVolcano.append("text")
            .attr("id", "y_label_goVolcano")
            .attr("class", "y_label_goVolcano")
            .attr("text-anchor", "end")
            .attr("y", -40)
            .attr("x","0")
            .attr("transform", "rotate(-90)")
            .style("text-transform", "none")
            .text("-log");

            goVolcano_Y_10 = chart_goVolcano.append("tspan");
            goVolcano_Y_parentheses = y_label_goVolcano.append("tspan").attr("class", "y_label");
            goVolcano_Y_p = y_label_goVolcano.append("tspan").attr("class", "y_label");
            goVolcano_Y_value = y_label_goVolcano.append("tspan").attr("class", "y_label");
            goVolcano_Y_10.text("10").style("font-size", "smaller").attr("baseline-shift", "sub");
            goVolcano_Y_parentheses.text("(");
              goVolcano_Y_p.text("p").style("font-style", "italic");
              goVolcano_Y_value.text("-value)");

              x_label_goVolcano = chart_goVolcano.append("text")
              .attr("id", "x_label_goVolcano")
              .attr("text-anchor", "end")
              .attr("x", width_goVolcano)
              .attr("y", height_goVolcano+40)
              .style("text-transform", "none")
              .attr("class", "y_label")
              .text("");

              goVolcano_x_abundance = x_label_goVolcano.append("tspan");
              goVolcano_x_abundance.text(scope.conditionName + " abundance log").attr("class", "y_label");
              goVolcano_x_2 = x_label_goVolcano.append("tspan");
              goVolcano_x_2.text("2").style("font-size", "smaller").attr("baseline-shift", "sub");
              goVolcano_x_parentheses = x_label_goVolcano.append("tspan").attr("class", "y_label");
              goVolcano_x_parentheses.text("(").attr("class", "y_label");
              goVolcano_x_condition = x_label_goVolcano.append("tspan").attr("class", "y_label");
              goVolcano_x_condition.text("condition").style("text-transform", "lowercase").attr("class", "y_label");
              goVolcano_x_control = x_label_goVolcano.append("tspan").attr("class", "y_label");
              goVolcano_x_control.text("/control)");


          scope.$on('myresize', function()
          {
             var full_page_width =  $('#page-content')[0].clientWidth
            margin_goVolcano = {top: Math.max((full_page_width * .025), 10), right:Math.max((full_page_width * 0.005), 50),
            bottom: Math.max((full_page_width * 0.005), 50), left: Math.max((full_page_width * 0.005), 50)},
            width_goVolcano = Math.max((full_page_width * 0.38),350) - margin_goVolcano.left - margin_goVolcano.right,
            height_goVolcano = (width_goVolcano * .8) - margin_goVolcano.top - margin_goVolcano.bottom;

             chart_goVolcano.attr("height", height_goVolcano + margin_goVolcano.top + margin_goVolcano.bottom)
            .attr("width", width_goVolcano + margin_goVolcano.left + margin_goVolcano.right)
            ;

            $('#goVolcanoSVG').attr("height", height_goVolcano + margin_goVolcano.top + margin_goVolcano.bottom)
            .attr("width", width_goVolcano + margin_goVolcano.left + margin_goVolcano.right);
            
            x_goVolcano = d3.scale.linear()
            .range([0, width_goVolcano])
            .domain([min_x_goVolcano, max_x_goVolcano]);

            y_goVolcano = d3.scale.linear()
            .range([height_goVolcano, 0])
            .domain([min_y_goVolcano, max_y_goVolcano]);

            xAxis_goVolcano = d3.svg.axis()
            .scale(x_goVolcano)
            .orient("bottom");

            yAxis_goVolcano = d3.svg.axis()
            .scale(y_goVolcano)
            .orient("left");

            zoom_goVolcano
            .x(x_goVolcano)
            .y(y_goVolcano)
            .scaleExtent([1, 50])
           ;

          
         $("#chart_goVolcano_body")
            .attr("width", width_goVolcano)
            .attr("height", height_goVolcano)
            .attr("transform", "translate(" + margin_goVolcano.left + "," + margin_goVolcano.top + ")");

              x_label_goVolcano
              .attr("x", width_goVolcano)
              .attr("y", height_goVolcano+40);

              $('#clip-rect-goVolcano')
            .attr("x", "0")
            .attr("y", "0")
            .attr('width', width_goVolcano)
            .attr('height', height_goVolcano)
            ;

            chart_goVolcano.selectAll("circle")
            .attr("cx", function (d) {
              return x_goVolcano(d.fc);
            })
            .attr("cy", function (d) {
              return y_goVolcano((-Math.log10(d.p)));
            });

           chart_goVolcano.selectAll("g.y.axis")
          .transition()
          .duration(0)
          .attr('transform', 'translate(0,0)')
          .call(yAxis_goVolcano);

          chart_goVolcano.selectAll("g.x.axis")
          .transition()
          .duration(0)
          .attr("transform", "translate(0," + height_goVolcano + ")")
          .call(xAxis_goVolcano);
          });


          // watch for data changes and re-render

          scope.$watch('data', function(newVals, oldVals) {
           scope.update(scope.data);
         });

            scope.$watch('testingCorrection', function(){
              scope.update(scope.data);
            });

            scope.$watch('go', function(){
               _timeout = $timeout(function() {
            scope.updateColor();
            _timeout = null;
               }, 300);
              
            });

            scope.$watch('highlight', function(){
               _timeout = $timeout(function() {
               
               scope.highlight.display!==undefined ? scope.updateViewMoleculesInChart() : null;
              
            _timeout = null;
               }, 300)
            });

            scope.updateViewMoleculesInChart = function()
            {
              var currCircles =  chart_goVolcano.selectAll("circle")[0];
              var pCutoff = parseFloat(scope.highlight.pValueCutoff);
              var fcCutoff = parseFloat(scope.highlight.fcCutoff);

              angular.forEach(currCircles, function(d){
                if (scope.highlight.fcSymbol===">" && scope.highlight.pValSymbol===">"){if(d.__data__.fc > fcCutoff && d.__data__.p > pCutoff){scope.setMolColor(d);}else{scope.unsetMolColor(d);}}
                if (scope.highlight.fcSymbol===">" && scope.highlight.pValSymbol==="<"){if(d.__data__.fc > fcCutoff && d.__data__.p < pCutoff){scope.setMolColor(d);}else{scope.unsetMolColor(d);}}
                if (scope.highlight.fcSymbol===">" && scope.highlight.pValSymbol==="> or <"){if(d.__data__.fc > fcCutoff){scope.setMolColor(d);}else{scope.unsetMolColor(d);}}
                if (scope.highlight.fcSymbol==="<" && scope.highlight.pValSymbol===">"){if(d.__data__.fc < fcCutoff && d.__data__.p > pCutoff){scope.setMolColor(d);}else{scope.unsetMolColor(d);}}
                if (scope.highlight.fcSymbol==="<" && scope.highlight.pValSymbol==="<"){if(d.__data__.fc < fcCutoff && d.__data__.p < pCutoff){scope.setMolColor(d);}else{scope.unsetMolColor(d);}}
                if (scope.highlight.fcSymbol==="<" && scope.highlight.pValSymbol==="> or <"){if(d.__data__.fc < fcCutoff){scope.setMolColor(d);}else{scope.unsetMolColor(d);}}
                if (scope.highlight.fcSymbol==="> or <" && scope.highlight.pValSymbol===">"){if(d.__data__.p > pCutoff){scope.setMolColor(d);}else{scope.unsetMolColor(d);}}
                if (scope.highlight.fcSymbol==="> or <" && scope.highlight.pValSymbol==="<"){if(d.__data__.p < pCutoff){scope.setMolColor(d);}else{scope.unsetMolColor(d);}}
                if (scope.highlight.fcSymbol==="> or <" && scope.highlight.pValSymbol==="> or <"){scope.setMolColor(d);}
              });

           chart_goVolcano.selectAll("circle")
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

              if (spinner != null)
              {
                spinner.stop();
              }
            }

            scope.setMolColor = function(d)
            {
              d.__data__.c = molHighlightColor;
              d.__data__.si=8;
              d.__data__.opacity =1;
              d.__data__.sw = "1px";
              d3.select(d).moveToFront();
            }
            scope.unsetMolColor = function(d)
            {
              d.__data__.c = "gray";
              d.__data__.si=6;
              d.__data__.opacity =0.6;
              d.__data__.sw = "0px";
            }

            scope.updateColor = function()
            {
               var goTermDict = [];
                if(scope.go!==undefined)
                {
                  if (scope.go.length>0)
                  {
                    angular.forEach(scope.go, function(d){
                      goTermDict[d]="";
                    });
                  }
                }
               var currCircles = chart_goVolcano.selectAll("circle");
               currCircles[0].forEach(function(d)
               {
                  if(goTermDict[d.__data__.i] !== undefined)
                  {
                    d.__data__.c = goHighlightColor;
                    d.__data__.si=8;
                    d.__data__.opacity =1;
                    d.__data__.sw = "1px";
                    d3.select(d).moveToFront();
                  }
                  else
                  {
                    d.__data__.c = "gray";
                    d.__data__.si=6;
                    d.__data__.opacity =0.6;
                    d.__data__.sw = "0px";
                  }
              });

              chart_goVolcano.selectAll("circle")
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

              if (spinner != null)
              {
                spinner.stop();
              }

            }


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
                    
             
            var goTermDict = [];
            if(scope.go!==undefined)
            {
              if (scope.go.length>0)
              {
                angular.forEach(scope.go, function(d){
                  goTermDict[d]="";
                });
              }
            }

              var count = 0;
             useData.forEach(function (d) {
              count++;
              d.vis = "visible";
              d.opacity = 0.6;
              d.si = 6;
              d.c = "gray";
              d.sw = "0px";
              if (goTermDict[d.unique_identifier_id]!==undefined)
              {
                d.c = goHighlightColor;
                d.si=8;
                d.opacity =1;
                d.sw = "1px";
              }
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
              d.highlight = false;
      
              d.transition = 0;
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
              min_x_goVolcano = minX;
              max_x_goVolcano = maxX;
              min_y_goVolcano = minY;
              max_y_goVolcano = maxY;
             }


             x_goVolcano = d3.scale.linear().range([0,width_goVolcano])
             .domain([min_x_goVolcano,max_x_goVolcano]);
             xAxis_goVolcano.scale(x_goVolcano).orient("bottom");
             y_goVolcano = d3.scale.linear().range([height_goVolcano,0])
             .domain([min_y_goVolcano,max_y_goVolcano]);
             yAxis_goVolcano.scale(y_goVolcano).orient("left");

             yAxis_goVolcano.scale(y_goVolcano);
             xAxis_goVolcano.scale(x_goVolcano);
             zoom_goVolcano.x(x_goVolcano);
             zoom_goVolcano.y(y_goVolcano);

             chart_goVolcano.selectAll("circle")
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
              return x_goVolcano(d.fc);
            })
             .attr("cy", function (d) {
              return y_goVolcano(-Math.log10(d.p));
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

             chart_goVolcano.append("g").attr("clip-path", "url(#clip_goVolcano)").selectAll("circle")
             .data(useData, function(d){return d.i;})
             .enter()
             .append("circle")
             .attr("r", function (d) {
              return (d.si / 2);
            })
             .attr("cx", x_goVolcano(0))
             .attr("cy", y_goVolcano(-.2))
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
              return x_goVolcano(d.fc);
            })
             .attr("cy", function (d) {
              return y_goVolcano(-Math.log10(d.p));
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

             chart_goVolcano.selectAll("circle")
             .on("mouseover", function (d) {
              var moleculeEntry = moleculeDict[d.i];
              d3.select(this).style("stroke", "black").style("stroke-opacity", 1).style("opacity", 1).style("fill", "#FFC10B").style("stroke-width", "2px");
              tip_goVolcano.html(function (i) {
                    var namePart = "<strong style='color:dodgerblue'>Molecule Identifier:</strong> <span style='color:white'>";
                if (scope.$parent.goShortenLongTerms)
                {
                  moleculeEntry.name >60 ? namePart += moleculeEntry.name.substring(0, 60) + "..." :  namePart +=moleculeEntry.name;     
                }
                else
                {
                 namePart +=moleculeEntry.name; 
               }

               namePart+= "</span> <br>";
               moleculeEntry.metadata.forEach(function(u)
               {
                var result = scope.$parent.featureMetadataTerms.find(x => x.name===u.name);
                if (result!==undefined)
                {
                  if (result.selected)
                  {
                    namePart += "<strong style='color:dodgerblue'>" + u.name + ":</strong> <span style='color:white'>"
                    if (scope.$parent.goShortenLongTerms)
                    {
                      u.text.length >60 ? namePart += u.text.substring(0, 60) + "..." :  namePart += u.text;
                    }
                    else
                    {
                      namePart += u.text;
                    }
                    namePart += "<br>";
                                    //namePart += "<strong style='color:dodgerblue'>" + u.name + ":</strong> <span style='color:white'>" + GetShortString(u.text) + "</span> <br>";
                                  }
                                }
                              });
               
                namePart += "<br>";
                d.p_value = +d.p_value;
                d.p_value_fdr = +d.p_value_fdr;
                d.p_value_bonferroni = +d.p_value_bonferroni;
                return (namePart
                  + "<strong style='color:dodgerblue'>" + "LFQ fold change: " + "</strong> <span style='color:white'>" + (Math.round(d.fc*10000)/10000) + " </span><br>"
                  + "<strong style='color:dodgerblue'>" + "P-Value: " + "</strong> <span style='color:white'>" + d.p_value.toExponential(4) + " </span><br>"
                  );
              });
              d3.select(this).moveToFront();
              tip_goVolcano.show();
            }).on("dblclick.zoom", null)
            .on("mouseout", function (d) {
              var moleculeEntry = moleculeDict[d.i];
              var selDataPoint = d3.select(this);
             {
                 d3.select(this).style("stroke", "black").style("stroke-opacity", 1).style("opacity", d.opacity).style("fill", d.c).style("stroke-width", d.sw);
             }

              tip_goVolcano.hide();
            });

            chart_goVolcano.selectAll("circle").sort(function(a,b){
              if(a.c===goHighlightColor && b.c!==goHighlightColor) return 1;
              else return -1;
            });

            chart_goVolcano.selectAll("circle")
            .data(useData, function(d){return d.i;})
            .exit()
            .remove();

            chart_goVolcano.selectAll("g.y.axis")
            .transition()
            .duration(0)
            .call(yAxis_goVolcano);

            chart_goVolcano.selectAll("g.x.axis")
            .transition()
            .duration(0)
            .call(xAxis_goVolcano);

            if (spinner != null)
            {
              spinner.stop();
            }
            goVolcano_x_abundance.text(scope.conditionName + " abundance log");
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

     d3.selection.prototype.moveToFront = function() {  
      return this.each(function(){
        this.parentNode.appendChild(this);
      });
    };

function zoomedgoVolcano()
{
 var panX = d3.event.translate[0];
 var panY = d3.event.translate[1];
 var scale = d3.event.scale;

 panX = panX > 0 ? 0 : panX;
 var maxX = -(scale - 1) * width_goVolcano - 10;
 panX = panX < maxX ? maxX : panX;

 panY = panY > 0 ? 0 : panY;
 var maxY = -(scale - 1) * height_goVolcano - 10;
 panY = panY < maxY ? maxY : panY;

 zoom_goVolcano.translate([panX, panY]);
 chart_goVolcano.select(".x.axis").call(xAxis_goVolcano);
 chart_goVolcano.select(".y.axis").call(yAxis_goVolcano);
 chart_goVolcano.selectAll("circle")
 .attr("cx", function (d) {
  return x_goVolcano(d.fc);
})
 .attr("cy", function (d) {
  return y_goVolcano(-Math.log10(d.p));
})
 .attr("r", function (d) {
  return (d.si / 2);
})
 ;
}
