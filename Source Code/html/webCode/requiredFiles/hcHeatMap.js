var margin_hcHeatMap=null;
var width_hcHeatMap=null;
var height_hcHeatMap=null;
var x_hcHeatMap=null;
var y_hcHeatMap=null;
var xAxis_hcHeatMap=null;
var yAxis_hcHeatMap=null;
var zoom_hcHeatMap=null;
var chart_hcHeatMap=null;
var tip_hcHeatMap=null;
var max_x_hcHeatMap = 5;
var min_x_hcHeatMap = -5;
var max_y_hcHeatMap = 10;
var min_y_hcHeatMap = 0;
var y_label_hcHeatMap = null;
var hcHeatMap_Y_10 = null;
var hcHeatMap_Y_parentheses = null;
var hcHeatMap_Y_p = null;
var hcHeatMap_Y_value = null;
var x_label_hcHeatMap = null;
var hcHeatMap_x_abundance = null;
var hcHeatMap_x_2 = null;
var hcHeatMap_x_parentheses = null;
var hcHeatMap_x_condition = null;
var hcHeatMap_x_control = null;
var hcHeatMapHoldPt = null;
var hcHeatMap_Chart_Parent = null;
var visibility_dict = null;
var canvas = null;
var context = null;
var detachedContainer = null;
var dataContainer = null;
var t = null;
var inttt = 0;
var heatScale = 1;

var rowLabelBar = null;
var colLabelBar = null;
var rowClusters = {};
var columnClusters = {};
var rowLineData = [];
var colLineData = [];
var showLabels = false;
var colLabelBar = null;
var rowLabelBar = null;
var rowCount = 0;
var heatMapClip = null;

var line_dendrogram = d3.svg.line()
.x(function (d) {
  return x_hcHeatMap(d.x);
})
.y(function (d) {
  return y_hcHeatMap(d.y);
});

