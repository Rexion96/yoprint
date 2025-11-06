import "./bootstrap";
import Echo from "laravel-echo";
import Pusher from "pusher-js";

Pusher.logToConsole = true;

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: "pusher",
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
    enabledTransports: ["ws", "wss", "sockjs", "xhr_streaming", "xhr_polling"],
});

document.addEventListener("DOMContentLoaded", () => {
    if (!document.getElementById("upload-table-body")) return;

    window.Echo.channel("uploads").listen(".status.updated", (e) => {
        const row = document.getElementById(`upload-row-${e.upload.id}`);
        if (row) {
            const statusText = (e.upload.status || "").toString();
            row.querySelector(".status").innerText =
                statusText.charAt(0).toUpperCase() + statusText.slice(1);
            if (e.upload.processed_at) {
                row.querySelector("td:nth-child(4)").innerText = new Date(
                    e.upload.processed_at
                )
                    .toISOString()
                    .slice(0, 19)
                    .replace("T", " ");
            }
        } else {
            console.log("Row not found for upload id", e.upload.id);
        }
    });
});
