# language: pl
Funkcja: Wykonywanie zadań przez pracownika
  Jako Pracownik
  Chcę mieć dostęp do listy swoich zadań przez magiczny link
  Aby móc raportować postępy bez tradycyjnego logowania

  Scenariusz: Dostęp do listy zadań przez token
    Mając pracownika "Piotr" z aktywnym tokenem "abc-123"
    Gdy wejdę na adres "/w/abc-123"
    Wtedy powinienem zobaczyć powitanie "Cześć, Piotr"
    Oraz listę zadań przypisanych do mnie na dziś

  Scenariusz: Oznaczanie zadania jako wykonane
    Mając otwartą listę zadań przez magiczny link
    I zadanie "Sprawdzenie poidła" o statusie "PENDING"
    Gdy kliknę przycisk "DONE" przy zadaniu
    Wtedy status zadania powinien zmienić się na "DONE"
    Oraz moje punkty w tym tygodniu powinny wzrosnąć o wartość zadania
    Oraz przycisk powinien zmienić się na "UNDO"

  Scenariusz: Cofanie wykonania zadania
    Mając zadanie "Czyszczenie koryta" o statusie "DONE"
    Gdy kliknę przycisk "UNDO" przy zadaniu
    Wtedy status zadania powinien wrócić do "PENDING"
    Oraz punkty dodane za to zadanie powinny zostać odjęte
