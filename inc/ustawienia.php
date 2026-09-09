<?php
/**
 * Ekran ustawien w kokpicie.
 *
 * TYLKO ADMINISTRATOR - decyzja klienta, nie nasza ostroznosc. Redaktor
 * szkolny pisze tresc; to, ktore udogodnienia strona w ogole oferuje, jest
 * decyzja o calej witrynie i zapada raz. Uprawnienie sprawdzamy DWA RAZY:
 * przy dokladaniu pozycji do menu i jeszcze raz w funkcji zapisujacej.
 * Ukrycie pozycji w menu nie jest zabezpieczeniem - adres ekranu i tak da
 * sie wpisac recznie, a zadanie zapisujace da sie wyslac bez zadnego ekranu.
 *
 * POD "USTAWIENIAMI", A NIE JAKO WLASNA POZYCJA NA GORZE MENU. Wtyczka
 * z jednym ekranem, na ktory zaglada sie raz na kwartal, nie ma czego
 * szukac miedzy Wpisami a Mediami. Menu kokpitu jest wspolnym zasobem
 * wszystkich wtyczek i kazda, ktora sie tam wpycha, zabiera miejsce
 * czynnosciom wykonywanym codziennie.
 *
 * FORMULARZ IDZIE DO admin-post.php, A NIE PRZEZ API USTAWIEN.
 * API ustawien zapisuje opcje samo, wiec caly zapis musialby sie miescic
 * w wywolaniu oczyszczajacym - a my mamy tu do zrobienia jedna rzecz wiecej:
 * zestaw startowy nadpisuje liste modulow, ktora przyszla z pol wyboru.
 * Wlasny odbiornik z jednym sprawdzeniem uprawnien i jednym jednorazowym
 * kluczem jest krotszy i widac w nim cala droge danych.
 *
 * @package alyxa-a11y
 */

defined( 'ABSPATH' ) || exit;

/**
 * Uprawnienie wymagane do ogladania i zmiany ustawien.
 */
const ALYXA_A11Y_UPRAWNIENIE = 'manage_options';

/**
 * Slug ekranu ustawien.
 */
const ALYXA_A11Y_EKRAN = 'alyxa-a11y';

/**
 * Dopisuje ekran do menu "Ustawienia".
 *
 * @return void
 */
function alyxa_menu_ustawien() {
	alyxa_hak_ekranu(
		add_options_page(
			__( 'Alyxa Accessibility', 'alyxa-a11y' ),
			__( 'Accessibility', 'alyxa-a11y' ),
			ALYXA_A11Y_UPRAWNIENIE,
			ALYXA_A11Y_EKRAN,
			'alyxa_ekran_ustawien'
		)
	);
}
add_action( 'admin_menu', 'alyxa_menu_ustawien' );

/**
 * Pamieta identyfikator ekranu nadany przez WordPressa.
 *
 * BIERZEMY GO Z add_options_page, A NIE SKLADAMY Z SLUGA. Identyfikator
 * zalezy od tego, pod czym ekran zawisl - dla menu "Ustawienia" wychodzi
 * settings_page_<slug>, ale zlozony recznie warunek przestaje pasowac
 * w chwili, gdy ekran przeniesie sie gdzie indziej albo gdy cudza wtyczka
 * przestawi menu. Arkusz przestalby sie wtedy wczytywac po cichu.
 *
 * @param string|null $nowy Identyfikator do zapamietania.
 * @return string
 */
function alyxa_hak_ekranu( $nowy = null ) {
	static $hak = '';

	if ( null !== $nowy ) {
		$hak = (string) $nowy;
	}

	return $hak;
}

/**
 * Adres ekranu ustawien.
 *
 * @param array<string, string> $argumenty Dodatkowe parametry adresu.
 * @return string
 */
function alyxa_adres_ustawien( array $argumenty = array() ) {
	return add_query_arg(
		array_merge( array( 'page' => ALYXA_A11Y_EKRAN ), $argumenty ),
		admin_url( 'options-general.php' )
	);
}

