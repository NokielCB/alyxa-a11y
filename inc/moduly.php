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
 * TO NIE JEST CALY REJESTR. Dziewiec modulow domyslnie wylaczonych stoi
 * w inc/katalog.php i dopisuje sie tym samym filtrem, tylko pozniej.
 * Rozdzial jest celowy: tutaj leza moduly uzgodnione z klientem i wlaczone
 * u niego, tam - propozycja dla kazdej innej strony.
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
		'grupa'     => 'tekst',
		'ikona'     => 'litery',
		'nazwa'     => __( 'Larger text', 'alyxa-a11y' ),
		'opis'      => __( 'Makes every text on the page bigger, in three steps up to 150%.', 'alyxa-a11y' ),
		'typ'       => 'stopnie',
		'stopnie'   => 3,

		/*
		 * PROCENT ZAMIAST "STOPIEN 2 Z 3". Odwiedzajacy zna procenty
		 * z powiekszania w przegladarce, a "drugi z trzech" nie mowi mu,
		 * ile to jest. Wartosci musza pochodzic stad, bo tylko ten modul
		 * wie, jaka skale wpisuje jego arkusz - rdzen nie ma jak zgadnac.
		 * Indeks 0 to stan wylaczony.
		 */
		'etykiety'  => array( '100%', '115%', '130%', '150%' ),

		/*
		 * KLUCZ 'klawisz' CZYTA MODUL SKROTOW, A NIE RDZEN. Rejestr podaje
		 * tablice 'dane' dalej nieprzeczytana - i wlasnie dlatego skrot moze
		 * tu stac. Modul dolozony filtrem deklaruje swoja litere tak samo
		 * jak nasze, a motyw, ktoremu litera koliduje z czyms wlasnym,
		 * podmienia ja jednym filtrem. Skrot dziala tylko wtedy, gdy modul
		 * skrotow jest wlaczony na stronie i przez odwiedzajacego.
		 */
		'dane'      => array( 'klawisz' => 't' ),
		'zgodnosc'  => array(
			'ocena'    => 'wspiera',
			'kryteria' => '1.4.4',
		),
		'css'       => ALYXA_A11Y_KATALOG . 'moduly/tekst.css',
		'domyslnie' => true,
		'kolejnosc' => 10,
	);

	$moduly[] = array(
		'slug'      => 'czcionka',
		'grupa'     => 'tekst',
		'ikona'     => 'kroj',
		'nazwa'     => __( 'Dyslexia-friendly font', 'alyxa-a11y' ),
		'opis'      => __( 'Atkinson Hyperlegible, a typeface drawn so that similar letters cannot be confused.', 'alyxa-a11y' ),
		'typ'       => 'przelacznik',
		'dane'      => array( 'klawisz' => 'f' ),
		'zgodnosc'  => array( 'ocena' => 'poza' ),
		'css'       => ALYXA_A11Y_KATALOG . 'moduly/czcionka.css',
		'domyslnie' => true,
		/* Zaraz za powiekszeniem tekstu, bo obie rzeczy dotycza pisma
		   i czytelnik szuka ich obok siebie, a nie na dwoch koncach listy. */
		'kolejnosc' => 15,
	);

	$moduly[] = array(
		'slug'      => 'odstepy',
		'grupa'     => 'tekst',
		'ikona'     => 'odstepy',
		'nazwa'     => __( 'More text spacing', 'alyxa-a11y' ),
		'opis'      => __( 'Taller lines, wider gaps between letters, words and paragraphs.', 'alyxa-a11y' ),
		'typ'       => 'przelacznik',
		'dane'      => array( 'klawisz' => 's' ),
		'zgodnosc'  => array(
			'ocena'    => 'wspiera',
			'kryteria' => '1.4.12',
		),
		'css'       => ALYXA_A11Y_KATALOG . 'moduly/odstepy.css',
		'domyslnie' => true,
		'kolejnosc' => 20,
	);

	$moduly[] = array(
		'slug'      => 'kontrast',
		'grupa'     => 'kolor',
		'ikona'     => 'kontrast',
		'nazwa'     => __( 'High contrast', 'alyxa-a11y' ),
		'opis'      => __( 'Black background, white text, yellow links. Photographs stay.', 'alyxa-a11y' ),
		'typ'       => 'przelacznik',
		'dane'      => array( 'klawisz' => 'k' ),
		'zgodnosc'  => array(
			'ocena'    => 'wspiera',
			'kryteria' => '1.4.6 (AAA)',
		),
		'css'       => ALYXA_A11Y_KATALOG . 'moduly/kontrast.css',
		'domyslnie' => true,
		'kolejnosc' => 30,
	);

	$moduly[] = array(
		'slug'      => 'linki',
		'grupa'     => 'kolor',
		'ikona'     => 'ogniwo',
		'nazwa'     => __( 'Underline links', 'alyxa-a11y' ),
		'opis'      => __( 'Every link gets an underline, so it stands out from ordinary text.', 'alyxa-a11y' ),
		'typ'       => 'przelacznik',
		'dane'      => array( 'klawisz' => 'u' ),
		'zgodnosc'  => array(
			'ocena'    => 'wspiera',
			'kryteria' => '1.4.1',
		),
		'css'       => ALYXA_A11Y_KATALOG . 'moduly/linki.css',
		'domyslnie' => true,
		'kolejnosc' => 40,
	);

	$moduly[] = array(
		'slug'      => 'animacje',
		'grupa'     => 'spokoj',
		'ikona'     => 'pauza',
		'nazwa'     => __( 'Stop animations', 'alyxa-a11y' ),
		'opis'      => __( 'Turns off movement, sliding and fading across the page.', 'alyxa-a11y' ),
		'typ'       => 'przelacznik',
		'dane'      => array( 'klawisz' => 'm' ),
		'zgodnosc'  => array(
			'ocena'    => 'wspiera',
			'kryteria' => '2.2.2, 2.3.3 (AAA)',
		),
		'css'       => ALYXA_A11Y_KATALOG . 'moduly/animacje.css',
		'domyslnie' => true,
		'kolejnosc' => 50,
	);

	$moduly[] = array(
		'slug'      => 'kursor',
		'grupa'     => 'wskaznik',
		'ikona'     => 'wskaznik',
		'nazwa'     => __( 'Large cursor', 'alyxa-a11y' ),
		'opis'      => __( 'A bigger mouse pointer, white with a black outline so it shows on any background.', 'alyxa-a11y' ),
		'typ'       => 'przelacznik',
		'dane'      => array( 'klawisz' => 'c' ),
		'zgodnosc'  => array( 'ocena' => 'poza' ),
		'css'       => ALYXA_A11Y_KATALOG . 'moduly/kursor.css',
		'domyslnie' => true,
		'kolejnosc' => 60,
	);

	$moduly[] = array(
		'slug'      => 'maska',
		'grupa'     => 'wskaznik',
		'ikona'     => 'pasmo',
		'nazwa'     => __( 'Reading mask', 'alyxa-a11y' ),
		'opis'      => __( 'Dims the page except for a band that follows the pointer, so the eye keeps its line.', 'alyxa-a11y' ),
		'typ'       => 'przelacznik',
		'dane'      => array( 'klawisz' => 'r' ),
		'zgodnosc'  => array( 'ocena' => 'poza' ),
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
	/*
	 * CZYTANIE WSKAZANEGO ELEMENTU. Ten sam glos, co odczyt calej strony,
	 * tylko krotszy zasieg: jeden akapit, jeden naglowek, jedna komorka -
	 * ten, ktory odwiedzajacy kliknal albo na ktorym stanal tabulatorem.
	 *
	 * PRZELACZNIK, A NIE TRZECI PRZYCISK PRZY ODCZYCIE STRONY. To jest
	 * ustawienie, ktore ma przetrwac przejscie na nastepna podstrone,
	 * a modul typu 'akcje' z zalozenia niczego nie zapisuje. Kto tego
	 * potrzebuje, potrzebuje na calej stronie, a nie na jednej podstronie.
	 *
	 * WARUNKOWY z tego samego powodu, co odczyt strony: bez glosu w jezyku
	 * strony kafelka nie ma wcale. Kafelek warunkowy to pierwszy taki
	 * przelacznik we wtyczce - odkrywa go metoda przygotuj() zachowania,
	 * bo wlacz() zaczyna dzialac dopiero po nacisnieciu kafelka, ktorego
	 * do tej chwili nie widac.
	 */
	$moduly[] = array(
		'slug'      => 'wskazywanie',
		'grupa'     => 'glos',
		'ikona'     => 'wskazane',
		'nazwa'     => __( 'Read what you click', 'alyxa-a11y' ),
		'opis'      => __( 'Reads one thing at a time: the paragraph, heading or link you click, or the one you reach with the Tab key.', 'alyxa-a11y' ),
		'typ'       => 'przelacznik',
		'dane'      => array(

			/* Po tym skrypt szuka glosu - patrz odczyt strony nizej. */
			'jezyk'   => get_bloginfo( 'language' ),

			'klawisz' => 'w',
		),
		'warunkowy' => true,
		'zgodnosc'  => array( 'ocena' => 'poza' ),
		'css'       => ALYXA_A11Y_KATALOG . 'moduly/wskazywanie.css',
		'domyslnie' => true,
		'kolejnosc' => 75,
	);

	$moduly[] = array(
		'slug'      => 'odczyt',
		'grupa'     => 'glos',
		'ikona'     => 'glos',
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
			 * o czym jest artykul.
			 *
			 * Wartosc domyslna przychodzi z ekranu ustawien, a gdy pole jest
			 * puste - z alyxa_obszar_domyslny(). Filtr stoi na wierzchu obu,
			 * bo motyw wie o sobie wiecej niz administrator wpisujacy selektor
			 * z pamieci.
			 *
			 * @since 0.7.0
			 *
			 * @param string $obszar Lista selektorow CSS oddzielona przecinkami.
			 */
			'obszar'     => (string) apply_filters( 'alyxa_obszar_odczytu', alyxa_obszar_odczytu() ),

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
		'zgodnosc'  => array( 'ocena' => 'poza' ),
		'css'       => '',
		'domyslnie' => true,
		'kolejnosc' => 80,
	);

	return $moduly;
}
add_filter( 'alyxa_moduly', 'alyxa_moduly_rdzenia' );
