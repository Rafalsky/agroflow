# language: pl
Funkcja: Ranking i Punktacja (Admin)
  Jako Zootechnik
  Chcę widzieć ranking pracowników
  Aby móc oceniać efektywność zespołu

  Scenariusz: Wyświetlanie globalnego rankingu
    Mając zarejestrowane wykonania zadań przez wielu pracowników
    Gdy przejdę do sekcji "Ranking"
    Wtedy powinienem zobaczyć listę pracowników posortowaną według sumy punktów
    Oraz każdy wpis powinien zawierać nazwę pracownika i jego całkowity wynik

  Scenariusz: Podsumowanie tygodniowe w widoku tygodnia
    Mając otwarty widok tygodnia "2026/07"
    Wtedy powinienem widzieć sekcję "Najlepsi w tym tygodniu"
    Oraz suma punktów zespołu dla tego tygodnia powinna być widoczna pod nagłówkiem
    Oraz suma punktów powinna się zgadzać z sumą punktów za wszystkie wykonane zadania w tym tygodniu
