<?php
// MaYoR - GPL - author: support@mayor.hu
class mayorCache {
    private $DATA = array();
    private $REGISTRY = array();
    public function exists($key) {
	return (!is_null($this->DATA[$key]));
    }
    public function get($key) {
	return $this->DATA[$key];
    }
    public function set($key,$data, $dataType=null) {
	$this->DATA[$key] = $data;
	if ($dataType!='') $this->REGISTRY[$dataType][] = $key;
    }
    public function del($key) {
	    unset($this->DATA[$key]);
	    unset($this->REGISTRY[array_search($key,$this->REGISTRY)]);
    }
    public function flushdb() {
	$this->DATA = array();
	$this->REGISTRY = array();
    }
    public function delType($dataType) {
	$c = count($this->REGISTRY[$dataType]);
	for ($i=0; $i<$c; $i++ ) {
	    $this->del($this->REGISTRY[$dataType][$i]);
	}
	unset($this->REGISTRY[$dataType]);
    }	
}
$mayorCache = new mayorCache();
?>