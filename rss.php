<?php

// required
require_once('./config/api_key.php');
require_once( './lib/Api.php' );

// initiate
$api = new Be_Api(API_KEY, CLIENT_SECRET);

//--------------------------------------------------------------------------
// Fetch Projects
//--------------------------------------------------------------------------
$projects = $api->searchProjects( array( 'sort' => 'featured_date', 'country' => 'AS') );
$last_update = date("D, d M Y g:i:s O",$projects[0]->published_on);

//--------------------------------------------------------------------------
// RSS HEADER
//--------------------------------------------------------------------------
header("Content-Type: application/xml; charset=ISO-8859-1");
$output =   '<?xml version="1.0" encoding="utf-8"?>' .
            '<feed xmlns="http://www.w3.org/2005/Atom" xml:lang="en">' . PHP_EOL .
                '<id></id>'.
                '<title>Behance: Australia</title>' . 
                '<updated>'.$last_update.'</updated>' .
                '<author>' .
                    '<name>Callum Barker</name>' . 
                    '<uri>http://github.com/csbarker</uri>' . 
                '</author>' .
                '<rights>Copyright '.date("Y").'</rights>';

//--------------------------------------------------------------------------
// RSS BODY
//--------------------------------------------------------------------------
foreach ($projects as $item) {
    $cover = '';
    $cover_count = count($item->covers);
    $cover_count_current = 0;

    // find the largest cover
    foreach ($item->covers as $key => $val) {
        if ($cover_count_current === $cover_count) {
            $cover = $item->covers->{$key};
        } else {
            $cover_count_current++; // not there yet
        }
    }
    
    // format name
    $name = $item->owners[0]->username . " ({$item->owners[0]->first_name}";
    $name .= (!empty($item->owners[0]->last_name)) ? " {$item->owners[0]->last_name})" : ')';
    
    // format location
    $location = '';
    if (!empty($item->owners[0]->city)) { $location .= $item->owners[0]->city . ', '; }
    if (!empty($item->owners[0]->state)) { $location .= $item->owners[0]->state . ', '; }
    $location .= $item->owners[0]->country . '.';
    
    // company
    $company = '';
    if (!empty($item->owners[0]->company)) { $company = " [{$item->owners[0]->company}]"; }
    
    // add entry
    $output .=  '<entry>' .
                    '<title>'.$item->name.'</title>' . PHP_EOL .
                    '<link>'.$item->url.'</link>' . PHP_EOL .
                    '<link rel="alternate" type="text/html" href="'.$item->url.'"/>' . PHP_EOL .
                    '<updated>'.date("D, d M Y g:i:s O",$item->published_on).'</updated>' . PHP_EOL .
                    '<content type="html">' . PHP_EOL .
                    htmlentities(
                        '<a href="'.$item->url.'"><img src="'.$cover.'" border="0"></a>' . PHP_EOL .
                        '<p>'.  join(', ', $item->fields).'</p>' . PHP_EOL .
                        '<table>'.PHP_EOL .
                            '<tr>' . 
                                '<td><a href="'.$item->owners[0]->url.'"><img src="'.$item->owners[0]->images->{50}.'" border="0"></a></td>' .
                                '<td>&nbsp; &nbsp; <a href="'.$item->owners[0]->url.'">'.$name.'</a>'.$company.'<br>&nbsp; &nbsp; '.$location.'</td>' .
                            '</tr>'. PHP_EOL .
                        '</table>'
                    ) . PHP_EOL .
                    '</content>' . PHP_EOL .
                '</entry> ' . PHP_EOL;
}

//--------------------------------------------------------------------------
// CLOSE AND OUTPUT
//--------------------------------------------------------------------------
$output .= '</feed>';
echo($output);