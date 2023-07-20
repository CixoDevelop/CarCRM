import { make_request, make_post_request } from "./request.js";
import { lang } from "./lang.js";

class user_adapter {
    constructor(apikey = null) {
        this.apikey = apikey;
    }

    async change_privilege(destination, name, state) {
        const params = {
            apikey: this.apikey,
            destination_user_apikey: destination,
            permission: name,
            new_state: state ? "true" : "false"
        };

        const result = await make_post_request(
            params, 
            "user/change_permission"
        );

        return this.parse_result(result);
    }

    async drop(dropper) {
        const params = {
            apikey: this.apikey,
            deleted: dropper
        };
        
        const result = await make_post_request(params, "user/drop");

        return this.parse_result(result);
    }

    async get_all() {
        const params = {
            apikey: this.apikey
        };

        const result = await make_request(params, "user/get_all");

        if (result.status === "fail") {
            return this.error_message(result);
        }
        
        return result.response;
    }

    async change_password(password, destination = null) {
        const params = {
            apikey: this.apikey,
            destination_user_apikey: 
                (destination == null) ? this.apikey : destination,
            password: password
        };

        const result = await make_post_request(
            params, 
            "user/update_password"
        );

        return this.parse_result(result);
    }

    async update_personal_data(personal_data, performer = null) {
        const params = {
            apikey: (performer == null) ? this.apikey : performer,
            destination_user_apikey: this.apikey,
            personal_data: personal_data
        };

        const result = await make_post_request(
            params, 
            "user/update_personal_data"
        )

        return this.parse_result(result);
    }

    async get_user() {
        const params = {
            apikey: this.apikey
        };

        const result = await make_request(params, "user/get");

        if (result.status === "fail") {
            return this.error_message(result);
        }

        return new user_container(result.response, this.apikey);
    }

    async create_user(nick, password) {
        const params = {
            apikey: this.apikey,
            nick: nick,
            password: password
        };

        const result = await make_post_request(params, "user/create");

        return this.parse_result(result);
    }

    async login(nick, password) {
        const params = {
            nick: nick, 
            password: password
        };

        const result = await make_request(params, "user/login");

        if (result.status == "fail") {
            return {
                error: this.error_message(result),
                apikey: false
            };
        }

        return {
            error: false,
            apikey: result.response
        };

    }

    error_message(result) {
        if (result.error_code in lang.errors) {
            return lang.errors[result.error_code];
        }

        return result.error_code + ": " + result.cause;
    }

    parse_result(result) {
        if (result.status == "success") return lang.errors.success;

        return this.error_message(result);
    }
}

class user_container {
    constructor (container, apikey) {
        this.apikey = apikey;
        this.nick = container.nick;
        this.personal_data = container.personal_data;
        this.privileges = container.privileges;
    }

    get apikey() {
        return this.apikey;
    }

    get admin() {
        return this.privileges.includes("system_admin");
    }

    get editor() {
        return this.privileges.includes("editor") || this.admin;
    }

    get reader() {
        return this.privileges.includes("reader") || this.editor;
    }

    apikey = "";
    nick = "";
    personal_data = [];
    privileges = [];
}

export { user_container, user_adapter };
