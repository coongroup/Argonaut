var x_clusterLinePlot = null;
var y_clusterLinePlot = null;
var zoom_clusterLinePlot = null;
var height_clusterLinePlot = null;
var width_clusterLinePlot = null;
var tip_clusterLinePlot = null;
var chart_clusterLinePlot = null;
var xAxis_clusterLinePlot = null;
var yAxis_clusterLinePlot = null;

var line_clusterLinePlot = d3.svg.line()
.x(function (d) {
  return x_clusterLinePlot(d.x); 
})
.y(function (d) {
  return y_clusterLinePlot(d.y);
});

function getMolID(item){
  console.log(item);
}

angular.module('coonDataApp')
.directive('clusterLinePlot',  ['$timeout', function ($timeout) {

 return {
   restrict: 'EA',
   scope: {
    data: "=", 
    conditions: "=",
    highlight: "="
  },

  link: function(scope, iElement, iAttrs) {
    height_clusterLinePlot = 365;
    width_clusterLinePlot = 585;
    chart_clusterLinePlot = d3.select('#clusterLinePlotColumn').append("svg").attr("id", "clusterLinePlot")
    .attr("height", height_clusterLinePlot)
    .attr("width", width_clusterLinePlot).append("g");

  // var clusterRect = chart_clusterLinePlot.append("rect").attr("x", 50).attr("y", 40).attr("height", 265).attr("width",520).style("fill", "green");

    scope.$watch('conditions', function(newVals, oldVals) {
      if(scope.conditions!==null && scope.conditions.length!==0)
      {
       chart_clusterLinePlot.selectAll('.axis').remove();
       var nameArray = [];
       for (var item in scope.conditions)
       {
        nameArray.push(scope.conditions[item]);
      }

      x_clusterLinePlot = d3.scale.ordinal()
      .domain(nameArray)
      .rangePoints([50, width_clusterLinePlot-15]);

      xAxis_clusterLinePlot = d3.svg.axis().scale(x_clusterLinePlot).orient("bottom");

      chart_clusterLinePlot.append("g").attr("class", "x axis").attr("transform", "translate(0,305)").call(xAxis_clusterLinePlot).selectAll("text") 
      .style("text-anchor", "end")
      .attr("dx", "-.8em")
      .attr("dy", ".15em")
      .style("font-size", "11px")
      .attr("transform", function(d) {
        return "rotate(-65)" 
      });    

      y_clusterLinePlot = d3.scale.linear()
      .range([305, 40])
      .domain([-2.5, 2.5]);

      yAxis_clusterLinePlot = d3.svg.axis().scale(y_clusterLinePlot).orient("left");
      chart_clusterLinePlot.append("g").attr("class", "y axis").attr("transform", "translate(50,0)").call(yAxis_clusterLinePlot);

         chart_clusterLinePlot.append("svg:clipPath")
              .attr("id", "clip_clusterLine")
              .append("svg:rect")
              .attr("id", "clip-rect-clusterLine")
              .attr("x", "51")
              .attr("y", "40")
              .attr('width', 520)
              .attr('height', 265)
              ;


    }
  });

scope.$watch('data', function(newVals, oldVals){
   chart_clusterLinePlot.selectAll(".clusterLine").remove();
  if (scope.data!== null)
  {

      //update domains
      var currMinY = 100;
      var currMaxY = -100;
      var keys = Object.keys(scope.data);
      var tmpData = [];
      angular.forEach(keys, function(d){
        var count = scope.data[d].length;
        var tmpArray = [];
        for (i = 0; i < count; i++)
        {
          tmpArray.push({i:d, x:scope.conditions[i], y:scope.data[d][i]});
          currMaxY = Math.max(currMaxY, scope.data[d][i]);
          currMinY = Math.min(currMinY, scope.data[d][i]);
        }
        tmpData.push(tmpArray);
      });
      currMinY-=0.5;
      currMaxY+=0.5;
      y_clusterLinePlot = d3.scale.linear()
      .range([305, 40])
      .domain([currMinY, currMaxY]);
      yAxis_clusterLinePlot = d3.svg.axis().scale(y_clusterLinePlot).orient("left");


     // chart_clusterLinePlot.selectAll(".clusterLine").remove();

      //chart_clusterLinePlot.append("path").datum(tmpData).attr("class", "clusterLine").attr("d", line_clusterLinePlot);

      chart_clusterLinePlot.selectAll(".clusterLine")
            .data(tmpData)
            .attr("class", "clusterLine")
                    //.duration(1200)
                    .attr("d", line_clusterLinePlot)
                    .attr("stroke-width", "1px")
                    .attr("stroke", "black")
                    .attr("stroke-linecap", "butt")
                    .attr("fill", "none")
                    .attr("stroke-linejoin", "miter")
                    ;

       chart_clusterLinePlot.append("g").attr("clip-path", "url(#clip_clusterLine)").selectAll(".clusterLine")
                    .data(tmpData)
                    .enter().append("path")
                     .attr("class",  function(d){ return "clusterLine " + d3.select(this)[0][0].__data__[0].i.replace(";","_");})
                     .attr("d", line_clusterLinePlot)
                    .attr("stroke-width", "1px")
                    .attr("stroke", "darkgray")
                    .attr("stroke-opacity", 1)
                    .attr("stroke-linecap", "butt")
                    .attr("fill", "none")
                    .attr("stroke-linejoin", "miter")
                    //.attr("molid", function(d){ return d3.select(this)[0][0].__data__[0].i;})
                    .on("mouseover", function(d){
                      $('#clusterProfTable tr.success').removeClass("success");
                      $('#clusterLineHighlight').attr("stroke", "darkgray").attr("stroke-width", "1px").attr("id", "");
                      d3.select(this).attr("stroke", "dodgerblue").attr("stroke-width", "4px").attr("id","clusterLineHighlight").moveToFront();
                      var molID = d3.select(this)[0][0].__data__[0].i.replace(";","_");
                      scope.$apply(function(){
                         scope.highlight = molID;
                      });
                    })
                    .on("mouseout", function(d){
                       d3.select(this).attr("stroke", "darkgray").attr("stroke-width", "1px");
                       scope.$apply(function(){
                         scope.highlight = "";
                      });
                    })
                    ;

      chart_clusterLinePlot.selectAll("g.y.axis")
      .transition()
      .duration(500)
      .call(yAxis_clusterLinePlot);



    }
  });
}
}
}]);