import { session_manager } from "../modules/session.js";
import { user_container, user_adapter } from "../modules/user.js";
import { lang } from "../modules/lang.js";
import { user_selector } from "../modules/user_selector.js";

document.addEventListener("DOMContentLoaded", async () => {
    const session = new session_manager(document.cookie);
    const adapter = new user_adapter(document.cookie);

    if (! await session.logged()) location.href = "login";
    if (!session.user.admin) location.href = "dashboard";

    const form = document.querySelector(".update-password-form");
    const user_selector_list = form.querySelector(".user-selector-list");
    const password = form.querySelector(".password");
    const password_repeat = form.querySelector(".password-repeat");
    const change = form.querySelector(".change");
    const result = form.querySelector(".result");

    const selector = new user_selector(adapter, user_selector_list);

    await selector.load_users_list();

    change.addEventListener("click", async () => {
        const selected = selector.get_selected_user();
        result.innerText = "";

        if (password.value !== password_repeat.value) {
            result.innerText = lang.errors.passwords_not_same;
            return;
        }

        if (selected === false) {
            result.innerText = lang.errors.user_not_choosed;
            return;
        }

        result.innerText = await adapter.change_password(
            password.value, 
            selected
        ); 
    });
});
