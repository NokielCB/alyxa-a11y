<?php
/**
 * Przycisk i panel na stronie.
 *
 * PANEL NIE JEST OKNEM MODALNYM.
 * Nie ma role="dialog", nie ma pulapki fokusu, nie zaslania strony
 * przyciemnieniem. To rozwiniecie tresci (wzorzec disclosure): przycisk
 * z aria-expanded i obszar, ktory pojawia sie zaraz za nim w dokumencie.
 * Dzieki temu Tab z przycisku wchodzi do panelu bez zadnego kodu, a Tab
 * z konca panelu wychodzi dalej w strone - tak, jak uzytkownik klawiatury
 * sie tego spodziewa. Okno modalne trzeba by pilnowac skryptem, ktory
 * przy kazdym bledzie zamyka fokus w pulapce; tutaj nie ma czego zepsuc.
 *
 * MARKUP WYCHODZI Z SERWERA UKRYTY.
 * Przycisk ma atrybut hidden i zdejmuje go dopiero skrypt. Bez JavaScriptu
 * panel nie zrobi nic - nie ma czym zapisac ustawienia ani przelaczyc klasy -
 * wiec kontrolka, ktora po nacisnieciu milczy, bylaby gorsza niz jej brak.
 * Ten sam idiom co przycisk powrotu na gore w motywie.
 *
 * @package alyxa-a11y
 */

defined( 'ABSPATH' ) || exit;

/**
 * Wypisuje przycisk i panel w stopce dokumentu.
 *
 * @return void
 */
function alyxa_panel() {
	if ( ! alyxa_pokaz_panel() ) {
		return;
	}

	$konfiguracja = alyxa_konfiguracja();
	$dzialy       = alyxa_dzialy_panelu( alyxa_moduly_wlaczone() );
	?>
	<div class="alyxa alyxa--<?php echo esc_attr( $konfiguracja['rog'] ); ?>">
		<button
			type="button"
			class="alyxa__przycisk"
			id="alyxa-przycisk"
			aria-expanded="false"
			aria-controls="alyxa-panel"
			hidden
		>
			<?php alyxa_ikona_panelu(); ?>
			<span class="alyxa-tylko-czytnik"><?php esc_html_e( 'Accessibility settings', 'alyxa-a11y' ); ?></span>
		</button>

		<div class="alyxa__panel" id="alyxa-panel" hidden>
			<div class="alyxa__naglowek">
				<h2 class="alyxa__tytul"><?php esc_html_e( 'Accessibility settings', 'alyxa-a11y' ); ?></h2>

				<?php
				/*
				 * PRZYCISK "PRZYWROC" STOI W NAGLOWKU, A NIE W STOPCE.
				 * Powod jest jeden i praktyczny: naglowek jest przyklejony
				 * do gory panelu, wiec przycisk wycofania zostaje pod reka
				 * przez caly czas przewijania. Do stopki trzeba bylo zjechac
				 * przez dziewietnascie kafelkow - a po wycofanie siega ten,
				 * kto wlasnie wlaczyl cos, czego nie chcial, i chce to
				 * odkrecic natychmiast, a nie po podrozy na dol listy.
				 *
				 * Jest tylko JEDEN taki przycisk. Drugi, opisany slowem
				 * w stopce, bylby dla czytnika ekranu druga pozycja o tej
				 * samej nazwie i tym samym skutku.
				 */
				?>
				<div class="alyxa__narzedzia">
					<button type="button" class="alyxa__reset" data-alyxa-reset title="<?php esc_attr_e( 'Reset all settings', 'alyxa-a11y' ); ?>">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M3 5v6h6"/><path d="M3.5 15a9 9 0 1 0 1.6-9.4L3 9"/></svg>
						<span class="alyxa-tylko-czytnik"><?php esc_html_e( 'Reset all settings', 'alyxa-a11y' ); ?></span>
					</button>

					<button type="button" class="alyxa__zamknij" data-alyxa-zamknij title="<?php esc_attr_e( 'Close accessibility settings', 'alyxa-a11y' ); ?>">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true" focusable="false"><path d="M6 6l12 12M18 6L6 18"/></svg>
						<span class="alyxa-tylko-czytnik"><?php esc_html_e( 'Close accessibility settings', 'alyxa-a11y' ); ?></span>
					</button>
				</div>
			</div>

			<?php if ( $dzialy ) : ?>
				<?php foreach ( $dzialy as $dzial ) : ?>
					<section
						class="alyxa__dzial"
						data-alyxa-dzial="<?php echo esc_attr( $dzial['slug'] ); ?>"
						<?php echo $dzial['ukryty'] ? 'hidden' : ''; ?>
					>
						<h3 class="alyxa__dzial-tytul"><?php echo esc_html( $dzial['nazwa'] ); ?></h3>

						<ul class="alyxa__lista">
							<?php foreach ( $dzial['moduly'] as $modul ) : ?>
								<?php alyxa_pozycja_modulu( $modul ); ?>
							<?php endforeach; ?>
						</ul>
					</section>
				<?php endforeach; ?>
			<?php else : ?>
				<p class="alyxa__pusto"><?php esc_html_e( 'No accessibility features are switched on for this site yet.', 'alyxa-a11y' ); ?></p>
			<?php endif; ?>

			<div class="alyxa__stopka">
				<?php alyxa_objasnienie_ryzyk( alyxa_moduly_wlaczone() ); ?>

				<?php
				/*
				 * Zdanie o tym, gdzie zapisuje sie wybor. Nie jest ozdoba:
				 * przesadza, ze panel nie potrzebuje zgody na ciasteczka,
				 * i to jest pierwsze pytanie, ktore zada koordynator
				 * dostepnosci albo inspektor ochrony danych.
				 */
				?>
				<p class="alyxa__nota"><?php esc_html_e( 'Your choices stay in this browser. Nothing is sent to the server.', 'alyxa-a11y' ); ?></p>
			</div>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'alyxa_panel' );