angular.module('coonDataApp')
.directive('hcHeatMap',  ['$timeout', function ($timeout) {

 return {
   restrict: 'EA',
   scope: {
    data: "=",
    cluster: "=",
    clusterkey: "=",
    label: "@",
    onClick: "&",
    showLabels: '@attr1',
    minFC : '@attr2',
    maxFC : '@attr3'
  },

  link: function(scope, iElement, iAttrs) {

   x_hcHeatMap = d3.scale.linear()
   .range([0, 600])
   .domain([0, 50]);

   y_hcHeatMap = d3.scale.linear()
   .range([800, 0])
   .domain([0, 4200]);

   zoom_hcHeatMap = d3.behavior.zoom()
   .y(y_hcHeatMap)
   .scaleExtent([1, 150])
   .on("zoom", function(d){ zoomedhcHeatMap(); moveRects(); });

   height_hcHeatMap = 820;
   width_hcHeatMap = 620;

   chart_hcHeatMap = d3.select('#hcHeatMapColumn').append("svg").attr("id", "heatMapSVG").attr("max-height", "820px").attr("max-width", "620px")
   .attr("height", height_hcHeatMap)
   .attr("width", width_hcHeatMap)
   ;

   tip_hcHeatMap = d3.tip()
   .attr('class', 'd3-tip')
   .direction('n')
   .offset([5, 0]);

   //chart_hcHeatMap.append("rect").attr("id", "main").attr("height", 820).attr("width", 620).attr("x", 0).attr("y", 0).style("fill", "white").style("stroke", "white");
   heatMapClip = chart_hcHeatMap.append("g").append("svg:clipPath")
   .attr("id", "clip_heatMap")
   .append("svg:rect")
   .attr("id", "clip-rect-hcHeatMap")
   .attr("x", "0")
   .attr("y", "0")
   .attr('width', 620)
   .attr('height', 740)
   ;

   colLabelBar = chart_hcHeatMap.append("g");

   rowLabelBar = chart_hcHeatMap.append("g").attr("clip-path", "url(#clip_heatMap)");

   legendBox = chart_hcHeatMap.append("rect").attr("id", "legendBox").attr("height", 15).attr("width", 400).attr("x", 145).attr("y", 780).style("fill-opacity", 0).attr("shape-rendering", "crispEdges");
   tickone = chart_hcHeatMap.append("path").attr("id", "lefttick").attr("d", "M 145 795 L 145 802").style("stroke", "white").attr("shape-rendering", "crispEdges");
   tickonelabel = chart_hcHeatMap.append("text").attr("id", "leftticklabel").attr("x", 145).attr("y", 815).attr("text-anchor", "middle");
   ticktwo = chart_hcHeatMap.append("path").attr("id", "midtick").attr("d", "M 345 795 L 345 802").style("stroke", "white").attr("shape-rendering", "crispEdges");
   ticktwolabel = chart_hcHeatMap.append("text").attr("id", "leftticklabel").attr("x", 345).attr("y", 815).attr("text-anchor", "middle");
   tickthree = chart_hcHeatMap.append("path").attr("id", "righttick").attr("d", "M 545 795 L 545 802").style("stroke", "white").attr("shape-rendering", "crispEdges");
   tickthreelabel = chart_hcHeatMap.append("text").attr("id", "leftticklabel").attr("x", 545).attr("y", 815).attr("text-anchor", "middle");

   chart_hcHeatMap.call(zoom_hcHeatMap);
   chart_hcHeatMap.call(tip_hcHeatMap);

   showLabels = scope.showLabels;

   //heatMapRect = chart_hcHeatMap.append("rect").attr("id", "heatmaprect").attr("height", 630).attr("width", 470).attr("x", 110).attr("y", 110).style("fill", "red");

   scope.$watch('data', function(newVals, oldVals) {


    if(scope.data!==null)
    {
      heatScale = 1;
      var oldImage = document.getElementById('heatMapImage');
      if (oldImage!==null && scope.data!==null)
      {
        $.when(document.getElementById('heatMapImage').remove()).then(scope.masterUpdate(scope.data));
      }
      else
      {
        scope.masterUpdate(scope.data);
      }
      scope.update(scope.data);
      legendBox.moveToFront();
      heatMapClip.moveToFront();
        //scope.cluster = "hey nick";
      }

    });

   scope.$watch('showLabels', function(newVals, oldVals)
   {
    $timeout(function()
    {
      showLabels = scope.showLabels;
      if (scope.data!==null)
      {
        heatScale = 1;
        var oldImage = document.getElementById('heatMapImage');
        if (oldImage!==null && scope.data!==null)
        {
          $.when(document.getElementById('heatMapImage').remove()).then(scope.masterUpdate(scope.data));
        }
        scope.update(scope.data);
        legendBox.moveToFront();
        heatMapClip.moveToFront();
        showLabels = scope.showLabels;
      }
    });
  });

   scope.$on('deleteHeatMap', function()
   {
     colLabelBar.selectAll(".colLabel").remove();
     colLabelBar.selectAll(".labelpath").remove();
     rowLabelBar.selectAll('.rowLabel').remove();
     chart_hcHeatMap.selectAll(".clusterRect").remove();
     var oldMap = document.getElementById('heatMapImage');
     if(oldMap!==null)
     {
       document.getElementById('heatMapImage').remove();
     }
     var oldImage = document.getElementById('colorBarImage');
     if (oldImage!==null)
     {
      document.getElementById('colorBarImage').remove();
      }
        tickone.style("stroke", "white");
      ticktwo.style("stroke", "white");
      tickthree.style("stroke", "white");
      legendBox.style("stroke", "white").style("stroke-opacity", 0);
      tickonelabel.text("");
      ticktwolabel.text("");
      tickthreelabel.text("");
      chart_hcHeatMap.selectAll(".errorBar").remove();
  })

   scope.updateMe = function()
   {
    //need to remove image here...
    if (scope.showLabels==="true")
    {
      colLabelBar.style("fill", "white");
      rowLabelBar.style("fill", "white");
    }
    else
    {
      colLabelBar.style("fill", "transparent");
      rowLabelBar.style("fill", "transparent");
    }
  }

  scope.masterUpdate = function(currMap)
  {
    if(currMap===null)
    {
      return;
    }
      //label out what range each component should fall in (col dend, row dend, heat map, cluster bar, etc...)
        //if show labels
          //col dendrogram x = (100 - 420) | y = (0 - 95)
          //row dendrogram x = (0 - 95) | y = (100 - 500)
          //heat map x = (100 - 420) | y = (100 - 500)
        //else
          //col dendrogram x = (100 - 570) | y = (0 - 95)
          //row dendrogram x = (0 - 95) | y = (100 - 650)
          //heat map x = (100 - 570) | y = (100 - 650)

          rowCount = 0;
          var colCount = 0;
          var maxRowDepth = 0;
          var maxColDepth = 0;
          var maxRowID = 0;
          var maxColID = 0;
          rowLineData = [];
          angular.forEach(rowClusters, function(d){
            d.MID==="" ? null : rowCount++;
            maxRowDepth = Math.max(maxRowDepth, d.d);
            maxRowID = Math.max(maxRowID, d.CID);
          });
          angular.forEach(columnClusters, function(d){
            d.MID==="" ? null : colCount++;
            maxColDepth = Math.max(maxColDepth, d.d);
            maxColID = Math.max(maxColID, d.CID);
          });

          var rowXCF = 0;
          var rowYCF = 0;
          var colXCF = 0;
          var colYCF = 0;

          if (scope.showLabels==="true")
          {
           colLabelBar.selectAll(".colLabel").remove();
           colLabelBar.selectAll(".labelpath").remove();
           rowLabelBar.selectAll('.rowLabel').remove();
           chart_hcHeatMap.selectAll(".clusterRect").remove();
           heatMapClip.attr("width", 620).attr("height", 610);
           var currGroup = chart_hcHeatMap.append("g").attr("clip-path", "url(#clip_heatMap)");

           plotColLabels(currMap.columnNameOrderArray);
            //heatMapRect.attr("height", 500).attr("width", 320);

            x_hcHeatMap = d3.scale.linear()
            .range([10, 610])
            .domain([0, 600]);

            y_hcHeatMap = d3.scale.linear()
            .range([750, 10])
            .domain([0, 740]);

            rowYCF = 500/rowCount;
            rowXCF = 95/maxRowDepth;
            colXCF = 320/colCount;
            colYCF = 95/maxColDepth;

            var rowClusterKeys = Object.keys(rowClusters);
            var maxRowID = rowClusterKeys[rowClusterKeys.length-1];
            var length = rowClusterKeys.length;
            for (var i = 0; i < length; i++)
            {
              var rowClustObj = rowClusters[rowClusterKeys[i]];
              if (rowClustObj.MID==="")
              {
                var subAIndex = rowClustObj.C[0];
                var subBIndex = rowClustObj.C[1];
                var subA = rowClusters[subAIndex];
                var subB = rowClusters[subBIndex];
                var midY = (subB.y + subA.y)/2;
                var currDepth = (maxRowDepth-rowClustObj.d) ;
                var subADepth = (maxRowDepth-subA.d);
                var subBDepth = (maxRowDepth-subB.d);
                rowClustObj.y = midY;
                rowLineData.push([{x:(subADepth*rowXCF),y:(subA.y * rowYCF)+140},
                  {x:(currDepth*rowXCF), y:(subA.y*rowYCF)+140},
                  {x:(currDepth * rowXCF), y:(subB.y*rowYCF)+140},
                  {x:(subBDepth*rowXCF),y:(subB.y*rowYCF)+140}]);
              }
            }

            var columnClusterKeys = Object.keys(columnClusters);
            var maxColID = columnClusterKeys[columnClusterKeys.length-1];
            length = columnClusterKeys.length;
            for (var i = 0; i < length; i++)
            {
              var colClustObj = columnClusters[columnClusterKeys[i]];
              if (colClustObj.MID==="")
              {
                var subAIndex = colClustObj.C[0];
                var subBIndex = colClustObj.C[1];
                var subA = columnClusters[subAIndex];
                var subB = columnClusters[subBIndex];
                var midX = (subA.x + subB.x)/2;
                colClustObj.x = midX;

                var currDepth =  (colClustObj.d);
                var subADepth =  (subA.d );
                var subBDepth =  (subB.d);
                colClustObj.x = midX;
                rowLineData.push([{x:((subA.x-.5) * colXCF) + 100 , y:(subADepth * colYCF)+650}, 
                  {x:((subA.x-.5) * colXCF) + 100, y:(currDepth * colYCF)+650}, 
                  {x:((subB.x-.5) * colXCF) + 100,y:(currDepth * colYCF)+650},
                  {x:((subB.x-.5) * colXCF) + 100,y:(subBDepth * colYCF)+650}]);
              }
            }

            for (var i = 0; i < rowCount; i++)
            {
              var currLabelText = currMap.rowNameOrderArray[i];
              rowLabelBar.append("text").attr("x", 435).attr("y", y_hcHeatMap(((rowCount - i) * rowYCF) + 140)).text(currLabelText).attr("font-size", 10)
              .style("fill", "white").attr("row", ((rowCount - i) * rowYCF) + 140).attr("class", "rowLabel row" + currLabelText);
            }

            var allColors = ["#1b9e77","#d95f02","#7570b3","#e7298a","#66a61e","#e6ab02","#a6761d","#666666"];
            var colorIndex = 0;
            var clusterCount = 1;
            currMap.clusterRanges.forEach(function(d)
            {
              var parts = d.split("_");
              var min = parts[0];
              var max = parts[1];
              var currColor = allColors[colorIndex];
              colorIndex++;
              colorIndex >= allColors.length ? colorIndex=0: null;
              currGroup.append("rect").attr("x", 585).attr("y", y_hcHeatMap(((max) * rowYCF)+140)).attr("width", 15).attr("height", (max-min + 1) * rowYCF).attr("fill", currColor)
              .attr("row", (max * rowYCF)+10).attr("class", "clusterRect").attr("min", min).attr("max", max).attr("clusterID", clusterCount + "of" + currMap.clusterRanges.length).on("click", function(d){
                var currClust = d3.select(this)[0][0].attributes;
                var currMin = currClust.min.value-1;
                var currMax = currClust.max.value-1;
                var currClusterKey = currClust.min.value + "_" + currClust.max.value;
                var tmpClusterData = {};
                for (i = currMin; i <= currMax; i++)
                {
                  //console.log(currMap.rowNameOrderArray[i]);
                  var currMol = currMap.rowNameOrderArray[rowCount-1-i];
                  tmpClusterData[currMol]=[];
                  for (j = 0; j < colCount; j++)
                  {
                    var currKey = j + "_" + (rowCount-1-i);
                    tmpClusterData[currMol].push(currMap.dataArray[currKey]);
                  }
                }
                scope.$apply(function(){
                  scope.cluster = tmpClusterData;
                  scope.clusterkey=currClusterKey;
                });

              });
              clusterCount++;

            });

colLabelBar.moveToFront();

var image = new Image();
image.onload = function()
{
 var canvas1 = document.createElement('canvas');
 canvas1.width = (rowCount * (32/50));
 canvas1.height = rowCount;
 var ctx = canvas1.getContext('2d');
 ctx.imageSmoothingEnabled = false;
 ctx.drawImage(image, 0, 0, (rowCount * (32/50)), rowCount);
 currGroup.append("svg:image").attr("xlink:href",canvas1.toDataURL('image/png')).attr("class", "heatmap")
 .attr("id", 'heatMapImage')
 .attr("width", 320)
 .attr("height", 500)
 .attr("x",110)
 .attr("y", 110)
 .attr("image-rendering", "pixelated")
 .on("mouseover", function(d){
  tip_hcHeatMap.showAbs();
})
 .on("mousemove", function (d){
   var tttext = findPoints(d3.mouse(this)[0], d3.mouse(this)[1], currMap, rowCount, colCount);
   tip_hcHeatMap.html(tttext);
   var offset = getOffset(d3.event.clientX,d3.event.clientY);
               // tip_hcHeatMap.offset([offset[0], offset[1]]);
               // tip_hcHeatMap.offset([50, 0]);
               tip_hcHeatMap.showAbs();
             })
 .on("mouseout", function(d){
  tip_hcHeatMap.hide();
  chart_hcHeatMap.selectAll('.labelpath').style("stroke", "darkgray").style("stroke-width", "1px");
})
 ;
};
image.src = currMap.b64;
}
else
{
  colLabelBar.selectAll(".colLabel").remove();
  colLabelBar.selectAll(".labelpath").remove();
  rowLabelBar.selectAll('.rowLabel').remove();
  chart_hcHeatMap.selectAll(".clusterRect").remove();
  heatMapClip.attr("width", 620).attr("height", 740);
  var currGroup = chart_hcHeatMap.append("g").attr("clip-path", "url(#clip_heatMap)");

            //heatMapRect.attr("height", 630).attr("width", 470);

            x_hcHeatMap = d3.scale.linear()
            .range([10, 610])
            .domain([0, 600]);

            y_hcHeatMap = d3.scale.linear()
            .range([750, 10])
            .domain([0, 740]);

            rowYCF = 630/rowCount;
            rowXCF = 95/maxRowDepth;
            colXCF = 470/colCount;
            colYCF = 95/maxColDepth;

            var rowClusterKeys = Object.keys(rowClusters);
            var maxRowID = rowClusterKeys[rowClusterKeys.length-1];
            var length = rowClusterKeys.length;
            for (var i = 0; i < length; i++)
            {
              var rowClustObj = rowClusters[rowClusterKeys[i]];
              if (rowClustObj.MID==="")
              {
                var subAIndex = rowClustObj.C[0];
                var subBIndex = rowClustObj.C[1];
                var subA = rowClusters[subAIndex];
                var subB = rowClusters[subBIndex];
                var midY = (subB.y + subA.y)/2;
                var currDepth = (maxRowDepth-rowClustObj.d) ;
                var subADepth = (maxRowDepth-subA.d);
                var subBDepth = (maxRowDepth-subB.d);
                rowClustObj.y = midY;
                rowLineData.push([{x:(subADepth*rowXCF),y:(subA.y * rowYCF)+10},
                  {x:(currDepth*rowXCF), y:(subA.y*rowYCF)+10},
                  {x:(currDepth * rowXCF), y:(subB.y*rowYCF)+10},
                  {x:(subBDepth*rowXCF),y:(subB.y*rowYCF)+10}]);
              }
            }

            var columnClusterKeys = Object.keys(columnClusters);
            var maxColID = columnClusterKeys[columnClusterKeys.length-1];
            length = columnClusterKeys.length;
            for (var i = 0; i < length; i++)
            {
              var colClustObj = columnClusters[columnClusterKeys[i]];
              if (colClustObj.MID==="")
              {
                var subAIndex = colClustObj.C[0];
                var subBIndex = colClustObj.C[1];
                var subA = columnClusters[subAIndex];
                var subB = columnClusters[subBIndex];
                var midX = (subA.x + subB.x)/2;
                colClustObj.x = midX;

                var currDepth =  (colClustObj.d);
                var subADepth =  (subA.d );
                var subBDepth =  (subB.d);
                colClustObj.x = midX;
                rowLineData.push([{x:((subA.x-.5) * colXCF) + 100 , y:(subADepth * colYCF)+650}, 
                  {x:((subA.x-.5) * colXCF) + 100, y:(currDepth * colYCF)+650}, 
                  {x:((subB.x-.5) * colXCF) + 100,y:(currDepth * colYCF)+650},
                  {x:((subB.x-.5) * colXCF) + 100,y:(subBDepth * colYCF)+650}]);
              }
            }

            //var allColors = ["#5fb233", "#6a7f93", "#f57206", "#eb0f13", "#8f2f8b", "#1396db"];
            var allColors = ["#1b9e77","#d95f02","#7570b3","#e7298a","#66a61e","#e6ab02","#a6761d","#666666"];
            var colorIndex = 0;
            var clusterCount = 1;
            currMap.clusterRanges.forEach(function(d)
            {
              var parts = d.split("_");
              var min = parts[0];
              var max = parts[1];
              var currColor = allColors[colorIndex];
              colorIndex++;
              colorIndex >= allColors.length ? colorIndex=0: null;
              currGroup.append("rect").attr("x", 585).attr("y", y_hcHeatMap(((max) * rowYCF)+10)).attr("width", 15).attr("height", (max-min + 1) * rowYCF).attr("fill", currColor)
              .attr("row", (max * rowYCF)+10).attr("class", "clusterRect").attr("min", min).attr("max", max).attr("clusterID", clusterCount + "of" + currMap.clusterRanges.length).on("click", function(d){
                var currClust = d3.select(this)[0][0].attributes;
                var currMin = currClust.min.value-1;
                var currMax = currClust.max.value-1;
                var currClusterKey = currClust.min.value + "_" + currClust.max.value;
                var tmpClusterData = {};
                for (i = currMin; i <= currMax; i++)
                {
                  var currMol = currMap.rowNameOrderArray[rowCount-1-i];
                  tmpClusterData[currMol]=[];
                  for (j = 0; j < colCount; j++)
                  {
                    var currKey = j + "_" + (rowCount-1-i);
                    tmpClusterData[currMol].push(currMap.dataArray[currKey]);
                  }
                }
                scope.$apply(function(){
                  scope.cluster = tmpClusterData;
                  scope.clusterkey = currClusterKey;
                });
              });
              clusterCount++;

            });

var image = new Image();
image.onload = function()
{
 var canvas1 = document.createElement('canvas');
 canvas1.width = (rowCount * (47/63));
 canvas1.height = rowCount;
 var ctx = canvas1.getContext('2d');
 ctx.imageSmoothingEnabled = false;
 ctx.drawImage(image, 0, 0, (rowCount * (47/63)), rowCount);
 currGroup.append("svg:image").attr("xlink:href",canvas1.toDataURL('image/png')).attr("class", "heatmap")
 .attr("id", 'heatMapImage')
 .attr("width", 470)
 .attr("height", 630)
 .attr("x",110)
 .attr("y", 110)
 .attr("image-rendering", "pixelated")
 .on("mouseover", function(d){
  tip_hcHeatMap.showAbs();
})
 .on("mousemove", function (d){
              //console.log(d3.event.clientX);
              var tttext = findPoints(d3.mouse(this)[0], d3.mouse(this)[1], currMap, rowCount, colCount);
              tip_hcHeatMap.html(tttext);
              var offset = getOffset(d3.event.clientX,d3.event.clientY);
               // tip_hcHeatMap.offset([offset[0], offset[1]]);
               // tip_hcHeatMap.offset([50, 0]);
               tip_hcHeatMap.showAbs();
             })
 .on("mouseout", function(d){
  tip_hcHeatMap.hide();
  chart_hcHeatMap.selectAll('.labelpath').style("stroke", "darkgray").style("stroke-width", "1px");
})
 ;
};
image.src = currMap.b64;

}
var oldImage = document.getElementById('colorBarImage');
if (oldImage!==null)
{
  document.getElementById('colorBarImage').remove();
}
var colorBar = new Image();
colorBar.onload = function()
{
  tickone.style("stroke", "black");
  ticktwo.style("stroke", "black");
  tickthree.style("stroke", "black");
  tickone.moveToFront();
  ticktwo.moveToFront();
  tickthree.moveToFront();
  tickonelabel.moveToFront();
  ticktwolabel.moveToFront();
  tickthreelabel.moveToFront();
  var midFC = (Number(scope.minFC) + Number(scope.maxFC))/2;
  tickonelabel.text(scope.minFC);
  ticktwolabel.text(midFC);
  tickthreelabel.text(scope.maxFC);
  var canvas2 = document.createElement('canvas');
  canvas2.width = 400;
  canvas2.height = 15;
  var ctx = canvas2.getContext('2d');
  ctx.imageSmoothingEnabled = false;
  ctx.drawImage(colorBar, 0, 0, 400, 15);
  chart_hcHeatMap.append("svg:image").attr("xlink:href",canvas2.toDataURL('image/png'))
  .attr("id", 'colorBarImage')
  .attr("width", 400)
  .attr("height", 15)
  .attr("x",145)
  .attr("y", 780)
  .attr("image-rendering", "pixelated")
  ;
  legendBox.style("stroke", "black").style("stroke-opacity", 1);
  legendBox.moveToFront();

};
colorBar.src = currMap.cb;

}

scope.update = function(data)
{
 if (data.length>0)
 {
  angular.forEach(data, function(d){
   d.p = -Math.log10(d.p_value);
   d.si = Math.max((d.p*3), 10);
   d.color = "gray";
   d.p > -Math.log10(0.05) && Math.abs(d.fc) > 1 ? d.color="green" : null;
   d.p > -Math.log10(0.05) && Math.abs(d.fc) < 1 ? d.color="dodgerblue" : null;
   d.p < -Math.log10(0.05) ? d.si=3: null;
 });
  var dataBinding = dataContainer.selectAll("custom.circle").data(data, function(d){return d.unique_identifier_id;});

  dataBinding
  .transition()
  .ease('linear')
  .duration(1500)
  .attr("cx", function(d){return x_hcHeatMap(d.fc);})
  .attr("cy", function(d){return y_hcHeatMap(d.p);})
  .attr("r", function(d){return d.si/2;})
  .attr("fillStyle",function(d){return d.color;});

  dataBinding.enter().append("custom").classed("circle", true)
  .attr("cx", function(d){return x_hcHeatMap(d.fc);})
  .attr("cy", function(d){return y_hcHeatMap(d.p);})
  .attr("r", function(d){return d.si/2;})
  .attr("fillStyle",function(d){return d.color;});

  dataBinding.exit()
  .attr("cx", function(d){return x_hcHeatMap(d.fc);})
  .attr("cy", function(d){return y_hcHeatMap(d.p);})
  .attr("r", function(d){return d.si/2;})
  .attr("fillStyle",function(d){return d.color;})
  .transition()
  .ease('linear')
  .duration(1500)
  .attr("cx", function(d){return x_hcHeatMap(0);})
  .attr("cy", function(d){return y_hcHeatMap(0);})
  .attr("r", function(d){return 0;})
  .attr("fillStyle",function(d){return "#fff";}).remove();

  t = d3.timer(function(elapsed) {
   scope.drawCanvas();
   return elapsed > 1500 ? true : false;
 });
}

else
{

  zoom_hcHeatMap.y(y_hcHeatMap);
  chart_hcHeatMap.selectAll(".errorBar").remove();
            //stroke-linecap="butt" fill="none" stroke-linejoin="miter"
            chart_hcHeatMap.selectAll(".errorBar")
            .data(rowLineData)
            .attr("class", "errorBar")
                    //.duration(1200)
                    .attr("d", line_dendrogram)
                    .attr("stroke-width", "1px")
                    .attr("stroke", "black")
                    .attr("stroke-linecap", "butt")
                    .attr("fill", "none")
                    .attr("stroke-linejoin", "miter")
                    ;
                    ;

                    chart_hcHeatMap.append("g").attr("clip-path", "url(#clip_heatMap)").selectAll(".errorBar")
                    .data(rowLineData)
                    .enter().append("path")
                    .attr("class", "errorBar")
                    .attr("d", line_dendrogram)
                    .attr("stroke-width", "1px")
                    .attr("stroke", "black")
                    .attr("stroke-linecap", "butt")
                    .attr("fill", "none")
                    .attr("stroke-linejoin", "miter")
                    //.duration(1200)
                    ;
                    ;

                    chart_hcHeatMap.selectAll(".errorBar")
                    .data(rowLineData)
                    .exit()

                    .remove();

                    //chart_hcHeatMap.call(zoomedhcHeatMap);
                  }

                }

                scope.drawCanvas = function()
                {
      //console.log("here");
      context.globalAlpha = 1.0
      context.fillStyle = "#fff";
      context.rect(0,0,width,height);
      context.fill();
      context.globalAlpha = 0.7;

      var elements = dataContainer.selectAll("custom.circle");
      elements.each(function(d){
       var node = d3.select(this);
       context.beginPath();
       context.fillStyle = node.attr("fillStyle");
       context.arc(node.attr("cx"), node.attr("cy"), node.attr("r"), 0, 2*Math.PI);
       context.fill();
       context.closePath();
     });
      //console.log(elements);
    }

  }
}
}]);

