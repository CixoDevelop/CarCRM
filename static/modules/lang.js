const lang = {
    dashboard: {
        greeter: "Witaj ",
        info: "" +
            "Oto panel główny uzytkownika systemu CarCRM, po lewej stronie " +
            "znajduje się nawigacja, aby przejść do ustawień konta, takich " + 
            "jak zmiana hasła, czy Twoje notatki użyj opcji \"Ustawienia\", " + 
            "natomiast aby przejść do edycji lub przeglądania samochodów" +
            "użyj zakładki \"Pojazdy\".",
        admin_info: "" + 
            "Jesteś administratorem, jako administrator powinieneś uważać " +
            "na to co robisz w systemie, możesz zmieniać hasła innych czy " +
            "ich uprawnienia, ZACHOWAJ OSTROŻNOŚĆ!!!",
        your_privileges: "Twoje uprawnienia:",
        your_notes: "Twoje notatki:",
    },

    menu: [
        { href: "dashboard", value: "Witaj" },
        { href: "cars", value: "Samochody" },
        { href: "change_personal_data", value: "Notatki" },
        { href: "settings", value: "Ustawienia" },
        { href: "logout", value: "Wyloguj" }
    ],

    errors: {
        success: "Operacja zakończona powodzeniem!",
        passwords_not_same: "Hasła nie sią identyczne!",
        user_not_choosed: "Musisz wybrać użytkownika!",
        60: "Hasło zbyt krótkie!",
        10: "Nick jest w użyciu!",
        80: "Nick jest zbyt krótki!",
        90: "Nie możesz usunąć sam siebie!",
        190: "Nie możesz usunąć swoich uprawnień administracyjnych!",
        240: "Hasło lub nick nie są prawidłowe!",
        403: "Wszyskie parametry muszą być wypełnione!",
    },

    cars: {
        create: "Stwórz pojazd",
        cancel: "Anuluj",
        submit: "Zatwierdź",
        upload: "Wgraj inne",
        base_params: [
            "Cena",
            "Marka",
            "Kolor",
            "Model",
        ],
        edit: "Edytuj",
        remove: "Usuń",
        reload: "Odśwież",
        sure: "Czy jesteś pewien, że chcesz usunąć ten samochód?",
        numbers: "Pojazd #",
        new_property: "Nowa właściwość",
        add_property: "Dodaj właściwość",
        property_name: "Nazwa właściwości",
        empty_property: "Nazwa właściwości nie może być pusta!",
        back: "Nie dodawaj własciwości",
        no_privileges: "Nie masz uprawnień do odczytu tej sekcji!",
        documents: "Pobierz dokument samochodu",
        photo: "Zdjęcie",
        photo_format: ".jpg, .png, .jpeg, .bmp, .wemb, .webp",
        docs_format: ".pdf, .txt, .doc, .docx",
        docs: "Dokument",
    },
    
    image: {
        close: "Zamknij",
    },
};

export { lang };
