MODEL DOMENY
1. Wprowadzenie

Dokument opisuje model domenowy systemu zarządzania tygodniowym cyklem pracy fermy trzody chlewnej.

Model jest projektowany jako modularny monolit z wyraźnym rozdzieleniem kontekstów domenowych (bounded contexts).

Na etapie początkowym aktywny jest moduł WorkCycle oraz Audit. Pozostałe moduły są przewidziane jako rozszerzenia.

2. Konteksty domenowe
2.1 WorkCycle (aktywny od Stage 1)

Odpowiada za:

zarządzanie tygodniem produkcyjnym

zarządzanie szablonami zadań

generowanie instancji zadań

realizację zadań

2.2 Audit (aktywny od Stage 0)

Odpowiada za:

rejestrowanie zdarzeń biznesowych

zapewnienie śladu operacyjnego

2.3 Worker (Stage 2)

Odpowiada za:

identyfikację pracowników

przypisywanie wykonania zadań

2.4 Scoring (Stage 3)

Odpowiada za:

naliczanie punktów

cele zespołowe

2.5 Welfare (Stage 4)

Odpowiada za:

rejestrowanie zmian stanu zwierząt

2.6 Incidents (Stage 5)

Odpowiada za:

zgłoszenia awarii

proces akceptacji i rozwiązania

3. Model WorkCycle
3.1 ProductionWeek

Reprezentuje tydzień produkcyjny.

Pola:

id

year (int)

week_number (int, ISO week)

status (OPEN | CLOSED)

opened_at (datetime)

closed_at (datetime nullable)

Zasady:

W systemie może istnieć tylko jeden otwarty tydzień.

Nowy tydzień powstaje automatycznie w niedzielę 00:00.

Zamknięty tydzień nie powinien być edytowany (reguła biznesowa).

3.2 TaskTemplate

Definicja zadania wzorcowego.

Pola:

id

name

points

priority (NORMAL | URGENT)

weekday (1–7)

recurring (bool)

category (string nullable)

active (bool)

Zasady:

Template recurring jest używany przy generowaniu nowego tygodnia.

Modyfikacja template nie wpływa na już wygenerowane instancje.

Template może zostać zdezaktywowany (active=false).

3.3 TaskInstance

Konkretne zadanie w ramach danego tygodnia.

Pola:

id

week_id (FK → ProductionWeek)

template_id (nullable FK → TaskTemplate)

name_snapshot

points_snapshot

priority_snapshot

weekday_snapshot

status (PENDING | DONE)

done_at (nullable datetime)

done_by_worker_id (nullable FK → Worker)

assigned_to_worker_id (nullable FK → Worker)

created_at

Zasady:

Każda instancja należy do dokładnie jednego tygodnia.

Snapshot danych zabezpiecza raporty historyczne.

Jeśli assigned_to_worker_id = null → zadanie jest zespołowe.

Jeśli assigned_to_worker_id ≠ null → w przyszłości możliwe ograniczenie wykonania.

4. Worker (Stage 2)
4.1 Worker

Reprezentuje pracownika.

Pola:

id

nickname

magic_token

active (bool)

created_at

Zasady:

magic_token służy do logowania bez hasła.

Worker może wykonywać wiele zadań.

Worker może mieć przypisane zadania (w przyszłości).

5. Audit
5.1 AuditLog

Rejestr zdarzeń biznesowych.

Pola:

id

entity_type (string)

entity_id (uuid/int)

event_type (string)

payload (jsonb)

actor_type (SYSTEM | ADMIN | WORKER)

actor_id (nullable)

created_at

ip_address (nullable)

Zasady:

Logowane są wyłącznie zdarzenia biznesowe.

Audit nie powinien blokować operacji głównej (fire-and-forget).

Audit jest niemodyfikowalny.

6. Scoring (Stage 3)
6.1 ScoreLedger

Rejestr przyznanych punktów.

Pola:

id

worker_id

week_id

task_instance_id

points

created_at

Zasady:

Każde oznaczenie DONE generuje wpis w ledger.

Cofnięcie DONE usuwa lub kompensuje wpis (decyzja implementacyjna).

Suma punktów liczona jest dynamicznie lub przez agregację.

7. Welfare (Stage 4)
7.1 CurrentStock

Aktualny stan zwierząt.

Pola:

id

current_count

updated_at

7.2 StockChange

Rejestr zmian stanu.

Pola:

id

delta (int)

reason (string)

note (nullable string)

previous_value

new_value

created_at

actor_id (nullable Worker/Admin)

Zasady:

Stan nie może spaść poniżej 0.

Każda zmiana musi być zapisana.

CurrentStock jest aktualizowany wyłącznie przez serwis domenowy.

8. Incidents (Stage 5)
8.1 Incident

Reprezentuje zgłoszenie awarii.

Pola:

id

title

description

status (PENDING | APPROVED | REJECTED | RESOLVED)

reported_by_worker_id

approved_by_admin_id (nullable)

resolved_by_worker_id (nullable)

created_at

resolved_at (nullable)

Zasady:

Incident jest osobnym bytem, nie dziedziczy po TaskInstance.

Po zatwierdzeniu może być wyświetlany w sekcji „Awarie”.

9. Relacje między bytami

ProductionWeek
→ ma wiele TaskInstance

TaskTemplate
→ może generować wiele TaskInstance

TaskInstance
→ należy do jednego ProductionWeek
→ może być powiązana z Worker (wykonanie / przypisanie)

Worker
→ może wykonać wiele TaskInstance
→ może mieć wiele wpisów ScoreLedger

StockChange
→ wpływa na CurrentStock

AuditLog
→ może dotyczyć dowolnej encji

10. Główne reguły domenowe

Tydzień jest centralnym bytem organizacyjnym.

Zadania recurring są generowane jako instancje na nowy tydzień.

Dane historyczne nie są nadpisywane.

Każda istotna operacja jest logowana.

Moduły są logicznie odseparowane.

System ma wspierać realną operację fermy, nie ją komplikować.