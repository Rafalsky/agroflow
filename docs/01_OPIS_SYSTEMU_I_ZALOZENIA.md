OPIS SYSTEMU I ZAŁOŻENIA PROJEKTOWE
1. Cel systemu

Celem systemu jest wsparcie zarządzania tygodniowym cyklem pracy w gospodarstwie rolnym (fermie trzody chlewnej) poprzez:

planowanie i realizację zadań operacyjnych,

kontrolę wykonania zadań i budowanie odpowiedzialności zespołowej,

rejestrowanie zdarzeń operacyjnych i przygotowanie fundamentu pod dalsze moduły (np. dobrostan zwierząt).

**System nie jest klasyczną aplikacją typu „todo”. Jego mechanika opiera się o rutynę, nawyki i rytm dnia:**
- Podejście "Planu Lekcji w Szkole" - raz ułożony harmonogram (np. w sierpniu) służy zespołowi przez kolejne miesiące (aż do czerwca) z jedynie drobnymi, okazyjnymi modyfikacjami.
- System nie pyta "co masz dzisiaj do zrobienia", ale weryfikuje "czy odhaczyłeś wszystko to, co ZAWSZE robisz o tej porze w dany dzień" (np. *czy woda zakręcona?*).
- Zootechnik konfiguruje zadania jednorazowo, a na co dzień sprawdza jedynie "obecność" (wykonanie) oraz raporty i wygenerowane eksporty. Użytkownicy wiedzą co mają robić, a system upewnia się, że nie pominęli kluczowych kroków (BHP, PIWET).

2. Punkt wyjścia domeny

Podstawową jednostką organizacyjną systemu jest:

Tydzień produkcyjny (ProductionWeek)

Każdy tydzień:

jest osobnym bytem w systemie,

posiada status (otwarty / zamknięty),

zawiera instancje zadań przypisane do tego tygodnia,

stanowi podstawę raportowania.

Nowy tydzień powstaje automatycznie w niedzielę o godzinie 00:00 na podstawie zdefiniowanych wcześniej szablonów zadań cyklicznych.

3. Role w systemie
Zootechnik (Administrator operacyjny)

Odpowiada za:

definiowanie szablonów zadań,

generowanie tygodnia produkcyjnego,

monitorowanie realizacji zadań,

zamykanie tygodnia,

w przyszłości: zarządzanie pracownikami, raporty, awarie.

Na etapie początkowym system działa bez logowania – wyłącznie w trybie administracyjnym.

Pracownik (etap późniejszy)

Będzie odpowiedzialny za:

realizację zadań,

oznaczanie zadań jako wykonane,

zgłaszanie awarii,

rejestrowanie zdarzeń operacyjnych.

4. Model zadań

System rozróżnia dwa poziomy:

4.1 Szablon zadania (TaskTemplate)

Definicja zadania powtarzalnego lub jednorazowego.

Pola:

nazwa,

liczba punktów,

priorytet (zwykłe / pilne),

dzień tygodnia,

czy powtarzalne (recurring),

kategoria (opcjonalnie).

Szablon nie jest wykonywany bezpośrednio – służy do generowania instancji.

4.2 Instancja zadania (TaskInstance)

Konkretne zadanie należące do określonego tygodnia produkcyjnego.

Instancja:

posiada snapshot danych z momentu wygenerowania,

może być oznaczona jako wykonana,

może mieć przypisanego wykonawcę (w przyszłości),

należy zawsze do jednego tygodnia.

5. Zasady generowania tygodnia

W niedzielę 00:00 tworzony jest nowy tydzień produkcyjny.

System generuje instancje zadań na podstawie aktywnych szablonów recurring.

Generowanie jest idempotentne (nie tworzy duplikatów).

Zaległe zadania z poprzednich tygodni pozostają widoczne jako „zaległe”.

6. Statusy
Tydzień:

OPEN

CLOSED

Zadanie:

PENDING

DONE

FROZEN / SKIPPED (zadania zablokowane lub celowo pominięte)

7. Widoki systemu (Stage 1)
Widok tygodniowy (desktop)

Sekcja: Zaległe

Dni tygodnia (poniedziałek–niedziela)

Oznaczenie zadań recurring (ikoną)

Akcja: oznacz jako wykonane / cofnięcie

