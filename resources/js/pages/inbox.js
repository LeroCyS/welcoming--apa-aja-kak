/** Inbox triase: update realtime (Echo) + polling 10 detik. */
let registered = false;

export default function (Alpine) {
    if (registered) return;
    registered = true;
    Alpine.data('inboxFeed', (url, filters, channel, opts = {}) => ({
        showDone: false,
        init() {
            const refresh = () => this.refresh();
            window.wlSubscribe(channel, { InboxUpdated: refresh, NewConversationAssigned: refresh }, refresh, 10000);
        },
        async refresh() {
            const { data } = await window.axios.get(url, { params: { ...filters, ...opts } });
            if (this.$refs.list) this.$refs.list.innerHTML = data.html;
        },
    }));
}
