<?php
/**
 * Odinstalowanie wtyczki.
 *
 * Tu, w odroznieniu od deaktywacji, kasujemy takze konfiguracje: usuniecie
 * wtyczki to swiadoma decyzja administratora, a zostawione opcje sa smieciem
 * w tabeli, ktorego nikt juz nie zobaczy i nie sprzatnie.
 *
 * Wyboru odwiedzajacych nie kasujemy, bo nie mamy do niego dostepu - siedzi
 * w pamieci ich przegladarek. Klasy, ktore z niego wynikaja, przestaja cokolwiek
 * znaczyc, gdy tylko znika arkusz modulow.
 *
 * @package alyxa-a11y
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'alyxa_a11y_konfiguracja' );
delete_option( 'alyxa_a11y_arkusz' );

$alyxa_uploads = wp_upload_dir();

if ( empty( $alyxa_uploads['error'] ) ) {
	$alyxa_katalog = trailingslashit( $alyxa_uploads['basedir'] ) . 'alyxa-a11y';

	foreach ( (array) glob( $alyxa_katalog . '/moduly-*.css' ) as $alyxa_plik ) {
		wp_delete_file( $alyxa_plik );
	}

	if ( is_dir( $alyxa_katalog ) ) {
		@rmdir( $alyxa_katalog ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- katalog moze zawierac pliki spoza wtyczki; wtedy zostaje.
	}
}
