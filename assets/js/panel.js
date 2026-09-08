/**
 * Alyxa Accessibility - obsluga panelu.
 *
 * SKRYPT NIE ZNA ANI JEDNEGO MODULU Z NAZWY. Dostaje z serwera liste
 * slugow z typem i liczba stopni, a cala jego praca sprowadza sie do
 * trzech rzeczy: odczytac wybor z pamieci przegladarki, zamienic go na
 * klasy na <html> i odlozyc z powrotem. Kazdy modul dopisany w kolejnych
 * fazach dziala tu bez jednej linijki zmian - rozni go tylko arkusz CSS
 * reagujacy na jego klase.
 *
 * WYBOR ODWIEDZAJACEGO NIGDY NIE OPUSZCZA JEGO PRZEGLADARKI.
 * localStorage, nie ciasteczko. Ciasteczko jedzie z kazdym zadaniem na
 * serwer, wiec bylaby to dana wysylana do placowki - z pytaniem o zgode
 * i wpisem w polityce prywatnosci. Tutaj serwer nie dowiaduje sie niczego.
 *
 * PANEL NIE JEST OKNEM MODALNYM. Nie zamykamy w nim fokusu i nie ruszamy
 * fokusu przy otwarciu: panel stoi w dokumencie tuz za przyciskiem, wiec
 * Tab wchodzi do niego sam, a Shift+Tab wraca. Escape zamyka i oddaje fokus
 * przyciskowi, ale tylko wtedy, gdy fokus byl w srodku - inaczej zabralibysmy
 * go komus, kto klikal myszka gdzie indziej.
 */
