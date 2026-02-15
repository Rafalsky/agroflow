Poniżej gotowa zawartość pliku:

```
03_ARCHITEKTURA_TECHNICZNA.md
```

---

# ARCHITEKTURA TECHNICZNA

## 1. Założenia architektoniczne

System projektowany jest jako:

* modularny monolit,
* jedna instancja aplikacji Symfony,
* jedna baza PostgreSQL,
* logiczne rozdzielenie kontekstów domenowych,
* brak mikroserwisów na etapie początkowym.

Celem architektury jest:

* prostota wdrożenia,
* niski koszt utrzymania,
* czytelny podział domen,
* możliwość dalszej rozbudowy bez przebudowy fundamentu.

---

# 2. Stack technologiczny

## Backend

* PHP 8.3+ (lub 8.4)
* Symfony 7.x
* Doctrine ORM
* Doctrine Migrations
* PostgreSQL
* Symfony Console (cron / jobs)
* Symfony Serializer
* Symfony Validator

## Frontend

* Twig (SSR jako shell aplikacji)
* Vue 3 (widgety / moduły interaktywne)
* Vite lub Webpack Encore (pipeline assetów)

## Infrastruktura

* Docker (docelowo)
* Nginx / Apache
* Cron (lub Symfony Scheduler w przyszłości)

---

# 3. Styl architektury aplikacji

System stosuje podejście:

> Lightweight Domain-Driven Structure

Nie pełny DDD z warstwą aplikacyjną i agregatami event-sourcing, lecz:

* wyraźne podziały domenowe,
* encje + serwisy domenowe,
* brak logiki biznesowej w kontrolerach,
* brak logiki biznesowej w Vue.

---

# 4. Struktura katalogów

Proponowana struktura:

```
src/
├── Domain/
│   ├── WorkCycle/
│   │   ├── Entity/
│   │   ├── Repository/
│   │   ├── Service/
│   │   └── Enum/
│   │
│   ├── Audit/
│   │   ├── Entity/
│   │   └── Service/
│   │
│   ├── Worker/              (Stage 2)
│   ├── Scoring/             (Stage 3)
│   ├── Welfare/             (Stage 4)
│   └── Incidents/           (Stage 5)
│
├── Application/
│   ├── Command/
│   └── DTO/
│
├── Infrastructure/
│   ├── Persistence/
│   ├── Security/
│   └── Scheduler/
│
├── Controller/
│
└── Kernel.php
```

---

# 5. Warstwy logiczne

## 5.1 Domain

Zawiera:

* encje Doctrine,
* serwisy domenowe,
* enumy,
* reguły biznesowe.

Nie zawiera:

* kontrolerów,
* Twig,
* kodu HTTP,
* zależności infrastrukturalnych.

---

## 5.2 Application

Warstwa orkiestracji przypadków użycia.

Zawiera:

* komendy,
* handler-y,
* DTO,
* logikę łączącą domenę z kontrolerami.

Nie zawiera:

* SQL,
* bezpośrednich operacji na requestach HTTP.

---

## 5.3 Infrastructure

Zawiera:

* implementacje repozytoriów,
* konfigurację Doctrine,
* security,
* scheduler,
* integracje z zewnętrznymi systemami.

---

## 5.4 Controller

Cienka warstwa HTTP.

Odpowiada wyłącznie za:

* przyjęcie requestu,
* walidację wejścia,
* wywołanie use-case,
* zwrócenie response.

Brak logiki biznesowej.

---

# 6. Generowanie tygodnia (mechanizm)

Implementacja:

1. Symfony Command:

   * `app:week:generate`
2. Cron wywołuje komendę w niedzielę 00:00
3. Komenda:

   * sprawdza czy tydzień istnieje
   * jeśli nie → tworzy ProductionWeek
   * generuje TaskInstance z TaskTemplate recurring

Reguła:

* generowanie musi być idempotentne

---

# 7. Audit – implementacja techniczna

## 7.1 AuditLogger (serwis)

Publiczne metody:

* log(entityType, entityId, eventType, payload, actor)

Audit:

* zapis do tabeli audit_log
* nie blokuje głównej operacji
* możliwe w przyszłości kolejkowanie

---

# 8. Frontend – podejście

## 8.1 SSR jako fundament

Twig:

* renderuje layout
* renderuje dane początkowe
* mount point dla Vue

## 8.2 Vue jako enhancement

Vue:

* interakcje (oznacz jako DONE)
* dynamiczne odświeżanie list
* formularze

Nie budujemy SPA w Stage 1.

---

# 9. API

Na etapie początkowym:

* wewnętrzne endpointy JSON
* brak publicznego API

Przykłady:

* POST /admin/week/generate
* POST /admin/task/{id}/done
* POST /admin/task/{id}/undo

W przyszłości:

* /api/worker/*
* /api/welfare/*

---

# 10. Baza danych – ogólne zasady

* UUID lub BIGINT (do decyzji)
* Indeksy na:

  * week_id
  * status
  * created_at
* JSONB dla payload audit
* Ograniczenia FK z ON DELETE RESTRICT

---

# 11. Bezpieczeństwo (etapowo)

Stage 1:

* brak auth (tryb administracyjny)

Stage 2:

* magic token workers
* cookie session

Stage 3+:

* role ADMIN / WORKER

---

# 12. Skalowalność

System projektowany dla:

* 10–20 pracowników
* kilkaset zadań tygodniowo
* kilka lat historii

PostgreSQL jest w pełni wystarczający.

---

# 13. Zasady rozwoju

1. Każda nowa funkcja musi mieć uzasadnienie operacyjne.
2. Brak premature optimization.
3. Audit nie może być opcjonalny.
4. Dane historyczne są niemodyfikowalne.
5. Snapshot zamiast referencji dla raportów.
6. Unikać dziedziczenia encji – preferować kompozycję.

---

# 14. Roadmapa techniczna

Stage 0:

* skeleton + domena + audit

Stage 1:

* WorkCycle + widok tygodnia

Stage 2:

* Worker + magic access

Stage 3:

* scoring

Stage 4:

* welfare (moduł Vue)

Stage 5:

* incidents