function zoomedhcHeatMap()
{
 var panX = d3.event.translate[0];
 var panY = d3.event.translate[1];
 var scale = d3.event.scale;
 heatScale = scale;

 panX = panX > 0 ? 0 : panX;
 var maxX = -(scale - 1) * width_hcHeatMap ;
 panX = panX < maxX ? maxX : panX;

 panY = panY > 0 ? 0 : panY;
 var maxY = -(scale - 1) * height_hcHeatMap;
 panY = panY < maxY ? maxY : panY;

 zoom_hcHeatMap.translate([panX, panY]);
// chart_hcHeatMap.select(".x.axis").call(xAxis_hcHeatMap);
 //chart_hcHeatMap.select(".y.axis").call(yAxis_hcHeatMap);
 chart_hcHeatMap.selectAll(".errorBar")
 .attr("d", line_dendrogram)
 ;

/* chart_hcHeatMap.selectAll("image")
.attr("transform", "translate(" + x_hcHeatMap(-10) + "," + y_hcHeatMap(750.1) + ") scale(1," + (d3.event.scale) + ")");*/
chart_hcHeatMap.selectAll(".heatmap")
.attr("transform", "translate(" + x_hcHeatMap(-10) + "," + y_hcHeatMap(750.1) + ") scale(1," + (d3.event.scale) + ")");


chart_hcHeatMap.selectAll(".clusterRect")
.attr("transform",  "translate(" + x_hcHeatMap(-10) + "," + y_hcHeatMap(750.1) + ") scale(1," + (d3.event.scale) + ")");


var pixelDensity = rowCount/heatScale;

rowLabelBar.selectAll('.rowLabel').attr("y", function(d){ return y_hcHeatMap(d3.select(this)[0][0].attributes.row.nodeValue); });
pixelDensity < 60 ? rowLabelBar.selectAll('.rowLabel').style("fill", "black") : rowLabelBar.selectAll('.rowLabel').style("fill", "white");
pixelDensity < 40 ? rowLabelBar.selectAll('.rowLabel').style("font-size", 14) : rowLabelBar.selectAll('.rowLabel').style("font-size", 10);
}

