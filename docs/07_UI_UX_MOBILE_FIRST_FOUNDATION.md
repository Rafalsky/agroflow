# UI/UX Stage: Mobile-First Foundation (Krok 1)

Cel tego kroku: wdrożyć bazowe założenia UX bez zmiany kolorystyki oraz przygotować kolejkę kolejnych prac implementacyjnych.

## 1. Założenia wdrożone w tym kroku (produkt + UX)

1. Mobile-first jako domyślny sposób projektowania każdej ścieżki wykonania zadania.
2. Struktura informacji oparta na zadaniach operacyjnych, nie na module technicznym.
3. Jeden główny cel na ekranie (telefon): wykonanie bieżącego zadania i przejście do następnego.
4. Stała logika statusów (`PENDING`, `DONE`, `FROZEN/SKIPPED`) i jednolite znaczenie akcji (`DONE/UNDO`).
5. RWD jako rozszerzenie: tablet/desktop nie zmienia logiki, tylko układ i gęstość informacji.

## 2. Architektura informacji (IA) - wersja funkcjonalna

### 2.1 Rola: Pracownik (telefon = główny kanał)
- Ekran wejścia: `Wejście Pracownika` (`/w/`).
- Ekran pracy dziennej: `Moje Zadania` (`/worker/tasks`).
- Sekcje na ekranie pracy:
  - `Do zrobienia teraz` (priorytet + aktualny blok dnia).
  - `Zaległe` (z poprzednich tygodni).
  - `Szybkie zgłoszenia` (Welfare, Incidents) jako widgety osadzone kontekstowo.
  - `Podsumowanie punktów` (bieżący tydzień + łącznie).

### 2.2 Rola: Zootechnik/Admin
- Ekran główny: `Panel Operacyjny` (`/admin`).
- Widoki operacyjne:
  - `Tydzień` (monitoring + przypisania + statusy).
  - `Pracownicy`.
  - `Szablony`.
  - `Dobrostan`.
  - `Awarie`.
  - `Historia/Audit`.
- Dla mobile admina: ten sam zakres funkcji, ale skrócone bloki i priorytet akcji operacyjnych (nie analityki).

## 3. Routing i przejścia między stronami (stan docelowy etapu UI)

### 3.1 Ścieżka pracownika
1. `GET /w/` -> formularz tokenu.
2. `POST /w/` -> redirect do `GET /worker/{accessToken}`.
3. `GET /worker/{accessToken}` -> autoryzacja magic-link i redirect do `GET /worker/tasks`.
4. `POST /worker/task/{id}/done` lub `POST /worker/task/{id}/undo` -> powrót do listy bez zmiany kontekstu strony.

### 3.2 Ścieżka admina
1. `GET /admin` -> panel operacyjny (default tab: tydzień bieżący).
2. Operacje tygodnia:
  - `POST /admin/week/{year}/{week}/generate`
  - `POST /admin/week/{year}/{week}/close`
  - `POST /admin/week/task/{id}/done`
  - `POST /admin/week/task/{id}/undo`
  - `POST /admin/week/task/{id}/assign`
3. Widok URL tygodnia pozostaje dostępny technicznie (`/admin/week/{year}/{week}`), ale UX powinien prowadzić przez jeden punkt wejścia (`/admin`) i deep-linki.

## 4. Zawartość ekranów (minimum funkcjonalne)

### 4.1 Worker - `/worker/tasks`
- Nagłówek: imię/nickname + punkty.
- Lista zadań w kolejności operacyjnej (najpierw zaległe pilne, potem bieżące).
- Przy każdym zadaniu:
  - nazwa,
  - status,
  - punkty,
  - jedna akcja główna (`DONE` lub `UNDO`),
  - wejście do widgetu kontekstowego, jeśli wymagane dane.
- Sekcja `Szybkie zgłoszenia` jako element listy zadań (nie osobny, ciężki moduł).

