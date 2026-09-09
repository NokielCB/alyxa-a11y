<?php
/**
 * Katalog modulow rdzenia.
 *
 * Kazdy modul to jeden wpis w tej tablicy i - prawie zawsze - jeden plik
 * w moduly/. Nie ma trzeciego miejsca: panel rysuje przelaczniki z rejestru,
 * skrypt dostaje z niego slug i typ, a arkusz powstaje ze sklejenia plikow
 * wskazanych kluczem 'css'. Kolejnosc zostawia luki co dziesiec, zeby moduly
 * dopisane w kolejnych fazach wchodzily na swoje miejsce bez przenumerowania
 * reszty.
 *
 * Wyjatkiem jest odczyt strony: nie zmienia jej wygladu ani o piksel, wiec
 * arkusza nie ma wcale. Klucz 'css' byl opcjonalny od pierwszej wersji
 * rejestru i dopiero ten modul z tego korzysta.
 *
 * DOMYSLNIE WLACZONE SA WSZYSTKIE MODULY RDZENIA. Instalacja bez zadnej
 * konfiguracji ma dawac dzialajacy panel, a nie pusty. Strona, ktorej cos
 * nie pasuje, wylaczy to na ekranie ustawien; strona, ktora nie chce nic
 * ustawiac, dostaje zestaw uzgodniony dla placowki publicznej.
 *
 * @package alyxa-a11y
 */

defined( 'ABSPATH' ) || exit;

/**
 * Dopisuje moduly rdzenia do rejestru.
 *
 * @param array<int, array<string, mixed>> $moduly Lista definicji.
 * @return array<int, array<string, mixed>>
 */
