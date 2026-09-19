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
 *     'typ'       (string)  'przelacznik' (wlacz/wylacz), 'stopnie' (0..n),
 *                           'akcje' (przyciski robiace cos tu i teraz),
 *                           'link' (odnosnik, ktory wyprowadza ze strony) albo
 *                           'kolory' (wlasna paleta odwiedzajacego)
 *     'stopnie'   (int)     liczba stopni dla typu 'stopnie', domyslnie 3
 *     'etykiety'  (array)   nazwy stopni widoczne przy kontrolce, indeks 0 = wylaczony
 *     'obieg'     (bool)    tylko dla typu 'stopnie': plus na ostatnim stopniu
 *                           wraca na zero zamiast stac w miejscu. Dla skali
 *                           wielkosci ma byc falszem - droga powrotna wiodlaby
 *                           przez powiekszenie jeszcze wieksze niz to, ktore
 *                           komus wlasnie przeszkodzilo. Dla zestawu rownorzednych
 *                           ustawien, jak wyrownanie tekstu, ma byc prawda: tam
 *                           nie ma "wiecej" ani "mniej", jest kilka opcji po kole
 *     'ikona'     (string)  nazwa rysunku z inc/ikony.php, opcjonalna
 *     'ikony'     (array)   tylko dla stopni z obiegiem: osobny rysunek dla
 *                           kazdego stopnia, indeks 0 = stan wyjsciowy. Pusty
 *                           wpis bierze rysunek z klucza 'ikona'. Kafelek
 *                           chodzacy w kolko pokazuje naraz jeden stopien,
 *                           wiec rysunek jest czescia odpowiedzi na pytanie
 *                           "co jest teraz ustawione"
 *     'grupa'     (string)  dzial na ekranie ustawien, patrz alyxa_grupy();
 *                           nieznana albo pusta laduje w dziale "pozostale"
 *     'akcje'     (array)   dla typu 'akcje': lista par slug + nazwa przycisku
 *     'dane'      (array)   dowolne wartosci dla zachowania modulu w skrypcie;
 *                           rdzen ich nie czyta, tylko podaje dalej
 *     'warunkowy' (bool)    pozycja wychodzi z serwera ukryta i pokazuje ja
 *                           dopiero zachowanie, gdy urzadzenie to potrafi
 *     'css'       (string)  bezwzgledna sciezka do arkusza modulu, opcjonalna
 *     'domyslnie' (bool)    czy modul jest wlaczony na nowej instalacji
 *     'kolejnosc' (int)     pozycja na liscie, mniejsza liczba wyzej
 *     'zgodnosc'  (array)   stosunek modulu do WCAG, patrz nizej
 *     'pola'      (array)   dla typu 'kolory': slug pola => nazwa pola
 *     'pary'      (array)   dla typu 'kolory': gotowe palety, kazda z kluczem
 *                           'nazwa' i kolorem #rrggbb dla kazdego pola
 *
 * KLUCZ 'zgodnosc' MOWI, JAK MODUL MA SIE DO WCAG - I MOWI TO GLOSNO.
 * Wtyczka jest dla kazdej strony, wiec nie ogranicza sie do tego, co
 * wytyczne zalecaja; ale modul, ktory wychodzi poza nie albo moze cos
 * pogorszyc, ma to powiedziec sam, zamiast liczyc na to, ze ktos przeczyta
 * komentarz w kodzie. Trzy klucze:
 *
 *     'ocena'    'wspiera' - realizuje konkretne kryterium,
 *                'poza'    - WCAG tego nie dotyczy, niczego nie psuje,
 *                'ryzyko'  - moze pogorszyc cos, co WCAG mierzy
 *     'kryteria' numery kryteriow, np. '1.4.8 (AAA)'
 *     'uwaga'    dla oceny 'ryzyko': jedno zdanie, CO konkretnie pogarsza
 *
 * Ocene widac na ekranie ustawien jako plakietke, a przy 'ryzyko' takze
 * w panelu: znak ostrzegawczy na kafelku, uwaga w opisie czytanym przez
 * czytnik ekranu i jedno zdanie objasnienia pod kafelkami. Modul bez klucza
 * nie dostaje plakietki - brak oceny to nie jest ocena "poza".
 *
 * TYP 'kolory' MA STAN BEDACY OBIEKTEM: { tekst: '#rrggbb', tlo: ... }.
 * Na <html> laduje klasa alyxa-<slug> i zmienna --alyxa-<slug>-<pole> dla
 * kazdego pola; arkusz modulu siega po zmienne. Pole o slugu 'tlo' jest
 * tlem - wzgledem niego panel liczy kontrast pozostalych pol.
 *
 * KLASY NA <html> POWSTAJA Z SLUGA, NIE Z OSOBNEGO POLA. Modul 'kontrast'
 * o typie przelacznik daje klase alyxa-kontrast, modul 'tekst' o typie
 * stopnie daje alyxa-tekst-1, alyxa-tekst-2, alyxa-tekst-3. Dzieki temu
 * skrypt w naglowku, ktory ustawia klasy przed pierwszym rysowaniem strony,
 * nie musi znac zadnej mapy - wystarcza mu klucze z pamieci przegladarki.
 *
 * TYP 'link' TEZ NIE MA STANU, I NIE MA NAWET ZACHOWANIA. Odnosnik do
 * deklaracji dostepnosci nie wlacza niczego na tej stronie - wyprowadza
 * z niej. Adres siedzi w tablicy 'dane' pod kluczem 'adres'; gdy jest pusty,
 * panel nie rysuje nic, bo odnosnik donikad jest gorszy niz jego brak.
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
			'obieg'     => false,
			'ikona'     => '',
			'ikony'     => array(),
			'grupa'     => '',
			'akcje'     => array(),
			'dane'      => array(),
			'warunkowy' => false,
			'css'       => '',
			'domyslnie' => false,
			'kolejnosc' => 10,
			'zgodnosc'  => array(),
			'pola'      => array(),
			'pary'      => array(),
		)
	);

	$modul['slug']      = $slug;
	$modul['typ']       = in_array( $modul['typ'], array( 'stopnie', 'akcje', 'link', 'kolory' ), true ) ? $modul['typ'] : 'przelacznik';
	$modul['stopnie']   = 'stopnie' === $modul['typ'] ? max( 1, (int) $modul['stopnie'] ) : 0;
	$modul['akcje']     = 'akcje' === $modul['typ'] ? alyxa_sprawdz_akcje( $modul['akcje'] ) : array();
	$modul['etykiety']  = is_array( $modul['etykiety'] ) ? array_values( array_map( 'strval', $modul['etykiety'] ) ) : array();
	$modul['obieg']     = 'stopnie' === $modul['typ'] && ! empty( $modul['obieg'] );
	$modul['ikona']     = is_string( $modul['ikona'] ) ? sanitize_key( $modul['ikona'] ) : '';
	$modul['ikony']     = alyxa_sprawdz_ikony( $modul['ikony'] );
	$modul['grupa']     = is_string( $modul['grupa'] ) ? sanitize_key( $modul['grupa'] ) : '';
	$modul['dane']      = is_array( $modul['dane'] ) ? $modul['dane'] : array();
	$modul['warunkowy'] = (bool) $modul['warunkowy'];
	$modul['domyslnie'] = (bool) $modul['domyslnie'];
	$modul['kolejnosc'] = (int) $modul['kolejnosc'];
	$modul['zgodnosc']  = alyxa_sprawdz_zgodnosc( $modul['zgodnosc'] );
	$modul['pola']      = 'kolory' === $modul['typ'] ? alyxa_sprawdz_pola( $modul['pola'] ) : array();
	$modul['pary']      = 'kolory' === $modul['typ'] ? alyxa_sprawdz_pary( $modul['pary'], $modul['pola'] ) : array();

	/*
	 * Paleta bez ani jednej gotowej pary nie ma wartosci poczatkowych dla
	 * pol wyboru koloru - a pole koloru bez wartosci pokazuje czern, ktora
	 * wygladalaby jak wybor, choc nikt go nie zrobil.
	 */
	if ( 'kolory' === $modul['typ'] && ( ! $modul['pola'] || ! $modul['pary'] ) ) {
		_doing_it_wrong(
			__FUNCTION__,
			'Modul panelu Alyxa o typie kolory musi miec pola i co najmniej jedna gotowa pare.',
			'1.3.0'
		);

		return null;
	}

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
 * Sprawdza ocene zgodnosci z WCAG.
 *
 * Nieznana ocena wypada w calosci, a nie zamienia sie w 'poza': modul,
 * ktory sie pomylil w slowie, nie powinien dostac plakietki mowiacej
 * "niczego nie psuje", skoro nikt tego nie stwierdzil.
 *
 * @param mixed $zgodnosc Tablica z rejestru.
 * @return array{ocena?: string, kryteria?: string, uwaga?: string}
 */
