# language: pl
Funkcja: Monitoring Dobrostanu i Stanu Stada
  Jako Pracownik lub Zootechnik
  Chcę aktualizować liczbę zwierząt w grupach
  Aby mieć realny podgląd na stan inwentarza

  Założenia:
    Mając otwarty widget "Welfare & Stock"

  Scenariusz: Rejestrowanie upadku w grupie prosiąt
    Mając stan grupy "Prosięta" wynoszący 100 sztuk
    Gdy wybiorę kategorię "Prosięta"
    I wybiorę powód "Upadek"
    I wpiszę wartość "-1"
    I kliknę "ZAPISZ ZMIANĘ"
    Wtedy stan grupy "Prosięta" powinien wynosić 99
    Oraz w historii zmian powinien pojawić się wpis o 1 upadku z moją nazwą

  Scenariusz: Walidacja stanu ujemnego
    Mając stan grupy "Lochy" wynoszący 5 sztuk
    Gdy spróbuję odjąć 10 sztuk z powodu "Sprzedaż"
    Wtedy system powinien wyświetlić błąd walidacji "Cannot reduce stock below zero"
    Oraz stan grupy "Lochy" nie powinien ulec zmianie

  Scenariusz: Rejestrowanie nowych urodzeń
    Gdy wybiorę kategorię "Prosięta"
    I wybiorę powód "Urodziny"
    I wpiszę wartość "12"
    I kliknę "ZAPISZ ZMIANĘ"
    Wtedy bieżący stan prosiąt powinien wzrosnąć o 12
