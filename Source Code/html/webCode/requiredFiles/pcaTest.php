<?php

function DoPCA($condition_dict, $master_id_list, $do_replicate = false)
{
	$currI = 0;
	$featureArray = array();
	foreach ($master_id_list as $key => $value) {
		$tmpArray = array();
		$has_zero = false;
		foreach ($condition_dict as $cond) {
			if($has_zero)
			{
				break;
			}
			if($do_replicate)
			{
				$valid = true;
				foreach ($cond->replicate_list as $rep) {
					if (array_key_exists($value, $rep->quant_dict))
					{
						array_push($tmpArray,$rep->quant_dict[$value]);
					}
					else
					{
						array_push($tmpArray,0);
						$valid = false;
						$has_zero = true;
					}
				}
			}
			else
			{
				if(array_key_exists($value, $cond->quant_dict_avg_val))
				{
					array_push($tmpArray, $cond->quant_dict_avg_val[$value]);
				}
				else
				{
					array_push($tmpArray,0);
					$has_zero = true;
				}
			}
		}
		if(!$has_zero)
		{
			for ($i = 0; $i < count($tmpArray); $i++)
			{
				$featureArray[$currI][$i] = $tmpArray[$i];
			}
			$currI++;
		}
	}
	if (count($featureArray)===0)
	{
		return null;
	}
	//TransposeArray($featureArray);
	$count = 0;
	$sd_array = array();
    //echo(json_encode($featureArray));
	foreach($featureArray as $row)
	{
		$avg = array_sum($row)/count($row);
		//$sd_array[$count] = stats_standard_deviation($row, true);
		$sd_array[$count] = standard_deviation($row);
		for ($i = 0; $i < count($row); $i++)
		{
			$featureArray[$count][$i]-=$avg;
		}
		$count++;
	}
	$matrix = new Matrix;
	$svd = $matrix->SVD($featureArray);
	//echo(json_encode($svd['W']) . "<br>");

	$singularValues = $svd['W'];
	$eigenvalues = array();
	$summedEigenValues = 0;
	for ($i = 0; $i < count($singularValues); $i++)
	{
		$newVal = pow($singularValues[$i], 2);
		$eigenvalues[$i] = $newVal/(count($singularValues)-1);
		$summedEigenValues+= $eigenvalues[$i];
	}

	$s = array();
	foreach ($svd['S'] as $key => $value) {
		$tmpArray = array();
		for($i = 0; $i < count($svd['S']); $i++)
		{
			array_push($tmpArray, 0);
		}
		array_push($s, $tmpArray);
	}

	$count = 0;
	foreach ($svd['S'] as $row) {
		$tmpArray = array();
		foreach ($row as $key => $value) {
			$s[$count][$key]=$value;
		}
		$count++;
	}

	$result = $matrix->matrixMultiplication($s, $svd['V']);


	$formatted_array = array();
	$count = 0;
	$repCount = 0;
	if ($do_replicate)
	{
        $repCount = 0;
		foreach ($condition_dict as $cond) {
			foreach ($cond->replicate_list as $rep) {
				for ($i =0; $i<10; $i++)
				{
					if ($i>=count($eigenvalues))
					{
						break;
					}
					$curr_eigenvalue = $eigenvalues[$i];
					$var_frac = $curr_eigenvalue/$summedEigenValues;
					$component_number = $i+1;
					$scaled_vector = $result[$i][$repCount];
					$curr_pc_obj = array('component_number' => $component_number, 'scaled_vector' => $scaled_vector, 'variance_fraction' => $var_frac, 'replicate_id' => $rep->replicate_id, 'condition_id' => $cond->condition_id);
					array_push($formatted_array, $curr_pc_obj);
				}
				$repCount++;
			}
		}
	}
	else
	{
		$condCount = 0;
		foreach ($condition_dict as $cond) {
			for ($i =0; $i<10; $i++)
			{
				if ($i>=count($eigenvalues))
				{
					break;
				}
				$curr_eigenvalue = $eigenvalues[$i];
				$var_frac = $curr_eigenvalue/$summedEigenValues;
				$component_number = $i+1;
				$scaled_vector = $result[$i][$condCount];
				$curr_pc_obj = array('component_number' => $component_number, 'scaled_vector' => $scaled_vector, 'variance_fraction' => $var_frac, 'condition_id' => $cond->condition_id);
				array_push($formatted_array, $curr_pc_obj);
			}
			$condCount++;
		}
	}
	return $formatted_array;

}