/**
 * Doklada odnosnik "Ustawienia" na liscie wtyczek.
 *
 * Pierwsze miejsce, w ktorym ktos szuka ustawien wtyczki, to wiersz wtyczki -
 * a nie menu, w ktorym trzeba najpierw zgadnac, pod czym ja schowano.
 *
 * @param array<int, string> $linki Odnosniki przy wtyczce.
 * @return array<int, string>
 */
function alyxa_link_do_ustawien( $linki ) {
	if ( ! current_user_can( ALYXA_A11Y_UPRAWNIENIE ) ) {
		return $linki;
	}

	array_unshift(
		$linki,
		'<a href="' . esc_url( alyxa_adres_ustawien() ) . '">' . esc_html__( 'Settings', 'alyxa-a11y' ) . '</a>'
	);

	return $linki;
}
add_filter( 'plugin_action_links_' . plugin_basename( ALYXA_A11Y_PLIK ), 'alyxa_link_do_ustawien' );

/**
 * Arkusz ekranu ustawien.
 *
 * Wczytywany wylacznie na naszym ekranie. Kokpit jest wspolny dla wszystkich
 * wtyczek i kazda regula dolozona globalnie potrafi popsuc cudzy ekran.
 *
 * @param string $hak Identyfikator biezacego ekranu.
 * @return void
 */
function alyxa_zasoby_ustawien( $hak ) {
	if ( ! alyxa_hak_ekranu() || alyxa_hak_ekranu() !== $hak ) {
		return;
	}

	wp_enqueue_style(
		'alyxa-a11y-ustawienia',
		ALYXA_A11Y_URL . 'assets/css/ustawienia.css',
		array(),
		ALYXA_A11Y_WERSJA
	);
}
add_action( 'admin_enqueue_scripts', 'alyxa_zasoby_ustawien' );

/**
 * Odbiera formularz ustawien.
 *
 * @return void
 */
function alyxa_odbierz_ustawienia() {
	if ( ! current_user_can( ALYXA_A11Y_UPRAWNIENIE ) ) {
		wp_die( esc_html__( 'You are not allowed to change accessibility settings on this site.', 'alyxa-a11y' ), '', array( 'response' => 403 ) );
	}

	check_admin_referer( 'alyxa_ustawienia' );

	$konfiguracja = alyxa_konfiguracja();
	$zestaw       = isset( $_POST['alyxa_zestaw'] ) ? sanitize_key( wp_unslash( $_POST['alyxa_zestaw'] ) ) : '';
	$wybrane      = alyxa_zestaw_na_moduly( $zestaw );

	if ( null === $wybrane ) {
		/*
		 * CHODZIMY PO REJESTRZE, A NIE PO TYM, CO PRZYSZLO W ZADANIU.
		 * Pole wyboru, ktore nie jest zaznaczone, nie przychodzi w ogole -
		 * gdybysmy szli po zadaniu, wylaczenie modulu byloby nie do zapisania,
		 * bo brak wpisu znaczy w tej wtyczce "wez wartosc domyslna z rejestru".
		 * Przy okazji nic spoza rejestru tu nie wejdzie.
		 */
		$przyslane = isset( $_POST['alyxa_moduly'] ) && is_array( $_POST['alyxa_moduly'] ) ? array_map( 'sanitize_key', array_keys( wp_unslash( $_POST['alyxa_moduly'] ) ) ) : array();
		$wybrane   = array();

		foreach ( alyxa_rejestr() as $slug => $modul ) {
			$wybrane[ $slug ] = in_array( $slug, $przyslane, true );
		}
	}

	alyxa_zapisz_konfiguracje(
		array(
			'moduly' => $wybrane,
			'rog'    => isset( $_POST['alyxa_rog'] ) ? sanitize_key( wp_unslash( $_POST['alyxa_rog'] ) ) : $konfiguracja['rog'],
			'odstep' => isset( $_POST['alyxa_odstep'] ) ? (int) wp_unslash( $_POST['alyxa_odstep'] ) : $konfiguracja['odstep'],
			'akcent' => isset( $_POST['alyxa_akcent'] ) ? sanitize_text_field( wp_unslash( $_POST['alyxa_akcent'] ) ) : $konfiguracja['akcent'],
			'obszar' => isset( $_POST['alyxa_obszar'] ) ? sanitize_text_field( wp_unslash( $_POST['alyxa_obszar'] ) ) : $konfiguracja['obszar'],
		)
	);

	wp_safe_redirect( alyxa_adres_ustawien( array( 'alyxa-zapisano' => $zestaw ? $zestaw : '1' ) ) );

	exit;
}
add_action( 'admin_post_alyxa_zapisz', 'alyxa_odbierz_ustawienia' );

