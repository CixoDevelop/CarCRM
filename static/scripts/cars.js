import { session_manager } from "../modules/session.js";
import { user_adapter, user_container } from "../modules/user.js";
import { car_adapter } from "../modules/car.js";
import { lang } from "../modules/lang.js";
import { file_uploader } from "../modules/file_uploader.js";
import { image } from "../modules/image.js";

class window_creator {
    constructor(adapter) {
        this.adapter = adapter;
    }

    create_param_input(name, content = "") {
        const input = document.createElement("input");

        input.type = "text";
        input.className = "car-param-input";
        input.placeholder = name;
        input.name = name;
        input.value = content;

        return input;
    }

    new_property_adder() {
        if (this.params.querySelector(".property-adding")) return;

        const new_property = document.createElement("input");
        const add_property = document.createElement("input");
        const back = document.createElement("input");

        new_property.className = "property-adding";
        new_property.type = "text";
        add_property.type = "button";
        back.type = "button";
        add_property.value = lang.cars.add_property;
        new_property.placeholder = lang.cars.property_name;
        back.value = lang.cars.back;

        back.addEventListener("click", () => {
            new_property.remove();
            add_property.remove();
            back.remove();
        });

        add_property.addEventListener("click", () => {
            if (!new_property.value) {
                this.result.innerText = lang.cars.empty_property;
                return;
            }

            const new_param = document.createElement("input");

            new_param.type = "text";
            new_param.placeholder = new_property.value;
            new_param.name = new_property.value;
            new_param.className = "car-param-input";

            this.params.appendChild(new_param);
                
            new_property.remove();
            add_property.remove();
            back.remove();
        });
            
        this.params.appendChild(new_property);
        this.params.appendChild(add_property);
        this.params.appendChild(back);
    }

    new_window(where, submit_callback) {
        this.container = document.createElement("div");
        this.params = document.createElement("div");
        this.result = document.createElement("p");

        const cancel = document.createElement("input");
        const submit = document.createElement("input");
        const add = document.createElement("input");

        this.container.className = "create-window";
        cancel.type = "button";
        submit.type = "button";
        add.type = "button";
        cancel.value = lang.cars.cancel;
        submit.value = lang.cars.submit;
        add.value = lang.cars.new_property;
   
        add.addEventListener("click", () => this.new_property_adder());
        cancel.addEventListener("click", () => this.close());
        submit.addEventListener("click", async () => submit_callback());
    
        this.container.appendChild(this.params);
        this.container.appendChild(add);
        this.container.appendChild(cancel);
        this.container.appendChild(submit);
        this.container.appendChild(this.result);
        where.appendChild(this.container);
    }

    create_window(where, callback) {
        this.new_window(where, async () => {
            await this.create();
            callback();
        });

        lang.cars.base_params.forEach(param => {
            this.params.appendChild(this.create_param_input(param));
        });
    }

    create_file_inputs() {
        const files_container = document.createElement("div");

        const documents = new file_uploader(
            lang.cars.docs,
            lang.cars.docs_format,
            async () => {
                documents.result = await this.adapter.documents_set(
                    this.car, 
                    documents
                );
            }
        );

        const photo = new file_uploader(
            lang.cars.photo, 
            lang.cars.photo_format, 
            async () => {
                photo.result = await this.adapter.photo_set(this.car, photo);
            }
        );

        files_container.appendChild(photo.create_window());
        files_container.appendChild(documents.create_window());

        return files_container;
    }

    async edit_window(car_id, where, callback) {
        const car = await this.adapter.get(car_id);
        
        this.car = car;
        this.new_window(where, async () => {
            await this.save();
            callback();
        });

        this.params.appendChild(this.create_file_inputs());

        for (const [key, value] of Object.entries(car.params)) {
            this.params.appendChild(this.create_param_input(key, value));
        }
    }

    async save() {
        let params = {};

        Array.from(this.params.querySelectorAll(".car-param-input")).forEach(
            input => {
                if (!input.name) return;
                if (!input.value) return;

                params[input.name] = input.value;
            }
        );

        this.result.innerText = await this.adapter.save(this.car, params);
    }

    async create() {
        let params = {};

        Array.from(this.params.querySelectorAll("input")).forEach(input => {
            if (!input.name) return;
            if (!input.value) return;

            params[input.name] = input.value;
        });

        this.result.innerText = await this.adapter.create(params);
    }

    close() {
        this.container.remove();
    }
};

class cars_loader {
    constructor(adapter, list, user) {
        this.adapter = adapter;
        this.list = list;
        this.user = user;
    }
    
    async load() {
        while (this.list.firstChild) this.list.firstChild.remove();
    
        Array.from(await this.adapter.get_all()).forEach(car => {
            const container = document.createElement("div");
            const car_id = document.createElement("p");

            container.className = "car";
            car_id.innerText = lang.cars.numbers + car.id;

            container.appendChild(car_id);

            if (car.photo !== "") {
                const photo = new image(car.photo);
                container.appendChild(photo.create(document.body));
            }

            if (car.documents !== "") {
                const documents = document.createElement("a");
                
                documents.href = car.documents;
                documents.innerText = lang.cars.documents;
                container.appendChild(documents);
            }

            for (const [key, content] of Object.entries(car.params)) {
                const name = document.createElement("p");
                const value = document.createElement("p");

                name.innerText = key;
                name.className = "key";
                value.innerText = content;
                value.className = "value";

                container.appendChild(name);
                container.appendChild(value);
            }

            if (this.user.editor) {
                const edit = document.createElement("input");
                const remove = document.createElement("input");

                edit.type = "button";
                edit.value = lang.cars.edit;
                remove.type = "button";
                remove.value = lang.cars.remove;

                edit.addEventListener("click", async () => {
                    const editor = new window_creator(this.adapter);
                    editor.edit_window(
                        car.id, 
                        document.body, 
                        () => this.load() 
                    );
                });

                remove.addEventListener("click", async () => {
                    if (!confirm(lang.cars.sure)) return;

                    await this.adapter.remove(car.id)
                    await this.load();
                });

                container.appendChild(edit);
                container.appendChild(remove);
            }

            this.list.appendChild(container);
        });
    }
}

document.addEventListener("DOMContentLoaded", async () => {
    const session = new session_manager(document.cookie);
    
    if (! await session.logged()) location.href = "login";
   
    const user = session.user;
    const content = document.querySelector(".content");

    if (!user.reader) content.innerText = lang.cars.no_privileges;

    const adapter = new car_adapter(user);
    const list = document.createElement("div");
    const loader = new cars_loader(adapter, list, user);
   
    list.className = "cars-list";

    content.appendChild(list);
    loader.load();

    if (user.editor) {
        const create_car = document.createElement("input");

        create_car.value = lang.cars.create;
        create_car.type = "button";
        create_car.addEventListener("click", async () => {
            if (document.querySelector(".create-window")) return;

            const creator = new window_creator(adapter);
            creator.create_window(document.body, () => loader.load());
        });
    
        content.appendChild(create_car);
    }

    const reload = document.createElement("input");
    
    reload.type = "button";
    reload.value = lang.cars.reload;
    reload.addEventListener("click", () => loader.load());

    content.appendChild(reload);
});
