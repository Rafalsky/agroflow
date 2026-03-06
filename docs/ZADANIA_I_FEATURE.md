# Zestawienie Wymagań z luznych notatek

Poniżej znajduje się kompletna derywacja zadań, raportów oraz reguł interfejsu zdefiniowanych w pliku `docs/luzne.notatki.txt`.

## 1. Surowa Lista Zadań i Procedur (Exhaustive List)
*By nic nie uciekło.*

- [ ] **Deratyzacja**: Wpięcie wszystkich karmników.
- [ ] **Tygodniowe cykle planowe**: Pon (Krycia, sprzedaż tucznika), Wt (Grupowanie), Śr (Szczepienia), Czw (Odsad), Pt (Przerzut warchlaka), So (Zasiedlenie porodówki).
- [ ] **Wytyczne warunkowe**: "W dzień przemieszczeń loch - nie paszować na halach".
- [ ] **Mieszalnia pasz**: Raport codzienny - kolor żółty (w trakcie produkcji), zielony (zasypane).
- [ ] **Sprzedaże**: Raportowanie sprzedaży tucznika, warchlaka (Tak/Nie, do kogo, ilość sztuk, ilość ton, lokalizacja narodzin). Transport tucznika.
- [ ] **Stan stada (PIWET)**: Raportowanie upadków (prosięta, warchlaki, tuczniki, lochy). Bezwzględnie aktualny stan liczbowy zwierząt i przemieszczenia.
- [ ] **Gnojowica**: Przepuszczenie dzienne (Tak/Nie), Awarie/Zapchanie (+uwagi, np. Hala Alicji).
- [ ] **Awarie i Socjal**: Panel zgłaszania "Awaria", "Braki w socjalnym".
- [ ] **Karmienie - Hale chlewni (A, B...)**: Checklista kroków (obstukiwanie, garnięcie, paszowanie, pojenie). Wykrycie chorób (locha nr), awarii, braku wyjadania paszy. Zapewnienie oprzyrządowania (deska, zgarniacz).
- [ ] **Karmienie - Porodówki (1, 2...)**: Pasza wg krzywej, odchody, sprawdzanie prosiąt (+kojec), badanie loch (+stanowisko), sprzątanie pod daszkami, kontrola temperatury, trociny, awarie. 
- [ ] **Izolatki (Chore lochy)**: Woda, ilość zjedzonej paszy, obecność odchodów.
- [ ] **Obchód Tuczarni (1, 2, 3, T3, D11)**: Awaria wody, wentylatora, karmnika. Podanie z ręki, rodzaj paszy (starter, grower, finisher). Pomiary sprzętu: temperatury w komorach (normy: małe 23°, średnie 21-22°, duże 19-20°). 
- [ ] **Zmiana 14:00-22:00**: Pytanie o pozostawioną pracę z pierwszej zmiany, lokalizacje pojenia i karmienia.
- [ ] **BHP i Instrukcje (instruction)**: Tekst przypominający o sprzęcie (trucie much -> załóż rękawice, okulary).
- [ ] **Kategorie Pracy**: Dezynsekcja, deratyzacja, dezynfekcja, porządki, ochrona wejść.
- [ ] **Interfejs UI**: Zadania czerwone (PENDING), zielone (DONE), *niebieskie (FROZEN - zamrożone/zawieszone)*. Wyświetlanie na karcie wykonawcy (Robert T.) i uwag. Dodatkowe widoki dla Akuszera i Zootechnika, filtrowanie historii wykonawcy.

---

## 2. Podział: Feature vs Wpis w Bazie (Kategoryzacja Implementacyjna)

Aby przenieść te notatki na aplikację modułową, należy rozdzielić proste operacje bazy danych (zwykłe checklisty "odhacz/wykonaj") od programistycznych funkcjonalności (Widgetów, które zbierają konkretne parametry w celach analitycznych).

