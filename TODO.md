# AgroFlow - Skonsolidowana Lista TODO (Master Plan)

Dokument ten jest technicznym drogowskazem opartym na dokumentacji z `docs/`. Prowadzimy tu bardzo precyzyjny zapis tego, co zostało ukończone (Stage 0 - Stage 7) i wyznaczamy mikrokroki, tzw. zadania atomowe, dla wszystkich brakujących funkcjonalności do osiągnięcia pełnego MVP wg Złotej Reguły AgroFlow (minimalizm w UI, Mobile-First i Widget-Driven Architecture).

---

## ✅ Faza 1: Fundamenty i Logowanie (Zakończone)
- [x] Inicjalizacja projektu, schematy bazy danych, Docker, Tailwind, Vue (Stage 0-1)
- [x] Serwis Punktacji, Architektura zapytań tygodniowych (Stage 3)
- [x] Panel Administratora, Generator Tygodnia
- [x] Logowanie Pracownika Tokenem (Autoryzacja)
- [x] Pulpit Pracownika Mobile-First (Widok Zadań, Kolory Statusów, ContextDrawer)

---

## 🏗️ Faza 2: Dynamiczne Widgety Pracownicze (Do Zrobienia)
Zadania z tej grupy polegają na stworzeniu kodu Vue dla konkretnej akcji (wczytywanego w `ContextDrawer`), zadeklarowaniu Typu Widgetu (`widget_type`) w `TaskTemplate` i obsłudze zapisu JSON w Backendzie (`ExecutionPayload`).

### 2.1. PIWET & Dobrostan (WelfareDeathWidget)
Najpilniejszy widget raportowo-liczbowy. 
- [ ] Utworzyć `WelfareDeathWidget.vue` w katalogu `assets/vue/widgets/`.
- [ ] Dodać w nim selecta (Locha/Tucznik/Warchlak/Prosię), pole input `Ilość` (number), oraz pole textarea `Uwagi` (wymagane jeśli Ilość > 0).
- [ ] Zintegrować widget w pliku `ContextDrawer.vue` tak, aby wysyłał stan do eventu i dołączany był do `payload`.
- [ ] Dodać obsługę w `WorkerController::markDone`, która wywołuje specjalny Serwis Odjemujący sztuki z `CurrentStock` (Welfare) jeśli w payload są upadki.
- [ ] Oflagować ten widget w Seedach dla zadań pt. "Sprawdzenie Hal" i przetestować E2E.

### 2.2. Klimat i Środowisko (TemperatureWidget)
- [ ] Utworzyć `TemperatureWidget.vue`.
- [ ] Dodanie Input: Temperatura w °C (Step: 0.1).
- [ ] Walidacja w locie w Vue: Odczyt z `widget_schema` min/max dla strefy (np. 19-20). Jeżeli temp. wykroczy poza normę, wysunięcie dodatkowego przymusowego pola `Komentarz_Korekty`.
- [ ] Przekazanie do backendu i zapisanie w Payloadzie. Zrobienie wizualnej lampki dla Zootechnika na widoku zadania.

### 2.3. Raporty Incydentów & Socjal (IncidentReportWidget)
- [ ] Utworzyć `IncidentWidget.vue`.
- [ ] Prosta forma: Typ Incydentu z Dropdownu (1. Awaria wentylatora, 2. Zapchana Gnojowica, 3. Brak Wody, 4. Braki w Socjalu, 5. Inne).
- [ ] Tekstowy Opis (multiline).
- [ ] Zapiąć pod odpowiednie check-pointy (np. "Obchód popołudniowy").

### 2.4. Mieszalnia i Logistyka (FeedStatusWidget)
- [ ] Stworzyć nową Encję lub tabelę `SiloStatus` / `FeedProduction`.
- [ ] Widget Pracownika Mieszalni: "Zgłoś status Silosu". Wybór paszy (Starter, Grower, Finisher) i przycisk (Stan: Pusty, W Trakcie, Zasypany).
- [ ] Kolorowanie kafelka na Żółto / Zielono na podstawie statusu.
- [ ] Logowanie do zadań dziennych.

