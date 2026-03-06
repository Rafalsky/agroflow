# Plan Reformy UI: Od "Todo-Listy" do "Planu Lekcji" 

W oparciu o rewizję obecnego interfejsu (widok `/admin/week/current`) oraz najnowsze ustalenia dotyczące filozofii "Rytmu Dnia" (Planu Lekcji), obecny interfejs wymaga gruntownej przebudowy, gdyż promuje generyczne podejście typu "Lista Zadań (To-Do)" z ciężkimi modalami, zamiast rutynowego odhaczania i szybkich widgetów.

## 1. Zmiana Terminologii i Logiki Organizacyjnej
- Zmiana nazwy sekcji "Zadania/Zaległe" na **"Rytm Dnia"** lub **"Plan Tygodnia"**.
- Wprowadzenie **Bloków Czasowych** wewnątrz każdego dnia (wizualne grupowanie w kolumnie danego dnia), np.:
    - **Poranny Obchód i Klimat** 
    - **Prace Bieżące / Karmienia** 
    - **Kontrola Końcowa i Raportowanie**
- Interfejs ma wymuszać "nawyk" – zadania wewnątrz bloków mają stałą kolejność.

## 2. Strategia Widget-Driven (Koniec z wielkim przyciskiem "Zrób")
- Obecnie system wymusza kliknięcie "Zrób", co otwiera przytłaczający, centralny modal "Detale zadania". To łamie "Złotą Regułę Prostoty".
- **Zadania binarne (Habits)**: Proste checkboxy dla rutyn (np. "Woda zakręcona", "Zgarnięte"), które po kliknięciu natychmiast oznaczają się jako wykonane (bez otwierania modali).
- **Zadania z danymi (Widgety)**: Zamiast "Zrób", dedykowany przycisk (np. `+ TEMP` dla temperatury, czerwona ikona `UPADEK` dla dobrostanu). Kliknięcie otwiera **ContextDrawer** (szufladę wyjeżdżającą z boku), gdzie jest tylko ten jeden konkretny widget, kontekst i przycisk Zapisu.

## 3. Komunikacja Wizualna (3 Kolory Statusu z prompt.md)
Obecny interfejs nadużywa koloru pomarańczowego. Należy bezwzględnie wdrożyć paletę:
- **PLANOWANE/ZABLOKOWANE (Szare/Niebieskie - Slate/Blue)**: Zadania czekające na swoją kolej lub celowo przesunięte (FROZEN/SKIPPED).
- **W TRAKCIE / AKTYWNE (Pomarańczowe/Czerwone - Rose/Orange)**: Zadania do wykonania w obecnym bloku czasowym lub zaległe pilne.
- **ZROBIONE (Zielone/Szmaragdowe - Emerald)**: Zadania odhaczone — wyraźna zmiana wyglądu (np. wyszarzenie, ikona "✓"), aby wzrok naturalnie pomijał to, co już zrobione i skupiał się na reszcie planu. Zapobiega to "hałasowi" po wykonaniu 80% dnia.

## 4. Architektura Interakcji i UX
- **ContextDrawer (Szuflada)**: Zastąpienie wszystkich modali szufladą boczną. Szuflada nie przysłania całego kontekstu tygodnia – pozwala użytkownikowi (np. zootechnikowi) nadal widzieć swój "Plan Lekcji" z boku, zgłaszając awarię czy temperaturę.
- **Wizualna Oś Czasu**: Podświetlenie obecnego dnia tygodnia (np. grubsze obramowanie kolumny "Środa" gdy jest środa).

## 5. Implementacja "Prostota ponad Skalę"
Usunięcie wszystkich zbędnych kliknięć. Widok ma przypominać wydrukowaną kartkę przyklejoną na drzwiach hali z miejscem na długopis (checkbox), na której w specyficznych momentach wpisujemy temperaturę lub zgłaszamy usterkę szufladą.

---
**Kolejne kroki przed implementacją UI**:
Zgodnie z poleceniem, zanim zaczniemy przebudowywać Vue i Twiga, musimy **zabezpieczyć obecny fundament logiki testami E2E (Cucumber)**. Pozwoli to na bezpieczną refaktoryzację komponentów wizualnych bez ryzyka zepsucia zapisu danych, generowania widoku i przypisywania statusów.
