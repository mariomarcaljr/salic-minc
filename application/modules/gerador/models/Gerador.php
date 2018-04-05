<?php

class Gerador_Model_Gerador extends Zend_Db_Table
{
    public function gerar()
    {
        return $this->_getTabelas();
    }

    private function _getTabelas()
    {
        $sql = "select  distinct tab.name tabela, col.name coluna, sch.name esquema, col.is_nullable,  
                        typ.name tipo, typ.max_length, typ.precision, typ.scale, typ.is_nullable -- ,  fk.* 
                from sys.columns col
                    INNER JOIN sys.tables tab on tab.object_id = col.object_id
                    INNER JOIN sys.types typ on typ.user_type_id = col.user_type_id
                    LEFT JOIN sys.schemas sch on tab.schema_id = sch.schema_id
                    LEFT JOIN sys.foreign_keys fk on tab.object_id = fk.referenced_object_id
                    LEFT JOIN sys.tables tabref on tabref.object_id = fk.parent_object_id
                where sch.name in ('dbo')
                order by sch.name, tab.name";

        $rowSet = $this->getDefaultAdapter()->query($sql)->fetchAll();

        $aTabelas = [];
        foreach($rowSet as $row){
            $aTabelas[$row['esquema']][$row['tabela']][$row['coluna']] = $row;
        }

        return $aTabelas;
    }
}