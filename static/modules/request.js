const encode_get_params = p => 
    "?" + Object.entries(p).map(
        kv => kv.map(encodeURIComponent
    ).join("=")).join("&");

const make_request = async (params, url) => {
    url += encode_get_params(params);

    const response = await fetch(url);
    const content = await response.json();

    return content;
}

const make_post_request = async (params, url) => {
    const response = await fetch(
        url,
        {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(params)
        }
    );

    const content = await response.json();

    return content;
}

export { make_request, make_post_request };
