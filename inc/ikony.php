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
		'litery'   => '<path d="M3 6h9M7.5 6v13"/><path d="M14 11h7M17.5 11v8"/>',

		/* Litera A - kroj pisma. */
		'kroj'     => '<path d="M4 20 12 4l8 16"/><path d="M7.3 14.5h9.4"/>',

		/* Wiersze tekstu z pionowa strzalka - odstepy. */
		'odstepy'  => '<path d="M4 4v16"/><path d="M4 4 2.2 6M4 4l1.8 2M4 20l-1.8-2M4 20l1.8-2"/><path d="M9 7h12M9 12h12M9 17h12"/>',

		/* Kolo w polowie wypelnione - kontrast. */
		'kontrast' => '<circle cx="12" cy="12" r="8.5"/><path d="M12 3.5v17a8.5 8.5 0 0 0 0-17z" fill="currentColor" stroke="none"/>',

		/* Ogniwo lancucha - odnosniki. */
		'ogniwo'   => '<path d="M10.2 13.8a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1.4 1.4"/><path d="M13.8 10.2a4 4 0 0 0-5.7 0l-3 3a4 4 0 1 0 5.7 5.7l1.4-1.4"/>',

		/* Dwie belki pauzy - zatrzymanie ruchu. */
		'pauza'    => '<circle cx="12" cy="12" r="8.5"/><path d="M10 9v6M14 9v6"/>',

		/* Strzalka wskaznika - ten sam ksztalt co kursor modulu. */
		'wskaznik' => '<path d="M6 3.5 18 12.6l-5.2.7 3 6.1-2.4 1.1-2.9-6.1-3.5 3.4z"/>',

		/* Pasmo w oknie - maska czytania. */
		'pasmo'    => '<rect x="3" y="4.5" width="18" height="15" rx="2"/><path d="M3 10h18M3 14h18"/>',

		/* Fala dzwieku przy pisku - odczyt na glos. */
		'glos'     => '<path d="M4 9.5h3.2L12 5.5v13l-4.8-4H4z"/><path d="M15.7 9.2a4 4 0 0 1 0 5.6"/><path d="M18.3 6.6a7.6 7.6 0 0 1 0 10.8"/>',
	);
}
