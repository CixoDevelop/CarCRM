import { session_manager } from "../modules/session.js";
import { user_container, user_adapter } from "../modules/user.js";
import { lang } from "../modules/lang.js";

document.addEventListener("DOMContentLoaded", async () => {
    const session = new session_manager(document.cookie);
    
    if (! await session.logged()) location.href = "login";

    const form = document.querySelector(".update-password-form");
    const password = form.querySelector("#password");
    const password_repeat = form.querySelector("#password-repeat");
    const change = form.querySelector("#change");
    const result = form.querySelector("#result");

    change.addEventListener("click", async () => {
        result.innerText = "";

        if (password.value != password_repeat.value) {
            result.innerText = lang.errors.passwords_not_same;
            return;
        }

        const adapter = new user_adapter(document.cookie);
        result.innerText = await adapter.change_password(password.value);
    });
});