/**
 * Rozklada moduly na dzialy, w kolejnosci z alyxa_grupy().
 *
 * DZIALY W PANELU WCHODZA W FAZIE 9 i jest to odwrocenie decyzji z fazy 8.
 * Wtedy modulow bylo dziesiec i siatka kafelkow byla czytelna bez naglowkow,
 * a kazdy naglowek liczyl sie jako kolejny przystanek czytnika ekranu przed
 * przelacznikiem, ktorego ktos szuka. Katalog opcjonalny podnosi mozliwa
 * liczbe kafelkow do dziewietnastu i rachunek sie odwraca: dziewietnascie
 * kafelkow bez podzialu to jeden ciag, po ktorym trzeba isc do konca,
 * a naglowki sa wtedy skokami, nie przeszkodami.
 *
 * DZIAL, W KTORYM WSZYSTKIE POZYCJE SA WARUNKOWE, WYCHODZI Z SERWERA UKRYTY.
 * Inaczej na urzadzeniu bez glosu zostawalby w panelu naglowek "Mowa" nad
 * pusta lista. Odkrywa go ta sama metoda, ktora odkrywa kafelek - pokaz()
 * z kontekstu zachowania.
 *
 * ODNOSNIK BEZ ADRESU WYPADA JUZ TUTAJ. Modul typu 'link' bez klucza 'adres'
 * nie ma czego pokazac, a gdyby zostal na liscie, potrafilby zrobic z pustego
 * dzialu dzial widoczny.
 *
 * @param array<string, array<string, mixed>> $moduly Moduly wlaczone na stronie.
 * @return array<int, array{slug: string, nazwa: string, ukryty: bool, moduly: array<int, array<string, mixed>>}>
 */
function alyxa_dzialy_panelu( array $moduly ) {
	$grupy  = alyxa_grupy();
	$wedlug = array();
	$dzialy = array();

	foreach ( $moduly as $modul ) {
		if ( 'link' === $modul['typ'] && empty( $modul['dane']['adres'] ) ) {
			continue;
		}

		$grupa = isset( $grupy[ $modul['grupa'] ] ) ? $modul['grupa'] : '';

		$wedlug[ $grupa ][] = $modul;
	}

	/* Modul z nieznanym dzialem laduje na koncu, zeby nie zniknal z panelu. */
	$kolejnosc = array_merge( $grupy, array( '' => __( 'Other', 'alyxa-a11y' ) ) );

	foreach ( $kolejnosc as $slug => $nazwa ) {
		if ( empty( $wedlug[ $slug ] ) ) {
			continue;
		}

		$ukryty = true;

		foreach ( $wedlug[ $slug ] as $modul ) {
			if ( ! $modul['warunkowy'] ) {
				$ukryty = false;

				break;
			}
		}

		$dzialy[] = array(
			'slug'   => $slug ? $slug : 'pozostale',
			'nazwa'  => $nazwa,
			'ukryty' => $ukryty,
			'moduly' => $wedlug[ $slug ],
		);
	}

	return $dzialy;
}

/**
 * Wypisuje pojedynczy przelacznik jako kafelek.
 *
 * KAFELEK ZAMIAST WIERSZA Z SUWAKIEM - zmiana wobec faz 1-7, na zyczenie.
 * Wzorem byla nakladka, ktora klient widzial na innych stronach szkol;
 * przenosimy z niej uklad, nie kolory. Kolory dalej pochodza z motywu.
 *
 * STAN NIESIE PLAKIETKA, KOLOR I OBWODKA NARAZ. Sam kolor nie wystarcza
 * (WCAG 1.4.1), a plakietka z ptaszkiem jest ksztaltem - widac ja przy
 * monochromatycznym widzeniu, w systemowym wysokim kontrascie i na wydruku.
 * Dla czytnika ekranu stan niesie aria-pressed, tak samo jak wczesniej.
 *
 * OPIS ZOSTAJE, TYLKO SCHODZI Z OCZU. W kafelku nie ma na niego miejsca,
 * a wyrzucenie go zabraloby czytnikowi ekranu jedyne zdanie tlumaczace,
 * co ten modul robi. Lezy wiec dalej w dokumencie, schowany dla oka
 * i podpiety przez aria-describedby.
 *
 * KAFELEK WARUNKOWY WYCHODZI Z SERWERA UKRYTY - tak samo jak blok czynnosci
 * nizej. Ukrycie siedzi na pozycji, a nie na przycisku, zeby razem z nim
 * znikal takze opis; zdejmuje je metoda przygotuj() zachowania, gdy okaze
 * sie, ze urzadzenie odwiedzajacego ma czym ten modul obsluzyc.
 *
 * @param array<string, mixed> $modul Definicja modulu z rejestru.
 * @return void
 */
