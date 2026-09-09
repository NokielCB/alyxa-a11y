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
	$moduly       = alyxa_moduly_wlaczone();

	/*
	 * FAZA 8: gdy ekran ustawien pozwoli juz wylaczyc wszystkie moduly,
	 * tu wroci warunek konczacy prace przy pustej liscie - panel bez ani
	 * jednego przelacznika nie ma po co zajmowac rogu ekranu. Dzis lista
	 * jest pusta z zalozenia i wlasnie ten pusty panel oceniamy.
	 */
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

				<button type="button" class="alyxa__zamknij" data-alyxa-zamknij>
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true" focusable="false"><path d="M6 6l12 12M18 6L6 18"/></svg>
					<span class="alyxa-tylko-czytnik"><?php esc_html_e( 'Close accessibility settings', 'alyxa-a11y' ); ?></span>
				</button>
			</div>

			<?php if ( $moduly ) : ?>
				<ul class="alyxa__lista">
					<?php foreach ( $moduly as $modul ) : ?>
						<?php alyxa_pozycja_modulu( $modul ); ?>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p class="alyxa__pusto"><?php esc_html_e( 'No accessibility features are switched on for this site yet.', 'alyxa-a11y' ); ?></p>
			<?php endif; ?>

			<div class="alyxa__stopka">
				<button type="button" class="alyxa__reset" data-alyxa-reset>
					<?php esc_html_e( 'Reset all settings', 'alyxa-a11y' ); ?>
				</button>

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
	?>
	<li class="alyxa__pozycja">
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
		</button>

		<?php if ( $id_opisu ) : ?>
			<p class="alyxa__opis alyxa-tylko-czytnik" id="<?php echo esc_attr( $id_opisu ); ?>"><?php echo esc_html( $modul['opis'] ); ?></p>
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
		</div>

		<?php if ( $id_opisu ) : ?>
			<p class="alyxa__opis alyxa-tylko-czytnik" id="<?php echo esc_attr( $id_opisu ); ?>"><?php echo esc_html( $modul['opis'] ); ?></p>
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
			<p class="alyxa__opis" id="<?php echo esc_attr( $id_opisu ); ?>"><?php echo esc_html( $modul['opis'] ); ?></p>
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