/**
 * Wypisuje ekran ustawien.
 *
 * @return void
 */
function alyxa_ekran_ustawien() {
	if ( ! current_user_can( ALYXA_A11Y_UPRAWNIENIE ) ) {
		return;
	}

	$konfiguracja = alyxa_konfiguracja();
	$rejestr      = alyxa_rejestr();
	$wlaczone     = alyxa_moduly_wlaczone();
	?>
	<div class="wrap alyxa-ustawienia">
		<h1><?php esc_html_e( 'Alyxa Accessibility', 'alyxa-a11y' ); ?></h1>

		<?php alyxa_komunikat_zapisu(); ?>

		<p class="alyxa-ustawienia__wstep">
			<?php esc_html_e( 'These settings decide what the panel offers on this site. What a visitor then switches on for themselves is kept in their own browser and never reaches the server.', 'alyxa-a11y' ); ?>
		</p>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="alyxa_zapisz">
			<?php wp_nonce_field( 'alyxa_ustawienia' ); ?>

			<?php alyxa_pola_modulow( $rejestr, $konfiguracja ); ?>
			<?php alyxa_pola_wygladu( $konfiguracja ); ?>
			<?php alyxa_pola_odczytu( $konfiguracja, $wlaczone ); ?>

			<?php
			/*
			 * ZAPIS MUSI STAC PRZED ZESTAWAMI I NIE JEST TO KWESTIA UKLADU.
			 * Enter wcisniety w polu tekstowym wysyla formularz tym przyciskiem,
			 * ktory stoi w dokumencie pierwszy. Gdyby byly to zestawy startowe,
			 * Enter w polu obszaru tresci przestawialby wszystkie moduly naraz.
			 */
			submit_button( __( 'Save settings', 'alyxa-a11y' ) );
			?>

			<?php alyxa_pola_zestawow(); ?>
		</form>

		<?php alyxa_stan_wtyczki( $wlaczone ); ?>
	</div>
	<?php
}

/**
 * Wypisuje komunikat po zapisie.
 *
 * @return void
 */
function alyxa_komunikat_zapisu() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- odczyt parametru adresu po przekierowaniu, bez zadnego skutku ubocznego.
	$zapisano = isset( $_GET['alyxa-zapisano'] ) ? sanitize_key( wp_unslash( $_GET['alyxa-zapisano'] ) ) : '';

	if ( ! $zapisano ) {
		return;
	}

	$zestawy = alyxa_zestawy();

	if ( isset( $zestawy[ $zapisano ] ) ) {
		$tresc = sprintf(
			/* translators: %s: name of the starter set that was applied. */
			__( 'Settings saved, with the modules from the "%s" set.', 'alyxa-a11y' ),
			$zestawy[ $zapisano ]['nazwa']
		);
	} else {
		$tresc = __( 'Settings saved.', 'alyxa-a11y' );
	}
	?>
	<div class="notice notice-success is-dismissible">
		<p><?php echo esc_html( $tresc ); ?></p>
	</div>
	<?php
}