function alyxa_pozycja_modulu( array $modul ) {
	$id_opisu = $modul['opis'] ? 'alyxa-opis-' . $modul['slug'] : '';

	if ( 'akcje' === $modul['typ'] ) {
		alyxa_pozycja_akcji( $modul, $id_opisu );

		return;
	}

	if ( 'stopnie' === $modul['typ'] ) {
		alyxa_pozycja_stopni( $modul, $id_opisu );

		return;
	}

	if ( 'link' === $modul['typ'] ) {
		alyxa_pozycja_linku( $modul, $id_opisu );

		return;
	}

	if ( 'kolory' === $modul['typ'] ) {
		alyxa_pozycja_kolorow( $modul, $id_opisu );

		return;
	}
	?>
	<li
		class="alyxa__pozycja"
		data-alyxa-pozycja="<?php echo esc_attr( $modul['slug'] ); ?>"
		<?php echo $modul['warunkowy'] ? 'hidden' : ''; ?>
	>
		<button
			type="button"
			class="alyxa__kafelek"
			data-alyxa-modul="<?php echo esc_attr( $modul['slug'] ); ?>"
			data-alyxa-typ="przelacznik"
			<?php if ( $id_opisu ) : ?>
				aria-describedby="<?php echo esc_attr( $id_opisu ); ?>"
			<?php endif; ?>
			aria-pressed="false"
		>
			<?php alyxa_ikona( $modul['ikona'] ); ?>
			<span class="alyxa__napis"><?php echo esc_html( $modul['nazwa'] ); ?></span>
			<?php alyxa_odznaka(); ?>
			<?php alyxa_znak_ryzyka( $modul ); ?>
		</button>

		<?php if ( $id_opisu ) : ?>
			<p class="alyxa__opis alyxa-tylko-czytnik" id="<?php echo esc_attr( $id_opisu ); ?>"><?php echo esc_html( alyxa_opis_modulu( $modul ) ); ?></p>
		<?php endif; ?>
	</li>
	<?php
}

/**
 * Wypisuje kafelek modulu stopniowanego.
 *
 * DWA PRZYCISKI ZAMIAST JEDNEGO CHODZACEGO W KOLKO - druga zmiana wobec
 * faz 1-7. Pierwsza wersja miala jeden przycisk przechodzacy 0, 1, 2, 3
 * i z powrotem do zera; argumentem bylo mniej przystankow tabulatora.
 * Przegral z tym, ze droga powrotna wiodla przez powiekszenie jeszcze
 * wieksze niz to, ktore komus wlasnie przeszkodzilo. Minus, procent, plus -
 * jak w przegladarce, ktora ci sami ludzie znaja.
 *
 * PROCENT, NIE "STOPIEN 2 Z 3". Wartosci pochodza z klucza 'etykiety'
 * rejestru, bo tylko modul wie, jaka skale wpisuje jego arkusz.
 *
 * @param array<string, mixed> $modul    Definicja modulu z rejestru.
 * @param string               $id_opisu Identyfikator akapitu z opisem.
 * @return void
 */