### 🚀 A. Nowe Featur'y i Widgety (Programowanie kodu Vue / PHP)
Poniższe elementy wymagają stworzenia nowych *Dynamicznych Widgetów* (`widget_type`) dołączanych do instancji zadań (podobnie jak ChecklistWidget) i zbierania tych danych w logach / encjach:
1. **`TemperatureWidget` (Klimat i Środowisko)**: Widget pytający pracownika o odczyt temperatur (wymusza wpisanie °C i weryfikuje względem norm np. 21-22 dla stref średnich). W przypadku odchyłów poprosi o uwagi. Obsługa Tuczarni i Porodówek.
2. **`WelfareDeathWidget` (Upadki dla PIWET)**: Specjalny formularz podłączony do infrastruktury *Stage 4 Welfare*. Przy zadaniu pyta o upadki (sztuki, sekcja, kategoria wiekowa). Aktualizuje centralny `CurrentStock`.
3. **`SalesDispatchWidget` (Sprzedaże)**: Formularz transportowy. Pola: Nabywca, Ilość sztuk, Wagaton, Powiązane zgrupowanie lokalizacji.
4. **`IncidentReportWidget` (Awarie, Zapchania, Szpital)**: Zaawansowany moduł tekstowo-raportowy. Może zgłosić "Zapchanie gnojowicy (Hala Alicji)", "Chorą lochę (Nr 445)", "Brak wyjadania", "Braki w socjalnym". To przyspieszona wersja *Stage 5 Incidents*.
5. **`FeedStatusWidget` (Materiały z Mieszalni i Logistyka)**: Widget potwierdzania zasypu (może zmieniać status z w takrcie produkcji na zasypane), wybór paszy (starter/grower/finisher) z list.
6. **Zarządzanie Zadaniami (UI/UX)**:
    - Dodanie statusu `FROZEN` w bazie (kolor niebieski). Zablokowanie zadania np. gdy hala jest pusta.
    - Opracowanie widoków "Dashboardu" ról (np. zakładka Zootechnik, zakładka Akuszer).

### 🗄️ B. Wpisy w Bazie (Migracje - Dane Operacyjne)
Wszystkie poniższe elementy to "proste" obiekty w konfiguracji systemu, nie wymagające modyfikacji silnika, ale prawidłowego wypelnienia w pliku migracji:
1. **Oznaczenia przypomnień do `instruction`**: 
   - Komunikaty BHP (Trucie much -> ubierz maskę!).
   - Restrykcje dniowe (Dzień przemieszczeń -> brak paszowania na korytarzach).
2. **Standardowe "ChecklistWidget" (Już zaimplementowane)**:
   - Obchód / Karmienie Hala A, Hala B: Checkbox "Garnięcie na szybko pod lochami", "Paszowanie", "Obstukiwanie".
   - Porodówka: "Sprzątanie pod daszkami", "Posypka".
3. **Harmonogram (Recurring)**: 
   - Stworzenie Szablonów Dniowych ("Szczepienia" przypięte tylko do Środy, "Zasiedlenie" tylko do Soboty itp.).
4. **Słowniki Kategorii**:
   - Skonfigurowanie kolumn `category` (Dezynsekcja, Bioasekuracja, Produkcja).

---

## 3. PROMPT NADZORUJĄCY KODOWANIE (Do wklejenia na wstęp każdej implementacji)

Poniższy tekst to "System Prompt" dla AI lub Programisty do ścisłego przestrzegania w trakcie zamieniania tej listy na kod. Zanim zaczniesz implementować kolejne moduły, powołaj się na te zasady:

> **[PROMPT ZASAD WDROŻENIOWYCH AGROFLOW]** 
> 
> "Programując nowe mechaniki Agroflow, trzymaj się następujących dyrektyw:
> 1. **Zero generycznych "Todo"**: Agroflow to narzędzie egzekucji operacji i tworzenia audytu bioasekuracyjnego we współpracy z wymogami PIWET. Nawet codzienne przejście przez halę to nie "Tickbox", lecz punkt zbierania danych klimatycznych, incydentów i martwych sztuk.
> 2. **Widget driven architecture**: Jeśli zadanie polega na wpisaniu wartości (sztuki, stopnie celsjusza, notatka z naprawy), zaprojektuj **Widget Vue (`widget_type: ...`)**, a jego wyjście zserializuj jako JSON w `executionPayload`. 
> 3. **Złota reguła Stage 4/5**: Zgłaszanie upadków (Welfare) i awarii sprzętów (Incidents) w pierwszym przejściu implementujemy WYŁĄCZNIE jako "Szybkie Widgety" przypinane wewnątrz szuflady (ContextDrawer) normalnego Dziennego/Tygodniowego zadania zdefiniowanego w encji `TaskInstance`. Nie buduj potężnych wieloekranowych modułów aplikacji dla tych zgłoszeń. Prostota ponad skalę!
> 4. **Trzy Kolory Zadań**: Zwróć uwagę na paletę podczas budowy UI w CSSie - Czerwone/Pomarańczowe to operacje wymagane (PENDING), Szmaragdowe/Zielone to odhaczone i skompletowane (DONE), a Niebieskie (FROZEN/SKIPPED) to te celowo przesunięte/zablokowane by nie mąciły cyklu produkcyjnego. 
> 5. **Odzielenie Treści od Logiki**: Dane tekstowe takie jak procedury BHP, wyposażenie hali itd. wrzucasz bezpośrednio do pola `instruction` z markdownem w bazie lub z migracjami, bez hardcodowania jakichkolwiek z nich w plikach widoków .html.twig czy .vue."