function TransposeArray(&$inputArray)
{
	$tmpArray = array();
	if (count($inputArray[0])==1)
	{
		for ($i=0; $i < count($inputArray); $i++)
		{
			$tmpArray[$i][0] = $inputArray[$i];
		}
	}
	else
	{
		for ($i = 0; $i < count($inputArray); $i++)
		{
			for ($j = 0; $j < count($inputArray[0]); $j++)
			{
				$tmpArray[$j][$i] = $inputArray[$i][$j];
			}
		}
	}
	$inputArray = $tmpArray;
}

//https://github.com/d3veloper/SVD
class Matrix {

    /**
     * matrixMultiplication
     * 
     * @param array $matrixA
     * @param array $matrixB
     * @return array
     */
    public function matrixMultiplication($matrixA, $matrixB){

    	$rowsA = count($matrixA);
    	$colsA = count($matrixA[0]);

    	$rowsB = count($matrixB);
    	$colsB = count($matrixB[0]);
    	$matrixProduct = array();
    	for ($i = 0; $i < $rowsA; $i++)
    	{
    		$tmpArray = array();
    		for ($j=0; $j < $colsB; $j++)
    		{
    			array_push($tmpArray, 0);
    		}
    		array_push($matrixProduct, $tmpArray);
    	}


    	if($colsA == $rowsB){
    		for($i = 0; $i < $rowsA; $i++){
    			for($j = 0; $j < $colsB; $j++){
    				for($p = 0; $p < $colsA; $p++){
    					$matrixProduct[$i][$j] += $matrixA[$i][$p] * $matrixB[$p][$j];
    				}
    			}
    		}
    	}else {
    		echo "Matrix Multiplication can not be done !";
    	}
    	return $matrixProduct;
    }
    
    /**
     * matrixTranspose
     * 
     * @param array $matrix
     * @return array
     */
    public function matrixTranspose($matrix){

    	$m = count($matrix);
    	$n = count($matrix[0]);

    	for($i = 0; $i < $n; $i++){
    		for($j = 0; $j < $m; $j++){
    			$matrixT[$i][$j] = $matrix[$j][$i];
    		}
    	}
    	return $matrixT;
    }
    
    /**
     * matrixRound
     * 
     * @param array $matrix
     * @return array
     */
    public function matrixRound($matrix){

    	$m = count($matrix);
    	$n = count($matrix[0]);

    	for($i = 0; $i < $m; $i++){
    		for($j = 0; $j < $n; $j++){
    			$matrixT[$i][$j] = round($matrix[$i][$j], 2);
    		}
    	}
    	return $matrixT;
    }
    
    /**
     * matrixConstruct
     * 
     * @param array $matrix
     * @param integer $rows
     * @param integer $columns
     * @return array
     */
        public function matrixConstruct($matrix, $rows, $columns){

        $m = count($matrix);
        $n = count($matrix[0]);
        /*for($i = 0; $i < $rows; $i++){
            for($j = 0; $j < $columns; $j++){
                $neoMatrix[$i][$j] = $matrix[$i][$j];
            }
        }*/
        for($i = 0; $i < $m; $i++){
            for($j = 0; $j < $n; $j++){
                $neoMatrix[$i][$j] = $matrix[$i][$j];
            }
        }
        return $neoMatrix;
    }
    
    /**
     * sameSign
     * 
     * @param integer $a
     * @param integer $b
     * @return integer
     */
    private function sameSign($a, $b){

    	if($b >= 0){
    		$result = abs($a);
    	}else {
    		$result = - abs($a);
    	}
    	return $result;
    }
    
    /**
     * maximum
     * 
     * @param integer $a
     * @param integer $b
     * @return integer
     */
    private function maximum($a, $b){

    	if($a < $b){
    		return $b;
    	}else {
    		return $a;
    	}
    }
    