function alyxa_pozycja_stopni( array $modul, $id_opisu ) {
	/*
	 * STOPNIE Z OBIEGIEM DOSTAJA INNA KONTROLKE, I TO NIE JEST OZDOBA.
	 * Para minus-plus opisuje skale: mniej i wiecej tego samego. Wyrownanie
	 * tekstu skala nie jest - to trzy rownorzedne ustawienia, miedzy ktorymi
	 * sie krazy, i wlasnie to mowi o module flaga 'obieg'. Skoro flaga juz
	 * niesie te roznice, niech decyduje takze o wygladzie: jeden kafelek,
	 * ktory pokazuje, co jest ustawione teraz, zamiast minusa i plusa,
	 * ktore obiecuja "mniej wyrownania" i "wiecej wyrownania".
	 */
	if ( $modul['obieg'] ) {
		alyxa_pozycja_cyklu( $modul, $id_opisu );

		return;
	}

	$id_nazwy = 'alyxa-nazwa-' . $modul['slug'];
	/*
	 * Znak minus, nie dywiz: "\xE2\x88\x92" ma szerokosc plusa, wiec oba
	 * przyciski wygladaja na tak samo szerokie, a nie jak plus i kreseczka.
	 */
	$kroki = array(
		array( '-1', "\xE2\x88\x92", __( 'Decrease', 'alyxa-a11y' ) ),
		array( '1', '+', __( 'Increase', 'alyxa-a11y' ) ),
	);
	?>
	<li class="alyxa__pozycja alyxa__pozycja--szeroka">
		<div
			class="alyxa__kafelek alyxa__kafelek--stopnie"
			data-alyxa-kafelek="<?php echo esc_attr( $modul['slug'] ); ?>"
			role="group"
			aria-labelledby="<?php echo esc_attr( $id_nazwy ); ?>"
			<?php if ( $id_opisu ) : ?>
				aria-describedby="<?php echo esc_attr( $id_opisu ); ?>"
			<?php endif; ?>
		>
			<?php alyxa_ikona( $modul['ikona'] ); ?>
			<span class="alyxa__napis" id="<?php echo esc_attr( $id_nazwy ); ?>"><?php echo esc_html( $modul['nazwa'] ); ?></span>

			<span class="alyxa__stopnie">
				<?php foreach ( $kroki as $krok ) : ?>
					<button
						type="button"
						class="alyxa__krok"
						data-alyxa-modul="<?php echo esc_attr( $modul['slug'] ); ?>"
						data-alyxa-krok="<?php echo esc_attr( $krok[0] ); ?>"
					>
						<span aria-hidden="true"><?php echo esc_html( $krok[1] ); ?></span>
						<span class="alyxa-tylko-czytnik"><?php echo esc_html( $krok[2] ); ?></span>
					</button>

					<?php if ( '-1' === $krok[0] ) : ?>
						<?php
						/*
						 * Odczyt jest obszarem aria-live, bo fokus zostaje na
						 * przycisku, a jego nazwa sie nie zmienia - inaczej
						 * nikt niewidzacy nie dowiedzialby sie, co nacisniecie
						 * dalo. Wypelnia go skrypt, bo stan zna dopiero on.
						 */
						?>
						<span class="alyxa__odczyt" data-alyxa-stan role="status"></span>
					<?php endif; ?>
				<?php endforeach; ?>
			</span>

			<?php alyxa_odznaka(); ?>
			<?php alyxa_znak_ryzyka( $modul ); ?>
		</div>

		<?php if ( $id_opisu ) : ?>
			<p class="alyxa__opis alyxa-tylko-czytnik" id="<?php echo esc_attr( $id_opisu ); ?>"><?php echo esc_html( alyxa_opis_modulu( $modul ) ); ?></p>
		<?php endif; ?>
	</li>
	<?php
}

/**
 * Wypisuje kafelek chodzacy w kolko - jeden przycisk na kilka ustawien.
 *
 * WYGLADA JAK PRZELACZNIK, BO ZACHOWUJE SIE JAK PRZELACZNIK: nacisniecie
 * zmienia ustawienie, plakietka mowi, ze cos jest wlaczone. Roznica jest
 * jedna - stanow jest wiecej niz dwa, a kafelek pokazuje ten, ktory
 * obowiazuje: rysunek i napis zmieniaja sie razem ze stopniem.
 *
 * WSZYSTKIE WARIANTY WYCHODZA Z SERWERA, UKRYTE POZA JEDNYM. Skrypt tylko
 * przestawia atrybut hidden, a nie sklada napisow ani rysunkow - dzieki temu
 * tlumaczenie zostaje w PHP, a przegladarka nie musi ufac zadnemu ciagowi
 * skladanemu w locie. Element ukryty przez hidden nie liczy sie takze do
 * nazwy dostepnej przycisku, wiec czytnik ekranu czyta dokladnie jeden wariant.
 *
 * NAZWA MODULU JEDZIE PRZED ETYKIETA, SCHOWANA DLA OKA. Sam napis "Do srodka"
 * nic nie znaczy w siatce kilkunastu kafelkow; nazwa dostepna brzmi wiec
 * "Wyrownanie tekstu: Do srodka", a widoczny napis w niej siedzi w calosci -
 * tego wymaga WCAG 2.5.3 od kazdego, kto steruje strona glosem.
 *
 * NIE MA TU aria-pressed ANI OBSZARU aria-live. Stan niesie nazwa dostepna,
 * ktora zmienia sie przy nacisnieciu - tak samo jak w przycisku odtwarzania,
 * ktory staje sie przyciskiem pauzy. Obszar aria-live powiedzialby to samo
 * drugi raz, a aria-pressed dolozylby do nazwy jeszcze "wcisniety", choc
 * stanow jest cztery, a nie dwa.
 *
 * @param array<string, mixed> $modul    Definicja modulu z rejestru.
 * @param string               $id_opisu Identyfikator akapitu z opisem.
 * @return void
 */
