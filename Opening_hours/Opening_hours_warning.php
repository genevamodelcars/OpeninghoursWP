<?php
/***
Opening_hours warning refresh
Auto update function
*/
	error_reporting(E_ERROR | E_PARSE);
	header("Access-Control-Allow-Origin: *"); 
	header('Access-Control-Allow-Methods: GET, POST, OPTIONS');	

	$xml=simplexml_load_file("http://www.genevamodelcars.ch/wp-content/plugins/Opening_hours/schedule.xml") or die("Error: Cannot create object");
		date_default_timezone_set('Europe/Zurich');
		
		$monthnow = date("M");
		$datenow = date("N");
                $datedaymonthnow = date("Ymd");
		$timenow = date("H:i");
		if ($xml->$monthnow->season == 0){
			$message = 'Pause hivernale';
			$statut = 'Piste fermée';
			$etat = 'closed';
			}
		elseif (($datedaymonthnow >= $xml->exep->start) and ($datedaymonthnow <= $xml->exep->stop)) {
			$message = $xml->exep->message;
			$statut = 'Piste fermée';
			$etat = 'closed';
			}
		else {
			if ($datenow == 7) {//Dimanche
				if (strtotime($timenow) >= strtotime($xml->$monthnow->weekend->open)){
					if (strtotime($timenow) <= strtotime($xml->$monthnow->weekend->close)){
					// Piste ouverte
					$message = 'Fermeture à ' . $xml->$monthnow->weekend->close . '.';
					$statut = 'Piste ouverte';
					$etat = 'opened';
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
					$etat = 'opened';
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
		echo '<a href="/circuit/" id="openinghours"><div class="' . $etat . '"><div class="statut">' . $statut . '</div><div class ="message">' . $message . '</div></div></a>';