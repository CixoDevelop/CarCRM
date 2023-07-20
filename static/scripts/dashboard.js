import { lang } from "../modules/lang.js";
import { session_manager } from "../modules/session.js";
import { user_adapter, user_container } from "../modules/user.js";

document.addEventListener("DOMContentLoaded", async () => {
    const session = new session_manager(document.cookie);

    if (! await session.logged()) location.href = "login";

    const user = session.user;
    const content = document.querySelector(".content");
    const greeter = document.createElement("h1");
    const info = document.createElement("p");
    const admin_info = document.createElement("p");
    const privileges_info = document.createElement("p");
    const privileges = document.createElement("ul");
    const personal_data_info = document.createElement("p");
    const personal_data = document.createElement("div");

    personal_data.className = "items";
    privileges.className = "privileges";

    greeter.innerText += lang.dashboard.greeter + user.nick;
    info.innerText = lang.dashboard.info;
    admin_info.innerText = lang.dashboard.admin_info;
    privileges_info.innerText = lang.dashboard.your_privileges;
    personal_data_info.innerText = lang.dashboard.your_notes;

    user.privileges.forEach(privilege => {
        const line = document.createElement("li");

        line.innerText = privilege;
        privileges.appendChild(line);
    });

    user.personal_data.forEach(data => {
        for (const key in data) {
            const key_element = document.createElement("p");
            const value_element = document.createElement("p");

            key_element.innerText = key;
            key_element.className = "key";

            value_element.innerText = data[key];
            value_element.className = "value";

            personal_data.appendChild(key_element);
            personal_data.appendChild(value_element);
        }
    });

    content.appendChild(greeter);
    content.appendChild(info);

    if (user.admin) {
        content.appendChild(admin_info);
    }

    content.appendChild(privileges_info);
    content.appendChild(privileges);
    content.appendChild(personal_data_info);
    content.appendChild(personal_data);
});
