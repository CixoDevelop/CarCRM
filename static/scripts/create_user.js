import { session_manager } from "../modules/session.js";
import { user_container, user_adapter } from "../modules/user.js";
import { lang } from "../modules/lang.js";

document.addEventListener("DOMContentLoaded", async () => {
    const session = new session_manager(document.cookie);
    const adapter = new user_adapter(document.cookie);
    
    if (! await session.logged()) location.href = "login";
    if (!session.user.admin) location.href = "login";

    const form = document.querySelector(".create-user-form");
    const nick = form.querySelector(".nick");
    const password = form.querySelector(".password");
    const password_repeat = form.querySelector(".password-repeat");
    const create = form.querySelector(".create");
    const result = form.querySelector(".result");

    create.addEventListener("click", async () => {
        result.innerText = "";

        if (password.value !== password_repeat.value) {
            result.innerText = lang.errors.passwords_not_same;
            return;
        }

        result.innerText = await adapter.create_user(
            nick.value, 
            password.value
        );
    });
});
