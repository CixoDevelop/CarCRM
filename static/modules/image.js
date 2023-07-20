import { lang } from "./lang.js";

class image {
    constructor(source) {
        this.source = source;
        this.gallery = false;
    }

    create(body) {
        this.body = body;

        this.image = document.createElement("img");

        this.image.src = this.source;
        this.image.className = "gallery-image";
        this.image.addEventListener("click", () => this.open());

        return this.image;
    }

    is_open() {
        return this.gallery !== false;
    }

    open() {
        if (this.is_open()) return;

        const close_button = document.createElement("p");
        const image = document.createElement("img");
        this.gallery = document.createElement("div");

        this.gallery.className = "gallery-open";
        
        close_button.innerText = lang.image.close;
        close_button.addEventListener("click", () => this.close());

        image.src = this.source;

        this.gallery.appendChild(image);
        this.gallery.appendChild(close_button);
        this.body.appendChild(this.gallery);
    }

    close() {
        if (!this.is_open()) return;

        this.gallery.remove();
        this.gallery = false;
    }
}

export { image };