### 2.5. Raporty Sprzedaży i Transferów (SalesDispatchWidget)
- [ ] Utworzyć formularz logistyczny (Odbiorca, Ilość Sztuk, Waga w Tonach, Wybór Tira - Tak/Nie).
- [ ] Oprogramować Backend by odejmował tuczniki z `CurrentStock` i wpisywał do logów rzeźniano-audytowych.

---

## 👔 Faza 3: Role Zarządcze (Zootechnik i Manager)
Gdy pracownicy w terenie nakarmią system danymi z Widgetów, administracja musi mieć wygodne interfejsy.

### 3.1. Panel Zootechnika (Lustro / Mirror)
- [ ] Rozdzielić uprawnienia `ROLE_ADMIN`, `ROLE_MANAGER`, `ROLE_ZOOTECHNICIAN`.
- [ ] Stworzyć widok "Zastępstwa / Podglądu" -> Zootechnik widzi `WorkerAgenda.vue` wybranego pracownika na swoim komputerze z prawem zmiany/nadpisania "Z Książki".
- [ ] Alert Board (Dashboard dla Zootechnika): Czerwona lista zadań URGE, w których dodano incydent (np. Awaria Gnojowicy) wymagająca reakcji z tagiem `NEW`.
- [ ] Możliwość dodawania przez Zootechnika jednorazowych zadań Ad-Hoc na konkretny profil w bieżącym dniu.

### 3.2. Rozliczenia Managera i Płace (Payroll Module)
- [ ] Zbudować encję `WeeklySettlement` i `Payroll`. 
- [ ] Zbudować widok Managera umożliwiający w sobotę/niedzielę kliknięcie "Zamknij Tydzień Produkcyjny".
- [ ] Skrypt (Command) w tle, który przelicza wszystkie punkty każdego pracownika z calego zeszłego tygodnia i wylicza bonusy złotówkowe na podstawie configu.
- [ ] Widok raportu płacowego (PDF/Tabela) z historią wydajności do wypłaty do ręki. Zablokowanie starych tygodni (zamrożenie zmian w bazie).

---

## ⚙️ Faza 4: Harmonogramy Wymuszone i CRUDy Konfiguracyjne
Przejście z "czystych" Seedów na pełną konfigurację z poziomu przeglądarki.

### 4.1. Pełny CRUD Zadań Template'ów
- [ ] Tworzenie `TaskTemplate` przez UI admina (Określanie punktów, wybór dni tygodnia z multicheckboxa, przypinanie konkretnego `widget_type` do zadania).
- [ ] Obsługa "Wytycznych Warunkowych" np. "Jeśli Dzień Transferów = Czw, to zablokuj Paszowanie Korytarzy". Dodanie silnika reguł (RuleEngine).

### 4.2. Pracownicy i Zespoły
- [ ] Możliwość resetowania pinu/tokena z UI Managera.
- [ ] Zarządzanie umowami i stawką per pkt w profilu (HR).

---

## 📲 Faza 5: PWA i Notyfikacje
- [ ] Dodanie manifestu PWA w `public/manifest.json`.
- [ ] Rejestracja Service Workera (`assets/sw.js`).
- [ ] Integracja z web-push dla zadań URGENT (jeśli zapchała się gnojowica by zootechnik dostał powiadomienie Push na swój zablokowany telefon).

---

## 🧪 Przebieg Prac - Jak Działamy?
Będziemy brać **jeden podpunkt widgetu naraz**.
1. Nowy branch / nowy commit na podpunkt (np. "Feature: WelfareDeathWidget frontend").
2. Integracja z widokiem.
3. Test Backendowy / API do odbioru payloadu.
4. Test Behat weryfikujący nową gałąź procesu.

**(Aktualnie skupiamy się na Fazie 2 i kolejno wdrażamy widgety do szuflady i db, aby Pracownik stał się samowystarczalny)**
