<?php
namespace DBRisinajumi\DBAnalizer;

class DBAnalyzer
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
    private $aErrors = [];

    public function __construct(\mysqli $db)
    {
        $this->db = $db;
    }

    /**
     * returns DB select result as array
     * 
     * @param string $sGroup
     * @param string $sSubGroup
     * @return array $aReturn
     */
    public function getSelect($sGroup, $sSubGroup)
    {
        
    }

    /**
     * 
     * 
     * @param string $sGroup
     * @param string $sSubGroup
     * @return string $sName
     */
    public function getName($sGroup, $sSubGroup)
    {
        
    }

    /**
     * 
     * 
     * @param string $sGroup
     * @param string $sSubGroup
     * @return string $sComment
     */
    public function getComment($sGroup, $sSubGroup)
    {
        
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

        return $oResult->fetch_all(MYSQLI_ASSOC);
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

        return $oResult->fetch_all(MYSQLI_ASSOC);
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
        }

        return $aSubGroupData;
    }
}