Interfejs ma być maksymalnie prosty, czytelny, bez nadmiaru funkcji.

7.1 Priorytet urządzeń mobilnych (mobile-first)

Głównym środowiskiem pracy użytkownika końcowego jest telefon na hali.

Dlatego:

projekt bazowy UI powstaje najpierw dla smartfona (360-430 px),

desktop i tablet są warstwą rozszerzającą (pełne RWD, ale wtórne do mobile),

krytyczne operacje (DONE/UNDO, zgłoszenie incydentu, wpis temperatury/upadku) muszą być możliwe do wykonania jedną ręką,

interakcje nie mogą zależeć wyłącznie od hovera/myszki,

komponenty interaktywne mają być "touch-friendly" (duże strefy kliku, czytelna typografia, niski próg pomyłek).

8. Audit / Historia zdarzeń

Od pierwszego etapu system prowadzi rejestr zdarzeń biznesowych.

Zapisywane są m.in.:

utworzenie tygodnia,

wygenerowanie zadań,

utworzenie / modyfikacja / usunięcie szablonu,

oznaczenie zadania jako wykonane,

zamknięcie tygodnia.

Audit:

przechowuje typ zdarzenia,

datę i czas,

identyfikator encji,

dane pomocnicze (JSON).

Celem audytu jest pełna kontrola operacyjna, wyciąganie błędów oraz możliwość późniejszej analizy.

**Raportowanie i Eksport:**
Z uwagi na zbierane dane pomiarowe i powtarzalne checklisty, pełna warstwa raportowa musi w późniejszych etapach zakładać **eksport danych do pliku Excel/CSV z uwzględnieniem podanych przez administratora filtrów**.

9. Punkty i odpowiedzialność zespołowa (etap późniejszy)

System przewiduje:

naliczanie punktów za wykonane zadania,

punkty indywidualne,

punkty zespołowe,

możliwość definiowania celów tygodniowych.

Na etapie początkowym funkcjonalność punktowa nie jest aktywna, lecz model danych uwzględnia jej przyszłe wdrożenie.

10. Modułowość systemu

System projektowany jest jako modularna aplikacja oparta o jedną instancję Symfony i jedną bazę danych.

Planowane moduły:

WorkCycle (zarządzanie tygodniem i zadaniami)

Welfare (stan zwierząt)

Incidents (awarie)

Scoring (punkty i cele)

Audit (wspólny rejestr zdarzeń)

Moduły są logicznie odseparowane w kodzie (katalogi, serwisy, encje), lecz działają w jednej aplikacji.

11. Moduł Animal Welfare (planowany)

Moduł służy do rejestrowania zmian liczby zwierząt.

Zapewnia:

aktualny stan stada,

walidację (brak stanu ujemnego),

historię zmian,

szybkie zgłaszanie poprzez **Widget Driven Architecture** (np. `WelfareDeathWidget`) zintegrowane bezpośrednio w bocznym panelu zadania (ContextDrawer), bez konieczności budowy osobnego, wielkiego modułu UI.

Moduł jest niezależny domenowo od WorkCycle, lecz operacyjnie mocno z nim zintegrowany.

12. Technologia

Backend:

Symfony 7.x

Doctrine ORM

PostgreSQL

Frontend:

Twig (SSR jako shell aplikacji)

Vue 3 (widgety / moduły interaktywne)

Baza danych:

PostgreSQL

migracje Doctrine

Architektura:

Monolit modularny

Jedna instancja aplikacji

Jedna baza danych

13. Zasady projektowe

Prostota ponad rozbudowanie.

Każda funkcja musi mieć uzasadnienie operacyjne.

Brak nadmiarowych mechanizmów autoryzacyjnych na starcie.

Domeny rozdzielone logicznie.

Audit od dnia 0.

System ma wspierać realną pracę operacyjną, a nie ją komplikować.

14. Etapy realizacji

Stage 0 – infrastruktura i fundament
Stage 1 – tygodniowy cykl + zadania (admin only)
Stage 2 – pracownicy + magic access
Stage 3 – scoring
Stage 4 – welfare (zwinne Widgety Vue, np. `WelfareDeathWidget`)
Stage 5 – incidents (zwinne Widgety Vue, np. `IncidentReportWidget`)
