<?php
$a = '/a/b/c/d/e.php';
$b = '/a/b/12/34/c.php';
function getRelativePath($a, $b) {  
    $returnPath = array(dirname($b));  
    $arrA = explode('/', $a);  
    $arrB = explode('/', $returnPath[0]);  
    for ($n = 1, $len = count($arrB); $n < $len; $n++) {  
        if ($arrA[$n] != $arrB[$n]) {  
            break;  
        }   
    }  
    if ($len - $n > 0) {  
        $returnPath = array_merge($returnPath, array_fill(1, $len - $n, '..'));  
    }  
      
    $returnPath = array_merge($returnPath, array_slice($arrA, $n));  
    return implode('/', $returnPath);  
   }  
   echo getRelativePath($a, $b);  