/**
 * Wypisuje pola wyboru modulow, dzial po dziale.
 *
 * DZIALY, BO DZIESIEC POL WYBORU JEDNO POD DRUGIM TO NIE JEST WYBOR, TYLKO
 * SCIANA. Kazdy dzial jest osobnym fieldset z legenda, wiec czytnik ekranu
 * oglasza go przy wejsciu w pierwsze pole - i wiadomo, czego dotyczy reszta.
 *
 * @param array<string, array<string, mixed>> $rejestr      Wszystkie moduly.
 * @param array<string, mixed>                $konfiguracja Konfiguracja strony.
 * @return void
 */
function alyxa_pola_modulow( array $rejestr, array $konfiguracja ) {
	$grupy     = alyxa_grupy();
	$pozostale = __( 'Other modules', 'alyxa-a11y' );
	$wedlug    = array();

	foreach ( $rejestr as $slug => $modul ) {
		$grupa = isset( $grupy[ $modul['grupa'] ] ) ? $modul['grupa'] : '';

		$wedlug[ $grupa ][ $slug ] = $modul;
	}
	?>
	<h2><?php esc_html_e( 'Modules', 'alyxa-a11y' ); ?></h2>

	<p class="alyxa-ustawienia__opis">
		<?php esc_html_e( 'A module that is off leaves nothing behind: no tile in the panel, no CSS rule, no event listener. It is not hidden - it is not on the page.', 'alyxa-a11y' ); ?>
	</p>

	<?php if ( ! $rejestr ) : ?>
		<p><?php esc_html_e( 'No modules are registered. Something has removed them with the alyxa_moduly filter.', 'alyxa-a11y' ); ?></p>

		<?php
		return;
	endif;

	foreach ( array_merge( $grupy, array( '' => $pozostale ) ) as $grupa => $nazwa_grupy ) :
		if ( empty( $wedlug[ $grupa ] ) ) {
			continue;
		}
		?>
		<fieldset class="alyxa-dzial">
			<legend class="alyxa-dzial__tytul"><?php echo esc_html( $nazwa_grupy ); ?></legend>

			<?php foreach ( $wedlug[ $grupa ] as $slug => $modul ) : ?>
				<?php
				$id       = 'alyxa-modul-' . $slug;
				$id_opisu = $modul['opis'] ? $id . '-opis' : '';
				$czy      = array_key_exists( $slug, $konfiguracja['moduly'] ) ? (bool) $konfiguracja['moduly'][ $slug ] : $modul['domyslnie'];
				?>
				<div class="alyxa-modul">
					<input
						type="checkbox"
						class="alyxa-modul__pole"
						id="<?php echo esc_attr( $id ); ?>"
						name="alyxa_moduly[<?php echo esc_attr( $slug ); ?>]"
						value="1"
						<?php checked( $czy ); ?>
						<?php if ( $id_opisu ) : ?>
							aria-describedby="<?php echo esc_attr( $id_opisu ); ?>"
						<?php endif; ?>
					>

					<label class="alyxa-modul__nazwa" for="<?php echo esc_attr( $id ); ?>">
						<?php echo esc_html( $modul['nazwa'] ); ?>
					</label>

					<?php if ( $id_opisu ) : ?>
						<p class="alyxa-modul__opis" id="<?php echo esc_attr( $id_opisu ); ?>"><?php echo esc_html( $modul['opis'] ); ?></p>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</fieldset>
		<?php
	endforeach;
}

/**
 * Wypisuje pola wygladu przycisku.
 *
 * @param array<string, mixed> $konfiguracja Konfiguracja strony.
 * @return void
 */