    /**
     * minimum
     * 
     * @param integer $a
     * @param integer $b
     * @return integer
     */
    private function minimum($a, $b){

    	if($a > $b){
    		return $b;
    	}else {
    		return $a;
    	}
    }
    
    /**
     * pythag
     * 
     * @param integer $a
     * @param integer $b
     * @return integer
     */
    private function pythag($a, $b){

    	$absa = abs($a);
    	$absb = abs($b);

    	if( $absa > $absb ){
    		return $absa * sqrt( 1.0 + pow( $absb / $absa , 2) );
    	}else {
    		if( $absb > 0.0 ){
    			return $absb * sqrt( 1.0 + pow( $absa / $absb, 2 ) );
    		}else {
    			return 0.0;
    		}
    	}
    }
    
    /**
     * SVD
     * 
     * @param array $matrix
     * @return array
     */
    public function SVD($matrix){

    	$m = count($matrix);
    	$n = count($matrix[0]);

    	$U  = $this->matrixConstruct($matrix, $m, $n);
    	$V  = $this->matrixConstruct($matrix, $n, $n);

    	$eps = 2.22045e-016;

        // Decompose Phase

        // Householder reduction to bidiagonal form.
    	$g = $scale = $anorm = 0.0;
    	for($i = 0; $i < $n; $i++){
    		$l = $i + 2;
    		$rv1[$i] = $scale * $g;
    		$g = $s = $scale = 0.0;
    		if($i < $m){
    			for($k = $i; $k < $m; $k++) $scale += abs($U[$k][$i]);
    				if($scale != 0.0) {
    					for($k = $i; $k < $m; $k++) {
    						$U[$k][$i] /= $scale;
    						$s += $U[$k][$i] * $U[$k][$i];
    					}
    					$f = $U[$i][$i];
    					$g = - $this->sameSign(sqrt($s), $f);
    					$h = $f * $g - $s;
    					$U[$i][$i] = $f - $g;
    					for($j = $l - 1; $j < $n; $j++){
    						for($s = 0.0, $k = $i; $k < $m; $k++) $s += $U[$k][$i] * $U[$k][$j];
    							$f = $s / $h;
    						for($k = $i; $k < $m; $k++) $U[$k][$j] += $f * $U[$k][$i];
    					}
    				for($k = $i; $k < $m; $k++) $U[$k][$i] *= $scale;
    			}
    	}
    	$W[$i] = $scale * $g;
    	$g = $s = $scale = 0.0;
    	if($i + 1 <= $m && $i + 1 != $n){
    		for ($k= $l - 1; $k < $n; $k++) $scale += abs($U[$i][$k]);
    			if($scale != 0.0){
    				for ($k= $l - 1; $k < $n; $k++){
    					$U[$i][$k] /= $scale;
    					$s += $U[$i][$k] * $U[$i][$k];
    				}
    				$f = $U[$i][$l - 1];
    				$g = - $this->sameSign(sqrt($s), $f);
    				$h = $f * $g - $s;
    				$U[$i][$l - 1] = $f - $g;
    				for($k = $l - 1; $k < $n; $k++) $rv1[$k] = $U[$i][$k] / $h;
    					for($j = $l - 1; $j < $m; $j++){
    						for($s = 0.0, $k = $l - 1; $k < $n; $k++) $s += $U[$j][$k] * $U[$i][$k];
    							for($k = $l - 1; $k < $n; $k++) $U[$j][$k] += $s * $rv1[$k];
    						}
    					for($k= $l - 1; $k < $n; $k++) $U[$i][$k] *= $scale;
    				}
    		}
    		$anorm = $this->maximum($anorm, (abs($W[$i]) + abs($rv1[$i])));
    	}

        // Accumulation of right-hand transformations.
    	for($i = $n - 1; $i >= 0; $i--){
    		if($i < $n - 1){
    			if($g != 0.0){
                    for($j = $l; $j < $n; $j++) // Double division to avoid possible underflow.
                    $V[$j][$i] = ($U[$i][$j] / $U[$i][$l]) / $g;
                    for($j = $l; $j < $n; $j++){
                    	for($s = 0.0, $k = $l; $k < $n; $k++) $s += ($U[$i][$k] * $V[$k][$j]);
                    		for($k = $l; $k < $n; $k++) $V[$k][$j] += $s * $V[$k][$i];
                    	}
                }
                for($j = $l; $j < $n; $j++) $V[$i][$j] = $V[$j][$i] = 0.0;
            }
        $V[$i][$i] = 1.0;
        $g = $rv1[$i];
        $l = $i;
    }

        // Accumulation of left-hand transformations.
    for($i = $this->minimum($m, $n) - 1; $i >= 0; $i--){
    	$l = $i + 1;
    	$g = $W[$i];
    	for($j = $l; $j < $n; $j++) $U[$i][$j] = 0.0;
    		if($g != 0.0){
    			$g = 1.0 / $g;
    			for($j = $l; $j < $n; $j++){
    				for($s = 0.0, $k = $l; $k < $m; $k++) $s += $U[$k][$i] * $U[$k][$j];
    					$f = ($s / $U[$i][$i]) * $g;
    				for($k = $i; $k < $m; $k++) $U[$k][$j] += $f * $U[$k][$i];
    			}
    		for($j = $i; $j < $m; $j++) $U[$j][$i] *= $g;
    	}else {
    		for($j = $i; $j < $m; $j++) $U[$j][$i] = 0.0;
    	}
    ++$U[$i][$i];
}

        // Diagonalization of the bidiagonal form
        // Loop over singular values, and over allowed iterations.
for($k = $n - 1; $k >= 0; $k--){
	for($its = 0; $its < 30; $its++){
		$flag = true;
		for($l = $k; $l >= 0; $l--){
			$nm = $l - 1;
			if( $l == 0 || abs($rv1[$l]) <= $eps*$anorm){
				$flag = false;
				break;
			}
			if(abs($W[$nm]) <= $eps*$anorm) break;
		}
		if($flag){
                    $c = 0.0;  // Cancellation of rv1[l], if l > 0.
                    $s = 1.0;
                    for($i = $l; $i < $k + 1; $i++){
                    	$f = $s * $rv1[$i];
                    	$rv1[$i] = $c * $rv1[$i];
                    	if(abs($f) <= $eps*$anorm) break;
                    	$g = $W[$i];
                    	$h = $this->pythag($f,$g);
                    	$W[$i] = $h;
                    	$h = 1.0 / $h;
                    	$c = $g * $h;
                    	$s = -$f * $h;
                    	for($j = 0; $j < $m; $j++){
                    		$y = $U[$j][$nm];
                    		$z = $U[$j][$i];
                    		$U[$j][$nm] = $y * $c + $z * $s;
                    		$U[$j][$i] = $z * $c - $y * $s;
                    	}
                    }
                }
                $z = $W[$k];
                if($l == $k){
                	if($z < 0.0){
                        $W[$k] = -$z; // Singular value is made nonnegative.
                        for($j = 0; $j < $n; $j++) $V[$j][$k] = -$V[$j][$k];
                    }
                break;
            }
            if($its == 29) print("no convergence in 30 svd iterations");
                $x = $W[$l]; // Shift from bottom 2-by-2 minor.
                $nm = $k - 1;
                $y = $W[$nm];
                $g = $rv1[$nm];
                $h = $rv1[$k];
                $f = (($y - $z) * ($y + $z) + ($g - $h) * ($g + $h)) / (2.0 * $h * $y);
                $g = $this->pythag($f,1.0);
                $f = (($x - $z) * ($x + $z) + $h * (($y / ($f + $this->sameSign($g,$f))) - $h)) / $x;
                $c = $s = 1.0;
                for($j = $l; $j <= $nm; $j++){
                	$i = $j + 1;
                	$g = $rv1[$i];
                	$y = $W[$i];
                	$h = $s * $g;
                	$g = $c * $g;
                	$z = $this->pythag($f,$h);
                	$rv1[$j] = $z;
                	$c = $f / $z;
                	$s = $h / $z;
                	$f = $x * $c + $g * $s;
                	$g = $g * $c - $x * $s;
                	$h = $y * $s;
                	$y *= $c;
                	for($jj = 0; $jj < $n; $jj++){
                		$x = $V[$jj][$j];
                		$z = $V[$jj][$i];
                		$V[$jj][$j] = $x * $c + $z * $s;
                		$V[$jj][$i] = $z * $c - $x * $s;
                	}
                	$z = $this->pythag($f,$h);
                    $W[$j] = $z;  // Rotation can be arbitrary if z = 0.
                    if($z){
                    	$z = 1.0 / $z;
                    	$c = $f * $z;
                    	$s = $h * $z;
                    }
                    $f = $c * $g + $s * $y;
                    $x = $c * $y - $s * $g;
                    for($jj = 0; $jj < $m; $jj++){
                    	$y = $U[$jj][$j];
                    	$z = $U[$jj][$i];
                    	$U[$jj][$j] = $y * $c + $z * $s;
                    	$U[$jj][$i] = $z * $c - $y * $s;
                    }
                }
                $rv1[$l] = 0.0;
                $rv1[$k] = $f;
                $W[$k] = $x;
            }
        }
        
        // Reorder Phase
        // Sort. The method is Shell's sort.
        // (The work is negligible as compared to that already done in decompose phase.)
        $inc = 1;
        do {
        	$inc *= 3;
        	$inc++;
        }   while($inc <= $n);
        
        do {
        	$inc /= 3;
        	for($i = $inc; $i < $n; $i++){
        		$sw = $W[$i];
        		for($k = 0; $k < $m; $k++) $su[$k] = $U[$k][$i];
        			for($k = 0; $k < $n; $k++) $sv[$k] = $V[$k][$i];
        				$j = $i;
        			while($W[$j - $inc] < $sw){
        				$W[$j] = $W[$j - $inc];
        				for($k = 0; $k < $m; $k++) $U[$k][$j] = $U[$k][$j - $inc];
        					for($k = 0; $k < $n; $k++) $V[$k][$j] = $V[$k][$j - $inc];
        						$j -= $inc;
        					if($j < $inc) break;
        				}
        				$W[$j] = $sw;
        				for($k = 0; $k < $m; $k++) $U[$k][$j] = $su[$k];
        					for($k = 0; $k < $n; $k++) $V[$k][$j] = $sv[$k];
        				}
        		}  while($inc > 1);

        		for($k = 0; $k < $n; $k++){
        			$s = 0;
        			for($i = 0; $i < $m; $i++) if ($U[$i][$k] < 0.0) $s++;
        				for($j = 0; $j < $n; $j++) if ($V[$j][$k] < 0.0) $s++;
        					if($s > ($m + $n)/2) {
        						for($i = 0; $i < $m; $i++) $U[$i][$k] = - $U[$i][$k];
        							for($j = 0; $j < $n; $j++) $V[$j][$k] = - $V[$j][$k];
        						}
        				}

        // calculate the rank
        				$rank = 0;
        				$frobA = 0;
        				$frobAk = 0;
        				for($i = 0; $i < count($W); $i++){
        					if(round($W[$i], 4) > 0){
        						$rank += 1;
        					}
        				}

        // Low-Rank Approximation
        				$q = 0.9;
        				$k = 0;
        				for($i = 0; $i < $rank; $i++) $frobA += $W[$i];
        					do{
        						for($i = 0; $i <= $k; $i++) $frobAk += $W[$i];
        							$clt = $frobAk / $frobA;
        						$k++;
        					}   while($clt < $q);

        // prepare S matrix as n*n daigonal matrix of singular values
        					for($i = 0; $i < $n; $i++){
        						for($j = 0; $j < $n; $j++){
        							$S[$i][$j] = 0;
        							$S[$i][$i] = $W[$i];
        						}
        					}

        					$matrices['U'] = $U;
        					$matrices['S'] = $S;
        					$matrices['W'] = $W;
        					$matrices['V'] = $this->matrixTranspose($V);
        					$matrices['Rank'] = $rank;
        					$matrices['K'] = $k;

        					return $matrices;
        				}
        			}
// input matrix
