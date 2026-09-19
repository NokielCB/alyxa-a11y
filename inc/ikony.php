<?php
/**
 * Ikony kafelkow.
 *
 * TRZECIE MIEJSCE, I JEST TO SWIADOMY WYJATEK OD ZASADY "REJESTR PLUS
 * ARKUSZ". Ikona jest znacznikiem, a znaczniki mieszkaja ze znacznikami:
 * kilkanascie sciezek SVG wklejonych w tablice rejestru zamienia ja
 * w nieczytelna sciane tekstu, ktora przy okazji trzeba by przepuszczac
 * przez wp_kses, bo z filtra moze przyjsc cokolwiek. Rejestr podaje wiec
 * nazwe ikony, a rysunek lezy tutaj.
 *
 * Modul bez ikony jest poprawny - kafelek dostaje wtedy sam napis. Modul
 * dolozony z zewnatrz filtrem alyxa_moduly moze wskazac nazwe z tej listy;
 * wlasny rysunek to zadanie na faze, w ktorej takich modulow bedzie wiecej
 * niz zero.
 *
 * DLACZEGO SVG W DOKUMENCIE, A NIE KROJ IKONOWY ALBO PLIK.
 * Kroj ikonowy rysuje znak z prywatnego obszaru Unicode i znika, gdy ktos
 * wymusi wlasna czcionke - czyli dokladnie wtedy, gdy odwiedzajacy wlaczy
 * nasz modul czcionki dla osob z dysleksja. Osobny plik to zadanie sieciowe
 * na kilkaset bajtow. Te rysunki sa czesciowo w dokumencie, dziedzicza kolor
 * po kafelku i nie kosztuja nic ponad wlasna dlugosc.
 *
 * @package alyxa-a11y
 */

defined( 'ABSPATH' ) || exit;

/**
 * Wypisuje ikone o podanej nazwie.
 *
 * Ikona jest ozdoba przy napisie, nie trescia - stad aria-hidden. Nazwe
 * kafelka niesie tekst obok, wiec czytnik ekranu nie oglasza jej dwa razy.
 *
 * @param string $nazwa Nazwa ikony z rejestru modulu.
 * @return void
 */
function alyxa_ikona( $nazwa ) {
	$sciezki = alyxa_sciezki_ikon();

	if ( empty( $sciezki[ $nazwa ] ) ) {
		return;
	}

	?>
	<svg
		class="alyxa__rysunek"
		viewBox="0 0 24 24"
		fill="none"
		stroke="currentColor"
		stroke-width="1.6"
		stroke-linecap="round"
		stroke-linejoin="round"
		aria-hidden="true"
		focusable="false"
	><?php echo $sciezki[ $nazwa ]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- staly znacznik z tego pliku, bez danych z zewnatrz. ?></svg>
	<?php
}

/**
 * Rysunki ikon.
 *
 * Kazdy w polu 24 na 24, sama linia, bez wypelnienia - grubosc i zaokraglenia
 * ustawia element nadrzedny, wiec ikona zmienia sie razem z kafelkiem.
 *
 * @return array<string, string> Nazwa ikony na zawartosc znacznika svg.
 */
