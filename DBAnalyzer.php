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
        $sSql = "SELECT `group` FROM ".TBL_NAME." WHERE hidden = 0";
        $oResult = $this->db->query($sSql);
        if ($oResult == 0) {
            return false;
        }

        return $oResult->fetch_all();
    }

    /**
     * returns list of subgroups for group
     * 
     * @param string $sGroup
     * @return array $aReturn
     */
    public function getSubGroupList($sGroup)
    {
        $sSql = "SELECT `subgroup` FROM ".TBL_NAME." WHERE hidden = 0 AND group = '".$this->db->escape($sGroup)."'";
        $oResult = $this->db->query($sSql);
        if ($oResult == 0) {
            return false;
        }

        return $oResult->fetch_all();
    }

    /**
     * exec all sql queries for selected group and returns number of rows
     * 
     * @return array $aReturn - array('id','name','group','subgroup','count')
     */
    public function getExecCountGroup($sGroup)
    {
        $sSql = "
            SELECT
                `id`, `name`, `subgroup`, `sql_statements`, `comments`
            FROM
                ".TBL_NAME."
            WHERE
                hidden = 0 AND group = '".$this->db->escape($sGroup)."'";
        $oResult = $this->db->query($sSql);
        if ($oResult == 0) {
            return false;
        }
    }
}