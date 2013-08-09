<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require dirname(__FILE__).'/DBAnalyzer.php';

$Analyzer = new \DBRisinajumi\DBAnalizer\DBAnalyzer();

$aGroups = $Analyzer->getGroupsList();
var_dump($aGroups);
$aSubGroups = $Analyzer->getSubGroupList("test");
var_dump($aSubGroups);