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
		var wartosc;

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

			/*
			 * Odsiewamy takze moduly, ktore stanu nie maja: czynnosc
			 * i odnosnik nie sa ustawieniem, wiec wpis pod ich slugiem moze
			 * pochodzic tylko z recznej edycji pamieci przegladarki albo
			 * ze zmiany typu modulu miedzy wersjami. Bez tego warunku
			 * zalozylibysmy na <html> klase, ktorej nie obsluguje zaden
			 * arkusz i ktorej nie da sie zdjac zadnym przelacznikiem.
			 */
			if ( ! moduly[ klucz ] || -1 === [ 'przelacznik', 'stopnie', 'kolory' ].indexOf( moduly[ klucz ].typ ) ) {
				odsiane = true;

				continue;
			}

			wartosc = poprawWartosc( klucz, surowe[ klucz ] );

			/*
			 * Wartosc bez skutku - falsz, zero, zepsuty obiekt kolorow - nie
			 * zostaje w pamieci. Inaczej wpis przezylby kazde przelaczenie,
			 * a skrypt w naglowku czytalby go przy kazdej odslonie.
			 */
			if ( false === wartosc || 0 === wartosc ) {
				odsiane = true;

				continue;
			}

			zapisane[ klucz ] = wartosc;
		}

		return zapisane;
	}

	/**
	 * Sprowadza wartosc do zakresu, jaki modul dopuszcza.
	 *
	 * @param {string} slug    Slug modulu.
	 * @param {*}      wartosc Wartosc z pamieci albo z klikniecia.
	 * @return {boolean|number} Wartosc do zapisania.
	 */
	function poprawWartosc( slug, wartosc ) {
		if ( 'kolory' === moduly[ slug ].typ ) {
			return poprawKolory( slug, wartosc );
		}

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
		var pole;
		var i;

		for ( i = 0; i < korzen.classList.length; i++ ) {
			if ( 0 === korzen.classList[ i ].indexOf( 'alyxa-' ) ) {
				doZdjecia.push( korzen.classList[ i ] );
			}
		}

		for ( i = 0; i < doZdjecia.length; i++ ) {
			korzen.classList.remove( doZdjecia[ i ] );
		}

		/*
		 * Zmienne modulu kolorow zdejmujemy po liscie jego pol, a nie po
		 * przedrostku: --alyxa-odwrocenie-tlo nalezy do zachowania innego
		 * modulu i jego zmiana to sprawa tamtego zachowania.
		 */
		for ( slug in moduly ) {
			if ( Object.prototype.hasOwnProperty.call( moduly, slug ) && 'kolory' === moduly[ slug ].typ ) {
				for ( i = 0; i < ( moduly[ slug ].pola || [] ).length; i++ ) {
					korzen.style.removeProperty( '--alyxa-' + slug + '-' + moduly[ slug ].pola[ i ] );
				}
			}
		}

		for ( slug in stan ) {
			if ( ! Object.prototype.hasOwnProperty.call( stan, slug ) || ! moduly[ slug ] ) {
				continue;
			}

			if ( true === stan[ slug ] ) {
				korzen.classList.add( 'alyxa-' + slug );
			} else if ( 'number' === typeof stan[ slug ] && stan[ slug ] > 0 ) {
				korzen.classList.add( 'alyxa-' + slug + '-' + stan[ slug ] );
			} else if ( stan[ slug ] && 'object' === typeof stan[ slug ] ) {
				korzen.classList.add( 'alyxa-' + slug );

				for ( pole in stan[ slug ] ) {
					if ( Object.prototype.hasOwnProperty.call( stan[ slug ], pole ) ) {
						korzen.style.setProperty( '--alyxa-' + slug + '-' + pole, stan[ slug ][ pole ] );
					}
				}
			}
		}
	}

	/**
	 * SILNIK MOWY - jeden na cala wtyczke.
	 *
	 * CZYTA PRZEGLADARKA, NIE MY. speechSynthesis to glos zainstalowany na
	 * urzadzeniu odwiedzajacego - ten sam, ktorym mowi jego telefon. Zadne
	 * zdanie z tej strony nie jedzie w tym celu na zaden serwer.
	 *
	 * DLACZEGO OSOBNO, A NIE W MODULE ODCZYTU. Bo silnik mowy jest jeden na
	 * przegladarke, a modulow, ktore z niego zyja, jest dwa: odczyt calej
	 * strony i czytanie wskazanego elementu. Druga kopia tego kodu znaczylaby
	 * dwie kolejki mowiace naraz przez ten sam glosnik. Tak jest odwrotnie:
	 * kto zaczyna mowic, przerywa poprzedniemu - i to jest dokladnie to,
	 * czego odwiedzajacy sie spodziewa, gdy w trakcie czytania calej strony
	 * kliknie w jeden akapit.
	 *
	 * WLASCICIEL. Modul, ktory mowi, zostawia tu dwa wywolania zwrotne:
	 * 'koniec' - wypowiedzi sie skonczyly, i 'przerwane' - ktos wszedl mu
	 * w slowo albo mowa zostala zatrzymana. Bez tego drugiego przycisk
	 * przerwanego modulu zostalby z napisem "Wstrzymaj czytanie", choc nic
	 * juz nie czyta.
	 *
	 * CZYTAMY TO, CO WIDAC. Tylko elementy widoczne na ekranie. Tekst
	 * schowany dla oka, a zostawiony dla czytnikow ekranu ("przejdz do
	 * tresci", "menu"), jest tu podwojnie nie na miejscu: odwiedzajacy go
	 * nie widzi, wiec nie zrozumie, skad sie wzial.
	 */
	var silnikMowy = ( function () {
		var mowa = window.speechSynthesis;

		/*
		 * Kawalek dluzszy niz to nic nie zyskuje, a duzo ryzykuje: Chrome od
		 * lat ucina pojedyncza wypowiedz po kilkunastu sekundach, i jest to
		 * jego blad, nie nasz. Krotkie kawalki chronia przed tym za darmo,
		 * bo i tak tniemy tekst na zdania.
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
		 * dokumentu. Zbior, a nie lista znacznikow: patrz zbierz().
		 */
		var granice = null;

		/*
		 * Numer podejscia. cancel() zglasza koniec takze tym wypowiedziom,
		 * ktore wyrzucil z kolejki - bez tego licznika koniec poprzedniego
		 * czytania kasowalby stan nastepnego, zaczetego ulamek sekundy
		 * pozniej.
		 */
		var pokolenie = 0;

		/* Modul, ktory teraz mowi, albo null. */
		var wlasciciel = null;

		var zejscieZalozone = false;

		/**
		 * Czy urzadzenie w ogole potrafi mowic.
		 *
		 * @return {boolean}
		 */
		function dostepny() {
			return !! ( mowa && window.SpeechSynthesisUtterance );
		}

		/**
		 * Jezyk, w ktorym mamy czytac.
		 *
		 * Pierwszenstwo ma to, co deklaruje sam dokument: na stronie
		 * wielojezycznej kazda podstrona ma wlasny atrybut lang, a ustawienie
		 * strony jest jedno na cala instalacje.
		 *
		 * @param {string} zapasowy Jezyk z rejestru modulu.
		 * @return {string}
		 */
		function jezyk( zapasowy ) {
			var zDokumentu = ( korzen.getAttribute( 'lang' ) || '' ).trim();

			return ( zDokumentu || zapasowy || '' ).toLowerCase().replace( /_/g, '-' );
		}

		/**
		 * Wybiera glos do czytania.
		 *
		 * DWA KRYTERIA, W TEJ KOLEJNOSCI. Najpierw glos dzialajacy na
		 * urzadzeniu (localService): glos sieciowy wysyla czytany tekst do
		 * dostawcy przegladarki, a panel obiecuje w stopce, ze wybor zostaje
		 * na tym urzadzeniu - wiec nie my mamy z tej obietnicy robic wyjatek.
		 * Potem zgodnosc calego kodu jezyka, bo pl-PL czyta polski tekst
		 * lepiej niz jakikolwiek inny wariant.
		 *
		 * @param {string} zapasowy Jezyk z rejestru modulu.
		 * @return {SpeechSynthesisVoice|null}
		 */
		function znajdzGlos( zapasowy ) {
			var pelny = jezyk( zapasowy );
			var podstawa = pelny.split( '-' )[ 0 ];
			var lista;
			var najlepszy = null;
			var najlepszaOcena = -1;
			var kod;
			var ocena;
			var i;

			if ( ! podstawa || ! dostepny() ) {
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
		 * Czy urzadzenie ma glos w jezyku tej strony.
		 *
		 * @param {string} zapasowy Jezyk z rejestru modulu.
		 * @return {boolean}
		 */
		function jestGlos( zapasowy ) {
			return !! znajdzGlos( zapasowy );
		}

		/**
		 * Zglasza sie po spis glosow, gdy przegladarka juz go zna.
		 *
		 * Lista glosow bywa pusta przy pierwszym pytaniu i dojezdza chwile
		 * pozniej wlasnym zdarzeniem.
		 *
		 * @param {Function} sluchacz Wywolanie po nadejsciu spisu.
		 * @return {void}
		 */
		function przySpisieGlosow( sluchacz ) {
			if ( dostepny() && mowa.addEventListener ) {
				mowa.addEventListener( 'voiceschanged', sluchacz );
			}
		}

		/**
		 * Ocenia element: czy wchodzimy w niego i czy zaczyna nowy wiersz.
		 *
		 * GRANICE WIERSZY BIERZEMY Z UKLADU, A NIE Z NAZW ZNACZNIKOW.
		 * Pierwsza wersja miala liste znacznikow blokowych i przegrala
		 * z pierwsza napotkana strona: motyw tego projektu daje <small>
		 * regule display: block, wiec naglowek "Skargi i wnioski" sklejal
		 * sie z poprzednia godzina w jedno slowo "15:00Skargi". Cudzego
		 * arkusza nie przewidzimy, a wyliczony display juz go uwzglednia -
		 * i tak go czytamy, zeby sprawdzic widocznosc.
		 *
		 * Warunek z jednym pikselem lapie tekst schowany technika "jeden
		 * piksel i przyciecie" - tak WordPress i pol swiata motywow chowa
		 * napisy pisane wylacznie dla czytnikow ekranu.
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
		 * Zbiera widoczny tekst poddrzewa.
		 *
		 * Korzen dostajemy z zewnatrz, bo to jedyne, czym rozni sie odczyt
		 * calej strony od czytania wskazanego akapitu: pierwszy podaje obszar
		 * tresci, drugi - klikniety element. Sam korzen nie przechodzi przez
		 * sito chodzika, i tak ma byc - skoro odwiedzajacy w niego kliknal,
		 * to go widzi.
		 *
		 * @param {HTMLElement} korzenTekstu Element, z ktorego zbieramy tekst.
		 * @return {string} Tekst z przejsciami do nowego wiersza na granicach wierszy.
		 */
		function zbierz( korzenTekstu ) {
			var chodzik;
			var kawalki = [];
			var wezel;

			if ( ! korzenTekstu ) {
				return '';
			}

			granice = new window.Set();

			chodzik = document.createTreeWalker(
				korzenTekstu,
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

						/* Wlasny panel - gdyby korzeniem okazalo sie cale body. */
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
		 * Tniemy najpierw po przecinkach i srednikach, bo tam i tak wypada
		 * oddech, a dopiero w ostatecznosci po spacjach.
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
		 * a porownanie wielkosci liter dziala tak samo dla "z" i dla "z"
		 * z kropka.
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
		 * Bez tego warunku adres "sekretariat@szkola.example.pl" rozpada sie
		 * na trzy wypowiedzi, z ktorych ostatnia brzmi "pl" - to nie jest
		 * przypadek teoretyczny, tylko pomiar ze strony kontaktowej tego
		 * projektu. Ta sama regula ratuje godziny (8.30) i kwoty.
		 *
		 * Drugi warunek: po kropce ma isc cos, co moze zaczynac zdanie. Mala
		 * litera znaczy, ze kropka nalezala do skrotu ("np. tak"), a nie do
		 * konca mysli.
		 *
		 * Trzeci: przed kropka ma stac cos dluzszego niz skrot. "ul." przed
		 * nazwa ulicy ma po sobie wielka litere i przechodzilo przez dwa
		 * poprzednie warunki, a wychodzila z tego osobna wypowiedz brzmiaca
		 * "ul". W watpliwych przypadkach nie tniemy: kropka zostawiona
		 * w srodku wypowiedzi i tak daje pauze, bo silnik mowy czyta
		 * interpunkcje - a ciecie w zlym miejscu slychac od razu.
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
						 * "ul.", "godz.", "Godz.", "np.". Ostatni znak musi
						 * byc litera, bo inaczej regula zjadalaby takze
						 * koniec zdania po godzinie ("15.00.").
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
			var linie = String( tekst ).split( '\n' );
			var wynik = [];
			var linia;
			var zdania;
			var i;
			var j;

			for ( i = 0; i < linie.length; i++ ) {
				linia = linie[ i ].replace( /\s+/g, ' ' ).trim();

				/*
				 * Wiersz bez ani jednej litery i cyfry to sama grafika albo
				 * interpunkcja - myslnik oddzielajacy sekcje, strzalka
				 * z przycisku. Zakresy zapisane numerami, zeby plik dalo sie
				 * przeczytac takze wtedy, gdy cos po drodze zgubi kodowanie:
				 * alfabet lacinski z ogonkami, grecki i cyrylica.
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
		 * Konczy prace obecnego wlasciciela.
		 *
		 * Wolane zawsze wtedy, gdy mowa urywa sie nie z jego woli: bo ktos
		 * inny zaczal mowic albo bo glos zostal zatrzymany. Modul dowiaduje
		 * sie o tym stad, a nie z kolejki przegladarki, ktora zglasza
		 * "canceled" takze wtedy, gdy sam o to poprosil.
		 *
		 * @return {void}
		 */
		function oddajGlos() {
			var poprzedni = wlasciciel;

			wlasciciel = null;

			if ( poprzedni && poprzedni.przerwane ) {
				poprzedni.przerwane();
			}
		}

		/**
		 * Konczy czytanie zgodnie z planem, na ostatniej wypowiedzi.
		 *
		 * @param {number} moje  Numer podejscia.
		 * @param {Object} opcje Wywolania zwrotne wlasciciela.
		 * @return {void}
		 */
		function zamknij( moje, opcje ) {
			if ( moje !== pokolenie ) {
				return;
			}

			/* Zeby blad zglaszony po koncu nie policzyl tego samego drugi raz. */
			pokolenie++;
			wlasciciel = null;

			if ( opcje.koniec ) {
				opcje.koniec();
			}
		}

		/**
		 * Wstawia wypowiedzi do kolejki przegladarki.
		 *
		 * Wszystkie naraz, a nie po jednej na koniec poprzedniej: kolejka
		 * przegladarki laczy je bez slyszalnej dziury, a my i tak mamy nad
		 * caloscia wladze przez pause, resume i cancel.
		 *
		 * @param {Array<string>} wypowiedzi Teksty do przeczytania.
		 * @param {number}        moje       Numer podejscia.
		 * @param {Object}        opcje      Wywolania zwrotne wlasciciela.
		 * @return {void}
		 */
		function kolejkuj( wypowiedzi, moje, opcje ) {
			var glos = znajdzGlos( opcje.jezyk );
			var kod = jezyk( opcje.jezyk );
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
						zamknij( moje, opcje );
					};
				}

				slowo.onerror = function ( zdarzenie ) {
					/*
					 * "canceled" i "interrupted" zglaszamy sobie sami,
					 * naciskajac stop albo zaczynajac od nowa. Kazdy inny
					 * blad - brak glosu, awaria silnika mowy - konczy
					 * czytanie, wiec panel ma o tym wiedziec.
					 */
					if ( 'canceled' === zdarzenie.error || 'interrupted' === zdarzenie.error ) {
						return;
					}

					zamknij( moje, opcje );
				};

				mowa.speak( slowo );
			}
		}

		/**
		 * Czyta podane wypowiedzi, przerywajac to, co bylo czytane wczesniej.
		 *
		 * @param {Array<string>} wypowiedzi Teksty do przeczytania.
		 * @param {Object}        opcje      Jezyk zapasowy i wywolania zwrotne
		 *                                   'koniec' oraz 'przerwane'.
		 * @return {void}
		 */
		function mow( wypowiedzi, opcje ) {
			var bylaKolejka;
			var moje;

			if ( ! dostepny() || ! wypowiedzi || ! wypowiedzi.length ) {
				return;
			}

			bylaKolejka = mowa.speaking || mowa.pending;

			oddajGlos();

			pokolenie++;
			moje = pokolenie;
			wlasciciel = opcje;

			mowa.cancel();

			/*
			 * PO CANCEL NA WSTRZYMANEJ KOLEJCE SILNIK ZOSTAJE WSTRZYMANY.
			 * Nastepne speak() trafia wtedy do kolejki, ktora stoi, i nie
			 * slychac nic - a przycisk twierdzi, ze czyta. resume() na pustej
			 * kolejce nie robi nic zlego, wiec wolamy go zawsze.
			 */
			mowa.resume();

			if ( ! zejscieZalozone ) {
				/*
				 * Bez tego glos czyta dalej na nastepnej podstronie, bo silnik
				 * mowy nalezy do przegladarki, a nie do dokumentu. Zakladamy
				 * to przy pierwszej wypowiedzi i juz nie zdejmujemy: silnik
				 * dziela dwa moduly, wiec zdejmowanie wymagaloby ich liczenia,
				 * a cancel() na milczacym silniku i tak nie robi nic.
				 */
				window.addEventListener( 'pagehide', zatrzymaj );
				zejscieZalozone = true;
			}

			/*
			 * cancel() dziala z opoznieniem, a wypowiedz wstawiona w tej samej
			 * chwili potrafi zginac razem z kasowana kolejka. Gdy nie bylo
			 * czego kasowac, nie ma tez na co czekac.
			 */
			if ( bylaKolejka ) {
				window.setTimeout( function () {
					kolejkuj( wypowiedzi, moje, opcje );
				}, 120 );

				return;
			}

			kolejkuj( wypowiedzi, moje, opcje );
		}

		/**
		 * Wstrzymuje czytanie.
		 *
		 * Sprawdzamy po chwili, czy silnik naprawde stanal. Czesc przegladarek
		 * mobilnych na pause() nie reaguje albo kasuje kolejke - a przycisk,
		 * ktory mowi "Wznow", gdy nie ma czego wznowic, jest gorszy niz brak
		 * przycisku.
		 *
		 * @param {Function} gdyNieStanal Wywolanie z informacja, czy mowa trwa.
		 * @return {void}
		 */
		function wstrzymaj( gdyNieStanal ) {
			if ( ! dostepny() ) {
				return;
			}

			mowa.pause();

			window.setTimeout( function () {
				if ( mowa.paused || ! gdyNieStanal ) {
					return;
				}

				gdyNieStanal( !! mowa.speaking );
			}, 300 );
		}

		/**
		 * Wznawia wstrzymane czytanie.
		 *
		 * @return {void}
		 */
		function wznow() {
			if ( dostepny() ) {
				mowa.resume();
			}
		}

		/**
		 * Konczy czytanie i kasuje kolejke.
		 *
		 * @return {void}
		 */
		function zatrzymaj() {
			if ( ! dostepny() ) {
				return;
			}

			pokolenie++;

			mowa.cancel();
			mowa.resume();

			oddajGlos();
		}

		return {
			dostepny: dostepny,
			jestGlos: jestGlos,
			przySpisieGlosow: przySpisieGlosow,
			zbierz: zbierz,
			naWypowiedzi: naWypowiedzi,
			mow: mow,
			wstrzymaj: wstrzymaj,
			wznow: wznow,
			zatrzymaj: zatrzymaj
		};
	}() );

	/**
	 * SLEDZENIE WYSOKOSCI - jeden mechanizm na wszystkie prowadnice.
	 *
	 * Dwa moduly potrzebuja dokladnie tego samego: wiedziec, na jakiej
	 * wysokosci okna odwiedzajacy wlasnie czyta. Maska czytania stawia tam
	 * jasne pasmo, linia czytania - kreske. Druga kopia tego kodu znaczylaby
	 * dwa nasluchy na kazdy ruch myszy i dwie petle klatek robiace to samo.
	 *
	 * SLEDZIMY WSKAZNIK I FOKUS, a nie sam wskaznik. Bez fokusu obie
	 * prowadnice bylyby modulami wylacznie dla myszy: ktos, kto chodzi po
	 * stronie tabulatorem, zostalby z pasmem stojacym w miejscu.
	 *
	 * NASLUCH ZAKLADA SIE PRZY PIERWSZYM SLUCHACZU i zdejmuje przy ostatnim.
	 * Wylaczony modul nie zostawia po sobie nasluchiwania - to jest ta sama
	 * zasada, ktora rzadzi cala wtyczka, tylko schowana o poziom nizej.
	 */
	var sledzeniePionu = ( function () {
		var sluchacze = [];
		var ostatniY = null;
		var czekaNaKlatke = false;
		var celFokusu = null;
		var zrodloFokusu = null;
		var klatekZaFokusem = 0;

		/**
		 * Ostatnio wskazana wysokosc.
		 *
		 * Zanim mysz sie ruszy i zanim cokolwiek dostanie fokus, nie wiemy
		 * nic - a prowadnica ma gdzies stac. Srodek okna jest jedynym
		 * miejscem, ktore niczego nie sugeruje.
		 *
		 * @return {number}
		 */
		function wysokosc() {
			return null === ostatniY ? window.innerHeight / 2 : ostatniY;
		}

		/**
		 * Podaje sluchaczom biezaca wysokosc.
		 *
		 * Drugi argument to element, ktory ta wysokosc wyznaczyl, albo null,
		 * gdy wyznaczyl ja wskaznik. Prowadnice robia z tym rozne rzeczy:
		 * pasmo maski srodkuje sie na wysokosci, a kreska linii woli stanac
		 * pod spodem elementu, zeby go nie przekreslic.
		 *
		 * @return {void}
		 */
		function powiadom() {
			var i;

			for ( i = 0; i < sluchacze.length; i++ ) {
				sluchacze[ i ]( wysokosc(), zrodloFokusu );
			}
		}

		/**
		 * Przelicza polozenie i podaje je dalej.
		 *
		 * Polozenie celu fokusu czytamy TUTAJ, a nie w chwili zdarzenia.
		 * Przejscie tabulatorem przewija strone, a motyw przewija ja plynnie -
		 * w chwili focusin element jest jeszcze tam, gdzie byl przed
		 * przewinieciem. Dopoki jego prostokat sie rusza, prosimy o kolejna
		 * klatke; limit klatek jest po to, zeby element, ktory porusza sie
		 * sam z siebie, nie trzymal nas w petli bez konca.
		 *
		 * @return {void}
		 */
		function przesun() {
			var obszarCelu;
			var wysokoscCelu;

			if ( celFokusu ) {
				obszarCelu = celFokusu.getBoundingClientRect();
				wysokoscCelu = obszarCelu.top + obszarCelu.height / 2;

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

			powiadom();
		}

		/**
		 * Odklada przeliczenie do najblizszej klatki.
		 *
		 * Wskaznik potrafi zglosic kilkaset zdarzen na sekunde, a ekran i tak
		 * rysuje szescdziesiat razy. Bez tej bramki liczylibysmy polozenie
		 * kilka razy na klatke i za kazdym razem ruszali ukladem strony.
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
		 * strone, a nie zeby cos wskazac - prowadnica skakalaby przy kazdym
		 * przewinieciu i uciekala spod tekstu, ktory wlasnie nadjezdza.
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
			zrodloFokusu = null;
			ostatniY = zdarzenie.clientY;

			zaplanuj();
		}

		/**
		 * Przejscie fokusu.
		 *
		 * Kontrolki panelu pomijamy - fokus na przelaczniku nie jest
		 * czytaniem strony, a prowadnica skakalaby na panel przy kazdym
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
			zrodloFokusu = cel;
			klatekZaFokusem = 0;

			zaplanuj();
		}

		/**
		 * Zaklada albo zdejmuje nasluchiwanie.
		 *
		 * @param {boolean} czy Czy nasluchiwac.
		 * @return {void}
		 */
		function nasluchuj( czy ) {
			if ( czy ) {
				document.addEventListener( 'pointermove', zeWskaznika, { passive: true } );
				document.addEventListener( 'focusin', zFokusu );
				window.addEventListener( 'resize', zaplanuj );

				return;
			}

			document.removeEventListener( 'pointermove', zeWskaznika );
			document.removeEventListener( 'focusin', zFokusu );
			window.removeEventListener( 'resize', zaplanuj );
		}

		return {

			/**
			 * Doklada sluchacza i od razu podaje mu biezaca wysokosc.
			 *
			 * @param {Function} sluchacz Wywolanie ( wysokosc, cel ).
			 * @return {void}
			 */
			dolacz: function ( sluchacz ) {
				if ( -1 !== sluchacze.indexOf( sluchacz ) ) {
					return;
				}

				sluchacze.push( sluchacz );

				if ( 1 === sluchacze.length ) {
					nasluchuj( true );
				}

				sluchacz( wysokosc(), zrodloFokusu );
			},

			/**
			 * Zdejmuje sluchacza.
			 *
			 * @param {Function} sluchacz Ten sam, ktory byl dolaczony.
			 * @return {void}
			 */
			odlacz: function ( sluchacz ) {
				var i = sluchacze.indexOf( sluchacz );

				if ( -1 === i ) {
					return;
				}

				sluchacze.splice( i, 1 );

				if ( ! sluchacze.length ) {
					nasluchuj( false );

					celFokusu = null;
					zrodloFokusu = null;
				}
			}
		};
	}() );

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
	 * UMOWA ZACHOWANIA - piec metod, wszystkie opcjonalne:
	 *
	 *     przygotuj( kontekst )  raz przy starcie, dla kazdego modulu
	 *                            wlaczonego na stronie - niezaleznie od tego,
	 *                            czy odwiedzajacy go wlaczyl
	 *     wlacz( kontekst )      modul wlaczony przez odwiedzajacego
	 *     wylacz()               modul wylaczony; ma po sobie posprzatac
	 *     akcja( slug )          odwiedzajacy nacisnal przycisk czynnosci
	 *     zmiana()               zmienilo sie dowolne ustawienie panelu
	 *
	 * Kontekst niesie slug, tablice 'dane' z rejestru, pozycje modulu
	 * w panelu i metode pokaz(), ktora te pozycje odkrywa razem z dzialem,
	 * w ktorym lezy.
	 *
	 * Modul z typem 'akcje' nie ma stanu, wiec jego zachowanie jest czynne
	 * przez caly czas, gdy modul jest wlaczony na stronie. Przelacznik i
	 * stopnie - tylko wtedy, gdy odwiedzajacy je wlaczyl.
	 *
	 * PO CO ODDZIELNE przygotuj. Kontrolka warunkowa - taka, o ktorej wie
	 * tylko przegladarka - wychodzi z serwera ukryta i odkryc ja moze
	 * wylacznie zachowanie modulu. Przelacznik ma z tym klopot bez wyjscia:
	 * jego wlacz() zaczyna dzialac dopiero wtedy, gdy odwiedzajacy nacisnie
	 * kafelek, a kafelka ukrytego nacisnac sie nie da. Stad metoda wolana
	 * niezaleznie od wyboru: tu odkrywa sie kontrolki, w wlacz() dziala sie.
	 *
	 * DLACZEGO W TYM SAMYM PLIKU, A NIE W OSOBNYM SKLEJANYM ARKUSZU JS.
	 * Bo caly ten kod to kilka kilobajtow, a drugi potok sklejania kosztowalby
	 * wiecej niz oszczedza - decyzja opisana w inc/zasoby.php. Modul wylaczony
	 * nie zostawia za to na stronie zadnego sladu: bez elementu, bez
	 * nasluchiwania, bez jednej reguly CSS.
	 */
	var zachowania = {
		/**
		 * ODWROCENIE BARW - tlo pod odwrocona strona.
		 *
		 * FILTR ODWRACA DZIECI BODY, ALE NIE SAMO BODY (dlaczego - patrz
		 * moduly/odwrocenie.css). Motyw blokowy maluje tlo wlasnie na body,
		 * a tresc lezy na nim przezroczysta: bez tego zachowania czarny tekst
		 * zmienialby sie w bialy na nadal bialym tle, czyli znikal. Liczymy
		 * wiec, jakim kolorem stalo by sie tlo strony po odwroceniu, i dajemy
		 * go body. Arkusz ma zapas #000 na chwile przed uruchomieniem skryptu -
		 * dla jasnych stron to jest dokladnie ten wynik.
		 *
		 * TLO CZYTAMY BEZ NASZEJ KLASY, bo nasza klasa to tlo wlasnie zmienia -
		 * odczyt z nia na miejscu oddawalby nasz wlasny wynik.
		 */
		odwrocenie: ( function () {
			/**
			 * Tlo strony w RGB, zanim cokolwiek odwrocilismy.
			 *
			 * @return {number[]}
			 */
			function tloStrony() {
				var miala = korzen.classList.contains( 'alyxa-odwrocenie' );
				var elementy = [ document.body, korzen ];
				var wynik = [ 255, 255, 255 ];
				var czesci;
				var i;

				korzen.classList.remove( 'alyxa-odwrocenie' );

				for ( i = 0; i < elementy.length; i++ ) {
					czesci = ( window.getComputedStyle( elementy[ i ] ).backgroundColor.match( /[\d.]+/g ) || [] ).map( Number );

					/* rgba z zerowym kanalem alfa to "brak tla" - szukamy dalej. */
					if ( czesci.length >= 3 && ( czesci.length < 4 || czesci[ 3 ] > 0 ) ) {
						wynik = czesci.slice( 0, 3 );

						break;
					}
				}

				if ( miala ) {
					korzen.classList.add( 'alyxa-odwrocenie' );
				}

				return wynik;
			}

			/**
			 * Kolor po invert(1) i hue-rotate(180deg), tak jak liczy filtr.
			 *
			 * Macierz obrotu barwy o 180 stopni z definicji hue-rotate
			 * w specyfikacji Filter Effects; kazdy jej wiersz sumuje sie do 1,
			 * wiec szarosc zostaje szaroscia.
			 *
			 * @param {number[]} rgb Kolor 0-255.
			 * @return {string}
			 */
			function poOdwroceniu( rgb ) {
				var r = 255 - rgb[ 0 ];
				var g = 255 - rgb[ 1 ];
				var b = 255 - rgb[ 2 ];
				var wyjscie = [
					-0.574 * r + 1.430 * g + 0.144 * b,
					0.426 * r + 0.430 * g + 0.144 * b,
					0.426 * r + 1.430 * g - 0.856 * b
				];

				return 'rgb(' + wyjscie.map( function ( kanal ) {
					return Math.round( Math.min( 255, Math.max( 0, kanal ) ) );
				} ).join( ',' ) + ')';
			}

			/**
			 * Ustawia tlo pod odwrocona strona.
			 *
			 * @return {void}
			 */
			function ustaw() {
				korzen.style.setProperty( '--alyxa-odwrocenie-tlo', poOdwroceniu( tloStrony() ) );
			}

			return {
				wlacz: ustaw,

				/* Ciemny tryb albo wlasne kolory wlaczone w trakcie zmieniaja tlo. */
				zmiana: ustaw,

				wylacz: function () {
					korzen.style.removeProperty( '--alyxa-odwrocenie-tlo' );
				}
			};
		}() ),

		/**
		 * FILTR DLA DALTONISTOW - rysunki filtrow w dokumencie.
		 *
		 * Filtr CSS z macierza barw istnieje tylko jako element SVG, do
		 * ktorego arkusz odwoluje sie przez url(#...). Skladamy go raz, przy
		 * starcie, niezaleznie od wyboru - kosztuje trzy niewidoczne elementy,
		 * a przelaczenie stopnia nie musi potem niczego budowac.
		 *
		 * LEZY W KONTENERZE PANELU, a nie wprost w body. Kontener jest
		 * wylaczony z lancucha filtrow, wiec rysunek filtra nie filtruje sam
		 * siebie - a element dolozony do body jako kolejne dziecko zlapalby
		 * regule, ktora go wlasnie uzywa.
		 *
		 * Zanim ten kod zdazy sie wykonac, arkusz odwoluje sie do elementu,
		 * ktorego jeszcze nie ma. Specyfikacja kaze wtedy pominac lancuch
		 * filtrow - strona przez chwile wyglada zwyczajnie, nie znika.
		 */
		daltonizm: {
			przygotuj: function ( ktos ) {
				var NS = 'http://www.w3.org/2000/svg';
				var macierze = ktos.dane.macierze || {};
				var rysunek = document.createElementNS( NS, 'svg' );
				var definicje = document.createElementNS( NS, 'defs' );
				var numer;
				var filtr;
				var macierz;

				rysunek.setAttribute( 'aria-hidden', 'true' );
				rysunek.setAttribute( 'focusable', 'false' );
				rysunek.setAttribute( 'width', '0' );
				rysunek.setAttribute( 'height', '0' );
				rysunek.setAttribute( 'style', 'position:absolute;width:0;height:0;overflow:hidden' );

				for ( numer in macierze ) {
					if ( ! Object.prototype.hasOwnProperty.call( macierze, numer ) || ! /^\d+$/.test( numer ) ) {
						continue;
					}

					filtr = document.createElementNS( NS, 'filter' );
					filtr.setAttribute( 'id', 'alyxa-daltonizm-' + numer );

					/* Macierze Machado sa liczone dla RGB liniowego. */
					filtr.setAttribute( 'color-interpolation-filters', 'linearRGB' );

					macierz = document.createElementNS( NS, 'feColorMatrix' );
					macierz.setAttribute( 'type', 'matrix' );
					macierz.setAttribute( 'values', String( macierze[ numer ] ) );

					filtr.appendChild( macierz );
					definicje.appendChild( filtr );
				}

				rysunek.appendChild( definicje );
				przycisk.parentNode.appendChild( rysunek );
			}
		},

		/**
		 * MASKA CZYTANIA - jasne pasmo, reszta strony przyciemniona.
		 *
		 * Wysokosc pasma i przyciemnienie robi arkusz modulu; tutaj zostaje
		 * jedno zadanie: przesunac pasmo tam, gdzie odwiedzajacy czyta.
		 * Wysokosci pilnuje wspolne sledzenie pionu wyzej.
		 */
		maska: ( function () {
			var pasmo = null;

			/**
			 * Srodkuje pasmo na podanej wysokosci.
			 *
			 * Pasmo nie wychodzi poza okno. Bez tego przy wskazniku przy
			 * gornej krawedzi polowa pasma bylaby poza ekranem, a widoczna
			 * czesc dwa razy wezsza, niz uzytkownik ustawil.
			 *
			 * @param {number} srodek Wysokosc w oknie.
			 * @return {void}
			 */
			function ustaw( srodek ) {
				var wysokosc;
				var gora;
				var najnizej;

				if ( ! pasmo ) {
					return;
				}

				wysokosc = pasmo.offsetHeight;
				gora = Math.round( srodek - wysokosc / 2 );
				najnizej = window.innerHeight - wysokosc;

				if ( gora > najnizej ) {
					gora = najnizej;
				}

				if ( gora < 0 ) {
					gora = 0;
				}

				pasmo.style.setProperty( '--alyxa-maska-gora', gora + 'px' );
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

					sledzeniePionu.dolacz( ustaw );
				},

				wylacz: function () {
					if ( ! pasmo ) {
						return;
					}

					sledzeniePionu.odlacz( ustaw );

					pasmo.parentNode.removeChild( pasmo );
					pasmo = null;
				}
			};
		}() ),

		/**
		 * LINIA CZYTANIA - kreska idaca za wskaznikiem i za fokusem.
		 *
		 * Robi to samo co maska, tylko mniejszym kosztem: nie przyciemnia
		 * strony, wiec nie zmienia ani jednego koloru. Oba moduly moga byc
		 * wlaczone naraz i wtedy kreska lezy w jasnym pasmie - warstwy
		 * ustawione sa w arkuszach tak, zeby wyszlo to samo z siebie.
		 */
		linia: ( function () {
			var kreska = null;

			/**
			 * Stawia kreske na podanej wysokosci.
			 *
			 * PRZY WSKAZNIKU KRESKA STOI POD JEGO GROTEM, a przy fokusie -
			 * pod dolna krawedzia elementu, na ktorym stanal tabulator.
			 * Wspolne sledzenie podaje srodek elementu, bo tego potrzebuje
			 * maska; kreska postawiona w srodku odnosnika przekreslalaby go
			 * i wygladala jak tekst usuniety, a nie jak prowadnica.
			 *
			 * @param {number}           gora Wysokosc w oknie.
			 * @param {HTMLElement|null} cel  Element, ktory ja wyznaczyl.
			 * @return {void}
			 */
			function ustaw( gora, cel ) {
				var y;
				var najnizej;

				if ( ! kreska ) {
					return;
				}

				y = cel ? cel.getBoundingClientRect().bottom + 2 : gora;
				najnizej = window.innerHeight - kreska.offsetHeight;

				if ( y > najnizej ) {
					y = najnizej;
				}

				if ( y < 0 ) {
					y = 0;
				}

				kreska.style.setProperty( '--alyxa-linia-gora', Math.round( y ) + 'px' );
			}

			return {
				wlacz: function () {
					if ( kreska ) {
						return;
					}

					kreska = document.createElement( 'div' );
					kreska.className = 'alyxa-linia__kreska';
					kreska.setAttribute( 'aria-hidden', 'true' );

					document.body.appendChild( kreska );

					sledzeniePionu.dolacz( ustaw );
				},

				wylacz: function () {
					if ( ! kreska ) {
						return;
					}

					sledzeniePionu.odlacz( ustaw );

					kreska.parentNode.removeChild( kreska );
					kreska = null;
				}
			};
		}() ),
		/**
		 * UKRYJ OBRAZY - cala robota robi arkusz, tutaj zostaje jedno pytanie.
		 *
		 * Czy na tej podstronie jest w ogole co chowac. Przelacznik, ktory na
		 * stronie bez ani jednego zdjecia niczego nie zmienia, jest gorszy niz
		 * jego brak: kto go nacisnie, uzna, ze wtyczka nie dziala. Stad kafelek
		 * warunkowy i jedna metoda zachowania.
		 */
		obrazy: ( function () {
			var WYBOR = 'img, picture, svg, canvas';

			/**
			 * Czy poza panelem jest jakikolwiek obraz.
			 *
			 * Wlasne rysunki pomijamy - panel ma ikony w svg, wiec bez tego
			 * warunku odpowiedz brzmialaby "tak" na kazdej stronie swiata.
			 *
			 * @return {boolean}
			 */
			function jestCoChowac() {
				var znalezione = document.querySelectorAll( WYBOR );
				var i;

				for ( i = 0; i < znalezione.length; i++ ) {
					if ( ! znalezione[ i ].closest( '.alyxa' ) ) {
						return true;
					}
				}

				return false;
			}

			return {
				przygotuj: function ( ktos ) {
					if ( jestCoChowac() ) {
						ktos.pokaz();
					}
				}
			};
		}() ),

		/**
		 * WYCISZ DZWIEKI.
		 *
		 * WYCISZAMY, A NIE ZATRZYMUJEMY. Zatrzymanie nagrania odbiera decyzje
		 * temu, kto je wlaczyl - a moze ogladac film z napisami. Wyciszenie
		 * zdejmuje to, co przeszkadza, i zostawia obraz.
		 *
		 * ZAPAMIETUJEMY POPRZEDNI STAN kazdego odtwarzacza i przywracamy go
		 * przy wylaczeniu modulu. Bez tego wylaczenie przelacznika WLACZALOBY
		 * dzwiek w nagraniu, ktore strona z zalozenia ma wyciszone - a takie
		 * sa wszystkie nagrania startujace same.
		 *
		 * CZEGO NIE POTRAFIMY, I MOWI TO OPIS MODULU: odtwarzacza osadzonego
		 * z innej strony - YouTube, Vimeo - nie da sie wyciszyc z zewnatrz.
		 * Ramka nalezy do tamtej strony i przegladarka nie pozwala jej dotknac.
		 */
		dzwieki: ( function () {
			var NAGRANIA = 'audio, video';

			var wyciszone = [];
			var sluchamy = false;
			var kontekst = null;

			/**
			 * Wycisza jeden odtwarzacz, zapamietujac jego poprzedni stan.
			 *
			 * @param {HTMLMediaElement} odtwarzacz Element audio albo video.
			 * @return {void}
			 */
			function wycisz( odtwarzacz ) {
				var i;

				if ( ! odtwarzacz || 'boolean' !== typeof odtwarzacz.muted ) {
					return;
				}

				for ( i = 0; i < wyciszone.length; i++ ) {
					if ( wyciszone[ i ].odtwarzacz === odtwarzacz ) {
						return;
					}
				}

				wyciszone.push( { odtwarzacz: odtwarzacz, bylo: odtwarzacz.muted } );

				odtwarzacz.muted = true;
			}

			/**
			 * Wycisza wszystko, co jest w dokumencie teraz.
			 *
			 * @return {void}
			 */
			function wyciszWszystko() {
				var znalezione = document.querySelectorAll( NAGRANIA );
				var i;

				for ( i = 0; i < znalezione.length; i++ ) {
					wycisz( znalezione[ i ] );
				}
			}

			/**
			 * Nagranie, ktore wlasnie ruszylo.
			 *
			 * Zdarzenie play nie propaguje sie w gore, wiec lapiemy je w fazie
			 * przechwytywania - inaczej odtwarzacz dolozony do strony po
			 * wlaczeniu modulu zagralby glosno mimo wlaczonego wyciszenia.
			 *
			 * @param {Event} zdarzenie Zdarzenie odtwarzania.
			 * @return {void}
			 */
			function zOdtwarzania( zdarzenie ) {
				wycisz( zdarzenie.target );
			}

			/**
			 * Pierwsze nagranie na stronie, ktora przy starcie zadnego nie miala.
			 *
			 * @return {void}
			 */
			function odkryjPoStarcie() {
				if ( kontekst ) {
					kontekst.pokaz();
				}
			}

			return {
				przygotuj: function ( ktos ) {
					kontekst = ktos;

					if ( document.querySelector( NAGRANIA ) ) {
						ktos.pokaz();

						return;
					}

					/*
					 * Strona moze dolozyc odtwarzacz pozniej - z galerii,
					 * z wtyczki, z kliknietego przycisku. Czekamy wtedy na
					 * pierwsze odtworzenie: jest to chwile za pozno, bo cos
					 * juz gra, ale kafelek pojawia sie dokladnie w chwili,
					 * w ktorej zaczyna byc potrzebny.
					 */
					document.addEventListener( 'play', odkryjPoStarcie, { capture: true, once: true } );
				},

				wlacz: function () {
					if ( sluchamy ) {
						return;
					}

					sluchamy = true;

					wyciszWszystko();

					document.addEventListener( 'play', zOdtwarzania, true );
				},

				wylacz: function () {
					var i;

					if ( ! sluchamy ) {
						return;
					}

					sluchamy = false;

					document.removeEventListener( 'play', zOdtwarzania, true );

					for ( i = 0; i < wyciszone.length; i++ ) {
						wyciszone[ i ].odtwarzacz.muted = wyciszone[ i ].bylo;
					}

					wyciszone = [];
				}
			};
		}() ),

		/**
		 * ODCZYT CALEJ STRONY NA GLOS.
		 *
		 * Cala mowa idzie przez wspolny silnik wyzej; tutaj zostaje to, czym
		 * ten modul rozni sie od czytania wskazanego elementu: obszar tresci,
		 * z ktorego bierzemy tekst, i trzystanowy przycisk.
		 *
		 * BEZ GLOSU W JEZYKU STRONY POZYCJI NIE MA WCALE. Polski tekst
		 * przeczytany glosem angielskim to belkot, ktory brzmi jak awaria
		 * strony, a nie jak brak glosu w systemie. Pozycja wychodzi wiec
		 * z serwera ukryta i pokazujemy ja dopiero, gdy jest czym czytac.
		 *
		 * CZYTAMY Z OBSZARU TRESCI, nie z menu i stopki: dla oka sa one
		 * nawigacja, a dla ucha kilkudziesiecioma sekundami, po ktorych nie
		 * wiadomo, o czym jest artykul.
		 */
		odczyt: ( function () {
			var kontekst = null;
			var pozycja = null;
			var przyciskCzytaj = null;
			var komunikat = null;
			var etykietaCzytaj = '';

			/* 'bezczynny', 'czyta' albo 'wstrzymany'. */
			var stanOdczytu = 'bezczynny';

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
			 * Wraca do stanu wyjsciowego.
			 *
			 * Silnik wola to i wtedy, gdy sam skonczyl czytac, i wtedy, gdy
			 * przerwal go ktos inny - dla przycisku to ta sama wiadomosc.
			 *
			 * @return {void}
			 */
			function naKoniec() {
				stanOdczytu = 'bezczynny';

				odswiez();
			}

			/**
			 * Zaczyna czytanie od poczatku.
			 *
			 * @return {void}
			 */
			function zacznij() {
				var wypowiedzi = silnikMowy.naWypowiedzi( silnikMowy.zbierz( obszarTresci() ) );

				if ( ! wypowiedzi.length ) {
					komunikat.textContent = dane( 'pusto' );

					return;
				}

				/*
				 * Najpierw silnik, dopiero potem stan. mow() konczy prace
				 * poprzedniego wlasciciela - a gdy poprzednim jestesmy my
				 * sami, jego naKoniec zdazylby przestawic przycisk z powrotem
				 * na "bezczynny" juz po tym, jak ustawilibysmy "czyta".
				 */
				silnikMowy.mow( wypowiedzi, {
					jezyk: dane( 'jezyk' ),
					koniec: naKoniec,
					przerwane: naKoniec
				} );

				stanOdczytu = 'czyta';

				odswiez();
			}

			/**
			 * Wstrzymuje czytanie.
			 *
			 * @return {void}
			 */
			function wstrzymaj() {
				silnikMowy.wstrzymaj( function ( mowiDalej ) {
					if ( 'wstrzymany' !== stanOdczytu ) {
						return;
					}

					stanOdczytu = mowiDalej ? 'czyta' : 'bezczynny';

					odswiez();
				} );

				stanOdczytu = 'wstrzymany';

				odswiez();
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

				if ( silnikMowy.jestGlos( dane( 'jezyk' ) ) ) {
					kontekst.pokaz();
				}
			}

			return {
				przygotuj: function ( ktos ) {
					kontekst = ktos;
					pozycja = kontekst.pozycja;

					if ( ! pozycja || ! silnikMowy.dostepny() ) {
						return;
					}

					/*
					 * Dwa pytania o glosy: teraz i wtedy, gdy przegladarka
					 * da znac, ze juz wie. Przy pierwszym spis bywa pusty.
					 */
					sprawdzGlosy();

					silnikMowy.przySpisieGlosow( sprawdzGlosy );
				},

				wlacz: function ( ktos ) {
					kontekst = ktos;
					pozycja = kontekst.pozycja;

					if ( ! pozycja || ! silnikMowy.dostepny() ) {
						return;
					}

					przyciskCzytaj = pozycja.querySelector( '[data-alyxa-akcja="czytaj"]' );
					komunikat = pozycja.querySelector( '[data-alyxa-komunikat]' );

					if ( ! przyciskCzytaj ) {
						return;
					}

					etykietaCzytaj = przyciskCzytaj.textContent.trim();
				},

				wylacz: function () {
					/*
					 * Pytamy o przycisk, a nie o pozycje: gdy urzadzenie nie
					 * ma silnika mowy, wlacz konczy sie przed dojsciem do
					 * czegokolwiek, co trzeba by teraz sprzatnac.
					 */
					if ( ! przyciskCzytaj ) {
						return;
					}

					silnikMowy.zatrzymaj();

					przyciskCzytaj = null;
					komunikat = null;
				},

				akcja: function ( nazwa ) {
					if ( ! przyciskCzytaj ) {
						return;
					}

					if ( 'stop' === nazwa ) {
						silnikMowy.zatrzymaj();

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
						silnikMowy.wznow();

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
						silnikMowy.zatrzymaj();
					}
				}
			};
		}() ),

		/**
		 * CZYTANIE WSKAZANEGO ELEMENTU.
		 *
		 * Ten sam glos, co przy odczycie calej strony, tylko krotszy zasieg:
		 * czytamy jeden akapit, jeden naglowek, jedna komorke tabeli - ten,
		 * ktory odwiedzajacy kliknal, albo ten, na ktorym stanal tabulatorem.
		 *
		 * DLACZEGO PRZELACZNIK, A NIE TRZECI PRZYCISK PRZY ODCZYCIE STRONY.
		 * Bo to jest ustawienie, ktore ma przetrwac przejscie na nastepna
		 * podstrone, a modul typu 'akcje' z zalozenia niczego nie zapisuje.
		 * Kto tego potrzebuje, potrzebuje na calej stronie, a nie na jednej.
		 *
		 * ODNOSNIK KLIKNIETY UCINA SIE SAM I TO NIE JEST USTERKA. Klikniecie
		 * przenosi na inna podstrone, a wyjscie ze strony ucisza glos -
		 * slychac wiec ulamek slowa. Naprawianie tego znaczyloby wstrzymywanie
		 * nawigacji, czyli psucie strony po to, zeby dzialalo udogodnienie.
		 * Odnosnik czyta sie w calosci tabulatorem i to jest dla niego
		 * wlasciwa droga.
		 *
		 * KURSORA NIE RUSZAMY. Modul duzego kursora ustawia cursor z flaga
		 * !important na wszystkim, wiec dwa moduly bilyby sie o te sama
		 * wlasciwosc, a wygrywalby ten pozniejszy w sklejonym arkuszu.
		 * Podpowiedzia dla oka jest obwodka na czytanym elemencie.
		 */
		wskazywanie: ( function () {
			var KLASA = 'alyxa-wskazywanie__czytany';

			/*
			 * Najmniejszy kawalek tresci, jaki ma sens przeczytac w calosci.
			 * Odnosnik i przycisk sa na tej liscie, bo tabulator zatrzymuje
			 * sie wlasnie na nich, a nie na akapicie, w ktorym leza.
			 */
			var BLOKI = 'p, li, h1, h2, h3, h4, h5, h6, td, th, dd, dt, figcaption, caption, blockquote, summary, label, a, button';

			/* Pola formularza omijamy: klikniecie w nie ma pisac, nie czytac. */
			var POLA = 'input, textarea, select, [contenteditable="true"]';

			var kontekst = null;
			var pozycja = null;
			var czytany = null;
			var sluchamy = false;

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
			 * Zdejmuje obwodke z ostatnio czytanego elementu.
			 *
			 * @return {void}
			 */
			function posprzataj() {
				if ( ! czytany ) {
					return;
				}

				czytany.classList.remove( KLASA );
				czytany = null;
			}

			/**
			 * Czyta podany element.
			 *
			 * @param {HTMLElement} element Element do przeczytania.
			 * @return {void}
			 */
			function czytaj( element ) {
				var wypowiedzi;

				/*
				 * Ten sam element drugi raz z rzedu to nie druga prosba, tylko
				 * to samo zdarzenie widziane dwa razy: klikniecie w odnosnik
				 * ustawia na nim takze fokus, wiec przychodzi i click,
				 * i focusin.
				 */
				if ( ! element || element === czytany ) {
					return;
				}

				/*
				 * O GLOS PYTAMY TUTAJ, A NIE PRZY ZAKLADANIU NASLUCHU.
				 * Spis glosow bywa pusty jeszcze przez chwile po wczytaniu
				 * strony i dojezdza wlasnym zdarzeniem - a wlacz() wypada
				 * wlasnie w tej chwili, gdy wybor jest odczytany z pamieci
				 * przegladarki. Pytanie zadane wtedy wypadaloby przeczaco
				 * na wlasnej stronie z zainstalowanym polskim glosem, i tak
				 * bylo, zanim to zmierzylismy. Do pierwszego klikniecia
				 * przegladarka spis juz zna.
				 *
				 * Bez glosu w jezyku strony nie czytamy nic: polski tekst
				 * przeczytany glosem angielskim jest gorszy niz cisza.
				 * Kafelka wtedy zreszta nie widac - ale wpis w pamieci moze
				 * byc sprzed odinstalowania glosu.
				 */
				if ( ! silnikMowy.jestGlos( dane( 'jezyk' ) ) ) {
					return;
				}

				wypowiedzi = silnikMowy.naWypowiedzi( silnikMowy.zbierz( element ) );

				if ( ! wypowiedzi.length ) {
					return;
				}

				/* Najpierw silnik: jego przerwane() zdejmuje poprzednia obwodke. */
				silnikMowy.mow( wypowiedzi, {
					jezyk: dane( 'jezyk' ),
					koniec: posprzataj,
					przerwane: posprzataj
				} );

				czytany = element;

				element.classList.add( KLASA );
			}

			/**
			 * Czy w ten element wolno nam w ogole zajrzec.
			 *
			 * @param {HTMLElement} cel Element ze zdarzenia.
			 * @return {boolean}
			 */
			function nasz( cel ) {
				return !! ( cel && cel.closest && ! cel.closest( '.alyxa' ) && ! cel.closest( POLA ) );
			}

			/**
			 * Klikniecie w strone.
			 *
			 * Zdarzenia nie zatrzymujemy i niczego nie odwolujemy: odnosnik ma
			 * dalej prowadzic tam, gdzie prowadzil, a przycisk robic swoje.
			 *
			 * @param {MouseEvent} zdarzenie Zdarzenie mysza albo palcem.
			 * @return {void}
			 */
			function zKlikniecia( zdarzenie ) {
				var cel = zdarzenie.target;
				var blok;

				if ( ! nasz( cel ) ) {
					return;
				}

				blok = cel.closest( BLOKI );

				/*
				 * Poza blokiem tresci czytamy sam klikniety element, ale tylko
				 * wtedy, gdy nie ma w sobie innych elementow. Bez tego warunku
				 * klikniecie w puste tlo strony trafialoby w sekcje albo w body
				 * i czytalo cala strone - czyli dokladnie to, czego ten modul
				 * mial nie robic.
				 */
				if ( ! blok && ! cel.firstElementChild ) {
					blok = cel;
				}

				czytaj( blok );
			}

			/**
			 * Wejscie fokusu na element.
			 *
			 * Czytamy dokladnie to, na czym stanal tabulator - bez szukania
			 * bloku wyzej. Fokus zatrzymuje sie na rzeczach, ktore same w sobie
			 * sa caloscia: na odnosniku, na przycisku, na naglowku z tabindex.
			 *
			 * @param {FocusEvent} zdarzenie Zdarzenie fokusu.
			 * @return {void}
			 */
			function zFokusu( zdarzenie ) {
				if ( ! nasz( zdarzenie.target ) ) {
					return;
				}

				czytaj( zdarzenie.target );
			}

			/**
			 * Pokazuje kafelek, gdy urzadzenie ma glos w jezyku strony.
			 *
			 * @return {void}
			 */
			function sprawdzGlosy() {
				if ( ! pozycja || ! pozycja.hidden ) {
					return;
				}

				if ( silnikMowy.jestGlos( dane( 'jezyk' ) ) ) {
					kontekst.pokaz();
				}
			}

			return {
				przygotuj: function ( ktos ) {
					kontekst = ktos;
					pozycja = kontekst.pozycja;

					if ( ! pozycja || ! silnikMowy.dostepny() ) {
						return;
					}

					sprawdzGlosy();

					silnikMowy.przySpisieGlosow( sprawdzGlosy );
				},

				wlacz: function ( ktos ) {
					kontekst = ktos;

					if ( sluchamy || ! silnikMowy.dostepny() ) {
						return;
					}

					sluchamy = true;

					document.addEventListener( 'click', zKlikniecia );
					document.addEventListener( 'focusin', zFokusu );
				},

				wylacz: function () {
					if ( ! sluchamy ) {
						return;
					}

					sluchamy = false;

					document.removeEventListener( 'click', zKlikniecia );
					document.removeEventListener( 'focusin', zFokusu );

					silnikMowy.zatrzymaj();

					posprzataj();
				},

				zmiana: function () {
					if ( czytany ) {
						silnikMowy.zatrzymaj();
					}
				}
			};
		}() ),

		/**
		 * SKROTY KLAWISZOWE - jedyny modul, ktory obsluguje inne moduly.
		 *
		 * DLATEGO WOLNO MU SIEGAC PO FUNKCJE RDZENIA. Kazde inne zachowanie
		 * zajmuje sie wylacznie soba i dostaje z rdzenia tylko swoj kontekst;
		 * ten wola przelacz() i zmienStopien() wprost, bo na tym polega jego
		 * zadanie. Nie zna przy tym ani jednego sluga: litery bierze z klucza
		 * 'klawisz' w tablicy 'dane' kazdego modulu, wiec modul dolozony
		 * filtrem dostaje skrot tak samo jak nasze.
		 *
		 * ALT+SHIFT, A NIE SAMA LITERA. Sama litera odbieralaby strone
		 * czytnikom ekranu, ktore uzywaja pojedynczych klawiszy do nawigacji -
		 * H skacze po naglowkach, K po odnosnikach. Alt+Shift to ta sama
		 * kombinacja, ktorej WordPress uzywa dla wlasnych klawiszy dostepu,
		 * wiec nie jest niczym nowym ani dla przegladarki, ani dla systemu.
		 *
		 * W POLU TEKSTOWYM SKROTY MILCZA. Na klawiaturze polskiej prawy Alt
		 * sluzy do pisania ogonkow, a ktos wypelniajacy formularz kontaktowy
		 * ma pisac, a nie przelaczac kontrast.
		 */
		skroty: ( function () {
			var POLA = 'input, textarea, select, [contenteditable="true"]';

			var kontekst = null;
			var opakowanie = null;
			var zapowiedz = null;
			var plakietki = [];
			var licznik = 0;
			var sluchamy = false;

			/**
			 * Wartosc z tablicy 'dane' tego modulu.
			 *
			 * @param {string} klucz Nazwa wartosci.
			 * @return {string}
			 */
			function dane( klucz ) {
				return ( kontekst && kontekst.dane && kontekst.dane[ klucz ] ) || '';
			}

			/**
			 * Litera skrotu modulu albo pusty ciag.
			 *
			 * @param {string} slug Slug modulu.
			 * @return {string}
			 */
			function litera( slug ) {
				var wartosc = ( moduly[ slug ].dane && moduly[ slug ].dane.klawisz ) || '';

				return String( wartosc ).toLowerCase();
			}

			/**
			 * Modul, ktory zglosil podana litere.
			 *
			 * @param {string} znak Mala litera.
			 * @return {string} Slug albo pusty ciag.
			 */
			function poLiterze( znak ) {
				var slug;

				for ( slug in moduly ) {
					if ( ! Object.prototype.hasOwnProperty.call( moduly, slug ) ) {
						continue;
					}

					if ( znak && litera( slug ) === znak ) {
						return slug;
					}
				}

				return '';
			}

			/**
			 * Kontrolka modulu w panelu.
			 *
			 * Kafelek stopniowany pytamy pierwszy, bo modul stopniowany ma
			 * data-alyxa-modul na obu przyciskach kroku - a plakietka ma stanac
			 * raz, na calej grupie.
			 *
			 * @param {string} slug Slug modulu.
			 * @return {HTMLElement|null}
			 */
			function kontrolka( slug ) {
				return panel.querySelector( '[data-alyxa-kafelek="' + slug + '"]' )
					|| panel.querySelector( '[data-alyxa-modul="' + slug + '"]' );
			}

			/**
			 * Nazwa modulu, wzieta z jego kafelka.
			 *
			 * Z dokumentu, a nie z serwera: napis juz tam jest, przetlumaczony
			 * i w tej samej postaci, ktora odwiedzajacy widzi na kafelku.
			 *
			 * @param {string} slug Slug modulu.
			 * @return {string}
			 */
			function nazwa( slug ) {
				var element = kontrolka( slug );
				var napis = element ? element.querySelector( '.alyxa__napis' ) : null;

				/* Kafelek cykliczny lamie napis na kilka wierszy zrodla. */
				return napis ? napis.textContent.replace( /\s+/g, ' ' ).trim() : slug;
			}

			/**
			 * Wstawia wartosci do wzoru napisu.
			 *
			 * @param {string} wzor    Wzor z %s albo %1$s i %2$s.
			 * @param {string} pierwsza Pierwsza wartosc.
			 * @param {string} druga    Druga wartosc.
			 * @return {string}
			 */
			function podstaw( wzor, pierwsza, druga ) {
				return String( wzor )
					.replace( '%1$s', pierwsza )
					.replace( '%2$s', druga )
					.replace( '%s', pierwsza );
			}

			/**
			 * Mowi, co sie wlasnie stalo.
			 *
			 * Jeden wiersz dla oka i dla czytnika ekranu naraz - to jest
			 * obszar role="status". Napis znika po chwili, bo zapowiedz jest
			 * potwierdzeniem czynnosci, a nie trescia strony.
			 *
			 * @param {string} tekst Napis do pokazania.
			 * @return {void}
			 */
			function powiedz( tekst ) {
				if ( ! zapowiedz ) {
					return;
				}

				zapowiedz.textContent = tekst;

				window.clearTimeout( licznik );

				licznik = window.setTimeout( function () {
					if ( zapowiedz ) {
						zapowiedz.textContent = '';
					}
				}, 4000 );
			}

			/**
			 * Przelacza modul i zapowiada wynik.
			 *
			 * @param {string} slug Slug modulu.
			 * @return {void}
			 */
			function uzyj( slug ) {
				var teraz;
				var etykiety;
				var stopien;

				if ( 'stopnie' === moduly[ slug ].typ ) {
					teraz = 'number' === typeof stan[ slug ] ? stan[ slug ] : 0;

					/*
					 * Z ostatniego stopnia wracamy na zero. Przy przycisku
					 * w panelu odrzucilismy takie chodzenie w kolko, bo droga
					 * powrotna wiodla przez powiekszenie jeszcze wieksze niz
					 * to, ktore komus przeszkodzilo - ale tam przycisk byl
					 * jedyna droga. Tu para minus-plus dalej stoi w panelu,
					 * a skrot ma miec jedna litere, nie dwie.
					 */
					zmienStopien( slug, teraz >= moduly[ slug ].stopnie ? -teraz : 1 );

					stopien = 'number' === typeof stan[ slug ] ? stan[ slug ] : 0;
					etykiety = moduly[ slug ].etykiety || [];

					if ( ! stopien ) {
						powiedz( podstaw( dane( 'wylaczono' ), nazwa( slug ), '' ) );

						return;
					}

					powiedz(
						podstaw(
							dane( 'stan' ),
							nazwa( slug ),
							etykiety[ stopien ] || String( stopien )
						)
					);

					return;
				}

				przelacz( slug );

				powiedz( podstaw( true === stan[ slug ] ? dane( 'wlaczono' ) : dane( 'wylaczono' ), nazwa( slug ), '' ) );
			}

			/**
			 * Litera z nacisnietego klawisza.
			 *
			 * NAJPIERW key, POTEM code. key niesie litere taka, jaka daje
			 * uklad klawiatury - czyli te, ktora odwiedzajacy widzi na kafelku.
			 * Bywa jednak, ze przy wcisnietym Alt uklad zwraca zamiast niej
			 * znak specjalny; wtedy zostaje code, czyli fizyczne polozenie
			 * klawisza na klawiaturze amerykanskiej.
			 *
			 * @param {KeyboardEvent} zdarzenie Zdarzenie klawiatury.
			 * @return {string} Mala litera albo pusty ciag.
			 */
			function znak( zdarzenie ) {
				var klucz = String( zdarzenie.key || '' ).toLowerCase();
				var kod = String( zdarzenie.code || '' );

				if ( 1 === klucz.length && klucz >= 'a' && klucz <= 'z' ) {
					return klucz;
				}

				if ( 0 === kod.indexOf( 'Key' ) && 4 === kod.length ) {
					return kod.charAt( 3 ).toLowerCase();
				}

				return '';
			}

			/**
			 * Nacisniecie klawisza.
			 *
			 * @param {KeyboardEvent} zdarzenie Zdarzenie klawiatury.
			 * @return {void}
			 */
			function zKlawiatury( zdarzenie ) {
				var wybrany;
				var slug;

				if ( ! zdarzenie.altKey || ! zdarzenie.shiftKey || zdarzenie.ctrlKey || zdarzenie.metaKey ) {
					return;
				}

				if ( zdarzenie.target && zdarzenie.target.closest && zdarzenie.target.closest( POLA ) ) {
					return;
				}

				wybrany = znak( zdarzenie );

				if ( ! wybrany ) {
					return;
				}

				if ( wybrany === String( dane( 'panel' ) ).toLowerCase() ) {
					zdarzenie.preventDefault();

					/*
					 * Otwarty panel dostaje fokus na przycisku, zeby dalo sie
					 * w niego wejsc tabulatorem od razu - inaczej skrot
					 * otwieralby cos, do czego trzeba dopiero dojechac
					 * z miejsca, w ktorym akurat stoi fokus.
					 */
					if ( otwarty() ) {
						zamknij( true );
					} else {
						otworz();
						przycisk.focus();
					}

					return;
				}

				slug = poLiterze( wybrany );

				if ( ! slug ) {
					return;
				}

				zdarzenie.preventDefault();

				uzyj( slug );
			}

			/**
			 * Doklada plakietki z literami i atrybut aria-keyshortcuts.
			 *
			 * @return {void}
			 */
			function opiszKafelki() {
				var slug;
				var element;
				var znaczek;

				for ( slug in moduly ) {
					if ( ! Object.prototype.hasOwnProperty.call( moduly, slug ) ) {
						continue;
					}

					if ( ! litera( slug ) ) {
						continue;
					}

					element = kontrolka( slug );

					if ( ! element ) {
						continue;
					}

					/*
					 * aria-keyshortcuts jest atrybutem stworzonym dokladnie
					 * do tego. Czytnik ekranu oglasza skrot razem z nazwa
					 * kontrolki, wiec sama plakietka moze zostac ozdoba dla
					 * oka - stad aria-hidden na niej.
					 */
					element.setAttribute( 'aria-keyshortcuts', 'Alt+Shift+' + litera( slug ).toUpperCase() );

					znaczek = document.createElement( 'span' );
					znaczek.className = 'alyxa__klawisz';
					znaczek.setAttribute( 'aria-hidden', 'true' );
					znaczek.textContent = litera( slug );

					element.appendChild( znaczek );

					plakietki.push( { element: element, znaczek: znaczek } );
				}
			}

			/**
			 * Zdejmuje plakietki i atrybuty.
			 *
			 * @return {void}
			 */
			function sprzatnijKafelki() {
				var i;

				for ( i = 0; i < plakietki.length; i++ ) {
					plakietki[ i ].element.removeAttribute( 'aria-keyshortcuts' );

					if ( plakietki[ i ].znaczek.parentNode ) {
						plakietki[ i ].znaczek.parentNode.removeChild( plakietki[ i ].znaczek );
					}
				}

				plakietki = [];
			}

			return {
				wlacz: function ( ktos ) {
					kontekst = ktos;

					if ( sluchamy ) {
						return;
					}

					sluchamy = true;

					opakowanie = przycisk.parentNode;

					zapowiedz = document.createElement( 'p' );
					zapowiedz.className = 'alyxa__zapowiedz';
					zapowiedz.setAttribute( 'role', 'status' );

					opakowanie.appendChild( zapowiedz );

					opiszKafelki();

					document.addEventListener( 'keydown', zKlawiatury );
				},

				wylacz: function () {
					if ( ! sluchamy ) {
						return;
					}

					sluchamy = false;

					document.removeEventListener( 'keydown', zKlawiatury );

					window.clearTimeout( licznik );

					sprzatnijKafelki();

					if ( zapowiedz && zapowiedz.parentNode ) {
						zapowiedz.parentNode.removeChild( zapowiedz );
					}

					zapowiedz = null;
				},

				/*
				 * Kafelek warunkowy - odczyt strony, czytanie wskazanego -
				 * potrafi pojawic sie po tym, jak plakietki juz stanely.
				 * Zmiana dowolnego ustawienia jest najblizsza chwila, w ktorej
				 * mozna je przeliczyc od nowa bez wlasnego nasluchiwania.
				 */
				zmiana: function () {
					if ( ! sluchamy ) {
						return;
					}

					sprzatnijKafelki();
					opiszKafelki();
				}
			};
		}() ),

		/**
		 * STRUKTURA STRONY - spis naglowkow i obszarow.
		 *
		 * To samo, co czytnik ekranu podaje pod jednym klawiszem, pokazane
		 * oku. Spis powstaje przy kazdym nacisnieciu od nowa, bo strona
		 * potrafi dolozyc tresc po wczytaniu - a spis z wczoraj wskazywalby
		 * elementy, ktorych juz nie ma.
		 *
		 * OBSZARY WEDLUG TYCH SAMYCH REGUL CO W CZYTNIKU. header i footer sa
		 * naglowkiem i stopka strony tylko wtedy, gdy nie leza w artykule,
		 * sekcji albo innym obszarze; section i form licza sie jako obszar
		 * dopiero z wlasna nazwa. Inaczej spis mialby tuzin "naglowkow strony"
		 * - po jednym na kazdy wpis na liscie aktualnosci.
		 *
		 * NACISNIECIE PRZENOSI FOKUS, NIE TYLKO PRZEWIJA. Po samym przewinieciu
		 * nastepny Tab zaczynalby od miejsca, w ktorym ktos byl wczesniej,
		 * czyli od panelu. Naglowek i obszar zwykle nie przyjmuja fokusu, wiec
		 * dostaja tabindex="-1" na czas, gdy go maja - i traca go razem
		 * z fokusem, zeby modul nie zostawial sladu w dokumencie.
		 */
		struktura: ( function () {
			var NAGLOWKI = 'h1, h2, h3, h4, h5, h6, [role="heading"]';
			var OBSZARY = 'header, footer, nav, main, aside, search, section, form, [role]';

			/* Obszary, w ktorych header i footer przestaja dotyczyc calej strony. */
			var ZAWEZAJACE = 'article, aside, main, nav, section, [role="article"], [role="complementary"], [role="main"], [role="navigation"], [role="region"]';

			var OZNACZENIE = 'alyxa-struktura__cel';

			var kontekst = null;
			var przyciskPokaz = null;
			var komunikat = null;
			var spis = null;
			var cele = [];
			var oznaczony = null;
			var dodanyTabindex = false;

			/**
			 * Wartosc z tablicy 'dane' rejestru.
			 *
			 * @param {string} klucz Nazwa wartosci.
			 * @return {*}
			 */
			function dane( klucz ) {
				return ( kontekst && kontekst.dane && kontekst.dane[ klucz ] ) || '';
			}

			/**
			 * Czy element jest na stronie dla oka i dla czytnika.
			 *
			 * Naglowek schowany klasa "tylko dla czytnika" przechodzi - ma
			 * wymiary, a czytnik go oglasza, wiec nalezy do struktury. Nie
			 * przechodzi nic z panelu: spis ma pokazywac strone, nie nas.
			 *
			 * @param {Element} element Element.
			 * @return {boolean}
			 */
			function widoczny( element ) {
				return ! element.closest( '.alyxa, [inert], [aria-hidden="true"]' ) && element.getClientRects().length > 0;
			}

			/**
			 * Tekst elementu ze scisnietymi odstepami.
			 *
			 * ANI textContent, ANI innerText. textContent skleja bloki bez
			 * odstepu: naglowek karty z podpisem w <small display: block> dal
			 * w spisie "Szkola podstawowaKlasy 1 do 8". innerText zna uklad,
			 * ale stosuje tez text-transform i ten sam naglowek wyszedl
			 * "SZKOLA PODSTAWOWA" - a czytnik ekranu czyta tekst sprzed
			 * przeksztalcenia i spis ma mowic to samo. Chodzimy wiec po wezlach
			 * sami: odstep na granicy kazdego elementu, ktory nie jest liniowy,
			 * i nic z elementow schowanych przez display: none. Ta sama lekcja
			 * co przy zbieraniu tekstu do odczytu na glos.
			 *
			 * @param {Element} element Element.
			 * @return {string}
			 */
			function tekst( element ) {
				var czesci = [];

				( function idz( wezel ) {
					var dziecko;
					var uklad;
					var blok;

					for ( dziecko = wezel.firstChild; dziecko; dziecko = dziecko.nextSibling ) {
						if ( 3 === dziecko.nodeType ) {
							czesci.push( dziecko.nodeValue );

							continue;
						}

						if ( 1 !== dziecko.nodeType ) {
							continue;
						}

						uklad = window.getComputedStyle( dziecko ).display;

						if ( 'none' === uklad ) {
							continue;
						}

						blok = 'br' === dziecko.localName || ( 'contents' !== uklad && 0 !== uklad.indexOf( 'inline' ) );

						if ( blok ) {
							czesci.push( ' ' );
						}

						idz( dziecko );

						if ( blok ) {
							czesci.push( ' ' );
						}
					}
				}( element ) );

				return czesci.join( '' ).replace( /\s+/g, ' ' ).trim();
			}

			/**
			 * Nazwa obszaru z aria-label albo aria-labelledby.
			 *
			 * @param {Element} element Obszar.
			 * @return {string}
			 */
			function nazwaObszaru( element ) {
				var etykieta = ( element.getAttribute( 'aria-label' ) || '' ).trim();
				var wskazania = ( element.getAttribute( 'aria-labelledby' ) || '' ).split( /\s+/ );
				var czesci = [];
				var wskazany;
				var i;

				if ( etykieta ) {
					return etykieta;
				}

				for ( i = 0; i < wskazania.length; i++ ) {
					wskazany = wskazania[ i ] ? document.getElementById( wskazania[ i ] ) : null;

					if ( wskazany ) {
						czesci.push( tekst( wskazany ) );
					}
				}

				return czesci.join( ' ' ).trim();
			}

			/**
			 * Rola obszaru albo pusty ciag, gdy element obszarem nie jest.
			 *
			 * @param {Element} element Element.
			 * @return {string}
			 */
			function rola( element ) {
				var role = dane( 'role' ) || {};
				var jawna = ( element.getAttribute( 'role' ) || '' ).trim().split( /\s+/ )[ 0 ];
				var rodzic = element.parentElement;

				/* Jawna rola wygrywa z elementem - takze wtedy, gdy obszarem go nie czyni. */
				if ( jawna ) {
					return Object.prototype.hasOwnProperty.call( role, jawna ) ? jawna : '';
				}

				switch ( element.localName ) {
					case 'main':
						return 'main';
					case 'nav':
						return 'navigation';
					case 'aside':
						return 'complementary';
					case 'search':
						return 'search';
					case 'section':
						return 'region';
					case 'form':
						return 'form';
					case 'header':
						return rodzic && rodzic.closest( ZAWEZAJACE ) ? '' : 'banner';
					case 'footer':
						return rodzic && rodzic.closest( ZAWEZAJACE ) ? '' : 'contentinfo';
				}

				return '';
			}

			/**
			 * Czy element lezy w obszarze tej samej roli, ktory juz jest w spisie.
			 *
			 * Motyw blokowy potrafi owinac grupe <header> czescia szablonu,
			 * ktora tez jest <header> - tak jest na stronie, na ktorej ten
			 * modul powstal. Czytnik ekranu oglasza wtedy dwa naglowki strony,
			 * ale na ekranie to jedno miejsce, a spis z dwiema identycznymi
			 * pozycjami prowadzacymi w ten sam punkt tylko myli. Obszary
			 * przychodza w kolejnosci dokumentu, wiec zewnetrzny jest juz
			 * przyjety, gdy dochodzimy do wewnetrznego.
			 *
			 * @param {Array}   przyjete Obszary juz w spisie.
			 * @param {Element} element  Sprawdzany element.
			 * @param {string}  ktora    Jego rola.
			 * @return {boolean}
			 */
			function wObszarze( przyjete, element, ktora ) {
				var i;

				for ( i = 0; i < przyjete.length; i++ ) {
					if ( przyjete[ i ].rola === ktora && przyjete[ i ].element.contains( element ) ) {
						return true;
					}
				}

				return false;
			}

			/**
			 * Poziom naglowka od 1 do 6.
			 *
			 * @param {Element} element Naglowek.
			 * @return {number}
			 */
			function poziom( element ) {
				var znacznik = /^h([1-6])$/.exec( element.localName );
				var zAtrybutu = parseInt( element.getAttribute( 'aria-level' ), 10 );

				if ( zAtrybutu >= 1 && zAtrybutu <= 6 ) {
					return zAtrybutu;
				}

				return znacznik ? parseInt( znacznik[ 1 ], 10 ) : 2;
			}

			/**
			 * Dopisuje jedna pozycje do listy spisu.
			 *
			 * Budujemy z createElement i textContent, nigdy z HTML: napis
			 * pochodzi z tresci strony, a ta potrafi zawierac cokolwiek.
			 *
			 * @param {HTMLElement} lista    Lista.
			 * @param {string}      napis    Napis pozycji.
			 * @param {string}      znacznik Znacznik poziomu albo pusty ciag.
			 * @param {number}      wciecie  Poziom wciecia.
			 * @param {Element}     cel      Element, do ktorego pozycja prowadzi.
			 * @return {void}
			 */
			function dopisz( lista, napis, znacznik, wciecie, cel ) {
				var pozycja = document.createElement( 'li' );
				var przycisk = document.createElement( 'button' );
				var element;

				przycisk.type = 'button';
				przycisk.className = 'alyxa__spis-cel';
				przycisk.setAttribute( 'data-alyxa-cel', String( cele.length ) );

				if ( znacznik ) {
					element = document.createElement( 'span' );
					element.className = 'alyxa__spis-poziom';
					element.textContent = znacznik;
					przycisk.appendChild( element );
				}

				element = document.createElement( 'span' );
				element.className = 'alyxa__spis-napis';
				element.textContent = napis.length > 120 ? napis.slice( 0, 119 ) + '…' : napis;
				przycisk.appendChild( element );

				pozycja.style.setProperty( '--alyxa-poziom', String( wciecie ) );
				pozycja.appendChild( przycisk );
				lista.appendChild( pozycja );

				cele.push( cel );
			}

			/**
			 * Dopisuje do spisu jedna czesc: tytul i liste albo zdanie, ze pusto.
			 *
			 * Tytul jest akapitem, a nie naglowkiem, i to celowo: naglowek
			 * w panelu wszedlby do spisu naglowkow samego czytnika ekranu jako
			 * czesc strony.
			 *
			 * @param {string} tytul Napis tytulu.
			 * @param {string} id    Identyfikator tytulu.
			 * @return {HTMLElement} Lista, do ktorej dopisuje sie pozycje.
			 */
			function czesc( tytul, id ) {
				var naglowek = document.createElement( 'p' );
				var lista = document.createElement( 'ul' );

				naglowek.className = 'alyxa__spis-tytul';
				naglowek.id = id;
				naglowek.textContent = tytul;

				lista.className = 'alyxa__spis-lista';
				lista.setAttribute( 'aria-labelledby', id );

				spis.appendChild( naglowek );
				spis.appendChild( lista );

				return lista;
			}

			/**
			 * Zamienia pusta liste na zdanie, ze nic nie znaleziono.
			 *
			 * @param {HTMLElement} lista Lista.
			 * @param {string}      zdanie Zdanie.
			 * @return {void}
			 */
			function pustaLista( lista, zdanie ) {
				var akapit;

				if ( lista.firstChild ) {
					return;
				}

				akapit = document.createElement( 'p' );
				akapit.className = 'alyxa__spis-pusto';
				akapit.textContent = zdanie;

				lista.parentNode.replaceChild( akapit, lista );
			}

			/**
			 * Buduje spis od nowa.
			 *
			 * @return {void}
			 */
			function zbuduj() {
				var role = dane( 'role' ) || {};
				var naglowki = document.querySelectorAll( NAGLOWKI );
				var obszary = document.querySelectorAll( OBSZARY );
				var listaNaglowkow;
				var listaObszarow;
				var ileNaglowkow = 0;
				var ileObszarow = 0;
				var przyjete = [];
				var jawna;
				var napis;
				var nazwa;
				var ktora;
				var stopien;
				var i;

				cele = [];
				spis.textContent = '';

				listaNaglowkow = czesc( dane( 'naglowki' ), 'alyxa-spis-naglowki' );

				for ( i = 0; i < naglowki.length; i++ ) {
					jawna = ( naglowki[ i ].getAttribute( 'role' ) || '' ).trim();

					/* <h2 role="presentation"> naglowkiem juz nie jest. */
					if ( ( jawna && 'heading' !== jawna ) || ! widoczny( naglowki[ i ] ) ) {
						continue;
					}

					stopien = poziom( naglowki[ i ] );
					napis = tekst( naglowki[ i ] ) || dane( 'bezTekstu' );

					dopisz( listaNaglowkow, napis, 'H' + stopien, stopien, naglowki[ i ] );

					ileNaglowkow++;
				}

				listaObszarow = czesc( dane( 'obszary' ), 'alyxa-spis-obszary' );

				for ( i = 0; i < obszary.length; i++ ) {
					ktora = rola( obszary[ i ] );

					if ( ! ktora || ! widoczny( obszary[ i ] ) || wObszarze( przyjete, obszary[ i ], ktora ) ) {
						continue;
					}

					nazwa = nazwaObszaru( obszary[ i ] );

					/* Sekcja i formularz bez nazwy nie sa obszarem - ani dla czytnika, ani tu. */
					if ( ! nazwa && ( 'region' === ktora || 'form' === ktora ) ) {
						continue;
					}

					napis = nazwa
						? String( dane( 'nazwany' ) || '%1$s: %2$s' ).replace( '%1$s', role[ ktora ] ).replace( '%2$s', nazwa )
						: role[ ktora ];

					dopisz( listaObszarow, napis, '', 1, obszary[ i ] );

					przyjete.push( { element: obszary[ i ], rola: ktora } );

					ileObszarow++;
				}

				pustaLista( listaNaglowkow, dane( 'brakNagl' ) );
				pustaLista( listaObszarow, dane( 'brakObsz' ) );

				komunikat.textContent = String( dane( 'znaleziono' ) )
					.replace( '%1$d', ileNaglowkow )
					.replace( '%2$d', ileObszarow );
			}

			/**
			 * Zdejmuje obrys i tabindex z miejsca, do ktorego przeniesiono fokus.
			 *
			 * @return {void}
			 */
			function zdejmijOznaczenie() {
				if ( ! oznaczony ) {
					return;
				}

				oznaczony.classList.remove( OZNACZENIE );
				oznaczony.removeEventListener( 'blur', zdejmijOznaczenie );

				if ( dodanyTabindex ) {
					oznaczony.removeAttribute( 'tabindex' );
				}

				oznaczony = null;
				dodanyTabindex = false;
			}

			/**
			 * Zamyka panel i przenosi fokus do wskazanego miejsca.
			 *
			 * Panel zamykamy najpierw: na telefonie zaslania cala strone, wiec
			 * przeniesienie za nim nie byloby widac. Przewijamy do srodka okna,
			 * bo przyklejony naglowek motywu zaslonilby element dosuniety do
			 * gory - chyba ze element jest wyzszy niz okno; wtedy do gory, zeby
			 * jego poczatek w ogole bylo widac.
			 *
			 * @param {Element} cel Element.
			 * @return {void}
			 */
			function przenies( cel ) {
				if ( ! cel || ! document.documentElement.contains( cel ) ) {
					return;
				}

				zdejmijOznaczenie();

				if ( cel.tabIndex < 0 && ! cel.hasAttribute( 'tabindex' ) ) {
					cel.setAttribute( 'tabindex', '-1' );
					dodanyTabindex = true;
				}

				oznaczony = cel;
				cel.classList.add( OZNACZENIE );

				zamknij( false );

				cel.focus( { preventScroll: true } );
				cel.addEventListener( 'blur', zdejmijOznaczenie );

				cel.scrollIntoView( {
					block: cel.getBoundingClientRect().height < window.innerHeight * 0.8 ? 'center' : 'start'
				} );
			}

			/**
			 * Nacisniecie pozycji spisu.
			 *
			 * @param {MouseEvent} zdarzenie Zdarzenie.
			 * @return {void}
			 */
			function zKlikniecia( zdarzenie ) {
				var pozycja = zdarzenie.target.closest( '[data-alyxa-cel]' );

				if ( pozycja ) {
					przenies( cele[ parseInt( pozycja.getAttribute( 'data-alyxa-cel' ), 10 ) ] );
				}
			}

			return {
				wlacz: function ( ktos ) {
					kontekst = ktos;

					if ( ! kontekst.pozycja || spis ) {
						return;
					}

					przyciskPokaz = kontekst.pozycja.querySelector( '[data-alyxa-akcja="pokaz"]' );
					komunikat = kontekst.pozycja.querySelector( '[data-alyxa-komunikat]' );

					if ( ! przyciskPokaz || ! komunikat ) {
						return;
					}

					spis = document.createElement( 'div' );
					spis.className = 'alyxa__spis';
					spis.id = 'alyxa-spis-struktury';
					spis.hidden = true;
					spis.addEventListener( 'click', zKlikniecia );

					kontekst.pozycja.appendChild( spis );

					przyciskPokaz.setAttribute( 'aria-expanded', 'false' );
					przyciskPokaz.setAttribute( 'aria-controls', spis.id );
				},

				wylacz: function () {
					zdejmijOznaczenie();

					if ( spis && spis.parentNode ) {
						spis.parentNode.removeChild( spis );
					}

					if ( przyciskPokaz ) {
						przyciskPokaz.removeAttribute( 'aria-expanded' );
						przyciskPokaz.removeAttribute( 'aria-controls' );
					}

					spis = null;
					cele = [];
				},

				/*
				 * Jeden przycisk rozwija i zwija spis. Napis zostaje ten sam,
				 * a stan niesie aria-expanded - ten sam wzorzec co przycisk
				 * otwierajacy panel.
				 */
				akcja: function ( nazwa ) {
					if ( 'pokaz' !== nazwa || ! spis ) {
						return;
					}

					if ( ! spis.hidden ) {
						spis.hidden = true;
						przyciskPokaz.setAttribute( 'aria-expanded', 'false' );
						komunikat.textContent = '';

						return;
					}

					zbuduj();

					spis.hidden = false;
					przyciskPokaz.setAttribute( 'aria-expanded', 'true' );
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
		var pozycja = panel.querySelector( '[data-alyxa-pozycja="' + slug + '"]' );

		return {
			slug: slug,
			dane: moduly[ slug ].dane || {},
			pozycja: pozycja,

			/**
			 * Odkrywa kontrolke modulu warunkowego.
			 *
			 * DLACZEGO METODA, A NIE pozycja.hidden = false W ZACHOWANIU.
			 * Bo od fazy 9 kafelek lezy w dziale, a dzial zlozony z samych
			 * kontrolek warunkowych wychodzi z serwera ukryty razem z nimi.
			 * Zachowanie, ktore odkrywalo by sam kafelek, zostawiloby go
			 * w ukrytym dziale - czyli dalej niewidocznego. Rdzen wie
			 * o dzialach, zachowanie nie musi.
			 *
			 * @return {void}
			 */
			pokaz: function () {
				var dzial;

				if ( ! pozycja ) {
					return;
				}

				pozycja.hidden = false;

				dzial = pozycja.closest( '.alyxa__dzial' );

				if ( dzial ) {
					dzial.hidden = false;
				}
			}
		};
	}

	/**
	 * Daje zachowaniom dojsc do glosu raz, przy starcie.
	 *
	 * Chodzimy po wszystkich modulach wlaczonych na tej stronie, a nie po
	 * wybranych przez odwiedzajacego: to jest miejsce, w ktorym modul moze
	 * odkryc swoja kontrolke, zanim ktokolwiek jej uzyje. Przelacznik
	 * warunkowy nie mialby jak tego zrobic pozniej - jego kafelek jest
	 * ukryty dopoty, dopoki nie powie, ze urzadzenie go udzwignie.
	 *
	 * @return {void}
	 */
	function przygotujZachowania() {
		var slug;

		for ( slug in zachowania ) {
			if ( ! Object.prototype.hasOwnProperty.call( zachowania, slug ) ) {
				continue;
			}

			if ( moduly[ slug ] && zachowania[ slug ].przygotuj ) {
				zachowania[ slug ].przygotuj( kontekstZachowania( slug ) );
			}
		}
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
	 * jego przyciskow.
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

			/*
			 * Obie metody sa nieobowiazkowe. Modul, ktory ma tylko przygotuj -
			 * bo cala jego prace robi arkusz, a skrypt tylko odkrywa kafelek -
			 * nie ma czego wlaczac ani wylaczac.
			 */
			if ( czynne && zachowania[ slug ].wlacz ) {
				zachowania[ slug ].wlacz( kontekstZachowania( slug ) );
			} else if ( ! czynne && zachowania[ slug ].wylacz ) {
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
	 * Doprowadza kontrolki w panelu do zgodnosci z wyborem.
	 *
	 * Chodzimy po modulach, a nie po elementach z atrybutem data: modul
	 * stopniowany ma dwa przyciski i jeden odczyt, wiec petla po elementach
	 * opisywalaby go dwa razy.
	 *
	 * @return {void}
	 */
	function odswiezKontrolki() {
		var slug;

		for ( slug in moduly ) {
			if ( Object.prototype.hasOwnProperty.call( moduly, slug ) ) {
				opisz( slug );
			}
		}
	}

	/**
	 * Ustawia stan kontrolki jednego modulu.
	 *
	 * @param {string} slug Slug modulu.
	 * @return {void}
	 */
	function opisz( slug ) {
		var kafelek;

		/* Czynnosc i odnosnik nie maja stanu, wiec nie ma tu czego opisywac. */
		if ( 'akcje' === moduly[ slug ].typ || 'link' === moduly[ slug ].typ ) {
			return;
		}

		if ( 'kolory' === moduly[ slug ].typ ) {
			opiszKolory( slug );

			return;
		}

		if ( 'stopnie' === moduly[ slug ].typ ) {
			/*
			 * Ta sama flaga, ktora decyduje o zachowaniu, decyduje tez
			 * o kontrolce: stopnie z obiegiem maja jeden kafelek chodzacy
			 * w kolko, stopnie bez obiegu - pare minus-plus z odczytem.
			 */
			if ( moduly[ slug ].obieg ) {
				opiszCykl( slug );
			} else {
				opiszStopnie( slug );
			}

			return;
		}

		kafelek = panel.querySelector( '[data-alyxa-modul="' + slug + '"]' );

		if ( kafelek ) {
			kafelek.setAttribute( 'aria-pressed', true === stan[ slug ] ? 'true' : 'false' );
		}
	}

	/**
	 * Ustawia stan kafelka stopniowanego.
	 *
	 * Odczyt bierze etykiete z rejestru - czyli procent, ktory ten modul sam
	 * o sobie podaje. Gdy modul zadnych nie deklaruje, zostaje opis "stopien
	 * X z Y", bo rdzen nie ma jak zgadnac, co jego stopnie znacza.
	 *
	 * Kafelek nie jest przyciskiem, wiec nie ma aria-pressed; stan niesie
	 * dla oka plakietka (atrybut ponizej), a dla czytnika ekranu odczyt,
	 * ktory jest obszarem aria-live.
	 *
	 * @param {string} slug Slug modulu.
	 * @return {void}
	 */
	function opiszStopnie( slug ) {
		var kafelek = panel.querySelector( '[data-alyxa-kafelek="' + slug + '"]' );
		var pole = kafelek ? kafelek.querySelector( '[data-alyxa-stan]' ) : null;
		var stopien = 'number' === typeof stan[ slug ] ? stan[ slug ] : 0;
		var etykiety = moduly[ slug ].etykiety || [];

		if ( ! kafelek || ! pole ) {
			return;
		}

		if ( etykiety[ stopien ] ) {
			pole.textContent = etykiety[ stopien ];
		} else if ( stopien > 0 ) {
			pole.textContent = ( teksty.stopien || '%1$d / %2$d' )
				.replace( '%1$d', stopien )
				.replace( '%2$d', moduly[ slug ].stopnie );
		} else {
			pole.textContent = teksty.wylaczone || '';
		}

		if ( stopien > 0 ) {
			kafelek.setAttribute( 'data-alyxa-czynny', '' );
		} else {
			kafelek.removeAttribute( 'data-alyxa-czynny' );
		}
	}

	/**
	 * Ustawia stan kafelka chodzacego w kolko.
	 *
	 * Wszystkie warianty leza w dokumencie od poczatku, wypisane przez PHP;
	 * tutaj zostaje tylko przestawienie atrybutu hidden. Nie skladamy ani
	 * napisu, ani rysunku - tlumaczenie zostaje po stronie serwera, a nazwa
	 * dostepna przycisku bierze sie sama z jedynego widocznego wariantu,
	 * bo element ukryty przez hidden do nazwy sie nie liczy.
	 *
	 * @param {string} slug Slug modulu.
	 * @return {void}
	 */
	function opiszCykl( slug ) {
		var kafelek = panel.querySelector( '[data-alyxa-kafelek="' + slug + '"]' );
		var stopien = 'number' === typeof stan[ slug ] ? stan[ slug ] : 0;
		var warianty;
		var i;

		if ( ! kafelek ) {
			return;
		}

		warianty = kafelek.querySelectorAll( '[data-alyxa-wariant]' );

		for ( i = 0; i < warianty.length; i++ ) {
			warianty[ i ].hidden = parseInt( warianty[ i ].getAttribute( 'data-alyxa-wariant' ), 10 ) !== stopien;
		}

		if ( stopien > 0 ) {
			kafelek.setAttribute( 'data-alyxa-czynny', '' );
		} else {
			kafelek.removeAttribute( 'data-alyxa-czynny' );
		}
	}

	/**
	 * Sprowadza stan modulu kolorow do obiektu z poprawnymi polami.
	 *
	 * Wszystko albo nic: obiekt, w ktorym brakuje jednego pola albo jedno
	 * nie jest kolorem #rrggbb, wypada w calosci. Uzupelnianie dawaloby
	 * palete, ktorej odwiedzajacy nie wybral.
	 *
	 * @param {string} slug    Slug modulu.
	 * @param {*}      wartosc Wartosc z pamieci.
	 * @return {Object|boolean} Kolory albo false.
	 */
	function poprawKolory( slug, wartosc ) {
		var pola = moduly[ slug ].pola || [];
		var wynik = {};
		var i;

		if ( ! wartosc || 'object' !== typeof wartosc || ! pola.length ) {
			return false;
		}

		for ( i = 0; i < pola.length; i++ ) {
			if ( 'string' !== typeof wartosc[ pola[ i ] ] || ! /^#[0-9a-f]{6}$/i.test( wartosc[ pola[ i ] ] ) ) {
				return false;
			}

			wynik[ pola[ i ] ] = wartosc[ pola[ i ] ].toLowerCase();
		}

		return wynik;
	}

	/**
	 * Kolory, ktore pokazuja pola: wybor odwiedzajacego albo pierwsza paleta.
	 *
	 * @param {string} slug Slug modulu.
	 * @return {Object}
	 */
	function wybraneKolory( slug ) {
		return stan[ slug ] && 'object' === typeof stan[ slug ] ? stan[ slug ] : ( moduly[ slug ].pary || [] )[ 0 ] || {};
	}

	/**
	 * Wlacza gotowa palete.
	 *
	 * @param {string} slug  Slug modulu.
	 * @param {number} numer Numer palety.
	 * @return {void}
	 */
	function ustawPalete( slug, numer ) {
		var para = ( moduly[ slug ].pary || [] )[ numer ];
		var wynik = para ? poprawKolory( slug, para ) : false;

		if ( ! wynik ) {
			return;
		}

		stan[ slug ] = wynik;

		zastosujZmiane();
	}

	/**
	 * Wlacza kolory przepisane z pol wyboru.
	 *
	 * Czytamy wszystkie pola naraz, a nie tylko to, ktore sie zmienilo:
	 * modul wylaczony pokazuje w polach pierwsza palete, wiec zmiana jednego
	 * koloru wlacza palete zlozona z niego i z dwoch pozostalych, ktore
	 * odwiedzajacy widzi.
	 *
	 * @param {string} slug Slug modulu.
	 * @return {void}
	 */
	function ustawKolory( slug ) {
		var pola = panel.querySelectorAll( '[data-alyxa-pole][data-alyxa-modul="' + slug + '"]' );
		var surowe = {};
		var wynik;
		var i;

		for ( i = 0; i < pola.length; i++ ) {
			surowe[ pola[ i ].getAttribute( 'data-alyxa-pole' ) ] = pola[ i ].value;
		}

		wynik = poprawKolory( slug, surowe );

		if ( ! wynik ) {
			return;
		}

		stan[ slug ] = wynik;

		zastosujZmiane();
	}

	/**
	 * Wylacza modul kolorow.
	 *
	 * @param {string} slug Slug modulu.
	 * @return {void}
	 */
	function wylaczKolory( slug ) {
		var paleta = panel.querySelector( '[data-alyxa-para][data-alyxa-modul="' + slug + '"]' );

		delete stan[ slug ];

		zastosujZmiane();

		/*
		 * Przycisk, ktory wlasnie nacisnieto, znika - fokus nie moze zostac
		 * na elemencie ukrytym, bo przepadlby razem z nim. Oddajemy go
		 * pierwszej palecie, czyli miejscu, z ktorego sie wlacza z powrotem.
		 */
		if ( paleta ) {
			paleta.focus();
		}
	}

	/**
	 * Wzgledna luminancja koloru #rrggbb wedlug WCAG.
	 *
	 * @param {string} kolor Kolor.
	 * @return {number}
	 */
	function luminancja( kolor ) {
		var kanaly = [ 1, 3, 5 ].map( function ( od ) {
			var c = parseInt( kolor.substr( od, 2 ), 16 ) / 255;

			return c <= 0.03928 ? c / 12.92 : Math.pow( ( c + 0.055 ) / 1.055, 2.4 );
		} );

		return 0.2126 * kanaly[ 0 ] + 0.7152 * kanaly[ 1 ] + 0.0722 * kanaly[ 2 ];
	}

	/**
	 * Odczyt kontrastu kazdego pola wzgledem tla.
	 *
	 * OBCINAMY DO JEDNEGO MIEJSCA, NIE ZAOKRAGLAMY. Kontrast 4,47:1
	 * zaokraglony dalby "4,5:1" obok ostrzezenia, ze wynik jest ponizej
	 * 4,5:1 - dwie sprzeczne informacje w jednym zdaniu.
	 *
	 * @param {HTMLElement} kafelek Pozycja modulu.
	 * @param {Object}      kolory  Kolory do sprawdzenia.
	 * @return {void}
	 */
	function opiszKontrast( kafelek, kolory ) {
		var pole = kafelek.querySelector( '[data-alyxa-kontrast]' );
		var wejscia = kafelek.querySelectorAll( '[data-alyxa-pole]' );
		var zdania = [];
		var ponizej = [];
		var jezyk = korzen.lang || undefined;
		var klucz;
		var nazwa;
		var jasniejszy;
		var ciemniejszy;
		var stosunek;
		var tekst;
		var i;

		if ( ! pole || ! kolory.tlo ) {
			return;
		}

		for ( i = 0; i < wejscia.length; i++ ) {
			klucz = wejscia[ i ].getAttribute( 'data-alyxa-pole' );

			if ( 'tlo' === klucz || ! kolory[ klucz ] ) {
				continue;
			}

			nazwa = wejscia[ i ].parentNode.firstElementChild.textContent;
			jasniejszy = Math.max( luminancja( kolory[ klucz ] ), luminancja( kolory.tlo ) );
			ciemniejszy = Math.min( luminancja( kolory[ klucz ] ), luminancja( kolory.tlo ) );
			stosunek = Math.floor( ( ( jasniejszy + 0.05 ) / ( ciemniejszy + 0.05 ) ) * 10 ) / 10;

			zdania.push(
				( teksty.kontrast || '%1$s: %2$s:1' )
					.replace( '%1$s', nazwa )
					.replace( '%2$s', stosunek.toLocaleString( jezyk, { minimumFractionDigits: 1, maximumFractionDigits: 1 } ) )
			);

			if ( stosunek < 4.5 ) {
				ponizej.push( nazwa );
			}
		}

		tekst = zdania.join( '. ' ) + '. ' + ( ponizej.length
			? ( teksty.ponizej || '%s' ).replace( '%s', ponizej.join( ', ' ) )
			: ( teksty.dosc || '' ) );

		/* Ten sam napis wpisany drugi raz bywa ogloszony drugi raz. */
		if ( pole.textContent !== tekst ) {
			pole.textContent = tekst;
		}

		if ( ponizej.length ) {
			pole.setAttribute( 'data-alyxa-ponizej', '' );
		} else {
			pole.removeAttribute( 'data-alyxa-ponizej' );
		}
	}

	/**
	 * Ustawia stan pozycji modulu kolorow.
	 *
	 * @param {string} slug Slug modulu.
	 * @return {void}
	 */
	function opiszKolory( slug ) {
		var kafelek = panel.querySelector( '[data-alyxa-kafelek="' + slug + '"]' );
		var kolory = wybraneKolory( slug );
		var wlaczone = !! stan[ slug ] && 'object' === typeof stan[ slug ];
		var elementy;
		var para;
		var zgodna;
		var klucz;
		var wylacz;
		var i;

		if ( ! kafelek ) {
			return;
		}

		elementy = kafelek.querySelectorAll( '[data-alyxa-pole]' );

		for ( i = 0; i < elementy.length; i++ ) {
			klucz = elementy[ i ].getAttribute( 'data-alyxa-pole' );

			if ( kolory[ klucz ] && elementy[ i ].value !== kolory[ klucz ] ) {
				elementy[ i ].value = kolory[ klucz ];
			}
		}

		/* Paleta jest "wcisnieta", gdy wybor zgadza sie z nia co do pola. */
		elementy = kafelek.querySelectorAll( '[data-alyxa-para]' );

		for ( i = 0; i < elementy.length; i++ ) {
			para = ( moduly[ slug ].pary || [] )[ parseInt( elementy[ i ].getAttribute( 'data-alyxa-para' ), 10 ) ] || {};
			zgodna = wlaczone;

			for ( klucz in para ) {
				if ( wlaczone && Object.prototype.hasOwnProperty.call( para, klucz ) && para[ klucz ] !== stan[ slug ][ klucz ] ) {
					zgodna = false;
				}
			}

			elementy[ i ].setAttribute( 'aria-pressed', zgodna ? 'true' : 'false' );
		}

		wylacz = kafelek.querySelector( '[data-alyxa-wylacz]' );

		if ( wylacz ) {
			wylacz.hidden = ! wlaczone;
		}

		if ( wlaczone ) {
			kafelek.setAttribute( 'data-alyxa-czynny', '' );
		} else {
			kafelek.removeAttribute( 'data-alyxa-czynny' );
		}

		opiszKontrast( kafelek, kolory );
	}

	/**
	 * Przelacza modul wlacz/wylacz.
	 *
	 * @param {string} slug Slug modulu.
	 * @return {void}
	 */
	function przelacz( slug ) {
		if ( ! moduly[ slug ] || 'przelacznik' !== moduly[ slug ].typ ) {
			return;
		}

		if ( true === stan[ slug ] ) {
			delete stan[ slug ];
		} else {
			stan[ slug ] = true;
		}

		zastosujZmiane();
	}

	/**
	 * Przesuwa modul stopniowany o jeden krok.
	 *
	 * DWA PRZYCISKI ZAMIAST JEDNEGO CHODZACEGO W KOLKO - zmiana wobec faz
	 * 1-7. Pierwsza wersja przechodzila 0, 1, 2, 3 i z powrotem do zera,
	 * a argumentem bylo mniej przystankow tabulatora. Przegral z tym, ze
	 * droga powrotna wiodla przez powiekszenie jeszcze wieksze niz to,
	 * ktore komus wlasnie przeszkodzilo.
	 *
	 * Na krancu nie robimy nic - tak samo jak powiekszanie w przegladarce.
	 * Bez tego warunku zapisywalibysmy do pamieci wartosc, ktora sie nie
	 * zmienila, i oglaszali czytnikowi zmiane, ktorej nie bylo.
	 *
	 * WYJATKIEM SA MODULY Z FLAGA "obieg" W REJESTRZE. Powyzszy argument
	 * dotyczy skali wielkosci: droga powrotna przez jeszcze wieksze
	 * powiekszenie jest gorsza od przycisku, ktory nic nie robi. Tam, gdzie
	 * stopnie sa rownorzednymi ustawieniami, a nie mniej i wiecej - jak
	 * wyrownanie tekstu - nie ma "jeszcze wieksze" i ten argument znika.
	 * Wtedy plus z ostatniego stopnia wraca na zero, czyli do wygladu
	 * strony bez modulu.
	 *
	 * @param {string} slug Slug modulu.
	 * @param {number} krok Kierunek: -1 albo 1.
	 * @return {void}
	 */
	function zmienStopien( slug, krok ) {
		if ( ! moduly[ slug ] || 'stopnie' !== moduly[ slug ].typ ) {
			return;
		}

		var teraz = 'number' === typeof stan[ slug ] ? stan[ slug ] : 0;
		var ile   = moduly[ slug ].stopnie;
		var dalej;

		if ( moduly[ slug ].obieg ) {
			/* Reszta z dzielenia liczy sie tez dla minusa: (0 - 1 + 4) % 4 = 3. */
			dalej = ( teraz + krok + ile + 1 ) % ( ile + 1 );
		} else {
			dalej = Math.min( Math.max( teraz + krok, 0 ), ile );
		}

		if ( dalej === teraz ) {
			return;
		}

		if ( 0 === dalej ) {
			delete stan[ slug ];
		} else {
			stan[ slug ] = dalej;
		}

		zastosujZmiane();
	}

	/**
	 * Odklada wybor, przepisuje go na strone i odswieza panel.
	 *
	 * @return {void}
	 */
	function zastosujZmiane() {
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

		zastosujZmiane();
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
		var slug;

		if ( kontrolka ) {
			/*
			 * Ten sam atrybut niesie modul, ale nie to samo nacisniecie.
			 * Przycisk z data-alyxa-akcja robi cos tu i teraz i niczego nie
			 * zapisuje, przycisk z data-alyxa-krok przesuwa stopien o jeden,
			 * a kazdy inny przelacza ustawienie.
			 */
			slug = kontrolka.getAttribute( 'data-alyxa-modul' );

			if ( kontrolka.hasAttribute( 'data-alyxa-para' ) ) {
				ustawPalete( slug, parseInt( kontrolka.getAttribute( 'data-alyxa-para' ), 10 ) );
			} else if ( kontrolka.hasAttribute( 'data-alyxa-wylacz' ) ) {
				wylaczKolory( slug );
			} else if ( kontrolka.hasAttribute( 'data-alyxa-pole' ) ) {
				/* Pole koloru: wybor przychodzi zdarzeniem change, nie kliknieciem. */
			} else if ( kontrolka.hasAttribute( 'data-alyxa-akcja' ) ) {
				wykonaj( slug, kontrolka.getAttribute( 'data-alyxa-akcja' ) );
			} else if ( kontrolka.hasAttribute( 'data-alyxa-krok' ) ) {
				zmienStopien( slug, parseInt( kontrolka.getAttribute( 'data-alyxa-krok' ), 10 ) );
			} else {
				przelacz( slug );
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

	/*
	 * Pole koloru zglasza wybor zdarzeniem change - dopiero gdy okno wyboru
	 * sie zamknie albo wartosc zostanie zatwierdzona. Zdarzenie input
	 * lecialoby przy kazdym ruchu suwaka, a z kazdym szedlby zapis do pamieci
	 * i nowy odczyt kontrastu dla czytnika ekranu.
	 */
	panel.addEventListener( 'change', function ( zdarzenie ) {
		var pole = zdarzenie.target.closest( '[data-alyxa-pole]' );

		if ( pole ) {
			ustawKolory( pole.getAttribute( 'data-alyxa-modul' ) );
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
	przygotujZachowania();
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