function alyxa_pozycja_cyklu( array $modul, $id_opisu ) {
	$etykiety = $modul['etykiety'];
	$ikony    = $modul['ikony'];
	?>
	<li class="alyxa__pozycja" data-alyxa-pozycja="<?php echo esc_attr( $modul['slug'] ); ?>">
		<button
			type="button"
			class="alyxa__kafelek alyxa__kafelek--cykl"
			data-alyxa-modul="<?php echo esc_attr( $modul['slug'] ); ?>"
			data-alyxa-kafelek="<?php echo esc_attr( $modul['slug'] ); ?>"
			data-alyxa-krok="1"
			<?php if ( $id_opisu ) : ?>
				aria-describedby="<?php echo esc_attr( $id_opisu ); ?>"
			<?php endif; ?>
		>
			<?php for ( $stopien = 0; $stopien <= $modul['stopnie']; $stopien++ ) : ?>
				<?php
				/*
				 * Stopien zerowy nosi nazwe modulu, a nie etykiete "tak jak
				 * na stronie": kafelek w stanie wyjsciowym ma sie przedstawic
				 * tak samo jak kazdy inny kafelek w siatce. Etykieta zerowa
				 * zostaje w rejestrze, bo potrzebuje jej odczyt przy parze
				 * minus-plus - czyli ten sam modul na stronie, ktora obieg
				 * wylaczyla filtrem.
				 */
				$napis = 0 === $stopien || empty( $etykiety[ $stopien ] )
					? $modul['nazwa']
					: $etykiety[ $stopien ];

				$rysunek = empty( $ikony[ $stopien ] ) ? $modul['ikona'] : $ikony[ $stopien ];
				?>
				<span
					class="alyxa__wariant"
					data-alyxa-wariant="<?php echo esc_attr( (string) $stopien ); ?>"
					<?php echo 0 === $stopien ? '' : 'hidden'; ?>
				>
					<?php alyxa_ikona( $rysunek ); ?>

					<span class="alyxa__napis">
						<?php if ( 0 !== $stopien ) : ?>
							<span class="alyxa-tylko-czytnik"><?php echo esc_html( $modul['nazwa'] ); ?>: </span>
						<?php endif; ?>
						<?php echo esc_html( $napis ); ?>
					</span>
				</span>
			<?php endfor; ?>

			<?php alyxa_odznaka(); ?>
			<?php alyxa_znak_ryzyka( $modul ); ?>
		</button>

		<?php if ( $id_opisu ) : ?>
			<p class="alyxa__opis alyxa-tylko-czytnik" id="<?php echo esc_attr( $id_opisu ); ?>"><?php echo esc_html( alyxa_opis_modulu( $modul ) ); ?></p>
		<?php endif; ?>
	</li>
	<?php
}

/**
 * Opis modulu razem z ostrzezeniem o ryzyku.
 *
 * Ostrzezenie jedzie w tym samym akapicie co opis, bo tylko ten akapit
 * czytnik ekranu podpina do kafelka. Znak na kafelku jest dla oka; bez
 * tego zdania osoba niewidzaca nie dowiedzialaby sie o ryzyku wcale.
 *
 * @param array<string, mixed> $modul Definicja modulu z rejestru.
 * @return string
 */
function alyxa_opis_modulu( array $modul ) {
	$opis = $modul['opis'];

	if ( empty( $modul['zgodnosc']['ocena'] ) || 'ryzyko' !== $modul['zgodnosc']['ocena'] || '' === $modul['zgodnosc']['uwaga'] ) {
		return $opis;
	}

	$ostrzezenie = sprintf(
		/* translators: 1: WCAG success criteria, for example 1.4.3; 2: one sentence saying what the module can make worse. */
		__( 'Warning (WCAG %1$s): %2$s', 'alyxa-a11y' ),
		$modul['zgodnosc']['kryteria'],
		$modul['zgodnosc']['uwaga']
	);

	return trim( $opis . ' ' . $ostrzezenie );
}

/**
 * Znak ostrzegawczy na kafelku modulu z ocena 'ryzyko'.
 *
 * KSZTALT, NIE KOLOR. Trojkat z wykrzyknikiem jest czytelny przy
 * monochromatycznym widzeniu, w systemowym wysokim kontrascie i na wydruku,
 * tak samo jak plakietka z ptaszkiem w przeciwleglym rogu. Stoi w dolnym
 * rogu, bo gorne zajmuja plakietka stanu i litera skrotu.
 *
 * @param array<string, mixed> $modul Definicja modulu z rejestru.
 * @return void
 */
function alyxa_znak_ryzyka( array $modul ) {
	if ( empty( $modul['zgodnosc']['ocena'] ) || 'ryzyko' !== $modul['zgodnosc']['ocena'] ) {
		return;
	}
	?>
	<span class="alyxa__ryzyko" aria-hidden="true">
		<?php alyxa_ikona( 'ostrzezenie' ); ?>
	</span>
	<?php
}