### 4.2 Admin - `/admin`
- Pasek kontrolny tygodnia: status, liczba zadań, liczba DONE, zaległe, punkty zespołu.
- Widok tygodnia: grupowanie po dniach i kategoriach, szybkie przypisanie pracownika, szybkie DONE/UNDO.
- Drawer/sheet: szczegóły zadania i widget wykonania, bez opuszczania kontekstu tygodnia.

## 5. Lista kolejnych kroków implementacyjnych (kolejność do realizacji)

1. Ujednolicenie routingu UX: jedna ścieżka główna dla admina (`/admin`) + deep-linki do tygodnia i filtrów.
2. Ujednolicenie worker flow: usunięcie rozjazdów między `WorkerTasks.vue` i `WorkerAgenda.vue`, jedna komponentowa ścieżka wykonania.
3. Standaryzacja komponentu zadania mobilnego: jednolity layout card + stałe miejsce CTA (`DONE/UNDO`).
4. Standaryzacja `ContextDrawer` na telefonie jako bottom sheet/fullscreen z poprawnym flow zapisu.
5. Refactor IA sekcji worker: `Do zrobienia teraz`, `Zaległe`, `Zgłoszenia` (bez dublowania informacji).
6. Ujednolicenie copy i etykiet CTA (PL, konsekwentne słownictwo domenowe).
7. Dopięcie testów e2e pod nową strukturę (najpierw testy krytycznych flow mobile).

## 6. Audyt e2e - błędy i niespójności do poprawy przed kolejnym krokiem UI

### 6.1 Błędy blokujące
1. Zdublowany `FeatureContext` pod tym samym namespace i klasą:
   - `tests/E2E/Behat/FeatureContext.php`
   - `tests/e2e/Behat/FeatureContext.php`
   To grozi konfliktem autoloadingu i niejednoznacznym wyborem implementacji kroków.

2. Kroki Gherkin w języku polskim bez pokrycia definicjami kroków:
   - `tests/e2e/features/admin/work_cycle.feature`
   - `tests/e2e/features/admin/scoring.feature`
   - `tests/e2e/features/worker/tasks.feature`
   - `tests/e2e/features/worker/welfare.feature`
   - `tests/e2e/features/incidents.feature`
   - `tests/e2e/features/welfare_death.feature`
   Aktualny kontekst implementuje wyłącznie zestaw kroków EN.

3. Niespójne trasy worker między scenariuszem a aplikacją:
   - scenariusz: wejście przez `/w/abc-123`
   - aplikacja: token obsługiwany przez `/worker/{accessToken}` po formularzu `/w/`.

### 6.2 Błędy wysokiego ryzyka (niestabilne testy)
1. Rozjazd selektorów i tekstów akcji:
   - test oczekuje np. `Oznacz jako Wykonane`, a UI używa skrótów (`ZRÓB`, `OTWÓRZ`) zależnie od widoku.
2. Feature `week_view.feature` testuje flow pod `/admin week current`, ale krok w jednym kontekście otwiera `/admin`, w drugim `/admin/week/current`.
3. W jednym kontekście czyszczona jest tabela `stock_change`, a faktyczna nazwa domenowa to `welfare_stock_change`.
4. Brak testów viewportu mobilnego jako bazowego scenariusza (obecnie testy są desktopocentryczne). 

## 7. Definicja gotowości przed kolejną iteracją UI

Kolejny krok UI zaczynamy dopiero gdy:
1. istnieje jeden aktywny `FeatureContext` i jedna konwencja kroków (PL albo EN),
2. krytyczny smoke flow worker (`/w/` -> token -> `/worker/tasks` -> `DONE`) przechodzi stabilnie,
3. smoke flow admin (`/admin` -> tydzień -> `DONE/UNDO`) przechodzi stabilnie,
4. testy nie zależą od kruchych klas CSS i losowych tekstów przycisków.
