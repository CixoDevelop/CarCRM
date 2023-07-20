import { make_request, make_post_request } from "./request.js";
import { lang } from "./lang.js";
import { user_container } from "./user.js";

class car_adapter {
    constructor(user) {
        this.user = user;
    }

    async get(id) {
        const result = await make_request(
            {apikey: this.user.apikey, id: id},
            "car/get"
        );

        if (result.status === "success") {
            return result.response;
        }

        return {};
    }

    async get_all() {
        const result = await make_request(
            { apikey: this.user.apikey },
            "car/get_all"
        );

        if (result.status === "success") {
            return result.response;
        }

        return [];
    }

    async save(car, params) {
        const request_params = {
            apikey: this.user.apikey,
            id: car.id,
            params: params,
        };

        const result = await make_post_request(
            request_params,
            "car/save"
        );

        return this.parse_result(result);
    }
   
    async photo_set(car, photo) {
        const params = {
            photo: await this.blob_to_base64(photo.content),
            extension: photo.extension,
            id: car.id,
            apikey: this.user.apikey
        };
        
        const result = await make_post_request(params, "car/send_photo");
        return this.parse_result(result);
    }

    async documents_set(car, documents) {
        const params = {
            document: await this.blob_to_base64(documents.content),
            extension: documents.extension,
            id: car.id,
            apikey: this.user.apikey
        };
        
        const result = await make_post_request(params, "car/send_document");
        return this.parse_result(result);
    }

    async blob_to_base64(blob) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();

            reader.addEventListener("load", () => {
                const position = reader.result.search("base64,") + 7;
                const content = reader.result.substr(position);
                resolve(content);
            });

            reader.addEventListener("error", (error) => reject(error));

            reader.readAsDataURL(blob);
        });
    }

    async create(params) {
        const request_params = {
            apikey: this.user.apikey,
            documents: "",
            params: params,
            photo: ""
        };

        const result = await make_post_request(
            request_params,
            "car/create"
        );

        return this.parse_result(result);
    }

    async remove(id) {
        const params = {
            apikey: this.user.apikey,
            id: id
        }

        const result = await make_request(params, "car/delete");

        console.log(result);

        return this.parse_result(result);
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

export { car_adapter };
