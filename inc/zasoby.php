<?php
/**
 * Zasoby frontendu: arkusz modulow, style panelu, skrypty.
 *
 * DWA ARKUSZE, NIE JEDEN.
 * panel.css to wyglad samego przycisku i panelu - jest zawsze taki sam,
 * nie zalezy od konfiguracji i ma sie odkladac w pamieci podrecznej
 * przegladarki na dlugo. Drugi arkusz sklejamy z plikow wlaczonych modulow
 * przy zapisie ustawien i wersjonujemy skrotem konfiguracji. Modul wylaczony
 * nie ma tam ani jednej reguly - nie jest przykryty inna regula, po prostu
 * go nie ma.
 *
 * DLACZEGO SKLEJANIE, A NIE PLIK NA MODUL.
 * Dziewietnascie modulow to dziewietnascie zadan HTTP, z ktorych kazde
 * niesie kilkaset bajtow tresci i kilkaset naglowkow. Sklejamy raz, przy
 * zapisie ustawien - czyli kilka razy w zyciu strony - zamiast przy kazdym
 * wyswietleniu.
 *
 * JEDEN PLIK JS, MODULY BRAMKOWANE KONFIGURACJA.
 * Tu decyzja jest odwrotna i celowo: kod przelacznikow to kilka kilobajtow
 * razem, wiec drugi potok sklejania kosztowalby wiecej niz oszczedza.
 * Skrypt dostaje z serwera liste slugow i sam nie wie nic wiecej.
 *
 * @package alyxa-a11y
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sciezka i adres katalogu na zbudowany arkusz.
 *
 * Arkusz idzie do uploads, a nie do katalogu wtyczki, bo katalog wtyczki
 * bywa niezapisywalny i znika przy aktualizacji, a uploads jest z definicji
 * zapisywalny i zostaje.
 *
 * @return array{sciezka: string, url: string}|null Null, gdy uploads sa niedostepne.
 */
function alyxa_katalog_arkusza() {
	$uploads = wp_upload_dir();

	if ( ! empty( $uploads['error'] ) ) {
		return null;
	}

	return array(
		'sciezka' => trailingslashit( $uploads['basedir'] ) . 'alyxa-a11y',
		'url'     => trailingslashit( $uploads['baseurl'] ) . 'alyxa-a11y',
	);
}

/**
 * Skleja arkusz wlaczonych modulow.
 *
 * Gdy zapis pliku sie nie uda - a na wspoldzielonym hostingu potrafi -
 * tresc laduje w opcji i zostanie wypisana w dokumencie. Strona ma dzialac
 * na hostingu, ktory nie pozwala pisac; wolniej, ale ma dzialac.
 *
 * @return array{skrot: string, plik: string, inline: string} Stan arkusza.
 */
