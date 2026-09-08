<?php
/**
 * Katalog modulow rdzenia.
 *
 * Kazdy modul to jeden wpis w tej tablicy i jeden plik w moduly/. Nie ma
 * trzeciego miejsca: panel rysuje przelaczniki z rejestru, skrypt dostaje
 * z niego slug i typ, a arkusz powstaje ze sklejenia plikow wskazanych
 * kluczem 'css'. Kolejnosc zostawia luki co dziesiec, zeby moduly dopisane
 * w kolejnych fazach wchodzily na swoje miejsce bez przenumerowania reszty.
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

	return $moduly;
}
add_filter( 'alyxa_moduly', 'alyxa_moduly_rdzenia' );
