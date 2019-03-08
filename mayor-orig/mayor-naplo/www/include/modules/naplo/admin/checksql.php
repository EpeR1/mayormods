<?php                                                                                                                                                                                                      


                                                                                                                                                                                                           
        function checkSqlConsistency($queryFile,$db,&$Q_ERR)                                                                                                                                               
        {                                                                                                                                                                                                  
                                                                                                                                                                                                           
            $convert = array("%DB%" => intezmenyDbNev(__INTEZMENY));                                                                                                                                       
                                                                                                                                                                                                           
            $fp = fopen($queryFile, 'r');                                                                                                                                                                  
            $query = fread($fp, filesize($queryFile));                                                                                                                                                     
            fclose($fp);                                                                                                                                                                                   
                                                                                                                                                                                                           
            // A tárolt eljárásoknak, függvényeknek "DELIMITER //" és "DELIMITER ; //" között kell lenniük - egy blokkban a file végén!                                                                    
            list($query, $delimiter) = explode('DELIMITER //', $query);                                                                                                                                    
                                                                                                                                                                                                           
            // Tábladefiníciók - normál query-k                                                                                                                                                            
            $QUERIES = explode(';', str_replace("\n", '', $query));                                                                                                                                        
            for ($i = 0; $i < count($QUERIES); $i++) {                                                                                                                                                     
                $q = $QUERIES[$i];                                                                                                                                                                         
                if (trim($q) != '' and substr($q, 0, 2) != '--' and substr($q, 0, 3) != '/*!') {                                                                                                           
                    if (is_array($convert)) foreach ( $convert as $mit=>$mire ) $q = str_replace($mit,$mire,$q);                                                                                           
                }                                                                                                                                                                                          
                if (substr($q,0,6) == 'CREATE') {                                                                                                                                                          
                    $_q = str_replace(' ','',$q);                                                                                                                                                          
                    $first = $second = 0;                                                                                                                                                                  
                    for ($c = 0; $c<strlen($_q); $c++) {                                                                                                                                                   
                        if ($_q[$c]==='`') {                                                                                                                                                               
                            if ($first==0) $first = $c;                                                                                                                                                    
                            else {                                                                                                                                                                         
                                $second = $c; break;                                                                                                                                                       
                            }                                                                                                                                                                              
                        }                                                                                                                                                                                  
                    }                                                                                                                                                                                      
                    $table = substr($_q,$first+1,$second-$first-1);                                                                                                                                        
                    $r = db_query("SHOW CREATE TABLE $table",array('modul'=>$db,'result'=>'record'));                                                                                                      
                    $q1 = str_replace("\n",'',str_replace(' ','',$r['Create Table']));                                                                                                                     
                    $q2 = str_replace(' ','',$_q);                                                                                                                                                         
                    $q1_tmp = substr($q1,0,strrpos($q1,")"));                                                                                                                                              
                    $q2_tmp = substr($q2,0,strrpos($q2,")"));                                                                                                                                              
                    if ($q1_tmp!=$q2_tmp) {                                                                                                                                                                
                        $Q_ERR[]=array('inDb'=>$r['Create Table'],'inFile'=>$q);                                                                                                                           
                    }                                                                                                                                                                                      
                }                                                                                                                                                                                          
            }                                                                                                                                                                                              
        }           


                                                                                                                                                                                                           
/*                                                                                                                                                                                                         
Paul's Simple Diff Algorithm v 0.1                                                                                                                                                                         
(C) Paul Butler 2007 <http://www.paulbutler.org/>                                                                                                                                                          
May be used and distributed under the zlib/libpng license.                                                                                                                                                 
This code is intended for learning purposes; it was written with short                                                                                                                                     
code taking priority over performance. It could be used in a practical                                                                                                                                     
application, but there are a few ways it could be optimized.                                                                                                                                               
Given two arrays, the function diff will return an array of the changes.                                                                                                                                   
I won't describe the format of the array, but it will be obvious                                                                                                                                           
if you use print_r() on the result of a diff on some test data.                                                                                                                                            
htmlDiff is a wrapper for the diff command, it takes two strings and                                                                                                                                       
returns the differences in HTML. The tags used are <ins> and <del>,                                                                                                                                        
which can easily be styled with CSS.                                                                                                                                                                       
*/                                                                                                                                                                                                         
                                                                                                                                                                                                           
function diff($old, $new){                                                                                                                                                                                 
foreach($old as $oindex => $ovalue){                                                                                                                                                                       
$nkeys = array_keys($new, $ovalue);                                                                                                                                                                        
foreach($nkeys as $nindex){                                                                                                                                                                                
$matrix[$oindex][$nindex] = isset($matrix[$oindex - 1][$nindex - 1]) ?                                                                                                                                     
$matrix[$oindex - 1][$nindex - 1] + 1 : 1;                                                                                                                                                                 
if($matrix[$oindex][$nindex] > $maxlen){                                                                                                                                                                   
$maxlen = $matrix[$oindex][$nindex];                                                                                                                                                                       
$omax = $oindex + 1 - $maxlen;                                                                                                                                                                             
$nmax = $nindex + 1 - $maxlen;                                                                                                                                                                             
}                                                                                                                                                                                                          
}                                                                                                                                                                                                          
}                                                                                                                                                                                                          
if($maxlen == 0) return array(array('d'=>$old, 'i'=>$new));                                                                                                                                                
return array_merge(                                                                                                                                                                                        
diff(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)),                                                                                                                                            
array_slice($new, $nmax, $maxlen),                                                                                                                                                                         
diff(array_slice($old, $omax + $maxlen), array_slice($new, $nmax + $maxlen)));                                                                                                                             
}                                                                                                                                                                                                          
                                                                                                                                                                                                           
function htmlDiff($old, $new){                                                                                                                                                                             
$diff = diff(explode(' ', $old), explode(' ', $new));                                                                                                                                                      
foreach($diff as $k){                                                                                                                                                                                      
if(is_array($k))                                                                                                                                                                                           
$ret .= (!empty($k['d'])?"<del>".implode(' ',$k['d'])."</del> ":'').                                                                                                                                       
(!empty($k['i'])?"<ins>".implode(' ',$k['i'])."</ins> ":'');                                                                                                                                               
else $ret .= $k . ' ';                                                                                                                                                                                     
}                                                                                                                                                                                                          
return $ret;                                                                                                                                                                                               
}                                                                                                                                                                                                          


                                                                                                                                                                                                           
?>
