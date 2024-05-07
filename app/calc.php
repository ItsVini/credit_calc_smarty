<?php
require_once dirname(__FILE__).'/../config.php';

// KONTROLER strony kalkulatora

// W kontrolerze niczego nie wysyła się do klienta.
// Wysłaniem odpowiedzi zajmie się odpowiedni widok.
// Parametry do widoku przekazujemy przez zmienne.

//ochrona kontrolera - poniższy skrypt przerwie przetwarzanie w tym punkcie gdy użytkownik jest niezalogowany
include _ROOT_PATH.'/app/security/check.php';

//pobranie parametrów
function getParams(&$credit,&$percent,&$years){
	$credit = $_REQUEST['credit'] ?? null;
	$percent = $_REQUEST['percent'] ?? null;
	$years = $_REQUEST['years'] ?? null;	
}

//walidacja parametrów z przygotowaniem zmiennych dla widoku
function validate(&$credit,&$percent,&$years,&$messages){
	// sprawdzenie, czy parametry zostały przekazane
	if ( ! ($credit && $percent && $years)) {
		// sytuacja wystąpi kiedy np. kontroler zostanie wywołany bezpośrednio - nie z formularza
		// teraz zakładamy, ze nie jest to błąd. Po prostu nie wykonamy obliczeń
		return false;
	}

	// sprawdzenie, czy potrzebne wartości zostały przekazane
	if ( $credit == "") {
		$messages [] = 'Nie podano liczby kredytu';
	}
	   if ($years == "") {
        $messages[] = 'Nie podano liczby lat';
    }
	if ( $percent == "") {
		$messages [] = 'Nie podano liczby oprocentowania';
	}

	//nie ma sensu walidować dalej gdy brak parametrów
	if ($messages) return false;
	
	// sprawdzenie, czy $credit i $percent są liczbami całkowitymi
	if (! is_numeric( $credit )) {
		$messages [] = 'Pierwsza wartość nie jest liczbą całkowitą';
	}
	    if (!is_numeric($years)) {
        $messages[] = 'Lata nie są liczbą całkowitą';
    }
	
	if (! is_numeric( $percent )) {
		$messages [] = 'Druga wartość nie jest liczbą całkowitą';
	}	

	return empty($messages);
}

function process(&$credit,&$percent,&$years,&$messages,&$result, $role){

	
	//konwersja parametrów na int
	$credit = intval($credit);
	$percent = intval($percent);
	$years = intval($years);
	
	//wykonanie operacji
    if ($role === 'admin') {
        $result = ($credit * pow((1 + $percent / 100), $years)) / (12 * $years);
    } else {
        $messages[] = 'Tylko administrator może wyliczyć miesięczną ratę!';
    }

}

//definicja zmiennych kontrolera
$credit = null;
$percent = null;
$years = null;
$result = null;
$messages = array();

//pobierz parametry i wykonaj zadanie jeśli wszystko w porządku
getParams($credit,$percent,$years);
if ( validate($credit,$percent,$years,$messages) ) { // gdy brak błędów
	process($credit,$percent,$years,$messages,$result, $role);
}

// Wywołanie widoku z przekazaniem zmiennych
// - zainicjowane zmienne ($messages,$credit,$percent,$years,$result)
//   będą dostępne w dołączonym skrypcie
include 'calc_view.php';