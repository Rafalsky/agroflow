# 05_IMPLEMENTATION_STAGE_0_1.md

## Cel dokumentu
Ten plik prowadzi krok po kroku przez implementację **Stage 0 + Stage 1** w sposób możliwie bezbolesny.
Zakładamy:
- Symfony 7.x
- PostgreSQL
- Doctrine ORM + Migrations
- Twig jako shell
- Vue 3 jako widgety (bez męczarni) → **Vite + @symfony/vite-bundle**
- Audit od dnia 0 (zdarzenia biznesowe)

> Uwaga: nazwy klas i ścieżek są proponowane. Trzymaj konsekwencję.

---

## Wstępne wymagania środowiska (Ubuntu)
Minimalne paczki PHP (CLI):
- ext-xml
- ext-zip
- ext-mbstring
- ext-intl
- ext-pgsql
- ext-curl
- unzip

Dodatkowo Node + Yarn.

---

# STAGE 0 — Foundation

## Milestone 0.1 — Podstawowy szkielet + uruchomienie
### Krok 0.1.1 — Composer dependencies
Zainstaluj bazowe paczki:

- doctrine + migrations
- twig
- maker (dev)

Checklist:
- [ ] `composer require symfony/twig-bundle`
- [ ] `composer require symfony/orm-pack doctrine/doctrine-migrations-bundle`
- [ ] `composer require --dev symfony/maker-bundle`

### Krok 0.1.2 — Konfiguracja DB (Postgres)
- [ ] Ustaw `DATABASE_URL` w `.env` (dev)
- [ ] `php bin/console doctrine:database:create`
- [ ] `php bin/console doctrine:migrations:migrate`

---

## Milestone 0.2 — Vite + Vue 3 (bez męczarni)
Cel: mieć działający pipeline i pierwszy widget Vue.

### Krok 0.2.1 — Vite bundle
- [ ] `composer require symfony/vite-bundle`

### Krok 0.2.2 — Yarn dependencies
- [ ] `yarn add -D vite @vitejs/plugin-vue`
- [ ] `yarn add vue@^3`

### Krok 0.2.3 — Minimalne pliki Vite
Utwórz:
- `vite.config.ts`
- `assets/app.ts`
- `assets/admin.ts`
- `assets/vue/admin-week.ts` (pierwszy widget)
- `templates/base.html.twig` (vite tags)

Checklist:
- [ ] w `base.html.twig` podpięte entrypointy Vite
- [ ] `/admin` renderuje mount point `#vue-admin-week`
- [ ] widget Vue montuje się i pokazuje tekst

### Krok 0.2.4 — Tryb dev
- [ ] `yarn dev`
- [ ] `symfony server:start` lub `php -S` (jak wolisz)

---

## Milestone 0.3 — Struktura modułów w kodzie
Utwórz katalogi:

src/Domain/
Audit/
Entity/
Service/
WorkCycle/
Entity/
Repository/
Service/
Enum/


Checklist:
- [ ] katalogi istnieją
- [ ] autoload PSR-4 OK

---

## Milestone 0.4 — Audit (tabela + logger)
### Założenie
Audit loguje zdarzenia biznesowe (nie każde techniczne update).

### Krok 0.4.1 — Encja AuditLog
Stwórz encję `AuditLog`:

Pola minimalne:
- id (BIGINT)
- entityType (string)
- entityId (string)  // przechowuj jako string, nawet jeśli id jest int
- eventType (string)
- payload (jsonb)
- actorType (string) // SYSTEM/ADMIN/WORKER (na razie używamy SYSTEM)
- actorId (nullable string)
- createdAt (datetime_immutable)
- ipAddress (nullable string)

Checklist:
- [ ] migracja
- [ ] tabela `audit_log` powstała (jsonb)
- [ ] indeks po `created_at`, opcjonalnie po `entity_type`

### Krok 0.4.2 — Serwis AuditLogger
Serwis `AuditLogger` z metodą np.:

- `log(string $entityType, string $entityId, string $eventType, array $payload = [], string $actorType='SYSTEM', ?string $actorId=null, ?string $ip=null): void`

Checklist:
- [ ] logger działa i zapisuje rekord
- [ ] test ręczny: endpoint /admin zapisuje event

---

## Milestone 0.5 — Minimalny routing admin
### Krok 0.5.1 — AdminController
- [ ] `/admin` → widok z layoutem i mount point Vue
- [ ] zapis do audytu `admin.opened`

Definition of Done Stage 0:
- [ ] DB działa, migracje działają
- [ ] Vite + Vue działa (widget na stronie admin)
- [ ] Audit zapisuje eventy
- [ ] Struktura modułów gotowa

---

# STAGE 1 — WorkCycle (tydzień + zadania) admin-only

## Milestone 1.1 — Encje i migracje WorkCycle
### 1.1.1 — ProductionWeek
Encja `ProductionWeek`:
- id (BIGINT)
- year (int)
- weekNumber (int)
- status (OPEN/CLOSED) — enum/string
- openedAt (datetime_immutable)
- closedAt (nullable datetime_immutable)

Constraints:
- UNIQUE(year, week_number)

### 1.1.2 — TaskTemplate
Encja `TaskTemplate`:
- id
- name (string)
- points (int)
- priority (NORMAL/URGENT) — enum/string
- weekday (1–7)
- recurring (bool)
- category (nullable string)
- active (bool)

Indexes:
- (recurring, active)
- weekday