function alyxa_pola_wygladu( array $konfiguracja ) {
	?>
	<h2><?php esc_html_e( 'The button', 'alyxa-a11y' ); ?></h2>

	<table class="form-table" role="presentation">
		<tr>
			<th scope="row">
				<label for="alyxa-rog"><?php esc_html_e( 'Corner', 'alyxa-a11y' ); ?></label>
			</th>
			<td>
				<select id="alyxa-rog" name="alyxa_rog">
					<?php foreach ( alyxa_rogi() as $slug => $nazwa ) : ?>
						<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $konfiguracja['rog'], $slug ); ?>>
							<?php echo esc_html( $nazwa ); ?>
						</option>
					<?php endforeach; ?>
				</select>

				<p class="description">
					<?php esc_html_e( 'The bottom right corner is usually taken already - by a back-to-top button, a chat bubble or a consent banner. Two round buttons on top of each other are two touch targets eating one another.', 'alyxa-a11y' ); ?>
				</p>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="alyxa-odstep"><?php esc_html_e( 'Distance from the edge', 'alyxa-a11y' ); ?></label>
			</th>
			<td>
				<input type="number" id="alyxa-odstep" name="alyxa_odstep" class="small-text" min="8" max="64" step="1" value="<?php echo esc_attr( (string) $konfiguracja['odstep'] ); ?>">
				<?php esc_html_e( 'px', 'alyxa-a11y' ); ?>

				<p class="description"><?php esc_html_e( 'Between 8 and 64. Below that the button sticks to the edge of the screen; above it, it floats in the middle of nothing.', 'alyxa-a11y' ); ?></p>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="alyxa-akcent"><?php esc_html_e( 'Accent colour', 'alyxa-a11y' ); ?></label>
			</th>
			<td>
				<input type="text" id="alyxa-akcent" name="alyxa_akcent" class="regular-text code" maxlength="7" pattern="#[0-9a-fA-F]{6}" placeholder="#1a4d8f" value="<?php echo esc_attr( $konfiguracja['akcent'] ); ?>">

				<p class="description">
					<?php esc_html_e( 'Leave this empty and the panel takes its colours from the theme, which is what it is built to do. Fill it in only when the theme publishes no palette, or when the panel has to stand out against it.', 'alyxa-a11y' ); ?>
				</p>
			</td>
		</tr>
	</table>
	<?php
}

/**
 * Wypisuje pole obszaru odczytu.
 *
 * Pole ma sens tylko wtedy, gdy na stronie jest cokolwiek, co czyta - stad
 * warunek. Ustawienie bez skutku jest gorsze niz jego brak, bo kazdy, kto
 * je zobaczy, bedzie sie zastanawial, czego nie zrobil.
 *
 * @param array<string, mixed>                $konfiguracja Konfiguracja strony.
 * @param array<string, array<string, mixed>> $wlaczone     Moduly wlaczone na stronie.
 * @return void
 */
function alyxa_pola_odczytu( array $konfiguracja, array $wlaczone ) {
	if ( ! isset( $wlaczone['odczyt'] ) && ! isset( $wlaczone['wskazywanie'] ) ) {
		return;
	}
	?>
	<h2><?php esc_html_e( 'Reading aloud', 'alyxa-a11y' ); ?></h2>

	<table class="form-table" role="presentation">
		<tr>
			<th scope="row">
				<label for="alyxa-obszar"><?php esc_html_e( 'What counts as the content', 'alyxa-a11y' ); ?></label>
			</th>
			<td>
				<input type="text" id="alyxa-obszar" name="alyxa_obszar" class="large-text code" value="<?php echo esc_attr( $konfiguracja['obszar'] ); ?>" placeholder="<?php echo esc_attr( alyxa_obszar_domyslny() ); ?>">

				<p class="description">
					<?php esc_html_e( 'CSS selectors separated by commas, tried one after another - the first one that matches anything wins. This is where "Read the page" starts from, so it should be the article, not the whole document: a menu and a footer read out loud are half a minute after which nobody knows what the page was about.', 'alyxa-a11y' ); ?>
				</p>

				<p class="description">
					<?php
					printf(
						/* translators: %s: the built-in list of CSS selectors. */
						esc_html__( 'Leave it empty to use the built-in list: %s', 'alyxa-a11y' ),
						'<code>' . esc_html( alyxa_obszar_domyslny() ) . '</code>'
					);
					?>
				</p>
			</td>
		</tr>
	</table>
	<?php
}

