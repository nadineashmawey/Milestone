const input = document.querySelector("#messageInput"),
    toast = document.querySelector("#chatToast");
input.oninput = () => {
    messageCount.textContent = input.value.length;
    if (input.value.trim()) {
        messageError.hidden = true;
        input.classList.remove("invalid");
    }
};
document.querySelectorAll("[data-team]").forEach(
    (btn) =>
        (btn.onclick = () => {
            document
                .querySelectorAll("[data-team]")
                .forEach((x) => x.classList.toggle("active", x === btn));
            chatTeam.textContent = btn.dataset.team;
        }),
);
messageForm.onsubmit = (e) => {
    e.preventDefault();
    const text = input.value.trim();
    if (!text) {
        messageError.hidden = false;
        input.classList.add("invalid");
        input.focus();
        return;
    }
    const msg = document.createElement("div");
    msg.className = "message own";
    msg.innerHTML =
        "<strong>Sara · TA</strong><p></p><small>Just now · Sent</small>";
    msg.querySelector("p").textContent = text;
    messages.append(msg);
    input.value = "";
    messageCount.textContent = "0";
    messages.scrollTop = messages.scrollHeight;
    toast.hidden = false;
    setTimeout(() => (toast.hidden = true), 3000);
};
toast.querySelector("button").onclick = () => (toast.hidden = true);
const state = new URLSearchParams(location.search).get("state");
if (state) {
    chatContent.hidden = true;
    chatState.hidden = false;
    const states = {
            empty: [
                "0",
                "No conversations yet",
                "Chats with assigned project teams will appear here.",
            ],
            error: [
                "!",
                "Project chat could not be loaded",
                "Something went wrong while loading messages.",
            ],
            failed: [
                "!",
                "Message was not sent",
                "Your message is still in the composer. Check the connection and try again.",
            ],
            revoked: [
                "!",
                "Chat access removed",
                "You can no longer message this team because project access was revoked.",
            ],
            loading: [
                "",
                "Loading project chat",
                '<div class="skeleton"><i></i><i></i><i></i></div>',
            ],
        },
        s = states[state] || states.error;
    chatState.classList.add(state);
    chatState.innerHTML = `<span class="state-icon">${s[0]}</span><h2>${s[1]}</h2>${state === "loading" ? s[2] : `<p>${s[2]}</p><a class="btn" href="/ta/chat">${state === "failed" ? "Try again" : "Return to chat"}</a>`}`;
}