function alyxa_sciezki_ikon() {
	return array(

		/* Dwie litery T roznej wielkosci - powiekszanie pisma. */
		'litery'     => '<path d="M3 6h9M7.5 6v13"/><path d="M14 11h7M17.5 11v8"/>',

		/* Litera A - kroj pisma. */
		'kroj'       => '<path d="M4 20 12 4l8 16"/><path d="M7.3 14.5h9.4"/>',

		/* Wiersze tekstu z pionowa strzalka - odstepy. */
		'odstepy'    => '<path d="M4 4v16"/><path d="M4 4 2.2 6M4 4l1.8 2M4 20l-1.8-2M4 20l1.8-2"/><path d="M9 7h12M9 12h12M9 17h12"/>',

		/* Wiersze rowne z lewej, poszarpane z prawej - wyrownanie do lewej. */
		'dolewej'    => '<path d="M4 5h16M4 10h10M4 15h16M4 20h10"/>',

		/*
		 * Te same wiersze zsuniete do srodka i do prawej. Dlugie wiersze
		 * zostaja pelne w kazdym z trzech rysunkow, a przesuwaja sie krotkie -
		 * inaczej ikona mowilaby o szerokosci akapitu, a nie o wyrownaniu.
		 */
		'dosrodka'   => '<path d="M4 5h16M7 10h10M4 15h16M7 20h10"/>',
		'doprawej'   => '<path d="M4 5h16M10 10h10M4 15h16M10 20h10"/>',

		/* Paleta malarska z trzema plamami farby - wlasne kolory. */
		'paleta'     => '<path d="M12 3.5a8.5 8.5 0 1 0 0 17c1.1 0 1.8-.8 1.8-1.7 0-.5-.2-.9-.5-1.2-.3-.3-.5-.7-.5-1.2 0-1 .8-1.8 1.8-1.8h2.1a4.8 4.8 0 0 0 4.8-4.8c0-3.4-4.3-6.3-9.5-6.3z"/><circle cx="7.6" cy="11.2" r="1.1"/><circle cx="10.2" cy="7.4" r="1.1"/><circle cx="14.6" cy="7.6" r="1.1"/>',

		/* Kwadrat przeciety po przekatnej, polowa wypelniona - odwrocenie barw. */
		'odwrocenie' => '<rect x="4" y="4" width="16" height="16" rx="2"/><path d="M20 4v14a2 2 0 0 1-2 2H4z" fill="currentColor" stroke="none"/>',

		/* Oko - filtr dla daltonistow. */
		'oko'        => '<path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12z"/><circle cx="12" cy="12" r="3"/>',

		/* Dwie nachodzace na siebie kartki, gorna polprzezroczysta - nakladka. */
		'nakladka'   => '<rect x="3.5" y="3.5" width="12" height="12" rx="1.5"/><rect x="8.5" y="8.5" width="12" height="12" rx="1.5" fill="currentColor" fill-opacity=".3"/>',

		/* Slonce do polowy zaciemnione, z krotkimi promieniami - przyciemnienie. */
		'jasnosc'    => '<circle cx="12" cy="12" r="4.2"/><path d="M12 7.8a4.2 4.2 0 0 1 0 8.4z" fill="currentColor" stroke="none"/><path d="M12 2.8v2M12 19.2v2M2.8 12h2M19.2 12h2M5.5 5.5l1.4 1.4M17.1 17.1l1.4 1.4M5.5 18.5l1.4-1.4M17.1 6.9l1.4-1.4"/>',

		/* Trojkat z wykrzyknikiem - ostrzezenie o ryzyku wzgledem WCAG. */
		'ostrzezenie' => '<path d="M12 3.8 21.2 19.8H2.8z"/><path d="M12 10v4.6"/><path d="M12 17.2h.01" stroke-width="2.4"/>',

		/* Litera B o grubej kresce - grubsze pismo. */
		'grubosc'    => '<path d="M8 5v14" stroke-width="2.6"/><path d="M8 5h5.2a3.5 3.5 0 0 1 0 7H8" stroke-width="2.6"/><path d="M8 12h6.2a3.5 3.5 0 0 1 0 7H8" stroke-width="2.6"/>',

		/* Kolo w polowie wypelnione - kontrast. */
		'kontrast'   => '<circle cx="12" cy="12" r="8.5"/><path d="M12 3.5v17a8.5 8.5 0 0 0 0-17z" fill="currentColor" stroke="none"/>',

		/* Ksiezyc - ciemny tryb. */
		'ksiezyc'    => '<path d="M20.2 14.8A8.6 8.6 0 0 1 9.2 3.8a8.6 8.6 0 1 0 11 11z"/>',

		/* Kropla w polowie wypelniona - nasycenie barw. */
		'kropla'     => '<path d="M12 3.4c3.6 4.4 5.6 7.3 5.6 9.8a5.6 5.6 0 0 1-11.2 0c0-2.5 2-5.4 5.6-9.8z"/><path d="M12 3.4v15.4a5.6 5.6 0 0 0 5.6-5.6c0-2.5-2-5.4-5.6-9.8z" fill="currentColor" stroke="none"/>',

		/* Ogniwo lancucha - odnosniki. */
		'ogniwo'     => '<path d="M10.2 13.8a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1.4 1.4"/><path d="M13.8 10.2a4 4 0 0 0-5.7 0l-3 3a4 4 0 1 0 5.7 5.7l1.4-1.4"/>',

		/* Dwie belki pauzy - zatrzymanie ruchu. */
		'pauza'      => '<circle cx="12" cy="12" r="8.5"/><path d="M10 9v6M14 9v6"/>',

		/* Przekreslone zdjecie - ukrywanie obrazow. */
		'obraz'      => '<rect x="3.5" y="5" width="17" height="14" rx="2"/><path d="m5.5 16.5 3.5-3.5 2.5 2.5"/><circle cx="15.2" cy="10" r="1.3"/><path d="M4 20.5 20 3.5"/>',

		/* Przekreslony glosnik - wyciszenie. */
		'cisza'      => '<path d="M4 9.5h3.2L12 5.5v13l-4.8-4H4z"/><path d="m16.2 10.2 4 4M20.2 10.2l-4 4"/>',

		/* Strzalka wskaznika - ten sam ksztalt co kursor modulu. */
		'wskaznik'   => '<path d="M6 3.5 18 12.6l-5.2.7 3 6.1-2.4 1.1-2.9-6.1-3.5 3.4z"/>',

		/* Pasmo w oknie - maska czytania. */
		'pasmo'      => '<rect x="3" y="4.5" width="18" height="15" rx="2"/><path d="M3 10h18M3 14h18"/>',

		/* Gruba kreska miedzy wierszami tekstu - linia czytania. */
		'linijka'    => '<path d="M4 5h16M4 9h11"/><path d="M2.5 13h19" stroke-width="2.8"/><path d="M4 17h16M4 21h11"/>',

		/* Strzalka wskaznika przy fali dzwieku - czytanie wskazanego. */
		'wskazane'   => '<path d="M4 3 12.6 9.6 8.9 10.1 11.1 14.5 9.3 15.2 7.2 10.9 4.7 13.3z"/><path d="M16.5 8.8a4.5 4.5 0 0 1 0 6.4"/><path d="M19.4 6.2a8.5 8.5 0 0 1 0 11.6"/>',

		/* Fala dzwieku przy pisku - odczyt na glos. */
		'glos'       => '<path d="M4 9.5h3.2L12 5.5v13l-4.8-4H4z"/><path d="M15.7 9.2a4 4 0 0 1 0 5.6"/><path d="M18.3 6.6a7.6 7.6 0 0 1 0 10.8"/>',

		/* Klawiatura - skroty klawiszowe. */
		'klawiatura' => '<rect x="2.5" y="6" width="19" height="12" rx="2"/><path d="M6 10h.01M9.5 10h.01M13 10h.01M16.5 10h.01M6 13.5h.01M9.5 13.5h.01M16.5 13.5h.01"/><path d="M12 13.5h1.5"/>',

		/* Kartka z zagieciem - deklaracja dostepnosci. */
		'dokument'   => '<path d="M6.5 3h7l4.5 4.5V21h-11.5z"/><path d="M13.5 3v4.5H18"/><path d="M9 12.5h6M9 16.5h6"/>',

		/* Litery A i g obok siebie, jedna z szeryfami - wybor kroju. */
		'pismo'      => '<path d="M2.5 19 7.5 5l5 14"/><path d="M4.3 14h6.4M1.5 19h2.4M11.1 19h2.4"/><circle cx="18" cy="13.5" r="3"/><path d="M21 10.5v8.2a3 3 0 0 1-5.4 1.8"/>',

		/* Litera H z pionowa kreska przed nia - wyroznienie naglowkow. */
		'naglowek'   => '<path d="M3.5 4v16" stroke-width="2.6"/><path d="M9 6v12M17 6v12M9 12h8"/>',

		/* Przerywana ramka wokol kursora dloni - obwodki odnosnikow. */
		'ramka'      => '<rect x="2.5" y="5.5" width="19" height="13" rx="2.5" stroke-dasharray="3 2.2"/><path d="M8 12h8M13 9l3 3-3 3"/>',

		/* Wiersze spisu z wcieciami - struktura strony. */
		'spis'       => '<path d="M4 5h.01M4 12h.01M4 19h.01" stroke-width="2.6"/><path d="M8 5h12M11 12h9M14 19h6"/>',
	);
}
