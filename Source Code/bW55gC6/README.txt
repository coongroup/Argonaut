# In config.php, need to change project path to the absolute path of the filesystem. 

# Track file locations where I changed cURL locations from data dev to local host
### curl_setopt($ch, CURLOPT_URL, "https://coonlabdatadev.com/DV/" . $projectID . "/nameOfScript.php");
### curl_setopt($ch, CURLOPT_URL, "127.0.0.1/projects/" . $projectID . "/nameOfScript.php");

##### Files which contain cURL commands...

addGoProcess.php
saveData.php
updateSampleType.php
updateLog2Transform.php
updateDataFilter.php
updateImputation.php
updateControl.php
queryHeatMap.php
deleteData.php
startGOClusterAnalysis.php