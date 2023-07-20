import { session_manager } from "../modules/session.js";
import { user_adapter, user_container } from "../modules/user.js";
import { lang } from "../modules/lang.js";

document.addEventListener("DOMContentLoaded", async () => {
    const session = new session_manager(document.cookie);

    if (! await session.logged()) location.href = "login";

    const create_item = (key_content, value_content) => {
        const item = document.createElement("span");
        const key = document.createElement("p");
        const value = document.createElement("p");
        const deleter = document.createElement("p");
        const breaker = document.createElement("br");

        item.classList.add("item");
        item.classList.add("row");

        key.className = "key";
        key.innerText = key_content;

        value.className = "value";
        value.innerText = value_content;

        deleter.className = "deleter";
        deleter.innerText = "Usuń";
    
        deleter.addEventListener("click", () => {
            item.remove();
        });

        item.appendChild(key);
        item.appendChild(value);
        item.appendChild(deleter);
        item.appendChild(breaker);

        return item;
    }

    const content = document.querySelector(".content");
    const personal_data = content.querySelector(".personal-data");
    const appender = personal_data.querySelector(".appender");
    const adder = appender.querySelector(".adder");
    const add_key = appender.querySelector(".key");
    const add_value = appender.querySelector(".value");

    adder.addEventListener("click", () => {
        if (add_key.value == "" || add_value.value == "") return;

        personal_data.appendChild(
            create_item(add_key.value, add_value.value)
        );

        add_key.value = "";
        add_value.value = "";
    });

    const adapter = new user_adapter(document.cookie);
    const user = session.user;

    const create_list_from_array = (items, data) => {
        data.forEach(item => {
            const data_array = Object.entries(item)[0];
            items.appendChild(create_item(data_array[0], data_array[1]));
        }); 
    };

    create_list_from_array(personal_data, user.personal_data);

    const create_array_to_save = personal_data => {
        let data = [];

        Array.from(personal_data.querySelectorAll(".row")).forEach(row => {
            const key = row.querySelector(".key").innerText;
            const value = row.querySelector(".value").innerText;

            let new_row = new Object();

            new_row[key] = value;

            data.push(new_row);
        });

        return data;
    };

    const save = personal_data.querySelector(".save");
    const saver = save.querySelector(".saver");
    const result = save.querySelector(".result");

    saver.addEventListener("click", async () => {
        const new_data = create_array_to_save(personal_data);
        const response = await adapter.update_personal_data(new_data);

        if (response !== true) {
            result.innerText = response;
            return;
        }

        result.innerText = "Udało się zapisać dane!";

        setTimeout(() => {result.innerText = "";}, 4000);
    });
});
