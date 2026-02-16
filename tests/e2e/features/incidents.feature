# language: pl
Funkcja: Zgłaszanie i obsługa awarii
  Jako Pracownik chcę zgłaszać awarie
  Jako Zootechnik chcę zarządzać ich naprawą

  Scenariusz: Pełny cykl życia awarii
    # 1. Zgłoszenie
    Mając otwarty widget awarii przez pracownika "Jan"
    Gdy wpiszę tytuł "Awaria poidła sala 3"
    I wybiorę priorytet "Wysoki"
    I kliknę "WYŚLIJ"
    Wtedy nowa awaria powinna pojawić się na liście ze statusem "Oczekuje"

    # 2. Akceptacja przez admina
    Gdy Zootechnik wejdzie do panelu "Awarie"
    I kliknie "ZATWIERDŹ" przy zgłoszeniu "Awaria poidła sala 3"
    Wtedy status awarii powinien zmienić się na "W realizacji"

    # 3. Naprawa przez pracownika
    Gdy pracownik "Jan" odświeży swoją listę
    I kliknie przycisk "ROZWIĄZANE" przy tej awarii
    Wtedy awaria powinna zniknąć z listy aktywnych zgłoszeń pracownika
    Oraz status awarii w panelu admina powinien wynosić "Naprawiona"

  Scenariusz: Odrzucenie niezasadnego zgłoszenia
    Gdy Zootechnik zobaczy nowe zgłoszenie
    I wpisze komentarz "To nie jest awaria, wystarczy wyczyścić"
    I kliknie "ODRZUĆ"
    Wtedy zgłoszenie powinno otrzymać status "Odrzucona"
    Oraz powód odrzucenia powinien być widoczny w historii zgłoszenia

  Scenariusz: Eksport bazy awarii do CSV
    Gdy Zootechnik kliknie przycisk "EKSPORT CSV" w panelu awarii
    Wtedy system powinien wygenerować plik .csv zawieraący wszystkie zgłoszenia
    Oraz plik powinien zawierać kolumny z tytułem, statusem i autorem zgłoszenia
