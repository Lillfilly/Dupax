<?php
class HtmlTable{
    private $html = "";

    public function __construct($resultSet = NULL, $totalRows = 0){
	if($resultSet != NULL){
	    $this->generateTable($resultSet, $totalRows);
	}
    }
    /**
    *	Generates a HTML table representing a resultset.
    *	$param $resultSet The query result.
    * 	@return string html for the table
    */
    public function generateTable($resultSet, $totalRows){
	$html;
	$keys = array();
	$vals = array();
	$resultCount = count($resultSet);

	//Divide into keys and values
	foreach($resultSet as $row){
	    foreach($row as $key => $val){
		$keys[$key] = $key;//We dont want any duplicates
		$vals[] = $val;
	    }
	}
	
	//Pagination links
	$link2 = '<a href="' . getQueryString(array('rpp' => 2)) . '">2</a>';
	$link4 = '<a href="' . getQueryString(array('rpp' => 4)) . '">4</a>';
	$link6 = '<a href="' . getQueryString(array('rpp' => 6)) . '">6</a>';
	$pageLinks = array();
	if($resultCount < $totalRows){
	    $pageCount = ceil($totalRows / $_GET['rpp']);

	    for($i = 1; $i <= $pageCount; $i++){
		$pageLinks[] = '<a href="' . getQueryString(array('rp' => $i)) . '">' . $i . '</a> ';
	    }
	}

	$html = "<table border>";
	//Add the pagination links
	$html .= '<tr>';
	$html .= '<td style="text-align:left" colspan=' . ceil(count($keys) / 2) . '>Sida: ';
	foreach($pageLinks as $link){
	    $html .= $link;
	}
	$html .= '</td>';
	$html .= '<td style="text-align:right;" colspan=' . floor(count($keys) / 2) . '>Resultat per sida: ' . "{$link2} {$link4} {$link6}</td>";
	$html .= '</tr>';
	//Create the table headers
	$html .= "<tr>";
	foreach($keys as $key){
	    $upArrow = '<a href="' . getQueryString(array('order' => 'desc', 'orderby' => $key)) . '">&uarr;</a>';
	    $downArrow = '<a href="' . getQueryString(array('order' => 'asc', 'orderby' => $key)) . '">&darr;</a>';
	    $html .= "<th>{$key} {$upArrow}{$downArrow}</th>";
	}
	$html .= '</tr>';

	//Add value rows
	if(count($vals) > 0){
	    $html .= '<tr>';
	    for($i = 0; $i < count($vals); $i++){
		if($i > 0 AND $i%count($keys) == 0){//End of row?
		    $html .= '</tr>';
		    if($i + 1 < count($vals)){//Do we need to make a new row?
			$html .= '<tr>';
		    }
		}
		$html .= "<td>{$vals[$i]}</td>";
	    }
	}
	$html .= '<tr><td style="text-align:center" colspan=' . count($keys) . '>Visar ' . count($resultSet) . " av {$totalRows} resultat</td></tr>";
	$html .= '</table>';
	$this->html = $html;
	return $html;
    }
    /**
    *	Get the html-code for the table
    *	@param $showBorder bool Show border (default false)
    *	@return string html of the table
    */
    public function getHtmlTable(){
	return $this->html;
    }
}
