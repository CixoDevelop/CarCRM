import { session_manager } from "../modules/session.js";
import { user_container, user_adapter } from "../modules/user.js";

document.addEventListener("DOMContentLoaded", async () => {
    const session = new session_manager(document.cookie);
    
    if (! await session.logged()) {
        location.href = "login";
    }

    if (session.user.admin) {
        document.querySelector(".admin-row").style.display = "block";
    }
});
