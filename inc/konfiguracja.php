<?php
/**
 * Konfiguracja wtyczki.
 *
 * To jest ustawienie STRONY, nie odwiedzajacego. Tu zapada decyzja, ktore
 * moduly w ogole istnieja na tej witrynie i jak wyglada przycisk. Wybor
 * odwiedzajacego - wlaczony kontrast, powiekszony tekst - zyje w pamieci
 * jego przegladarki i nigdy nie trafia na serwer. Rozdzial jest celowy:
 * dzieki niemu wtyczka nie przetwarza zadnych danych osobowych i nie
 * wymaga wpisu w polityce prywatnosci ani zgody na ciasteczka.
 *
 * @package alyxa-a11y
 */

defined( 'ABSPATH' ) || exit;

/**
 * Nazwa opcji z konfiguracja strony.
 */
const ALYXA_A11Y_OPCJA = 'alyxa_a11y_konfiguracja';

/**
 * Nazwa opcji ze stanem zbudowanego arkusza modulow.
 */
const ALYXA_A11Y_OPCJA_CSS = 'alyxa_a11y_arkusz';

/**
 * Klucz w localStorage przegladarki odwiedzajacego.
 *
 * Ta sama stala jest przekazywana do skryptu w naglowku i do panel.js.
 * Zmiana wartosci kasuje ustawienia wszystkim odwiedzajacym, wiec nie
 * zmieniamy jej bez powodu.
 */
const ALYXA_A11Y_KLUCZ = 'alyxa-a11y';

/**
 * Konfiguracja domyslna.
 *
 * @return array<string, mixed>
 */
function alyxa_konfiguracja_domyslna() {
	return array(
		'moduly' => array(),
		'rog'    => 'lewy-dol',
		'odstep' => 16,
		'akcent' => '',
		'wersja' => ALYXA_A11Y_WERSJA,
	);
}

/**
 * Dopuszczalne rogi ekranu.
 *
 * DLACZEGO DOMYSLNIE LEWY DOLNY. Prawy dolny rog jest w praktyce zajety:
 * tam siedza przyciski powrotu na gore, czaty i banery zgod. W tym projekcie
 * konkretnie .zpo-do-gory z motywu. Dwa okragle przyciski jeden na drugim to
 * nie jest problem estetyczny, tylko dwa cele dotykowe, ktore sie zjadaja.
 *
 * @return array<string, string> Slug rogu => etykieta dla ekranu ustawien.
 */
function alyxa_rogi() {
	return array(
		'lewy-dol'   => __( 'Bottom left', 'alyxa-a11y' ),
		'prawy-dol'  => __( 'Bottom right', 'alyxa-a11y' ),
		'lewy-gora'  => __( 'Top left', 'alyxa-a11y' ),
		'prawy-gora' => __( 'Top right', 'alyxa-a11y' ),
	);
}

/**
 * Zwraca konfiguracje strony, uzupelniona o wartosci domyslne.
 *
 * @param bool $odswiez Wymusza ponowny odczyt z bazy. Uzywane po zapisie,
 *                      bo w tym samym zadaniu pamiec statyczna trzymalaby
 *                      jeszcze konfiguracje sprzed zmiany - i arkusz modulow
 *                      zbudowalby sie ze starej listy.
 * @return array<string, mixed>
 */
function alyxa_konfiguracja( $odswiez = false ) {
	static $konfiguracja = null;

	if ( null !== $konfiguracja && ! $odswiez ) {
		return $konfiguracja;
	}

	$zapisana = get_option( ALYXA_A11Y_OPCJA, array() );

	if ( ! is_array( $zapisana ) ) {
		$zapisana = array();
	}

	$konfiguracja = alyxa_oczysc_konfiguracje( wp_parse_args( $zapisana, alyxa_konfiguracja_domyslna() ) );

	return $konfiguracja;
}

/**
 * Sprowadza konfiguracje do wartosci, ktorym mozna zaufac.
 *
 * Wywolywana i przy odczycie, i przy zapisie. Przy odczycie, bo opcja
 * moglaby pochodzic ze starszej wersji wtyczki albo z recznej edycji bazy;
 * przy zapisie, bo tak wymaga recenzja wordpress.org i tak jest bezpiecznie.
 *
 * @param array<string, mixed> $wejscie Surowa konfiguracja.
 * @return array<string, mixed>
 */
function alyxa_oczysc_konfiguracje( array $wejscie ) {
	$czyste = alyxa_konfiguracja_domyslna();

	if ( isset( $wejscie['moduly'] ) && is_array( $wejscie['moduly'] ) ) {
		foreach ( $wejscie['moduly'] as $slug => $czy ) {
			$slug = sanitize_key( (string) $slug );

			if ( '' === $slug || ! preg_match( '/^[a-z0-9-]+$/', $slug ) ) {
				continue;
			}

			$czyste['moduly'][ $slug ] = (bool) $czy;
		}
	}

	if ( isset( $wejscie['rog'] ) && array_key_exists( $wejscie['rog'], alyxa_rogi() ) ) {
		$czyste['rog'] = $wejscie['rog'];
	}

	if ( isset( $wejscie['odstep'] ) ) {
		/* Ponizej 8 px przycisk kleil sie do krawedzi, powyzej 64 wisial w powietrzu. */
		$czyste['odstep'] = max( 8, min( 64, (int) $wejscie['odstep'] ) );
	}

	if ( isset( $wejscie['akcent'] ) ) {
		/* Pusty ciag znaczy "wez kolor z motywu" i jest wartoscia poprawna. */
		$czyste['akcent'] = (string) sanitize_hex_color( (string) $wejscie['akcent'] );
	}

	return $czyste;
}

/**
 * Zapisuje konfiguracje i przebudowuje arkusz modulow.
 *
 * Jedyna droga do zmiany ustawien. Ekran w kokpicie (faza 8) i ewentualne
 * skrypty wdrozeniowe maja wolac wlasnie to, zeby nie dalo sie zapisac
 * konfiguracji bez odswiezenia sklejonego CSS.
 *
 * @param array<string, mixed> $wejscie Nowa konfiguracja.
 * @return array<string, mixed> Konfiguracja po oczyszczeniu.
 */
function alyxa_zapisz_konfiguracje( array $wejscie ) {
	$czyste = alyxa_oczysc_konfiguracje( $wejscie );

	update_option( ALYXA_A11Y_OPCJA, $czyste );

	alyxa_konfiguracja( true );
	alyxa_zbuduj_css();

	return $czyste;
}

/**
 * Skrot konfiguracji, uzywany jako nazwa i wersja zbudowanego arkusza.
 *
 * Liczy sie z listy wlaczonych modulow, czasow modyfikacji ich plikow CSS
 * i wersji wtyczki. Kazda z tych rzeczy zmienia tresc arkusza, wiec kazda
 * musi zmieniac jego adres - inaczej odwiedzajacy dostanie z pamieci
 * podrecznej arkusz sprzed zmiany ustawien.
 *
 * @return string Dwanascie znakow szesnastkowych.
 */
function alyxa_skrot_konfiguracji() {
	$sklad = array( ALYXA_A11Y_WERSJA );

	foreach ( alyxa_moduly_wlaczone() as $slug => $modul ) {
		$czas = ( $modul['css'] && file_exists( $modul['css'] ) ) ? filemtime( $modul['css'] ) : 0;

		$sklad[] = $slug . ':' . $czas;
	}

	return substr( md5( implode( '|', $sklad ) ), 0, 12 );
}
