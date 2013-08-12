<?php
namespace DBRisinajumi\DBDataAnalizer;

class DbDataAnalyzer
{
    const TBL_NAME = 'db_analyzer';
    /**
     * mysqli connection
     * @var object 
     */
    private $db;
    /**
     * errors from sql statements
     * @var array 
     */
    private $aErrors = array();
    
    private $aMySqliNumericTypes = array(
        MYSQLI_TYPE_TINY,
        MYSQLI_TYPE_SHORT,
        MYSQLI_TYPE_LONG,
        MYSQLI_TYPE_FLOAT,
        MYSQLI_TYPE_DOUBLE,
        MYSQLI_TYPE_LONGLONG,
        MYSQLI_TYPE_INT24,
        MYSQLI_TYPE_NEWDECIMAL,
        MYSQLI_TYPE_DECIMAL,
   );
    
   /*MYSQL_TYPE_DECIMAL,
   MYSQLI_TYPE_TINY,
   MYSQLI_TYPE_SHORT,
   MYSQLI_TYPE_LONG,
   MYSQLI_TYPE_FLOAT,
   MYSQLI_TYPE_DOUBLE,
   MYSQLI_TYPE_NULL,
   MYSQLI_TYPE_TIMESTAMP,
   MYSQLI_TYPE_LONGLONG,
   MYSQLI_TYPE_INT24,
   MYSQLI_TYPE_DATE, 
   MYSQLI_TYPE_TIME,
   MYSQLI_TYPE_DATETIME, 
   MYSQLI_TYPE_YEAR,
   MYSQLI_TYPE_NEWDATE, 
   MYSQLI_TYPE_VARCHAR,
   MYSQLI_TYPE_BIT,
   MYSQLI_TYPE_NEWDECIMAL=246,
   MYSQLI_TYPE_ENUM=247,
   MYSQLI_TYPE_SET=248,
   MYSQLI_TYPE_TINY_BLOB=249,
   MYSQLI_TYPE_MEDIUM_BLOB=250,
   MYSQLI_TYPE_LONG_BLOB=251,
   MYSQLI_TYPE_BLOB=252,
   MYSQLI_TYPE_VAR_STRING=253,
   MYSQLI_TYPE_STRING=254,
   MYSQLI_TYPE_GEOMETRY=255*/
    
    public function __construct(\mysqli $db)
    {
        $this->db = $db;
    }

    /**
     * returns DB Analyzer record
     * 
     * @param int $nId
     * @return array $aReturn
     */
    public function getSelect($nId)
    {
        $sSql = "
        SELECT
            `id`, `name`, `subgroup`, `sql_statement`, `comments`
        FROM
            ".self::TBL_NAME."
        WHERE
            hidden = 0 AND
            `id` = '".$this->db->escape_string($nId)."'
        LIMIT 1
        ";
        //echo $sSql;
        $oAnalyzeResult = $this->db->query($sSql);
        if ($oAnalyzeResult->num_rows == 0) {
            return false;
        }

        return $oAnalyzeResult->fetch_assoc();
    }
    
    /**
     * returns DB select result as array
     * 
     * @param int $nId
     * @return array $aReturn
     */
    public function getResult($nId)
    {
        $aRow = $this->getSelect($nId);
        $oResult = $this->db->query($aRow['sql_statement']);

        return $oResult;
    }

    /**
     * returns vlibTemplate compatible array
     * 
     * @param array $aArr - mysqli data
     * @param array $aFields - mysqli fields
     * @return array
     */
    public function getArrForVlib($aArr, $aFields)
    {
        $aReturn = array();
        foreach ($aArr as $nId => $aResult) {
            foreach ($aResult as $nId2 => $sValue) {
                $aReturn[$nId]['columns'][$nId2] = array(
                    'value' => $sValue,
                    'is_numeric' => $aFields[$nId2]['is_numeric'],
                    'type' => $aFields[$nId2]['type']
                );
            }
        }

        return $aReturn;
    }

    /**
     * returns array of fields for sql statement
     * 
     * @param \mysqli object $oDbResult
     * @return array
     */
    public function fetchFields($oDbResult)
    {
        $aReturn = array();
        $aFields = (array)$oDbResult->fetch_fields();
        foreach ($aFields as $oField) {
            $aReturn[] = array(
                'name' => $oField->name,
                'type' => $oField->type,
                'is_numeric' => $this->isMysqliTypeNumeric($oField->type)
            );
        }

        return $aReturn;
    }
    
    private function isMysqliTypeNumeric($nType)
    {
        return in_array($nType, $this->aMySqliNumericTypes);
    }

    /**
     * returns group list in array
     * 
     * @return array $aReturn
     */
    public function getGroupsList()
    {
        $sSql = "SELECT `group` FROM ".self::TBL_NAME." WHERE hidden = 0";
        $oResult = $this->db->query($sSql);
        if ($oResult == 0) {
            return false;
        }

        return $this->fetchAll($oResult, MYSQLI_ASSOC);
    }

    /**
     * returns subgroup data for group
     * 
     * @param string $sGroup
     * @return array $aReturn
     */
    public function getSubGroupList($sGroup)
    {
        $sSql = "
        SELECT
            `id`, `name`, `subgroup`, `sql_statement`, `comments`
        FROM
            ".self::TBL_NAME."
        WHERE
            hidden = 0 AND
            `group` = '".$this->db->escape_string($sGroup)."'
        ";
        //echo $sSql;
        $oResult = $this->db->query($sSql);
        if ($oResult->num_rows == 0) {
            return false;
        }

        return $this->fetchAll($oResult, MYSQLI_ASSOC);
    }

    /**
     * exec all sql queries for selected group and returns number of rows
     * 
     * @return array $aReturn - array('id','name','group','subgroup','count')
     */
    public function getExecCountGroup($sGroup)
    {
        $aSubGroupData = $this->getSubGroupList($sGroup);
        if (empty($aSubGroupData)) {
            return false;
        }
        foreach ($aSubGroupData as $nId => $aSubGroup) {
            $oResult = $this->db->query($aSubGroup['sql_statement']);
            if (!$oResult) {
                $this->aErrors[] = $this->db->error;
                $aSubGroupData[$nId]['error'] = $this->db->error;
                $aSubGroupData[$nId]['num_rows'] = 0;
            } else {
                $aSubGroupData[$nId]['num_rows'] = $oResult->num_rows;
            }
            unset($aSubGroupData[$nId]['sql_statement']);
        }

        return $aSubGroupData;
    }
    
    /**
     * for compatibility with older php versions
     * 
     * @param mysqli_result object $oResult
     * @param int $nResultType
     * @return array
     */
    public function fetchAll($oResult, $nResultType = MYSQLI_ASSOC)
    {
        if (method_exists('mysqli_result', 'fetch_all')) // Compatibility layer with PHP < 5.3
            $res = mysqli_fetch_all($oResult, $nResultType);
        else
            for ($res = array(); $tmp = mysqli_fetch_array($oResult, $nResultType);) $res[] = $tmp;

        return $res;
    }
}