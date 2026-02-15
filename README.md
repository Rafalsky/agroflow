# AgroFlow

> Operacyjny system zarządzania tygodniowym cyklem pracy fermy trzody chlewnej.

AgroFlow nie jest klasyczną aplikacją typu "todo".  
To uproszczony system operacyjny dla fermy — zaprojektowany wokół tygodnia produkcyjnego jako podstawowej jednostki organizacyjnej.

Projekt powstał z realnej potrzeby: odciążenia zootechnika i centralizacji obowiązków operacyjnych w jednym miejscu.

---

# 🎯 Cel systemu

System wspiera:

- planowanie i realizację zadań operacyjnych,
- kontrolę wykonania zadań przez zespół,
- rejestrowanie zdarzeń operacyjnych,
- budowanie odpowiedzialności zespołowej,
- przygotowanie fundamentu pod moduły rozszerzone (np. dobrostan zwierząt).

Priorytetem jest **prostota operacyjna**, nie rozbudowanie funkcjonalne.

---

# 🧠 Punkt wyjścia domeny

Centralnym bytem systemu jest:

## ProductionWeek (Tydzień produkcyjny)

Każdy tydzień:

- jest osobnym bytem w systemie,
- posiada status `OPEN` / `CLOSED`,
- zawiera instancje zadań przypisane do danego tygodnia,
- stanowi podstawę raportowania i rozliczania pracy.

Nowy tydzień powstaje automatycznie w niedzielę o 00:00 na podstawie aktywnych szablonów zadań cyklicznych.

---

# 👥 Role w systemie

## Zootechnik (Administrator operacyjny)

Odpowiada za:

- definiowanie szablonów zadań,
- generowanie tygodnia produkcyjnego,
- monitorowanie realizacji zadań,
- zamykanie tygodnia,
- (w kolejnych etapach) zarządzanie pracownikami, raporty, awarie.

Na etapie początkowym system działa bez logowania — wyłącznie w trybie administracyjnym.

---

## Pracownik (Stage 2)

Będzie odpowiedzialny za:

- realizację zadań,
- oznaczanie zadań jako wykonane,
- zgłaszanie awarii,
- rejestrowanie zdarzeń operacyjnych.

---

# 🗂 Model zadań

## 1️⃣ TaskTemplate (Szablon zadania)

Definicja zadania powtarzalnego lub jednorazowego.

Pola:
- nazwa,
- liczba punktów,
- priorytet (`NORMAL` / `URGENT`),
- dzień tygodnia,
- recurring (czy powtarzalne),
- kategoria (opcjonalnie),
- active.

Szablon nie jest wykonywany bezpośrednio — służy do generowania instancji.

---

## 2️⃣ TaskInstance (Instancja zadania)

Konkretne zadanie należące do danego tygodnia produkcyjnego.

- posiada snapshot danych z momentu wygenerowania,
- może być oznaczone jako wykonane,
- może mieć przypisanego wykonawcę (w przyszłości),
- należy zawsze do jednego ProductionWeek.

Statusy:
- `PENDING`
- `DONE`

---

# 🔄 Zasady generowania tygodnia

- W niedzielę 00:00 tworzony jest nowy ProductionWeek.
- System generuje instancje zadań na podstawie aktywnych template recurring.
- Generowanie jest idempotentne (brak duplikatów).
- Zaległe zadania z poprzednich tygodni pozostają widoczne jako „zaległe”.

---

# 🧾 Audit (Historia zdarzeń)

Od pierwszego etapu system rejestruje zdarzenia biznesowe.

Zapisywane są m.in.:

- utworzenie tygodnia,
- wygenerowanie zadań,
- utworzenie / modyfikacja / usunięcie szablonu,
- oznaczenie zadania jako wykonane,
- zamknięcie tygodnia.

Audit:

- przechowuje typ zdarzenia,
- identyfikator encji,
- dane pomocnicze (JSON),
- datę i czas operacji,
- typ aktora (SYSTEM / ADMIN / WORKER).

Audit jest niemodyfikowalny i stanowi podstawę kontroli operacyjnej.

---

# 🧱 Modułowość systemu

System jest projektowany jako **modularny monolit** oparty o jedną instancję Symfony i jedną bazę danych.

## Moduły

- **WorkCycle** — zarządzanie tygodniem i zadaniami
- **Audit** — rejestr zdarzeń
- **Worker** (Stage 2)
- **Scoring** (Stage 3)
- **Welfare** (Stage 4)
- **Incidents** (Stage 5)

Moduły są logicznie odseparowane w kodzie (katalogi, serwisy, encje), lecz działają w jednej aplikacji.

---

# 🐖 Moduł Welfare (Stage 4)

Moduł służy do rejestrowania zmian liczby zwierząt.

Zapewnia:

- aktualny stan stada,
- walidację (brak stanu ujemnego),
- historię zmian,
- mobilny interfejs operacyjny.

Każda zmiana jest zapisywana jako `StockChange`.  
Stan aktualizowany jest wyłącznie przez serwis domenowy.

---

# 🧮 Scoring (Stage 3)

System przewiduje:

- punkty za wykonane zadania,
- punkty indywidualne,
- punkty zespołowe,
- możliwość definiowania celów tygodniowych.

Punkty przechowywane są w modelu `ScoreLedger` (event-based).

---

# 🚨 Incidents (Stage 5)

Awarie są osobnym bytem domenowym.

Flow:
- zgłoszenie,
- akceptacja / odrzucenie,
- rozwiązanie.

Nie dziedziczą po TaskInstance.

---

# 🛠 Technologia

Backend:
- PHP 8.3+
- Symfony 7.x
- Doctrine ORM
- PostgreSQL

Frontend:
- Twig (SSR jako shell aplikacji)
- Vue 3 (widgety / moduły interaktywne)
- Vite

Architektura:
- Modularny monolit
- Jedna aplikacja
- Jedna baza danych

---

# 🧩 Główne reguły domenowe

- Tydzień jest centralnym bytem organizacyjnym.
- Dane historyczne nie są nadpisywane.
- Snapshot w TaskInstance zabezpiecza raporty.
- Każda istotna operacja jest logowana.
- Prostota UX ponad rozbudowanie.
- System wspiera realną operację fermy, nie ją komplikuje.

---

# 🗺 Roadmapa

Stage 0 — infrastruktura i fundament  
Stage 1 — tygodniowy cykl + zadania (admin only)  
Stage 2 — pracownicy + magic access  
Stage 3 — scoring  
Stage 4 — welfare  
Stage 5 — incidents  
Stage 6 — SaaS readiness  

---

# 🚀 Status projektu

Projekt w fazie aktywnego rozwoju (MVP – Stage 1).

Celem pierwszego etapu jest pełne wdrożenie systemu w jednym gospodarstwie i weryfikacja realnej wartości operacyjnej przed przejściem do modelu SaaS.

---

# 📌 Filozofia projektu

> Prostota ponad rozbudowanie.  
> Operacyjność ponad „ładne feature’y”.  
> Audit od dnia 0.  
> Najpierw działa u mnie, potem u innych.

---