function moveRects()
{
  if (showLabels==="true")
  {
   //rowLabelBar.moveToFront();
   colLabelBar.moveToFront();

 }
 else
 {
  rowLabelBar.moveToBack();
  colLabelBar.moveToBack();
}
tickone.moveToFront();
ticktwo.moveToFront();
tickthree.moveToFront();
tickonelabel.moveToFront();
ticktwolabel.moveToFront();
tickthreelabel.moveToFront();
legendBox.moveToFront();

}

function findPoints(x, y, data, rowCount, colCount)
{
  if (showLabels==="true")
  {
    //x => 111-430
    //y => 110-610

    //calculate index for both x and y

    var xIndex = Math.floor(((x - 111)/319) * colCount);
    xIndex = Math.min(xIndex, colCount-1);
    var yIndex = Math.floor(((y-110)/500) * rowCount);
    yIndex = Math.min(yIndex, rowCount-1);

    var colName = data.columnNameOrderArray[xIndex].replace(/ /g, "_");
    var rowName = data.rowNameOrderArray[yIndex];

    chart_hcHeatMap.selectAll('.labelpath').style("stroke", "darkgray").style("stroke-width", "1px");
    chart_hcHeatMap.selectAll('.' + colName).style("stroke", "black").style("stroke-width", "2px");
/*    rowLabelBar.selectAll('.rowLabel').style("font-weight", "normal").style("font-style", "normal");
rowLabelBar.selectAll('.row' + rowName).style("font-weight", "bold").style("font-style", "italic");*/

var fc = data.dataArray[xIndex + "_" + yIndex];

var returnHTML = "<strong style='color:dodgerblue'>Molecule Identifier:</strong> <span style='color:white'>" + rowName + "</span> <br>";
returnHTML += "<strong style='color:dodgerblue'>Condition Name:</strong> <span style='color:white'>" + colName + "</span> <br>";
returnHTML += "<strong style='color:dodgerblue'>Molecule Fold Change:</strong> <span style='color:white'>" + fc + "</span>";
return returnHTML;
}
else
{
     //x => 111-580
     //y => 110-740
     var xIndex = Math.floor(((x - 111)/469) * colCount);
     xIndex = Math.min(xIndex, colCount-1);
     var yIndex = Math.floor(((y-110)/630) * rowCount);
     yIndex = Math.min(yIndex, rowCount-1);

     var colName = data.columnNameOrderArray[xIndex];
     var rowName = data.rowNameOrderArray[yIndex];
     var fc = data.dataArray[xIndex + "_" + yIndex];

     var returnHTML = "<strong style='color:dodgerblue'>Molecule Identifier:</strong> <span style='color:white'>" + rowName + "</span> <br>";
     returnHTML += "<strong style='color:dodgerblue'>Condition Name:</strong> <span style='color:white'>" + colName + "</span> <br>";
     returnHTML += "<strong style='color:dodgerblue'>Molecule Fold Change:</strong> <span style='color:white'>" + fc + "</span>";
     return returnHTML;
   }
 }

 function getOffset(x, y)
 {
  //x should stay fixed
  //y should scale a little bit

  tip_hcHeatMap.nwkpos(y+window.pageYOffset, x);

}