function alyxa_sprawdz_zgodnosc( $zgodnosc ) {
	if ( ! is_array( $zgodnosc ) || empty( $zgodnosc['ocena'] ) ) {
		return array();
	}

	if ( ! in_array( $zgodnosc['ocena'], array( 'wspiera', 'poza', 'ryzyko' ), true ) ) {
		return array();
	}

	return array(
		'ocena'    => $zgodnosc['ocena'],
		'kryteria' => isset( $zgodnosc['kryteria'] ) ? sanitize_text_field( (string) $zgodnosc['kryteria'] ) : '',
		'uwaga'    => isset( $zgodnosc['uwaga'] ) ? sanitize_text_field( (string) $zgodnosc['uwaga'] ) : '',
	);
}

/**
 * Sprawdza pola modulu kolorow.
 *
 * Slug pola trafia do nazwy zmiennej CSS, wiec przepuszczamy ten sam zestaw
 * znakow co przy slugu modulu.
 *
 * @param mixed $pola Slug pola => nazwa.
 * @return array<string, string>
 */
function alyxa_sprawdz_pola( $pola ) {
	$czyste = array();

	if ( ! is_array( $pola ) ) {
		return $czyste;
	}

	foreach ( $pola as $slug => $nazwa ) {
		$slug = sanitize_key( (string) $slug );

		if ( '' !== $slug && is_string( $nazwa ) && '' !== $nazwa ) {
			$czyste[ $slug ] = $nazwa;
		}
	}

	return $czyste;
}

