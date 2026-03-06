# AGROFLOW SYSTEM PROMPT

**UWAGA DO AI / PROGRAMISTY:**
Przy każdej nowej iteracji kodowania, tworzeniu nowych funkcji, pisaniu logiki domenowej lub interfejsu użytkownika w tym projekcie, **MASZ OBOWIĄZEK** zastosować się do poniższych zasad. 

Zanim wygenerujesz jakikolwiek kod:
1. Przeczytaj i przeanalizuj wszystkie pliki z katalogu `docs/`, aby zrozumieć architekturę modularnego monolitu, podział na domeny (`WorkCycle`, `Audit`, `Scoring`, `Welfare`, `Incidents`) oraz założenia biznesowe aplikacji.
2. Zapoznaj się z założeniami z pliku `docs/ZADANIA_I_FEATURE.md`.

## ZASADY WDROŻENIOWE (CORE RULES)

1. **Zero generycznych "Todo"**
Agroflow to narzędzie egzekucji operacji i tworzenia audytu bioasekuracyjnego we współpracy z wymogami PIWET. Nawet codzienne przejście przez halę to nie jest proste "Tickbox" (zaznacz i zapomnij), lecz punkt zbierania danych operacyjnych, klimatycznych, zgłaszania incydentów i martwych sztuk.

2. **Widget Driven Architecture**
Jeśli zadanie powtarzalne (np. obchód, karmienie, sprzedaż) polega na wpisaniu specyficznej wartości (liczba sztuk, stopnie Celsjusza, notatka z naprawy, numer lochy), zaprojektuj **Dynamiczny Widget Vue** (z użyciem parametru `widget_type` zdefiniowanego w encji `TaskTemplate`), a zebrane w nim dane zserializuj jako JSON i zapisz do `execution_payload` w `TaskInstance` przy oznaczaniu zadania jako DONE.

3. **Złota Reguła Prostoty (Rozbudowa Modułów)**
Zgłaszanie awarii sprzętów (Incidents) oraz upadków zwierząt (Welfare) w pierwszych etapach wdrażaj **WYŁĄCZNIE jako "Szybkie Widgety"**. Podpinaj je wewnątrz bocznej szuflady (`ContextDrawer.vue`) standardowego zadania zdefiniowanego w harmonogramie. Nie buduj od razu potężnych, wieloekranowych modułów (SPA) do tych zgłoszeń. Prostota ponad skalowalność!

4. **Trzy Kolory Zadań (UI/UX)**
Bezwzględnie respektuj paletę barw podczas budowy interfejsu (Tailwind CSS):
- **Czerwone / Pomarańczowe (`bg-rose-500` / `bg-orange-600`)** – Operacje wymagane, pilne lub oczekujące (Status `PENDING`, priorytet `URGENT`).
- **Szmaragdowe / Zielone (`bg-emerald-600`)** – Zadania odhaczone, skompletowane i wykonane prawidłowo (Status `DONE`).
- **Niebieskie / Szare (`bg-slate-500` / blue)** – Zadania zablokowane, pominięte lub zamrożone (np. `SKIPPED`, `FROZEN`), które celowo zostały przesunięte, by nie zaburzać punktacji i statystyk cyklu produkcyjnego.

5. **Oddzielenie Treści od Logiki**
Wszelkie teksty informacyjne, procedury BHP, wyposażenie hali czy wytyczne do zadań umieszczaj bezpośrednio w polach tekstowych (np. `instruction` w `TaskTemplate`) w samej bazie danych (krzystając z Doctrine Migrations w celu ich zasilenia). **Nigdy** nie hardkoduj treści merytorycznych ("Załóż maskę", "Zgarnij ruszt") bezpośrednio w plikach widoków `.html.twig` czy `.vue`.

6. **Testowalność e2e**
Każdy feature ma proof of concept w postaci:
1. Testu e2e cucumber z jawnymi stepami
2. Dowodem dla użytkownika w postaci zrzutu ekranu funkcjonalności, kroków do weryfikacji poprawnej implementacji czy pełnoprawnym dowodem
