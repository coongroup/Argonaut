

//var colorArray =["#DA70D6","#008080","#9400D3","#9370DB","#3CB371","#00BFFF","#6495ED","#FF69B4","#EE82EE","#228B22","#FF0000","#FA8072","#FF1493","#006400","#483D8B","#B8860B","#800000","#FF8C00","#8B4513","#A0522D","#DAA520","#2E8B57","#5F9EA0","#CD853F","#6B8E23","#6A5ACD","#48D1CC","#BA55D3","#008000","#800080","#FF4500","#E9967A","#FF6347","#4169E1","#556B2F","#D2691E","#1E90FF","#CD5C5C","#00CED1","#8A2BE2","#20B2AA","#32CD32","#A52A2A","#C71585","#8B008B","#66CDAA","#008B8B","#BC8F8F","#4682B4","#0000FF","#F08080","#7B68EE","#DB7093","#808000","#FFA500","#8FBC8F","#DC143C","#B22222","#9932CC","#FF7F50","#8B0000"];
var colorIndex = 0;
var currentLookupType = "";
 jQuery(document).ready(function () {
        $(document).on('shown.bs.tab', 'a[data-toggle="tab"]', function (e) {
            var tabTitle = e.target.innerHTML;
            switch (tabTitle) {
                case "Data Lookup":
                $('#LookupTableOne').bootstrapTable('resetView');
                    break;
                default:
                    break;
            }
        });
    });

 function RefreshBootstrapTable()
 {
    $('#LookupTableOne').bootstrapTable('resetView');
 }



 var 
 colorArray = ["#9370DB","#8A2BE2","#FF1493","#A0522D","#A52A2A","#7B68EE","#9400D3","#2E8B57","#DC143C","#CD853F","#008000","#6A5ACD","#6495ED","#3CB371","#C71585","#B8860B","#8B4513","#BC8F8F","#4682B4","#B22222","#9932CC","#006400","#228B22","#FF4500","#8B008B","#FF0000","#556B2F","#FF69B4","#808000","#FA8072","#FF6347","#20B2AA","#FF7F50","#5F9EA0","#DB7093","#008B8B","#CD5C5C","#008080","#1E90FF","#DA70D6","#F08080","#D2691E","#4169E1","#6B8E23","#BA55D3"];