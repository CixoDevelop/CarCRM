import { session_manager } from "../modules/session.js";
import { user_container, user_adapter } from "../modules/user.js";
import { lang } from "../modules/lang.js";
import { user_selector } from "../modules/user_selector.js";

class privileges_manager {
    constructor(list) {
        this.list = list;
        this.start = [];
    }

    create_list_item(privilege) {
        const container = document.createElement("span");
        const content = document.createElement("p");
        const remove = document.createElement("p");

        container.className = "item";

        content.className = "privilege";
        content.innerText = privilege;

        remove.className = "remove";
        remove.innerText = "Usuń";

        remove.addEventListener("click", () => {
            container.remove();
        });

        container.appendChild(content);
        container.appendChild(remove);

        return container;
    }

    load_privileges(privileges) {
        this.start = privileges;

        while (this.list.firstChild) {
            this.list.firstChild.remove();
        }

        const container = document.createElement("span");
        const new_privilege = document.createElement("input");
        const add_privilege = document.createElement("input");

        container.className = "item";

        new_privilege.type = "text";
        new_privilege.placeholder = "Nazwa uprawnienia";
        new_privilege.className = "new-privilege";

        add_privilege.type = "button";
        add_privilege.value = "Dodaj";
        add_privilege.className = "add-privilege";

        container.appendChild(new_privilege);
        container.appendChild(add_privilege);
        this.list.appendChild(container);

        add_privilege.addEventListener("click", () => {
            if (new_privilege.value == "") return;

            this.list.appendChild(this.create_list_item(new_privilege.value));
            new_privilege.value = "";
        });

        privileges.forEach(privilege => {
            this.list.appendChild(this.create_list_item(privilege));
        });
    }

    compare() {
        let compare_result = [];
        let current_privileges = [];
        
        Array.from(this.list.querySelectorAll(".privilege")).forEach(p => {
            current_privileges.push(p.innerText);
        });

        current_privileges.forEach(new_privilege => {
            if (this.start.includes(new_privilege)) return;

            compare_result.push({
                name: new_privilege,
                state: true
            });
        });

        this.start.forEach(old_privilege => {
            if (current_privileges.includes(old_privilege)) return;

            compare_result.push({
                name: old_privilege,
                state: false
            });
        });

        return compare_result;
    }
}

document.addEventListener("DOMContentLoaded", async () => {
    const session = new session_manager(document.cookie);
    const adapter = new user_adapter(document.cookie);

    if (! await session.logged()) location.href = "login";
    if (!session.user.admin) location.href = "dashboard";

    const form = document.querySelector(".update-privileges-form");
    const privileges_list = form.querySelector(".privileges");
    const save = form.querySelector(".save");
    const result = form.querySelector(".result");
    const selector_list = form.querySelector(".user-selector-list");
    const selector = new user_selector(adapter, selector_list);

    let manager = new privileges_manager(privileges_list);
    let selected = "";

    await selector.load_users_list();
    
    selector.set_callback(async (selected_apikey) => {
        const selected_adapter = new user_adapter(selected_apikey); 
        const selected_user = await selected_adapter.get_user();
        const privileges = selected_user.privileges;
    
        selected = selected_apikey;

        await manager.load_privileges(privileges);
    });

    save.addEventListener("click", async () => {
        result.innerText = "";

        if (selected == "") {
            result.innerText = lang.errors.user_not_choosed;
            return;
        }
        
        const compare = manager.compare();

        for (let count = 0; count < compare.length; ++count) {
            const response = await adapter.change_privilege(
                selected,
                compare[count].name, 
                compare[count].state
            );

            result.innerText = response;
        }

        result.innerText = lang.errors.success;
    });
});
