<?php
/**
 * Rejestr modulow.
 *
 * CALA WTYCZKA STOI NA TYM PLIKU. Panel nie wie, co potrafi - pyta rejestr
 * i rysuje tyle przelacznikow, ile dostal. Arkusz stylow nie jest pisany
 * recznie, tylko sklejany z plikow, ktore zadeklarowaly moduly. Skrypt nie
 * ma zaszytych nazw funkcji, tylko dostaje z serwera liste slugow i typow.
 * Dodanie modulu w kolejnych fazach to wpis w tej tablicy plus jeden plik
 * CSS - nie ma miejsca, w ktorym trzeba by dopisac go po raz drugi.
 *
 * FAZA 1 NIE REJESTRUJE ANI JEDNEGO MODULU. Tak ma byc: chcemy najpierw
 * ocenic dostepnosc samego panelu, zanim zacznie cokolwiek zmieniac na
 * stronie. Pusta tablica ponizej to nie zaslepka do usuniecia, tylko punkt,
 * w ktorym faza 2 dopisze pierwsze pozycje.
 *
 * @package alyxa-a11y
 */

defined( 'ABSPATH' ) || exit;

/**
 * Zwraca rejestr modulow.
 *
 * Ksztalt pojedynczego wpisu:
 *
 *     'slug'      (string)  wymagany, znaki a-z 0-9 i myslnik; z niego powstaje
 *                           klasa na <html> oraz identyfikator w localStorage
 *     'nazwa'     (string)  wymagana, przetlumaczona etykieta przelacznika
 *     'opis'      (string)  zdanie pod etykieta, opcjonalne
 *     'typ'       (string)  'przelacznik' (wlacz/wylacz), 'stopnie' (0..n)
 *                           albo 'akcje' (przyciski robiace cos tu i teraz)
 *     'stopnie'   (int)     liczba stopni dla typu 'stopnie', domyslnie 3
 *     'etykiety'  (array)   nazwy stopni dla czytnika ekranu, indeks 0 = wylaczony
 *     'akcje'     (array)   dla typu 'akcje': lista par slug + nazwa przycisku
 *     'dane'      (array)   dowolne wartosci dla zachowania modulu w skrypcie;
 *                           rdzen ich nie czyta, tylko podaje dalej
 *     'warunkowy' (bool)    pozycja wychodzi z serwera ukryta i pokazuje ja
 *                           dopiero zachowanie, gdy urzadzenie to potrafi
 *     'css'       (string)  bezwzgledna sciezka do arkusza modulu, opcjonalna
 *     'domyslnie' (bool)    czy modul jest wlaczony na nowej instalacji
 *     'kolejnosc' (int)     pozycja na liscie, mniejsza liczba wyzej
 *
 * KLASY NA <html> POWSTAJA Z SLUGA, NIE Z OSOBNEGO POLA. Modul 'kontrast'
 * o typie przelacznik daje klase alyxa-kontrast, modul 'tekst' o typie
 * stopnie daje alyxa-tekst-1, alyxa-tekst-2, alyxa-tekst-3. Dzieki temu
 * skrypt w naglowku, ktory ustawia klasy przed pierwszym rysowaniem strony,
 * nie musi znac zadnej mapy - wystarcza mu klucze z pamieci przegladarki.
 *
 * TYP 'akcje' NIE MA STANU I DLATEGO NIE MA KLASY. Odczyt strony nie jest
 * ustawieniem, ktore ma przetrwac przejscie na nastepna podstrone - jest
 * czynnoscia, ktora sie zaczyna i konczy. Taki modul nie zapisuje niczego
 * w pamieci przegladarki i nie zaklada niczego na <html>; cala jego praca
 * dzieje sie w zachowaniu po stronie skryptu.
 *
 * @return array<string, array<string, mixed>> Moduly indeksowane slugiem.
 */
function alyxa_rejestr() {
	static $rejestr = null;

	if ( null !== $rejestr ) {
		return $rejestr;
	}

	/**
	 * Filtruje liste modulow panelu.
	 *
	 * Punkt wejscia dla obcej strony, ktora chce dolozyc wlasny przelacznik
	 * bez forkowania wtyczki. Wpis niekompletny jest pomijany, a nie powoduje
	 * bledu krytycznego - blad w cudzym kodzie nie ma polozyc strony.
	 *
	 * @since 0.1.0
	 *
	 * @param array<int, array<string, mixed>> $moduly Lista definicji modulow.
	 */
	$surowe = apply_filters( 'alyxa_moduly', array() );

	$rejestr = array();

	if ( ! is_array( $surowe ) ) {
		return $rejestr;
	}

	foreach ( $surowe as $modul ) {
		$modul = alyxa_sprawdz_modul( $modul );

		if ( null === $modul ) {
			continue;
		}

		$rejestr[ $modul['slug'] ] = $modul;
	}

	uasort(
		$rejestr,
		static function ( $a, $b ) {
			return $a['kolejnosc'] <=> $b['kolejnosc'];
		}
	);

	return $rejestr;
}

/**
 * Uzupelnia i sprawdza pojedyncza definicje modulu.
 *
 * @param mixed $modul Definicja z rejestru albo z filtra.
 * @return array<string, mixed>|null Uzupelniona definicja albo null, gdy odrzucona.
 */