/**
 * Wypisuje zestawy startowe.
 *
 * KAZDY ZESTAW JEST PRZYCISKIEM WYSYLAJACYM FORMULARZ, a nie polem wyboru
 * do zatwierdzenia osobnym przyciskiem. Zestaw nie jest ustawieniem, ktore
 * sie trzyma - jest czynnoscia, ktora raz przestawia pola wyboru wyzej. To
 * ta sama roznica, ktora w panelu rozdziela przelacznik od przycisku
 * czynnosci, tylko po drugiej stronie kokpitu.
 *
 * @return void
 */
function alyxa_pola_zestawow() {
	?>
	<h2><?php esc_html_e( 'Starter sets', 'alyxa-a11y' ); ?></h2>

	<p class="alyxa-ustawienia__opis">
		<?php esc_html_e( 'A set switches every module at once, so it also switches off the ones it does not include. Use it as a starting point and correct it above.', 'alyxa-a11y' ); ?>
	</p>

	<div class="alyxa-zestawy">
		<?php foreach ( alyxa_zestawy() as $slug => $zestaw ) : ?>
			<div class="alyxa-zestaw">
				<button type="submit" class="button" name="alyxa_zestaw" value="<?php echo esc_attr( $slug ); ?>">
					<?php echo esc_html( $zestaw['nazwa'] ); ?>
				</button>

				<p class="alyxa-zestaw__opis"><?php echo esc_html( $zestaw['opis'] ); ?></p>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * Wypisuje stan wtyczki.
 *
 * ARKUSZ POTRAFI NIE BYC PLIKIEM. Na hostingu, ktory nie pozwala pisac
 * w uploads, sklejone reguly ida do dokumentu - strona dziala, ale wolniej
 * i bez pamieci podrecznej przegladarki. Bez tego wiersza nikt by sie o tym
 * nie dowiedzial, bo nic nie jest zepsute.
 *
 * @param array<string, array<string, mixed>> $wlaczone Moduly wlaczone na stronie.
 * @return void
 */
function alyxa_stan_wtyczki( array $wlaczone ) {
	$stan = alyxa_stan_arkusza();
	?>
	<h2><?php esc_html_e( 'Status', 'alyxa-a11y' ); ?></h2>

	<table class="widefat striped alyxa-stan">
		<tbody>
			<tr>
				<th scope="row"><?php esc_html_e( 'Modules on', 'alyxa-a11y' ); ?></th>
				<td>
					<?php
					echo esc_html(
						$wlaczone
							? implode( ', ', wp_list_pluck( $wlaczone, 'nazwa' ) )
							: __( 'None. The panel will tell visitors there is nothing to switch on.', 'alyxa-a11y' )
					);
					?>
				</td>
			</tr>

			<tr>
				<th scope="row"><?php esc_html_e( 'Module stylesheet', 'alyxa-a11y' ); ?></th>
				<td>
					<?php if ( ! empty( $stan['plik'] ) ) : ?>
						<code><?php echo esc_html( basename( $stan['plik'] ) ); ?></code>
					<?php elseif ( ! empty( $stan['inline'] ) ) : ?>
						<?php esc_html_e( 'Written into the page itself, because the uploads folder is not writable. Everything works; the rules just cannot be cached by the browser.', 'alyxa-a11y' ); ?>
					<?php else : ?>
						<?php esc_html_e( 'Empty - none of the modules that are on brings any CSS with it.', 'alyxa-a11y' ); ?>
					<?php endif; ?>
				</td>
			</tr>

			<tr>
				<th scope="row"><?php esc_html_e( 'Visitor settings', 'alyxa-a11y' ); ?></th>
				<td><?php esc_html_e( 'Kept in each visitor\'s own browser. The plugin sets no cookies, stores no personal data and needs no consent banner.', 'alyxa-a11y' ); ?></td>
			</tr>
		</tbody>
	</table>
	<?php
}
