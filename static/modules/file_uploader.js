import { lang } from "./lang.js";

class file_uploader {
    constructor(title, types, callback) {
        this.types = types;
        this.callback = callback;
        this.title = title;
    }

    set result(state) {
        this.result_p.innerText = state;

        return state;
    }

    create_window() {
        const uploader = document.createElement("div");
        const upload = document.createElement("input");
        const submit = document.createElement("input");
        const cancel = document.createElement("input");
        const title = document.createElement("p");
        this.result_p = document.createElement("p");
        this.file = document.createElement("input");

        title.innerHTML = this.title;
        uploader.className = "file-uploader";

        this.file.type = "file";
        this.file.accept = this.types;

        cancel.type = "button";
        cancel.value = lang.cars.cancel;

        submit.type = "button";
        submit.value = lang.cars.submit;

        upload.type = "button";
        upload.value = lang.cars.upload;

        const show = () => {
            upload.style.display = "none";
            submit.style.display = "block";
            cancel.style.display = "block";
            this.file.style.display = "block";
            this.result_p.style.display = "none";
        };

        const hide = () => {
            upload.style.display = "block";
            submit.style.display = "none";
            cancel.style.display = "none";
            this.file.style.display = "none";
            this.result_p.style.display = "block";
            this.result = "";
        };

        upload.addEventListener("click", () => show());
        cancel.addEventListener("click", () => hide());
        submit.addEventListener("click", () => { this.callback(); hide(); } );
   
        uploader.appendChild(title);
        uploader.appendChild(upload);
        uploader.appendChild(this.file);
        uploader.appendChild(cancel);
        uploader.appendChild(submit);
        uploader.appendChild(this.result_p);
   
        hide();

        return uploader;
    }

    get extension() {
        return this.file.value.slice(this.file.value.lastIndexOf(".") + 1);
    }
    
    get content() {
        return this.file.files.item(0);
    }
}

export { file_uploader };
