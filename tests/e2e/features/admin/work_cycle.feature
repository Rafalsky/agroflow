# language: pl
Funkcja: Zarządzanie Cyklem Pracy (Admin)
  Jako Zootechnik
  Chcę zarządzać tygodniami produkcyjnymi i zadaniami
  Aby zapewnić ciągłość operacyjną gospodarstwa

  Założenia:
    Mając otwarty panel administratora
    I istnieją zdefiniowane szablony zadań cyklicznych

  Scenariusz: Generowanie zadań na nowy tydzień
    Gdy przejdę do widoku bieżącego tygodnia
    I kliknę przycisk "Generuj tydzień"
    Wtedy system powinien utworzyć instancje zadań zgodnie z szablonami
    I powiniene kazać komunikat "Wygenerowano zadania cykliczne"
    Oraz lista zadań na poniedziałek nie powinna być pusta

  Scenariusz: Przypisywanie pracownika do zadania
    Gdy przejdę do widoku tygodnia "2026/07"
    I wybiorę pracownika "Jan Kowalski" dla zadania "Karmienie loch"
    Wtedy zadanie powinno wyświetlać "Jan Kowalski" jako wykonawcę
    Oraz w logu audytu powinien pojawić się wpis o przypisaniu zadania

  Scenariusz: Zamykanie tygodnia produkcyjnego
    Mając wygenerowany tydzień o statusie "OPEN"
    Gdy kliknę przycisk "Zamknij tydzień"
    Wtedy status tygodnia powinien zmienić się na "CLOSED"
    I edycja zadań w tym tygodniu powinna być zablokowana
