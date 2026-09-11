<?php
/**
 * Katalog opcjonalny - moduly, ktore stoja w kodzie i sa domyslnie wylaczone.
 *
 * DLACZEGO W OGOLE ISTNIEJA, SKORO U NAS NIE SA WLACZONE. Bo wtyczka jedzie
 * na wordpress.org, a druga szkola ma inna strone niz nasza: z galeriami,
 * z nagraniami z akademii, z motywem bez ciemnego wariantu. Kryterium
 * wejscia do katalogu jest z planu i jest twarde: modul wchodzi, jesli robi
 * cos, czego przegladarka i system nie robia lepiej; nie potrafi rozwalic
 * ukladu strony gospodarza; da sie go zweryfikowac klawiatura i czytnikiem
 * ekranu.
 *
 * DOMYSLNIE WYLACZONE ZNACZY NIEOBECNE, a nie ukryte. Instalacja, ktora
 * niczego nie ustawia, dostaje dziesiec modulow rdzenia i ani jednej reguly
 * CSS z tego pliku - te dziewiec pozycji istnieje wtedy wylacznie jako lista
 * pol wyboru na ekranie ustawien.
 *
 * OSOBNY PLIK, A NIE DOPISEK DO moduly.php. Rdzen i katalog rzadza sie
 * innymi prawami: rdzen jest uzgodniony z klientem i wlaczony, katalog jest
 * propozycja. Filtr wisi na priorytecie 20, wiec rdzen wchodzi do rejestru
 * pierwszy; o kolejnosci na liscie i tak decyduje klucz 'kolejnosc'.
 *
 * @package alyxa-a11y
 */

defined( 'ABSPATH' ) || exit;

/**
 * Dopisuje moduly katalogu do rejestru.
 *
 * @param array<int, array<string, mixed>> $moduly Lista definicji.
 * @return array<int, array<string, mixed>>
 */