function alyxa_moduly_rdzenia( $moduly ) {
	$moduly[] = array(
		'slug'      => 'tekst',
		'nazwa'     => __( 'Larger text', 'alyxa-a11y' ),
		'opis'      => __( 'Three steps up to 150%. Press again to go back to normal.', 'alyxa-a11y' ),
		'typ'       => 'stopnie',
		'stopnie'   => 3,
		'css'       => ALYXA_A11Y_KATALOG . 'moduly/tekst.css',
		'domyslnie' => true,
		'kolejnosc' => 10,
	);

	$moduly[] = array(
		'slug'      => 'czcionka',
		'nazwa'     => __( 'Dyslexia-friendly font', 'alyxa-a11y' ),
		'opis'      => __( 'Atkinson Hyperlegible, a typeface drawn so that similar letters cannot be confused.', 'alyxa-a11y' ),
		'typ'       => 'przelacznik',
		'css'       => ALYXA_A11Y_KATALOG . 'moduly/czcionka.css',
		'domyslnie' => true,
		/* Zaraz za powiekszeniem tekstu, bo obie rzeczy dotycza pisma
		   i czytelnik szuka ich obok siebie, a nie na dwoch koncach listy. */
		'kolejnosc' => 15,
	);

	$moduly[] = array(
		'slug'      => 'odstepy',
		'nazwa'     => __( 'More text spacing', 'alyxa-a11y' ),
		'opis'      => __( 'Taller lines, wider gaps between letters, words and paragraphs.', 'alyxa-a11y' ),
		'typ'       => 'przelacznik',
		'css'       => ALYXA_A11Y_KATALOG . 'moduly/odstepy.css',
		'domyslnie' => true,
		'kolejnosc' => 20,
	);

	$moduly[] = array(
		'slug'      => 'kontrast',
		'nazwa'     => __( 'High contrast', 'alyxa-a11y' ),
		'opis'      => __( 'Black background, white text, yellow links. Photographs stay.', 'alyxa-a11y' ),
		'typ'       => 'przelacznik',
		'css'       => ALYXA_A11Y_KATALOG . 'moduly/kontrast.css',
		'domyslnie' => true,
		'kolejnosc' => 30,
	);

	$moduly[] = array(
		'slug'      => 'linki',
		'nazwa'     => __( 'Underline links', 'alyxa-a11y' ),
		'opis'      => __( 'Every link gets an underline, so it stands out from ordinary text.', 'alyxa-a11y' ),
		'typ'       => 'przelacznik',
		'css'       => ALYXA_A11Y_KATALOG . 'moduly/linki.css',
		'domyslnie' => true,
		'kolejnosc' => 40,
	);

	$moduly[] = array(
		'slug'      => 'animacje',
		'nazwa'     => __( 'Stop animations', 'alyxa-a11y' ),
		'opis'      => __( 'Turns off movement, sliding and fading across the page.', 'alyxa-a11y' ),
		'typ'       => 'przelacznik',
		'css'       => ALYXA_A11Y_KATALOG . 'moduly/animacje.css',
		'domyslnie' => true,
		'kolejnosc' => 50,
	);

	$moduly[] = array(
		'slug'      => 'kursor',
		'nazwa'     => __( 'Large cursor', 'alyxa-a11y' ),
		'opis'      => __( 'A bigger mouse pointer, white with a black outline so it shows on any background.', 'alyxa-a11y' ),
		'typ'       => 'przelacznik',
		'css'       => ALYXA_A11Y_KATALOG . 'moduly/kursor.css',
		'domyslnie' => true,
		'kolejnosc' => 60,
	);

	$moduly[] = array(
		'slug'      => 'maska',
		'nazwa'     => __( 'Reading mask', 'alyxa-a11y' ),
		'opis'      => __( 'Dims the page except for a band that follows the pointer, so the eye keeps its line.', 'alyxa-a11y' ),
		'typ'       => 'przelacznik',
		'css'       => ALYXA_A11Y_KATALOG . 'moduly/maska.css',
		'domyslnie' => true,
		'kolejnosc' => 70,
	);

	/*
	 * ODCZYT NIE JEST USTAWIENIEM, TYLKO CZYNNOSCIA - stad typ 'akcje'.
	 * Nie zapisuje sie w pamieci przegladarki i nie zaklada klasy na <html>:
	 * nikt nie chce, zeby strona zaczela do niego mowic sama, gdy nastepnego
	 * dnia wejdzie na inna podstrone.
	 *
	 * POZYCJA WYCHODZI Z SERWERA UKRYTA. Czy w ogole jest czym czytac, wie
	 * dopiero przegladarka - lista glosow to wlasnosc urzadzenia, nie strony.
	 * Bez glosu w jezyku strony przycisku nie pokazujemy wcale; czytanie
	 * polskiego tekstu angielskim glosem daje belkot, a nie udogodnienie.
	 */
	$moduly[] = array(
		'slug'      => 'odczyt',
		'nazwa'     => __( 'Read aloud', 'alyxa-a11y' ),
		'opis'      => __( 'Reads the main content with a voice installed on your own device. Reading stops when you leave the page.', 'alyxa-a11y' ),
		'typ'       => 'akcje',
		'akcje'     => array(
			array(
				'slug'  => 'czytaj',
				'nazwa' => __( 'Read the page', 'alyxa-a11y' ),
			),
			array(
				'slug'  => 'stop',
				'nazwa' => __( 'Stop reading', 'alyxa-a11y' ),
			),
		),
		'dane'      => array(
			/*
			 * Jezyk bierzemy z ustawien strony, a nie wpisujemy na sztywno:
			 * po nim skrypt szuka glosu. Skrypt woli to, co deklaruje sam
			 * dokument - na stronie wielojezycznej kazda podstrona ma swoj
			 * atrybut lang - a tej wartosci uzywa, gdy dokument milczy.
			 */
			'jezyk'      => get_bloginfo( 'language' ),

			/**
			 * Filtruje obszar strony podawany do odczytu.
			 *
			 * Pierwszy pasujacy element wygrywa. Czytamy tresc, a nie cala
			 * strone: menu, stopka i okruszki sa dla oka nawigacja, a dla
			 * ucha - kilkudziesiecioma sekundami, po ktorych nie wiadomo,
			 * o czym jest artykul. Ekran ustawien dostanie to pole w fazie 8.
			 *
			 * @since 0.7.0
			 *
			 * @param string $obszar Lista selektorow CSS oddzielona przecinkami.
			 */
			'obszar'     => (string) apply_filters( 'alyxa_obszar_odczytu', 'main, [role="main"], .site-main, #content, article' ),

			'pauza'      => __( 'Pause reading', 'alyxa-a11y' ),
			'wznow'      => __( 'Resume reading', 'alyxa-a11y' ),
			'trwa'       => __( 'Reading the page', 'alyxa-a11y' ),
			'wstrzymane' => __( 'Reading paused', 'alyxa-a11y' ),
			'pusto'      => __( 'There is no text to read on this page.', 'alyxa-a11y' ),
		),
		'warunkowy' => true,

		/*
		 * Jedyny modul rdzenia bez wlasnego arkusza. Nie zmienia strony
		 * ani o piksel - wyglad jego przyciskow to sprawa typu 'akcje',
		 * czyli panel.css, a nie tego konkretnego modulu.
		 */
		'css'       => '',
		'domyslnie' => true,
		'kolejnosc' => 80,
	);

	return $moduly;
}
add_filter( 'alyxa_moduly', 'alyxa_moduly_rdzenia' );