/**
 * Objasnienie znakow ostrzegawczych, w stopce panelu.
 *
 * ROZWIJANE, BO JEST DLA TYCH, KTORZY PYTAJA. Znak na kafelku mowi "tu jest
 * cos do wiedzenia"; kto chce wiedziec co, otwiera to jednym nacisnieciem.
 * Natywne details i summary, bez skryptu - dzialaja klawiatura i czytnikiem
 * ekranu z pudelka, a zamkniete zajmuja jeden wiersz.
 *
 * Opisy kafelkow sa schowane dla oka (koszt ukladu siatki, znany od fazy 8),
 * wiec to jest jedyne miejsce, w ktorym osoba widzaca przeczyta, CO dany
 * modul moze pogorszyc. Czytnik ekranu slyszy to samo w opisie kafelka.
 *
 * @param array<string, array<string, mixed>> $moduly Moduly wlaczone na stronie.
 * @return void
 */
function alyxa_objasnienie_ryzyk( array $moduly ) {
	$ryzykowne = array();

	foreach ( $moduly as $modul ) {
		if ( ! empty( $modul['zgodnosc']['ocena'] ) && 'ryzyko' === $modul['zgodnosc']['ocena'] && '' !== $modul['zgodnosc']['uwaga'] ) {
			$ryzykowne[] = $modul;
		}
	}

	if ( ! $ryzykowne ) {
		return;
	}
	?>
	<details class="alyxa__ryzyka">
		<summary>
			<span class="alyxa__ryzyko alyxa__ryzyko--w-tekscie" aria-hidden="true"><?php alyxa_ikona( 'ostrzezenie' ); ?></span>
			<?php esc_html_e( 'Why some tiles carry a warning sign', 'alyxa-a11y' ); ?>
		</summary>

		<p><?php esc_html_e( 'These settings go beyond the WCAG guidelines and can make part of the page harder to read. They are here because some people find them helpful.', 'alyxa-a11y' ); ?></p>

		<ul>
			<?php foreach ( $ryzykowne as $modul ) : ?>
				<li>
					<strong><?php echo esc_html( $modul['nazwa'] ); ?></strong>
					<?php
					/* translators: %s: WCAG success criteria, for example 1.4.3. */
					echo esc_html( sprintf( __( '(WCAG %s):', 'alyxa-a11y' ), $modul['zgodnosc']['kryteria'] ) );
					?>
					<?php echo esc_html( $modul['zgodnosc']['uwaga'] ); ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</details>
	<?php
}

/**
 * Wypisuje pozycje modulu kolorow - gotowe palety i trzy pola koloru.
 *
 * GOTOWE PALETY STOJA PIERWSZE. Natywne pole koloru jest dostepne bardzo
 * roznie: w jednej przegladarce to siatka obslugiwana strzalkami, w innej
 * systemowe okno, ktore czytnik ekranu ledwo widzi. Paleta to zwykly
 * przycisk - dziala wszedzie tak samo - wiec kto nie chce albo nie moze
 * walczyc z polem koloru, ma pelna droge bez niego.
 *
 * KONTRAST LICZY SIE NA ZYWO i jest obszarem aria-live. To jest miejsce,
 * w ktorym ten modul mowi o WCAG: nie zabrania wybrac szarego na czarnym
 * (ktos z nadwrazliwoscia na swiatlo moze tego chciec), ale mowi wprost,
 * ze wynik spadl ponizej progu.
 *
 * PANEL ZOSTAJE WE WLASNYCH KOLORACH. Gdyby bral kolory odwiedzajacego,
 * wybor czarnego na czarnym zabralby przycisk, ktorym sie to odkreca.
 *
 * @param array<string, mixed> $modul    Definicja modulu z rejestru.
 * @param string               $id_opisu Identyfikator akapitu z opisem.
 * @return void
 */