function alyxa_moduly_katalogu( $moduly ) {
	$moduly[] = array(
		'slug'      => 'wyrownanie',
		'grupa'     => 'tekst',
		'ikona'     => 'dolewej',
		'nazwa'     => __( 'Text alignment', 'alyxa-a11y' ),
		'opis'      => __( 'Three settings, one after another: every line starting in the same place, every line centred, every line ending in the same place. Justified text stops stretching the spaces between words and no word is broken at the end of a line.', 'alyxa-a11y' ),
		'typ'       => 'stopnie',
		'stopnie'   => 3,

		/* Trzy rownorzedne ustawienia, a nie skala - plus z ostatniego wraca
		   na zero, zamiast stac w miejscu. */
		'obieg'     => true,

		/*
		 * Etykiety mowia o poczatku i koncu wiersza, a nie o lewej i prawej.
		 * Na stronie pisanej od prawej "do lewej" znaczylo by "na koniec",
		 * a arkusz i tak uzywa wlasciwosci logicznych - etykieta ma mowic
		 * to samo, co robi kod.
		 */
		'etykiety'  => array(
			__( 'As the page has it', 'alyxa-a11y' ),
			__( 'Line start', 'alyxa-a11y' ),
			__( 'Centred', 'alyxa-a11y' ),
			__( 'Line end', 'alyxa-a11y' ),
		),

		/*
		 * Rysunek osobno dla kazdego stopnia, bo kafelek pokazuje naraz
		 * jeden stopien i to on odpowiada za pytanie "co jest teraz
		 * ustawione". Indeks 0 zostaje pusty - stan wyjsciowy bierze
		 * rysunek z klucza 'ikona'.
		 */
		'ikony'     => array( '', 'dolewej', 'dosrodka', 'doprawej' ),
		'dane'      => array( 'klawisz' => 'l' ),
		'css'       => ALYXA_A11Y_KATALOG . 'moduly/wyrownanie.css',
		'domyslnie' => false,
		'kolejnosc' => 22,
	);

	$moduly[] = array(
		'slug'      => 'grubosc',
		'grupa'     => 'tekst',
		'ikona'     => 'grubosc',
		'nazwa'     => __( 'Bolder text', 'alyxa-a11y' ),
		'opis'      => __( 'Thickens ordinary text. Headings and words already in bold are left alone, because a fixed weight could make them lighter than they are.', 'alyxa-a11y' ),
		'typ'       => 'przelacznik',
		'dane'      => array( 'klawisz' => 'b' ),
		'css'       => ALYXA_A11Y_KATALOG . 'moduly/grubosc.css',
		'domyslnie' => false,
		'kolejnosc' => 24,
	);

	/*
	 * CIEMNY TRYB NIE JEST WARIANTEM MOTYWU I NIE UDAJE, ZE JEST.
	 * Plan zakladal, ze modul siegnie po ciemny wariant motywu i schowa sie,
	 * gdy motyw zadnego nie ma. Odpadlo to przy pierwszym pytaniu: wariant
	 * stylu w motywie blokowym wybiera administrator dla calej witryny,
	 * a nie odwiedzajacy dla siebie - nie ma czego podac pojedynczej osobie,
	 * bo wariant jest ustawieniem strony, nie przelacznikiem w przegladarce.
	 * Malujemy wiec wlasna ciemna palete, tym samym mlotem co wysoki kontrast
	 * i z ta sama furtka: motyw, ktory zrobi to lepiej, podmienia plik CSS
	 * tego modulu jednym filtrem (przyklad stoi w moduly/kontrast.css).
	 */
	$moduly[] = array(
		'slug'      => 'ciemny',
		'grupa'     => 'kolor',
		'ikona'     => 'ksiezyc',
		'nazwa'     => __( 'Dark mode', 'alyxa-a11y' ),
		'opis'      => __( 'A dark page with light text, for reading in the evening or with light sensitivity. High contrast wins over it when both are on.', 'alyxa-a11y' ),
		'typ'       => 'przelacznik',
		'dane'      => array( 'klawisz' => 'd' ),
		'css'       => ALYXA_A11Y_KATALOG . 'moduly/ciemny.css',
		'domyslnie' => false,
		'kolejnosc' => 35,
	);

	/*
	 * ODBARWIANIE MA WLASNA PULAPKE, ZLAPANA JESZCZE W FAZIE 1: element
	 * z filtrem innym niz none staje sie blokiem zawierajacym dla wszystkich
	 * potomkow position: fixed. Filtr na body zabralby wiec przyciskowi
	 * panelu przyklejenie do okna - odwiedzajacy wlaczylby odbarwienie
	 * i stracil kontrolke, ktora sie je wylacza. Arkusz tego modulu naklada
	 * filtr na dzieci body Z POMINIECIEM naszych; calosc opisana
	 * w moduly/nasycenie.css.
	 */
	$moduly[] = array(
		'slug'      => 'nasycenie',
		'grupa'     => 'kolor',
		'ikona'     => 'kropla',
		'nazwa'     => __( 'Fewer colours', 'alyxa-a11y' ),
		'opis'      => __( 'Weakens the colours of the page, down to grey. A warning: two colours that differ only in hue become the same shade, so anything told by colour alone stops being visible.', 'alyxa-a11y' ),
		'typ'       => 'stopnie',
		'stopnie'   => 2,

		/* Procent nasycenia, tak jak procent wielkosci przy powiekszaniu. */
		'etykiety'  => array( '100%', '50%', '0%' ),
		'dane'      => array( 'klawisz' => 'g' ),
		'css'       => ALYXA_A11Y_KATALOG . 'moduly/nasycenie.css',
		'domyslnie' => false,
		'kolejnosc' => 37,
	);

	/*
	 * KAFELEK WARUNKOWY: na stronie bez ani jednego obrazu przelacznik
	 * "ukryj obrazy" jest przyciskiem, ktory nic nie robi. Zachowanie modulu
	 * pyta dokument, czy jest tu co chowac, i dopiero wtedy pokazuje kafelek.
	 */
	$moduly[] = array(
		'slug'      => 'obrazy',
		'grupa'     => 'spokoj',
		'ikona'     => 'obraz',
		'nazwa'     => __( 'Hide pictures', 'alyxa-a11y' ),
		'opis'      => __( 'Photographs and graphics become invisible, leaving their space and their description behind. A warning: a timetable or a notice published as a picture disappears with them.', 'alyxa-a11y' ),
		'typ'       => 'przelacznik',
		'dane'      => array( 'klawisz' => 'i' ),
		'warunkowy' => true,
		'css'       => ALYXA_A11Y_KATALOG . 'moduly/obrazy.css',
		'domyslnie' => false,
		'kolejnosc' => 52,
	);

	$moduly[] = array(
		'slug'      => 'dzwieki',
		'grupa'     => 'spokoj',
		'ikona'     => 'cisza',
		'nazwa'     => __( 'Mute sound', 'alyxa-a11y' ),
		'opis'      => __( 'Silences recordings on this page, including the ones that start playing later. A player embedded from another site cannot be silenced from here.', 'alyxa-a11y' ),
		'typ'       => 'przelacznik',
		'dane'      => array( 'klawisz' => 'q' ),
		'warunkowy' => true,

		/*
		 * Bez arkusza: wyciszenie jest wlasciwoscia odtwarzacza, a nie
		 * wygladem. Drugi taki modul po odczycie strony.
		 */
		'css'       => '',
		'domyslnie' => false,
		'kolejnosc' => 54,
	);

	$moduly[] = array(
		'slug'      => 'linia',
		'grupa'     => 'wskaznik',
		'ikona'     => 'linijka',
		'nazwa'     => __( 'Reading line', 'alyxa-a11y' ),
		'opis'      => __( 'A line that follows the pointer and the keyboard focus, so the eye keeps its place in a long text. Lighter than the reading mask, which dims everything else.', 'alyxa-a11y' ),
		'typ'       => 'przelacznik',
		'dane'      => array( 'klawisz' => 'n' ),
		'css'       => ALYXA_A11Y_KATALOG . 'moduly/linia.css',
		'domyslnie' => false,
		'kolejnosc' => 72,
	);

	/*
	 * MODUL, KTORY OBSLUGUJE INNE MODULY - jedyny taki we wtyczce. Litery
	 * bierze z klucza 'klawisz' w tablicy 'dane' kazdego modulu, wiec sam
	 * nie zna ani jednego sluga; modul dolozony filtrem deklaruje swoja
	 * litere tak samo jak nasze.
	 *
	 * WLASNEJ LITERY NIE MA I MIEC NIE MOZE: zeby ja nacisnac, trzeba by
	 * miec juz wlaczone skroty.
	 */
	$moduly[] = array(
		'slug'      => 'skroty',
		'grupa'     => 'pomoc',
		'ikona'     => 'klawiatura',
		'nazwa'     => __( 'Keyboard shortcuts', 'alyxa-a11y' ),
		'opis'      => __( 'Switches the other modules with Alt+Shift and the letter shown in the corner of each tile. Alt+Shift+A opens and closes this panel. Shortcuts are ignored while you are typing in a field.', 'alyxa-a11y' ),
		'typ'       => 'przelacznik',
		'dane'      => array(

			/*
			 * Litera panelu jest wartoscia rejestru, a nie stala w skrypcie:
			 * na stronie, ktora ma juz wlasny skrot pod Alt+Shift+A,
			 * podmienia sie ja filtrem razem z reszta rejestru.
			 */
			'panel'     => 'a',

			/* Napisy zapowiedzi - skrypt nie ma skad wziac ich sam. */
			/* translators: %s: name of the module that was switched on. */
			'wlaczono'  => __( '%s: on', 'alyxa-a11y' ),
			/* translators: %s: name of the module that was switched off. */
			'wylaczono' => __( '%s: off', 'alyxa-a11y' ),
			/* translators: 1: name of the module, 2: the level it was set to, for example 130%. */
			'stan'      => __( '%1$s: %2$s', 'alyxa-a11y' ),
		),
		'css'       => ALYXA_A11Y_KATALOG . 'moduly/skroty.css',
		'domyslnie' => false,
		'kolejnosc' => 90,
	);

	/*
	 * ODNOSNIK, NIE PRZELACZNIK - typ 'link'. Deklaracja dostepnosci jest
	 * wymagana ustawa od kazdej placowki publicznej i ma byc osiagalna ze
	 * strony glownej; panel dostepnosci jest miejscem, w ktorym ludzie jej
	 * szukaja. Adres bierze sie z ekranu ustawien, a gdy go tam nie ma,
	 * panel nie rysuje nic.
	 */
	$moduly[] = array(
		'slug'      => 'deklaracja',
		'grupa'     => 'pomoc',
		'ikona'     => 'dokument',
		'nazwa'     => __( 'Accessibility statement', 'alyxa-a11y' ),
		'opis'      => __( 'Opens the page where this institution says how accessible its site is and who to write to about it.', 'alyxa-a11y' ),
		'typ'       => 'link',
		'dane'      => array(
			'adres' => alyxa_adres_deklaracji(),
		),
		'css'       => '',
		'domyslnie' => false,
		'kolejnosc' => 95,
	);

	return $moduly;
}
add_filter( 'alyxa_moduly', 'alyxa_moduly_katalogu', 20 );
