STAGE PLAN (ROADMAPA WDROŻENIA)
0. Cel dokumentu

Ten dokument jest planem realizacji systemu w etapach (Stage 0–6) w modelu A → B:

A: narzędzie działające w jednym gospodarstwie (realna wartość operacyjna)

B: produkt SaaS (wiele gospodarstw, proces sprzedaży, stabilność, bezpieczeństwo)

Każdy stage ma:

zakres (Scope)

rezultaty (Deliverables)

kryteria ukończenia (Definition of Done)

milestone’y (kolejne kroki)

Stage 0 — Fundament projektu (Foundation)
Cel

Działająca aplikacja Symfony z bazą PostgreSQL, migracjami, minimalnym UI i modułem audytu od dnia 0.

Scope

Symfony + Doctrine + Migrations

Postgres

Twig (shell)

Pipeline assetów dla Vue (widget mount)

Audit (tabela + serwis loggera)

Struktura katalogów pod moduły (WorkCycle/Audit itd.)

Deliverables

uruchomienie lokalne + dev config

audit_log zapisuje zdarzenia

strona admin działa (prosty layout + mount point Vue)

Definition of Done

php bin/console doctrine:migrations:migrate działa od zera

Healthcheck endpoint działa

AuditLogger zapisuje zdarzenie testowe do audit_log

Jest minimalny layout aplikacji (Twig)

Milestones

Repo + skeleton + env (dev)

Doctrine + Postgres + migrations

