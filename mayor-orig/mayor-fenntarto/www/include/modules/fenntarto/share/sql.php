<?php

    function getEnumField($modul, $table, $field) {

        $table = '`'.str_replace('.','`.`',$table).'`';
        $q = "SHOW FIELDS FROM %s LIKE '%s'";
        $v = array($table, $field);
        $field = db_query($q, array('fv' => 'getEnumField', 'modul' => $modul, 'result' => 'record', 'values' => $v));
        $enum = substr($field['Type'], 6, -2);
        $values = explode("','", $enum);
        
        return $values;
    }
    
    function getSetField($modul, $table, $field) {

        $table = '`'.str_replace('.','`.`',$table).'`';
        $q = "SHOW FIELDS FROM %s LIKE '%s'";
        $v = array($table, $field);
        $field = db_query($q, array('fv' => 'getSetField', 'modul' => $modul, 'result' => 'record', 'values' => $v));
        $set = substr($field['Type'], 5, -2);
        $values = explode("','", $set);

        return $values;
    }

?>