import { user_adapter, user_container } from "../modules/user.js";
import { session_manager } from "../modules/session.js";

document.addEventListener("DOMContentLoaded", async () => {
    const session = new session_manager(document.cookie);

    if (await session.logged()) location.href = "dashboard";
});

document.addEventListener("DOMContentLoaded", async () => {
    let form = document.querySelector(".login");
    let nick = form.querySelector("#nick");
    let password = form.querySelector("#password");
    let result = form.querySelector(".result");

    form.addEventListener("submit", async (event) => {
        event.preventDefault();

        const adapter = new user_adapter();
        const response = await adapter.login(nick.value, password.value);
    
        if (response.error !== false) {
            result.innerText = response.error;
            return;
        }
        
        document.cookie = response.apikey;
        location.href = "dashboard";
        result.innerText = "Udało się zalogować!";
    });
});
