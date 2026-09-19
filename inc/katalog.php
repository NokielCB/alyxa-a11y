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
		'zgodnosc'  => array(
			'ocena'    => 'ryzyko',
			'kryteria' => '1.4.8 (AAA)',
			'uwaga'    => __( 'Centred text and text aligned to the line end are harder to read in long passages. The first setting, line start, is the one that helps.', 'alyxa-a11y' ),
		),
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
		'zgodnosc'  => array( 'ocena' => 'poza' ),
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
		'zgodnosc'  => array( 'ocena' => 'poza' ),
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
		'zgodnosc'  => array(
			'ocena'    => 'ryzyko',
			'kryteria' => '1.4.1',
			'uwaga'    => __( 'Anything the page says with colour alone stops being visible.', 'alyxa-a11y' ),
		),
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
		'zgodnosc'  => array(
			'ocena'    => 'ryzyko',
			'kryteria' => '1.1.1',
			'uwaga'    => __( 'A picture that carries text or information disappears together with the others.', 'alyxa-a11y' ),
		),
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
		'zgodnosc'  => array(
			'ocena'    => 'wspiera',
			'kryteria' => '1.4.2',
		),
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
		'zgodnosc'  => array( 'ocena' => 'poza' ),
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
		'zgodnosc'  => array( 'ocena' => 'poza' ),
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
		'zgodnosc'  => array( 'ocena' => 'poza' ),
		'css'       => '',
		'domyslnie' => false,
		'kolejnosc' => 95,
	);

	/*
	 * WLASNE KOLORY - PIERWSZY MODUL TYPU 'kolory'.
	 * Kontrast i ciemny tryb daja palete, ktora ktos ulozyl za odwiedzajacego.
	 * Tu odwiedzajacy uklada ja sam - i to jest dokladnie to, czego wymaga
	 * WCAG 1.4.8 na poziomie AAA: kolor tekstu i tla wybiera uzytkownik.
	 * Gotowe pary sa punktem wyjscia, nie jedynym wyborem; kazda z nich ma
	 * kontrast tekstu i odnosnikow powyzej 7:1 (policzone przy dopisywaniu,
	 * wynik w komentarzu przy kazdej parze) - takze na powierzchni drugiego
	 * planu, ktora motyw moze z nich wymieszac. Pierwsza wersja miala
	 * jasniejsze odnosniki w dwoch jasnych paletach i na takiej powierzchni
	 * (tlo z domieszka 7% tekstu) schodzily do 6,9:1 - zmierzone w fazie 11.
	 *
	 * WYGRYWA Z KONTRASTEM I Z CIEMNYM TRYBEM. Kto wybral kolory sam, ten
	 * powiedzial dokladniej, czego chce, niz przelacznik, ktory wlaczyl
	 * wczesniej. Jak to jest zrobione, opisuje moduly/kolory.css.
	 */
	$moduly[] = array(
		'slug'      => 'kolory',
		'grupa'     => 'kolor',
		'ikona'     => 'paleta',
		'nazwa'     => __( 'Your own colours', 'alyxa-a11y' ),
		'opis'      => __( 'Choose the colour of text, background and links, or start from a ready palette. The panel shows the contrast of your choice while you make it. Your colours win over high contrast and dark mode.', 'alyxa-a11y' ),
		'typ'       => 'kolory',
		'pola'      => array(
			'tekst' => __( 'Text', 'alyxa-a11y' ),
			'tlo'   => __( 'Background', 'alyxa-a11y' ),
			'linki' => __( 'Links', 'alyxa-a11y' ),
		),
		'pary'      => array(
			/* Tekst 16,1:1, odnosniki 9,5:1. */
			array(
				'nazwa' => __( 'Black on cream', 'alyxa-a11y' ),
				'tekst' => '#1a1a1a',
				'tlo'   => '#fdf6e3',
				'linki' => '#083f80',
			),
			/* Tekst 19,7:1, odnosniki 13,2:1. */
			array(
				'nazwa' => __( 'Yellow on black', 'alyxa-a11y' ),
				'tekst' => '#ffff66',
				'tlo'   => '#000000',
				'linki' => '#7fd8ff',
			),
			/* Tekst 16,2:1, odnosniki 11,9:1. */
			array(
				'nazwa' => __( 'White on navy', 'alyxa-a11y' ),
				'tekst' => '#ffffff',
				'tlo'   => '#0b1f44',
				'linki' => '#ffd966',
			),
			/* Tekst 11,6:1, odnosniki 9,2:1. */
			array(
				'nazwa' => __( 'Brown on beige', 'alyxa-a11y' ),
				'tekst' => '#3b2412',
				'tlo'   => '#f3e5c8',
				'linki' => '#18357a',
			),
		),
		'zgodnosc'  => array(
			'ocena'    => 'wspiera',
			'kryteria' => '1.4.8 (AAA)',
		),
		'css'       => ALYXA_A11Y_KATALOG . 'moduly/kolory.css',
		'domyslnie' => false,
		'kolejnosc' => 32,
	);

	/*
	 * CZTERY MODULY PONIZEJ SA OZNACZONE JAKO RYZYKO I TO JEST UCZCIWE.
	 * Kazdy zmienia kolory calej strony naraz, wiec kazdy moze pogorszyc
	 * cos, co WCAG mierzy. Sa w katalogu, bo ludzie o nie prosza i bo dla
	 * czesci z nich sa wygodne - ale administrator widzi ostrzezenie przy
	 * polu wyboru, a odwiedzajacy znak ostrzegawczy na kafelku.
	 *
	 * Trzy z nich to filtry CSS i dziela jeden lancuch filtrow - opis
	 * w moduly/odwrocenie.css. Przyciemnienie i nakladka to warstwy nad
	 * strona, bez filtra, wiec nie zabieraja nikomu przyklejenia do okna.
	 */
	$moduly[] = array(
		'slug'      => 'odwrocenie',
		'grupa'     => 'kolor',
		'ikona'     => 'odwrocenie',
		'nazwa'     => __( 'Invert colours', 'alyxa-a11y' ),
		'opis'      => __( 'Turns light into dark and dark into light across the whole page. Photographs and videos are turned back, so people keep their natural colours.', 'alyxa-a11y' ),
		'typ'       => 'przelacznik',
		'dane'      => array( 'klawisz' => 'e' ),
		'zgodnosc'  => array(
			'ocena'    => 'ryzyko',
			'kryteria' => '1.4.1',
			'uwaga'    => __( 'Colours swap their meaning - red turns cyan and green turns pink - and drawings and icons stay inverted.', 'alyxa-a11y' ),
		),
		'css'       => ALYXA_A11Y_KATALOG . 'moduly/odwrocenie.css',
		'domyslnie' => false,
		'kolejnosc' => 38,
	);

	/*
	 * FILTR KOREKCYJNY, NIE SYMULACJA. Symulacja pokazuje osobie widzacej
	 * barwy, jak strone widzi daltonista - pozyteczne dla projektanta,
	 * bezuzyteczne dla czytelnika. Korekcja (daltonizacja) liczy, jakie
	 * roznice barw dana osoba traci, i przesuwa je tam, gdzie je widzi.
	 * Macierze: symulacja Machado i in. (2009) przy pelnym nasileniu,
	 * przesuniecie bledu wedlug Fidanera; kazdy wiersz sumuje sie do 1,
	 * wiec szarosci - w tym czarny tekst na bialym - zostaja nietkniete.
	 *
	 * Trzy rownorzedne ustawienia, a nie skala - stad obieg i kafelek
	 * chodzacy w kolko.
	 */
	$moduly[] = array(
		'slug'      => 'daltonizm',
		'grupa'     => 'kolor',
		'ikona'     => 'oko',
		'nazwa'     => __( 'Colour blindness filter', 'alyxa-a11y' ),
		'opis'      => __( 'Shifts the colours of the page so that pairs easy to confuse with a given type of colour blindness move further apart. One setting for each of the three types.', 'alyxa-a11y' ),
		'typ'       => 'stopnie',
		'stopnie'   => 3,
		'obieg'     => true,
		'etykiety'  => array(
			__( 'As the page has it', 'alyxa-a11y' ),
			__( 'Red (protanopia)', 'alyxa-a11y' ),
			__( 'Green (deuteranopia)', 'alyxa-a11y' ),
			__( 'Blue (tritanopia)', 'alyxa-a11y' ),
		),
		'dane'      => array(
			'klawisz'  => 'x',

			/*
			 * Wartosci feColorMatrix, indeks = stopien. Liczby, nie znacznik:
			 * rysunek filtra sklada skrypt przez createElementNS, wiec zaden
			 * ciag z rejestru nie trafia do dokumentu jako HTML.
			 */
			'macierze' => array(
				1 => '1 0 0 0 0 0.4789 0.4769 0.0442 0 0 0.5973 -0.6887 1.0914 0 0 0 0 0 1 0',
				2 => '1 0 0 0 0 0.1628 0.7250 0.1122 0 0 0.4547 -0.6454 1.1907 0 0 0 0 0 1 0',
				3 => '0.7412 -0.4072 0.6660 0 0 0.0751 0.5852 0.3397 0 0 0 0 1 0 0 0 0 0 1 0',
			),
		),
		'zgodnosc'  => array(
			'ocena'    => 'ryzyko',
			'kryteria' => '1.4.1',
			'uwaga'    => __( 'The filter moves every colour on the page, including those that were already easy to tell apart, and some people find the result harder to read, not easier.', 'alyxa-a11y' ),
		),
		'css'       => ALYXA_A11Y_KATALOG . 'moduly/daltonizm.css',
		'domyslnie' => false,
		'kolejnosc' => 39,
	);

	/*
	 * NAKLADKA BARWNA - kolorowa folia do czytania, przeniesiona na ekran.
	 * Uwaga w rejestrze mowi wprost to, co wiadomo z badan: skutecznosci
	 * przy dysleksji nie wykazano. Jest tu, bo czesc osob czyta z nia
	 * wygodniej - a to wystarczajacy powod, jesli nikt nie obiecuje wiecej.
	 */
	$moduly[] = array(
		'slug'      => 'nakladka',
		'grupa'     => 'kolor',
		'ikona'     => 'nakladka',
		'nazwa'     => __( 'Colour overlay', 'alyxa-a11y' ),
		'opis'      => __( 'Lays a light tint over the whole page, the way a coloured reading sheet lies over a book. Four tints, one after another.', 'alyxa-a11y' ),
		'typ'       => 'stopnie',
		'stopnie'   => 4,
		'obieg'     => true,
		'etykiety'  => array(
			__( 'As the page has it', 'alyxa-a11y' ),
			__( 'Yellow', 'alyxa-a11y' ),
			__( 'Blue', 'alyxa-a11y' ),
			__( 'Green', 'alyxa-a11y' ),
			__( 'Pink', 'alyxa-a11y' ),
		),
		'dane'      => array( 'klawisz' => 'y' ),
		'zgodnosc'  => array(
			'ocena'    => 'ryzyko',
			'kryteria' => '1.4.3',
			'uwaga'    => __( 'A tint lowers the contrast of the whole page. Research has not shown that coloured overlays help with dyslexia; they are here because some people find them more comfortable.', 'alyxa-a11y' ),
		),
		'css'       => ALYXA_A11Y_KATALOG . 'moduly/nakladka.css',
		'domyslnie' => false,
		'kolejnosc' => 41,
	);

	/*
	 * PRZYCIEMNIENIE TO SKALA, WIEC MA MINUS I PLUS. Etykiety mowia, o ile
	 * strona jest ciemniejsza, a nie jaka jest jej jasnosc - plus ma
	 * znaczyc "wiecej tego modulu", tak jak przy kazdym innym.
	 */
	$moduly[] = array(
		'slug'      => 'przyciemnienie',
		'grupa'     => 'kolor',
		'ikona'     => 'jasnosc',
		'nazwa'     => __( 'Dim the page', 'alyxa-a11y' ),
		'opis'      => __( 'Darkens the whole page in three steps, for reading in a dark room or with light sensitivity. Unlike dark mode, it keeps the page\'s own colours.', 'alyxa-a11y' ),
		'typ'       => 'stopnie',
		'stopnie'   => 3,
		'etykiety'  => array( '0%', '15%', '30%', '45%' ),
		'dane'      => array( 'klawisz' => 'j' ),
		'zgodnosc'  => array(
			'ocena'    => 'ryzyko',
			'kryteria' => '1.4.3',
			'uwaga'    => __( 'Dimming lowers the contrast of the whole page; at the darkest step grey text can fall below the WCAG minimum.', 'alyxa-a11y' ),
		),
		'css'       => ALYXA_A11Y_KATALOG . 'moduly/przyciemnienie.css',
		'domyslnie' => false,
		'kolejnosc' => 42,
	);

	/*
	 * INNE KROJE - DOPELNIENIE MODULU CZCIONKI, NIE JEGO NASTEPCA.
	 * Atkinson Hyperlegible zostaje wyborem, ktory ma za soba najmocniejsze
	 * podstawy; tutaj sa kroje, o ktore ludzie prosza z imienia. Trzy
	 * systemowe to lista z zalecen British Dyslexia Association - nic nie
	 * pobieraja, bo sa juz na urzadzeniu, a gdy ktoregos brakuje, przegladarka
	 * bierze najblizszy zapasowy. OpenDyslexic lezy we wtyczce (licencja SIL
	 * OFL 1.1, assets/fonts/OFL-OpenDyslexic.txt) i pobiera sie dopiero wtedy,
	 * gdy ktos go wybierze.
	 *
	 * WYGRYWA Z MODULEM CZCIONKI, jesli oba sa wlaczone - wybor konkretnego
	 * kroju jest dokladniejszy niz przelacznik "czytelniejszy kroj". Ten sam
	 * argument i ta sama sztuczka co przy wlasnych kolorach; opis w arkuszu.
	 */
	$moduly[] = array(
		'slug'      => 'kroje',
		'grupa'     => 'tekst',
		'ikona'     => 'pismo',
		'nazwa'     => __( 'Other typefaces', 'alyxa-a11y' ),
		'opis'      => __( 'Four typefaces, one after another: Verdana, Georgia, Comic Sans and OpenDyslexic. If your device does not have one of the first three, the closest one it has is used.', 'alyxa-a11y' ),
		'typ'       => 'stopnie',
		'stopnie'   => 4,
		'obieg'     => true,

		/*
		 * Nazwy krojow sa nazwami wlasnymi - nie tlumaczy sie ich.
		 *
		 * MIEKKI LACZNIK W "OpenDyslexic". Napis na kafelku jest skladany
		 * wlasnie tym krojem, a w nim to jedno slowo ma 165 px przy kafelku
		 * szerokim na 123 (okno 320 px) - wystawalo poza panel. Miekki
		 * lacznik pozwala zlamac je w naturalnym miejscu, "Open-/Dyslexic",
		 * a czytniki ekranu go nie wymawiaja.
		 */
		'etykiety'  => array(
			__( 'As the page has it', 'alyxa-a11y' ),
			'Verdana',
			'Georgia',
			'Comic Sans',
			"Open\u{00AD}Dyslexic",
		),
		'dane'      => array( 'klawisz' => 'p' ),
		'zgodnosc'  => array(
			'ocena'    => 'ryzyko',
			'kryteria' => '1.4.10',
			'uwaga'    => __( 'Wider typefaces take more room. With OpenDyslexic and enlarged text on a narrow phone screen, a long word can stick out past the edge and the page scrolls sideways. Research has not shown that OpenDyslexic reads better than any other clear typeface.', 'alyxa-a11y' ),
		),
		'css'       => ALYXA_A11Y_KATALOG . 'moduly/kroje.css',
		'domyslnie' => false,
		'kolejnosc' => 16,
	);

	/*
	 * WYROZNIENIE NAGLOWKOW. Czytnik ekranu podaje poziom naglowka sam;
	 * oko musi go zgadywac z wielkosci pisma, a motyw potrafi miec naglowek
	 * drugiego i trzeciego stopnia tej samej wielkosci. Kreska, tlo i znacznik
	 * poziomu pokazuja uklad strony tak, jak slyszy go czytnik.
	 */
	$moduly[] = array(
		'slug'      => 'naglowki',
		'grupa'     => 'tekst',
		'ikona'     => 'naglowek',
		'nazwa'     => __( 'Highlight headings', 'alyxa-a11y' ),
		'opis'      => __( 'Marks every heading with a bar, a light background and its level, from H1 to H6, so the outline of the page shows at a glance.', 'alyxa-a11y' ),
		'typ'       => 'przelacznik',
		'dane'      => array( 'klawisz' => 'h' ),
		'zgodnosc'  => array( 'ocena' => 'poza' ),
		'css'       => ALYXA_A11Y_KATALOG . 'moduly/naglowki.css',
		'domyslnie' => false,
		'kolejnosc' => 26,
	);

	/*
	 * OBWODKI ODNOSNIKOW I PRZYCISKOW. Podkreslenie z rdzenia mowi, co jest
	 * odnosnikiem w tekscie; obwodka mowi, gdzie konczy sie pole do
	 * klikniecia - takze przy przyciskach, kafelkach i ikonach bez podpisu.
	 * Dwa rozne pytania, wiec dwa przelaczniki, ktore mozna wlaczyc razem.
	 */
	$moduly[] = array(
		'slug'      => 'ramki',
		'grupa'     => 'kolor',
		'ikona'     => 'ramka',
		'nazwa'     => __( 'Highlight links and buttons', 'alyxa-a11y' ),
		'opis'      => __( 'Draws a frame around every link and button, so you can see what can be clicked and how far it reaches.', 'alyxa-a11y' ),
		'typ'       => 'przelacznik',
		'dane'      => array( 'klawisz' => 'o' ),
		'zgodnosc'  => array(
			'ocena'    => 'wspiera',
			'kryteria' => '1.4.1',
		),
		'css'       => ALYXA_A11Y_KATALOG . 'moduly/ramki.css',
		'domyslnie' => false,
		'kolejnosc' => 44,
	);

	/*
	 * STRUKTURA STRONY - SPIS NAGLOWKOW I OBSZAROW, JAKI MA CZYTNIK EKRANU.
	 * NVDA i VoiceOver maja taka liste pod jednym klawiszem; osoba, ktora
	 * czytnika nie uzywa, a nawiguje klawiatura albo ma klopot z ogarnieciem
	 * dlugiej strony, nie ma jej wcale. Nacisniecie pozycji przenosi fokus
	 * do wskazanego miejsca, wiec nastepny Tab idzie dalej od niego.
	 *
	 * CZYNNOSC, NIE PRZELACZNIK. Spis dotyczy tej jednej podstrony i nie ma
	 * czego zapamietac na nastepna. Buduje sie od nowa przy kazdym otwarciu,
	 * bo strona potrafi dolozyc tresc po wczytaniu.
	 *
	 * Napisy spisu przychodza w 'dane', bo skrypt nie ma skad wziac
	 * tlumaczenia sam - ten sam wzorzec co zapowiedzi skrotow.
	 */
	$moduly[] = array(
		'slug'      => 'struktura',
		'grupa'     => 'pomoc',
		'ikona'     => 'spis',
		'nazwa'     => __( 'Page structure', 'alyxa-a11y' ),
		'opis'      => __( 'Lists the headings and regions of this page. Choose one to move straight to it.', 'alyxa-a11y' ),
		'typ'       => 'akcje',
		'akcje'     => array(
			array(
				'slug'  => 'pokaz',
				'nazwa' => __( 'Show headings and regions', 'alyxa-a11y' ),
			),
		),
		'dane'      => array(
			'naglowki'   => __( 'Headings', 'alyxa-a11y' ),
			'obszary'    => __( 'Regions', 'alyxa-a11y' ),
			'bezTekstu'  => __( 'Heading without text', 'alyxa-a11y' ),
			'brakNagl'   => __( 'This page has no headings.', 'alyxa-a11y' ),
			'brakObsz'   => __( 'This page has no marked regions.', 'alyxa-a11y' ),
			/* translators: 1: number of headings, 2: number of regions. */
			'znaleziono' => __( 'Headings: %1$d. Regions: %2$d.', 'alyxa-a11y' ),
			/* translators: 1: kind of region, for example Navigation, 2: its own name. */
			'nazwany'    => __( '%1$s: %2$s', 'alyxa-a11y' ),

			/*
			 * Klucze to role ARIA. Element HTML (nav, main...) skrypt
			 * sprowadza do roli sam, wiec lista zostaje jedna.
			 */
			'role'       => array(
				'banner'        => __( 'Page header', 'alyxa-a11y' ),
				'navigation'    => __( 'Navigation', 'alyxa-a11y' ),
				'main'          => __( 'Main content', 'alyxa-a11y' ),
				'complementary' => __( 'Side content', 'alyxa-a11y' ),
				'contentinfo'   => __( 'Page footer', 'alyxa-a11y' ),
				'search'        => __( 'Search', 'alyxa-a11y' ),
				'form'          => __( 'Form', 'alyxa-a11y' ),
				'region'        => __( 'Section', 'alyxa-a11y' ),
			),
		),
		'zgodnosc'  => array(
			'ocena'    => 'wspiera',
			'kryteria' => '2.4.1',
		),
		'css'       => ALYXA_A11Y_KATALOG . 'moduly/struktura.css',
		'domyslnie' => false,
		'kolejnosc' => 85,
	);

	return $moduly;
}
add_filter( 'alyxa_moduly', 'alyxa_moduly_katalogu', 20 );
