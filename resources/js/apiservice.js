import $ from "jquery";

export function apiService(url, method = "GET", data = {}, headers = {}) {
    if (method == "POST" || method == "PUT" || method == "DELETE") {
        headers = {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        };
    }

    return $.ajax({
        url: url,
        method: method,
        data: data,
        headers: headers,
    })
        .done(function (response) {
            console.log("Success:", response);
        })
        .fail(function (jqXHR, textStatus, errorThrown) {
            console.error("Error:", textStatus, errorThrown);
        });
}
