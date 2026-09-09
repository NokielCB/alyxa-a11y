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

		/* Pusty ciag znaczy "wez liste wbudowana" - patrz alyxa_obszar_odczytu. */
		'obszar' => '',
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
 * Wbudowana lista selektorow obszaru tresci.
 *
 * Kolejnosc jest kolejnoscia pierwszenstwa, a nie zbiorem: skrypt probuje
 * selektory PO KOLEI i bierze pierwszy, ktory cokolwiek znajdzie. Zaczynamy
 * od znacznika main, bo to jedyny z tej listy, ktory cos znaczy takze dla
 * czytnika ekranu; reszta to nazwy, ktore utarly sie w motywach.
 *
 * @return string
 */
function alyxa_obszar_domyslny() {
	return 'main, [role="main"], .site-main, #content, article';
}

/**
 * Obszar tresci, z ktorego czytamy strone na glos.
 *
 * @return string Lista selektorow CSS oddzielona przecinkami.
 */
function alyxa_obszar_odczytu() {
	$konfiguracja = alyxa_konfiguracja();

	return $konfiguracja['obszar'] ? $konfiguracja['obszar'] : alyxa_obszar_domyslny();
}

/**
 * Zestawy startowe modulow.
 *
 * PO CO, SKORO KAZDY MODUL MA SWOJ PRZELACZNIK. Bo dziesiec przelacznikow
 * postawionych przed kims, kto pierwszy raz slyszy o maskach czytania,
 * to nie jest wybor, tylko egzamin. Zestaw daje punkt wyjscia, ktory
 * mozna potem poprawic pojedynczym przelacznikiem.
 *
 * Zestaw 'placowka' to nasze wartosci domyslne, czyli to, co ma sens dla
 * strony urzedu albo szkoly; wpis null znaczy "zapytaj rejestru o klucz
 * domyslnie", zeby modul dolozony filtrem trafil tam, gdzie sam wskazal.
 *
 * @return array<string, array{nazwa: string, opis: string, moduly: array<int, string>|null}>
 */
function alyxa_zestawy() {
	return array(
		'minimalny' => array(
			'nazwa'  => __( 'Minimal', 'alyxa-a11y' ),
			'opis'   => __( 'Larger text, spacing and high contrast. The three that help the most people, and the three that cannot surprise anyone.', 'alyxa-a11y' ),
			'moduly' => array( 'tekst', 'odstepy', 'kontrast' ),
		),
		'placowka'  => array(
			'nazwa'  => __( 'Public institution', 'alyxa-a11y' ),
			'opis'   => __( 'Everything this plugin ships as standard. This is what a new installation starts with.', 'alyxa-a11y' ),
			'moduly' => null,
		),
		'pelny'     => array(
			'nazwa'  => __( 'Everything', 'alyxa-a11y' ),
			'opis'   => __( 'Every module in the register, including any added by your theme or another plugin.', 'alyxa-a11y' ),
			'moduly' => array_keys( alyxa_rejestr() ),
		),
	);
}

/**
 * Rozpisuje zestaw startowy na wybor modul po module.
 *
 * Wynik jest ZAWSZE pelna lista rejestru - takze z wpisami falszywymi.
 * Zestaw ma ustawiac stan wszystkiego, co strona zna, a nie tylko wlaczac
 * swoje pozycje: inaczej "minimalny" zostawialby wlaczone to, co ktos
 * wlaczyl przed jego wybraniem, i nie bylby minimalny.
 *
 * @param string $slug Slug zestawu.
 * @return array<string, bool>|null Null, gdy zestawu nie ma.
 */
function alyxa_zestaw_na_moduly( $slug ) {
	$zestawy = alyxa_zestawy();

	if ( ! isset( $zestawy[ $slug ] ) ) {
		return null;
	}

	$lista  = $zestawy[ $slug ]['moduly'];
	$moduly = array();

	foreach ( alyxa_rejestr() as $modul_slug => $modul ) {
		$moduly[ $modul_slug ] = null === $lista ? $modul['domyslnie'] : in_array( $modul_slug, $lista, true );
	}

	return $moduly;
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

	if ( isset( $wejscie['obszar'] ) ) {
		$czyste['obszar'] = alyxa_oczysc_selektory( (string) $wejscie['obszar'] );
	}

	return $czyste;
}

/**
 * Sprowadza liste selektorow CSS do postaci, ktora mozna wpisac do dokumentu.
 *
 * CZEGO NIE SPRAWDZAMY: czy selektor jest skladniowo poprawny. Serwer tego
 * nie wie - poprawnosc selektora ocenia silnik przegladarki, a nasz skrypt
 * probuje kazdy osobno w bloku try, wiec bledny po prostu nic nie znajduje
 * i przepuszcza kolejny. Tutaj chodzi o co innego: ta wartosc jedzie do
 * dokumentu razem z ustawieniami dla skryptu, wiec nie moze wyniesc ze soba
 * niczego, co konczy atrybut albo otwiera znacznik.
 *
 * Wpuszczamy wiec dokladnie ten zestaw znakow, ktorego wymaga selektor -
 * z klamrami, srednikiem i nawiasami katowymi wlacznie nie ma tu nic
 * do roboty.
 *
 * @param string $wejscie Lista selektorow oddzielona przecinkami.
 * @return string
 */
function alyxa_oczysc_selektory( $wejscie ) {
	$czysty = preg_replace( '/[^a-zA-Z0-9\-_ .,#\[\]=\"\':()^$*~|+>]/', '', $wejscie );
	$czysty = preg_replace( '/\s+/', ' ', (string) $czysty );

	return trim( (string) $czysty, " ,\t\n" );
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

	/*
	 * Przebudowa arkusza wisi na zapisie opcji, a nie stoi tutaj. Roznica
	 * jest widoczna dopiero wtedy, gdy opcje zapisze kto inny - WP-CLI,
	 * skrypt wdrozeniowy, import ustawien - a wtedy sklejony arkusz zostalby
	 * z poprzedniej listy modulow i strona pokazalaby przelaczniki bez regul
	 * albo reguly bez przelacznikow.
	 */
	return $czyste;
}

/**
 * Odswieza pamiec i arkusz po kazdym zapisie konfiguracji.
 *
 * Pamiec statyczna trzymalaby w tym samym zadaniu konfiguracje sprzed zmiany,
 * a arkusz zbudowalby sie ze starej listy modulow.
 *
 * @return void
 */
function alyxa_po_zapisie_konfiguracji() {
	alyxa_konfiguracja( true );
	alyxa_zbuduj_css();
}
add_action( 'update_option_' . ALYXA_A11Y_OPCJA, 'alyxa_po_zapisie_konfiguracji' );
add_action( 'add_option_' . ALYXA_A11Y_OPCJA, 'alyxa_po_zapisie_konfiguracji' );

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
