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
 * w CSS zapisac sie nie da - jak maska czytania idaca za wskaznikiem albo
 * odczyt strony na glos - dopisuje sie tam pod swoim slugiem. To zachowanie
 * deklaruje, do ktorego modulu nalezy; rdzen tylko chodzi po tablicy i pyta,
 * czy ten modul jest teraz wlaczony.
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
	 * isc za wskaznikiem, a tego nie da sie zapisac w CSS. Odczyt strony
	 * potrzebuje jeszcze wiecej, bo nie zmienia wygladu strony w ogole.
	 *
	 * Rdzen ponizej dalej nie wie, co robi ktorykolwiek modul. Chodzi po tej
	 * tablicy i pyta o jedno: czy modul o tym slugu jest wlaczony na stronie
	 * i wlaczony przez odwiedzajacego. To zachowanie deklaruje, do ktorego
	 * modulu nalezy, nie rdzen deklaruje, ktore zachowania istnieja.
	 *
	 * UMOWA ZACHOWANIA - cztery metody, wszystkie oprocz pierwszej opcjonalne:
	 *
	 *     wlacz( kontekst )  modul wlaczony; kontekst niesie slug, tablice
	 *                        'dane' z rejestru i pozycje modulu w panelu
	 *     wylacz()           modul wylaczony; ma po sobie posprzatac do zera
	 *     akcja( slug )      odwiedzajacy nacisnal przycisk czynnosci
	 *     zmiana()           zmienilo sie dowolne ustawienie panelu
	 *
	 * Modul z typem 'akcje' nie ma stanu, wiec jego zachowanie jest czynne
	 * przez caly czas, gdy modul jest wlaczony na stronie. Przelacznik i
	 * stopnie - tylko wtedy, gdy odwiedzajacy je wlaczyl.
	 *
	 * DLACZEGO W TYM SAMYM PLIKU, A NIE W OSOBNYM SKLEJANYM ARKUSZU JS.
	 * Bo caly ten kod to kilka kilobajtow, a drugi potok sklejania kosztowalby
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
		}() ),

		/**
		 * ODCZYT STRONY NA GLOS.
		 *
		 * CZYTA PRZEGLADARKA, NIE MY. speechSynthesis to glos zainstalowany
		 * na urzadzeniu odwiedzajacego - ten sam, ktorym mowi jego telefon.
		 * Zadne zdanie z tej strony nie jedzie w tym celu na nasz serwer.
		 *
		 * BEZ GLOSU W JEZYKU STRONY POZYCJI NIE MA WCALE. Polski tekst
		 * przeczytany glosem angielskim to belkot, ktory brzmi jak awaria
		 * strony, a nie jak brak glosu w systemie. Pozycja wychodzi wiec
		 * z serwera ukryta i pokazujemy ja dopiero, gdy jest czym czytac.
		 *
		 * CZYTAMY TO, CO WIDAC. Z obszaru tresci - nie z menu i stopki -
		 * i tylko te elementy, ktore sa na ekranie widoczne. Tekst schowany
		 * dla oka, a zostawiony dla czytnikow ekranu ("przejdz do tresci",
		 * "menu"), jest tu podwojnie nie na miejscu: odwiedzajacy go nie
		 * widzi, wiec nie zrozumie, skad sie wzial.
		 */
		odczyt: ( function () {
			var mowa = window.speechSynthesis;

			/*
			 * Kawalek dluzszy niz to nic nie zyskuje, a duzo ryzykuje:
			 * Chrome od lat ucina pojedyncza wypowiedz po kilkunastu
			 * sekundach, i jest to jego blad, nie nasz. Krotkie kawalki
			 * chronia przed tym za darmo, bo i tak tniemy tekst na zdania.
			 */
			var LIMIT = 160;

			/* Elementy, ktore nie niosa tresci albo niosa ja nie dla ucha. */
			var pomijane = {
				SCRIPT: 1, STYLE: 1, NOSCRIPT: 1, TEMPLATE: 1, IFRAME: 1,
				OBJECT: 1, EMBED: 1, VIDEO: 1, AUDIO: 1, CANVAS: 1, SVG: 1,
				SELECT: 1, TEXTAREA: 1, INPUT: 1, BUTTON: 1
			};

			/*
			 * Elementy zaczynajace nowy wiersz, zbierane przy przechodzeniu
			 * dokumentu. Zbior, a nie lista znacznikow: patrz zbierzTekst.
			 */
			var granice = null;

			var kontekst = null;
			var pozycja = null;
			var przyciskCzytaj = null;
			var komunikat = null;
			var etykietaCzytaj = '';

			/* 'bezczynny', 'czyta' albo 'wstrzymany'. */
			var stanOdczytu = 'bezczynny';

			/*
			 * Numer podejscia. cancel() zglasza koniec takze tym wypowiedziom,
			 * ktore wyrzucil z kolejki - bez tego licznika koniec poprzedniego
			 * czytania kasowalby stan nastepnego, zaczetego ulamek sekundy
			 * pozniej.
			 */
			var pokolenie = 0;

			/**
			 * Wartosc z tablicy 'dane' rejestru.
			 *
			 * @param {string} klucz Nazwa wartosci.
			 * @return {string}
			 */
			function dane( klucz ) {
				return ( kontekst && kontekst.dane && kontekst.dane[ klucz ] ) || '';
			}

			/**
			 * Jezyk, w ktorym mamy czytac.
			 *
			 * Pierwszenstwo ma to, co deklaruje sam dokument: na stronie
			 * wielojezycznej kazda podstrona ma wlasny atrybut lang, a
			 * ustawienie strony jest jedno na cala instalacje.
			 *
			 * @return {string}
			 */
			function jezyk() {
				var zDokumentu = ( korzen.getAttribute( 'lang' ) || '' ).trim();

				return ( zDokumentu || dane( 'jezyk' ) ).toLowerCase().replace( /_/g, '-' );
			}

			/**
			 * Wybiera glos do czytania.
			 *
			 * DWA KRYTERIA, W TEJ KOLEJNOSCI. Najpierw glos dzialajacy na
			 * urzadzeniu (localService): glos sieciowy wysyla czytany tekst
			 * do dostawcy przegladarki, a panel obiecuje w stopce, ze wybor
			 * zostaje na tym urzadzeniu - wiec nie my mamy z tej obietnicy
			 * robic wyjatek. Potem zgodnosc calego kodu jezyka, bo pl-PL
			 * czyta polski tekst lepiej niz jakikolwiek inny wariant.
			 *
			 * @return {SpeechSynthesisVoice|null}
			 */
			function znajdzGlos() {
				var pelny = jezyk();
				var podstawa = pelny.split( '-' )[ 0 ];
				var lista;
				var najlepszy = null;
				var najlepszaOcena = -1;
				var kod;
				var ocena;
				var i;

				if ( ! podstawa ) {
					return null;
				}

				lista = mowa.getVoices() || [];

				for ( i = 0; i < lista.length; i++ ) {
					kod = ( lista[ i ].lang || '' ).toLowerCase().replace( /_/g, '-' );

					if ( kod !== podstawa && 0 !== kod.indexOf( podstawa + '-' ) ) {
						continue;
					}

					ocena = ( lista[ i ].localService ? 2 : 0 ) + ( kod === pelny ? 1 : 0 );

					if ( ocena > najlepszaOcena ) {
						najlepszaOcena = ocena;
						najlepszy = lista[ i ];
					}
				}

				return najlepszy;
			}

			/**
			 * Element, z ktorego bierzemy tresc.
			 *
			 * Selektory probujemy PO KOLEI, a nie jedna lista przekazana do
			 * querySelector: lista zwraca element najwczesniejszy w dokumencie,
			 * a nie ten, ktory pasuje do najwczesniejszego selektora - czyli
			 * kolejnosc zapisana w rejestrze przestalaby cokolwiek znaczyc.
			 *
			 * @return {HTMLElement}
			 */
			function obszarTresci() {
				var selektory = dane( 'obszar' ).split( ',' );
				var znaleziony;
				var i;

				for ( i = 0; i < selektory.length; i++ ) {
					try {
						znaleziony = document.querySelector( selektory[ i ].trim() );
					} catch ( blad ) {
						znaleziony = null;
					}

					if ( znaleziony ) {
						return znaleziony;
					}
				}

				return document.body;
			}

			/**
			 * Ocenia element: czy wchodzimy w niego i czy zaczyna nowy wiersz.
			 *
			 * GRANICE WIERSZY BIERZEMY Z UKLADU, A NIE Z NAZW ZNACZNIKOW.
			 * Pierwsza wersja miala liste znacznikow blokowych i przegrala
			 * z pierwsza napotkana strona: motyw tego projektu daje
			 * <small> reguly display: block, wiec naglowek "Skargi i wnioski"
			 * sklejal sie z poprzednia godzina w jedno slowo "15:00Skargi".
			 * Cudzego arkusza nie przewidzimy, a wyliczony display juz go
			 * uwzglednia - i tak go czytamy, zeby sprawdzic widocznosc.
			 *
			 * Warunek z jednym pikselem lapie tekst schowany technika
			 * "jeden piksel i przyciecie" - tak WordPress i pol swiata
			 * motywow chowa napisy pisane wylacznie dla czytnikow ekranu.
			 *
			 * @param {HTMLElement} element Badany element.
			 * @return {boolean} Czy czytac jego zawartosc.
			 */
			function oceniaj( element ) {
				var styl = window.getComputedStyle( element );
				var obszar;

				if ( 'none' === styl.display || 'hidden' === styl.visibility ) {
					return false;
				}

				obszar = element.getBoundingClientRect();

				if ( obszar.width <= 1 && obszar.height <= 1 && ! element.firstElementChild ) {
					return false;
				}

				if ( 'BR' === element.tagName.toUpperCase() || ( 'inline' !== styl.display && 'contents' !== styl.display ) ) {
					granice.add( element );
				}

				return true;
			}

			/**
			 * Zbiera widoczny tekst obszaru tresci.
			 *
			 * @return {string} Tekst z przejsciami do nowego wiersza na granicach wierszy.
			 */
			function zbierzTekst() {
				var chodzik;
				var kawalki = [];
				var wezel;

				granice = new window.Set();

				chodzik = document.createTreeWalker(
					obszarTresci(),
					window.NodeFilter.SHOW_ELEMENT | window.NodeFilter.SHOW_TEXT,
					{
						acceptNode: function ( wezel ) {
							if ( 3 === wezel.nodeType ) {
								return window.NodeFilter.FILTER_ACCEPT;
							}

							/* Odrzucenie w chodziku pomija cale poddrzewo, nie sam element. */
							if ( pomijane[ wezel.tagName.toUpperCase() ] ) {
								return window.NodeFilter.FILTER_REJECT;
							}

							if ( wezel.hasAttribute( 'hidden' ) || 'true' === wezel.getAttribute( 'aria-hidden' ) ) {
								return window.NodeFilter.FILTER_REJECT;
							}

							/* Wlasny panel - gdyby obszarem tresci okazalo sie cale body. */
							if ( wezel.classList.contains( 'alyxa' ) ) {
								return window.NodeFilter.FILTER_REJECT;
							}

							if ( ! oceniaj( wezel ) ) {
								return window.NodeFilter.FILTER_REJECT;
							}

							return window.NodeFilter.FILTER_ACCEPT;
						}
					}
				);

				while ( ( wezel = chodzik.nextNode() ) ) {
					if ( 1 === wezel.nodeType ) {
						if ( granice.has( wezel ) ) {
							kawalki.push( '\n' );
						}

						continue;
					}

					kawalki.push( wezel.data );
				}

				granice = null;

				return kawalki.join( '' );
			}

			/**
			 * Doklada kawalek do listy, tnac go, gdy przekracza limit.
			 *
			 * Tniemy najpierw po przecinkach i srednikach, bo tam i tak
			 * wypada oddech, a dopiero w ostatecznosci po spacjach.
			 *
			 * @param {Array<string>} lista   Lista kawalkow.
			 * @param {string}        kawalek Tekst do dolozenia.
			 * @return {void}
			 */
			function dolozKawalek( lista, kawalek ) {
				var reszta = kawalek;
				var ciecie;

				while ( reszta.length > LIMIT ) {
					ciecie = reszta.lastIndexOf( ', ', LIMIT );

					if ( ciecie < LIMIT / 3 ) {
						ciecie = reszta.lastIndexOf( '; ', LIMIT );
					}

					if ( ciecie < LIMIT / 3 ) {
						ciecie = reszta.lastIndexOf( ' ', LIMIT );
					}

					if ( ciecie < 1 ) {
						ciecie = LIMIT;
					}

					lista.push( reszta.slice( 0, ciecie + 1 ).trim() );

					reszta = reszta.slice( ciecie + 1 );
				}

				if ( reszta.trim() ) {
					lista.push( reszta.trim() );
				}
			}

			/**
			 * Czy znak jest mala litera.
			 *
			 * Bez klas Unicode, bo te wymagaja flagi 'u' i nowszego silnika,
			 * a porownanie wielkosci liter dziala tak samo dla "z" i dla "ż".
			 *
			 * @param {string} znak Pojedynczy znak.
			 * @return {boolean}
			 */
			function malaLitera( znak ) {
				return !! znak && znak === znak.toLowerCase() && znak !== znak.toUpperCase();
			}

			/**
			 * Dzieli wiersz na zdania.
			 *
			 * KROPKA KONCZY ZDANIE TYLKO WTEDY, GDY STOI PRZED ODSTEPEM.
			 * Bez tego warunku adres "sekretariat@szkola.example.pl" rozpada
			 * sie na trzy wypowiedzi, z ktorych ostatnia brzmi "pl" - to nie
			 * jest przypadek teoretyczny, tylko pomiar ze strony kontaktowej
			 * tego projektu. Ta sama regula ratuje godziny (8.30) i kwoty.
			 *
			 * Drugi warunek: po kropce ma isc cos, co moze zaczynac zdanie.
			 * Mala litera znaczy, ze kropka nalezala do skrotu ("np. tak"),
			 * a nie do konca mysli.
			 *
			 * Trzeci: przed kropka ma stac cos dluzszego niz skrot. "ul."
			 * przed nazwa ulicy ma po sobie wielka litere i przechodzilo
			 * przez dwa poprzednie warunki, a wychodzila z tego osobna
			 * wypowiedz brzmiaca "ul". W watpliwych przypadkach nie tniemy:
			 * kropka zostawiona w srodku wypowiedzi i tak daje pauze, bo
			 * silnik mowy czyta interpunkcje - a ciecie w zlym miejscu
			 * slychac od razu.
			 *
			 * @param {string} linia Wiersz tekstu.
			 * @return {Array<string>}
			 */
			function naZdania( linia ) {
				return linia
					.replace(
						/([.!?…])(\s+)/g,
						function ( calosc, znak, odstep, gdzie, tekst ) {
							var przed;

							if ( malaLitera( tekst.charAt( gdzie + calosc.length ) ) ) {
								return calosc;
							}

							przed = tekst.slice( 0, gdzie ).split( /[\s(„"]/ ).pop();

							/*
							 * Krotkie slowo zakonczone mala litera to skrot:
							 * "ul.", "godz.", "Godz.", "np.". Ostatni znak
							 * musi byc litera, bo inaczej regula zjadalaby
							 * takze koniec zdania po godzinie ("15.00.").
							 */
							if ( '.' === znak && przed.length <= 5 && malaLitera( przed.charAt( przed.length - 1 ) ) ) {
								return calosc;
							}

							return znak + '\n';
						}
					)
					.split( '\n' );
			}

			/**
			 * Dzieli tekst na wypowiedzi.
			 *
			 * Jedno zdanie to jedna wypowiedz. Nie sklejamy krotkich zdan
			 * w wieksze paczki: przerwa miedzy wypowiedziami jest slyszalna
			 * i wypada dokladnie tam, gdzie czytajacy sam by ja zrobil.
			 *
			 * @param {string} tekst Zebrany tekst.
			 * @return {Array<string>}
			 */
			function naWypowiedzi( tekst ) {
				var linie = tekst.split( '\n' );
				var wynik = [];
				var linia;
				var zdania;
				var i;
				var j;

				for ( i = 0; i < linie.length; i++ ) {
					linia = linie[ i ].replace( /\s+/g, ' ' ).trim();

					/*
					 * Wiersz bez ani jednej litery i cyfry to sama grafika
					 * albo interpunkcja - myslnik oddzielajacy sekcje, strzalka
					 * z przycisku. Zakresy zapisane numerami, zeby plik dalo
					 * sie przeczytac takze wtedy, gdy cos po drodze zgubi
					 * kodowanie: alfabet lacinski z ogonkami, grecki i cyrylica.
					 */
					if ( ! linia || ! /[0-9a-z\u00c0-\u024f\u0370-\u04ff]/i.test( linia ) ) {
						continue;
					}

					zdania = naZdania( linia );

					for ( j = 0; j < zdania.length; j++ ) {
						if ( zdania[ j ].trim() ) {
							dolozKawalek( wynik, zdania[ j ].trim() );
						}
					}
				}

				return wynik;
			}

			/**
			 * Doprowadza etykiete przycisku i komunikat do zgodnosci ze stanem.
			 *
			 * Etykieta mowi, co zrobi nastepne nacisniecie, a komunikat -
			 * co dzieje sie teraz. Dwie rozne rzeczy dwoma roznymi zdaniami:
			 * gdyby mowily to samo, czytnik ekranu powtorzylby je po sobie.
			 *
			 * @return {void}
			 */
			function odswiez() {
				if ( ! przyciskCzytaj ) {
					return;
				}

				if ( 'czyta' === stanOdczytu ) {
					przyciskCzytaj.textContent = dane( 'pauza' ) || etykietaCzytaj;
					komunikat.textContent = dane( 'trwa' );

					return;
				}

				if ( 'wstrzymany' === stanOdczytu ) {
					przyciskCzytaj.textContent = dane( 'wznow' ) || etykietaCzytaj;
					komunikat.textContent = dane( 'wstrzymane' );

					return;
				}

				przyciskCzytaj.textContent = etykietaCzytaj;
				komunikat.textContent = '';
			}

			/**
			 * Wstawia wypowiedzi do kolejki przegladarki.
			 *
			 * Wszystkie naraz, a nie po jednej na koniec poprzedniej: kolejka
			 * przegladarki laczy je bez slyszalnej dziury, a my i tak mamy
			 * nad caloscia wladze przez pause, resume i cancel.
			 *
			 * @param {Array<string>} wypowiedzi Teksty do przeczytania.
			 * @param {number}        moje       Numer podejscia.
			 * @return {void}
			 */
			function kolejkuj( wypowiedzi, moje ) {
				var glos = znajdzGlos();
				var kod = jezyk();
				var slowo;
				var i;

				if ( moje !== pokolenie ) {
					return;
				}

				for ( i = 0; i < wypowiedzi.length; i++ ) {
					slowo = new window.SpeechSynthesisUtterance( wypowiedzi[ i ] );
					slowo.lang = kod;

					if ( glos ) {
						slowo.voice = glos;
					}

					if ( i === wypowiedzi.length - 1 ) {
						slowo.onend = function () {
							if ( moje !== pokolenie ) {
								return;
							}

							stanOdczytu = 'bezczynny';

							odswiez();
						};
					}

					slowo.onerror = function ( zdarzenie ) {
						/*
						 * "canceled" i "interrupted" zglaszamy sobie sami,
						 * naciskajac stop albo zaczynajac od nowa. Kazdy inny
						 * blad - brak glosu, awaria silnika mowy - konczy
						 * czytanie, wiec panel ma o tym wiedziec.
						 */
						if ( moje !== pokolenie || 'canceled' === zdarzenie.error || 'interrupted' === zdarzenie.error ) {
							return;
						}

						stanOdczytu = 'bezczynny';

						odswiez();
					};

					mowa.speak( slowo );
				}
			}

			/**
			 * Zaczyna czytanie od poczatku.
			 *
			 * @return {void}
			 */
			function zacznij() {
				var wypowiedzi = naWypowiedzi( zbierzTekst() );
				var bylaKolejka = mowa.speaking || mowa.pending;
				var moje;

				if ( ! wypowiedzi.length ) {
					komunikat.textContent = dane( 'pusto' );

					return;
				}

				pokolenie++;
				moje = pokolenie;

				mowa.cancel();

				/*
				 * PO CANCEL NA WSTRZYMANEJ KOLEJCE SILNIK ZOSTAJE WSTRZYMANY.
				 * Nastepne speak() trafia wtedy do kolejki, ktora stoi, i nie
				 * slychac nic - a przycisk twierdzi, ze czyta. resume() na
				 * pustej kolejce nie robi nic zlego, wiec wolamy go zawsze.
				 */
				mowa.resume();

				stanOdczytu = 'czyta';

				odswiez();

				/*
				 * cancel() dziala z opoznieniem, a wypowiedz wstawiona w tej
				 * samej chwili potrafi zginac razem z kasowana kolejka. Gdy
				 * nie bylo czego kasowac, nie ma tez na co czekac.
				 */
				if ( bylaKolejka ) {
					window.setTimeout( function () {
						kolejkuj( wypowiedzi, moje );
					}, 120 );

					return;
				}

				kolejkuj( wypowiedzi, moje );
			}

			/**
			 * Wstrzymuje czytanie.
			 *
			 * Sprawdzamy po chwili, czy silnik naprawde stanal. Czesc
			 * przegladarek mobilnych na pause() nie reaguje albo kasuje
			 * kolejke - a przycisk, ktory mowi "Wznow", gdy nie ma czego
			 * wznowic, jest gorszy niz brak przycisku.
			 *
			 * @return {void}
			 */
			function wstrzymaj() {
				mowa.pause();

				stanOdczytu = 'wstrzymany';

				odswiez();

				window.setTimeout( function () {
					if ( 'wstrzymany' !== stanOdczytu || mowa.paused ) {
						return;
					}

					stanOdczytu = mowa.speaking ? 'czyta' : 'bezczynny';

					odswiez();
				}, 300 );
			}

			/**
			 * Konczy czytanie i wraca na poczatek.
			 *
			 * @return {void}
			 */
			function zatrzymaj() {
				pokolenie++;

				mowa.cancel();
				mowa.resume();

				stanOdczytu = 'bezczynny';

				odswiez();
			}

			/**
			 * Wyjscie ze strony.
			 *
			 * Bez tego glos czyta dalej na nastepnej podstronie, bo silnik
			 * mowy nalezy do przegladarki, a nie do dokumentu. Zerujemy takze
			 * stan, zeby powrot przyciskiem "wstecz" - ktory potrafi przywrocic
			 * strone w calosci, razem z naszymi etykietami - nie zastal
			 * przycisku z napisem "Wstrzymaj".
			 *
			 * @return {void}
			 */
			function zejscie() {
				zatrzymaj();
			}

			/**
			 * Pokazuje pozycje, gdy urzadzenie ma glos w jezyku strony.
			 *
			 * @return {void}
			 */
			function sprawdzGlosy() {
				if ( ! pozycja || ! pozycja.hidden ) {
					return;
				}

				if ( znajdzGlos() ) {
					pozycja.hidden = false;
				}
			}

			return {
				wlacz: function ( ktos ) {
					kontekst = ktos;
					pozycja = kontekst.pozycja;

					/* Bez silnika mowy pozycja zostaje ukryta - nie ma czym czytac. */
					if ( ! pozycja || ! mowa || ! window.SpeechSynthesisUtterance ) {
						return;
					}

					przyciskCzytaj = pozycja.querySelector( '[data-alyxa-akcja="czytaj"]' );
					komunikat = pozycja.querySelector( '[data-alyxa-komunikat]' );

					if ( ! przyciskCzytaj || ! komunikat ) {
						return;
					}

					etykietaCzytaj = przyciskCzytaj.textContent.trim();

					/*
					 * Lista glosow bywa pusta przy pierwszym pytaniu i dojezdza
					 * chwile pozniej wlasnym zdarzeniem. Pytamy wiec dwa razy:
					 * teraz i wtedy, gdy przegladarka da znac, ze juz wie.
					 */
					sprawdzGlosy();

					mowa.addEventListener( 'voiceschanged', sprawdzGlosy );
					window.addEventListener( 'pagehide', zejscie );
				},

				wylacz: function () {
					/*
					 * Pytamy o przycisk, a nie o pozycje: gdy urzadzenie nie
					 * ma silnika mowy, wlacz konczy sie przed dojsciem do
					 * czegokolwiek, co trzeba by teraz sprzatnac.
					 */
					if ( ! przyciskCzytaj ) {
						pozycja = null;

						return;
					}

					zatrzymaj();

					mowa.removeEventListener( 'voiceschanged', sprawdzGlosy );
					window.removeEventListener( 'pagehide', zejscie );

					pozycja.hidden = true;
					pozycja = null;
					przyciskCzytaj = null;
					komunikat = null;
				},

				akcja: function ( nazwa ) {
					if ( ! przyciskCzytaj ) {
						return;
					}

					if ( 'stop' === nazwa ) {
						zatrzymaj();

						return;
					}

					if ( 'czytaj' !== nazwa ) {
						return;
					}

					if ( 'czyta' === stanOdczytu ) {
						wstrzymaj();

						return;
					}

					if ( 'wstrzymany' === stanOdczytu ) {
						mowa.resume();

						stanOdczytu = 'czyta';

						odswiez();

						return;
					}

					zacznij();
				},

				/*
				 * KAZDA ZMIANA USTAWIEN PRZERYWA CZYTANIE. Tekst zebralismy
				 * w chwili nacisniecia przycisku, a wlaczenie kontrastu albo
				 * innej czcionki potrafi zmienic to, co na stronie widac -
				 * czytalibysmy dalej stan, ktorego juz nie ma. Przy okazji
				 * daje to wyjscie awaryjne: dowolny przelacznik ucisza glos.
				 */
				zmiana: function () {
					if ( 'bezczynny' !== stanOdczytu ) {
						zatrzymaj();
					}
				}
			};
		}() )
	};

	var czynneZachowania = {};

	/**
	 * Sklada kontekst podawany zachowaniu.
	 *
	 * Zachowanie dostaje wszystko, czego potrzebuje, zamiast szukac tego
	 * samo po dokumencie: swoja pozycje w panelu i tablice 'dane' z rejestru,
	 * ktorej rdzen nie czyta i nie rozumie.
	 *
	 * @param {string} slug Slug modulu.
	 * @return {Object}
	 */
	function kontekstZachowania( slug ) {
		return {
			slug: slug,
			dane: moduly[ slug ].dane || {},
			pozycja: panel.querySelector( '[data-alyxa-pozycja="' + slug + '"]' )
		};
	}

	/**
	 * Doprowadza zachowania do zgodnosci z wyborem.
	 *
	 * Wlaczamy i wylaczamy tylko przy zmianie, a nie przy kazdym wywolaniu:
	 * inaczej kazde klikniecie w dowolny inny modul zdejmowaloby i zakladalo
	 * maske od nowa, razem z nasluchiwaniem.
	 *
	 * MODUL BEZ STANU JEST CZYNNY OD RAZU. Typ 'akcje' nie ma czego zapisac
	 * w pamieci przegladarki - jego zachowanie ma dzialac zawsze wtedy, gdy
	 * modul jest wlaczony na stronie, bo inaczej nie mialby kto przygotowac
	 * przyciskow ani sprawdzic, czy urzadzenie w ogole to potrafi.
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

			czynne = !! moduly[ slug ] && ( 'akcje' === moduly[ slug ].typ || true === stan[ slug ] );

			if ( czynne === !! czynneZachowania[ slug ] ) {
				continue;
			}

			czynneZachowania[ slug ] = czynne;

			if ( czynne ) {
				zachowania[ slug ].wlacz( kontekstZachowania( slug ) );
			} else {
				zachowania[ slug ].wylacz();
			}
		}
	}

	/**
	 * Zglasza zachowaniom zmiane ustawien.
	 *
	 * @return {void}
	 */
	function powiadomOZmianie() {
		var slug;

		for ( slug in zachowania ) {
			if ( ! Object.prototype.hasOwnProperty.call( zachowania, slug ) ) {
				continue;
			}

			if ( czynneZachowania[ slug ] && zachowania[ slug ].zmiana ) {
				zachowania[ slug ].zmiana();
			}
		}
	}

	/**
	 * Wykonuje czynnosc modulu.
	 *
	 * @param {string} slug  Slug modulu.
	 * @param {string} nazwa Slug czynnosci.
	 * @return {void}
	 */
	function wykonaj( slug, nazwa ) {
		if ( ! moduly[ slug ] || ! czynneZachowania[ slug ] || ! zachowania[ slug ].akcja ) {
			return;
		}

		zachowania[ slug ].akcja( nazwa );
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

		/* Modul czynnosci nie ma stanu, wiec nie ma tu czego opisywac. */
		if ( 'akcje' === moduly[ slug ].typ ) {
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
		if ( ! moduly[ slug ] || 'akcje' === moduly[ slug ].typ ) {
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
		powiadomOZmianie();
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
		powiadomOZmianie();
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
		var czynnosc;

		if ( kontrolka ) {
			/*
			 * Ten sam atrybut niesie modul, ale nie to samo nacisniecie.
			 * Przycisk z data-alyxa-akcja robi cos tu i teraz i niczego nie
			 * zapisuje; kazdy inny przelacza ustawienie.
			 */
			czynnosc = kontrolka.getAttribute( 'data-alyxa-akcja' );

			if ( czynnosc ) {
				wykonaj( kontrolka.getAttribute( 'data-alyxa-modul' ), czynnosc );
			} else {
				przelacz( kontrolka.getAttribute( 'data-alyxa-modul' ) );
			}

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
