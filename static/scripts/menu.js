import { lang } from "../modules/lang.js";

document.addEventListener("DOMContentLoaded", () => {
    const navigation = document.querySelector(".navigation");
    const list = document.createElement("ul");

    const create_list_item  = (href, value) => {
        const link = document.createElement("a");
        const item = document.createElement("li");

        link.href = href;
        item.innerText = value;

        link.appendChild(item);

        return link;
    };

    lang.menu.forEach(menu_item => {
        list.appendChild(create_list_item(menu_item.href, menu_item.value));
    });

    navigation.appendChild(list);
});
