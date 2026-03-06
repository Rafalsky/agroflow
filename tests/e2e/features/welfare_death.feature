# language: pl
Funkcja: Zgłaszanie upadków zwierząt (PIWET)
  Zgodnie z wymogami PIWET i Stage 4 Welfare
  Jako Pracownik chcę móc szybko zgłaszać upadki zwierząt podczas obchodu
  Aby stan stada był zawsze aktualny

  Scenariusz: Zgłoszenie upadku z poziomu szuflady zadania
    # 1. Wejście w zadanie
    Mając wygenerowany tydzień z zadaniem "Raport Upadków"
    Gdy kliknę w to zadanie
    Wtedy powinna wysunąć się szuflada z widgetem "WelfareDeathWidget"

    # 2. Wypełnienie widgetu
    Gdy wybiorę kategorię wiekową "tuczniki" (FATTENERS)
    I wpiszę liczbę sztuk "2"
    I wpiszę powód/notatkę "Nagły upadek w komorze 3"
    I kliknę "Oznacz jako Wykonane"

    # 3. Weryfikacja po stronie serwera i bazy
    Wtedy zadanie powinno otrzymać status "Wykonano" (DONE)
    Oraz stan magazynowy stada "FATTENERS" powinien pomniejszyć się o 2 sztuki
    Oraz w logach audytu (WelfareHistory) powinien pojawić się wpis o powodzie "DEATH"