### 1.1.3 — TaskInstance
Encja `TaskInstance`:
- id
- week (ManyToOne ProductionWeek)
- template (nullable ManyToOne TaskTemplate)

Snapshot fields:
- nameSnapshot (string)
- pointsSnapshot (int)
- prioritySnapshot (string)
- weekdaySnapshot (int)

Execution:
- status (PENDING/DONE)
- doneAt (nullable datetime_immutable)

Future-proof (nullable, jeszcze nieużywane):
- doneByWorkerId (nullable BIGINT/FK później)
- assignedToWorkerId (nullable BIGINT/FK później)

Indexes:
- week_id
- status
- weekday_snapshot
- done_at

Checklist:
- [ ] migracje utworzone i zastosowane

---

## Milestone 1.2 — Generator tygodnia (idempotentny)
### Cel
Dla danego (year, week) stworzyć tydzień, a następnie wygenerować instancje zadań recurring.

### Serwis: WeekGenerator
Metody (propozycja):
- `getOrCreateWeek(int $year, int $week): ProductionWeek`
- `generateRecurringTasks(ProductionWeek $week): int` (zwraca liczbę utworzonych instancji)

Zasady:
- jeśli `TaskInstance` dla `(week_id, template_id)` już istnieje → nie twórz ponownie
- snapshot kopiowany z template

Audit:
- `week.created` (gdy utworzono nowy tydzień)
- `week.tasks_generated` (ile utworzono instancji)

Checklist:
- [ ] generator idempotentny
- [ ] powtórne uruchomienie nie dubluje
- [ ] audit zapisuje eventy

---

## Milestone 1.3 — Admin CRUD TaskTemplate
Użyj MakerBundle:
- [ ] `make:crud TaskTemplate`

Uprość UI:
- tylko kluczowe pola
- checkbox `active`, `recurring`
- select `weekday`
- select `priority`

Audit:
- `task_template.created`
- `task_template.updated`
- `task_template.deleted`

Checklist:
- [ ] CRUD działa
- [ ] audit dla create/update/delete

---

## Milestone 1.4 — Widok tygodnia (admin)
### Routing
Propozycja:
- `/admin/week/current` → redirect do aktualnego tygodnia
- `/admin/week/{year}/{week}` → widok tygodnia
- POST `/admin/week/{year}/{week}/generate` → generuj instancje
- POST `/admin/task-instance/{id}/done`
- POST `/admin/task-instance/{id}/undo`
- POST `/admin/week/{year}/{week}/close` (opcjonalnie w Stage 1)

### UI
Na stronie tygodnia:
- Sekcja `Zaległe`:
  - wszystkie `TaskInstance` z wcześniejszych tygodni o status=PENDING
- Sekcje dniówki:
  - pon–nd, sort po priority
- Ikonka recurring:
  - jeśli `template_id != null AND template.recurring = true` (lub dodaj prosty boolean snapshot)

Audit:
- `task_instance.done`
- `task_instance.undone`

Checklist:
- [ ] generuj tydzień działa z UI
- [ ] DONE/UNDO działa
- [ ] zaległe działa
- [ ] audit loguje kluczowe eventy

---

## Milestone 1.5 — Vue widget (opcjonalnie, ale zalecane)
Cel: uprościć “DONE/UNDO” bez przeładowania.

Propozycja:
- Twig renderuje listę + data-attributes
- Vue łapie kliknięcia i robi fetch POST na endpointy
- po sukcesie odświeża status elementu (prosty state)

Checklist:
- [ ] klik DONE/UNDO działa bez reload
- [ ] błędy pokazane minimalnie (alert/inline)

---

## Milestone 1.6 — Close week (opcjonalne na koniec Stage 1)
- przycisk “Zamknij tydzień”
- status CLOSED
- blokada edycji template nie jest konieczna
- blokada DONE/UNDO w closed week do decyzji (domyślnie: blokuj)

Audit:
- `week.closed`

---

## Definition of Done Stage 1
- [ ] Da się stworzyć tydzień i wygenerować recurring tasks
- [ ] Widok tygodnia pokazuje zaległe + dni tygodnia
- [ ] DONE/UNDO działa
- [ ] Template CRUD działa
- [ ] Audit loguje: week created/tasks generated, template CRUD, task done/undo (min.)

---

# Sugerowana kolejność commitów
1. `chore: init doctrine + migrations + postgres`
2. `chore: add vite + vue and base layout`
3. `feat(audit): add audit_log and AuditLogger`
4. `feat(workcycle): add entities and migrations`
5. `feat(workcycle): implement WeekGenerator (idempotent)`
6. `feat(admin): add TaskTemplate CRUD`
7. `feat(admin): add week view + generate`
8. `feat(admin): add DONE/UNDO actions + audit`
9. `feat(ui): vue enhance done/undo`

---

# Minimalne endpointy (Stage 1)
- GET  `/admin`
- GET  `/admin/task-templates`
- GET  `/admin/week/{year}/{week}`
- POST `/admin/week/{year}/{week}/generate`
- POST `/admin/task-instance/{id}/done`
- POST `/admin/task-instance/{id}/undo`
- POST `/admin/week/{year}/{week}/close` (opcjonalnie)

---

# Notatki projektowe (ważne)
1. **Nie używaj dziedziczenia encji** na tym etapie.
2. **Snapshot w TaskInstance** jest obowiązkowy.
3. **Audit loguje zdarzenia biznesowe**, nie każdy update.
4. Generator tygodnia musi być **idempotentny**.
5. UI ma być “rolnicze”: duże elementy, prosty flow, zero zbędnych opcji
