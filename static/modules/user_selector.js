class user_selector {
    constructor(adapter, list) {
        this.adapter = adapter;
        this.list = list;
    }

    set_callback(callback) {
        Array.from(
            this.list.querySelectorAll("input[name='user-select']")
        ).forEach(input => {
            input.addEventListener("change", () => {callback(input.value);});
        });
    }

    async load_users_list() {
        const all_users = await this.adapter.get_all();

        while (this.list.firstChild) {
            this.list.removeChild(this.list.firstChild);
        }

        all_users.forEach(user => {
            const container = document.createElement("span");
            const checker = document.createElement("input");
            const nick = document.createElement("label");

            container.className = "item";

            nick.innerText = user.user.nick;
            nick.className = "nick"; 
            nick.htmlFor = user.user.nick;
        
            checker.id = user.user.nick;
            checker.name = "user-select";
            checker.type = "radio";
            checker.value = user.apikey;
            checker.className = "checker";
      
            container.appendChild(checker);
            container.appendChild(nick);
            this.list.appendChild(container);
        });
    }

    get_selected_user() {
        let selected = false;

        Array.from(
            this.list.querySelectorAll("input[name='user-select']")
        ).forEach(input => {
            if (input.checked) selected = input.value;
        });

        return selected;
    }
}

export { user_selector };
