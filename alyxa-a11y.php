<?php
/**
 * Plugin Name:       Alyxa Accessibility
 * Description:       Accessibility panel built on the active theme's own design tokens. Every feature is a module that can be switched off site by site, and a module that is off leaves no button, no CSS rule and no listener behind.
 * Version:           1.4.0
 * Requires at least: 6.5
 * Requires PHP:      8.1
 * Author:            Jakub Nokielski
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       alyxa-a11y
 * Domain Path:       /languages
 *
 * @package alyxa-a11y
 */

/*
 * DLACZEGO NAPISY SA PO ANGIELSKU, A KOMENTARZE PO POLSKU.
 * Wtyczka ma trafic do repozytorium wordpress.org, a tamtejszy system
 * tlumaczen zaklada jezyk zrodlowy angielski - polski wchodzi katalogiem
 * w languages/ i przez translate.wordpress.org. Komentarze zostaja w jezyku
 * projektu, bo tlumaczen kodu nikt nie czyta, a recenzent ich nie ocenia.
 *
 * DLACZEGO OSOBNA WTYCZKA, A NIE CZESC MOTYWU ANI ZPO CORE.
 * Panel ma pojechac na inne strony, ktore nie maja ani tego motywu, ani
 * danych placowki. Zaleznosci: zadne. Bez jQuery, bez npm, bez kroku
 * budowania - tak samo jak reszta tego projektu.
 */

defined( 'ABSPATH' ) || exit;

define( 'ALYXA_A11Y_WERSJA', '1.4.0' );
define( 'ALYXA_A11Y_PLIK', __FILE__ );
define( 'ALYXA_A11Y_KATALOG', plugin_dir_path( __FILE__ ) );
define( 'ALYXA_A11Y_URL', plugin_dir_url( __FILE__ ) );

require_once ALYXA_A11Y_KATALOG . 'inc/rejestr.php';
require_once ALYXA_A11Y_KATALOG . 'inc/moduly.php';
require_once ALYXA_A11Y_KATALOG . 'inc/konfiguracja.php';
require_once ALYXA_A11Y_KATALOG . 'inc/katalog.php';
require_once ALYXA_A11Y_KATALOG . 'inc/zasoby.php';
require_once ALYXA_A11Y_KATALOG . 'inc/ikony.php';
require_once ALYXA_A11Y_KATALOG . 'inc/panel.php';

/*
 * Ekran ustawien tylko w kokpicie. Na odslonie dla odwiedzajacego ten plik
 * nie ma nic do roboty, a kazdy niepotrzebnie wczytany plik to czas
 * odpowiedzi liczony dla kazdego wejscia na strone.
 */
if ( is_admin() ) {
	require_once ALYXA_A11Y_KATALOG . 'inc/ustawienia.php';
}

/**
 * Tlumaczenia wtyczki.
 *
 * WordPress od 6.7 sam znajduje katalog languages/ po naglowku Domain Path,
 * ale wywolanie zostaje: instalacje na starszym rdzeniu tez maja dostac
 * polski interfejs, a koszt to jedna funkcja na init.
 *
 * @return void
 */
function alyxa_wczytaj_tlumaczenia() {
	load_plugin_textdomain( 'alyxa-a11y', false, dirname( plugin_basename( ALYXA_A11Y_PLIK ) ) . '/languages' );
}
add_action( 'init', 'alyxa_wczytaj_tlumaczenia' );

/**
 * Aktywacja.
 *
 * Zapisujemy konfiguracje domyslna i budujemy arkusz modulow od razu,
 * zeby pierwsze wejscie na strone nie trafilo na brak pliku i nie musialo
 * go generowac w trakcie odpowiedzi dla odwiedzajacego.
 *
 * @return void
 */
function alyxa_aktywacja() {
	if ( false === get_option( ALYXA_A11Y_OPCJA, false ) ) {
		/* Arkusz zbuduje sie sam - przebudowa wisi na zapisie tej opcji. */
		add_option( ALYXA_A11Y_OPCJA, alyxa_konfiguracja_domyslna() );

		return;
	}

	/* Ponowna aktywacja: opcja juz jest, wiec nikt arkusza nie zbuduje. */
	alyxa_zbuduj_css();
}
register_activation_hook( ALYXA_A11Y_PLIK, 'alyxa_aktywacja' );

/**
 * Deaktywacja.
 *
 * Sprzatamy wygenerowany arkusz, bo jest odtwarzalny z konfiguracji.
 * Samej konfiguracji nie ruszamy - wylaczenie wtyczki na czas diagnozy
 * nie moze kasowac ustawien redaktora. Od tego jest odinstalowanie.
 *
 * @return void
 */
function alyxa_deaktywacja() {
	alyxa_usun_zbudowany_css();
}
register_deactivation_hook( ALYXA_A11Y_PLIK, 'alyxa_deaktywacja' );
