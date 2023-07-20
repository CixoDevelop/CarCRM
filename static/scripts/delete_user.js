import { session_manager } from "../modules/session.js";
import { user_adapter, user_container } from "../modules/user.js";
import { lang } from "../modules/lang.js";
import { user_selector } from "../modules/user_selector.js";

document.addEventListener("DOMContentLoaded", async () => {
    const session = new session_manager(document.cookie);
    const adapter = new user_adapter(document.cookie);

    if (! await session.logged()) location.href = "login";
    if (!session.user.admin) location.href = "dashboard";

    const form = document.querySelector(".delete-user-form");
    const remove = form.querySelector(".remove");
    const result = form.querySelector(".result");
    const user_selector_list = form.querySelector(".user-selector-list");

    const selector = new user_selector(adapter, user_selector_list);
    
    await selector.load_users_list();

    remove.addEventListener("click", async () => {
        const selected = selector.get_selected_user();
        result.innerText = "";

        if (selected === false) {
            result.innerText = lang.errors.user_not_choosed;
            return;
        }

        if (!confirm("Czy napewno chcesz usunąć użytkownika?")) {
            return;
        }

        result.innerText = await adapter.drop(selected);
        
        await selector.load_users_list();
    });
});