function alyxa_pozycja_kolorow( array $modul, $id_opisu ) {
	$id_nazwy = 'alyxa-nazwa-' . $modul['slug'];
	$slug     = $modul['slug'];
	$pierwsza = $modul['pary'][0];
	?>
	<li class="alyxa__pozycja alyxa__pozycja--szeroka" data-alyxa-pozycja="<?php echo esc_attr( $slug ); ?>">
		<div
			class="alyxa__kafelek alyxa__kafelek--kolory"
			data-alyxa-kafelek="<?php echo esc_attr( $slug ); ?>"
			role="group"
			aria-labelledby="<?php echo esc_attr( $id_nazwy ); ?>"
			<?php if ( $id_opisu ) : ?>
				aria-describedby="<?php echo esc_attr( $id_opisu ); ?>"
			<?php endif; ?>
		>
			<p class="alyxa__nazwa" id="<?php echo esc_attr( $id_nazwy ); ?>"><?php alyxa_ikona( $modul['ikona'] ); ?><?php echo esc_html( $modul['nazwa'] ); ?></p>

			<div class="alyxa__palety">
				<?php foreach ( $modul['pary'] as $numer => $para ) : ?>
					<?php
					/*
					 * Probka rysuje sie kolorami pary przez zmienne w atrybucie
					 * style. Wartosci przeszly sanitize_hex_color w rejestrze,
					 * wiec do atrybutu trafia wylacznie #rrggbb.
					 */
					$styl = '';

					foreach ( $modul['pola'] as $pole => $nazwa_pola ) {
						$styl .= '--alyxa-probka-' . $pole . ':' . $para[ $pole ] . ';';
					}
					?>
					<button
						type="button"
						class="alyxa__paleta"
						data-alyxa-modul="<?php echo esc_attr( $slug ); ?>"
						data-alyxa-para="<?php echo esc_attr( (string) $numer ); ?>"
						aria-pressed="false"
						style="<?php echo esc_attr( $styl ); ?>"
					>
						<span class="alyxa__probka" aria-hidden="true">Aa</span>
						<span class="alyxa__napis"><?php echo esc_html( $para['nazwa'] ); ?></span>
					</button>
				<?php endforeach; ?>
			</div>

			<div class="alyxa__pola">
				<?php foreach ( $modul['pola'] as $pole => $nazwa_pola ) : ?>
					<label class="alyxa__pole">
						<span><?php echo esc_html( $nazwa_pola ); ?></span>
						<input
							type="color"
							data-alyxa-modul="<?php echo esc_attr( $slug ); ?>"
							data-alyxa-pole="<?php echo esc_attr( $pole ); ?>"
							value="<?php echo esc_attr( $pierwsza[ $pole ] ); ?>"
						>
					</label>
				<?php endforeach; ?>
			</div>

			<p class="alyxa__komunikat alyxa__kontrast" data-alyxa-kontrast role="status"></p>

			<button type="button" class="alyxa__akcja alyxa__wylacz" data-alyxa-modul="<?php echo esc_attr( $slug ); ?>" data-alyxa-wylacz hidden>
				<?php
				/* translators: %s: name of the module, for example "Your own colours". */
				echo esc_html( sprintf( __( 'Switch off: %s', 'alyxa-a11y' ), $modul['nazwa'] ) );
				?>
			</button>

			<?php alyxa_odznaka(); ?>
		</div>

		<?php if ( $id_opisu ) : ?>
			<p class="alyxa__opis alyxa-tylko-czytnik" id="<?php echo esc_attr( $id_opisu ); ?>"><?php echo esc_html( alyxa_opis_modulu( $modul ) ); ?></p>
		<?php endif; ?>
	</li>
	<?php
}

/**
 * Plakietka wlaczonego kafelka.
 *
 * Rysunek, nie znak z czcionki: znak zalezalby od kroju, ktory na tej
 * stronie moze byc dowolny - razem z naszym wlasnym modulem czcionki
 * dla osob z dysleksja.
 *
 * @return void
 */
function alyxa_odznaka() {
	?>
	<span class="alyxa__odznaka" aria-hidden="true">
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="M5 12.5 10 17.5 19 7"/></svg>
	</span>
	<?php
}

/**
 * Wypisuje pozycje modulu, ktory nie ma stanu, tylko przyciski czynnosci.
 *
 * DLACZEGO NIE PRZELACZNIK. Przelacznik obiecuje, ze cos zostaje wlaczone -
 * a odczyt strony konczy sie sam na ostatnim zdaniu i nie przechodzi na
 * nastepna podstrone. Przycisk, ktory po chwili sam wraca do polozenia
 * wyjsciowego, klamie o tym, czym jest; grupa przyciskow czynnosci nie.
 *
 * POZYCJA WARUNKOWA WYCHODZI Z SERWERA UKRYTA. Serwer nie wie, czy
 * przegladarka odwiedzajacego ma czym czytac po polsku, a to jest wiadomosc
 * z urzadzenia, nie ze strony. Zdejmuje ukrycie zachowanie modulu - ten sam
 * idiom, co przycisk otwierajacy panel, ktory pojawia sie dopiero wtedy,
 * gdy jest czym przelaczac.
 *
 * @param array<string, mixed> $modul    Definicja modulu z rejestru.
 * @param string               $id_opisu Identyfikator akapitu z opisem.
 * @return void
 */