function alyxa_sprawdz_modul( $modul ) {
	if ( ! is_array( $modul ) || empty( $modul['slug'] ) || empty( $modul['nazwa'] ) ) {
		_doing_it_wrong(
			__FUNCTION__,
			'Modul panelu Alyxa musi miec klucze slug i nazwa.',
			'0.1.0'
		);

		return null;
	}

	$slug = sanitize_key( $modul['slug'] );

	/*
	 * Slug wchodzi do nazwy klasy CSS i do klucza w localStorage, wiec
	 * sanitize_key nie wystarcza - dopuszcza podkreslenia, ktorych nie
	 * chcemy w klasach. Wzorzec trzymamy tu, w jednym miejscu, bo ten sam
	 * warunek powtarza sie po stronie skryptu.
	 */
	if ( '' === $slug || ! preg_match( '/^[a-z0-9-]+$/', $slug ) ) {
		_doing_it_wrong(
			__FUNCTION__,
			'Slug modulu Alyxa moze zawierac tylko male litery, cyfry i myslnik.',
			'0.1.0'
		);

		return null;
	}

	$modul = wp_parse_args(
		$modul,
		array(
			'opis'      => '',
			'typ'       => 'przelacznik',
			'stopnie'   => 3,
			'etykiety'  => array(),
			'akcje'     => array(),
			'dane'      => array(),
			'warunkowy' => false,
			'css'       => '',
			'domyslnie' => false,
			'kolejnosc' => 10,
		)
	);

	$modul['slug']      = $slug;
	$modul['typ']       = in_array( $modul['typ'], array( 'stopnie', 'akcje' ), true ) ? $modul['typ'] : 'przelacznik';
	$modul['stopnie']   = 'stopnie' === $modul['typ'] ? max( 1, (int) $modul['stopnie'] ) : 0;
	$modul['akcje']     = 'akcje' === $modul['typ'] ? alyxa_sprawdz_akcje( $modul['akcje'] ) : array();
	$modul['dane']      = is_array( $modul['dane'] ) ? $modul['dane'] : array();
	$modul['warunkowy'] = (bool) $modul['warunkowy'];
	$modul['domyslnie'] = (bool) $modul['domyslnie'];
	$modul['kolejnosc'] = (int) $modul['kolejnosc'];

	/*
	 * Modul akcji bez ani jednej poprawnej akcji nie ma czym dzialac -
	 * wyszlaby z tego pozycja z nazwa i bez zadnego przycisku. Odrzucamy
	 * go tak samo jak modul bez sluga, zamiast rysowac atrape.
	 */
	if ( 'akcje' === $modul['typ'] && ! $modul['akcje'] ) {
		_doing_it_wrong(
			__FUNCTION__,
			'Modul panelu Alyxa o typie akcje musi miec co najmniej jedna akcje.',
			'0.7.0'
		);

		return null;
	}

	return $modul;
}

/**
 * Sprawdza liste akcji modulu.
 *
 * Slug akcji trafia do atrybutu data i do wywolania zachowania, wiec
 * przepuszczamy dokladnie ten sam zestaw znakow co przy slugu modulu.
 * Wpis niekompletny wypada z listy, a nie klada pozostalych.
 *
 * @param mixed $akcje Lista definicji akcji.
 * @return array<int, array{slug: string, nazwa: string}>
 */
function alyxa_sprawdz_akcje( $akcje ) {
	$czyste = array();

	if ( ! is_array( $akcje ) ) {
		return $czyste;
	}

	foreach ( $akcje as $akcja ) {
		if ( ! is_array( $akcja ) || empty( $akcja['slug'] ) || empty( $akcja['nazwa'] ) ) {
			continue;
		}

		$slug = sanitize_key( $akcja['slug'] );

		if ( '' === $slug || ! preg_match( '/^[a-z0-9-]+$/', $slug ) ) {
			continue;
		}

		$czyste[] = array(
			'slug'  => $slug,
			'nazwa' => (string) $akcja['nazwa'],
		);
	}

	return $czyste;
}

/**
 * Zwraca pojedynczy modul albo null.
 *
 * @param string $slug Slug modulu.
 * @return array<string, mixed>|null
 */
function alyxa_modul( $slug ) {
	$rejestr = alyxa_rejestr();

	return $rejestr[ $slug ] ?? null;
}

/**
 * Zwraca moduly wlaczone w konfiguracji tej strony.
 *
 * MODUL WYLACZONY NIE ZOSTAWIA SLADU. Nie ma go w tej tablicy, wiec nie ma
 * jego przelacznika w panelu, jego CSS nie trafia do sklejanego arkusza,
 * a skrypt nie dostaje jego sluga i nie zna klasy, ktora mialby ustawiac.
 * To nie jest ukrywanie - tego kodu po prostu nie ma na stronie.
 *
 * @return array<string, array<string, mixed>>
 */
function alyxa_moduly_wlaczone() {
	$konfiguracja = alyxa_konfiguracja();
	$wybor        = $konfiguracja['moduly'];
	$wlaczone     = array();

	foreach ( alyxa_rejestr() as $slug => $modul ) {
		$czy = array_key_exists( $slug, $wybor ) ? (bool) $wybor[ $slug ] : $modul['domyslnie'];

		if ( $czy ) {
			$wlaczone[ $slug ] = $modul;
		}
	}

	return $wlaczone;
}
