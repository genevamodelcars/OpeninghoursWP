<?php
/***
 Plugin Name: Opening hours by genevamodelcars
 Description: genevamodelcars plugin for display opening hours of the track.
 Version: 0.1
 Author: BDM for genevamodelcars
*/

// Avoid direct calls to this file
if ( !defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

class Opening_hours
{
	const VERSION = '0.1';

	/**
	 * Constructor. Hooks all interactions to initialize the class.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @see	 add_shortcode()
	 */
	public function __construct()
	{
		add_shortcode('Opening_hours', array($this, 'Opening_hours_fct') ); // link new function to shortcode name
		add_shortcode('Opening_hours_table', array($this, 'Opening_hours_table_fct') ); // link new function to shortcode name
		add_shortcode('Opening_hours_alert', array($this, 'Opening_hours_alert_fct') ); // link new function to shortcode name
		add_shortcode('Opening_hours_clock', array($this, 'Opening_hours_clock_fct') ); // link new function to shortcode name
	} // END __construct()

	/**
	 * Fetch and return required events.
	 * @param  array $atts 	shortcode attributes
	 * @return string 	shortcode output
	 */
	public function Opening_hours_fct()
	{
		$string_warning = '<div id="auto_load_warning_div"></div>';

		?>

		<script>
		
		var $jq = jQuery.noConflict();
		
			function auto_load_warning(){
				$jq.ajax({
				url: "/wp-content/plugins/Opening_hours/Opening_hours_warning.php",
				cache: false,
				success: function(data){
					$jq("#auto_load_warning_div").html(data);
				} 
				});
			}
		
			$jq(document).ready(function(){
		
				auto_load_warning(); //Call auto_load() function when DOM is Ready
		
			});

			  //Refresh auto_load() function after 500 milliseconds
			  setInterval(auto_load_warning,3000);
		 </script>
		<?php

		return $string_warning;
	}
	
	public function Opening_hours_table_fct()
	{

		$xml=simplexml_load_file("http://www.genevamodelcars.ch/wp-content/plugins/Opening_hours/schedule.xml") or die("Error: Cannot create object");
		date_default_timezone_set('Europe/Zurich');

		$monthnum = date("n");
		$monthfrench = array("Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"); 

		$string = '<table class="table table-striped">
				<thead>
				  <tr>
					<th>Mois</th>
					<th>Lundi - Samedi</th>
					<th>Dimanche</th>
				 </tr>
				</thead>
				<tbody>';

		for ($i = 1; $i <= 12; $i++) {
			$monthName = date('M', mktime(0, 0, 0, $i, 10));
			
			//Check active month
			if ($i == $monthnum){
				$classe = 'success';
			}
			else {
			$classe = '';
			}
				if ($xml->$monthName->season == 0){
					$string .= '<tr class="' . $classe . '"><td>' . $monthfrench[date('n', mktime(0, 0, 0, $i, 10))-1] . '</td>
					<td>Fermeture hivernale</td><td>Fermeture hivernale</td></tr>';
				}
				else {
					$string .=  '<tr class="' . $classe . '">
						<td>' . $monthfrench[date('n', mktime(0, 0, 0, $i, 10))-1] . '</td>
						<td>' . $xml->$monthName->week->open . ' - ' . $xml->$monthName->week->close . '</td>
						<td>' . $xml->$monthName->weekend->open . ' - ' . $xml->$monthName->weekend->close . '</td></tr>';
				}
		}

		$string .=  '    </tbody>
		  </table>';
		return $string;
	}

	public function Opening_hours_alert_fct()
	{

		$xml=simplexml_load_file("http://www.genevamodelcars.ch/wp-content/plugins/Opening_hours/schedule.xml") or die("Error: Cannot create object");
		date_default_timezone_set('Europe/Zurich');

		$monthnow = date("M");
		$datenow = date("N");

		if ($datenow == 7){
			$closetime = strtotime($xml->$monthnow->weekend->close);
		}
		else {
			$closetime = strtotime($xml->$monthnow->week->close);
		}
	
	?>
	
	<script>       
		function refresh() {
        
        var d = new Date();
        var time = d.getTime()/1000;
        var alertduration =30*60;
       
        if ((time > <?php echo $closetime; ?> - alertduration) && (time < <?php echo $closetime; ?>))
            {
                
                $jq("#openninghoursalert").css({
                  'visibility': 'visible',
                  'opacity': '1',
		  'height': '100%',
		  'margin-bottom': '40px',
                  'transition': 'visibility 2s, opacity 2s linear'
                });
                
            } else {
             
                 $jq("#openninghoursalert").css({
                  'visibility': 'hidden',
                  'opacity': '0',
                  'height': '0',
		  'margin-bottom': '0',
                  'transition': 'visibility 2s, opacity 2s linear'
                 });
                
            }
        }
        setInterval("refresh();",5000);
	</script>
	<?php

	return '<div class = "alert-danger" style ="visibility: hidden; margin-bottom: 0px; text-transform: uppercase; height: 0px; font-weight: 700;" id="openninghoursalert">Le circuit ferme dans moins de 30 min.</div>';

	}

	public function Opening_hours_clock_fct()
	{
		//xml pasring
		$xml=simplexml_load_file("http://www.genevamodelcars.ch/wp-content/plugins/Opening_hours/note.xml") or die("Error: Cannot create object");
		date_default_timezone_set('Europe/Zurich');
		$sessionnum = $xml->count();
		$sessiontime = 60 / $sessionnum;

		//Clock definition
		$height = 600;
		$c = $height/2;
		$r = $c/2;
		$p = 2*3.14*$r;

		echo '<div class="container"><svg width="100%" viewBox="0 0 ' . $height . ' ' . $height . '">';

		for ($i = 0; $i < $sessionnum; $i++) {
   			echo  '<circle r="' . $r . '" cx="' . $c . '" cy="' . $c . '" style="stroke-dasharray:' . $sessiontime  / 60 * $p . ' ' . $p . ';stroke:' . $xml->Session[$i]->Color . '; 		transform: rotate(' . (-90 + ($sessiontime*$i)/$sessiontime/$sessionnum*360) . 'deg); transform-origin: 50% 50%;" id="' . $i . '" />';   
		}
    		echo '<circle r="' . $r*1.3 . '" cx="' . $c . '" cy="' . $c . '" style="fill: white;"/>';
    		echo '<g>
        	<line x1="' . $c . '" y1="' . $c . '" x2="' . $c . '" y2="' . $c*0.7 . '" transform="rotate(80 100 100)" style="stroke-width: 5px; stroke: black;" id="hourhand">
            		<animatetransform attributeName="transform"
                              attributeType="XML"
                              type="rotate"
                              dur="43200s"
                              repeatCount="indefinite"/>
       		</line>
        	<line x1="' . $c . '" y1="' . $c . '" x2="' . $c . '" y2="' . $c*0.5 . '" style="stroke-width: 6px; stroke: black;" id="minutehand">
            	<animatetransform attributeName="transform"
                              attributeType="XML"
                              type="rotate"
                              dur="3600s"
                              repeatCount="indefinite"/>
        	</line>
        	<line x1="' . $c . '" y1="' . $c . '" x2="' . $c . '" y2="' . $c*0.4 . '" style="stroke-width: 4px; stroke: black;" id="secondhand">
            		<animatetransform attributeName="transform"
                              attributeType="XML"
                              type="rotate"
                              dur="60s"
                              repeatCount="indefinite"/>
        	</line>
    	</g>
    	<circle id="center" style="fill:black; stroke: black; stroke-width: 4px;" cx="' . $c . '" cy="' . $c . '" r="3"></circle>';
    	echo '</svg></div>';

	//return $clockreturn;
    
	?>

	<script>
	var hands = [];
	hands.push(document.querySelector('#secondhand > *'));
	hands.push(document.querySelector('#minutehand > *'));
	hands.push(document.querySelector('#hourhand > *'));

	var cx = <?php echo $c; ?>;
	var cy = <?php echo $c; ?>;

	function shifter(val) {
	  return [val, cx, cy].join(' ');
	}

	var date = new Date();
	var hoursAngle = 360 * date.getHours() / 12 + date.getMinutes() / 2;
	var minuteAngle = 360 * date.getMinutes() / 60;
	var secAngle = 360 * date.getSeconds() / 60;

	hands[0].setAttribute('from', shifter(secAngle));
	hands[0].setAttribute('to', shifter(secAngle + 360));
	hands[1].setAttribute('from', shifter(minuteAngle));
	hands[1].setAttribute('to', shifter(minuteAngle + 360));
	hands[2].setAttribute('from', shifter(hoursAngle));
	hands[2].setAttribute('to', shifter(hoursAngle + 360));

	for(var i = 1; i <= 12; i++) {
	  var el = document.createElementNS('http://www.w3.org/2000/svg', 'line');
	  el.setAttribute('x1', '<?php echo $c; ?>');
	  el.setAttribute('y1', '<?php echo $c*0.35; ?>');
	  el.setAttribute('x2', '<?php echo $c; ?>');
	  el.setAttribute('y2', '<?php echo $c*0.45; ?>');
	  el.setAttribute('transform', 'rotate(' + (i*360/12) + ' <?php echo $c; ?> <?php echo $c; ?>)');
	  el.setAttribute('style', 'stroke: black;');
	  document.querySelector('svg').appendChild(el);
	}

	function changesession() {
	var date = new Date();
	for(var i = 0; i < <?php echo $sessionnum; ?>; i++) {
    	if (Math.floor(date.getMinutes() / (60 / <?php echo $sessionnum; ?>)) == i){
        
        	document.getElementById(i).style["stroke-width"] = "<?php echo $r*1.3; ?>";
        
    	} else {
    
        	document.getElementById(i).style["stroke-width"] = "<?php echo $r; ?>";
        
    	}
	}
	}
  
	setInterval("changesession();",500);

	</script>
	<?php

	

	}

	/**
	 * Checks if the plugin attribute is valid
	 *
	 * @since 1.0.5
	 *
	 * @param string $prop
	 * @return boolean
	 */
	private function isValid( $prop )
	{
		return ($prop !== 'false');
	}

	/**
	 * Fetch and trims the excerpt to specified length
	 *
	 * @param integer $limit Characters to show
	 * @param string $source  content or excerpt
	 *
	 * @return string
	 */
	private function get_excerpt( $limit, $source = null )
	{
		$excerpt = get_the_excerpt();
		if( $source == "content" ) {
			$excerpt = get_the_content();
		}

		$excerpt = preg_replace(" (\[.*?\])", '', $excerpt);
		$excerpt = strip_tags( strip_shortcodes($excerpt) );
		$excerpt = substr($excerpt, 0, $limit);
		$excerpt = trim(preg_replace( '/\s+/', ' ', $excerpt));
		$excerpt .= '...';

		return $excerpt;
	}
}

/**
 * Instantiate the main class
 *
 * @since 1.0.0
 * @access public
 *
 */
global $Opening_hours;
$Opening_hours = new Opening_hours();