( function () {
	'use strict';

	var ustawienia = window.alyxaUstawienia;

	if ( ! ustawienia || ! ustawienia.klucz ) {
		return;
	}

	var przycisk = document.getElementById( 'alyxa-przycisk' );
	var panel = document.getElementById( 'alyxa-panel' );

	if ( ! przycisk || ! panel ) {
		return;
	}

	var korzen = document.documentElement;
	var moduly = ustawienia.moduly || {};
	var teksty = ustawienia.teksty || {};
	var odsiane = false;
	var stan = wczytaj();

	/**
	 * Odczytuje zapisany wybor.
	 *
	 * Pamiec przegladarki potrafi rzucic wyjatkiem, a nie zwrocic null:
	 * w trybie prywatnym, przy wylaczonych danych witryn, przy zapelnionym
	 * limicie. Panel ma wtedy dzialac dalej, tyle ze bez zapamietywania.
	 *
	 * Odsiewamy klucze, ktorych nie ma w rejestrze tej strony. Modul mogl
	 * zostac wylaczony w ustawieniach po tym, jak odwiedzajacy go wlaczyl,
	 * a wtedy klasa wisialaby na <html> bez zadnego arkusza, ktory by ja
	 * obslugiwal - i bez przelacznika, ktorym dalo sie ja zdjac.
	 *
	 * @return {Object} Wybor odwiedzajacego.
	 */
	function wczytaj() {
		var zapisane = {};
		var surowe;
		var klucz;

		try {
			surowe = JSON.parse( window.localStorage.getItem( ustawienia.klucz ) || '{}' );
		} catch ( blad ) {
			return zapisane;
		}

		if ( ! surowe || 'object' !== typeof surowe ) {
			return zapisane;
		}

		for ( klucz in surowe ) {
			if ( ! Object.prototype.hasOwnProperty.call( surowe, klucz ) ) {
				continue;
			}

			if ( ! moduly[ klucz ] ) {
				odsiane = true;

				continue;
			}

			zapisane[ klucz ] = poprawWartosc( klucz, surowe[ klucz ] );
		}

		return zapisane;
	}

	/**
	 * Sprowadza wartosc do zakresu, jaki modul dopuszcza.
	 *
	 * @param {string} slug    Slug modulu.
	 * @param {*}      wartosc Wartosc z pamieci albo z kliknięcia.
	 * @return {boolean|number} Wartosc do zapisania.
	 */
	function poprawWartosc( slug, wartosc ) {
		if ( 'stopnie' === moduly[ slug ].typ ) {
			var liczba = parseInt( wartosc, 10 );

			if ( isNaN( liczba ) || liczba < 0 ) {
				liczba = 0;
			}

			return Math.min( liczba, moduly[ slug ].stopnie );
		}

		return true === wartosc;
	}

	/**
	 * Odklada wybor do pamieci przegladarki.
	 *
	 * @return {void}
	 */
	function zapisz() {
		try {
			/*
			 * Pusty wybor kasuje wpis, zamiast zapisywac pusty obiekt.
			 * Skrypt w naglowku ma wtedy jedno sprawdzenie mniej przed
			 * pierwszym rysowaniem strony, a w pamieci przegladarki nie
			 * zostaje slad po kims, kto wszystko wylaczyl.
			 */
			if ( ! Object.keys( stan ).length ) {
				window.localStorage.removeItem( ustawienia.klucz );

				return;
			}

			window.localStorage.setItem( ustawienia.klucz, JSON.stringify( stan ) );
		} catch ( blad ) {
			/* Brak zapisu nie moze zatrzymac przelaczania w biezacej odslonie. */
		}
	}

	/**
	 * Przepisuje wybor na klasy elementu <html>.
	 *
	 * Najpierw zdejmujemy WSZYSTKIE klasy z naszym przedrostkiem, potem
	 * dokladamy aktualne. Bez tego zerowania przy module stopniowanym
	 * zostawalyby na elemencie dwie klasy naraz - poprzedni i biezacy
	 * stopien - a wygrywalaby ta pozniejsza w arkuszu, nie ta wybrana.
	 *
	 * DLACZEGO PO PRZEDROSTKU, A NIE PO LISCIE MODULOW.
	 * Skrypt w naglowku sklada klasy z tego, co znajdzie w pamieci
	 * przegladarki - nie zna rejestru, bo dziala, zanim cokolwiek z serwera
	 * do niego dotrze. Gdy administrator wylaczy modul, ktory odwiedzajacy
	 * mial wlaczony, tamten skrypt i tak zalozy jego klase. Sprzatanie po
	 * liscie modulow by jej nie zdjelo - bo tego modulu juz na liscie nie ma -
	 * i zostalaby na <html> na zawsze, razem z kazda inna nazwa, ktora
	 * ktokolwiek wpisze do pamieci przegladarki. Przedrostek lapie wszystko.
	 *
	 * @return {void}
	 */
	function zastosuj() {
		var slug;
		var doZdjecia = [];
		var i;

		for ( i = 0; i < korzen.classList.length; i++ ) {
			if ( 0 === korzen.classList[ i ].indexOf( 'alyxa-' ) ) {
				doZdjecia.push( korzen.classList[ i ] );
			}
		}

		for ( i = 0; i < doZdjecia.length; i++ ) {
			korzen.classList.remove( doZdjecia[ i ] );
		}

		for ( slug in stan ) {
			if ( ! Object.prototype.hasOwnProperty.call( stan, slug ) || ! moduly[ slug ] ) {
				continue;
			}

			if ( true === stan[ slug ] ) {
				korzen.classList.add( 'alyxa-' + slug );
			} else if ( 'number' === typeof stan[ slug ] && stan[ slug ] > 0 ) {
				korzen.classList.add( 'alyxa-' + slug + '-' + stan[ slug ] );
			}
		}
	}

	/**
	 * Doprowadza przelaczniki w panelu do zgodnosci z wyborem.
	 *
	 * @return {void}
	 */
	function odswiezKontrolki() {
		var kontrolki = panel.querySelectorAll( '[data-alyxa-modul]' );
		var i;

		for ( i = 0; i < kontrolki.length; i++ ) {
			opisz( kontrolki[ i ] );
		}
	}

	/**
	 * Ustawia stan pojedynczej kontrolki.
	 *
	 * Przy module stopniowanym tekst stopnia jest czescia nazwy dostepnej
	 * przycisku, wiec czytnik oglasza zmiane bez zadnego obszaru aria-live.
	 * Przy przelaczniku te role pelni aria-pressed.
	 *
	 * @param {HTMLElement} kontrolka Przycisk modulu.
	 * @return {void}
	 */
	function opisz( kontrolka ) {
		var slug = kontrolka.getAttribute( 'data-alyxa-modul' );
		var wartosc = stan[ slug ];
		var pole = kontrolka.querySelector( '[data-alyxa-stan]' );

		if ( ! moduly[ slug ] ) {
			return;
		}

		if ( 'stopnie' === moduly[ slug ].typ ) {
			var stopien = 'number' === typeof wartosc ? wartosc : 0;

			kontrolka.setAttribute( 'aria-pressed', stopien > 0 ? 'true' : 'false' );

			if ( pole ) {
				pole.textContent = stopien > 0
					? ( teksty.stopien || '%1$d / %2$d' )
						.replace( '%1$d', stopien )
						.replace( '%2$d', moduly[ slug ].stopnie )
					: ( teksty.wylaczone || '' );
			}

			return;
		}

		kontrolka.setAttribute( 'aria-pressed', true === wartosc ? 'true' : 'false' );
	}

	/**
	 * Przelacza modul na nastepna wartosc.
	 *
	 * Modul stopniowany chodzi w kolko: 0, 1, 2, 3, znowu 0. Jeden przycisk
	 * zamiast pary "wiecej / mniej" - mniej kontrolek do przejscia tabulatorem,
	 * a droga powrotna do stanu domyslnego jest zawsze skonczona i krotka.
	 *
	 * @param {string} slug Slug modulu.
	 * @return {void}
	 */
	function przelacz( slug ) {
		if ( ! moduly[ slug ] ) {
			return;
		}

		if ( 'stopnie' === moduly[ slug ].typ ) {
			var teraz = 'number' === typeof stan[ slug ] ? stan[ slug ] : 0;
			var dalej = teraz + 1 > moduly[ slug ].stopnie ? 0 : teraz + 1;

			if ( 0 === dalej ) {
				delete stan[ slug ];
			} else {
				stan[ slug ] = dalej;
			}
		} else if ( true === stan[ slug ] ) {
			delete stan[ slug ];
		} else {
			stan[ slug ] = true;
		}

		zapisz();
		zastosuj();
		odswiezKontrolki();
	}

	/**
	 * Kasuje caly wybor.
	 *
	 * @return {void}
	 */
	function wyzeruj() {
		stan = {};

		zapisz();
		zastosuj();
		odswiezKontrolki();
	}

	/**
	 * Otwiera panel.
	 *
	 * @return {void}
	 */
	function otworz() {
		panel.hidden = false;
		przycisk.setAttribute( 'aria-expanded', 'true' );
	}

	/**
	 * Zamyka panel.
	 *
	 * @param {boolean} oddajFokus Czy przeniesc fokus na przycisk.
	 * @return {void}
	 */
	function zamknij( oddajFokus ) {
		/*
		 * Kolejnosc ma znaczenie. Gdyby panel zniknal, zanim fokus z niego
		 * wyjdzie, przegladarka odlozylaby fokus na <body> i nastepny Tab
		 * zaczynalby od poczatku strony.
		 */
		if ( oddajFokus && panel.contains( document.activeElement ) ) {
			przycisk.focus();
		}

		panel.hidden = true;
		przycisk.setAttribute( 'aria-expanded', 'false' );
	}

	/**
	 * Czy panel jest otwarty.
	 *
	 * @return {boolean}
	 */
	function otwarty() {
		return ! panel.hidden;
	}

	przycisk.addEventListener( 'click', function () {
		if ( otwarty() ) {
			zamknij( true );
		} else {
			otworz();
		}
	} );

	panel.addEventListener( 'click', function ( zdarzenie ) {
		var kontrolka = zdarzenie.target.closest( '[data-alyxa-modul]' );

		if ( kontrolka ) {
			przelacz( kontrolka.getAttribute( 'data-alyxa-modul' ) );

			return;
		}

		if ( zdarzenie.target.closest( '[data-alyxa-reset]' ) ) {
			wyzeruj();

			return;
		}

		if ( zdarzenie.target.closest( '[data-alyxa-zamknij]' ) ) {
			zamknij( true );
		}
	} );

	document.addEventListener( 'keydown', function ( zdarzenie ) {
		if ( 'Escape' === zdarzenie.key && otwarty() ) {
			zamknij( true );
		}
	} );

	/*
	 * Klikniecie poza panelem zamyka go, ale fokusu nie ruszamy: skoro
	 * uzytkownik klika gdzie indziej, to wlasnie tam chce byc.
	 */
	document.addEventListener( 'click', function ( zdarzenie ) {
		if ( ! otwarty() || panel.contains( zdarzenie.target ) || przycisk.contains( zdarzenie.target ) ) {
			return;
		}

		zamknij( false );
	} );

	/*
	 * Klasy sa juz na <html> - zalozyl je skrypt z naglowka, zanim strona
	 * zostala narysowana. Powtarzamy to tutaj na wypadek, gdyby tamten
	 * skrypt nie doszedl do skutku; zastosuj() zaczyna od zdjecia wszystkiego,
	 * wiec drugie wywolanie niczego nie dubluje.
	 */
	zastosuj();
	odswiezKontrolki();

	/*
	 * Pamiec przegladarki zawierala wpisy, ktorych ta strona juz nie zna -
	 * po wylaczeniu modulu w ustawieniach albo po tym, jak ktos dopisal tam
	 * cos recznie. Odklada sie odsiana wersje, zeby nie wracaly przy kazdej
	 * kolejnej odslonie i zeby skrypt w naglowku przestal zakladac ich klasy.
	 */
	if ( odsiane ) {
		zapisz();
	}

	/* Dopiero teraz przycisk ma sens: jest czym przelaczac i gdzie zapisac. */
	przycisk.hidden = false;
}() );
