var margin_barChart=null;
var width_barChart=null;
var height_barChart=null;
var x_barChart=null;
var y_barChart=null;
var xAxis_barChart=null;
var yAxis_barChart=null;
var zoom_barChart=null;
var chart_barChart=null;
var tip_barChart=null;
var max_x_barChart = 5;
var min_x_barChart = -5;
var max_y_barChart = 10;
var min_y_barChart = -10;
var y_label_barChart = null;
var barChart_Y_10 = null;
var barChart_Y_parentheses = null;
var barChart_Y_p = null;
var barChart_Y_value = null;
var x_label_barChart = null;
var barChart_x_abundance = null;
var barChart_x_2 = null;
var barChart_x_parentheses = null;
var barChart_x_condition = null;
var barChart_x_control = null;
var errorBarData = [];

var line_barChart = d3.svg.line()
    .x(function (d) {
        return x_barChart(d.x + .45);
    })
    .y(function (d) {
        return y_barChart(d.y);
    });

angular.module('coonDataApp')
.directive('moleculeBarChart',  ['$timeout', function ($timeout) {
  return {
          restrict: 'EA',
          scope: {
            data: "=",
            label: "@",
            onClick: "&",
            moleculeName: '@attr1',
            pValueCutoff: '@attr2',
            foldChangeCutoff: '@attr3',
            order:'@attr4', 
            testingCorrection: '@attr5'
          },

          link: function(scope, iElement, iAttrs) {

          	var selectedBar = null;

          	var full_page_width = $('#page-content')[0].clientWidth;
          	 margin_barChart = {top: Math.max((full_page_width * 0.025), 20), right:Math.max((full_page_width * 0.075), 50),
          	  bottom: Math.max((full_page_width * 0.025), 30), left: Math.max((full_page_width * 0.075), 50)};
	         width_barChart = Math.max((full_page_width * 0.80),350) - margin_barChart.left - margin_barChart.right;
            	height_barChart = (.65 * width_barChart) - margin_barChart.top - margin_barChart.bottom;


	         x_barChart = d3.scale.linear()
	        .range([0, width_barChart])
	        .domain([min_x_barChart, max_x_barChart]);

		    y_barChart = d3.scale.linear()
		        .range([height_barChart, 0])
		        .domain([min_y_barChart, max_y_barChart]);

		    xAxis_barChart = d3.svg.axis()
		        .scale(x_barChart)
		        .orient("bottom");

		    yAxis_barChart = d3.svg.axis()
		        .scale(y_barChart)
		        .orient("left");

		    chart_barChart = d3.select('#BarChartFullPlot').append("svg").attr("id", "barChartSVG")
		        .attr("height", height_barChart + margin_barChart.top + margin_barChart.bottom)
		        .attr("width", width_barChart + margin_barChart.left + margin_barChart.right)
		        .append("g")
		        .attr("id", "chart_barChart_body")
		        .attr("transform", "translate(" + margin_barChart.left + "," + margin_barChart.top + ")")
		        .attr("width", width_barChart)
		        .attr("height", height_barChart)
		    ;

		    chart_barChart.append("g")
		        .attr('transform', 'translate(0,0)')
		        .attr("class", "y axis")
		        .call(yAxis_barChart);

		    chart_barChart.append("svg:clipPath")
		        .attr("id", "clip_barChart")
		        .append("svg:rect")
		        .attr("id", "clip-rect-barChart")
		        .attr("x", "0")
		        .attr("y", "0")
		        .attr('width', width_barChart)
		        .attr('height', height_barChart)
		    ;

		    tip_barChart = d3.tip()
		        .attr('class', 'd3-tip')
		        .offset([-10, 0]);

		    chart_barChart.call(tip_barChart);

		    y_label_barChart = chart_barChart.append("text")
		        .attr("id", "y_label_barChart")
		        .attr("class", "y_label_barChart")
		        .attr("text-anchor", "end")
		        .attr("y", -40)
		        .attr("x","0")
		        .attr("transform", "rotate(-90)")
		        .style("text-transform", "none")
		        .text("Log2 " + scope.moleculeName + " Fold Change");

           scope.$on('myresize', function()
          {
			var full_page_width = $('#page-content')[0].clientWidth;
          	  margin_barChart = {top: Math.max((full_page_width * .025), 20), right:Math.max((full_page_width * 0.075), 50),
             bottom: Math.max((full_page_width * 0.025), 30), left: Math.max((full_page_width * 0.075), 50)};
             width_barChart = Math.max((full_page_width * 0.8),350) - margin_barChart.left - margin_barChart.right;
             height_barChart = (.65 * width_barChart) - margin_barChart.top - margin_barChart.bottom;

             chart_barChart.attr("height", height_barChart + margin_barChart.top + margin_barChart.bottom)
            .attr("width", width_barChart + margin_barChart.left + margin_barChart.right)
            ;

            $('#barChartSVG').attr("height", height_barChart + margin_barChart.top + margin_barChart.bottom)
            .attr("width", width_barChart + margin_barChart.left + margin_barChart.right);
            
             x_barChart = d3.scale.linear().range([0,width_barChart]).domain([min_x_barChart,max_x_barChart]);
			    y_barChart = d3.scale.linear().range([0,height_barChart]).domain([max_y_barChart, min_y_barChart]);

            xAxis_barChart = d3.svg.axis()
            .scale(x_barChart)
            .orient("bottom");

            yAxis_barChart = d3.svg.axis()
            .scale(y_barChart)
            .orient("left");

          
         $("#chart_barChart_body")
            .attr("width", width_barChart)
            .attr("height", height_barChart)
            .attr("transform", "translate(" + margin_barChart.left + "," + margin_barChart.top + ")");

              y_label_barChart
              .attr("x", 0)
              .attr("y", -40);

              $('#clip-rect-barChart')
            .attr("x", "0")
            .attr("y", "0")
            .attr('width', width_barChart)
            .attr('height', height_barChart)
            ;

           chart_barChart.selectAll("g.y.axis")
          .transition()
          .duration(0)
          .attr('transform', 'translate(0,0)')
          .call(yAxis_barChart);

          chart_barChart.selectAll("g.x.axis")
          .transition()
          .duration(0)
          .attr("transform", "translate(0," + height_barChart + ")")
          .call(xAxis_barChart);
          chart_barChart.selectAll('.errorBar').attr("d", line_barChart).attr("stroke-dasharray", "0 0")
			        .attr("stroke-dashoffset", "0");

           chart_barChart.selectAll(".bar")
			        .attr("x", function (d) {
			            return x_barChart(d.count);
			        })
			        .attr("width", function (d) {
			            return x_barChart(.9);
			        })
			        .attr("y", function (d) {
			            if (d.fold_change < 0) {
			                return y_barChart(0);
			            }
			            else {
			                return y_barChart(+d.fold_change);
			            }
			        })
			        .attr("height", function (d) {
			            if (+d.fold_change < 0) {
			                return (y_barChart(+d.fold_change) - y_barChart(0));
			            }
			            else {
			                return (y_barChart(0) - y_barChart(+d.fold_change));
			            }
			        });

			
          });

			

			scope.$watch('data', function() { 
            scope.update(scope.data);
          });

			scope.$watch('pValueCutoff', function() { 
            scope.updateBarColor();
          });

			scope.$watch('foldChangeCutoff', function() { 
            scope.updateBarColor();
          });

			scope.$watch('moleculeName', function() { 
            y_label_barChart.text("Log2 " + scope.moleculeName + " Fold Change");
          });

			scope.$watch('order', function() {
            scope.update(scope.data);
          });

			scope.$watch('testingCorrection', function(){
				scope.updateBarColor();
			})

			scope.updateBarColor = function()
			{
				chart_barChart.selectAll(".bar")[0].forEach(function(d){
					d.p = +d.p_value;
			    	if (scope.testingCorrection=="fdradjusted")
			    	{
			    		d.p = +d.p_value_fdr;
			    	}
			    	if (scope.testingCorrection=="bonferroni")
			    	{
			    		d.p=+d.p_value_bonferroni;
			    	}
			    	d.color = "gray";
			    	d.originalColor = "gray";
			    	if (d.p < scope.pValueCutoff)
		            {
		                if (Math.abs(d.fold_change)> scope.foldChangeCutoff)
		                {
		                    d.color = "green";
		                    d.originalColor = "green";
		                }
		                d.color = "dodgerblue";
		                d.originalColor = "dodgerblue";
		            }
				});
				if (selectedBar != null)
				{
					selectedBar.attr("color", "orange");
				}
				chart_barChart.selectAll(".bar").style("fill", function (d) {
			            return d.color;
			        });
			}

			scope.update = function(data)
			{
				 if (data.length > 0)
            {
				var count = 0.5;
			    var minY = 10000000;
			    var maxY = -10000000;
			    var currentBarData = [];
			    var currentLineData = [];
			    errorBarData = [];
			    var countDict = [];
			    data.forEach(function(d)
			    {
			    	d.p = +d.p_value;
			    	if (scope.testingCorrection=="fdradjusted")
			    	{
			    		d.p = +d.p_value_fdr;
			    	}
			    	if (scope.testingCorrection=="bonferroni")
			    	{
			    		d.p=+d.p_value_bonferroni;
			    	}
			    	 d.color="gray";
			    	 d.originalColor = "gray";
			    	 if (d.p < scope.pValueCutoff)
		            {
		                if (Math.abs(d.fold_change)> scope.foldChangeCutoff)
		                {
		                    d.color = "green";
		                    d.originalColor = "green";
		                }
		                else
		                {
			                d.color = "dodgerblue";
			                d.originalColor = "dodgerblue";
			            }
		            }
			           

			        d.count = count;
			        countDict[d.condition_name] = count;
			        var lowSD = (+d.fold_change) - (+d.std_dev);
			        var highSD = (+d.fold_change) + (+d.std_dev);
			        if (lowSD < minY)
			        {
			            minY = lowSD;
			        }
			        if (highSD > maxY)
			        {
			            maxY = highSD;
			        }
			        errorBarData.push([{x:count, y:lowSD, id:count+"sd"}, { x:count, y:highSD, id:count+"sd"}]);
			        errorBarData.push([{x:count -.2, y:lowSD, id:count+"lsd"}, { x:count +.2, y:lowSD, id:count+"lsd"}]);
			        errorBarData.push([{ x:count -.2, y:highSD, id:count+"hsd"}, { x:count +.2, y:highSD, id:count+"hsd"}]);
			        count++;
			    });
			    errorBarData.push([{x: -.45, y:0, id:"origin"}, {x:count -.45, y:0, id:"origin"}]);
			    if (minY==10000000 && maxY== -10000000)
			    {
			        minY = -9.5;
			        maxY = 9.5;
			    }
			    minY-=.5;
			    maxY+=.5;
			    minY = Math.min(minY,0);
			    x_barChart = d3.scale.linear().range([0,width_barChart]).domain([0,count]);
			    y_barChart = d3.scale.linear().range([0,height_barChart]).domain([maxY, minY]);

			    min_x_barChart = 0;
			    max_x_barChart = count;
			    min_y_barChart = minY;
			    max_y_barChart = maxY;

			    xAxis_barChart.scale(x_barChart);
			    yAxis_barChart.scale(y_barChart);

			    if (selectedBar != null)
			    {
			    	selectedBar[0][0].__data__.color = "orange";
			    }

			    chart_barChart.selectAll(".bar")
			        .data(data, function(d){
			            return d.condition_id;
			        })
			        .transition()
			        .duration(1200)
			        .attr("class", "bar")
			        .attr("x", function (d) {
			            return x_barChart(d.count);
			        })
			        .attr("width", function (d) {
			            return x_barChart(.9);
			        })
			        .attr("y", function (d) {
			            if (d.fold_change < 0) {
			                return y_barChart(0);
			            }
			            else {
			                return y_barChart(+d.fold_change);
			            }
			        })
			        .attr("height", function (d) {
			            if (+d.fold_change < 0) {
			                return (y_barChart(+d.fold_change) - y_barChart(0));
			                return (y_barChart(0) - y_barChart(+d.fold_change));
			            }
			            else {
			                return (y_barChart(0) - y_barChart(+d.fold_change));
			            }
			        })
			        .style("fill", function (d) {
			           return d.color;
			        })
			        .attr("opacity", ".7")
			        .attr("shape-rendering", "crispEdges");

			    chart_barChart.selectAll(".bar")
			        .data(data, function(d){
			            return d.condition_id;
			        })
			        .enter().append("rect")
			        .attr("height", (0))
			        .attr("color", "dodgerblue")
			        .transition()
			        .duration(1200)
			        .attr("class", "bar")
			        .attr("x", function (d) {
			            return x_barChart(d.count);
			        })
			        .attr("width", function (d) {
			            return x_barChart(.9);
			        })
			        .attr("y", function (d) {
			            if (d.fold_change < 0) {
			                return y_barChart(0);
			            }
			            else {
			                return y_barChart(+d.fold_change);
			            }
			        })
			        .attr("height", function (d) {
			            if (+d.fold_change < 0) {
			                return (y_barChart(+d.fold_change) - y_barChart(0));
			            }
			            else {
			                return (y_barChart(0) - y_barChart(+d.fold_change));
			            }
			        })
			        .style("fill", function (d) {
			            return d.color;
			        })
			        .attr("opacity", ".7")
			        .attr("shape-rendering", "crispEdges");

			    chart_barChart.selectAll(".bar")
			        .data(data, function(d){
			            return d.condition_id;
			        })
			        .exit()
			        .transition()
			        .duration(1200)
			        .attr("height", (0))
			        .attr("fill", function (d) {
			            return d.color;
			        })
			        .remove();


			    chart_barChart.selectAll(".errorBar")
			        .data(errorBarData, function(d){return d[0].id})
			        .attr("class", "errorBar")
			        .attr("stroke-dasharray", "0 0")
			        .attr("stroke-dashoffset", "0")
			        .transition()
			        .duration(1200)
			        .attr("d", line_barChart)
			        .attr("stroke-width", "1px")
			        .attr("stroke", "black")
			        ;
			    ;

			    chart_barChart.append("g").selectAll(".errorBar")
			        .data(errorBarData, function(d){return d[0].id})
			        .enter().append("path")
			        .attr("class", "errorBar")
			        .attr("d", line_barChart)
			        .attr("stroke-width", "1px")
			        .attr("stroke", "black")
			        .attr("stroke-dasharray", function () {
			            return (this.getTotalLength() + " " + this.getTotalLength());
			        })
			        .attr("stroke-dashoffset", function () {
			            return (this.getTotalLength());
			        })
			        .transition()
			        .duration(1200)
			        .attr("stroke-width", "1px")
			        //.attr("stroke-dasharray", "0 0")
			        .attr("stroke-dashoffset", "0")
			        ;
			    ;

			    chart_barChart.selectAll(".errorBar")
			        .data(errorBarData, function(d){return d[0].id})
			        .exit()
			        .attr("stroke-dasharray", function () {
			            // console.log(this.getTotalLength());
			            return (this.getTotalLength() + " " + this.getTotalLength());
			        })
			        .transition()
			        .duration(1200)

			        .attr("stroke-dashoffset", function () {
			            return (this.getTotalLength());
			        })
			        .remove();

			    chart_barChart.selectAll("rect")
			        .on("mouseover", function (d) {
			        	if (d3.select(this)[0][0].style.fill != "orange")
			        	{
			            	d3.select(this).style("opacity", 1).style("fill", "#FFC10B");
			        	}
			            var moleculeEntry = moleculeDict[d.mol_id];
			            var namePart = "<strong style='color:dodgerblue'>Condition Name: </strong> <span style='color:white'>" + d.condition_name + "</span> <br><br>"
			                + "<strong style='color:dodgerblue'>Molecule Identifier:</strong> <span style='color:white'>" + moleculeEntry.name + "</span> <br>";
			            moleculeEntry.metadata.forEach(function(u)
			            {
			                namePart += "<strong style='color:dodgerblue'>" + u.name + ":</strong> <span style='color:white'>" + GetShortString(u.text) + "</span> <br>";
			            });
			            d.p_value = +d.p_value;
			            d.p_value_fdr = +d.p_value_fdr;
			            d.p_value_bonferroni = +d.p_value_bonferroni;
			            namePart += "<br>"
			                + "<strong style='color:dodgerblue'>" + "<span>" + d.condition_name + "</span>" + " Delta LFQ: " + "</strong> <span style='color:white'>" + d.fold_change + " </span><br>"
			                + "<strong style='color:dodgerblue'>" + "<span>" + d.condition_name + "</span>" + " P-value: " + "</strong> <span style='color:white'>" + d.p_value.toExponential(4) + " </span><br>"
			                + "<strong style='color:dodgerblue'>" + "<span>" + d.condition_name + "</span>" + " FDR-adjusted Q-value: " + "</strong> <span style='color:white'>" + d.p_value_fdr.toExponential(4) + " </span><br>"
			                + "<strong style='color:dodgerblue'>" + "<span>" + d.condition_name + "</span>" + " Bonferroni P-value: " + "</strong> <span style='color:white'>" + d.p_value_bonferroni.toExponential(4) + " </span><br>";
			            tip_barChart.html(namePart);

			            tip_barChart.show();

			        })
					.on("click", function(d)
					{
						if (selectedBar != null)
						{
							selectedBar.style("opacity", .7).style("fill", function (d) {
								d.color = d.originalColor;
			                return (d.originalColor);
			            });
						}
						  d3.select(this).style("fill", "orange").style("opacity", .7);
						  var moleculeEntry = moleculeDict[d.mol_id];
			            var namePart = "<p><strong style='color:dodgerblue'>Condition Name: </strong> <span style='color:white'>" + d.condition_name + "</span> <br>";
			             namePart += "<br>"
			                + "<strong style='color:dodgerblue'>" + "<span>" + d.condition_name + "</span>" + " Delta LFQ: " + "</strong> <span style='color:white'>" + d.fold_change + " </span><br>"
			                + "<strong style='color:dodgerblue'>" + "<span>" + d.condition_name + "</span>" + " P-value: " + "</strong> <span style='color:white'>" + d.p_value.toExponential(4) + " </span><br>"
			                + "<strong style='color:dodgerblue'>" + "<span>" + d.condition_name + "</span>" + " FDR-adjusted Q-value: " + "</strong> <span style='color:white'>" + d.p_value_fdr.toExponential(4) + " </span><br>"
			                + "<strong style='color:dodgerblue'>" + "<span>" + d.condition_name + "</span>" + " Bonferroni P-value: " + "</strong> <span style='color:white'>" + d.p_value_bonferroni.toExponential(4) + " </span><br></p>";
			                scope.$parent.tooltipQuantText = namePart;
			                 scope.$apply();
			                selectedBar = d3.select(this);

					})
			        .on("mouseout", function () {
			        	if (d3.select(this)[0][0].style.fill!="orange")
			        	{
			            d3.select(this).style("opacity", .7).style("fill", function (d) {
			                return (d.color);
			            });
			        }
			            tip_barChart.hide();
			        });

			    chart_barChart.selectAll("g.y.axis")
			        .transition()
			        .duration(1200)
			        .call(yAxis_barChart);

			    chart_barChart.selectAll("g.x.axis")
			        .transition()
			        .duration(1200)
			        .call(xAxis_barChart);

			        chart_barChart.selectAll(".bar").moveToBack();
			}
			}
			
		}
      }
  }]);