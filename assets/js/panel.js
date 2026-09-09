/**
 * Alyxa Accessibility - obsluga panelu.
 *
 * RDZEN NIE ZNA ANI JEDNEGO MODULU Z NAZWY. Dostaje z serwera liste
 * slugow z typem i liczba stopni, a cala jego praca sprowadza sie do
 * trzech rzeczy: odczytac wybor z pamieci przegladarki, zamienic go na
 * klasy na <html> i odlozyc z powrotem. Modul, ktoremu wystarczy arkusz
 * CSS reagujacy na klase - a takich jest wiekszosc - dziala tu bez
 * jednej linijki zmian.
 *
 * WYJATKIEM JEST TABLICA ZACHOWAN. Modul, ktory potrzebuje czegos, czego
 * w CSS zapisac sie nie da - jak maska czytania idaca za wskaznikiem -
 * dopisuje sie tam pod swoim slugiem. To zachowanie deklaruje, do ktorego
 * modulu nalezy; rdzen tylko chodzi po tablicy i pyta, czy ten modul jest
 * teraz wlaczony.
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
	 * ZACHOWANIA MODULOW - jedyne miejsce w skrypcie, ktore zna slugi.
	 *
	 * Wiekszosc modulow to sam arkusz CSS reagujacy na klase, i te dalej
	 * nie potrzebuja stad ani bajta. Maska czytania potrzebuje: pasmo musi
	 * isc za wskaznikiem, a tego nie da sie zapisac w CSS.
	 *
	 * Rdzen ponizej dalej nie wie, co robi ktorykolwiek modul. Chodzi po tej
	 * tablicy i pyta o jedno: czy modul o tym slugu jest wlaczony na stronie
	 * i wlaczony przez odwiedzajacego. To zachowanie deklaruje, do ktorego
	 * modulu nalezy, nie rdzen deklaruje, ktore zachowania istnieja.
	 *
	 * DLACZEGO W TYM SAMYM PLIKU, A NIE W OSOBNYM SKLEJANYM ARKUSZU JS.
	 * Bo caly ten kod to niecaly kilobajt, a drugi potok sklejania kosztowalby
	 * wiecej niz oszczedza - decyzja opisana w inc/zasoby.php. Modul wylaczony
	 * nie zostawia za to na stronie zadnego sladu: bez elementu, bez
	 * nasluchiwania, bez jednej reguly CSS.
	 */
	var zachowania = {
		maska: ( function () {
			var pasmo = null;
			var ostatniY = null;
			var czekaNaKlatke = false;
			var celFokusu = null;
			var klatekZaFokusem = 0;

			/**
			 * Przesuwa pasmo tak, zeby jego srodek wypadl na ostatnio
			 * wskazanej wysokosci.
			 *
			 * Pasmo nie wychodzi poza okno. Bez tego przy wskazniku przy
			 * gornej krawedzi polowa pasma bylaby poza ekranem, a widoczna
			 * czesc dwa razy wezsza, niz uzytkownik ustawil.
			 *
			 * @return {void}
			 */
			function przesun() {
				if ( ! pasmo ) {
					return;
				}

				/*
				 * Polozenie celu fokusu czytamy TUTAJ, a nie w chwili zdarzenia.
				 * Przejscie tabulatorem przewija strone, a motyw przewija ja
				 * plynnie - w chwili focusin element jest jeszcze tam, gdzie byl
				 * przed przewinieciem. Dopoki jego prostokat sie rusza, prosimy
				 * o kolejna klatke; limit klatek jest po to, zeby element, ktory
				 * porusza sie sam z siebie, nie trzymal nas w petli bez konca.
				 */
				if ( celFokusu ) {
					var obszarCelu = celFokusu.getBoundingClientRect();
					var wysokoscCelu = obszarCelu.top + obszarCelu.height / 2;

					if ( null !== ostatniY && Math.abs( wysokoscCelu - ostatniY ) < 0.5 ) {
						celFokusu = null;
					} else if ( klatekZaFokusem > 60 ) {
						celFokusu = null;
					} else {
						klatekZaFokusem++;

						zaplanuj();
					}

					ostatniY = wysokoscCelu;
				}

				var wysokosc = pasmo.offsetHeight;
				var srodek = null === ostatniY ? window.innerHeight / 2 : ostatniY;
				var gora = Math.round( srodek - wysokosc / 2 );
				var najnizej = window.innerHeight - wysokosc;

				if ( gora > najnizej ) {
					gora = najnizej;
				}

				if ( gora < 0 ) {
					gora = 0;
				}

				pasmo.style.setProperty( '--alyxa-maska-gora', gora + 'px' );
			}

			/**
			 * Odklada przesuniecie do najblizszej klatki.
			 *
			 * Wskaznik potrafi zglosic kilkaset zdarzen na sekunde, a ekran
			 * i tak rysuje szescdziesiat razy. Bez tej bramki liczylibysmy
			 * polozenie kilka razy na klatke i za kazdym razem ruszali
			 * ukladem strony.
			 *
			 * @return {void}
			 */
			function zaplanuj() {
				if ( czekaNaKlatke ) {
					return;
				}

				czekaNaKlatke = true;

				window.requestAnimationFrame( function () {
					czekaNaKlatke = false;

					przesun();
				} );
			}

			/**
			 * Ruch wskaznika.
			 *
			 * Dotyk pomijamy. Palec przesuwa sie po ekranie, zeby przewinac
			 * strone, a nie zeby cos wskazac - pasmo skakaloby przy kazdym
			 * przewinieciu i uciekalo spod tekstu, ktory wlasnie nadjezdza.
			 *
			 * @param {PointerEvent} zdarzenie Zdarzenie wskaznika.
			 * @return {void}
			 */
			function zeWskaznika( zdarzenie ) {
				if ( 'touch' === zdarzenie.pointerType ) {
					return;
				}

				/* Mysz przejmuje prowadzenie od klawiatury. */
				celFokusu = null;
				ostatniY = zdarzenie.clientY;

				zaplanuj();
			}

			/**
			 * Przejscie fokusu.
			 *
			 * Bez tego maska byla by modulem wylacznie dla myszy: ktos, kto
			 * chodzi po stronie tabulatorem, zostawalby z pasmem stojacym
			 * w miejscu i przyciemnieniem na tym, co wlasnie czyta.
			 *
			 * Kontrolki panelu pomijamy - fokus na przelaczniku nie jest
			 * czytaniem strony, a pasmo skakaloby na panel przy kazdym
			 * wejsciu w ustawienia.
			 *
			 * @param {FocusEvent} zdarzenie Zdarzenie fokusu.
			 * @return {void}
			 */
			function zFokusu( zdarzenie ) {
				var cel = zdarzenie.target;

				if ( ! cel || ! cel.getBoundingClientRect || przycisk === cel || panel.contains( cel ) ) {
					return;
				}

				celFokusu = cel;
				klatekZaFokusem = 0;

				zaplanuj();
			}

			return {
				wlacz: function () {
					if ( pasmo ) {
						return;
					}

					pasmo = document.createElement( 'div' );
					pasmo.className = 'alyxa-maska__pasmo';

					/* Dla czytnika ekranu maska nie istnieje - to zaslona
					   dla oczu, a nie tresc. */
					pasmo.setAttribute( 'aria-hidden', 'true' );

					document.body.appendChild( pasmo );

					przesun();

					document.addEventListener( 'pointermove', zeWskaznika, { passive: true } );
					document.addEventListener( 'focusin', zFokusu );
					window.addEventListener( 'resize', zaplanuj );
				},

				wylacz: function () {
					if ( ! pasmo ) {
						return;
					}

					document.removeEventListener( 'pointermove', zeWskaznika );
					document.removeEventListener( 'focusin', zFokusu );
					window.removeEventListener( 'resize', zaplanuj );

					pasmo.parentNode.removeChild( pasmo );
					pasmo = null;
					celFokusu = null;
				}
			};
		}() )
	};

	var czynneZachowania = {};

	/**
	 * Doprowadza zachowania do zgodnosci z wyborem.
	 *
	 * Wlaczamy i wylaczamy tylko przy zmianie, a nie przy kazdym wywolaniu:
	 * inaczej kazde klikniecie w dowolny inny modul zdejmowaloby i zakladalo
	 * maske od nowa, razem z nasluchiwaniem.
	 *
	 * @return {void}
	 */
	function zsynchronizujZachowania() {
		var slug;
		var czynne;

		for ( slug in zachowania ) {
			if ( ! Object.prototype.hasOwnProperty.call( zachowania, slug ) ) {
				continue;
			}

			czynne = !! moduly[ slug ] && true === stan[ slug ];

			if ( czynne === !! czynneZachowania[ slug ] ) {
				continue;
			}

			czynneZachowania[ slug ] = czynne;

			if ( czynne ) {
				zachowania[ slug ].wlacz();
			} else {
				zachowania[ slug ].wylacz();
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
		zsynchronizujZachowania();
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
		zsynchronizujZachowania();
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
	zsynchronizujZachowania();
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