function alyxa_pozycja_akcji( array $modul, $id_opisu ) {
	$id_nazwy = 'alyxa-nazwa-' . $modul['slug'];
	?>
	<li
		class="alyxa__pozycja alyxa__pozycja--szeroka alyxa__pozycja--akcje"
		data-alyxa-pozycja="<?php echo esc_attr( $modul['slug'] ); ?>"
		<?php echo $modul['warunkowy'] ? 'hidden' : ''; ?>
	>
		<p class="alyxa__nazwa" id="<?php echo esc_attr( $id_nazwy ); ?>"><?php alyxa_ikona( $modul['ikona'] ); ?><?php echo esc_html( $modul['nazwa'] ); ?></p>

		<?php if ( $id_opisu ) : ?>
			<p class="alyxa__opis" id="<?php echo esc_attr( $id_opisu ); ?>"><?php echo esc_html( alyxa_opis_modulu( $modul ) ); ?></p>
		<?php endif; ?>

		<?php
		/*
		 * Grupa, a nie zbior luznych przyciskow: czytnik ekranu oglasza jej
		 * nazwe przy wejsciu, wiec "Zatrzymaj czytanie" nie wisi w prozni.
		 */
		?>
		<div
			class="alyxa__akcje"
			role="group"
			aria-labelledby="<?php echo esc_attr( $id_nazwy ); ?>"
			<?php if ( $id_opisu ) : ?>
				aria-describedby="<?php echo esc_attr( $id_opisu ); ?>"
			<?php endif; ?>
		>
			<?php foreach ( $modul['akcje'] as $akcja ) : ?>
				<button
					type="button"
					class="alyxa__akcja"
					data-alyxa-modul="<?php echo esc_attr( $modul['slug'] ); ?>"
					data-alyxa-akcja="<?php echo esc_attr( $akcja['slug'] ); ?>"
				>
					<?php echo esc_html( $akcja['nazwa'] ); ?>
				</button>
			<?php endforeach; ?>
		</div>

		<?php
		/*
		 * Obszar komunikatu jest w dokumencie od poczatku i pusty. Czytnik
		 * ekranu sledzi obszar aria-live tylko wtedy, gdy zastal go przy
		 * budowaniu drzewa - element dolozony razem z trescia bywa ogloszony
		 * dopiero za nastepnym razem albo wcale.
		 *
		 * Ten sam komunikat niesie dwie rzeczy naraz: dla oka jest jedynym
		 * potwierdzeniem, ze cos sie dzieje, zanim glos zdazy zaczac mowic
		 * (a przy pierwszym uzyciu potrafi to potrwac), a dla ucha - stanem,
		 * ktorego nie ma jak wyczytac z etykiety przycisku.
		 */
		?>
		<p class="alyxa__komunikat" data-alyxa-komunikat role="status"></p>
	</li>
	<?php
}

/**
 * Wypisuje pozycje modulu, ktory jest odnosnikiem.
 *
 * DLACZEGO ODNOSNIK, A NIE PRZYCISK CZYNNOSCI. Bo to jest odnosnik i ma sie
 * zachowywac jak odnosnik: pokazac adres na pasku stanu, otworzyc sie
 * srodkowym przyciskiem myszy w nowej karcie, trafic do listy odnosnikow
 * czytnika ekranu. Przycisk, ktory przenosi na inna strone, kazda z tych
 * rzeczy odbiera.
 *
 * BEZ target="_blank". Otwieranie w nowej karcie bez ostrzezenia jest
 * niespodzianka dla kazdego, a przy czytniku ekranu i przy powiekszeniu
 * ekranowym niespodzianka kosztowna: przycisk "wstecz" przestaje dzialac,
 * bo poprzedniej strony nie ma w historii tej karty.
 *
 * @param array<string, mixed> $modul    Definicja modulu z rejestru.
 * @param string               $id_opisu Identyfikator akapitu z opisem.
 * @return void
 */
function alyxa_pozycja_linku( array $modul, $id_opisu ) {
	$adres = isset( $modul['dane']['adres'] ) ? (string) $modul['dane']['adres'] : '';

	/* Bez adresu nie ma czego pokazac; alyxa_dzialy_panelu odsiewa to wczesniej. */
	if ( '' === $adres ) {
		return;
	}
	?>
	<li class="alyxa__pozycja alyxa__pozycja--szeroka">
		<a
			class="alyxa__odnosnik"
			href="<?php echo esc_url( $adres ); ?>"
			<?php if ( $id_opisu ) : ?>
				aria-describedby="<?php echo esc_attr( $id_opisu ); ?>"
			<?php endif; ?>
		>
			<?php alyxa_ikona( $modul['ikona'] ); ?>
			<span class="alyxa__napis"><?php echo esc_html( $modul['nazwa'] ); ?></span>

			<svg class="alyxa__strzalka" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M5 12h13M13 6l6 6-6 6"/></svg>
		</a>

		<?php if ( $id_opisu ) : ?>
			<p class="alyxa__opis" id="<?php echo esc_attr( $id_opisu ); ?>"><?php echo esc_html( alyxa_opis_modulu( $modul ) ); ?></p>
		<?php endif; ?>
	</li>
	<?php
}

/**
 * Ikona na przycisku otwierajacym.
 *
 * Postac z rozlozonymi rekami jest ustalonym znakiem dostepnosci - ten sam
 * ksztalt niesie przycisk w systemach Apple, Windows i Androidzie. Nazwe
 * dostepna daje tekst obok, schowany dla oka; ikona jest aria-hidden, zeby
 * czytnik nie ogloszil jej drugi raz.
 *
 * @return void
 */
function alyxa_ikona_panelu() {
	?>
	<svg class="alyxa__ikona" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
		<circle cx="12" cy="4.3" r="1.9" fill="currentColor" stroke="none"/>
		<path d="M4.7 8.6h14.6"/>
		<path d="M12 8.6v5.1"/>
		<path d="M12 13.7l-2.7 6.1M12 13.7l2.7 6.1"/>
	</svg>
	<?php
}