function alyxa_zbuduj_css() {
	$skrot = alyxa_skrot_konfiguracji();
	$css   = '';

	foreach ( alyxa_moduly_wlaczone() as $slug => $modul ) {
		if ( ! $modul['css'] || ! is_readable( $modul['css'] ) ) {
			continue;
		}

		$tresc = file_get_contents( $modul['css'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- odczyt pliku z wtyczki, nie zadanie sieciowe.

		if ( false === $tresc ) {
			continue;
		}

		$css .= "/* " . $slug . " */\n" . alyxa_przepisz_adresy( $tresc, $modul['css'] ) . "\n";
	}

	$stan = array(
		'skrot'  => $skrot,
		'plik'   => '',
		'inline' => '',
	);

	alyxa_usun_zbudowany_css();

	/* Zero wlaczonych modulow to poprawny stan, nie blad - tak wyglada faza 1. */
	if ( '' === trim( $css ) ) {
		update_option( ALYXA_A11Y_OPCJA_CSS, $stan, false );

		return $stan;
	}

	$katalog = alyxa_katalog_arkusza();

	if ( $katalog && alyxa_zapisz_plik( $katalog['sciezka'] . '/moduly-' . $skrot . '.css', $css ) ) {
		$stan['plik'] = 'moduly-' . $skrot . '.css';
	} else {
		$stan['inline'] = $css;
	}

	update_option( ALYXA_A11Y_OPCJA_CSS, $stan, false );

	return $stan;
}

/**
 * Przepisuje wzgledne adresy w arkuszu modulu na bezwzgledne.
 *
 * Sklejona calosc lezy w uploads, a nie w katalogu modulu, wiec url()
 * wskazujacy plik lezacy obok arkusza trafialby w zle miejsce. Przy zapisie
 * awaryjnym - gdy arkusz idzie wprost do dokumentu - liczylby sie od adresu
 * strony, czyli w jeszcze inne. Przepisujemy raz, przy budowaniu.
 *
 * Dzieki temu plik CSS modulu zostaje zwyklym plikiem CSS: mozna go otworzyc,
 * podlaczyc bezposrednio albo podmienic z motywu i wszedzie zachowa sie tak
 * samo. Alternatywa - wlasny znacznik w rodzaju %URL% podmieniany przy
 * sklejaniu - odebralaby mu te wlasnosc.
 *
 * @param string $css  Tresc arkusza modulu.
 * @param string $plik Sciezka pliku, z ktorego pochodzi.
 * @return string
 */
function alyxa_przepisz_adresy( $css, $plik ) {
	$baza = alyxa_url_katalogu( dirname( $plik ) );

	if ( '' === $baza ) {
		return $css;
	}

	/*
	 * Nie ruszamy tego, co juz jest bezwzgledne: adresu ze schematem (http:,
	 * data:), zaczynajacego sie od // albo od /, oraz odwolania do elementu
	 * tego samego dokumentu - url(#id) wskazuje maske albo filtr SVG,
	 * a doklejenie katalogu zepsuloby grafike.
	 */
	return (string) preg_replace_callback(
		'#url\(\s*([\'"]?)(?![a-z][a-z0-9+.-]*:|//|/|\#)([^\'")]+)\1\s*\)#i',
		static function ( $trafienie ) use ( $baza ) {
			$adres = $baza . '/' . trim( $trafienie[2] );

			/*
			 * Skracamy /katalog/../ do niczego. Przegladarka zrobilaby to sama,
			 * ale adres w zbudowanym arkuszu ma sie dac przeczytac - to jedyne
			 * miejsce, w ktorym widac, dokad naprawde wskazuje url() z modulu.
			 */
			do {
				$adres = (string) preg_replace( '#/[^/]+/\.\./#', '/', $adres, 1, $ile );
			} while ( $ile );

			return 'url(' . $trafienie[1] . $adres . $trafienie[1] . ')';
		},
		$css
	);
}

/**
 * Adres URL katalogu wskazanego sciezka na dysku.
 *
 * Nie plugins_url(): arkusz modulu moze pochodzic z motywu, ktory podmienil
 * go filtrem alyxa_moduly. Idziemy wiec od wp-content, bo pod nim leza
 * i wtyczki, i motywy, i uploads.
 *
 * Instalacja z katalogiem wtyczek wyniesionym poza wp-content i poza korzen
 * WordPressa dostanie pusty ciag, a adresy zostana nietkniete. To celowe:
 * sciezka wzgledna, ktora nie zadziala, jest lepsza od bezwzglednej, ktora
 * prowadzi w cudze miejsce.
 *
 * @param string $sciezka Sciezka katalogu.
 * @return string Adres bez ukosnika na koncu albo pusty ciag.
 */
function alyxa_url_katalogu( $sciezka ) {
	$sciezka = wp_normalize_path( $sciezka );

	$korzenie = array(
		array( wp_normalize_path( WP_CONTENT_DIR ), content_url() ),
		array( wp_normalize_path( ABSPATH ), site_url() ),
	);

	foreach ( $korzenie as $para ) {
		$korzen = untrailingslashit( $para[0] );

		if ( '' !== $korzen && 0 === strpos( $sciezka, $korzen . '/' ) ) {
			return untrailingslashit( $para[1] ) . substr( $sciezka, strlen( $korzen ) );
		}
	}

	return '';
}

/**
 * Zapisuje plik przez warstwe plikow WordPressa.
 *
 * Nie file_put_contents: na instalacjach z FTP albo z ograniczeniami
 * uprawnien bezposredni zapis albo nie zadziala, albo zostawi plik
 * z wlascicielem, ktorego serwer WWW nie odczyta.
 *
 * @param string $sciezka Bezwzgledna sciezka pliku.
 * @param string $tresc   Tresc do zapisania.
 * @return bool
 */
function alyxa_zapisz_plik( $sciezka, $tresc ) {
	global $wp_filesystem;

	require_once ABSPATH . 'wp-admin/includes/file.php';

	if ( 'direct' !== get_filesystem_method() ) {
		return false;
	}

	if ( ! WP_Filesystem() ) {
		return false;
	}

	$katalog = dirname( $sciezka );

	if ( ! $wp_filesystem->is_dir( $katalog ) && ! wp_mkdir_p( $katalog ) ) {
		return false;
	}

	return $wp_filesystem->put_contents( $sciezka, $tresc, FS_CHMOD_FILE );
}

/**
 * Kasuje wszystkie zbudowane arkusze.
 *
 * Kasujemy wzorcem, a nie po nazwie z opcji: gdyby opcja rozjechala sie
 * z zawartoscia katalogu - po przywroceniu kopii bazy, po recznej edycji -
 * zostalyby tam pliki, ktorych juz nic nie sprzata.
 *
 * @return void
 */
function alyxa_usun_zbudowany_css() {
	$katalog = alyxa_katalog_arkusza();

	if ( ! $katalog || ! is_dir( $katalog['sciezka'] ) ) {
		return;
	}

	foreach ( (array) glob( $katalog['sciezka'] . '/moduly-*.css' ) as $plik ) {
		wp_delete_file( $plik );
	}
}

/**
 * Zwraca aktualny stan arkusza, budujac go, gdy zdezaktualizowal sie po cichu.
 *
 * Po cichu dezaktualizuje sie przy aktualizacji wtyczki i przy zmianie
 * rejestru filtrem - czyli wtedy, gdy nikt nie zapisywal ustawien, a lista
 * modulow mimo to jest inna niz w chwili budowania.
 *
 * @return array{skrot: string, plik: string, inline: string}
 */
function alyxa_stan_arkusza() {
	$stan  = get_option( ALYXA_A11Y_OPCJA_CSS, array() );
	$skrot = alyxa_skrot_konfiguracji();

	if ( ! is_array( $stan ) || ( $stan['skrot'] ?? '' ) !== $skrot ) {
		return alyxa_zbuduj_css();
	}

	return wp_parse_args(
		$stan,
		array(
			'skrot'  => $skrot,
			'plik'   => '',
			'inline' => '',
		)
	);
}

/**
 * Czy panel ma sie w ogole pojawic na tej odslonie.
 *
 * @return bool
 */
function alyxa_pokaz_panel() {
	/**
	 * Filtruje decyzje o wyswietleniu panelu.
	 *
	 * @since 0.1.0
	 *
	 * @param bool $pokaz Czy wyswietlic panel.
	 */
	return (bool) apply_filters( 'alyxa_pokaz_panel', ! is_admin() && ! is_feed() && ! is_embed() );
}

/**
 * Style i skrypty panelu.
 *
 * @return void
 */
function alyxa_zasoby() {
	if ( ! alyxa_pokaz_panel() ) {
		return;
	}

	wp_enqueue_style(
		'alyxa-panel',
		ALYXA_A11Y_URL . 'assets/css/panel.css',
		array(),
		(string) filemtime( ALYXA_A11Y_KATALOG . 'assets/css/panel.css' )
	);

	wp_add_inline_style( 'alyxa-panel', alyxa_zmienne_css() );

	$stan = alyxa_stan_arkusza();

	if ( $stan['plik'] ) {
		$katalog = alyxa_katalog_arkusza();

		wp_enqueue_style(
			'alyxa-moduly',
			$katalog['url'] . '/' . $stan['plik'],
			array( 'alyxa-panel' ),
			$stan['skrot']
		);
	} elseif ( $stan['inline'] ) {
		wp_add_inline_style( 'alyxa-panel', $stan['inline'] );
	}

	wp_enqueue_script(
		'alyxa-panel',
		ALYXA_A11Y_URL . 'assets/js/panel.js',
		array(),
		(string) filemtime( ALYXA_A11Y_KATALOG . 'assets/js/panel.js' ),
		true
	);

	wp_add_inline_script( 'alyxa-panel', 'var alyxaUstawienia = ' . wp_json_encode( alyxa_dane_dla_skryptu() ) . ';', 'before' );
}
add_action( 'wp_enqueue_scripts', 'alyxa_zasoby' );

/**
 * Zmienne CSS wynikajace z konfiguracji strony.
 *
 * Tylko to, co redaktor moze zmienic na ekranie ustawien. Reszta - kolory,
 * odstepy, promienie - siedzi w panel.css z lancuchem wartosci zapasowych
 * siegajacym presetow motywu.
 *
 * @return string
 */
function alyxa_zmienne_css() {
	$konfiguracja = alyxa_konfiguracja();

	$linie = array( '--alyxa-odstep: ' . (int) $konfiguracja['odstep'] . 'px;' );

	if ( $konfiguracja['akcent'] ) {
		$linie[] = '--alyxa-akcent: ' . $konfiguracja['akcent'] . ';';
	}

	return ':root{' . implode( '', $linie ) . '}';
}

/**
 * Dane przekazywane skryptowi.
 *
 * Skrypt nie zna zadnego modulu z nazwy. Dostaje slug, typ i liczbe stopni,
 * i to wystarcza, zeby obsluzyc kazdy przelacznik, ktory kiedykolwiek
 * dopiszemy do rejestru.
 *
 * KLUCZ 'dane' JEDZIE NIEPRZECZYTANY. Modul, ktory ma po stronie skryptu
 * wlasne zachowanie, potrzebuje czasem czegos, o czym rdzen nie ma pojecia:
 * odczyt strony chce jezyka i selektora obszaru do czytania. Rdzen podaje
 * te tablice dalej i nigdzie do niej nie zaglada - inaczej kazdy nowy modul
 * dopisywalby sie takze tutaj, a caly sens rejestru polega na tym, ze nie
 * dopisuje sie nigdzie indziej.
 *
 * @return array<string, mixed>
 */
function alyxa_dane_dla_skryptu() {
	$moduly = array();

	foreach ( alyxa_moduly_wlaczone() as $slug => $modul ) {
		$moduly[ $slug ] = array(
			'typ'     => $modul['typ'],
			'stopnie' => $modul['stopnie'],
		);

		if ( $modul['obieg'] ) {
			$moduly[ $slug ]['obieg'] = true;
		}

		/*
		 * Etykiety stopni: procenty przy kontrolce rozmiaru tekstu. Wpisuje
		 * je skrypt, a nie serwer, bo zmieniaja sie przy kazdym nacisnieciu.
		 */
		if ( $modul['etykiety'] ) {
			$moduly[ $slug ]['etykiety'] = $modul['etykiety'];
		}

		if ( $modul['dane'] ) {
			$moduly[ $slug ]['dane'] = $modul['dane'];
		}

		/*
		 * Modul kolorow: lista pol i gotowe palety. Skrypt potrzebuje ich,
		 * zeby odsiac z pamieci przegladarki pole, ktorego modul nie zna,
		 * i zeby nacisniecie palety przepisalo wszystkie pola naraz.
		 */
		if ( 'kolory' === $modul['typ'] ) {
			$moduly[ $slug ]['pola'] = array_keys( $modul['pola'] );
			$moduly[ $slug ]['pary'] = array_map(
				static function ( $para ) {
					unset( $para['nazwa'] );

					return $para;
				},
				$modul['pary']
			);
		}
	}

	return array(
		'klucz'  => ALYXA_A11Y_KLUCZ,
		'moduly' => $moduly,
		'teksty' => array(
			/* translators: 1: current level, 2: number of levels. */
			'stopien'   => __( 'level %1$d of %2$d', 'alyxa-a11y' ),
			'wylaczone' => __( 'off', 'alyxa-a11y' ),
			/* translators: 1: name of a colour field, for example Links; 2: contrast ratio, for example 7.2. */
			'kontrast'  => __( '%1$s: contrast %2$s:1', 'alyxa-a11y' ),
			/* translators: %s: name of the colour field, or several of them. */
			'ponizej'   => __( 'Below 4.5:1, the WCAG minimum for text: %s.', 'alyxa-a11y' ),
			'dosc'      => __( 'Every pair reaches at least 4.5:1.', 'alyxa-a11y' ),
		),
	);
}

/**
 * Skrypt ustawiajacy klasy przed pierwszym rysowaniem strony.
 *
 * DLACZEGO W NAGLOWKU I DLACZEGO W DOKUMENCIE, A NIE W PLIKU.
 * Odwiedzajacy z wlaczonym wysokim kontrastem wchodzi na strone i przez
 * ulamek sekundy widzi ja w kolorach domyslnych, zanim skrypt ze stopki
 * zdazy dolozyc klase. Dla kogos, kto wlaczyl kontrast dlatego, ze inaczej
 * nie czyta, ten blysk nie jest drobiazgiem. Osobny plik nie pomoze - musi
 * byc pobrany, a to znowu czas. Stad kilkanascie wierszy w dokumencie,
 * wykonywanych zanim przegladarka narysuje cokolwiek.
 *
 * Nazwy klas budujemy tu drugi raz, obok panel.js. To swiadome powtorzenie:
 * regula jest czterowierszowa, a alternatywa - wspolny plik - kasuje caly
 * sens tego rozwiazania.
 *
 * @return void
 */
function alyxa_skrypt_w_glowie() {
	if ( ! alyxa_pokaz_panel() ) {
		return;
	}

	$js = "(function(){try{var p=window.localStorage.getItem(" . wp_json_encode( ALYXA_A11Y_KLUCZ ) . ");"
		. "if(!p){return;}var s=JSON.parse(p),e=document.documentElement,k,w,p,d=[];"
		/* Klucz idzie prosto do nazwy klasy, wiec przepuszczamy tylko to, co sami zapisujemy. */
		. "for(k in s){if(!Object.prototype.hasOwnProperty.call(s,k)||!/^[a-z0-9-]+$/.test(k)){continue;}"
		. "w=s[k];if(w===true){d.push('alyxa-'+k);}"
		. "else if(typeof w==='number'&&w>0){d.push('alyxa-'+k+'-'+Math.floor(w));}"
		/*
		 * Modul kolorow: obiekt pol. Zmienne ustawiamy tutaj, a nie dopiero
		 * w stopce, z tego samego powodu co klasy - inaczej strona blysnelaby
		 * w kolorach motywu przed kolorami odwiedzajacego.
		 */
		. "else if(w&&typeof w==='object'){d.push('alyxa-'+k);for(p in w){if(Object.prototype.hasOwnProperty.call(w,p)&&/^[a-z0-9-]+$/.test(p)&&/^#[0-9a-f]{6}$/i.test(w[p])){e.style.setProperty('--alyxa-'+k+'-'+p,w[p]);}}}}"
		. "if(d.length){e.className+=' '+d.join(' ');}}catch(b){}}());";

	wp_print_inline_script_tag( $js, array( 'id' => 'alyxa-stan' ) );
}
add_action( 'wp_head', 'alyxa_skrypt_w_glowie', 1 );