function plotColLabels(labels)
{

  colLabelBar.selectAll(".colLabel").remove();
  colLabelBar.selectAll(".colLabelBackground").remove();
  var keys = Object.keys(labels);
  var diff = 600/(keys.length-1);
  var fontSize = 13;
  keys.length >50 ? fontSize = 10 : null;
  keys.length > 80 ? fontSize = 9 : null; 
  var maxBar = null;
  var maxHeight = 0;
  var colWidth = 320/(keys.length);
  var leftLabel = 0;
  var rightLabel = 0;

  keys.forEach(function(d){
    var x = 10 + (d * diff);
    var shortLabelText = labels[d].length > 10 ? labels[d].substring(0, 10) + "..." : labels[d];
    var currText = colLabelBar.append("text").attr("transform", "translate(" + x + ",740) rotate(-90)").attr("class", "colLabel").attr("font-size", fontSize).text(shortLabelText).attr("id", "colLabel" + d);
    var currBBox = currText.node().getBBox();
    var currRect = colLabelBar.append("rect").attr("x", x-fontSize ).attr("y", 740-currBBox.width).attr("height", currBBox.width).attr("width", currBBox.height).attr("fill", "white")
    .attr("class", "colLabelBackground").attr("id", "colLabelBackground" + d);
    maxHeight = Math.max(maxHeight, currBBox.width);

    var colXPos = 110 + (d * colWidth) + (colWidth/2);
    colXPos > x ? leftLabel++ : rightLabel++;

  });

  var verticalDrop = 128 - (maxHeight) - 5;
  var leftDrop = verticalDrop/leftLabel;
  var rightDrop = verticalDrop/rightLabel;
  var leftPass = 0;
  var rightPass = 0;

  keys.forEach(function(d){
    var x = 10 + (d * diff);
    var colXPos = 110 + (d * colWidth) + (colWidth/2);

    if (colXPos>x)
    {
      var currRect = document.getElementById("colLabelBackground" + d);
      var boxX = currRect.x.animVal.value;
      var boxY = currRect.y.animVal.value-1;
      var y1 = 612;
      var y2 = 617 + (leftPass * leftDrop); leftPass++;
      var y3 = boxY;
      var x1 = colXPos;
      var x2 = boxX + (fontSize/2);

      colLabelBar.append("path").attr("d", "M " + x2 + " " + y3 + " L " + x2 + " " + y2 + " L " + x1 + " " + y2 + " L " + x1 + " " + y1)
      .style("stroke", "darkgray").attr("shape-rendering", "crispEdges").attr("class", "labelpath " + labels[d].replace(/ /g, "_")).attr("fill-opacity", 0);

    }
    else
    {
      var currRect = document.getElementById("colLabelBackground" + d);
      var boxX = currRect.x.animVal.value;
      var boxY = currRect.y.animVal.value-1;
      var y1 = 612;
      var y2 = 612 + verticalDrop - (rightPass * rightDrop); rightPass++;
      var y3 = boxY;
      var x1 = colXPos;
      var x2 = boxX + (fontSize/2);
      colLabelBar.append("path").attr("d", "M " + x2 + " " + y3 + " L " + x2 + " " + y2 + " L " + x1 + " " + y2 + " L " + x1 + " " + y1)
      .style("stroke", "darkgray").attr("shape-rendering", "crispEdges").attr("class", "labelpath " + labels[d].replace(/ /g, "_")).attr("fill-opacity", 0);
    }

  });

colLabelBar.selectAll(".colLabelBackground").remove();
}

d3.selection.prototype.moveToFront = function() {  
  return this.each(function(){
    this.parentNode.appendChild(this);
  });
};
d3.selection.prototype.moveToBack = function() {  
  return this.each(function() { 
    var firstChild = this.parentNode.firstChild; 
    if (firstChild) { 
      this.parentNode.insertBefore(this, firstChild); 
    } 
  });
};