/**
 * Sprawdza gotowe palety modulu kolorow.
 *
 * Para, ktorej brakuje koloru dla ktoregokolwiek pola, wypada w calosci.
 * Uzupelnianie jej czymkolwiek dawaloby palete, ktorej nikt nie ulozyl -
 * i ktorej kontrastu nikt nie sprawdzil.
 *
 * @param mixed                 $pary Lista palet.
 * @param array<string, string> $pola Pola modulu, juz sprawdzone.
 * @return array<int, array<string, string>>
 */
function alyxa_sprawdz_pary( $pary, array $pola ) {
	$czyste = array();

	if ( ! is_array( $pary ) ) {
		return $czyste;
	}

	foreach ( $pary as $para ) {
		if ( ! is_array( $para ) || empty( $para['nazwa'] ) || ! is_string( $para['nazwa'] ) ) {
			continue;
		}

		$wynik = array( 'nazwa' => $para['nazwa'] );

		foreach ( array_keys( $pola ) as $pole ) {
			$kolor = isset( $para[ $pole ] ) ? sanitize_hex_color( (string) $para[ $pole ] ) : '';

			/* Tylko pelny zapis szesciocyfrowy - taki przyjmuje pole koloru. */
			if ( ! $kolor || 7 !== strlen( $kolor ) ) {
				continue 2;
			}

			$wynik[ $pole ] = strtolower( $kolor );
		}

		$czyste[] = $wynik;
	}

	return $czyste;
}

/**
 * Sprawdza liste rysunkow stopni.
 *
 * Nazwy ida do alyxa_ikona(), ktora rysunku o nieznanej nazwie po prostu nie
 * wypisze - wiec zla nazwa kosztuje pusty kafelek, a nie bledny znacznik.
 * Mimo to przepuszczamy je przez sanitize_key, bo klucz z rejestru moze
 * pochodzic z filtra w cudzym motywie.
 *
 * @param mixed $ikony Lista nazw rysunkow, indeks = stopien.
 * @return array<int, string>
 */
function alyxa_sprawdz_ikony( $ikony ) {
	if ( ! is_array( $ikony ) ) {
		return array();
	}

	$czyste = array();

	foreach ( array_values( $ikony ) as $ikona ) {
		$czyste[] = is_string( $ikona ) ? sanitize_key( $ikona ) : '';
	}

	return $czyste;
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
 * Dzialy, na ktore rozpada sie lista modulow.
 *
 * DZIALY SA TERAZ TAKZE W PANELU - zmiana wobec fazy 8. Wtedy modulow bylo
 * dziesiec, siatka kafelkow byla czytelna bez naglowkow, a kazdy naglowek
 * liczyl sie jako kolejny przystanek dla czytnika ekranu miedzy odwiedzajacym
 * a przelacznikiem, ktorego szuka. Katalog opcjonalny z fazy 9 podnosi
 * mozliwa liczbe kafelkow do dziewietnastu i ten rachunek sie odwraca:
 * dziewietnascie kafelkow bez podzialu to jeden ciag, po ktorym trzeba isc
 * do konca, a naglowki dzialow sa dla czytnika ekranu skokami, nie
 * przeszkodami.
 *
 * DZIALY SA W PANELU ZAWSZE, TAKZE PRZY DZIESIECIU MODULACH. Regula
 * zalezna od liczby wlaczonych modulow dawalaby dwie rozne budowy panelu
 * na dwoch stronach tej samej wtyczki - a odwiedzajacy uczy sie jednej.
 *
 * Kolejnosc tej tablicy jest kolejnoscia dzialow i na ekranie ustawien,
 * i w panelu.
 *
 * @return array<string, string> Slug dzialu => nazwa dzialu.
 */
function alyxa_grupy() {
	return array(
		'tekst'    => __( 'Text and reading', 'alyxa-a11y' ),
		'kolor'    => __( 'Colour and contrast', 'alyxa-a11y' ),

		/* Wyciszanie i chowanie: moduly, ktore czegos ubywaja, nie dokladaja. */
		'spokoj'   => __( 'Fewer distractions', 'alyxa-a11y' ),

		'wskaznik' => __( 'Pointer and reading guides', 'alyxa-a11y' ),

		/* Nie "Reading aloud": tak nazywa sie modul, ktory w tym dziale lezy. */
		'glos'     => __( 'Speech', 'alyxa-a11y' ),

		'pomoc'    => __( 'Help', 'alyxa-a11y' ),
	);
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
