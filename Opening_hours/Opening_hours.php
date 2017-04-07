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
	} // END __construct()

	/**
	 * Fetch and return required events.
	 * @param  array $atts 	shortcode attributes
	 * @return string 	shortcode output
	 */
	public function Opening_hours_fct()
	{
		$xml=simplexml_load_file("http://www.genevamodelcars.ch/wp-content/plugins/Opening_hours/schedule.xml") or die("Error: Cannot create object");
		date_default_timezone_set('Europe/Zurich');
		
		$monthnow = date("M");
		$datenow = date("N");
		$timenow = date("H:i");
		if ($xml->$monthnow->season == 0){
			$message = 'Pause hivernale';
			$statut = 'Piste fermée';
			$etat = 'close';
			}
		else {
			if ($datenow == 7) {//Dimanche
				if (strtotime($timenow) >= strtotime($xml->$monthnow->weekend->open)){
					if (strtotime($timenow) <= strtotime($xml->$monthnow->weekend->close)){
					// Piste ouverte
					$message = 'Fermeture à ' . $xml->$monthnow->weekend->close . '.';
					$statut = 'Piste ouverte';
					$etat = 'open';
					}
					else {
					// Piste fermée fin de journée
				    $message = 'A demain!';
					$statut = 'Piste fermée';
					$etat = 'closed';
					}
				}
				else {
				//Piste va ouvrir
					$message = 'Ouverture à ' . $xml->$monthnow->weekend->open . '.';
					$statut = 'Piste fermée';	
					$etat = 'closed';					
					}
			}		
			else { //Semaine
				if (strtotime($timenow) >= strtotime($xml->$monthnow->week->open)) {
					if (strtotime($timenow) <= strtotime($xml->$monthnow->week->close)) {
					// Piste ouverte
					$message = 'Fermeture à ' . $xml->$monthnow->week->close . '.';
					$statut = 'Piste ouverte';
					$etat = 'open';
					}
					else {	// Piste fermée fin de journée
				    $message = 'A demain!';
					$statut = 'Piste fermée';
					$etat = 'closed';
					}
				}
				else {
				//Piste va ouvrir
					$message = 'Ouverture à ' . $xml->$monthnow->week->open . '.';
					$statut = 'Piste fermée';
					$etat = 'closed';
				}
			}
		}
		return '<div id="openinghours"><div class="' . $etat . '"><div class="statut">' . $statut . '</div><div class ="message">' . $message . '</div></div></div>';
	}
	
	public function Opening_hours_table_fct()
	{

		$xml=simplexml_load_file("http://www.genevamodelcars.ch/wp-content/plugins/Opening_hours/schedule.xml") or die("Error: Cannot create object");
		date_default_timezone_set('Europe/Zurich');

		$monthnum = date("n");
		$monthfrench = array("Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"); 

		$string = '<table class="table .table-striped">
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