Struktura modułów w src/Domain/*

AuditLog entity + migration + AuditLogger

UI shell (Twig) + pipeline Vue (Hello Widget)

Stage 1 — WorkCycle core (admin-only, bez auth)
Cel

Zootechnik zarządza tygodniem produkcyjnym i zadaniami recurring/standard. System generuje instancje zadań na tydzień i pozwala oznaczać DONE.

Scope

ProductionWeek (OPEN/CLOSED)

TaskTemplate (recurring + weekday)

TaskInstance (snapshot, status DONE/PENDING)

Generator tygodnia (idempotentny)

Widok tygodniowy (desktop)

Sekcja “zaległe”

Audit zdarzeń biznesowych

Deliverables

/admin/week/:year/:week — widok tygodnia

Przycisk: “Generuj tydzień” (dla dev; docelowo cron)

Przycisk: “Zamknij tydzień” (opcjonalnie na końcu stage)

Oznaczanie zadań jako DONE / UNDO

Ikonka “recurring” przy instancji

Definition of Done

da się wygenerować tydzień i nie powstają duplikaty

listy: zaległe + dni tygodnia (pon–nd)

DONE/UNDO działa i zapisuje audyt

zmiany template nie psują instancji (snapshot istnieje)

Milestones

Encje + migracje: production_week, task_template, task_instance

Serwis WeekGenerator (idempotentny)

Kontrolery admin: generate/open/close week

Widok tygodnia (Twig + proste akcje)

Vue widget (opcjonalnie): oznacz DONE bez reload

Stage 2 — Workers + magic URL (identyfikacja wykonawcy)
Cel

Pracownik wchodzi w link i wykonuje zadania. System zapisuje kto wykonał zadanie.

Scope

Worker (nickname, token, active)

Magic access: /w/{token}

Worker view: mobilna lista zadań

TaskInstance: done_by_worker_id, done_at

Audit: logowania (opcjonalnie) i wykonania przez worker

Deliverables

/admin/workers — CRUD (zootechnik)

/w/{token} — worker list view

DONE zapisuje wykonawcę

Zootechnik widzi wykonawcę w admin view

Definition of Done

worker token działa bez manualnego logowania/hasła

DONE zapisuje done_by i done_at

istnieje możliwość dezaktywacji workera (token przestaje działać)

audit rejestruje “task done by worker”

Milestones

Worker entity + migration

Admin CRUD workers + generowanie tokenów

Worker view (mobile) + endpointy DONE/UNDO

Integracja audytu pod worker

Stage 3 — Scoring (punkty) w wersji “ledger”
Cel

Punkty są naliczane stabilnie i audytowalnie: indywidualne i zespołowe per tydzień.

Scope

ScoreLedger (wpis per wykonanie)

Agregacje: worker/week, team/week

Cofnięcie DONE: kompensacja lub usunięcie wpisu (preferowana kompensacja)

Deliverables

widok punktów w tygodniu (admin)

widok punktów pracownika (worker)

podstawowy “team total” dla tygodnia

Definition of Done

każde DONE tworzy zapis w ledger

UNDO usuwa/kompensuje zapis w ledger deterministycznie

suma punktów = suma ledger (bez niespójności)

Milestones

ScoreLedger entity + migration

Hook w logice DONE/UNDO

Agregacje + proste endpointy

Widoki admin/worker

Stage 4 — Welfare (Animal Welfare Widget)
Cel

Rejestrowanie zmian stanu stada z pełną historią i walidacją (brak wartości ujemnych). Mobilny interfejs.

Scope

CurrentStock (aktualny stan)

StockChange (delta, reason, note, previous, new)

Serwis ApplyStockChange (jedyna ścieżka modyfikacji)

Historia zmian + audyt

Deliverables

/welfare — widok modułu (widget Vue)

aktualny stan + formularz zmiany (delta + reason + note)

lista historii zmian

walidacja “nie schodzimy poniżej 0”

Definition of Done

zmiana stanu zawsze zapisuje StockChange

CurrentStock nie jest edytowany ręcznie (tylko serwis)

audyt rejestruje zmianę

UI mobilne działa stabilnie (bez skomplikowanych ekranów)

Milestones

Encje welfare + migracje

Serwis domenowy + walidacje

Endpointy + autoryzacja (na razie admin/worker token)

Vue widget + historia

Stage 5 — Incidents (awarie) jako osobny byt
Cel

Zgłoszenia awarii z procesem akceptacji i rozwiązywania. Widoczność awarii nad listą zadań.

Scope

Incident entity + status flow

Worker: report incident

Admin: approve/reject

Worker: resolve

Raport awarii: eksport CSV (najpierw CSV, nie “Excel xlsx”)

Deliverables

sekcja “Awarie” (worker + admin)

panel akceptacji (admin)

historia statusów (audit)

Definition of Done

pełny flow: report → approve/reject → resolve

awarie są oddzielone od zadań cyklicznych (osobny byt)

audyt rejestruje wszystkie przejścia statusów

Milestones

Encje + migracje

Workflow serwisowy (status machine)

UI worker/admin

Eksport CSV awarii (admin)

Stage 6 — SaaS readiness (A → B)
Cel

Przygotować system do wdrożeń jako produkt (wiele gospodarstw, minimalne bezpieczeństwo i stabilność).

Scope

Multi-tenant (gospodarstwo jako tenant)

Podstawowe role i zarządzanie dostępem

Hardening tokenów (rotacja, wygasanie)

Backup/retencja

Monitoring (logi, metryki)

Proces wdrożenia nowego gospodarstwa (onboarding)

Deliverables

farm/tenant jako pierwszy-klasy byt

izolacja danych per tenant (FK + scope)

panel admin: tworzenie tenantów + pracowników

minimalny billing/proces fakturowania (nawet manualny)

Definition of Done

jedna instancja obsługuje wiele gospodarstw bez mieszania danych

tokeny i dostępy są bezpieczne minimalnie pod produkcję

jest procedura backup + restore

jest proces onboardingu “od zera” w < 1h

Milestones

Tenant model + migracje + scoping repozytoriów

Auth/roles minimalne (admin/worker)

Token hardening (exp/rotation)

Backup + monitoring

Onboarding checklist + dokumentacja

Zasady ogólne realizacji

Prostota UX > feature’y.

Audit od dnia 0 (zdarzenia biznesowe, nie każdy techniczny update).

Snapshot danych w TaskInstance (spójne raporty historyczne).

Idempotencja generatorów (week generation nie tworzy duplikatów).

Modułowość w kodzie (WorkCycle/Welfare/Incidents itd. osobno).

Bez SaaS “przed czasem” — najpierw działa u Ciebie, potem u 2–3 gospodarstw, dopiero potem Stage 6.

Minimalne metryki (warto zbierać od Stage 1)

liczba zadań / tydzień

% wykonanych w terminie

liczba zaległych na koniec tygodnia

czas przygotowania raportu tygodniowego (przed/po)

(od Stage 5) liczba awarii / tydzień i czas do rozwiązania